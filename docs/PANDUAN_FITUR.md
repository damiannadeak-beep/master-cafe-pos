# Hasil Implementasi: Pembayaran QRIS Manual (Upload Bukti)

Fitur utama *Self-Service* Anda kini telah terpasang dengan sempurna di dalam *branch* `fitur-qris-manual`. Sistem tidak lagi bergantung pada Midtrans, melainkan menggunakan logika verifikasi manual oleh kasir untuk memastikan 0% potongan biaya admin pihak ketiga.

## Daftar Perubahan:
1. **Database:** Tabel `pembayaran` kini memiliki kolom `bukti_bayar` untuk menyimpan tautan gambar (path) yang diunggah pelanggan.
2. **Halaman Konsumen (Checkout):** 
   - Halaman `checkout.blade.php` telah dirancang ulang sepenuhnya.
   - Menampilkan gambar QRIS statis di bagian tengah.
   - Menyediakan fitur *drag-and-drop* atau klik untuk mengunggah gambar bukti struk.
   - Menggunakan validasi batas ukuran file (Maksimal 2MB) agar server (*cPanel*) tidak cepat penuh.
3. **Logika Backend (PaymentController):** 
   - Fungsi `uploadBukti()` baru akan mengompres dan meletakkan foto ke folder aman (`storage/app/public/bukti_bayar`), lalu mengganti status pembayaran menjadi `pending_verification`.
   - Mengirim notifikasi otomatis ke *bell* kasir.
4. **Halaman Kasir (Pesanan Aktif):** 
   - Apabila ada pesanan dengan status `pending_verification`, layar kasir akan memunculkan tombol peringatan berwarna oranye: **"Cek Bukti Bayar"**.
   - Ketika tombol ditekan, sebuah *pop-up* moderen akan muncul membesarkan foto kiriman pelanggan.
   - Kasir memiliki kuasa penuh untuk menekan **Valid & Terima** (uang masuk ke buku kas) atau **Tolak** (jika struk palsu).

---

## 🎯 Panduan Uji Coba (Simulasi End-to-End)

Inilah waktunya menguji secara nyata karya Anda! Silakan ikuti langkah simulasi 2 peran ini di *browser* komputer Anda:

### Peran 1: Sebagai Pelanggan
1. Buka *browser*, lalu masuk/login sebagai **Konsumen**.
2. Masukkan beberapa menu ke keranjang dan lakukan pemesanan dari meja.
3. Pada halaman pembayaran, cobalah unggah gambar apa saja dari komputer Anda (anggap sebagai foto struk M-Banking).
4. Klik **Kirim Bukti Pembayaran**. Status pesanan Anda akan berubah menjadi *Menunggu Verifikasi Kasir*.

### Peran 2: Sebagai Kasir
1. Buka tab baru di *browser* (atau *Incognito Window*), lalu *login* sebagai **Kasir/Admin**.
2. Buka menu **POS Kasir -> Pesanan Aktif**.
3. Cari pesanan Anda tadi. Anda akan melihat tombol baru **"Cek Bukti Bayar"**.
4. Klik tombol tersebut. Pastikan foto yang diunggah pelanggan muncul jelas.
5. Klik **"Valid & Terima Pembayaran"**.
6. Amati apakah status pesanan langsung berubah menjadi "Dibayar" dan struk siap dicetak ke Dapur!

> [!TIP]
> Jika uji coba ini berhasil mulus 100%, beri tahu saya agar kita bisa langsung menggabungkannya ke sistem utama (Master) dan membuang *branch* sementaranya!
