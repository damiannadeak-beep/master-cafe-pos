<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KasirShift;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;

class ShiftController extends Controller
{
    public function bukaShift()
    {
        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if ($shift) {
            return redirect()->route('kasir.pos');
        }
        return view('waitress.shift.buka');
    }

    public function storeBukaShift(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0'
        ]);

        KasirShift::create([
            'user_id' => auth()->id(),
            'modal_awal' => $request->modal_awal,
            'status' => 'open'
        ]);

        return redirect()->route('kasir.pos')->with('success', 'Shift berhasil dibuka. Selamat bekerja!');
    }

    public function tutupShift()
    {
        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if (!$shift) {
            return redirect()->route('kasir.pos')->with('error', 'Tidak ada shift yang aktif.');
        }

        return view('waitress.shift.tutup', compact('shift'));
    }

    public function storeTutupShift(Request $request)
    {
        $request->validate([
            'uang_fisik_aktual' => 'required|numeric|min:0'
        ]);

        $shift = KasirShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if (!$shift) {
            return redirect()->route('kasir.pos');
        }

        // Kalkulasi pemasukan tunai selama shift (pesanan kasir ini & pesanan online/QR)
        // FIX #9: Sum dari pembayaran.total_bayar (setelah diskon), bukan pesanan.total (sebelum diskon)
        $totalTunai = Pembayaran::where('status', 'paid')
            ->where('metode', 'cash')
            ->where('updated_at', '>=', $shift->waktu_buka)
            ->whereHas('pesanan', function($q) use ($shift) {
                $q->where(function($sub) use ($shift) {
                    $sub->where('id_kasir', $shift->user_id)
                        ->orWhereNull('id_kasir');
                });
            })->sum('total_bayar');

        // Kalkulasi pengeluaran kasir selama shift
        $totalPengeluaran = Pengeluaran::where('user_id', auth()->id())
            ->where('created_at', '>=', $shift->waktu_buka)
            ->sum('nominal');

        $harapanFisik = $shift->modal_awal + $totalTunai - $totalPengeluaran;
        $selisih = $request->uang_fisik_aktual - $harapanFisik;

        $shift->update([
            'waktu_tutup' => now(),
            'uang_fisik_aktual' => $request->uang_fisik_aktual,
            'total_pemasukan_tunai' => $totalTunai,
            'total_pengeluaran' => $totalPengeluaran,
            'selisih' => $selisih,
            'status' => 'closed'
        ]);

        // Notify Admin
        $admins = \App\Models\User::role('pemilik')->get();
        $kasirName = auth()->user()->name;
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\WebPushNotification(
            'Laporan Shift Kasir',
            "Kasir {$kasirName} telah menutup shift. Pemasukan: Rp " . number_format($totalTunai, 0, ',', '.'),
            '/admin/kasir'
        ));

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Shift berhasil ditutup. Terima kasih atas kerja keras Anda!');
    }

    /**
     * Data internal untuk laporan shift (menghilangkan duplikasi).
     */
    public function getShiftReportData(): array
    {
        $kasir_id = auth()->id();
        
        $shift = KasirShift::where('user_id', $kasir_id)->latest('id')->first();
        if (!$shift) {
            throw new \Exception('Tidak ada data shift ditemukan.');
        }

        $query = Pembayaran::with('pesanan.detail_pesanan.menu')
            ->whereHas('pesanan', function($q) use ($kasir_id) {
                $q->where(function($sub) use ($kasir_id) {
                    $sub->where('id_kasir', $kasir_id)
                        ->orWhereNull('id_kasir');
                });
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
            return view('waitress.shift_report', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ekspor Laporan Tutup Shift Kasir ke PDF
     */
    public function exportShiftReportPdf()
    {
        try {
            $data = $this->getShiftReportData();
            $data['hariIni'] = $data['shift']->waktu_buka ? $data['shift']->waktu_buka->format('Y-m-d') : date('Y-m-d');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('waitress.shift_report_pdf', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Shift_Kasir_' . $data['hariIni'] . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat file PDF: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor Laporan Tutup Shift Kasir ke Microsoft Excel (.xls)
     */
    public function exportShiftReportExcel()
    {
        try {
            $data = $this->getShiftReportData();
            $shift = $data["shift"];
            $hariIni = $shift->waktu_buka->format("Y-m-d");
            $filename = "laporan_shift_kasir_" . $hariIni . ".xls";

            $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Shift Kasir</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            $html .= '<body style="font-family: Arial, sans-serif; font-size: 10pt;">';
            
            // Header
            $html .= '<table border="0" cellpadding="3" cellspacing="0">';
            $html .= '<tr><td colspan="4" style="font-size: 14pt; font-weight: bold; text-align: left;">MASTER CAFE POS</td></tr>';
            $html .= '<tr><td colspan="4" style="font-size: 10pt; text-align: left;">Laporan Rekonsiliasi Tutup Shift Kasir</td></tr>';
            $html .= '<tr><td colspan="4"></td></tr>';
            $html .= '<tr><td colspan="2" style="text-align: left;">STAF KASIR: ' . strtoupper(auth()->user()->name) . '</td><td colspan="2" style="text-align: right;">Tanggal Shift: ' . \Carbon\Carbon::parse($hariIni)->translatedFormat("d F Y") . '</td></tr>';
            $html .= '<tr><td colspan="2"></td><td colspan="2" style="text-align: right;">Dicetak: ' . now()->translatedFormat("H:i") . ' WIB</td></tr>';
            $html .= '<tr><td colspan="4"></td></tr>';
            $html .= '</table>';

            // KPI Grid
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            $html .= '<tr>';
            $html .= '<td style="font-weight: bold; text-align: center; background-color: #ffffff;">Kas Tunai (Laci Kas)</td>';
            $html .= '<td style="font-weight: bold; text-align: center; background-color: #ffffff;">Non-Tunai (QRIS)</td>';
            $html .= '<td colspan="2" style="font-weight: bold; text-align: center; background-color: #ffffff;">Total Omzet Shift</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align: center; font-size: 12pt;">Rp ' . number_format($data["totalCash"], 0, ",", ".") . '</td>';
            $html .= '<td style="text-align: center; font-size: 12pt;">Rp ' . number_format($data["totalQris"], 0, ",", ".") . '</td>';
            $html .= '<td colspan="2" style="text-align: center; font-size: 12pt; font-weight: bold;">Rp ' . number_format($data["totalSemua"], 0, ",", ".") . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            
            $html .= '<br>';

            // Menu Items Table
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            $html .= '<tr>';
            $html .= '<td style="font-weight: bold; text-align: center; background-color: #ffffff;">No</td>';
            $html .= '<td style="font-weight: bold; text-align: left; background-color: #ffffff;">Nama Menu / Item</td>';
            $html .= '<td style="font-weight: bold; text-align: center; background-color: #ffffff;">Jumlah Terjual</td>';
            $html .= '<td style="font-weight: bold; text-align: right; background-color: #ffffff;">Subtotal Penjualan</td>';
            $html .= '</tr>';

            $no = 1;
            if (empty($data["rekapMenu"])) {
                $html .= '<tr><td colspan="4" style="text-align: center;">Belum ada item terjual pada shift ini.</td></tr>';
            } else {
                foreach ($data["rekapMenu"] as $nama => $itemData) {
                    $html .= '<tr>';
                    $html .= '<td style="text-align: center;">' . $no++ . '</td>';
                    $html .= '<td style="text-align: left;">' . $nama . '</td>';
                    $html .= '<td style="text-align: center;">' . $itemData["jumlah"] . ' porsi</td>';
                    $html .= '<td style="text-align: right;">Rp ' . number_format($itemData["subtotal"], 0, ",", ".") . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '<tr style="font-weight: bold;">';
                $html .= '<td colspan="2" style="text-align: right;">TOTAL ITEM TERJUAL</td>';
                $html .= '<td style="text-align: center;">' . $data["totalItemTerjual"] . ' porsi</td>';
                $html .= '<td style="text-align: right;">Rp ' . number_format($data["totalSemua"], 0, ",", ".") . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table></body></html>';

            return response($html, 200, [
                "Content-Type" => "application/vnd.ms-excel",
                "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }
}
