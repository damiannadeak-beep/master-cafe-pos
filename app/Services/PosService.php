<?php

namespace App\Services;

use App\Models\{Pesanan, DetailPesanan, Pembayaran, Setting};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PosService
{
    /**
     * Mengambil query dasar pesanan aktif (Strict Pay-First Policy untuk Takeaway)
     */
    public function getActiveOrdersQuery()
    {
        return Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen'])
            ->whereNotIn('status', ['cancelled', 'void'])
            ->where(function ($statusQ) {
                // 1. Pesanan yang sedang diproses di dapur (pending / processing)
                $statusQ->whereIn('status', ['pending', 'processing'])
                    // 2. ATAU pesanan yang sudah selesai dimasak (completed) tetapi BELUM LUNAS
                    ->orWhere(function ($compUnpaidQ) {
                        $compUnpaidQ->where('status', 'completed')
                            ->where(function ($pSub) {
                                $pSub->whereDoesntHave('pembayaran')
                                     ->orWhereHas('pembayaran', function ($p) {
                                         $p->where('status', '!=', 'paid');
                                     });
                            });
                    });
            })
            ->where(function ($sub) {
                // 1. Dibuat langsung oleh Kasir di POS
                $sub->whereNotNull('id_kasir')
                    // 2. Pesanan Dine-In (Makan di Tempat) dengan Meja Aktif (Open Bill)
                    ->orWhere(function ($dineInQ) {
                        $dineInQ->where('tipe_pesanan', 'dine_in')
                                ->whereNotNull('id_meja');
                    })
                    // 3. Sudah Lunas (QRIS, Transfer, atau Tunai Kasir)
                    ->orWhereHas('pembayaran', function ($p) {
                        $p->where('status', 'paid')
                          // 4. Tunai di Meja (Cash)
                          ->orWhere(function ($cashQ) {
                              $cashQ->where('status', 'unpaid')
                                    ->where('metode', 'cash');
                          });
                    });
            });
    }

    /**
     * Mengambil query dasar riwayat pesanan selesai
     */
    public function getCompletedOrdersQuery(int $limit = 50)
    {
        return Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen', 'kasir'])
            ->where('status', 'completed')
            ->whereHas('pembayaran', function ($p) {
                $p->where('status', 'paid');
            })
            ->orderBy('updated_at', 'desc')
            ->take($limit);
    }

    /**
     * Mengelompokkan pesanan aktif per Meja (Dine-In) atau per Tamu (Takeaway)
     */
    public function groupActiveOrders(Collection $orders): Collection
    {
        $groups = [];

        foreach ($orders as $order) {
            if ($order->tipe_pesanan === 'dine_in') {
                if ($order->id_meja) {
                    $cleanCust = strtolower(trim($order->guest_name ?? ''));
                    $guestSuffix = !empty($cleanCust) ? ('_' . $cleanCust) : '';
                    $groupKey = 'table_' . $order->id_meja . $guestSuffix;
                } else {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone ?? '');
                    $groupKey = 'dinein_nomeja_' . ($cleanPhone ?: strtolower(trim($order->customer_name ?: 'guest'))) . '_' . $order->id;
                }
            } else {
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone ?? '');
                if (!empty($cleanPhone)) {
                    $groupKey = 'takeaway_phone_' . $cleanPhone;
                } else {
                    $groupKey = 'takeaway_name_' . strtolower(trim($order->customer_name ?: 'guest'));
                }
            }

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'group_key' => $groupKey,
                    'tipe_pesanan' => $order->tipe_pesanan,
                    'meja' => $order->meja,
                    'id_meja' => $order->id_meja,
                    'customer_name' => $order->customer_name,
                    'guest_phone' => $order->guest_phone,
                    'created_at' => $order->created_at,
                    'latest_created_at' => $order->created_at,
                    'orders' => collect([]),
                ];
            }

            $groups[$groupKey]['orders']->push($order);
            if ($order->created_at > $groups[$groupKey]['latest_created_at']) {
                $groups[$groupKey]['latest_created_at'] = $order->created_at;
            }
        }

        $result = collect([]);
        foreach ($groups as $g) {
            $ordersList = $g['orders']->sortBy('id')->values();
            $orderIds = $ordersList->pluck('id')->all();
            $primaryOrder = $ordersList->first();

            $totalItems = 0;
            $totalBill = 0;
            $totalDiscount = 0;
            $allPaid = true;
            $hasPending = false;
            $hasProcessing = false;
            $unpaidAmount = 0;
            $totalUangDiterima = 0;
            $totalUangKembalian = 0;
            $hasCashUnpaid = false;

            foreach ($ordersList as $ord) {
                $ordTotal = (int) (($ord->pembayaran && (float)$ord->pembayaran->total_bayar > 0) 
                    ? $ord->pembayaran->total_bayar 
                    : ($ord->total - ($ord->discount_amount ?? 0)));
                $totalBill += $ordTotal;
                $totalDiscount += (float) ($ord->discount_amount ?? 0);

                $isThisPaid = ($ord->pembayaran && $ord->pembayaran->status === 'paid');
                if (!$isThisPaid) {
                    $allPaid = false;
                    $unpaidAmount += $ordTotal;
                    if ($ord->pembayaran && $ord->pembayaran->metode === 'cash') {
                        $hasCashUnpaid = true;
                    }
                } else {
                    if ($ord->pembayaran) {
                        $totalUangDiterima += (int) ($ord->pembayaran->uang_diterima ?? 0);
                        $totalUangKembalian += (int) ($ord->pembayaran->uang_kembalian ?? 0);
                    }
                }

                if ($ord->status === 'pending') {
                    $hasPending = true;
                } elseif ($ord->status === 'processing') {
                    $hasProcessing = true;
                }

                foreach ($ord->detail_pesanan as $detail) {
                    $totalItems += $detail->jumlah;
                }
            }

            // Jika belum lunas dan ada cash unpaid, set uang_diterima = unpaid amount (uang pas)
            if (!$allPaid && $hasCashUnpaid) {
                $totalUangDiterima = (int) $unpaidAmount;
                $totalUangKembalian = 0;
            }

            $overallStatus = $hasPending ? 'pending' : ($hasProcessing ? 'processing' : 'completed');

            // Filter pesanan yang memenuhi syarat untuk divoid (belum lunas dan belum selesai dibuat/dimasak)
            $voidableOrders = $ordersList->filter(function ($ord) {
                $isPaid = ($ord->pembayaran && $ord->pembayaran->status === 'paid');
                $isCompleted = ($ord->status === 'completed');
                $isCancelled = in_array($ord->status, ['cancelled', 'void']);
                return !$isPaid && !$isCompleted && !$isCancelled;
            })->values();

            $voidOrderOptions = $voidableOrders->map(function ($o) {
                $tot = (float) (($o->pembayaran && (float)$o->pembayaran->total_bayar > 0)
                    ? $o->pembayaran->total_bayar
                    : ($o->total - ($o->discount_amount ?? 0)));
                return [
                    'id' => $o->id,
                    'label' => 'Pesanan #' . $o->id . ' (Belum Bayar - Rp ' . number_format($tot, 0, ',', '.') . ')',
                    'is_paid' => false,
                ];
            })->all();

            $canVoid = !empty($voidOrderOptions);
            $primaryVoidId = $voidableOrders->first()?->id ?? $primaryOrder->id;

            $result->push((object) [
                'group_key' => $g['group_key'],
                'primary_order' => $primaryOrder,
                'orders' => $ordersList,
                'order_ids' => $orderIds,
                'order_ids_string' => implode(' & #', $orderIds),
                'order_ids_badge' => implode(', ', array_map(fn($id) => '#' . $id, $orderIds)),
                'is_grouped' => count($ordersList) > 1,
                'tipe_pesanan' => $g['tipe_pesanan'],
                'meja' => $g['meja'],
                'id_meja' => $g['id_meja'],
                'customer_name' => $g['customer_name'],
                'guest_phone' => $g['guest_phone'],
                'created_at' => $g['created_at'],
                'latest_created_at' => $g['latest_created_at'],
                'total_items' => $totalItems,
                'total_bill' => $totalBill,
                'total_discount' => $totalDiscount,
                'all_paid' => $allPaid,
                'unpaid_amount' => $unpaidAmount,
                'total_uang_diterima' => $totalUangDiterima,
                'total_uang_kembalian' => $totalUangKembalian,
                'overall_status' => $overallStatus,
                'can_void' => $canVoid,
                'voidable_orders' => $voidableOrders,
                'void_order_options' => $voidOrderOptions,
                'primary_void_id' => $primaryVoidId,
            ]);
        }

        return $result;
    }

    /**
     * Mengelompokkan riwayat pesanan selesai per Meja / Tamu dalam sesi waktu berdekatan
     */
    public function groupCompletedOrders(Collection $orders): Collection
    {
        $groups = [];

        foreach ($orders as $order) {
            $windowBlock = $order->created_at ? (floor($order->created_at->hour / 3)) : 0;
            $datePrefix = $order->created_at ? $order->created_at->format('Y-m-d') : 'today';

            if ($order->tipe_pesanan === 'dine_in') {
                if ($order->id_meja) {
                    $groupKey = 'table_' . $order->id_meja . '_' . $datePrefix . '_b' . $windowBlock;
                } else {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone ?? '');
                    $groupKey = 'dinein_nomeja_' . ($cleanPhone ?: strtolower(trim($order->customer_name ?: 'guest'))) . '_' . $datePrefix . '_b' . $windowBlock;
                }
            } else {
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone ?? '');
                if (!empty($cleanPhone)) {
                    $groupKey = 'takeaway_phone_' . $cleanPhone . '_' . $datePrefix . '_b' . $windowBlock;
                } else {
                    $groupKey = 'takeaway_name_' . strtolower(trim($order->customer_name ?: 'guest')) . '_' . $datePrefix . '_b' . $windowBlock;
                }
            }

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'group_key' => $groupKey,
                    'tipe_pesanan' => $order->tipe_pesanan,
                    'meja' => $order->meja,
                    'id_meja' => $order->id_meja,
                    'customer_name' => $order->customer_name,
                    'guest_phone' => $order->guest_phone,
                    'created_at' => $order->created_at,
                    'latest_created_at' => $order->created_at,
                    'orders' => collect([]),
                ];
            }

            $groups[$groupKey]['orders']->push($order);
            if ($order->created_at > $groups[$groupKey]['latest_created_at']) {
                $groups[$groupKey]['latest_created_at'] = $order->created_at;
            }
        }

        $result = collect([]);
        foreach ($groups as $g) {
            $ordersList = $g['orders']->sortBy('id')->values();
            $orderIds = $ordersList->pluck('id')->all();
            $primaryOrder = $ordersList->first();

            $totalItems = 0;
            $totalBill = 0;
            $totalDiscount = 0;
            $allPaid = true;
            $totalUangDiterima = 0;
            $totalUangKembalian = 0;

            foreach ($ordersList as $ord) {
                $ordTotal = (int) (($ord->pembayaran && (float)$ord->pembayaran->total_bayar > 0) 
                    ? $ord->pembayaran->total_bayar 
                    : ($ord->total - ($ord->discount_amount ?? 0)));
                $totalBill += $ordTotal;
                $totalDiscount += (float) ($ord->discount_amount ?? 0);

                $isThisPaid = ($ord->pembayaran && $ord->pembayaran->status === 'paid');
                if (!$isThisPaid) {
                    $allPaid = false;
                }

                if ($ord->pembayaran) {
                    $totalUangDiterima += (int) ($ord->pembayaran->uang_diterima ?? 0);
                    $totalUangKembalian += (int) ($ord->pembayaran->uang_kembalian ?? 0);
                }

                foreach ($ord->detail_pesanan as $detail) {
                    $totalItems += $detail->jumlah;
                }
            }

            $result->push((object) [
                'group_key' => $g['group_key'],
                'primary_order' => $primaryOrder,
                'orders' => $ordersList,
                'order_ids' => $orderIds,
                'order_ids_string' => implode(',', $orderIds),
                'order_ids_display' => implode(' & #', $orderIds),
                'order_ids_badge' => implode(', ', array_map(fn($id) => '#' . $id, $orderIds)),
                'is_grouped' => count($ordersList) > 1,
                'tipe_pesanan' => $g['tipe_pesanan'],
                'meja' => $g['meja'],
                'id_meja' => $g['id_meja'],
                'customer_name' => $g['customer_name'],
                'guest_phone' => $g['guest_phone'],
                'created_at' => $g['created_at'],
                'latest_created_at' => $g['latest_created_at'],
                'total_items' => $totalItems,
                'total_bill' => $totalBill,
                'total_discount' => $totalDiscount,
                'all_paid' => $allPaid,
                'total_uang_diterima' => $totalUangDiterima,
                'total_uang_kembalian' => $totalUangKembalian,
            ]);
        }

        return $result;
    }

    /**
     * Helper proaktif untuk sinkronisasi status Midtrans
     */
    public function checkAndUpdateMidtransStatus(Pesanan $pesanan): void
    {
        if (!$pesanan || !$pesanan->pembayaran || $pesanan->pembayaran->status === 'paid' || $pesanan->pembayaran->metode === 'cash') {
            return;
        }

        $serverKey = trim(Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey')));
        $rawIsProd = Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction'));
        $isProduction = filter_var($rawIsProd, FILTER_VALIDATE_BOOLEAN);

        if (empty($serverKey)) {
            return;
        }

        $candidates = [];
        if (!empty($pesanan->pembayaran->midtrans_order_id)) {
            $candidates[] = $pesanan->pembayaran->midtrans_order_id;
        }

        if (empty($candidates)) {
            return;
        }

        $trxStatus = null;
        $paymentType = null;

        try {
            \Midtrans\Config::$serverKey = $serverKey;
            \Midtrans\Config::$isProduction = $isProduction;

            foreach ($candidates as $candidateId) {
                try {
                    $statusResponse = \Midtrans\Transaction::status($candidateId);
                    if ($statusResponse && isset($statusResponse->transaction_status)) {
                        $trxStatus = $statusResponse->transaction_status;
                        $paymentType = $statusResponse->payment_type ?? null;
                        break;
                    }
                } catch (\Exception $e) {}
            }

            if ($trxStatus && in_array($trxStatus, ['settlement', 'capture'])) {
                $pembayaran = $pesanan->pembayaran;
                $pembayaran->status = 'paid';
                $normType = strtolower($paymentType ?? '');
                $pembayaran->metode = in_array($normType, ['qris', 'gopay', 'shopeepay']) ? 'qris' : ($normType ?: 'qris');
                $pembayaran->tanggal = now();
                $pembayaran->save();

                if ($pesanan->status === 'pending') {
                    $pesanan->status = 'processing';
                    $pesanan->save();
                }

                try {
                    broadcast(new \App\Events\PesananUpdated($pesanan));
                } catch (\Throwable $e) {}
            }
        } catch (\Exception $e) {}
    }

    /**
     * Memisahkan pesanan (Split Order)
     */
    public function splitOrder(Pesanan $pesananAsli, array $items): Pesanan
    {
        return DB::transaction(function () use ($pesananAsli, $items) {
            $pesananBaru = Pesanan::create([
                'id_konsumen' => $pesananAsli->id_konsumen,
                'id_meja' => $pesananAsli->id_meja,
                'id_kasir' => auth()->id() ?? $pesananAsli->id_kasir,
                'status' => $pesananAsli->status,
                'tipe_pesanan' => $pesananAsli->tipe_pesanan,
                'guest_name' => $pesananAsli->guest_name,
                'guest_phone' => $pesananAsli->guest_phone,
                'tanggal' => now(),
                'total' => 0,
                'total_hpp' => 0
            ]);

            $totalBaru = 0;
            $totalAsliSebelumSplit = $pesananAsli->total;
            $hppAsliSebelumSplit = (float) ($pesananAsli->total_hpp ?? 0);

            foreach ($items as $item) {
                $detail = DetailPesanan::where('id', $item['id_detail'])->where('id_pesanan', $pesananAsli->id)->first();
                if ($detail) {
                    if ($item['jumlah'] < $detail->jumlah) {
                        $sisaJumlah = $detail->jumlah - $item['jumlah'];
                        $hargaSatuan = $detail->subtotal / $detail->jumlah;
                        
                        $subtotalBaru = $hargaSatuan * $item['jumlah'];
                        $subtotalSisa = $hargaSatuan * $sisaJumlah;
                        
                        $detail->update([
                            'jumlah' => $sisaJumlah,
                            'subtotal' => $subtotalSisa
                        ]);

                        DetailPesanan::create([
                            'id_pesanan' => $pesananBaru->id,
                            'id_menu' => $detail->id_menu,
                            'jumlah' => $item['jumlah'],
                            'subtotal' => $subtotalBaru
                        ]);
                        $totalBaru += $subtotalBaru;
                    } else if ($item['jumlah'] >= $detail->jumlah) {
                        $totalBaru += $detail->subtotal;
                        $detail->update(['id_pesanan' => $pesananBaru->id]);
                    }
                }
            }

            // Distribusi HPP proporsional
            $hppBaru = 0;
            if ($totalAsliSebelumSplit > 0) {
                $rasio = $totalBaru / $totalAsliSebelumSplit;
                $hppBaru = round($hppAsliSebelumSplit * $rasio, 2);
            }

            $pesananBaru->update([
                'total' => $totalBaru,
                'total_hpp' => $hppBaru
            ]);

            Pembayaran::create([
                'id_pesanan' => $pesananBaru->id,
                'status' => 'unpaid',
                'total_bayar' => $totalBaru
            ]);

            $totalAsli = DetailPesanan::where('id_pesanan', $pesananAsli->id)->sum('subtotal');
            $hppAsli = max(0, $hppAsliSebelumSplit - $hppBaru);

            $pesananAsli->update([
                'total' => $totalAsli,
                'total_hpp' => $hppAsli,
                'promo_id' => null,
                'discount_amount' => 0
            ]);

            $pesananAsli->pembayaran()->update([
                'total_bayar' => $totalAsli
            ]);

            if ($totalAsli == 0) {
                $pesananAsli->delete();
            }

            return $pesananBaru;
        });
    }

    /**
     * Mengambil kalkulasi data jumlah pesanan aktif dan hash pembaruan
     */
    public function getActiveOrdersCountData(): array
    {
        $activeOrdersQuery = Pesanan::whereNotIn('status', ['cancelled', 'void'])
            ->where(function ($statusQ) {
                // 1. Pesanan yang sedang diproses di dapur (pending / processing)
                $statusQ->whereIn('status', ['pending', 'processing'])
                    // 2. ATAU pesanan yang sudah selesai dimasak (completed) tetapi BELUM LUNAS
                    ->orWhere(function ($compUnpaidQ) {
                        $compUnpaidQ->where('status', 'completed')
                            ->where(function ($pSub) {
                                $pSub->whereDoesntHave('pembayaran')
                                     ->orWhereHas('pembayaran', function ($p) {
                                         $p->where('status', '!=', 'paid');
                                     });
                            });
                    });
            })
            ->where(function ($sub) {
                // 1. Dibuat langsung oleh Kasir di POS
                $sub->whereNotNull('id_kasir')
                    // 2. Pesanan Dine-In (Makan di Tempat) dengan Meja Aktif (Open Bill)
                    ->orWhere(function ($dineInQ) {
                        $dineInQ->where('tipe_pesanan', 'dine_in')
                                ->whereNotNull('id_meja');
                    })
                    // 3. Sudah Lunas (QRIS, Transfer, atau Tunai Kasir)
                    ->orWhereHas('pembayaran', function ($p) {
                        $p->where('status', 'paid')
                          // 4. Tunai di Meja (Cash)
                          ->orWhere(function ($cashQ) {
                              $cashQ->where('status', 'unpaid')
                                    ->where('metode', 'cash');
                          });
                    });
            });

        $count = (clone $activeOrdersQuery)->count();
        $latestId = (clone $activeOrdersQuery)->max('id') ?? 0;

        $activeHash = (clone $activeOrdersQuery)
            ->with('pembayaran')
            ->get()
            ->map(function ($o) {
                $payStatus = $o->pembayaran?->status ?? 'unpaid';
                $payTime = $o->pembayaran?->updated_at?->timestamp ?? 0;
                $orderTime = $o->updated_at?->timestamp ?? 0;
                return "{$o->id}-{$o->status}-{$payStatus}-{$payTime}-{$orderTime}";
            })
            ->join('|');

        $completedMax = Pesanan::where('status', 'completed')->whereHas('pembayaran', fn($p) => $p->where('status', 'paid'))->max('updated_at') ?? '';
        $completedCount = Pesanan::where('status', 'completed')->whereHas('pembayaran', fn($p) => $p->where('status', 'paid'))->whereDate('created_at', today())->count();
        $activeHash .= "|comp:{$completedCount}-{$completedMax}";

        return [
            'count' => $count,
            'latest_id' => $latestId,
            'completed_count' => $completedCount,
            'hash' => md5($activeHash),
        ];
    }

    /**
     * Membatalkan pesanan dari kasir (Void).
     */
    public function voidOrder(int $idPesanan, string $password, ?string $alasan, $user)
    {
        DB::beginTransaction();
        try {
            $pesanan = Pesanan::findOrFail($idPesanan);

            if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                throw new \Exception('Password yang dimasukkan salah.');
            }

            if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid') {
                throw new \Exception('Pesanan sudah dibayar lunas dan tidak dapat dihapus/divoid.');
            }

            if ($pesanan->status === 'completed') {
                throw new \Exception('Pesanan sudah selesai dan tidak dapat divoid.');
            }

            if ($pesanan->status === 'cancelled') {
                throw new \Exception('Pesanan sudah dibatalkan sebelumnya.');
            }

            DB::table('void_logs')->insert([
                'pesanan_id' => $pesanan->id,
                'kasir_id' => $user->id,
                'alasan' => $alasan ?? 'Batal',
                'total_nilai' => $pesanan->total,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $pesanan->cancelOrder();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
