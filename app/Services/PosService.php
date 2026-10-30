<?php

namespace App\Services;

use App\Models\{Pesanan, DetailPesanan, Pembayaran, Setting};
use App\Enums\{OrderStatus, PaymentStatus, PaymentMethod, OrderType};
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
            ->whereNotIn('status', [OrderStatus::CANCELLED->value, OrderStatus::VOID->value])
            ->where(function ($statusQ) {
                // 1. Pesanan yang sedang diproses di dapur (pending / processing)
                $statusQ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PROCESSING->value])
                    // 2. ATAU pesanan yang sudah selesai dimasak (completed) tetapi BELUM LUNAS
                    ->orWhere(function ($compUnpaidQ) {
                        $compUnpaidQ->where('status', OrderStatus::COMPLETED->value)
                            ->where(function ($pSub) {
                                $pSub->whereDoesntHave('pembayaran')
                                     ->orWhereHas('pembayaran', function ($p) {
                                         $p->where('status', '!=', PaymentStatus::PAID->value);
                                     });
                            });
                    });
            })
            ->where(function ($sub) {
                // 1. Dibuat langsung oleh Kasir di POS
                $sub->whereNotNull('id_kasir')
                    // 2. Sudah dikonfirmasi pembayarannya (Lunas via QRIS/VA atau memilih opsi Bayar Tunai di Kasir)
                    ->orWhereHas('pembayaran', function ($p) {
                        $p->where('status', PaymentStatus::PAID->value)
                          // 3. Tunai di Meja (Cash): Konsumen sudah selesai memilih metode bayar tunai di kasir
                          ->orWhere(function ($cashQ) {
                              $cashQ->where('status', PaymentStatus::UNPAID->value)
                                    ->where('metode', PaymentMethod::CASH->value);
                          });
                    });
            });
    }

    /**
     * Mengambil query dasar riwayat pesanan selesai
     */
    public function getCompletedOrdersQuery(int $limit = 50, bool $todayOnly = false)
    {
        $query = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen', 'kasir'])
            ->where('status', OrderStatus::COMPLETED->value)
            ->whereHas('pembayaran', function ($p) {
                $p->where('status', PaymentStatus::PAID->value);
            });

        if ($todayOnly) {
            $query->where(function ($q) {
                $q->whereDate('updated_at', today())
                  ->orWhereDate('created_at', today());
            });
        }

        return $query->orderBy('updated_at', 'desc')->take($limit);
    }

    /**
     * Mengambil query riwayat pesanan dibatalkan / void
     */
    public function getVoidedOrdersQuery(int $limit = 50, bool $todayOnly = false)
    {
        $query = Pesanan::withTrashed()
            ->where(function ($q) {
                $q->whereNotNull('deleted_at')
                  ->orWhereIn('status', [OrderStatus::CANCELLED->value, OrderStatus::VOID->value]);
            })
            ->with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen', 'kasir', 'voidLog.kasir']);

        if ($todayOnly) {
            $query->where(function ($q) {
                $q->whereDate('deleted_at', today())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('deleted_at')
                         ->where(function ($q3) {
                             $q3->whereDate('updated_at', today())
                                ->orWhereDate('created_at', today());
                         });
                  });
            });
        }

        return $query->orderByRaw('COALESCE(deleted_at, updated_at) DESC')->take($limit);
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
                $custName = strtolower(trim($order->customer_name ?? ''));
                if (!empty($cleanPhone)) {
                    $groupKey = 'takeaway_phone_' . $cleanPhone;
                } elseif (!empty($custName) && !in_array($custName, ['tamu', 'guest', 'pelanggan'])) {
                    $groupKey = 'takeaway_name_' . $custName;
                } else {
                    $groupKey = 'takeaway_order_' . $order->id;
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
     * Mengelompokkan riwayat pesanan selesai.
     * Hanya pesanan yang dibayar bersamaan dalam satu transaksi pembayaran (batch payment)
     * di meja yang sama (atau takeaway yang sama) yang digabungkan.
     * Pesanan baru di sesi berikutnya akan selalu terpisah menjadi kartu mandiri.
     */
    public function groupCompletedOrders(Collection $orders): Collection
    {
        $groups = [];

        foreach ($orders as $order) {
            $rawPay = $order->pembayaran?->tanggal;
            $payCarbon = $rawPay ? \Carbon\Carbon::parse($rawPay) : null;
            $matchedKey = null;

            if ($order->tipe_pesanan === 'dine_in' && $order->id_meja && $payCarbon) {
                // Cari group meja yang sama yang dibayar bersamaan dalam toleransi <= 5 detik
                foreach ($groups as $k => $gData) {
                    if (($gData['tipe_pesanan'] ?? null) === 'dine_in' && ($gData['id_meja'] ?? null) === $order->id_meja && !empty($gData['pay_time'])) {
                        $diffSec = abs($gData['pay_time']->diffInSeconds($payCarbon));
                        if ($diffSec <= 5) {
                            $matchedKey = $k;
                            break;
                        }
                    }
                }
                if (!$matchedKey) {
                    $matchedKey = 'table_' . $order->id_meja . '_pay_' . $order->id;
                }
            } elseif ($order->tipe_pesanan === 'takeaway' && !empty($order->guest_phone) && $payCarbon) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone);
                foreach ($groups as $k => $gData) {
                    if (($gData['tipe_pesanan'] ?? null) === 'takeaway' && !empty($gData['clean_phone']) && $gData['clean_phone'] === $cleanPhone && !empty($gData['pay_time'])) {
                        $diffSec = abs($gData['pay_time']->diffInSeconds($payCarbon));
                        if ($diffSec <= 5) {
                            $matchedKey = $k;
                            break;
                        }
                    }
                }
                if (!$matchedKey) {
                    $matchedKey = 'takeaway_' . $cleanPhone . '_pay_' . $order->id;
                }
            } else {
                $matchedKey = 'order_' . $order->id;
            }

            if (!isset($groups[$matchedKey])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone ?? '');
                $groups[$matchedKey] = [
                    'group_key' => $matchedKey,
                    'tipe_pesanan' => $order->tipe_pesanan,
                    'meja' => $order->meja,
                    'id_meja' => $order->id_meja,
                    'clean_phone' => $cleanPhone,
                    'pay_time' => $payCarbon,
                    'customer_name' => $order->customer_name,
                    'guest_phone' => $order->guest_phone,
                    'created_at' => $order->created_at,
                    'latest_created_at' => $order->created_at,
                    'orders' => collect([]),
                ];
            }

            $groups[$matchedKey]['orders']->push($order);
            if ($order->created_at > $groups[$matchedKey]['latest_created_at']) {
                $groups[$matchedKey]['latest_created_at'] = $order->created_at;
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
     * Menggabungkan dua pesanan aktif (Merge Bill / Merge Orders)
     * Seluruh item dari pesanan sumber ($sourcePesanan) dilebur ke pesanan tujuan ($targetPesanan)
     */
    public function mergeOrders(Pesanan $targetPesanan, Pesanan $sourcePesanan): Pesanan
    {
        return DB::transaction(function () use ($targetPesanan, $sourcePesanan) {
            if ($targetPesanan->id === $sourcePesanan->id) {
                throw new \Exception('Tidak bisa menggabungkan pesanan dengan dirinya sendiri.');
            }

            if (($targetPesanan->pembayaran && $targetPesanan->pembayaran->status === 'paid') ||
                ($sourcePesanan->pembayaran && $sourcePesanan->pembayaran->status === 'paid')) {
                throw new \Exception('Salah satu pesanan sudah berstatus lunas, tidak dapat digabungkan.');
            }

            if (in_array($targetPesanan->status, ['cancelled', 'void']) ||
                in_array($sourcePesanan->status, ['cancelled', 'void'])) {
                throw new \Exception('Pesanan yang sudah dibatalkan tidak dapat digabungkan.');
            }

            // 1. Pindahkan seluruh detail_pesanan dari source ke target
            DetailPesanan::where('id_pesanan', $sourcePesanan->id)->update([
                'id_pesanan' => $targetPesanan->id
            ]);

            // 2. Hitung ulang total penjualan dan HPP pada pesanan target
            $newTotal = (float) DetailPesanan::where('id_pesanan', $targetPesanan->id)->sum('subtotal');
            $newHpp = (float) ($targetPesanan->total_hpp ?? 0) + (float) ($sourcePesanan->total_hpp ?? 0);

            // Sesuaikan status jika salah satu pesanan masih pending atau processing
            $newStatus = $targetPesanan->status;
            if ($sourcePesanan->status === 'pending' || $targetPesanan->status === 'pending') {
                $newStatus = 'pending';
            } elseif ($sourcePesanan->status === 'processing' || $targetPesanan->status === 'processing') {
                $newStatus = 'processing';
            }

            $targetPesanan->update([
                'total' => $newTotal,
                'total_hpp' => $newHpp,
                'promo_id' => null,
                'discount_amount' => 0,
                'status' => $newStatus
            ]);

            // 3. Perbarui catatan pembayaran pada pesanan target
            if ($targetPesanan->pembayaran) {
                $targetPesanan->pembayaran->update([
                    'total_bayar' => $newTotal
                ]);
            } else {
                Pembayaran::create([
                    'id_pesanan' => $targetPesanan->id,
                    'status' => 'unpaid',
                    'total_bayar' => $newTotal
                ]);
            }

            // 4. Hapus pembayaran pada source jika ada
            if ($sourcePesanan->pembayaran) {
                $sourcePesanan->pembayaran->delete();
            }

            // 5. Hapus pesanan sumber (force delete agar tidak menjadi kartu kosong di riwayat void)
            $sourcePesanan->forceDelete();

            return $targetPesanan->fresh(['detail_pesanan.menu', 'pembayaran', 'meja']);
        });
    }

    /**
     * Mengambil kalkulasi data jumlah pesanan aktif dan hash pembaruan
     */
    public function getActiveOrdersCountData(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('active_orders_count_data', 2, function () {
            $activeOrdersQuery = Pesanan::whereNotIn('status', ['cancelled', 'void'])
                ->where(function ($statusQ) {
                    $statusQ->whereIn('status', ['pending', 'processing'])
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
                    $sub->whereNotNull('id_kasir')
                        ->orWhereHas('pembayaran', function ($p) {
                            $p->where('status', 'paid')
                              ->orWhere(function ($cashQ) {
                                  $cashQ->where('status', 'unpaid')
                                        ->where('metode', 'cash');
                              });
                        });
                });

            $count = (clone $activeOrdersQuery)->count();
            $latestId = (clone $activeOrdersQuery)->max('id') ?? 0;

            $activeOrdersList = (clone $activeOrdersQuery)
                ->select(['id', 'status', 'updated_at'])
                ->with(['pembayaran:id,id_pesanan,status,updated_at'])
                ->get();

            $activeHash = $activeOrdersList->map(function ($o) {
                $payStatus = $o->pembayaran?->status ?? 'unpaid';
                $payTime = $o->pembayaran?->updated_at?->timestamp ?? 0;
                $orderTime = $o->updated_at?->timestamp ?? 0;
                return "{$o->id}-{$o->status}-{$payStatus}-{$payTime}-{$orderTime}";
            })->join('|');

            $completedMax = Pesanan::where('status', 'completed')->whereHas('pembayaran', fn($p) => $p->where('status', 'paid'))->max('updated_at') ?? '';
            $completedCount = Pesanan::where('status', 'completed')
                ->whereHas('pembayaran', fn($p) => $p->where('status', 'paid'))
                ->where(function ($q) {
                    $q->whereDate('updated_at', today())
                      ->orWhereDate('created_at', today());
                })->count();

            $voidedMax = Pesanan::withTrashed()->where(fn($q) => $q->whereNotNull('deleted_at')->orWhereIn('status', ['cancelled', 'void']))->max('updated_at') ?? '';
            $voidedCount = Pesanan::withTrashed()
                ->where(function ($q) {
                    $q->whereNotNull('deleted_at')
                      ->orWhereIn('status', ['cancelled', 'void']);
                })
                ->where(function ($q) {
                    $q->whereDate('deleted_at', today())
                      ->orWhere(function ($q2) {
                          $q2->whereNull('deleted_at')
                             ->where(function ($q3) {
                                 $q3->whereDate('updated_at', today())
                                    ->orWhereDate('created_at', today());
                             });
                      });
                })->count();

            $activeHash .= "|comp:{$completedCount}-{$completedMax}|void:{$voidedCount}-{$voidedMax}";

            return [
                'count' => $count,
                'latest_id' => $latestId,
                'completed_count' => $completedCount,
                'voided_count' => $voidedCount,
                'hash' => md5($activeHash),
            ];
        });
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

    /**
     * Update status pesanan dari kasir (processing, completed, dll)
     */
    public function updateOrderStatus(array $ids, string $targetStatus, ?int $kasirId = null): array
    {
        $orders = Pesanan::with(['konsumen', 'meja', 'detail_pesanan.menu'])->whereIn('id', $ids)->get();
        if ($orders->isEmpty()) {
            throw new \Exception('Pesanan tidak ditemukan.');
        }

        foreach ($orders as $pesanan) {
            $pesanan->update([
                'status' => $targetStatus,
                'id_kasir' => $kasirId ?: auth()->id()
            ]);

            if ($targetStatus === OrderStatus::COMPLETED->value) {
                $pesanan->detail_pesanan()->update(['is_served' => true]);
            }

            if ($targetStatus === OrderStatus::COMPLETED->value && $pesanan->id_meja) {
                $hasRemaining = Pesanan::where('id_meja', $pesanan->id_meja)
                    ->whereNotIn('status', [OrderStatus::CANCELLED->value, OrderStatus::VOID->value])
                    ->where(function ($q) {
                        $q->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PROCESSING->value])
                          ->orWhere(function ($sub) {
                              $sub->where('status', OrderStatus::COMPLETED->value)
                                  ->where(function ($pSub) {
                                      $pSub->whereDoesntHave('pembayaran')
                                           ->orWhereHas('pembayaran', function ($p) {
                                               $p->where('status', '!=', PaymentStatus::PAID->value);
                                           });
                                  });
                          });
                    })
                    ->exists();

                if (!$hasRemaining && $pesanan->meja && !$pesanan->meja->is_available) {
                    $pesanan->meja->update(['is_available' => true]);
                }
            }

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
                if ($pesanan->id_meja && $pesanan->meja) {
                    broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[PosService] Gagal broadcast status: ' . $e->getMessage());
            }

            if ($pesanan->id_konsumen && $pesanan->konsumen) {
                try {
                    $statusText = $pesanan->status === OrderStatus::COMPLETED->value ? 'Selesai' : 'Diproses';
                    $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                        'Pesanan ' . $statusText,
                        'Pesanan Anda (Order #' . $pesanan->id . ') saat ini ' . strtolower($statusText) . '.',
                        '/konsumen/profil'
                    ));
                } catch (\Throwable $notifyErr) {
                    \Illuminate\Support\Facades\Log::warning('WebPush gagal dikirim: ' . $notifyErr->getMessage());
                }
            }

            if ($pesanan->status === OrderStatus::COMPLETED->value && $pesanan->tipe_pesanan === OrderType::TAKEAWAY->value && !empty($pesanan->guest_phone)) {
                try {
                    $pesanan->loadMissing(['detail_pesanan.menu']);
                    $waMessage = \App\Services\WhatsAppService::formatTakeawayReadyMessage($pesanan);
                    \App\Services\WhatsAppService::sendMessage($pesanan->guest_phone, $waMessage);
                } catch (\Throwable $waErr) {
                    \Illuminate\Support\Facades\Log::warning('[PosService] Gagal kirim WA: ' . $waErr->getMessage());
                }
            }
        }

        \Illuminate\Support\Facades\Cache::forget('active_orders_count_data');

        return [
            'status' => $targetStatus,
            'order_ids' => $ids
        ];
    }

    /**
     * Toggle status centang per-item menu (is_served) oleh waitress
     */
    public function toggleItemServed(int $idDetail): array
    {
        $item = DetailPesanan::with('pesanan.detail_pesanan')->findOrFail($idDetail);
        $item->is_served = !$item->is_served;
        $item->save();

        $pesanan = $item->pesanan;
        $allServed = false;
        $servedCount = 0;
        $totalCount = 0;
        $statusChanged = false;

        if ($pesanan) {
            $pesanan->load('detail_pesanan');
            $totalCount = $pesanan->detail_pesanan->count();
            $servedCount = $pesanan->detail_pesanan->where('is_served', true)->count();
            $allServed = ($servedCount === $totalCount && $totalCount > 0);

            if ($allServed && $pesanan->status !== OrderStatus::COMPLETED->value) {
                $pesanan->status = OrderStatus::COMPLETED->value;
                $pesanan->save();
                $statusChanged = true;
            } elseif (!$allServed && $pesanan->status === OrderStatus::COMPLETED->value) {
                $pesanan->status = OrderStatus::PROCESSING->value;
                $pesanan->save();
                $statusChanged = true;
            }

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
            } catch (\Throwable $e) {}
        }

        \Illuminate\Support\Facades\Cache::forget('active_orders_count_data');

        return [
            'success' => true,
            'id' => $item->id,
            'is_served' => (bool)$item->is_served,
            'served_count' => $servedCount,
            'total_count' => $totalCount,
            'all_served' => $allServed,
            'order_id' => $pesanan ? $pesanan->id : null,
            'order_status' => $pesanan ? $pesanan->status : null,
            'status_changed' => $statusChanged,
            'message' => $item->is_served ? 'Menu ditandai siap / disajikan.' : 'Menu ditandai belum siap.'
        ];
    }
}
