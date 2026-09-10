<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{Pesanan, Pembayaran, Setting};
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Ambil pengaturan Midtrans dari database (atau fallback ke config jika belum diatur)
        \Midtrans\Config::$serverKey = Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey'));
        \Midtrans\Config::$isProduction = Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction')) == '1';
        \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function checkout(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'pembayaran'])->findOrFail($id_pesanan);
        $pembayaran = $pesanan->pembayaran;

        // Validasi kepemilikan pesanan (User login atau Guest dengan Token yang Cocok)
        $token = $request->query('token') ?? session('order_token');
        $isAuthorized = false;

        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            $isAuthorized = true;
        } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            $isAuthorized = true;
        } elseif (!$pesanan->id_konsumen && empty($pesanan->order_token) && session('active_order_id') == $pesanan->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        // Cegah generate ulang jika sudah lunas
        if ($pembayaran && $pembayaran->status === 'paid') {
            if ($pesanan->order_token) {
                return redirect('/tracking/' . $pesanan->order_token)->with('info', 'Pesanan ini sudah lunas.');
            }
            return redirect()->back()->with('error', 'Pesanan ini sudah lunas.');
        }

        // Generate Midtrans Snap Token jika Server Key valid
        $clientKey = Setting::getVal('midtrans_client_key', config('services.midtrans.clientKey'));
        $serverKey = Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey'));
        
        $isProduction = Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction')) == '1';

        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$isProduction = $isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $snapToken = $pembayaran->snap_token;

        if (empty($snapToken) && !empty($serverKey) && !str_contains($serverKey, 'xxxxxx')) {
            try {
                $itemDetails = [];
                foreach ($pesanan->detail_pesanan as $detail) {
                    $itemDetails[] = [
                        'id' => (string) $detail->id_menu,
                        'price' => (int) round($detail->subtotal / max(1, $detail->jumlah)),
                        'quantity' => (int) $detail->jumlah,
                        'name' => mb_substr($detail->menu->nama_menu ?? 'Item Menu', 0, 50),
                    ];
                }

                if ($pesanan->discount_amount > 0) {
                    $itemDetails[] = [
                        'id' => 'DISCOUNT',
                        'price' => -(int) $pesanan->discount_amount,
                        'quantity' => 1,
                        'name' => 'Diskon Promo',
                    ];
                }

                $customerName = $pesanan->guest_name ?: ($pesanan->konsumen?->name ?? 'Tamu Master Cafe');
                $customerEmail = $pesanan->konsumen?->email ?? 'guest@mastercafe.local';

                $midtransParams = [
                    'transaction_details' => [
                        'order_id' => 'ORDER-' . $pesanan->id . '-' . time(),
                        'gross_amount' => (int) $pembayaran->total_bayar,
                    ],
                    'customer_details' => [
                        'first_name' => $customerName,
                        'email' => $customerEmail,
                    ],
                    'item_details' => $itemDetails,
                ];

                $snapToken = \Midtrans\Snap::getSnapToken($midtransParams);
                $pembayaran->update(['snap_token' => $snapToken]);
            } catch (\Throwable $e) {
                Log::warning('[PaymentController] Midtrans getSnapToken notice: ' . $e->getMessage());
                $snapToken = null;
            }
        }

        $isSandboxMock = empty($snapToken) && (empty($serverKey) || str_contains($serverKey, 'xxxxxx'));

        return view('konsumen.checkout', compact('pesanan', 'pembayaran', 'snapToken', 'clientKey', 'isProduction', 'isSandboxMock'));
    }

    public function simulateMidtransPay(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::with('pembayaran')->findOrFail($id_pesanan);

        $token = $request->input('token') ?? $request->query('token') ?? session('order_token');
        $isAuthorized = false;

        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            $isAuthorized = true;
        } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            $isAuthorized = true;
        } elseif (!$pesanan->id_konsumen && empty($pesanan->order_token) && session('active_order_id') == $pesanan->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        $pembayaran = $pesanan->pembayaran;
        if ($pembayaran) {
            $pembayaran->update([
                'status' => 'paid',
                'metode' => 'qris',
                'tanggal' => now(),
            ]);

            $pesanan->update(['status' => 'processing']);

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
                if ($pesanan->id_meja && $pesanan->meja) {
                    broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                }
            } catch (\Throwable $e) {
                Log::warning('[PaymentController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            $namaKonsumen = $pesanan->customer_name;
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => 'Pesanan Lunas QRIS: Order #' . $pesanan->id . ' (' . $namaKonsumen . ') telah lunas.',
                'is_read' => false
            ]);
        }

        $targetUrl = $pesanan->order_token ? url('/tracking/' . $pesanan->order_token . '?paid=1') : url('/');
        return redirect($targetUrl)->with('success', 'Pembayaran QRIS Midtrans berhasil diselesaikan (LUNAS).');
    }

    public function chooseCashPay(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::with(['pembayaran', 'meja'])->findOrFail($id_pesanan);

        $token = $request->input('token') ?? $request->query('token') ?? session('order_token');
        $isAuthorized = false;

        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            $isAuthorized = true;
        } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            $isAuthorized = true;
        } elseif (!$pesanan->id_konsumen && empty($pesanan->order_token) && session('active_order_id') == $pesanan->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        $pembayaran = $pesanan->pembayaran;
        if ($pembayaran && $pembayaran->status !== 'paid') {
            $pembayaran->update([
                'status' => 'unpaid',
                'metode' => 'cash',
            ]);

            // Ubah status pesanan agar langsung diproses dimasak dapur
            $pesanan->update(['status' => 'processing']);

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
                if ($pesanan->id_meja && $pesanan->meja) {
                    broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                }
            } catch (\Throwable $e) {
                Log::warning('[PaymentController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            $namaKonsumen = $pesanan->guest_name ?: ($pesanan->konsumen?->name ?? 'Tamu');
            $namaMeja = $pesanan->meja ? $pesanan->meja->nama_meja_atau_nomor : 'Meja';
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => '💵 Pesanan Bayar Cash: ' . $namaMeja . ' (' . $namaKonsumen . ') - Rp ' . number_format($pembayaran->total_bayar, 0, ',', '.') . ' (Bayar tunai saat makanan diantar).',
                'is_read' => false
            ]);
        }

        $targetUrl = $pesanan->order_token ? url('/tracking/' . $pesanan->order_token . '?cash=1') : url('/');
        return redirect($targetUrl)->with('success', 'Pilihan bayar Tunai berhasil dikirim. Waitress akan membawakan makanan beserta struk tagihan Anda.');
    }

    public function uploadBukti(Request $request, $id_pesanan)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $pesanan = Pesanan::with('pembayaran')->findOrFail($id_pesanan);

        $token = $request->input('token') ?? $request->query('token') ?? session('order_token');
        $isAuthorized = false;

        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            $isAuthorized = true;
        } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            $isAuthorized = true;
        } elseif (!$pesanan->id_konsumen && empty($pesanan->order_token) && session('active_order_id') == $pesanan->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        $pembayaran = $pesanan->pembayaran;

        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/bukti_bayar', $filename);

            $pembayaran->bukti_bayar = 'bukti_bayar/' . $filename;
            $pembayaran->status = 'pending_verification';
            $pembayaran->save();
            
            // Broadcast notification to cashier that there is a new payment to verify
            \App\Models\Notification::create([
                'type' => 'payment_verification',
                'message' => 'Pesanan #' . $pesanan->id . ' menunggu verifikasi pembayaran.',
                'id_meja' => $pesanan->id_meja,
            ]);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Silakan tunggu verifikasi kasir.');
    }


    public function webhook(Request $request)
    {
        // 1. Validasi Signature Key
        $serverKey = Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey'));
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Ekstrak ID Pesanan dari order_id (ORDER-{id}-{time} atau {id})
        $orderIdParts = explode('-', $request->order_id);
        $id_pesanan = isset($orderIdParts[1]) ? $orderIdParts[1] : $orderIdParts[0];

        $pembayaran = Pembayaran::where('id_pesanan', $id_pesanan)->first();
        if (!$pembayaran) return response()->json(['message' => 'Not Found'], 404);

        $pesanan = Pesanan::with(['meja', 'konsumen'])->find($id_pesanan);

        // 3. Update Status Berdasarkan Midtrans
        $transactionStatus = $request->transaction_status;

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $pembayaran->update([
                'status' => 'paid',
                'metode' => $request->payment_type == 'qris' ? 'qris' : 'cash',
                'tanggal' => now()
            ]);
            
            if ($pesanan) {
                $pesanan->update(['status' => 'processing']);

                // Trigger WebSocket Event (Reverb) ke POS Kasir & Layar Monitor Meja
                try {
                    broadcast(new \App\Events\PesananBaru($pesanan));

                    if ($pesanan->id_meja && $pesanan->meja) {
                        broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                    }
                } catch (\Throwable $e) {
                    Log::warning('[Midtrans Webhook] Gagal broadcast WebSocket: ' . $e->getMessage());
                }
            }

            // Beri notifikasi ke kasir
            $namaKonsumen = $pesanan ? $pesanan->customer_name : 'Tamu';
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => 'Pesanan Lunas QRIS: Order #' . $id_pesanan . ' (' . $namaKonsumen . ') telah lunas.',
                'is_read' => false
            ]);

            // Notify Admin and Kasir via Web Push
            $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
            \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                'Pesanan Lunas (Midtrans)',
                'Order #' . $id_pesanan . ' (' . $namaKonsumen . ') telah dibayar lunas via QRIS.',
                '/kasir/pos'
            ));

            // Kirim E-Receipt ke email jika ada
            if ($pesanan && $pesanan->konsumen && $pesanan->konsumen->email) {
                try {
                    Mail::to($pesanan->konsumen->email)->send(new ReceiptMail($pesanan));
                } catch (\Exception $mailEx) {
                    Log::error("Gagal mengirim e-receipt Midtrans: " . $mailEx->getMessage());
                }
            }
            
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            if ($pesanan) {
                $pesanan->cancelOrder();
            }
            Log::info("Pembayaran gagal untuk Order ID: {$id_pesanan}. Stok telah dikembalikan.");
        }

        return response()->json(['message' => 'Webhook Berhasil Diterima']);
    }
}