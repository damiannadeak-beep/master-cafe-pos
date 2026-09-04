# Simulasi Perbandingan Biaya Sistem Pembayaran (Payment Gateway)

Dokumen ini disusun sebagai bahan analisis untuk Skripsi dan pertimbangan bisnis bagi Master Cafe dalam memilih metode pembayaran mandiri (Self-Service).

## ðŸ“Š Asumsi Data Transaksi Master Cafe
- **Rata-rata tagihan 1 meja:** Rp 50.000
- **Estimasi transaksi QRIS per hari:** 50 meja
- **Total Omzet Digital Sehari:** Rp 2.500.000
- **Total Omzet Digital Sebulan (30 Hari):** Rp 75.000.000

---

## 1. Skenario A: Sistem API Otomatis (Midtrans / Tripay)
Model bisnis ini menggunakan API resmi. Pendaftaran gratis dan tidak ada biaya bulanan, namun mengenakan tarif potongan per transaksi (MDR QRIS) sebesar **0,7%**.

### Perhitungan Biaya (Kerugian Potongan):
- **Potongan per 1 Meja:** 0,7% x Rp 50.000 = **Rp 350**
- **Potongan per Hari:** Rp 350 x 50 meja = **Rp 17.500**
- **Total Uang yang Terpotong (Sebulan):** Rp 17.500 x 30 hari = **Rp 525.000 / bulan**

*Kesimpulan:* Cocok untuk kafe yang baru buka dengan volume transaksi kecil. Namun jika kafe ramai, biaya potongan ini akan sangat membebani omzet bersih kafe.

---

## 2. Skenario B: Sistem Cek Mutasi Otomatis (Moota / MutasiKita)
Model bisnis ini menggunakan robot (RPA) untuk mengecek riwayat mutasi *Internet Banking* pemilik kafe secara otomatis.

### Perhitungan Biaya:
- **Biaya Langganan Sistem:** **Rp 150.000 / bulan (Flat/Tetap)**
- **Potongan per Transaksi:** **Rp 0** (Semua uang dari pelanggan utuh masuk ke rekening).

*Kesimpulan:* Sangat cocok untuk kafe yang omzetnya sudah besar. Berapapun jumlah pelanggannya, biayanya tetap Rp 150.000/bulan tanpa ada potongan persentase.

---

## 3. Skenario C: Sistem Manual (Upload Bukti Struk) - DIPILIH
Sistem yang saat ini diimplementasikan di Master Cafe. Pelanggan memindai QRIS bank kafe biasa, lalu mengunggah (*upload*) *screenshot* bukti transfer ke aplikasi. Kasir memvalidasinya secara manual di layar.

### Perhitungan Biaya:
- **Biaya Langganan Sistem:** **Rp 0 / bulan**
- **Potongan per Transaksi Sistem:** **Rp 0** (Tidak ada API pihak ketiga yang mengambil untung).

*Kesimpulan:* **Pilihan paling hemat (100% Gratis)**. Metode ini dipilih berdasarkan permintaan Master Cafe yang tidak menginginkan adanya pemotongan biaya admin dari pihak ketiga, sekaligus memaksimalkan peran kasir yang sudah berjaga.
---

## 🎓 Strategi Arsitektur Dual-Mode (Untuk Sidang Skripsi)

Untuk memfasilitasi kebutuhan **Akademis (Dosen)** yang menuntut kecanggihan teknologi dan kebutuhan **Riil Bisnis (Klien)** yang menolak biaya admin, aplikasi Master Cafe POS dirancang menggunakan arsitektur **Dual-Mode**:

1. **Mode Otomatis (Midtrans Sandbox - Untuk Demo Akademis):**
   Aplikasi mengintegrasikan API *Payment Gateway* menggunakan *Sandbox Environment* (Lingkungan Simulasi). Ini memungkinkan mahasiswa untuk mendemonstrasikan sistem verifikasi pembayaran otomatis yang canggih secara *real-time* kepada dosen penguji tanpa harus memotong uang sungguhan sepeser pun.
   
2. **Mode Manual (Upload Struk QRIS - Untuk Produksi/Implementasi Kafe):**
   Saat diaplikasikan langsung di Master Cafe, sistem diubah ke mode manual di mana pelanggan mengunggah foto struk transfer. Hal ini menjamin keuntungan kafe tetap utuh 100% tanpa ada potongan 0,7% dari pihak ketiga (Midtrans/Tripay), menyesuaikan dengan *pain point* utama yang dikeluhkan oleh UMKM tersebut.

Rancangan arsitektur ini menunjukkan kemampuan ganda mahasiswa: **Mampu mengintegrasikan API modern secara teknis**, sekaligus **mampu menganalisis dan beradaptasi terhadap batasan biaya operasional klien di dunia nyata**.