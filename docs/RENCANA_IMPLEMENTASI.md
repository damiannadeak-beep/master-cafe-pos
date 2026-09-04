# Implementasi Fitur Pembayaran QRIS Manual (Upload Bukti)

Berdasarkan keputusan pemilik kafe, kita akan membangun sistem pemesanan mandiri dengan metode validasi manual agar Master Cafe tidak perlu membayar potongan biaya admin pihak ketiga (Midtrans/Xendit). 

## Gambaran Alur (Workflow)
1. Pelanggan memesan via HP -> masuk ke halaman *Checkout*.
2. Halaman *Checkout* menampilkan *Barcode* QRIS Statis milik Master Cafe.
3. Pelanggan memindai QRIS tersebut menggunakan *m-banking*, lalu mengunggah (*upload*) *screenshot* bukti transfernya.
4. Kasir melihat pesanan masuk dengan status "Menunggu Verifikasi" dan bisa mengklik tombol untuk melihat foto struk yang diunggah pelanggan.
5. Kasir memvalidasi foto tersebut, lalu menekan "Terima Pembayaran" (pesanan lunas & masuk antrean dapur).

## Open Questions
> [!IMPORTANT]
> 1. **Gambar QRIS Utama:** Di *database* sebelumnya, saya melihat ada *file* QRIS yang sempat Anda *upload* (dengan nama acak `E7fb...jpg`). Apakah kita akan menggunakan gambar tersebut sebagai QRIS Statis sementaranya?
> 2. **Notifikasi Kasir:** Saat ini kasir harus melakukan *refresh* (segarkan halaman) atau melihat lonceng notifikasi untuk tahu ada pesanan masuk. Apakah ini sudah cukup untuk saat ini?

---

## Proposed Changes

### 1. Database Migration
Menambahkan kolom baru di tabel `pembayaran` untuk menyimpan nama *file* gambar struk.
#### [NEW] database/migrations/xxxx_add_bukti_bayar_to_pembayaran_table.php
- Menambahkan kolom `bukti_bayar` (string, nullable) dan mengubah status default untuk menampung `pending_verification`.

### 2. Antarmuka Konsumen (UI)
Merombak halaman *checkout* agar tidak menggunakan Midtrans.
#### [MODIFY] resources/views/konsumen/checkout.blade.php
- Menghapus semua baris kode Midtrans.
- Menambahkan gambar QRIS besar di tengah halaman.
- Menambahkan form *upload* gambar khusus untuk bukti transfer dengan tampilan premium.

### 3. Logika Backend (Controller Konsumen)
Menangani *file upload* dari konsumen.
#### [MODIFY] routes/web.php
- Menambah *route* POST untuk `konsumen/order/{id_pesanan}/upload-bukti`.
#### [MODIFY] app/Http/Controllers/PaymentController.php
- Membuat fungsi `uploadBukti()` yang bertugas mengompres dan menyimpan gambar ke folder `/storage/app/public/bukti_bayar/`.

### 4. Antarmuka Kasir (UI)
Memberikan kemampuan kasir untuk melihat foto struk pelanggan.
#### [MODIFY] resources/views/components/kasir/active-order-card.blade.php
- Jika pesanan berstatus *pending verification*, tombol "Bayar" berubah menjadi warna oranye berbunyi "Cek Bukti Bayar".
#### [MODIFY] resources/views/components/kasir/payment-modal.blade.php
- Menampilkan foto struk ukuran besar di dalam *pop-up* (modal) kasir agar mudah dicek mata.
- Menambahkan tombol hijau "Valid & Lunas" dan tombol merah "Tolak / Struk Palsu".

### 5. Logika Kasir (Controller Kasir)
#### [MODIFY] app/Http/Controllers/PosController.php
- Menambah logika untuk menerima pembayaran yang diverifikasi manual.

---

## Verification Plan

### Manual Verification
1. Kita akan menyimulasikan diri sebagai pelanggan yang memesan menu dari meja.
2. Kita akan mengunggah gambar sembarang (sebagai bukti transfer palsu).
3. Kita akan masuk ke akun Kasir dan mengecek apakah gambar tersebut muncul di layar kasir.
4. Kasir memencet tombol "Valid", dan kita cek apakah struk dapur tercetak dan pendapatan kafe bertambah.

---

## 🔮 Rencana Masa Depan (Opsi Tambahan)

Jika di kemudian hari pemilik Master Cafe memutuskan ingin menggunakan pembayaran **Sistem Otomatis (Payment Gateway)** agar kasir tidak perlu lagi repot mengecek foto secara manual, kita sepakat untuk menggunakan **Tripay**. 

### Persyaratan Integrasi Tripay:
- Menyiapkan KTP Asli dan Foto *Selfie* dengan KTP.
- Rekening Bank yang sama persis dengan nama di KTP.
- Memastikan aplikasi Master Cafe POS sudah berstatus *Online* (berjalan di server/hosting publik).

### Langkah Teknis (Jika Dijalankan Nanti):
1. **Pendaftaran:** Membuat akun Tripay sebagai Merchant Perorangan (UMKM).
2. **Library Backend:** Menginstal SDK/Library Tripay via Composer (composer require).
3. **Database:** Menambah kolom 	ripay_reference pada tabel pembayaran.
4. **Checkout URL:** Mengubah fungsi checkout() agar saat pelanggan klik "Bayar", sistem "menembak" API Tripay dan memunculkan *barcode* QRIS dinamis.
5. **Webhook/Callback:** Membuat rute Route::post('/tripay/callback') agar server Tripay bisa melapor balik ke aplikasi kita bahwa *"Uang sudah masuk otomatis"*.