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
    public function processPayment($id_pesanan, $metode, $email_pelanggan = null, $kasir_id = null)
    {
        $pesanan = Pesanan::with('konsumen')->findOrFail($id_pesanan);
        
        // Auto-create pembayaran record if it doesn't exist (fail-safe)
        $pembayaran = Pembayaran::firstOrCreate(
            ['id_pesanan' => $id_pesanan],
            [
                'status' => 'unpaid',
                'total_bayar' => $pesanan->total - ($pesanan->discount_amount ?? 0),
            ]
        );

        if ($pembayaran->status === 'paid') {
            throw new \Exception('Pesanan ini sudah dibayar.');
        }

        $pembayaran->update([
            'status' => 'paid',
            'metode' => $metode,
            'tanggal' => now(),
            'total_bayar' => $pesanan->total - ($pesanan->discount_amount ?? 0),
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

    /**
     * Send email receipt to customer.
     */
    private function sendEmailReceipt(Pesanan $pesanan, $targetEmail = null)
    {
        if (!$targetEmail && $pesanan->konsumen && $pesanan->konsumen->email) {
            $targetEmail = $pesanan->konsumen->email;
        }

        if ($targetEmail) {
            try {
                Mail::to($targetEmail)->send(new ReceiptMail($pesanan));
            } catch (\Exception $mailEx) {
                Log::error("Gagal mengirim e-receipt: " . $mailEx->getMessage());
            }
        }
    }
}
