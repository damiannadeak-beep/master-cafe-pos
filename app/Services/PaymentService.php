<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Confirm payment for an order and send notification.
     *
     * @param int $id_pesanan
     * @param string $metode
     * @param string|null $email_pelanggan
     * @param int|null $kasir_id
     * @return Pesanan
     * @throws \Exception
     */
    public function processPayment($id_pesanan, $metode, $email_pelanggan = null, $kasir_id = null, $nominalTunai = null, $isUangPas = false)
    {
        $pesanan = Pesanan::with('konsumen')->findOrFail($id_pesanan);
        $totalBayar = $pesanan->total - ($pesanan->discount_amount ?? 0);
        
        // Auto-create pembayaran record if it doesn't exist (fail-safe)
        $pembayaran = Pembayaran::firstOrCreate(
            ['id_pesanan' => $id_pesanan],
            [
                'status' => 'unpaid',
                'total_bayar' => $totalBayar,
            ]
        );

        if ($pembayaran->status === 'paid') {
            throw new \Exception('Pesanan ini sudah dibayar.');
        }

        $uangDiterima = $pembayaran->uang_diterima;
        $uangKembalian = $pembayaran->uang_kembalian ?? 0;
        $catatanKembalian = $pembayaran->catatan_kembalian;

        if ($metode === 'cash') {
            if ($isUangPas) {
                $uangDiterima = $totalBayar;
                $uangKembalian = 0;
                $catatanKembalian = 'Uang Pas (Tanpa Kembalian)';
            } elseif (!empty($nominalTunai) && is_numeric($nominalTunai)) {
                $uangDiterima = (float) $nominalTunai;
                $uangKembalian = max(0, $uangDiterima - $totalBayar);
                if ($uangKembalian > 0) {
                    $catatanKembalian = 'Uang Rp ' . number_format($uangDiterima, 0, ',', '.') . ' (Kembalian Rp ' . number_format($uangKembalian, 0, ',', '.') . ')';
                } else {
                    $catatanKembalian = 'Uang Pas (Tanpa Kembalian)';
                }
            }
        }

        $pembayaran->update([
            'status' => 'paid',
            'metode' => $metode,
            'tanggal' => now(),
            'total_bayar' => $totalBayar,
            'uang_diterima' => $uangDiterima,
            'uang_kembalian' => $uangKembalian,
            'catatan_kembalian' => $catatanKembalian,
        ]);

        if ($kasir_id) {
            $pesanan->update(['id_kasir' => $kasir_id]);
        }

        // Otomatis bebaskan meja jika sudah lunas dan tidak ada pesanan aktif lain di meja tersebut
        if ($pesanan->id_meja && $pesanan->tipe_pesanan === 'dine_in') {
            $hasOtherActiveOrders = Pesanan::where('id_meja', $pesanan->id_meja)
                ->where('id', '!=', $pesanan->id)
                ->where(function ($q) {
                    $q->whereIn('status', ['pending', 'processing'])
                      ->orWhere(function ($sub) {
                          $sub->where('status', 'completed')
                              ->where(function ($pSub) {
                                  $pSub->whereDoesntHave('pembayaran')
                                       ->orWhereHas('pembayaran', function ($p) {
                                           $p->where('status', '!=', 'paid');
                                       });
                              });
                      });
                })->exists();

            if (!$hasOtherActiveOrders) {
                $meja = \App\Models\Meja::find($pesanan->id_meja);
                if ($meja && !$meja->is_available) {
                    $meja->update(['is_available' => true]);
                    try {
                        broadcast(new \App\Events\MejaStatusUpdated($meja));
                    } catch (\Throwable $e) {
                        // ignore broadcast error if offline
                    }
                }
            }
        }

        $this->notifyCustomer($pesanan);
        $this->sendEmailReceipt($pesanan, $email_pelanggan);

        return $pesanan;
    }

    /**
     * Send Web Push Notification to customer.
     */
    private function notifyCustomer(Pesanan $pesanan)
    {
        if ($pesanan->id_konsumen) {
            $pesanan->loadMissing('konsumen');
            if ($pesanan->konsumen) {
                try {
                    $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                        'Pembayaran Diterima',
                        'Pembayaran untuk Order #' . $pesanan->id . ' telah dikonfirmasi oleh Kasir.',
                        '/konsumen/profil'
                    ));
                } catch (\Exception $e) {
                    Log::warning("Gagal mengirim push notifikasi pembayaran: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send email receipt to customer.
     */
    private function sendEmailReceipt(Pesanan $pesanan, $targetEmail = null)
    {
        if (!$targetEmail && $pesanan->id_konsumen) {
            $pesanan->loadMissing('konsumen');
            if ($pesanan->konsumen && $pesanan->konsumen->email) {
                $targetEmail = $pesanan->konsumen->email;
            }
        }

        if ($targetEmail) {
            try {
                Mail::to($targetEmail)->send(new ReceiptMail($pesanan));
            } catch (\Exception $mailEx) {
                Log::error("Gagal mengirim e-receipt: " . $mailEx->getMessage());
            }
        }
    }

    /**
     * Generate Midtrans Snap Token untuk transaksi pesanan kasir / online.
     *
     * @param Pesanan $pesanan
     * @return array
     * @throws \Exception
     */
    public function createSnapToken(Pesanan $pesanan): array
    {
        $serverKey = trim(\App\Models\Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey')));
        $clientKey = trim(\App\Models\Setting::getVal('midtrans_client_key', config('services.midtrans.clientKey')));
        $rawIsProd = \App\Models\Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction'));
        $isProduction = filter_var($rawIsProd, FILTER_VALIDATE_BOOLEAN);

        if (empty($serverKey)) {
            throw new \Exception('Midtrans Server Key belum diatur di sistem.');
        }

        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$isProduction = $isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $pesanan->loadMissing(['detail_pesanan.menu', 'pembayaran', 'konsumen']);
        $totalBayar = (int) round(($pesanan->pembayaran && (float)$pesanan->pembayaran->total_bayar > 0)
            ? $pesanan->pembayaran->total_bayar
            : ($pesanan->total - ($pesanan->discount_amount ?? 0)));

        if ($totalBayar <= 0) {
            throw new \Exception('Total tagihan pesanan harus lebih besar dari Rp 0.');
        }

        $midtransOrderId = 'POS-' . $pesanan->id . '-' . time();

        $itemDetails = [];
        foreach ($pesanan->detail_pesanan as $detail) {
            $itemDetails[] = [
                'id' => (string) $detail->id_menu,
                'price' => (int) round($detail->subtotal / max(1, $detail->jumlah)),
                'quantity' => (int) $detail->jumlah,
                'name' => mb_substr($detail->menu->nama_menu ?? 'Item Menu', 0, 50),
            ];
        }

        if (($pesanan->discount_amount ?? 0) > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) $pesanan->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon Promo',
            ];
        }

        $customerName = $pesanan->guest_name ?: ($pesanan->konsumen?->name ?? 'Pelanggan POS Kasir');

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $totalBayar,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $pesanan->konsumen?->email ?? 'kasir@mastercafe.local',
                'phone' => $pesanan->guest_phone ?? '08123456789',
            ],
        ];

        if (!empty($itemDetails)) {
            $params['item_details'] = $itemDetails;
        }

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan ke record pembayaran
        $pembayaran = Pembayaran::firstOrCreate(
            ['id_pesanan' => $pesanan->id],
            ['status' => 'unpaid', 'total_bayar' => $totalBayar]
        );
        $pembayaran->update([
            'snap_token' => $snapToken,
            'midtrans_order_id' => $midtransOrderId,
            'total_bayar' => $totalBayar,
        ]);

        return [
            'snap_token' => $snapToken,
            'client_key' => $clientKey,
            'is_production' => $isProduction,
            'midtrans_order_id' => $midtransOrderId,
            'total_bayar' => $totalBayar,
        ];
    }
}
