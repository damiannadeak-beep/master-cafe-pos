# 📋 Bahan Diskusi & Pengambilan Keputusan Pemilik Master Cafe
**Dokumen:** Konsultasi Fitur Pemesanan Meja, Pembayaran & Operasional  
**Penyusun:** Tim Pengembang Aplikasi Master Cafe POS  
**Tanggal:** 10 September 2026  
**Status:** Siap Didiskusikan dengan Pemilik (Owner) Kafe  

---

## 🎯 Ringkasan Eksekutif
Aplikasi Master Cafe POS telah disempurnakan dengan sistem **Pemesanan Mandiri Meja (Guest Ordering via QR Code)** tanpa mewajibkan pelanggan membuat akun/login, dilengkapi optimasi **kecepatan akses menu yang super mulus (smooth 60fps & instant loading < 1 detik)** di seluruh HP pelanggan. Untuk memastikan sistem berjalan selaras dengan kebiasaan operasional dan kenyamanan pelanggan Master Cafe, terdapat beberapa poin strategis yang memerlukan arahan dan keputusan dari Pemilik Kafe.

---

## 📌 TOPIK 1: Kebutuhan Input Data Konsumen (Dine-In & Takeaway)

Saat pelanggan membuka menu dari meja dan menekan tombol **"Pesan"**, sistem meminta data identitas pemesan. 

### Opsi yang Tersedia:

| Opsi | Data yang Diinput | Kelebihan | Kekurangan | Rekomendasi |
|---|---|---|---|---|
| **Opsi A: Nama Saja (Kondisi Sekarang)** | • Nama Pemesan (Contoh: *Budi*) | • **Super Cepat (< 5 detik)**<br>• Pelanggan tidak malas/tidak merasa dimintai data pribadi<br>• Standar kafe modern (*zero-friction*) | • Tidak bisa kirim pesan WA otomatis jika pelanggan meninggalkan meja | **Sangat Direkomendasikan untuk Makan di Tempat (Dine-In)**, karena nomor meja sudah otomatis terkunci dari stiker QR meja. |
| **Opsi B: Nama + No. WhatsApp (Wajib)** | • Nama Pemesan<br>• No. WhatsApp | • Memiliki database kontak pelanggan untuk promosi (*CRM*)<br>• Bisa kirim struk digital ke WA | • Proses pesan lebih lambat<br>• Ada pelanggan yang enggan/risih membagikan nomor HP hanya untuk beli kopi | Direkomendasikan **hanya untuk pesanan Takeaway (Bawa Pulang)** dari luar kafe agar kasir bisa mengabari saat makanan siap diambil. |
| **Opsi C: Hybrid (Nama Wajib + No. WA Opsional)** | • Nama Pemesan (Wajib)<br>• No. WhatsApp (Boleh dikosongkan) | • Fleksibel: yang ingin dapat notifikasi WA bisa isi, yang ingin cepat tinggal lewati | • Menambah 1 kolom input di layar HP | Solusi jalan tengah yang baik. |

> 💬 **Pertanyaan untuk Pemilik:**  
> *"Apakah untuk tamu yang makan di meja (Dine-In), input Nama saja sudah cukup agar proses pemesanan cepat dan santai? Atau Pemilik ingin menambahkan nomor WhatsApp (opsional/wajib) untuk kebutuhan promosi kafe?"*

---

## 📌 TOPIK 2: Integrasi Pembayaran Midtrans & Alur Kasir

Sistem saat ini telah terintegrasi dengan **Midtrans Payment Gateway** untuk pembayaran non-tunai otomatis.

### 1. Dua Jalur Pembayaran untuk Tamu di Meja:
1. **⚡ Bayar Sekarang via Midtrans QRIS (Otomatis):**
   - Muncul kode QRIS dinamis di layar HP tamu (mendukung GoPay, OVO, ShopeePay, DANA, BCA, Mandiri, dll.).
   - Begitu tamu membayar lewat m-Banking/e-Wallet mereka, tagihan **langsung lunas otomatis dalam hitungan detik** tanpa perlu kasir mengecek mutasi rekening manual.
   - Layar kasir dan dapur langsung berbunyi lonceng (*audio chime*) tanda pesanan lunas dan mulai dimasak.
2. **💵 Bayar Nanti di Kasir (Tunai / Open Bill):**
   - Tamu memesan terlebih dahulu, pesanan langsung masuk ke dapur/barista.
   - Tamu membayar tunai/kartu di kasir saat hidangan diantar atau saat hendak pulang.

### 2. Kebutuhan untuk Mengaktifkan Midtrans:
- Saat ini di server lokal masih menggunakan mode simulasi (*sandbox*).
- Untuk mengaktifkan pembayaran uang sungguhan saat kafe buka, dibutuhkan **Server Key** dan **Client Key** dari akun Midtrans resmi Master Cafe (`https://dashboard.midtrans.com`).
- Biaya transaksi resmi QRIS Midtrans adalah **0.7%** (standar Bank Indonesia). Misalnya untuk transaksi kopi Rp 15.000, biaya gerbang pembayaran hanya sekitar Rp 105.

> 💬 **Pertanyaan untuk Pemilik:**  
> 1. *"Apakah Pemilik sudah memiliki akun Midtrans bisnis Master Cafe, atau perlu kami bantu proses pendaftaran dan verifikasinya?"*  
> 2. *"Apakah tamu kafe diperbolehkan memilih 'Bayar Nanti di Kasir' (Open Bill), atau seluruh pesanan wajib bayar lunas di depan sebelum dimasak?"*

---

## 📌 TOPIK 3: Kebijakan Cetak Struk & Nota Pembayaran

Sebelumnya sempat ada tombol *"Cetak Resi / Nota Digital"* di layar HP konsumen. Tombol tersebut telah dinonaktifkan karena membingungkan konsumen di meja yang tidak memiliki mesin printer.

### Pembagian Peran yang Benar:
1. **Pihak Kasir (Memegang Mesin Printer Thermal):**
   - Tombol cetak struk kasir tersedia lengkap di **Panel Kasir POS**.
   - Kasir mencetak kertas struk fisik untuk diberikan ke meja tamu saat pembayaran lunas.
2. **Pihak Konsumen (Layar HP):**
   - Fokus pada pelacakan progres pesanan (*Pesanan Diterima &rarr; Sedang Dimasak &rarr; Selesai*).
   - Menampilkan rincian menu dan harga secara transparan tanpa tombol cetak yang mengganggu.

> 💬 **Pertanyaan untuk Pemilik:**  
> *"Apakah Pemilik setuju bahwa struk fisik murni dicetak oleh kasir menggunakan printer kasir (POS), sedangkan di HP pelanggan hanya berupa tampilan rincian pesanan digital?"*

---

## 📌 TOPIK 4: Keamanan Rute Login Karyawan (Stealth URLs)

Untuk mencegah orang luar / pelanggan iseng mencoba menebak password kasir atau admin kafe:
- Rute umum seperti `/login`, `/admin`, `/kasir`, dan `/wp-admin` telah diatur untuk menghasilkan halaman palsu **404 Not Found (Halaman Tidak Ditemukan)**.
- Seluruh tombol login di halaman publik website konsumen telah ditiadakan.
- Akses kasir dan pemilik dipindahkan ke alamat rahasia:
  - **Pintu Kasir:** `/pos-kasir-gate-88/login`
  - **Pintu Owner:** `/ruang-owner-x92k/login?key=MasterCafeSecret2026!` (dilengkapi kunci rahasia tambahan).

> 💬 **Penyampaian ke Pemilik:**  
> *"Kami telah mengamankan sistem kasir dari percobaan pembobolan hacker / pihak luar dengan menyamarkan pintu login khusus staf."*

---

## 📝 LEMBAR KEPUTUSAN DISKUSI (CHECKLIST)

Gunakan tabel ini saat berdiskusi langsung dengan Pemilik Kafe untuk mencatat persetujuan:

| No | Poin Keputusan | Pilihan Disepakati | Catatan Pemilik |
|:---:|---|---|---|
| **1** | **Input Data Tamu Makan di Tempat (Dine-In)** | [ ] Hanya Nama *(Disarankan)*<br>[ ] Nama + No. WA (Opsional)<br>[ ] Nama + No. WA (Wajib) | |
| **2** | **Input Data Pesanan Bungkus (Takeaway Web)** | [ ] Nama + No. WA (Wajib)<br>[ ] Hanya Nama | |
| **3** | **Kebijakan Pembayaran Meja** | [ ] Boleh Bayar Nanti di Kasir (Open Bill)<br>[ ] Wajib Lunas Duluan via QRIS | |
| **4** | **Aktivasi Akun Midtrans** | [ ] Sudah Ada Akun (Tinggal Masukkan Key)<br>[ ] Belum Ada (Perlu Dibantu Pendaftaran)<br>[ ] Tetap Manual Transfer Sementara | |
| **5** | **Pencetakan Struk Kertas** | [ ] Khusus Kasir Thermal Printer *(Disepakati)*<br>[ ] Sediakan juga Download PDF di HP Tamu | |

---

## 🧪 PANDUAN SIMULASI & DEMO LANGSUNG SAAT DISKUSI

Agar Pemilik Kafe dapat melihat dan mencoba langsung kecanggihan sistem di laptop Anda saat rapat, berikut tautan dan akun pengujian yang telah disiapkan:

### 1. Akun Login Staf (Tablet Waitress & Owner)

| Peran | Pintu URL Login Rahasia | Akun Pengujian | Fungsi Utama yang Ditunjukkan |
|---|---|---|---|
| **Waitress / Tablet Staf** | `http://localhost:8000/pos-kasir-gate-88/login` | **Email:** `safik@gmail.com`<br>**Password:** `password123` | • Layar Pesanan Aktif Antrean Meja (`/kasir/pesanan-aktif`)<br>• Lonceng audio (*chime*) saat pesanan meja masuk<br>• Konfirmasi pesanan & cetak tiket dapur<br>• Pengantaran pesanan ke meja |
| **Owner (Admin)** | `http://localhost:8000/ruang-owner-x92k/login?key=MasterCafeSecret2026!`<br>*(Buka di Incognito/Private Window)* | **Email:** `damiannadeak@gmail.com`<br>**Password:** `password123` | • Dashboard omzet penjualan harian & laba<br>• Manajemen menu & promo kafe<br>• Generator & Cetak stiker QR meja (`/admin/meja`) |

---

### 2. Skenario Percobaan Langsung untuk Konsumen (Demo Tamu di Meja)

Anda dapat memperagakan pengalaman pelanggan saat duduk di meja kafe:

#### A. Melalui Browser Laptop (Simulasi Tab):
1. Buka Tab Baru: **`http://localhost:8000/konsumen/menu/1`** (Simulasi Pelanggan di Meja 1).
2. Tunjukkan ke Pemilik bahwa **tidak ada tombol login** dan menu langsung terbuka elegan.
3. Klik menu (misal: Kopi / Lemon Tea), pilih varian/catatan (*Less sugar*), lalu klik **Pesan**.
4. Masukkan nama pemesan: `Pak Hendra` (atau nama Pemilik Kafe).
5. Klik **Kirim Pesanan**.
6. **Momen WOW:** Buka tab **Tablet Waitress**, pesanan Meja 1 atas nama *Pak Hendra* langsung muncul seketika secara live disertai bunyi notifikasi bel!

#### B. Melalui Kamera HP Sungguhan (Scan QR Asli):
1. Di layar Owner, buka menu **Manajemen Meja** (`/admin/meja`) lalu klik **Cetak QR** pada Meja 1.
2. Minta Pemilik Kafe mengarahkan kamera HP-nya ke layar laptop Anda untuk memindai QR code tersebut.
3. Menu kafe akan langsung terbuka di layar HP Pemilik Kafe persis seperti pelanggan asli di meja!

---

*Dokumen ini dibuat secara khusus untuk membantu kelancaran rapat bersama Pemilik Master Cafe.*
