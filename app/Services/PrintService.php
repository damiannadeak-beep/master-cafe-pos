<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Setting;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrintService
{
    /**
     * Print Thermal Receipt (ESC/POS Network)
     * 
     * @param int $id_pesanan
     * @throws \Exception
     */
    public function printReceipt($id_pesanan)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'kasir', 'meja'])->findOrFail($id_pesanan);
        
        if (!$order->pembayaran || $order->pembayaran->status !== 'paid') {
            throw new \Exception('Pesanan belum dibayar lunas.');
        }

        $printer_active = Setting::getVal('printer_active') == '1';
        $printer_ip = Setting::getVal('printer_ip');
        $printer_port = Setting::getVal('printer_port', 9100);

        if (!$printer_active || empty($printer_ip)) {
            throw new \Exception('Fitur printer thermal tidak aktif atau IP belum diatur di Pengaturan.');
        }

        try {
            $connector = new NetworkPrintConnector($printer_ip, $printer_port);
            $printer = new Printer($connector);
            
            $storeName = Setting::getVal('store_name', 'Master Cafe');
            $storeAddress = Setting::getVal('store_address', '');
            
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text($storeName . "\n");
            $printer->setEmphasis(false);
            $printer->text($storeAddress . "\n");
            $printer->text("--------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Waktu   : " . Carbon::parse($order->pembayaran->tanggal)->format('d/m/Y H:i') . "\n");
            $printer->text("Kasir   : " . ($order->kasir->name ?? 'Kasir') . "\n");
            $printer->text("Meja    : " . ($order->meja->nama_meja_atau_nomor ?? '-') . "\n");
            $printer->text("Metode  : " . strtoupper($order->pembayaran->metode ?? '-') . "\n");
            $printer->text("--------------------------------\n");

            foreach ($order->detail_pesanan as $detail) {
                $namaMenu = substr($detail->menu->nama_menu, 0, 20);
                $qty = str_pad($detail->jumlah . "x", 4, " ", STR_PAD_RIGHT);
                $harga = str_pad(number_format($detail->subtotal, 0, ',', '.'), 8, " ", STR_PAD_LEFT);
                
                $printer->text($namaMenu . "\n");
                $printer->text("    " . $qty . $harga . "\n");
                
                if (!empty($detail->selected_variants)) {
                    $variants = json_decode($detail->selected_variants, true);
                    if (is_array($variants) && count($variants) > 0) {
                        $varText = implode(', ', array_column($variants, 'name'));
                        $printer->text("    - " . substr($varText, 0, 26) . "\n");
                    }
                }

                if (!empty($detail->catatan)) {
                    $printer->text("    * " . substr($detail->catatan, 0, 26) . "\n");
                }
            }
            $printer->text("--------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            if ($order->discount_amount > 0) {
                $printer->text("Subtotal : Rp " . number_format($order->total, 0, ',', '.') . "\n");
                $printer->text("Diskon   : Rp " . number_format($order->discount_amount, 0, ',', '.') . "\n");
            }
            $printer->setEmphasis(true);
            $printer->text("TOTAL : Rp " . number_format($order->pembayaran->total_bayar, 0, ',', '.') . "\n");
            $printer->setEmphasis(false);
            $printer->text("--------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $footerText = Setting::getVal('receipt_footer', 'Terima kasih atas kunjungan Anda!');
            $printer->text(str_replace('\n', "\n", $footerText) . "\n\n\n\n\n");

            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception('Gagal terhubung ke printer (' . $printer_ip . ':' . $printer_port . '). Error: ' . $e->getMessage());
        }
    }

    /**
     * Print Kitchen Receipt (ESC/POS Network)
     * 
     * @param int $id_pesanan
     * @throws \Exception
     */
    public function printKitchenReceipt($id_pesanan)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'meja'])->findOrFail($id_pesanan);
        
        $printer_active = Setting::getVal('printer_active') == '1';
        $printer_ip = Setting::getVal('printer_ip');
        $printer_port = Setting::getVal('printer_port', 9100);

        if (!$printer_active || empty($printer_ip)) {
            throw new \Exception('Fitur printer thermal tidak aktif atau IP belum diatur di Pengaturan.');
        }

        try {
            $connector = new NetworkPrintConnector($printer_ip, $printer_port);
            $printer = new Printer($connector);
            
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2);
            $printer->text("TIKET DAPUR\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("--------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Order #" . $order->id . " | Meja: " . ($order->meja->nama_meja_atau_nomor ?? '-') . "\n");
            $printer->text("Waktu  : " . Carbon::parse($order->tanggal)->format('d/m/Y H:i') . "\n");
            $printer->text("Tipe   : " . strtoupper(str_replace('_', ' ', $order->tipe_pesanan)) . "\n");
            $printer->text("--------------------------------\n");

            foreach ($order->detail_pesanan as $detail) {
                $printer->setEmphasis(true);
                $printer->text($detail->jumlah . "x " . $detail->menu->nama_menu . "\n");
                $printer->setEmphasis(false);
                
                if (!empty($detail->selected_variants)) {
                    $variants = json_decode($detail->selected_variants, true);
                    if (is_array($variants) && count($variants) > 0) {
                        $varText = implode(', ', array_column($variants, 'name'));
                        $printer->text("  [Varian] " . substr($varText, 0, 21) . "\n");
                    }
                }

                if (!empty($detail->catatan)) {
                    $printer->text("  [Catatan] " . substr($detail->catatan, 0, 20) . "\n");
                }
                
                $printer->text("\n");
            }
            $printer->text("--------------------------------\n");
            $printer->text("\n\n\n\n");
            
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception('Gagal mencetak ke dapur. Error: ' . $e->getMessage());
        }
    }
}
