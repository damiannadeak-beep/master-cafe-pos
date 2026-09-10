# 📄 Product Requirements Document (PRD)
## Sistem Pemesanan Konsumen Tanpa Login & Integrasi Midtrans Payment Gateway
**Proyek:** Master Cafe POS  
**Versi:** 2.1 (Guest Ordering, Live Tracking & Midtrans Gateway)  
**Status:** Disetujui  
**Tanggal:** 9 September 2026  

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
4. **Pelacakan Live Real-Time Tanpa Login:** Konsumen memantau progres pesanannya (*Diterima &rarr; Lunas &rarr; Dimasak &rarr; Siap &rarr; Selesai*) via WebSocket Reverb & LocalStorage HP.
5. **Penyatuan Portal Login Staff:** Rute `/login` dikhususkan sebagai satu-satunya pintu resmi untuk Karyawan (Admin, Waitress, Barista, Chef).
6. **Akses Menu Super Smooth & Mulus (< 1 Detik Loading Time):** Pengalaman membuka menu dibuat sangat mulus (*smooth transition 60fps*), tanpa lag di HP Android/iOS.

---

## 3. Alur Pemesanan Utama (Core 5-Step Order Flow)

### 🍽️ Alur Resmi Pemesanan Meja (Dine-In Pay-First Flow):
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
Waitress mengonfirmasi pesanan di Tablet Waitress, mencetak struk pesanan (tiket dapur), dan menyerahkannya kepada Tukang Masak / Chef
                   ↓
[LANGKAH 5]
Setelah makanan selesai dimasak oleh Chef → Waitress mengantarkan pesanan langsung ke meja konsumen (Selesai ✅)
```

*(Catatan: Untuk pilihan bayar tunai di kasir tetap didukung sebagai opsi fleksibel jika disetujui Pemilik Kafe).*

### 🛍️ Alur B: Bawa Pulang (Takeaway via Web Luar / Pre-Order)
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

*(Catatan: Untuk pembeli bungkus yang datang langsung ke meja kasir / walk-in, tetap bisa memesan secara langsung ke kasir seperti biasa).*

---

## 4. Spesifikasi Fitur Utama

### 4.1. Akses Menu Meja Langsung (Direct Table Access)
- **Mekanisme:** QR Code di meja berisi URL tertandatangan (*signed URL*) atau ID meja, contoh: `/meja/{id_meja}`.
- **Tampilan:**
  - Header meja: *"Meja 03 - Master Cafe"*.
  - Indikator jam buka/tutup minimalis di sudut atas: `🟢 Buka (16.00 - 23.00)`.
  - Daftar katalog menu lengkap dengan foto, harga, dan opsi varian.

### 4.2. Formulir Checkout Cepat
- **Nomor Meja:** Terkunci otomatis sesuai QR yang dipindai (untuk Dine-In).
- **Nomor Antrean:** Terbit otomatis format `#TK-xx` (untuk Takeaway).
- **Nama Pemesan:** Wajib (misal: "Budi").
- **No. WhatsApp:** Wajib untuk takeaway web, opsional untuk meja.
- **Pilihan Metode Bayar:**
  - *Midtrans QRIS / E-Wallet (Auto-Settlement)*.
  - *Bayar Tunai di Kasir / Open Bill* (khusus Dine-In).

### 4.3. Integrasi Midtrans Payment Gateway
- **Engine:** Menggunakan package `midtrans/midtrans-php` (sudah terpasang di project).
- **Mekanisme Snap QRIS Dinamis:**
  1. Saat memilih QRIS, sistem meminta Snap Token ke API Midtrans sesuai nominal tagihan.
  2. Muncul pop-up / QRIS dinamis di layar HP konsumen.
  3. Konsumen membayar dengan m-Banking (BCA, Mandiri, BRI, dll.) atau E-Wallet (GoPay, OVO, ShopeePay, Dana).
  4. **Webhook Handler (`/api/midtrans/webhook`):**
     - Menerima notifikasi status `settlement`.
     - Otomatis mengubah status pesanan dari `unpaid` &rarr; `paid`.
     - Mengirim sinyal WebSocket Reverb ke layar Dapur & Kasir untuk mencetak tiket dan membunyikan lonceng (*audio chime*).
     - Layar HP konsumen berganti detik itu juga ke status: *Pembayaran Berhasil! Pesanan Sedang Dimasak*.
- **Lingkungan Pengujian Sandbox (Uji Coba Gratis):**
  - Pada tahap pengembangan lokal, sistem wajib dikonfigurasi dalam mode **Midtrans Sandbox** (`midtrans_is_production = 0`).
  - Pengujian dilakukan menggunakan simulator QRIS/kartu resmi Midtrans, sehingga dapat disimulasikan berkali-kali tanpa memotong uang riil sepeser pun. Mode *Production* hanya diaktifkan saat peluncuran resmi.

### 4.4. Mekanisme Pelacakan Live Tanpa Akun (Live Order Tracking)
1. **URL Pesanan Unik (Secure Order Token):**
   - Setiap pesanan memiliki URL pelacakan acak aman, contoh:  
     `https://mastercafe.nadeak.net/tracking/ORD-20260909-X9A2`
2. **Penyimpanan Browser Lokal (LocalStorage HP) & Batas Waktu (TTL 12 Jam):**
   - Browser HP menyimpan data pesanan aktif (`order_token` & `id_meja`).
   - Jika browser tertutup tidak sengaja, saat membuka kembali web cafe akan muncul bar notifikasi:  
     *"Pesanan Meja 03 Anda sedang disiapkan. [Klik untuk Lihat Status]"*.
   - **Masa Berlaku Simpanan (12 Jam / Goldilocks Standard):**  
     Data di HP konsumen otomatis dibersihkan saat pesanan berstatus **✅ Selesai (Completed)** atau maksimal **12 jam** sejak pemesanan. Durasi ini menjamin:
     - Pelanggan yang nongkrong lama (hingga 3–6 jam) tetap aman melihat status pesanannya.
     - Jika keesokan harinya pelanggan datang kembali, memori browser HP sudah otomatis bersih seperti baru.
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
- **Notifikasi Kasir:** Layar POS Kasir membunyikan lonceng (*audio chime*) dan memunculkan pop-up instan.

### 4.6. Struk Digital & Ulasan Pasca-Pesanan
- **Struk Digital (E-Receipt):** Setelah pesanan selesai, konsumen dapat mengunduh bukti transaksi (PDF/gambar) via tombol **[📥 Simpan Struk Digital]**.
- **Rating & Ulasan:** Di layar tracking muncul kartu umpan balik bintang 1–5 sehingga konsumen bisa langsung memberi review dan review langsung tersimpan ke dashboard toko.

### 4.7. Indikator Toko Buka/Tutup Minimalis
- Badge penanda kecil di navbar: `🟢 Buka` / `🔴 Tutup`.
- Jika tutup, tombol checkout dinonaktifkan halus tanpa mengganggu konsumen yang ingin melihat-lihat menu.

### 4.8. Kebijakan Pembatalan Pesanan (Zero Waste)
- Konsumen hanya bisa membatalkan pesanan dari HP jika status masih *Menunggu Pembayaran / Pending*.
- Begitu status berubah jadi *Sedang Dimasak Dapur 👨‍🍳*, tombol batal di HP otomatis dikunci. Pembatalan darurat hanya bisa dilakukan lewat Kasir (*Void Order*).

### 4.9. Optimasi Performa & Akses Menu Mulus (Smooth & Fast Menu Rendering)
- **Instant Menu Render (< 1 Detik):** Daftar menu dimuat secara instan saat QR meja dipindai tanpa delay atau *layout shift* (CLS 0).
- **Optimasi Gambar (Lazy Loading & WebP):** Foto hidangan dikompresi hemat bandwidth dan menggunakan mekanisme *lazy loading* agar konsumsi data di HP pelanggan sangat ringan.
- **Transisi Mulus 60fps (Smooth Micro-Animations):** Interaksi penambahan item (+/-) ke keranjang, modal pemilihan varian, serta perubahan tombol checkout dilapisi animasi transisi CSS yang mulus dan responsif.
- **Performa Ringan di Seluruh Perangkat:** Bebas lag di berbagai spesifikasi HP (Android entry-level maupun iOS).

---

## 5. Penataan Arsitektur Autentikasi & Antarmuka

| Komponen | Kondisi Lama | Penyesuaian Baru |
|---|---|---|
| **`/login`** | Dipakai konsumen umum | Menjadi portal login tunggal untuk **Staff / Karyawan** |
| **`/staff/login`** | Halaman terpisah | Di-redirect ke `/login` resmi |
| **Rute Konsumen** | Wajib `['auth', 'role:konsumen']` | Dibuka untuk umum (*public guest*) |
| **Profil Konsumen (`/konsumen/profil`)** | Edit nama, avatar, & ganti password | **Dihapus total** (konsumen tidak memiliki akun) |
| **Navbar Publik** | Tombol login, register, avatar profil | **Dibersihkan total** (hanya Logo, Menu, Keranjang, Status) |
| **Tabel `pesanan`** | `id_konsumen` wajib terisi akun user | `id_konsumen` sudah `nullable()`, ditambah kolom `guest_name`, `order_token` |
| **Payment Gateway** | Belum terhubung otomatis | **Terintegrasi Midtrans (Snap QRIS & Webhook)** |

---

## 6. Skenario Operasional: Mengatasi Risiko Meja Kabur Tanpa Login (Dine & Dash Mitigation)

### 6.1. Fakta Lapangan di Master Cafe
Berdasarkan wawancara dengan staf kasir Master Cafe:
- Kasir memiliki kebiasaan mengandalkan **ingatan visual** terhadap pengunjung mana yang sudah bayar dan mana yang belum.
- **Tantangan di Area Luas & Jam Sibuk (*Peak Hours*):** Master Cafe memiliki area duduk yang luas. Di saat cafe ramai, ingatan manusia memiliki keterbatasan (*human error*), rawan lupa, atau tidak terlihat saat kasir sedang sibuk meracik minuman/melayani antrean.

### 6.2. Solusi Penguat Kasir (*Visual Table Assistant + Midtrans*)
Sistem POS tidak mengubah kultur kasir, melainkan menjadi **asisten visual pengingat otomatis**:

1. 🟡 **Pengingat Warna Meja di Layar POS Kasir:**
   - Kasir tidak perlu memeras ingatan di kepala. Di layar kasir, meja yang memilih bayar belakangan akan menyala warna **Kuning Terang** mencolok:  
     `Meja 03 - Kak Rian (Belum Bayar: Rp 75.000 - 45 Menit)`.
   - Kasir cukup melirik layar untuk memantau status meja secara instan.

2. 💳 **Kemudahan Bayar QRIS Mandiri dari HP Meja:**
   - Seringkali pengunjung bukan berniat kabur, melainkan malas berdiri dan mengantre di kasir.
   - Di layar HP meja pengunjung ada tombol: **[Bayar via QRIS Midtrans Sekarang]**.
   - Pengunjung bisa langsung bayar dari meja sebelum berdiri. Begitu lunas, speaker kasir seketika membunyikan lonceng 🔔 *Ting-tung!* dan warna Meja 03 di layar kasir otomatis berganti menjadi **🔵 Biru (LUNAS ✅)**.

3. 🙋 **Prosedur Sapaan Ramah Pelayan Lantai:**
   - Jika kasir/pelayan melihat pengunjung Meja 03 sudah berdiri merapikan barang bawaan namun di layar POS meja tersebut masih menyala **Kuning (Belum Bayar)**:
   - Staf dapat menyapa dengan ramah dan sopan:  
     *"Permisi Kak, untuk Meja 03 pembayarannya mau dibantu di kasir atau sudah via QRIS di HP?"* 😊

### 6.3. Kebijakan Rombongan & Gabung Meja (Tetap Simpel Sesuai Kebiasaan Kasir)
- **Keputusan Desain:** Berdasarkan hasil diskusi langsung dengan staf kasir, sistem **tidak menambahkan fitur penggabungan meja otomatis (*merge table*) yang rumit** agar kasir tidak terbebani alur kerja baru.
- **Operasional Lapangan:** 
  - Setiap meja tetap berjalan mandiri sesuai stiker QR masing-masing seperti yang sudah berjalan lancar saat ini.
  - Jika ada rombongan di dua meja (misal Meja 03 & Meja 04) yang ingin membayar sekaligus, kasir cukup menyelesaikan transaksi Meja 03 lalu Meja 04 secara berturut-turut di kasir, atau konsumen membayar masing-masing dari HP via Midtrans QRIS.
  - Ini menjaga sistem tetap ringan, mudah dioperasikan kasir, dan bebas dari kerumitan teknis.

---

## 7. Fondasi Unggulan yang Sudah Aktif Berjalan

Project Master Cafe POS saat ini telah memiliki teknologi mutakhir yang sudah berjalan di kode:
1. 🔊 **Audio Chime Kasir (bell.wav + WebAudio API)**: Berbunyi otomatis saat ada pesanan masuk / panggil pelayan / Midtrans settlement.
2. 📱 **Progressive Web App (PWA)**: Aplikasi POS kasir bisa di-install langsung di HP/tablet kasir tanpa Play Store.
3. 🖨️ **Cetak Thermal ESC/POS Jaringan (Port 9100)**: Cetak struk kasir dan tiket dapur instan via Wi-Fi.
4. 🧾 **Pisah Bon (Split Bill)**: Kasir dapat memecah satu pesanan meja menjadi beberapa nota terpisah.

---

## 8. Kebijakan Pengembangan Aman (Git Branching & Local-Only Testing)

1. 🌿 **Branch Khusus:** Seluruh pengerjaan fitur ini dilakukan pada branch baru terpisah: `feature/guest-ordering-midtrans`.
2. 🔒 **Karantina Server:** **TIDAK BOLEH melakukan git push ke GitHub atau cPanel** selama masa pengerjaan dan pengujian.
3. 💻 **Pengujian 100% di Komputer Lokal:** Seluruh pengetesan (migrasi DB, pesanan meja, simulasi Midtrans Sandbox, WebSocket Reverb, cetak struk) disimulasikan tuntas di komputer lokal (*localhost*).
4. 🚀 **Merge & Deploy:** Kode baru hanya akan di-merge ke `master` setelah seluruh unit test lulus dan disetujui secara resmi oleh Pemilik Master Cafe.

---

## 9. Rencana Tahapan Implementasi (Milestones)

- [x] **Fase 1 (Git & Database):** Buat branch `feature/guest-ordering-midtrans`, buat migration untuk menambahkan kolom `guest_name` dan `order_token` pada tabel `pesanan` (`id_konsumen` dan `tipe_pesanan` sudah ada).
- [x] **Fase 2 (Rute Publik & Form Menu Meja):** Buka rute `/meja/{id_meja}` dari middleware `auth`, lengkapi checkout tanpa login (hanya input nama), serta tambahkan indikator jam buka/tutup minimalis.
- [x] **Fase 3 (Integrasi Midtrans Snap & Webhook):** Sambungkan pop-up Midtrans QRIS dinamis dan tangani callback webhook otomatis (`settlement` &rarr; `paid` &rarr; trigger dapur & audio chime).
- [x] **Fase 4 (Layar Live Tracking, Call Bell, & E-Receipt):** Buat halaman `/tracking/{order_token}` dengan WebSocket Reverb live update, tombol panggil pelayan meja, tombol unduh struk digital, dan kartu rating bintang pasca-selesai.
- [x] **Fase 5 (Pembersihan Profil Konsumen & Penyatuan Login Staff):** Hapus rute profil konsumen, bersihkan navbar publik dari tombol login/register konsumen, dan alihkan rute `/login` khusus untuk karyawan.
- [x] **Fase 6 (Pengujian Lokal Menyeluruh):** Simulasi pemesanan tamu dari scan QR meja, pembayaran Midtrans Sandbox, notifikasi suara kasir, hingga rating pasca-selesai di komputer lokal.
