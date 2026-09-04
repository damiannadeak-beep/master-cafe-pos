<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bahan;
use App\Models\VoidLog;
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PrintService;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Pos\{StoreManualOrderRequest, VoidOrderRequest, SplitOrderRequest, UpdateOrderStatusRequest, PayOrderRequest};

class PosController extends Controller
{
    /**
     * Menampilkan Halaman POS untuk Kasir
     */
    public function index()
    {
        $menus = Menu::where('is_available', true)->get();
        $mejas = Meja::all();
        $promos = Promo::active()->get();

        return view('kasir.pos', compact('menus', 'mejas', 'promos'));
    }

    /**
     * Menampilkan Halaman Pesanan Aktif Konsumen
     */
    public function pesananAktif()
    {
        $orders = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen'])
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'processing'])
                      ->orWhere(function ($q) {
                          $q->where('status', 'completed')
                            ->whereHas('pembayaran', function ($p) {
                                $p->where('status', '!=', 'paid');
                            });
                      });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kasir.pesanan_aktif', compact('orders'));
    }

    /**
     * Mengambil jumlah pesanan aktif untuk badge notifikasi
     */
    public function activeOrdersCount()
    {
        $count = Pesanan::where(function ($query) {
            $query->whereIn('status', ['pending', 'processing'])
                  ->orWhere(function ($q) {
                      $q->where('status', 'completed')
                        ->whereHas('pembayaran', function ($p) {
                            $p->where('status', '!=', 'paid');
                        });
                  });
        })->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Memproses pesanan manual dari Kasir
     */
    public function storeManualOrder(StoreManualOrderRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 2. Buat Data Pesanan Baru
            $pesanan = Pesanan::create([
                'id_konsumen' => null,
                'id_meja' => $validated['id_meja'],
                'id_kasir' => auth()->id(),
                'tipe_pesanan' => $validated['tipe_pesanan'],
                'tanggal' => now(),
                'status' => 'pending',
                'promo_id' => $validated['promo_id'] ?? null
            ]);

            // Otomatis matikan ketersediaan meja jika ini pesanan dine-in
            if ($validated['id_meja'] && $validated['tipe_pesanan'] === 'dine_in') {
                Meja::where('id', $validated['id_meja'])->update(['is_available' => false]);
            }

            // 3. Proses item pesanan via OrderService (lock stok, kurangi bahan, buat detail)
            $result = $orderService->processOrderItems($pesanan, $validated['items']);
            $totalSemua = $result['total'];
            $total_hpp = $result['total_hpp'];

            // 4. Hitung diskon via OrderService
            $discountAmount = $orderService->calculateDiscount(
                $totalSemua,
                $validated['promo_id'] ?? null,
                $validated['items']
            );

            $totalBayar = $totalSemua - $discountAmount;

            // Update total harga dan diskon di tabel pesanan
            $pesanan->update([
                'total' => $totalSemua,
                'discount_amount' => $discountAmount,
                'total_hpp' => $total_hpp
            ]);

            // 5. Proses Status Pembayaran
            $statusBayar = $validated['pembayaran_langsung'] ? 'paid' : 'unpaid';
            $metodeBayar = $validated['pembayaran_langsung'] ? ($validated['metode_pembayaran'] ?? 'cash') : null;

            Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'metode' => $metodeBayar,
                'status' => $statusBayar,
                'total_bayar' => $totalBayar,
                'tanggal' => $validated['pembayaran_langsung'] ? now() : null,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Pesanan manual berhasil diproses.',
                'id_pesanan' => $pesanan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pesanan dari kasir
     */
    public function updateOrderStatus(UpdateOrderStatusRequest $request, $id_pesanan)
    {
        try {
            $pesanan = Pesanan::findOrFail($id_pesanan);
            
            // Update status
            $pesanan->update([
                'status' => $request->validated('status'),
                'id_kasir' => auth()->id() // Kasir yang memproses pesanan
            ]);

            // Notify Customer via Web Push
            if ($pesanan->konsumen) {
                $statusText = $pesanan->status === 'completed' ? 'Selesai' : 'Diproses';
                $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                    'Pesanan ' . $statusText,
                    'Pesanan Anda (Order #' . $pesanan->id . ') saat ini ' . strtolower($statusText) . '.',
                    '/konsumen/profil'
                ));
            }

            return response()->json([
                'message' => 'Status pesanan berhasil diupdate',
                'status' => $pesanan->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pembayaran dari kasir
     */
    
    public function verifyPayment($id_pesanan, PaymentService $paymentService)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = $paymentService->processPayment(
                $id_pesanan,
                'qris', // Default to QRIS since it was uploaded
                null,
                auth()->id()
            );

            // Set status pesanan menjadi processing (dimasak) jika dine_in dan belum processing
            if ($pesanan->status === 'pending') {
                $pesanan->status = 'processing';
                $pesanan->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran pesanan #'.$pesanan->id.' berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal verifikasi pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memverifikasi pembayaran.');
        }
    }

    public function rejectPayment($id_pesanan)
    {
        try {
            $pesanan = Pesanan::with('pembayaran')->findOrFail($id_pesanan);
            if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'pending_verification') {
                $pesanan->pembayaran->status = 'unpaid';
                $pesanan->pembayaran->bukti_bayar = null;
                $pesanan->pembayaran->save();
                return redirect()->back()->with('success', 'Bukti pembayaran ditolak. Pesanan dikembalikan ke status belum bayar.');
            }
            return redirect()->back()->with('error', 'Pesanan tidak dalam status verifikasi.');
        } catch (\Exception $e) {
            Log::error('Gagal tolak pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menolak pembayaran.');
        }
    }

    public function payOrder(PayOrderRequest $request, $id_pesanan, PaymentService $paymentService)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            
            $pesanan = $paymentService->processPayment(
                $id_pesanan,
                $validated['metode'],
                $validated['email_pelanggan'] ?? null,
                auth()->id()
            );

            DB::commit();
            return response()->json([
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'id_pesanan' => $pesanan->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk pesanan (Thermal 58mm)
     */
    public function printReceipt($id)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'kasir', 'meja'])->findOrFail($id);
        
        if (!$order->pembayaran || $order->pembayaran->status !== 'paid') {
            abort(403, 'Pesanan belum dibayar lunas.');
        }

        return view('kasir.receipt', compact('order'));
    }

    /**
     * Cetak struk langsung ke Printer Thermal (Raw ESC/POS Network)
     */
    public function printThermalReceipt($id, PrintService $printService)
    {
        try {
            $printService->printReceipt($id);
            return response()->json(['message' => 'Struk berhasil dikirim ke printer thermal.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Membatalkan pesanan dari kasir (Void).
     */
    public function voidOrder(VoidOrderRequest $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();
            $pesanan = Pesanan::findOrFail($id_pesanan);

            if (!Hash::check($request->input('password'), auth()->user()->password)) {
                throw new \Exception('Password yang dimasukkan salah.');
            }

            if ($pesanan->status === 'completed') {
                throw new \Exception('Pesanan sudah selesai dan tidak dapat divoid.');
            }

            // Simpan log void
            DB::table('void_logs')->insert([
                'pesanan_id' => $pesanan->id,
                'kasir_id' => auth()->id(),
                'alasan' => $request->input('alasan') ?? 'Batal',
                'total_nilai' => $pesanan->total,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Batalkan pesanan dan restore stok
            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil divoid. Stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk dapur (tanpa harga).
     */
    public function printKitchenReceipt($id, PrintService $printService)
    {
        try {
            $printService->printKitchenReceipt($id);
            return response()->json(['message' => 'Tiket dapur berhasil dikirim ke printer thermal.']);
        } catch (\Exception $e) {
            // Jika fitur mati, jatuh kembali ke print html biasa (fallback)
            if (str_contains($e->getMessage(), 'tidak aktif')) {
                $order = Pesanan::with(['detail_pesanan.menu', 'meja'])->findOrFail($id);
                return view('kasir.kitchen_receipt', compact('order'));
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Data internal untuk laporan shift (menghilangkan duplikasi).
     */
    private function getShiftReportData(): array
    {
        $kasir_id = auth()->id();
        
        $shift = \App\Models\KasirShift::where('user_id', $kasir_id)->latest('id')->first();
        if (!$shift) {
            throw new \Exception('Tidak ada data shift ditemukan.');
        }

        $query = Pembayaran::with('pesanan.detail_pesanan.menu')
            ->whereHas('pesanan', function($q) use ($kasir_id) {
                $q->where('id_kasir', $kasir_id);
            })
            ->where('status', 'paid')
            ->where('updated_at', '>=', $shift->waktu_buka);

        if ($shift->waktu_tutup) {
            $query->where('updated_at', '<=', $shift->waktu_tutup);
        }
        
        $pembayarans = $query->get();

        $totalCash = $pembayarans->where('metode', 'cash')->sum('total_bayar');
        $totalQris = $pembayarans->where('metode', 'qris')->sum('total_bayar');
        $totalSemua = $totalCash + $totalQris;

        // Hitung rekap menu terjual
        $rekapMenu = [];
        $totalItemTerjual = 0;
        foreach ($pembayarans as $pay) {
            if ($pay->pesanan) {
                foreach ($pay->pesanan->detail_pesanan as $detail) {
                    if ($detail->menu) {
                        $nama = $detail->menu->nama_menu;
                        if (!isset($rekapMenu[$nama])) {
                            $rekapMenu[$nama] = ['jumlah' => 0, 'subtotal' => 0];
                        }
                        $rekapMenu[$nama]['jumlah'] += $detail->jumlah;
                        $rekapMenu[$nama]['subtotal'] += $detail->subtotal;
                        $totalItemTerjual += $detail->jumlah;
                    }
                }
            }
        }

        return compact('totalCash', 'totalQris', 'totalSemua', 'pembayarans', 'shift', 'rekapMenu', 'totalItemTerjual');
    }

    /**
     * Menampilkan laporan tutup shift kasir
     */
    public function shiftReport()
    {
        try {
            $data = $this->getShiftReportData();
            return view('kasir.shift_report', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function exportShiftReportPdf()
    {
        try {
            $data = $this->getShiftReportData();
            $data['hariIni'] = $data['shift']->waktu_buka->format('Y-m-d');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kasir.shift_report_pdf', $data);
            return $pdf->download('Laporan_Shift_' . $data['hariIni'] . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ekspor Laporan Tutup Shift Kasir ke Microsoft Excel (.xls)
     */
    public function exportShiftReportExcel()
    {
        try {
            $data = $this->getShiftReportData();
            $shift = $data['shift'];
            $hariIni = $shift->waktu_buka->format('Y-m-d');
            $filename = 'laporan_shift_kasir_' . $hariIni . '.xls';

            $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Shift Kasir</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            $html .= '<body style="font-family: Arial, sans-serif; font-size: 10pt;">';
            $html .= '<table border="0" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 10pt; border-collapse: collapse;">';

            // Title Bar
            $html .= '<tr><td colspan="5" style="font-size: 11pt; font-weight: bold; text-align: center; height: 28px; vertical-align: middle; border: 0.5pt solid #000000; background-color: #ffffff;">LAPORAN SHIFT KASIR - ' . strtoupper(auth()->user()->name) . ' (' . $shift->waktu_buka->format('d/m/Y') . ')</td></tr>';

            // Table Column Headers
            $html .= '<tr style="font-weight: bold; text-align: center; background-color: #ffffff;">';
            $html .= '<td style="border: 0.5pt solid #000000; font-weight: bold;">No. Invoice</td>';
            $html .= '<td style="border: 0.5pt solid #000000; font-weight: bold;">Tanggal / Waktu</td>';
            $html .= '<td style="border: 0.5pt solid #000000; font-weight: bold;">Tipe Pesanan</td>';
            $html .= '<td style="border: 0.5pt solid #000000; font-weight: bold;">Metode Bayar</td>';
            $html .= '<td style="border: 0.5pt solid #000000; font-weight: bold;">Total Tagihan (Rp)</td>';
            $html .= '</tr>';

            $totalShiftSum = 0;
            foreach ($data['pembayarans'] as $p) {
                $invoiceNo = 'INV/' . ($p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('Ymd') : now()->format('Ymd')) . '/' . str_pad($p->id_pesanan, 5, '0', STR_PAD_LEFT);
                $waktu = $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y H.i') : '-';
                $tipe = ucfirst(str_replace('_', ' ', $p->pesanan->tipe_pesanan ?? 'Dine In'));
                $metode = strtoupper($p->metode ?? 'CASH');
                if ($metode === 'QRIS') {
                    $metode = 'TRANSFER_BANK_QRIS';
                }
                $total = (float) $p->total_bayar;
                $totalShiftSum += $total;

                $html .= '<tr>';
                $html .= '<td style="border: 0.5pt solid #b0b0b0;">' . $invoiceNo . '</td>';
                $html .= '<td style="border: 0.5pt solid #b0b0b0; text-align: center;">' . $waktu . '</td>';
                $html .= '<td style="border: 0.5pt solid #b0b0b0;">' . $tipe . '</td>';
                $html .= '<td style="border: 0.5pt solid #b0b0b0;">' . $metode . '</td>';
                $html .= '<td style="border: 0.5pt solid #b0b0b0; text-align: right;">' . number_format($total, 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }

            // Summary Total Row (Accounting Double Line)
            $html .= '<tr style="font-weight: bold;">';
            $html .= '<td colspan="4" style="text-align: right; border-top: 1pt solid #000000; border-bottom: 2.25pt double #000000; border-left: 0.5pt solid #000000; font-weight: bold;">TOTAL PENJUALAN SHIFT</td>';
            $html .= '<td style="text-align: right; border-top: 1pt solid #000000; border-bottom: 2.25pt double #000000; border-right: 0.5pt solid #000000; font-weight: bold;">' . number_format($totalShiftSum, 0, ',', '.') . '</td>';
            $html .= '</tr>';

            $html .= '</table></body></html>';

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Memisahkan pesanan (Split Bill)
     */
    public function splitOrder(SplitOrderRequest $request, $id_pesanan)
    {
        $validated = $request->validate([
            'split_items' => 'required|array',
            'split_items.*.id_detail' => 'required|exists:detail_pesanan,id',
            'split_items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $pesananAsli = Pesanan::with('detail_pesanan')->findOrFail($id_pesanan);

            if ($pesananAsli->status === 'completed' || ($pesananAsli->pembayaran && $pesananAsli->pembayaran->status === 'paid')) {
                throw new \Exception('Pesanan sudah dibayar, tidak bisa dipisah.');
            }

            // Simpan total asli sebelum split untuk menghitung rasio HPP
            $totalAsliSebelumSplit = $pesananAsli->total;
            $hppAsliSebelumSplit = $pesananAsli->total_hpp;

            // 1. Buat Pesanan Baru (Clone)
            $pesananBaru = $pesananAsli->replicate();
            $pesananBaru->total = 0;
            $pesananBaru->total_hpp = 0; // Reset HPP pesanan baru
            $pesananBaru->discount_amount = 0; // Reset diskon
            $pesananBaru->promo_id = null; // Promo tidak dipindah otomatis
            $pesananBaru->save();

            $totalBaru = 0;

            // 2. Pindahkan Detail Pesanan
            foreach ($validated['split_items'] as $item) {
                $detail = DetailPesanan::where('id', $item['id_detail'])->where('id_pesanan', $pesananAsli->id)->first();
                if ($detail) {
                    if ($item['jumlah'] < $detail->jumlah) {
                        // Pecah record detail pesanan
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
                        // Pindah seluruhnya
                        $totalBaru += $detail->subtotal;
                        $detail->update(['id_pesanan' => $pesananBaru->id]);
                    }
                }
            }

            // =====================================================================
            // FIX #3: Distribusi HPP secara proporsional berdasarkan rasio harga jual
            // =====================================================================
            $hppBaru = 0;
            if ($totalAsliSebelumSplit > 0) {
                // Rasio HPP = (total harga jual yang dipindah / total harga jual sebelum split) * HPP asli
                $rasio = $totalBaru / $totalAsliSebelumSplit;
                $hppBaru = round($hppAsliSebelumSplit * $rasio, 2);
            }

            // 3. Update Total Pesanan Baru (termasuk HPP)
            $pesananBaru->update([
                'total' => $totalBaru,
                'total_hpp' => $hppBaru
            ]);
            Pembayaran::create([
                'id_pesanan' => $pesananBaru->id,
                'status' => 'unpaid',
                'total_bayar' => $totalBaru
            ]);

            // 4. Update Total Pesanan Lama (Asli) termasuk HPP
            $totalAsli = DetailPesanan::where('id_pesanan', $pesananAsli->id)->sum('subtotal');
            $hppAsli = $hppAsliSebelumSplit - $hppBaru; // Sisa HPP = HPP awal - HPP yang dipindah
            
            $pesananAsli->update([
                'total' => $totalAsli,
                'total_hpp' => $hppAsli,
                'promo_id' => null, // Hapus promo jika pesanan pecah
                'discount_amount' => 0
            ]);
            $pesananAsli->pembayaran()->update([
                'total_bayar' => $totalAsli
            ]);

            // Cek apakah pesanan asli jadi kosong, hapus jika iya
            if ($totalAsli == 0) {
                $pesananAsli->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dipisah.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil daftar notifikasi terbaru (misal untuk Panggil Pelayan)
     */
    public function getNotifications()
    {
        $notifications = \App\Models\Notification::where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($notifications);
    }

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function readNotification($id)
    {
        $notif = \App\Models\Notification::find($id);
        if ($notif) {
            $notif->update(['is_read' => true]);
            return response()->json(['message' => 'Notifikasi ditandai dibaca']);
        }
        return response()->json(['error' => 'Notifikasi tidak ditemukan'], 404);
    }
}