# Checklist Fitur Pembayaran QRIS Manual (Upload Bukti)

Berikut adalah daftar pekerjaan untuk merealisasikan sistem *Self-Service* dengan verifikasi pembayaran secara manual oleh kasir.

- [x] Membuat *branch* Git baru (`fitur-qris-manual`).
- [x] **Database:** Membuat *migration* untuk menambah kolom `bukti_bayar` pada tabel `pembayaran`.
- [x] **Database:** Menjalankan `php artisan migrate`.
- [x] **UI Konsumen:** Merombak `resources/views/konsumen/checkout.blade.php`.
    - [ ] Menghapus skrip Midtrans.
    - [ ] Menambahkan gambar QRIS statis di halaman utama.
    - [ ] Menambahkan *form upload file* (gambar struk transfer).
- [x] **Backend Konsumen:** Memperbarui `PaymentController.php`.
    - [ ] Membuat metode `uploadBukti()` untuk menyimpan gambar ke `storage/app/public/bukti_bayar`.
    - [ ] Mengubah status pembayaran menjadi `pending_verification`.
- [x] **UI Kasir:** Memperbarui komponen kasir.
    - [x] `active-order-card.blade.php`: Menambah tombol "Cek Bukti Bayar" jika status = `pending_verification`.
    - [x] Membuat modal (*pop-up*) untuk membesarkan foto bukti bayar pelanggan.
- [x] **Backend Kasir:** Memperbarui `PosController.php`.
    - [x] Membuat logika `verifyPayment()` (Terima / Tolak pembayaran).
- [x] **Uji Coba:** (Menunggu Anda) Melakukan simulasi secara *end-to-end* sebagai Konsumen dan Kasir.
