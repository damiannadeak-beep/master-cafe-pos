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
        $pembayaran = Pembayaran::where('id_pesanan', $id_pesanan)->first();

        if (!$pembayaran) {
            throw new \Exception('Data pembayaran tidak ditemukan.');
        }

        if ($pembayaran->status === 'paid') {
            throw new \Exception('Pesanan ini sudah dibayar.');
        }

        $pembayaran->update([
            'status' => 'paid',
            'metode' => $metode,
            'tanggal' => now(),
        ]);

        if ($kasir_id) {
            $pesanan->update(['id_kasir' => $kasir_id]);
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
