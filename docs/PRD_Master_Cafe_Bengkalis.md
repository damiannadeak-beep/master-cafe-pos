# Product Requirement Document (PRD) — Master Cafe POS System

## 1. Informasi Proyek & Studi Kasus
- **Nama Sistem**: Master Cafe Point of Sale (POS) & Management System
- **Studi Kasus**: Master Cafe (Bengkalis, Riau)
- **Pengembang**: Damian Nadeak (Mahasiswa Polbeng)
- **Tujuan**: Tugas Akhir (TA) - Perancangan dan Pembangunan Sistem Informasi Kasir, Operasional, dan Manajemen Cafe.

---

## 2. Latar Belakang & Identifikasi Masalah
Master Cafe merupakan salah satu usaha kuliner (cafe) di Bengkalis yang melayani pelanggan dengan menu makanan dan minuman. Berdasarkan analisis awal, sistem operasional cafe memerlukan peningkatan dari sistem kasir/manual sederhana menjadi sistem POS terintegrasi untuk menangani:
1. **Pemesanan & Status Meja**: Pemantauan kapasitas meja (*dine-in*) dan kecepatan penyampaian pesanan ke dapur/bar.
2. **Pencetakan Pesanan Terpisah (Multi-Printer)**: Pemisahan tiket pesanan ke printer Barista (minuman) dan Dapur (makanan).
3. **Akuntabilitas Kasir & Shift**: Pengawasan modal kas awal, selisih kas, dan pembatalan pesanan (*void/refund*) menggunakan otorisasi Supervisor.
4. **Manajemen Bahan Baku & Resep (BOM)**: Pengurangan otomatis stok bahan baku (kopi, susu, sirup) berdasarkan setiap transaksi penjualan.
5. **Pelaporan Manajemen Jarak Jauh**: Dashboard pemilik untuk memantau omzet harian, profit, menu terlaris, dan stok menipis.

---

## 3. Scope & Modul Utama Aplikasi

### 3.1 Role & Hak Akses User (RBAC)
- **Owner / Manager**: Akses penuh ke dashboard laporan, manajemen user, stok, promo, audit void, dan pengaturan toko.
- **Kasir**: Akses ke modul POS transaksi, buka/tutup shift kas, cetak nota, dan pengajuan void.
- **Waitress / Pelayan**: Akses ke modul pemesanan meja dan status ketersediaan meja.
- **Barista / Dapur**: Akses ke modul Kitchen Display System (KDS) atau printer ticket pesanan.

### 3.2 Modul Utama Sistem
1. **Modul POS & Kasir**:
   - Pemesanan Dine-In, Takeaway, dan Online.
   - Pindah Meja (Move Table) & Gabung Nota (Merge Bill) / Split Bill.
   - Multi-Payment Method (Cash, QRIS, Debit/Credit).
   - Cetak Nota Thermal & E-Receipt.
2. **Modul Manajemen Meja (Table Management)**:
   - Denah visual status meja (Kosong, Terisi, Reserved).
3. **Modul Dapur & Bar (Kitchen & Bar Dispatcher)**:
   - Auto-routing cetak ticket ke printer Bar / Dapur.
4. **Modul Inventory & Resep (BOM)**:
   - Master bahan baku & produk.
   - Resep (Bill of Materials) untuk auto-deduct stok.
   - Stock Opname & Wastage (Bahan Rusak/Expired).
5. **Modul Shift & Cash Control**:
   - Input Modal Kas Awal & Hitung Uang Fisik Kas Akhir.
   - Audit Selisih Kasir.
6. **Modul Otorisasi & Anti-Fraud**:
   - PIN Supervisor untuk Void/Refund & Diskon Manual.
7. **Modul Laporan & Dashboard Owner**:
   - Laporan Penjualan Harian/Bulanan.
   - Laporan Laba/Rugi & Menu Terlaris (Best Seller).
   - Export PDF & Excel.

---

## 4. Rencana Pengembangan (Roadmap Sprints)
- **Sprint 1**: Analisis Kebutuhan (Wawancara Master Cafe), Database Migration & Refactoring Core Auth/RBAC.
- **Sprint 2**: Modul POS Kasir, Table Management, & Integration Multi-Payment.
- **Sprint 3**: Modul Multi-Printer Router / Kitchen Display & BOM Inventory Engine.
- **Sprint 4**: Modul Shift Kasir, Security Void PIN, & Dashboard Analytics Owner.
- **Sprint 5**: Testing, UAT di Master Cafe Bengkalis, & Penyusunan Dokumen Skripsi/TA.
