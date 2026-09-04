# Hasil Wawancara / Observasi Pemilik Master Cafe

Dokumen ini berisi transkrip jawaban resmi dari pemilik Master Cafe terkait kebutuhan operasional kafe. Data ini menjadi landasan (latar belakang masalah) utama pengembangan aplikasi kasir (POS) ini.

## 1. Kendala Operasional Harian
**Pertanyaan:** Apa kendala terbesar yang paling sering mengganggu kelancaran pesanan dari meja pelanggan hingga ke dapur?
**Jawaban Pemilik:** Tidak Ada
**Implikasi Sistem:** Fitur pemesanan mandiri (self-service) akan semakin mempercepat alur pesanan dari meja ke dapur yang saat ini sudah berjalan lancar, tanpa perlu campur tangan pelayan.

## 2. Keuangan & Kasir (Shift)
**Pertanyaan:** Apakah pernah atau sering terjadi selisih antara total catatan penjualan dengan uang fisik yang ada di laci kasir saat tutup warung?
**Jawaban Pemilik:** Sering terjadi salah perhitungan
**Implikasi Sistem:** Sistem wajib memiliki laporan penjualan otomatis dan rekap uang masuk/keluar (buku kas) yang presisi untuk menghindari *human error* dan mencegah kebocoran dana.

## 3. Pemantauan Stok Bahan Baku
**Pertanyaan:** Bagaimana cara Bapak/Ibu mengecek sisa bahan baku? Apakah karyawan pernah lupa mencatat sehingga bahan tiba-tiba habis saat jam sibuk?
**Jawaban Pemilik:** Mengecek manual & karyawan sering kali lupa untuk mencatat barang habis
**Implikasi Sistem:** Sistem wajib memiliki fitur Manajemen Stok (Inventaris) yang terintegrasi. Saat pesanan dibuat, stok bahan baku harus berkurang secara otomatis.

## 4. Efisiensi Pembayaran
**Pertanyaan:** Bagaimana cara kasir mencatat dan memverifikasi pembayaran digital (seperti QRIS atau transfer)?
**Jawaban Pemilik:** QRIS & TF sudah muncul sendiri di Aplikasi Kasir
**Implikasi Sistem:** Pemilik menginginkan integrasi digital. Namun untuk skripsi, fitur ini akan diakali dengan metode unggah bukti bayar (manual verification) oleh kasir agar sistem berjalan tanpa potongan biaya admin pihak ketiga.

## 5. Solusi Pemesanan Mandiri (Self-Order)
**Pertanyaan:** Jika ada sistem di mana pelanggan bisa memesan dan langsung membayar sendiri dari meja (via Scan QR), apakah menurut Bapak/Ibu itu akan sangat membantu?
**Jawaban Pemilik:** Ya, Sangat membantu
**Implikasi Sistem:** Ini memvalidasi fokus utama skripsi: "Fitur Self-Service" wajib dibangun dan diutamakan.

## 6. Harapan Terhadap Sistem Baru
**Pertanyaan:** Fitur atau jenis laporan seperti apa yang paling Bapak/Ibu butuhkan?
**Jawaban Pemilik:** Fitur yg lengkap dengan uang masuk, uang keluar dan tersedia fitur stock barang
**Implikasi Sistem:** 
- Laporan Pemasukan & Pengeluaran (Buku Kas).
- Fitur Manajemen Stok Bahan Baku/Barang.