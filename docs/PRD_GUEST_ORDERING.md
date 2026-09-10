# 📄 Product Requirements Document (PRD)
## Master Cafe Smart Self-Ordering System (Sistem Pemesanan Mandiri Cerdas)
**Proyek:** Master Cafe Smart Self-Ordering System  
**Versi:** 2.2 (Guest Self-Ordering, Hybrid Waitress POS, Live Tracking & Midtrans Gateway)  
**Status:** Disetujui & Terimplementasi Berjalan  
**Tanggal:** 10 September 2026  

---

## 1. Latar Belakang & Alasan Bisnis (Business Rationale)

### 🚀 3 Alasan Utama Pembaharuan Sistem:
1. **Efisiensi Waktu Konsumen:** Memangkas durasi pemesanan karena konsumen cenderung menghabiskan waktu cukup lama dalam memilih makanan. Dengan menu digital interaktif di HP, konsumen bebas bereksplorasi tanpa merasa canggung.
2. **Penghematan Biaya Operasional (Cost Saving):** Mengurangi pengeluaran biaya tenaga kerja (waitress & kasir) karena alur pemesanan dan verifikasi pembayaran non-tunai telah otomatis ditangani oleh sistem.
3. **Keleluasaan Tambah Pesanan (Re-Order Freedom):** Memberikan ruang dan kebebasan yang lebih luas kepada konsumen jika ingin menambah pesanan kopi/makanan kapan saja tanpa harus menunggu atau mencari pelayan.

### Masalah Operasional Lama:
1. **Friction Tinggi (Beban Mental):** Konsumen wajib registrasi/login sebelum melihat menu.
2. **Antrean Kasir & Efisiensi Meja:** Konsumen mengantre lama di kasir hanya untuk membayar.
3. **Pemeriksaan Mutasi Manual:** Kasir harus mengecek foto bukti transfer satu per satu secara manual.

---

## 2. Tujuan & Sasaran (Goals & Objectives)

1. **Pemesanan Meja Super Cepat (Zero-Friction):** Konsumen menyelesaikan pesanan dalam waktu kurang dari **45 detik** dari scan QR meja tanpa perlu membuat akun/password.
2. **Pembayaran Otomatis dengan Midtrans (Auto-Settlement):** Verifikasi pembayaran instan via QRIS Dinamis & Virtual Account (VA) tanpa perlu kasir fisik.
3. **Antrean Masuk Setelah Lunas:** Pesanan otomatis masuk ke antrean **Tablet Waitress / Station Pelayan** begitu pembayaran terverifikasi otomatis oleh Midtrans.
4. **Sistem Pemesanan Hibrida (Hybrid Support):** Pelayan / Waitress dapat mengisikan pesanan secara manual via Tablet Waitress (`/kasir/pos`) untuk konsumen lansia, walk-in, atau konsumen yang HP-nya bermasalah.
5. **Pelacakan Live Real-Time Tanpa Login:** Konsumen memantau progres pesanannya (*Diterima &rarr; Lunas &rarr; Dimasak &rarr; Siap &rarr; Selesai*) via WebSocket Reverb & LocalStorage HP (TTL 12 jam).
6. **Penyatuan Portal Login Staff:** Rute `/login` dikhususkan sebagai satu-satunya pintu resmi untuk Karyawan (Admin, Waitress, Barista, Chef).
7. **Refactoring Nomenklatur Waitress:** Seluruh istilah antarmuka lantai kafe 100% dialihkan ke istilah **Waitress / Tablet Waitress** untuk menciptakan diferensiasi peran yang jelas.
8. **Akses Menu Super Smooth & Mulus (< 1 Detik Loading Time):** Pengalaman membuka menu dibuat sangat mulus (*smooth transition 60fps*), tanpa lag di HP Android/iOS.

---

## 3. Alur Pemesanan Utama (Core Hybrid Order Flow)

### 🍽️ Alur A: Self-Service QR Code (Dine-In Pay-First Flow Utama)
```
[LANGKAH 1]
Konsumen memindai (scan) Barcode QR di atas meja → Terbuka tampilan website menu meja → Pilih menu & konfirmasi pesanan (input Nama Pemesan)
                   ↓
[LANGKAH 2]
Konsumen langsung melakukan pembayaran di HP via QR (QRIS Dinamis) atau Transfer Virtual Account (Midtrans Payment Gateway)
                   ↓
[LANGKAH 3]
Sistem mendeteksi pembayaran telah LUNAS (Auto-Settlement) → Pesanan otomatis masuk ke dalam Antrean Pesanan pada Tablet Waitress / Station Pelayan (Lonceng Audio 🔔)
                   ↓
[LANGKAH 4]
Waitress mengonfirmasi pesanan di Tablet Waitress, mencetak struk pesanan (tiket dapur), dan menyerahkannya kepada Chef / Barista
                   ↓
[LANGKAH 5]
Setelah makanan selesai dimasak → Waitress mengantarkan pesanan langsung ke meja konsumen (Selesai ✅)
```
*(Catatan: Kebijakan 100% Wajib Bayar Lunas di Depan (Pay-First Policy) diberlakukan secara penuh via Midtrans QRIS/VA untuk membebaskan kafe dari risiko pesanan tidak dibayar).*

### 📋 Alur B: Manual Order via Tablet Waitress (Pemesanan oleh Staff untuk Konsumen Non-HP)
```
[Konsumen Mendatangi Kasir/Waitress atau Pelayan Datang ke Meja]
                   ↓
[Waitress Membuka Screen Tablet POS di /kasir/pos]
                   ↓
[Pilih Nomor Meja → Pilih Item Makanan & Minuman]
                   ↓
[Pilih Metode Bayar: Tunai (Cash) atau QRIS Manual]
                   ↓
[Klik "Simpan Pesanan" → Tiket Dapur Terintegrasi & Meja Ditandai Terisi (Merah)]
```

### 🛍️ Alur C: Bawa Pulang (Takeaway via Web Luar / Pre-Order)
```
[Buka Website / Pilih Mode Bawa Pulang (Takeaway)]
                   ↓
[Pilih Menu & Masukkan ke Keranjang]
                   ↓
[Checkout: Isi Nama & No. WhatsApp]
                   ↓
[Wajib Bayar Lunas: Midtrans QRIS / GoPay / ShopeePay / VA]
                   ↓
[Webhook Midtrans Terverifikasi LUNAS (Detik itu juga)]
                   ↓
[Terbit Nomor Antrean #TK-xx & Layar Live Tracking]
                   ↓
[Dapur Mulai Memasak Otomatis → Konsumen Tinggal Ambil di Cafe]
```

---

## 4. Spesifikasi Fitur Utama

### 4.1. Akses Menu Meja Langsung (Direct Table Access)
- **Mekanisme:** QR Code di meja berisi URL tertandatangan (*signed URL*) atau ID meja, contoh: `/meja/{id_meja}`.
- **Tampilan:**
  - Header meja: *"Meja 03 - Master Cafe"*.
  - Indikator jam buka/tutup minimalis di sudut atas: `🟢 Buka (16.00 - 23.00)`.
  - Daftar katalog menu lengkap dengan foto, harga, dan opsi varian.

### 4.2. Formulir Checkout Cepat (Strict Pay-First)
- **Nomor Meja:** Terkunci otomatis sesuai QR yang dipindai (untuk Dine-In).
- **Nomor Antrean:** Terbit otomatis format `#TK-xx` (untuk Takeaway).
- **Nama Pemesan:** Wajib (misal: "Budi").
- **No. WhatsApp:** Wajib untuk takeaway web, opsional untuk meja.
- **Pilihan Metode Bayar:** *Midtrans QRIS Dinamis & Virtual Account (Auto-Settlement Lunas di Depan)*.

### 4.3. Integrasi Midtrans Payment Gateway
- **Engine:** Menggunakan package `midtrans/midtrans-php` (sudah terpasang di project).
- **Mekanisme Snap QRIS Dinamis:**
  1. Saat memilih QRIS, sistem meminta Snap Token ke API Midtrans sesuai nominal tagihan.
  2. Muncul pop-up / QRIS dinamis di layar HP konsumen.
  3. Konsumen membayar dengan m-Banking (BCA, Mandiri, BRI, dll.) atau E-Wallet (GoPay, OVO, ShopeePay, Dana).
  4. **Webhook Handler (`/api/midtrans/webhook`):**
     - Menerima notifikasi status `settlement`.
     - Otomatis mengubah status pesanan dari `unpaid` &rarr; `paid`.
     - Mengirim sinyal WebSocket Reverb ke layar Dapur & Waitress untuk mencetak tiket dan membunyikan lonceng (*audio chime*).
     - Layar HP konsumen berganti detik itu juga ke status: *Pembayaran Berhasil! Pesanan Sedang Dimasak*.
- **Lingkungan Pengujian Sandbox (Uji Coba Gratis):**
  - Pada tahap pengembangan lokal, sistem wajib dikonfigurasi dalam mode **Midtrans Sandbox** (`midtrans_is_production = 0`).
  - Mode *Production* diaktifkan dari menu Setting Admin dengan mengisi Server Key & Client Key asli.

### 4.4. Mekanisme Pelacakan Live Tanpa Akun (Live Order Tracking)
1. **URL Pesanan Unik (Secure Order Token):**
   - Setiap pesanan memiliki URL pelacakan acak aman, contoh:  
     `https://mastercafe.nadeak.net/tracking/ORD-20260909-X9A2`
2. **Penyimpanan Browser Lokal (LocalStorage HP) & Batas Waktu (TTL 12 Jam):**
   - Browser HP menyimpan data pesanan aktif (`order_token` & `id_meja`).
   - Data di HP konsumen otomatis dibersihkan saat pesanan berstatus **✅ Selesai (Completed)** atau maksimal **12 jam** sejak pemesanan.
3. **Status Real-time (Websocket / Reverb):**
   - Status di layar HP berganti live tanpa reload:
     - 🟡 **Menunggu Pembayaran**
     - 👨‍🍳 **Sedang Dimasak oleh Dapur**
     - ☕ **Siap Diantar ke Meja Anda**
     - ✅ **Selesai (Completed)**

### 4.5. Tombol Panggil Pelayan Digital (Table Call Bell)
- **Terikat ke Nomor Meja:** Pelayan langsung tahu meja mana yang memanggil tanpa butuh data akun konsumen.
- **Pilihan 1 Sentuhan:** 🙋 *Panggil Pelayan*, 🧻 *Minta Tisu / Asbak*, 🧾 *Minta Bill*.
- **Anti-Spam Cooldown:** Tombol terkunci 2 menit setelah ditekan.
- **Notifikasi Waitress:** Layar POS Waitress membunyikan lonceng (*audio chime*) dan memunculkan pop-up instan.

### 4.6. Struk Digital & Ulasan Pasca-Pesanan
- **Struk Digital (E-Receipt):** Setelah pesanan selesai, konsumen dapat mengunduh bukti transaksi (PDF/gambar) via tombol **[📥 Simpan Struk Digital]**.
- **Rating & Ulasan:** Di layar tracking muncul kartu umpan balik bintang 1–5 sehingga konsumen bisa langsung memberi review dan review langsung tersimpan ke dashboard toko.

---

## 5. Penataan Arsitektur Autentikasi & Nomenklatur Peran

| Komponen | Kondisi Lama | Penyesuaian Baru |
|---|---|---|
| **`/login`** | Dipakai konsumen umum | Portal login tunggal untuk **Staff / Karyawan** |
| **Penyebutan Peran Staf** | Kasir | **Waitress / Tablet Waitress** |
| **Modul Backend POS** | `Kasir*.php` controllers & views | `Waitress*.php` (Meja, Stok, Pengeluaran, Shift) |
| **Rute Konsumen** | Wajib `['auth', 'role:konsumen']` | Dibuka untuk umum (*public guest*) |
| **Profil Konsumen** | Edit nama, avatar, & password | **Dihapus total** (konsumen tidak memiliki akun) |
| **Tabel `pesanan`** | `id_konsumen` wajib terisi akun user | `id_konsumen` `nullable()`, ditambah `guest_name`, `order_token` |
| **Payment Gateway** | Belum terhubung otomatis | **Terintegrasi Midtrans (Snap QRIS & Webhook)** |

---

## 6. Skenario Operasional & Protokol Pemeliharaan

### 6.1. Pengelolaan Meja & Mencegah Meja Kabur
1. 🟡 **Pengingat Warna Meja di Layar POS Waitress:**
   - Di layar Tablet Waitress, meja yang belum membayar akan menyala warna **Kuning Terang** mencolok.
2. 💳 **Kemudahan Bayar QRIS Mandiri dari HP Meja:**
   - Konsumen bisa bayar dari meja via Midtrans QRIS. Begitu lunas, speaker Tablet Waitress membunyikan lonceng 🔔 dan warna meja berganti **🔵 Biru (LUNAS ✅)**.
3. 📋 **Alur Input Manual untuk Non-HP:**
   - Waitress siap melayani input pesanan manual dari tablet jika ada tamu yang membutuhkan bantuan.

### 6.2. Protokol Pemeliharaan Cache & Stabilitas
Pasca update kode atau perubahan file Blade/Route, wajib diisolasi dengan pembersihan cache berikut agar tidak memicu error kompilasi view (*ViewNotFoundException*):
```bash
php artisan optimize:clear
```

---

## 7. Standar Sistem Desain & Identitas Visual (Official Dark Bronze Design System)

Seluruh antarmuka aplikasi mematuhi standar desain baku yang ditetapkan pada `docs/DESIGN_SYSTEM.md`:

### 🎨 7.1. Palet Warna Utama (Dark Bronze Palette):
- **Background Utama (Level 0):** `#0e1217` (Hitam Gelap Elegan).
- **Surface & Kartu (Level 1):** `#161b22` (Gelap Timbul Kontras).
- **Border & Pembatas (Level 2):** `#21262d` (Garis Batas Halus 1px).
- **Aksen Perunggu (Bronze Gradient):** `linear-gradient(135deg, #986c43 0%, #c08e5c 100%)`.
- **Teks Utama & Redup:** `#ffffff` (Putih Murni) & `#a0aab2` (Abu-abu Kebiruan).

### ✒️ 7.2. Tipografi Baku:
- **Rye (Serif):** Judul utama & elemen branding.
- **Alex Brush (Cursive) & Caveat:** Sentuhan artistik logo & slogan.
- **Outfit / Plus Jakarta Sans:** Teks badan, daftar menu, tabel harga, & UI operasional.

### 📐 7.3. Grid 8-Point & Target Sentuh:
- Seluruh margin, padding, border-radius (16px), dan ukuran tombol menggunakan kelipatan **8px**.
- Ukuran tombol interaktif minimal **40px - 48px** agar mudah dan akurat ditekan dengan jari pada layar tablet/smartphone.

---

## 8. Ringkasan Status Tahapan (Milestones)

- [x] **Fase 1 (Database & Migrasi):** `guest_name` dan `order_token` pada tabel `pesanan` aktif.
- [x] **Fase 2 (Rute Publik & Guest Self-Service):** Rute `/konsumen/menu/{id_meja}` tanpa login aktif.
- [x] **Fase 3 (Integrasi Midtrans Snap & Webhook):** Auto-settlement QRIS & VA aktif.
- [x] **Fase 4 (Live Order Tracking & Table Bell):** Pelacakan real-time TTL 12 jam & tombol panggil pelayan aktif.
- [x] **Fase 5 (Refactoring Waitress & Staff Portal):** Seluruh nomenklatur & rute Kasir dialihkan ke **Waitress / Tablet Waitress**.
- [x] **Fase 6 (Hybrid POS & Cache Clearance):** Input manual via POS Waitress & pembersihan cache kompilasi tuntas.
