# Catatan Branch yang Belum Di-Merge

Dokumen ini berisi daftar branch fitur yang sudah selesai dikerjakan dan teruji di lokal,
tetapi **belum di-merge ke `master`** dan belum di-deploy ke server produksi.

---

## 1. `feature/realtime-websockets` — WebSocket Real-Time (Laravel Reverb)

**Status:** ✅ Selesai & Teruji di Lokal  
**Tanggal Selesai:** 5 September 2026  
**Deskripsi:**  
Mengganti metode HTTP Polling (Javascript `setInterval` 10 detik) dengan koneksi WebSocket
persisten menggunakan **Laravel Reverb**. Kasir akan menerima notifikasi pesanan baru secara
instan (real-time) tanpa membebani server.

### File yang Berubah
| File | Perubahan |
|---|---|
| `app/Events/PesananBaru.php` | **[NEW]** Event broadcast ke channel `kasir-notifications` |
| `app/Http/Controllers/OrderController.php` | Dispatch event setelah pesanan dibuat |
| `resources/views/layouts/kasir.blade.php` | Echo listener menggantikan setInterval |
| `resources/js/echo.js` | Konfigurasi Laravel Echo untuk Reverb |
| `resources/js/bootstrap.js` | Import echo.js |
| `config/broadcasting.php` | Konfigurasi broadcasting Reverb |
| `routes/channels.php` | Route channels bawaan Laravel |
| `public/manifest.json` | Perbaikan path logo PWA |
| `public/sw.js` | Perbaikan path logo PWA |
| `.env` | Variabel REVERB_* dan VITE_REVERB_* |

### Cara Merge
```bash
git checkout master
git merge feature/realtime-websockets
git push origin master
```

### Cara Menjalankan di Server (Setelah Merge & Deploy)
Server Reverb membutuhkan proses daemon yang berjalan terus-menerus di background.
Di cPanel, gunakan **Supervisor** atau **Cron Job** untuk menjalankan:
```bash
php artisan reverb:start
```

### Catatan Penting
- Pastikan port `8080` (atau port yang dikonfigurasi) tidak diblokir oleh firewall server.
- Untuk server produksi, ubah `REVERB_SCHEME` dari `http` ke `https` dan sesuaikan
  `REVERB_HOST` dengan domain Anda.
- Variabel `VITE_REVERB_*` di `.env` produksi harus mengarah ke domain publik,
  bukan `localhost`.