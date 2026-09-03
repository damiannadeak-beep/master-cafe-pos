# Rencana Standardisasi Desain & UI/UX (Master Cafe)

**Dibuat oleh:** AI (Bertindak sebagai *Senior UI/UX Developer & Ahli POS System*)

Dokumen ini merangkum rencana pembaruan identitas visual untuk seluruh sistem Master Cafe POS (Konsumen, Kasir, Admin), berdasarkan referensi desain yang telah disetujui.

## 1. Palet Warna (Color Palette)
- **Background Utama:** #0e1217 (Gelap pekat, memberikan kesan elegan).
- **Surface / Kartu:** #161b22 (Sedikit lebih terang dari background agar elemen timbul).
- **Gradasi Aksen & Tombol (Bronze):** linear-gradient(135deg, #986c43 0%, #c08e5c 100%).
- **Warna Teks Utama:** #ffffff (Putih murni untuk kontras tinggi di atas latar gelap).
- **Warna Teks Redup:** #a0aab2 (Abu-abu kebiruan untuk teks pendukung/subjudul).

## 2. Tipografi (Typography)
Aplikasi akan mengadopsi tiga jenis font utama (diimpor dari Google Fonts) untuk mencerminkan logo baru:
1. **Rye (Serif)**
   - Penggunaan: Judul utama (H1, H2), Teks "MASTER" pada logo.
2. **Alex Brush (Cursive)**
   - Penggunaan: Aksen artistik, Teks "Cafe" pada logo.
3. **Caveat (Handwriting)**
   - Penggunaan: Slogan, kutipan promo, Teks "SINCE 2020".
4. **Outfit / Plus Jakarta Sans (Sans Serif)**
   - Penggunaan: Teks badan, nama menu, tabel harga, UI kasir (agar tetap mudah dibaca secara cepat).

## 3. Tata Letak & Spasi (Layout & Spacing)
- **Border Radius:** 16px (1rem) untuk sudut kartu dan modal agar terlihat modern namun terstruktur (kotak proporsional).
- **Padding Kartu:** 24px (1.5rem) untuk memberi ruang napas yang cukup (kotak tidak terasa sesak).
- **Batas Tabel (Table Borders):** 1px solid #21262d agar terpisah rapi dengan latar belakang.

## 4. Langkah Implementasi (To-Do)
1. [ ] Menyuntikkan tag <link> Google Fonts (Alex Brush, Caveat, Rye) ke dalam pp.blade.php, dmin.blade.php, dan kasir.blade.php.
2. [ ] Mengganti nilai variabel CSS :root lama dengan palet warna #0e1217 dan gradasi #986c43 -> #c08e5c.
3. [ ] Mengubah seluruh logo teks statis di navbar Admin dan Kasir menjadi rangkaian HTML+CSS yang mencerminkan desain 	ext-master, 	ext-cafe, dan 	ext-since.
4. [ ] Memastikan 	able-dark dan elemen Kasir berpadu sempurna dengan background #0e1217.
## 5. Pembersihan Kode (Code Cleanup)
- Menghapus seluruh identitas warna lama (#111418, #1a1d24, #2a2d32, dll) secara permanen.
- Membersihkan variabel CSS lama dari seluruh file Blade (pp.blade.php, dmin.blade.php, kasir.blade.php).
- Memastikan tidak ada percampuran (overlapping) antara warna desain lama dengan desain baru.
## 6. Sistem Grid 8-Point (8pt Grid System)
- Seluruh ukuran desain (spasi, padding, margin, font size, border-radius) wajib menggunakan **kelipatan 8** (8px, 16px, 24px, 32px, 40px, dst).
- Hal ini bertujuan agar proporsi antarmuka tetap seimbang ("tidak ada yang jomplang terlalu kecil atau terlalu besar") dan dapat menyesuaikan dengan sempurna di berbagai ukuran layar (Desktop, Tablet, maupun HP).
## 7. Pengalaman Interaksi & Animasi (Micro-interactions)
- Transisi halus (	ransition: all 0.3s ease) pada tombol dan kartu saat disorot kursor (hover).
- Efek *lift-up* (terangkat) hanya diterapkan secara **selektif** pada elemen yang bisa di-klik (seperti tombol aksi dan kartu menu), BUKAN pada kartu informasi statis (agar tidak membingungkan pengguna).

## 8. Kustomisasi Scrollbar (Bilah Gulir)
- Menghilangkan *scrollbar* bawaan *browser* yang kaku dan tebal.
- Menggantinya dengan *scrollbar* tipis (8px) yang menyatu dengan tema gelap. **Warna Track (Jalur):** `#0e1217` (sama dengan background utama agar tidak terlihat). **Warna Thumb (Batang):** `#21262d` (abu-abu gelap, menyatu dengan border). **Hover State:** Batang akan sedikit berubah menjadi `rgba(152, 108, 67, 0.5)` (semi-transparan perunggu) HANYA saat digeser agar elegan dan tidak mencolok secara berlebihan.

## 9. Ukuran Sentuhan Layar (Touch Targets)
- Memastikan semua tombol interaktif (terutama di aplikasi Kasir) memiliki tinggi minimal **40px - 48px** (mengikuti kelipatan 8) agar sangat nyaman ditekan dengan jari pada perangkat sentuh (Tablet/HP) tanpa risiko salah pencet.
## 10. Hierarki & Kedalaman Warna Gelap (Color Elevation & Depth)
- **Aturan Dilarang Menimpa Warna Sama:** Jangan menimpa lapisan berwarna gelap dengan warna gelap yang sama persis (misal: jangan meletakkan form input hitam di atas kartu hitam).
- **Sistem Elevasi (Semakin ke atas semakin terang):**
  - **Level 0 (Latar/Background):** #0e1217 (Paling gelap)
  - **Level 1 (Kartu/Surface):** #161b22 (Sedikit lebih terang dari latar)
  - **Level 2 (Input/Bilah/Dropdown):** #21262d atau menggunakan garis batas transparan ringan.
- Hal ini menjaga agar tampilan tidak terlihat datar/mati (*flat/muddy*) dan pengguna bisa dengan mudah membedakan batas antar elemen (kedalaman UI/UX).
## 11. Pendekatan Tema Tunggal (Single Theme Approach)
- Aplikasi ini difokuskan hanya pada **Satu Tema Utama** (yaitu desain *Dark Bronze* yang elegan) sebagai identitas baku (*default*).
- **Tidak ada fitur "Toggle Light/Dark Mode"** pada tahap ini. Hal ini dilakukan untuk memfokuskan sumber daya pada penyempurnaan UI/UX satu tema secara maksimal. 
- Jika di masa mendatang pihak Master Cafe meminta variasi mode terang (*Light Mode*), struktur variabel CSS ini sudah disiapkan sedemikian rupa agar sangat mudah diduplikasi.
## 12. Skala Tumpukan Layar (Z-Index Scale)
- Agar tidak ada insiden elemen menembus/menimpa secara acak (misal: *dropdown* tertutup *header*, atau *modal* tertimpa *sidebar*), aplikasi ini mengunci sistem Z-Index tetap:
  - z-index: 10 - Elemen Mengambang (*Floating buttons/cards*)
  - z-index: 100 - Header & Navbar Utama
  - z-index: 1020 - Sidebar Mengambang (*Sticky cart sidebar*)
  - z-index: 1050 - Pop-up Dropdown Menu
  - z-index: 2000 - Jendela Modal Utama (Modal Pembayaran)
## 13. Cakupan Perombakan Menyeluruh (Global Redesign Scope)
- Seluruh standarisasi desain (warna, tipografi, grid 8-point, dll) **berlaku secara global tanpa terkecuali**.
- Termasuk seluruh halaman Autentikasi (Login, Register, Lupa Password).
- Termasuk halaman Konsumen (Beranda/Menu Publik).
- Termasuk halaman Kasir POS dan seluruh modul laporannya.
- Termasuk halaman Dashboard Admin dan seluruh fitur manajemennya.
## 14. Sistem Notifikasi (Alerts & Toasts)
- Dilarang menggunakan lert() bawaan *browser* yang kaku. Semua pesan sukses/gagal (seperti "Pesanan Berhasil Disimpan") harus menggunakan elemen *Alert* atau *Toast* (*pop-up* kecil di sudut layar) yang didesain menggunakan palet warna *Dark Bronze* agar elegan dan tidak mengganggu alur kerja kasir.

## 15. Konsistensi Ikonografi (Iconography)
- Ukuran ikon (*Bootstrap Icons* atau *FontAwesome*) harus selalu sejajar dan simetris dengan teks di sebelahnya.
- Ukuran baku ikon di dalam tombol: 1.25rem (20px). Jarak antara ikon dan teks wajib menggunakan kelipatan 8 (misal: margin-right: 8px).

## 16. Keterbacaan & Kontras (Accessibility / A11y)
- Memastikan warna teks redup (#a0aab2) pada latar gelap (#161b22) tetap memiliki rasio kontras yang cukup tinggi (*High Contrast*). Hal ini krusial agar layar Kasir POS tetap bisa dibaca dengan tajam meskipun layar mesin kasir terpapar pantulan cahaya lampu kafe yang terang.