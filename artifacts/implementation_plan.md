# Implementasi Fitur Lupa Kata Sandi (Forgot Password)

Ide yang sangat bagus! Fitur Lupa Sandi adalah salah satu standar wajib dalam sistem informasi modern agar pengguna (terutama konsumen) tidak kehilangan akses ke akun mereka.

Karena kita menggunakan Laravel, sistem dasar (*back-end*) untuk me-reset *password* sebenarnya sudah tersedia, kita hanya perlu memoles tampilannya dan mengonfigurasi jalur pengiriman emailnya.

## Open Questions

> [!IMPORTANT]
> **Metode Pengiriman Email**
> Untuk mengirimkan *link reset password*, sistem membutuhkan sebuah layanan email (SMTP). Ada dua pendekatan yang bisa kita lakukan saat ini:
> 1. **(Mode Simulasi - Disarankan untuk tes lokal saat ini):** Sistem pura-pura mengirim email, tapi *link reset password*-nya sebenarnya dicetak ke dalam *file log* lokal (`storage/logs/laravel.log`). Sangat cepat untuk keperluan tes tanpa ribet pengaturan email asli.
> 2. **(Mode Produksi - Wajib untuk cPanel nanti):** Sistem menggunakan akun Gmail/Webmail Anda (misal Gmail SMTP) untuk benar-benar mengirimkan email berisi *link reset* ke kotak masuk pelanggan.
>
> **Pertanyaan:** Apakah kita mulai dari Mode Simulasi dulu untuk membuktikan tampilannya berjalan? Ataukah Anda ingin langsung mensetting akun Gmail asli sekarang juga?

## Proposed Changes

---

### Tampilan (*Views*)

Saya akan merombak tampilan bawaan Laravel yang masih putih polos menjadi tampilan premium *dark mode* dengan aksen *gold* yang sama seperti halaman Login dan Register kita.

#### [MODIFY] [resources/views/auth/login.blade.php](file:///C:/xampp/htdocs/master-cafe-pos/resources/views/auth/login.blade.php)
- Menambahkan tombol atau tautan "Lupa Kata Sandi?" yang mengarah ke halaman reset.

#### [MODIFY] [resources/views/auth/passwords/email.blade.php](file:///C:/xampp/htdocs/master-cafe-pos/resources/views/auth/passwords/email.blade.php)
- Merombak halaman input alamat email pengguna yang lupa *password* menggunakan desain *glassmorphism* dan warna `#161b22`.

#### [MODIFY] [resources/views/auth/passwords/reset.blade.php](file:///C:/xampp/htdocs/master-cafe-pos/resources/views/auth/passwords/reset.blade.php)
- Merombak halaman pembuatan kata sandi baru (tempat pengguna memasukkan token dari email dan *password* baru) agar berdesain premium.

---

### Konfigurasi (*Environment*)

#### [MODIFY] [.env](file:///C:/xampp/htdocs/master-cafe-pos/.env)
- Mengonfigurasi `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, dan kredensial email lainnya (bergantung pada jawaban Anda pada *Open Questions* di atas).

## Verification Plan

### Manual Verification
1. Kita akan mencoba menekan tombol "Lupa Password" dari halaman Login.
2. Memasukkan alamat email salah satu konsumen uji coba.
3. Memastikan email/simulasi pengiriman *link* berhasil tanpa pesan *error*.
4. Mengklik *link reset* dan mencoba menyetel *password* baru.
5. Mencoba *login* menggunakan *password* yang baru saja direset.
