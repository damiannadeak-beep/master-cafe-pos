<?php

namespace App\Helpers;

use Spatie\Activitylog\Models\Activity;

class ActivityLogHelper
{
    /**
     * Format Model target menjadi nama bisnis yang mudah dipahami pemilik kafe
     */
    public static function formatTarget(Activity $log): array
    {
        $type = class_basename($log->subject_type);
        $icon = 'bi-record-circle';
        $label = $type ?: 'Sistem';

        switch ($type) {
            case 'Pesanan':
                $label = 'Pesanan Pelanggan';
                $icon = 'bi-receipt';
                break;
            case 'Menu':
                $label = 'Menu Produk';
                $icon = 'bi-cup-hot';
                break;
            case 'Bahan':
                $label = 'Bahan Baku';
                $icon = 'bi-box-seam';
                break;
            case 'Pengeluaran':
                $label = 'Biaya Operasional';
                $icon = 'bi-cash-coin';
                break;
            case 'User':
                $label = 'Akun Pengguna / Staf';
                $icon = 'bi-person';
                break;
            case 'KasirShift':
                $label = 'Shift Kasir';
                $icon = 'bi-clock-history';
                break;
            case 'Meja':
                $label = 'Meja Kafe';
                $icon = 'bi-grid-fill';
                break;
        }

        return [
            'label' => $label,
            'icon' => $icon,
            'id' => $log->subject_id ? '#' . $log->subject_id : '',
        ];
    }

    /**
     * Format aksi (created, updated, deleted) menjadi teks ramah
     */
    public static function formatEvent(string $event): array
    {
        switch ($event) {
            case 'created':
                return [
                    'label' => 'Data Baru',
                    'badge' => 'bg-success text-white',
                    'icon' => 'bi-plus-circle'
                ];
            case 'updated':
                return [
                    'label' => 'Perubahan Data',
                    'badge' => 'bg-warning text-dark',
                    'icon' => 'bi-pencil-square'
                ];
            case 'deleted':
                return [
                    'label' => 'Dihapus',
                    'badge' => 'bg-danger text-white',
                    'icon' => 'bi-trash'
                ];
            default:
                return [
                    'label' => ucfirst($event),
                    'badge' => 'bg-secondary text-white',
                    'icon' => 'bi-activity'
                ];
        }
    }

    /**
     * Terjemahkan nama kolom database menjadi istilah operasional kafe
     */
    public static function formatFieldName(string $field): string
    {
        return match ($field) {
            'status' => 'Status Pesanan',
            'total' => 'Total Tagihan',
            'stok' => 'Jumlah Stok',
            'harga' => 'Harga Jual',
            'nama_menu' => 'Nama Menu',
            'nama_bahan' => 'Nama Bahan',
            'tipe_pesanan' => 'Jenis Pesanan',
            'is_available' => 'Ketersediaan',
            'nominal' => 'Nominal Biaya',
            'keterangan' => 'Keterangan',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Kategori',
            'shift' => 'Shift Kasir',
            'name' => 'Nama Staf',
            'email' => 'Alamat Email',
            'no_hp' => 'Nomor WhatsApp / HP',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }

    /**
     * Format nilai data menjadi ramah dibaca manusia (Rp, label status bahasa Indonesia)
     */
    public static function formatValue(string $field, $val): string
    {
        if ($val === null || $val === '') {
            return '-';
        }
        if (is_bool($val)) {
            return $val ? 'Tersedia / Aktif' : 'Tidak Aktif / Habis';
        }
        if ($field === 'status') {
            return match (strtolower((string)$val)) {
                'pending' => 'Menunggu Pembayaran',
                'diproses' => 'Sedang Diproses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'paid' => 'Sudah Lunas',
                default => ucfirst((string)$val),
            };
        }
        if ($field === 'tipe_pesanan') {
            return match (strtolower((string)$val)) {
                'dine_in' => 'Makan di Tempat (Dine In)',
                'takeaway' => 'Bungkus (Takeaway)',
                default => ucfirst((string)$val),
            };
        }
        if (in_array($field, ['total', 'harga', 'nominal', 'total_hpp', 'discount_amount'])) {
            $num = floatval($val);
            return 'Rp ' . number_format($num, 0, ',', '.');
        }
        if ($field === 'is_available') {
            return $val ? 'Tersedia' : 'Habis';
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return (string)$val;
    }

    /**
     * Ringkasan aktivitas dalam 1 kalimat bahasa Indonesia yang sangat ramah pemilik kafe
     */
    public static function getFriendlyDescription(Activity $log): string
    {
        $type = class_basename($log->subject_type);
        $event = $log->event;
        $props = $log->properties;
        $id = $log->subject_id ? '#' . $log->subject_id : '';

        if ($type === 'Pesanan') {
            if ($event === 'created') {
                $tipe = $props['attributes']['tipe_pesanan'] ?? 'dine_in';
                $tipeStr = $tipe === 'takeaway' ? 'Bungkus (Takeaway)' : 'Makan di Tempat';
                return "Membuat pesanan baru {$id} ({$tipeStr})";
            }
            if ($event === 'updated') {
                if (isset($props['attributes']['status'])) {
                    $statusStr = self::formatValue('status', $props['attributes']['status']);
                    return "Status pesanan {$id} diubah menjadi '{$statusStr}'";
                }
                if (isset($props['attributes']['total'])) {
                    $totalStr = self::formatValue('total', $props['attributes']['total']);
                    return "Total tagihan pesanan {$id} diperbarui menjadi {$totalStr}";
                }
                return "Memperbarui rincian data pesanan {$id}";
            }
            if ($event === 'deleted') {
                return "Menghapus / membatalkan pesanan {$id}";
            }
        }

        if ($type === 'Menu') {
            if ($event === 'updated') {
                if (isset($props['attributes']['stok'])) {
                    $oldStok = $props['old']['stok'] ?? '?';
                    $newStok = $props['attributes']['stok'];
                    return "Stok produk {$id} berubah dari {$oldStok} menjadi {$newStok}";
                }
                if (isset($props['attributes']['harga'])) {
                    $oldHarga = self::formatValue('harga', $props['old']['harga'] ?? 0);
                    $newHarga = self::formatValue('harga', $props['attributes']['harga']);
                    return "Harga jual menu {$id} diubah dari {$oldHarga} menjadi {$newHarga}";
                }
                return "Memperbarui informasi menu produk {$id}";
            }
            if ($event === 'created') {
                $nama = $props['attributes']['nama_menu'] ?? 'Menu Baru';
                return "Menambahkan menu baru '{$nama}'";
            }
            if ($event === 'deleted') {
                return "Menghapus menu {$id}";
            }
        }

        if ($type === 'Bahan') {
            if ($event === 'updated' && isset($props['attributes']['stok'])) {
                $oldStok = $props['old']['stok'] ?? '?';
                $newStok = $props['attributes']['stok'];
                return "Stok bahan baku {$id} berubah dari {$oldStok} menjadi {$newStok}";
            }
            return "Perubahan data bahan baku {$id}";
        }

        if ($type === 'Pengeluaran') {
            if ($event === 'created') {
                $nom = self::formatValue('nominal', $props['attributes']['nominal'] ?? 0);
                $ket = $props['attributes']['keterangan'] ?? '';
                return "Mencatat pengeluaran operasional baru sebesar {$nom} (" . ($ket ?: 'Biaya operasional') . ")";
            }
            return "Perubahan data pengeluaran {$id}";
        }

        return $log->description ?: "Aktivitas pada {$type} {$id}";
    }
}
