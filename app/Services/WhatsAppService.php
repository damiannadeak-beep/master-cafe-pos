<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim Pesan WhatsApp via Provider API (Default: Fonnte API / Custom Gateway)
     *
     * @param string $targetPhone Nomor WhatsApp Tujuan (08xxx / 628xxx)
     * @param string $message Isi Pesan WhatsApp
     * @return bool
     */
    public static function sendMessage(string $targetPhone, string $message): bool
    {
        // 1. Format Nomor Telepon ke standar 628xxx
        $formattedPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (str_starts_with($formattedPhone, '0')) {
            $formattedPhone = '62' . substr($formattedPhone, 1);
        }

        if (empty($formattedPhone)) {
            Log::warning('[WhatsAppService] Nomor WA tujuan tidak valid / kosong.');
            return false;
        }

        // 2. Ambil Setting API Key & URL dari Database Setting / .env
        $apiKey = Setting::getVal('wa_gateway_api_key', config('services.whatsapp.api_key', env('WA_GATEWAY_API_KEY')));
        $providerUrl = Setting::getVal('wa_gateway_url', config('services.whatsapp.url', env('WA_GATEWAY_URL', 'https://api.fonnte.com/send')));

        // 3. Jika API Key belum dikonfigurasi, simpan pesan di Laravel Log (Simulasi Log)
        if (empty($apiKey)) {
            Log::info("[WhatsAppService] (SIMULASI - WA API Key belum diset) Mengirim ke {$formattedPhone}:\n" . $message);
            return true;
        }

        try {
            // Pengiriman via HTTP Request ke Provider Fonnte / Standard WA Gateway
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post($providerUrl, [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("[WhatsAppService] Berhasil mengirim pesan WA ke {$formattedPhone}");
                return true;
            } else {
                Log::warning("[WhatsAppService] Gagal kirim WA ke {$formattedPhone}. Respon: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("[WhatsAppService] Exception saat mengirim pesan WA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format Pesan Notifikasi Pesanan Takeaway Siap Diambil di Kasir
     *
     * @param \App\Models\Pesanan $pesanan
     * @return string
     */
    public static function formatTakeawayReadyMessage($pesanan): string
    {
        $nama = $pesanan->customer_name ?: 'Kak';
        $orderId = $pesanan->id;

        $itemList = "";
        if ($pesanan->relationLoaded('detail_pesanan')) {
            foreach ($pesanan->detail_pesanan as $detail) {
                $menuNama = $detail->menu->nama_menu ?? 'Item';
                $itemList .= "• {$detail->jumlah}x {$menuNama}\n";
            }
        }

        $storeName = Setting::getVal('store_name', 'Master Cafe');

        $msg = "☕ *{$storeName}* - Pesanan Siap Diambil! 🛍️\n\n";
        $msg .= "Halo Kak *{$nama}*,\n";
        $msg .= "Pesanan Bawa Pulang (Takeaway) Anda (*Order #{$orderId}*) telah selesai dimasak & dikemas rapi! 🎁\n\n";
        $msg .= "📍 *Silakan ambil pesanan Anda di counter kasir {$storeName}.*\n\n";
        if (!empty($itemList)) {
            $msg .= "*Rincian Pesanan:*\n" . $itemList . "\n";
        }
        $msg .= "Terima kasih telah memesan di {$storeName}! Selamat menikmati! 🙏";

        return $msg;
    }
}
