# Checklist Implementasi Fitur Lupa Sandi

- [x] Membuat *branch* baru (`fitur-lupa-sandi`)
- [x] Mengubah pengaturan `.env` agar menggunakan simulasi pengiriman email (`MAIL_MAILER=log`)
- [x] Menambahkan *link* "Lupa Password?" pada desain halaman Login Konsumen (`login.blade.php`)
- [x] Merombak desain tampilan *form* permintaan reset password (`email.blade.php`)
- [x] Merombak desain tampilan *form* kata sandi baru (`reset.blade.php`)
- [x] Minta user menguji alur simulasi secara *end-to-end* (Minta reset -> Cek Log -> Klik Link -> Set Password Baru -> Sukses Login)
