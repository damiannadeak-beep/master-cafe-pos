# BAB II TINJAUAN PUSTAKA

## 2.1 Kajian Terdahulu

Kajian terdahulu merupakan penelusuran terhadap artikel jurnal ilmiah terakreditasi yang telah dipublikasikan secara resmi dengan topik relevan seputar sistem kasir (*Point of Sale*) dan sistem pemesanan mandiri (*Self-Service*). Kajian ini bertujuan untuk memetakan perkembangan teknologi yang telah dicapai, mengevaluasi kelebihan dan kelemahan dari solusi yang ada, serta mempertegas posisi kebaruan (*novelty*) dan kontribusi dari sistem yang dikembangkan pada Master Cafe.

Seluruh rujukan kajian terdahulu dalam penelitian ini merupakan publikasi ilmiah terbitan tahun **2024** yang telah terindeks secara resmi pada **Portal SINTA Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi (Kemendikbudristek)** dengan peringkat akreditasi **SINTA 4** serta memiliki *Digital Object Identifier* (DOI) aktif yang terdaftar di Crossref:

1. **Penelitian oleh Pratama dan Khristianto (2024) [1]**  
   * **Judul Asli:** *"Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati"*  
   * **Nama Jurnal & Akreditasi:** *INTECOMS: Journal of Information Technology and Computer Science*, Vol. 7, No. 1, hlm. 64–70, Januari 2024. Terakreditasi **SINTA 4**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.31539/intecoms.v7i1.8181](https://doi.org/10.31539/intecoms.v7i1.8181) (Akses OJS: [https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/8181](https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/8181))  
   * **Ringkasan Metode:** Menggunakan bahasa pemrograman PHP, basis data MySQL, dan pendekatan SDLC model sekuensial linier (Waterfall) untuk mengotomatiskan proses pemesanan menu melalui pemindaian barcode/QR Code pada meja kafe.  
   * **Kelebihan:** Mampu memangkas waktu tunggu pelanggan dengan menghilangkan proses manual pelayan mendatangi meja untuk mencatat pesanan, serta data pesanan pelanggan langsung tersimpan secara digital ke sistem.  
   * **Kekurangan:** Sistem belum terintegrasi dengan modul kasir keuangan yang komprehensif (seperti pembukuan uang modal awal kasir, pencatatan kas keluar darurat, dan rekonsiliasi kas fisik per shift kerja), belum menerapkan arsitektur *Progressive Web App* (PWA), serta belum memiliki pembatasan lokasi GPS meja (*geofencing*).

2. **Penelitian oleh Irwansa dan Huda (2024) [2]**  
   * **Judul Asli:** *"Pemanfaatan QR Code Dalam Pemesanan Makanan & Minuman Pada Rumah Makan Kejora Jaya Menggunakan Metode User Centered Design (UCD)"*  
   * **Nama Jurnal & Akreditasi:** *INTECOMS: Journal of Information Technology and Computer Science*, Vol. 7, No. 1, hlm. 196–206, Februari 2024. Terakreditasi **SINTA 4**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.31539/intecoms.v7i1.8429](https://doi.org/10.31539/intecoms.v7i1.8429) (Akses OJS: [https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/8429](https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/8429))  
   * **Ringkasan Metode:** Menerapkan metode *User Centered Design* (UCD) dan pemodelan *Unified Modeling Language* (UML) dalam rekayasa sistem berbasis web untuk menggantikan nota kertas pesanan manual dengan pemindaian stiker QR Code di meja makan.  
   * **Kelebihan:** Mengurangi beban kerja pelayan secara efektif, mengeliminasi risiko kertas nota basah atau tulisan tangan yang tidak terbaca, dan mempercepat alur data pesanan ke bagian dapur.  
   * **Kekurangan:** Belum mendukung arsitektur multi-stasiun (pemisahan dinamis antara hak akses Tablet Waitress dan Kasir Utama), belum dilengkapi fasilitas pelaporan tutup shift kasir harian, serta belum memiliki dukungan cetak struk ke printer thermal Bluetooth.

3. **Penelitian oleh Wulandari dan Handayani (2024) [3]**  
   * **Judul Asli:** *"Sistem Inventory dan Pemesanan Menu Berbasis Web dan Mobile pada Hana Chick"*  
   * **Nama Jurnal & Akreditasi:** *INTECOMS: Journal of Information Technology and Computer Science*, Vol. 7, No. 6, hlm. 2291–2301, Desember 2024. Terakreditasi **SINTA 4**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.31539/intecoms.v7i6.13257](https://doi.org/10.31539/intecoms.v7i6.13257) (Akses OJS: [https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/13257](https://journal.ipm2kpe.or.id/index.php/INTECOM/article/view/13257))  
   * **Ringkasan Metode:** Menggunakan metode Waterfall untuk merancang sistem pemesanan menu online dan pengelolaan stok inventaris bahan secara otomatis, menyelesaikan persoalan selisih pencatatan keuangan manual berbasis spreadsheet.  
   * **Kelebihan:** Mampu menyinkronkan pengurangan stok inventaris menu secara *real-time* saat terjadi pemesanan serta menyediakan pelaporan arus transaksi pemasukan dan pengeluaran.  
   * **Kekurangan:** Sistem pemesanan difokuskan pada pengiriman pesanan jarak jauh dan belum dioptimasi untuk interaksi pemesanan mandiri pelanggan di meja kafe (*Dine-In Self-Service QR*), belum memiliki verifikasi lokasi radius kafe (*geofencing*), serta belum memiliki modul rekonsiliasi kas fisik per shift staf kasir.

Perbandingan komparatif antara ketiga penelitian terdahulu di atas dengan sistem yang diusulkan pada Master Cafe disajikan pada Tabel 2.1.

### Tabel 2.1 Perbandingan Penelitian Terdahulu dengan Sistem yang Diusulkan

| No | Peneliti & Tahun | Judul Penelitian & Jurnal | Metode / Teknologi | Kelebihan | Kelemahan / Keterbatasan | Posisi & Kebaruan Penelitian Ini (Master Cafe) |
|:---|:---|:---|:---|:---|:---|:---|
| 1 | Pratama & Khristianto (2024) [1] | *Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati* (INTECOMS - **SINTA 4**) | PHP, MySQL, Waterfall, QR Code | Mempercepat alur pemesanan dan mengurangi antrean pelanggan di kasir. | Belum memiliki modul rekonsiliasi kas kasir per shift, belum berbasis PWA, dan belum ada proteksi lokasi meja. | Mengintegrasikan pemesanan mandiri QR Code berbasis PWA dengan sistem kasir POS lengkap yang memiliki pembukuan shift harian kasir dan proteksi lokasi GPS. |
| 2 | Irwansa & Huda (2024) [2] | *Pemanfaatan QR Code Dalam Pemesanan Makanan & Minuman Pada Rumah Makan Kejora Jaya Menggunakan Metode User Centered Design (UCD)* (INTECOMS - **SINTA 4**) | Web-based, UCD, UML, QR Code Meja | Mengeliminasi risiko nota kertas pesanan hilang/basah dan tulisan tidak terbaca. | Belum mendukung multi-stasiun (*waitress tablet* & kasir) dan belum ada cetak struk thermal kasir. | Menyediakan arsitektur multi-stasiun (Admin, Kasir POS, Tablet Waitress, dan Konsumen) dengan integrasi printer thermal Bluetooth dan ekspor PDF. |
| 3 | Wulandari & Handayani (2024) [3] | *Sistem Inventory dan Pemesanan Menu Berbasis Web dan Mobile pada Hana Chick* (INTECOMS - **SINTA 4**) | Web & Mobile, Waterfall, Inventory Management | Pengurangan stok menu otomatis saat transaksi dan pencatatan pemasukan/pengeluaran. | Belum dioptimasi untuk pemesanan mandiri di meja (*Dine-In QR*), belum ada geofencing, dan belum ada rekonsiliasi kas fisik shift kasir. | Menggabungkan pemesanan mandiri di meja (*Dine-In QR*) dan takeaway, verifikasi radius lokasi GPS, serta rekonsiliasi selisih kas fisik di akhir shift kasir. |

Berdasarkan analisis perbandingan di atas, ditemukan kesenjangan penelitian (*research gap*) di mana penelitian terdahulu umumnya mengkaji sistem pemesanan QR Code dan pencatatan kasir secara terpisah. Posisi penelitian ini adalah menghadirkan solusi terintegrasi yang menggabungkan:
1. **Sistem Kasir Terpadu (Point of Sale):** Dilengkapi pencatatan transaksi kasir cepat (tunai & QRIS), manajemen buka dan tutup shift kerja kasir, pencatatan kas modal/kas keluar darurat, serta rekonsiliasi selisih uang fisik kasir harian.
2. **Fitur Pemesanan Mandiri (*Self-Service* QR Code):** Dibangun dengan teknologi *Progressive Web App* (PWA) yang responsif dan cepat dibuka di ponsel pintar konsumen tanpa perlu mengunduh aplikasi di toko digital.
3. **Keamanan Pemesanan (*Geofencing GPS*):** Dilengkapi opsi verifikasi koordinat lokasi kafe menggunakan formula Haversine untuk memastikan pesanan meja (*Dine-In*) hanya dapat dikirim jika pelanggan secara fisik terdeteksi berada di dalam radius Master Cafe.

---

## 2.2 Landasan Teori

Landasan teori memuat uraian teori-teori ilmiah, konsep dasar, arsitektur perangkat lunak, dan teknologi pendukung yang dijadikan sebagai pijakan ilmiah dalam perancangan dan implementasi sistem.

### 2.2.1 Sistem Kasir (Point of Sale / POS)
*Point of Sale* (POS) secara harfiah merujuk pada titik atau lokasi tempat transaksi penjualan antara pedagang dan pelanggan berlangsung [4]. Dalam konteks teknologi informasi, sistem POS adalah kombinasi perangkat lunak (*software*) dan perangkat keras (*hardware*) yang digunakan untuk mencatat transaksi penjualan, menghitung kalkulasi harga, mengelola persediaan stok barang, serta mencetak bukti transaksi pembayaran (struk belanja) [5].

Sistem POS modern pada industri *food and beverage* (F&B) tidak hanya berfungsi sebagai mesin kasir elektronik (*electronic cash register*), melainkan berkembang menjadi pusat kendali operasional kafe [6]. Fungsi vital POS meliputi:
1. **Pencatatan Transaksi Cepat:** Menerima pembayaran tunai maupun non-tunai (seperti QRIS).
2. **Manajemen Kas dan Shift Kerja:** Mencatat modal awal kasir, kas masuk penjualan, kas keluar darurat, serta rekonsiliasi uang fisik kasir pada akhir shift untuk mendeteksi adanya selisih (*surplus/shortage*).
3. **Pengelolaan Stok Menu:** Pengurangan jumlah porsi menu otomatis secara *real-time* setiap kali transaksi diselesaikan.
4. **Pelaporan Operasional:** Menghasilkan laporan omzet harian, mingguan, hingga bulanan yang akurat guna mempermudah audit pemilik kafe.

### 2.2.2 Konsep Sistem Pemesanan Mandiri (Self-Service Ordering System)
*Self-Service Ordering System* adalah inovasi layanan yang memungkinkan konsumen untuk melihat daftar menu, menentukan pilihan hidangan, menambahkan instruksi khusus/catatan, dan mengirimkan pesanan secara langsung melalui antarmuka digital tanpa harus menunggu pelayan datang ke meja atau mengantre di kasir [7].

Penerapan *self-service* pada industri kafe memberikan sejumlah keuntungan strategis [8]:
1. **Mengurangi Waktu Tunggu dan Antrean:** Pelanggan yang duduk di meja dapat langsung memesan saat itu juga, sehingga kepadatan di area meja kasir berkurang drastis.
2. **Meminimalisir Kesalahan Manusia (*Human Error*):** Mengeliminasi salah dengar atau salah catat pesanan oleh pelayan, karena rincian menu diinput langsung oleh pelanggan.
3. **Meningkatkan Nilai Rata-rata Pesanan (*Upselling*):** Katalog visual digital yang menarik memicu minat beli pelanggan untuk memesan varian menu tambahan atau promo yang ditawarkan.

### 2.2.3 Quick Response Code (QR Code)
*Quick Response Code* (QR Code) adalah bentuk evolusi dari barcode satu dimensi (*1D Barcode*) menjadi matriks dua dimensi (*2D Barcode*) yang dikembangkan oleh Denso Wave pada tahun 1994 [9]. QR Code mampu menyimpan data dengan kapasitas yang jauh lebih besar (mencapai ribuan karakter alfanumerik) dan dapat dibaca dari segala arah (360 derajat) menggunakan kamera ponsel pintar [10].

Pada sistem *self-service* kafe, QR Code ditempelkan pada masing-masing meja makan dengan memuat tautan (*Uniform Resource Locator* / URL) yang berisi parameter identifikasi meja (misalnya `https://mastercafe.nadeak.net/konsumen/menu/table/{id_meja}`). Ketika kamera ponsel memindai kode tersebut, browser pelanggan secara otomatis diarahkan ke antarmuka katalog menu dengan identitas nomor meja yang sudah terkunci di sistem [11].

### 2.2.4 Progressive Web App (PWA) dan Desain Web Responsif
*Progressive Web Application* (PWA) adalah metodologi pengembangan aplikasi web yang menggabungkan fleksibilitas web dengan keunggulan fungsional aplikasi mobile native [12]. PWA dibangun menggunakan teknologi web standar (HTML5, CSS3, dan JavaScript) dengan penambahan komponen penting berupa:
1. **Web App Manifest (`manifest.json`):** File konfigurasi berformat JSON yang mendefinisikan identitas aplikasi (nama, ikon, warna tema, serta tampilan *standalone* tanpa bilah navigasi browser) sehingga aplikasi web dapat ditambahkan langsung ke layar utama (*Add to Home Screen*) ponsel pengguna [13].
2. **Service Worker:** Skrip JavaScript latar belakang (*background thread*) yang berfungsi sebagai *network proxy*. *Service worker* memungkinkan penyimpanan aset ke dalam *cache* lokal, sehingga aplikasi web tetap dapat diakses dengan cepat bahkan pada kondisi jaringan internet yang tidak stabil [14].

*Responsive Web Design* (RWD) memastikan tata letak dan elemen antarmuka sistem kasir dan menu mandiri dapat beradaptasi secara optimal pada berbagai ukuran layar perangkat, mulai dari layar ponsel pintar pelanggan, tablet staf *waitress*, hingga monitor komputer meja kasir [15].

### 2.2.5 Framework Laravel dan Arsitektur Model-View-Controller (MVC)
Laravel adalah sebuah framework aplikasi web berbasis bahasa pemrograman PHP yang bersifat *open-source* dan dirancang dengan mengusung prinsip keanggunan sintaksis (*expressive and elegant syntax*) [16]. Laravel menyediakan berbagai pustaka bawaan yang mempercepat proses rekayasa perangkat lunak, antara lain *routing*, sistem keamanan enkripsi CSRF (*Cross-Site Request Forgery*), middleware otentikasi multi-role, sistem templat *Blade*, serta *Object-Relational Mapping* (ORM) yang bernama Eloquent [17].

Laravel menerapkan pola arsitektur **Model-View-Controller (MVC)** untuk memisahkan logika bisnis, struktur data, dan antarmuka pengguna [18]:
1. **Model:** Komponen yang bertanggung jawab menangani representasi data dan interaksi langsung dengan tabel-tabel pada basis data.
2. **View:** Komponen antarmuka pengguna (*User Interface*) yang bertugas menyajikan data grafis kepada pengguna sistem.
3. **Controller:** Komponen perantara yang bertugas menerima *request* dari pengguna melalui rute (*route*), memproses logika bisnis menggunakan Model, dan mengembalikan hasil olahan ke dalam View yang sesuai.

### 2.2.6 Basis Data Relasional dan MySQL
Basis data relasional (*Relational Database Management System* / RDBMS) adalah sistem penyimpanan data terstruktur yang mengorganisasikan informasi ke dalam bentuk tabel-tabel dua dimensi yang terdiri atas baris (*records*) dan kolom (*attributes*) [19]. Antar-tabel dapat dihubungkan melalui relasi kunci primer (*Primary Key*) dan kunci asing (*Foreign Key*).

MySQL merupakan salah satu RDBMS terpopuler di dunia yang menggunakan bahasa kueri *Structured Query Language* (SQL). Keunggulan MySQL terletak pada kecepatan eksekusi data, keandalan transaksi berprinsip ACID (*Atomicity, Consistency, Isolation, Durability*), serta kompatibilitasnya yang tinggi dengan lingkungan ekosistem PHP dan framework Laravel [20].

### 2.2.7 Unified Modeling Language (UML)
*Unified Modeling Language* (UML) adalah bahasa visual standar industri untuk mendokumentasikan, menspesifikasikan, dan merancang artefak-artefak dari sistem perangkat lunak berorientasi objek [21]. Diagram-diagram UML yang digunakan dalam perancangan sistem ini meliputi:
1. **Use Case Diagram:** Diagram yang mendeskripsikan fungsionalitas sistem dari sudut pandang para pengguna (aktor) yang berinteraksi dengan sistem, seperti Admin/Pemilik, Kasir, dan Konsumen [22].
2. **Activity Diagram:** Diagram yang memodelkan alur kerja (*workflow*) fungsional sistem secara berurutan, menggambarkan transisi aksi dari suatu aktivitas ke aktivitas lainnya beserta titik percabangan keputusan (*decision point*) [23].
3. **Entity Relationship Diagram (ERD):** Pemodelan konseptual struktur basis data yang menunjukkan entitas, atribut, dan relasi logis antar-entitas data dalam sistem kasir dan pesanan [24].

### 2.2.8 Pengujian Black Box Testing
*Black Box Testing* (pengujian kotak hitam) adalah metodologi pengujian perangkat lunak yang berfokus pada pengujian fungsionalitas sistem tanpa harus menguji struktur kode internal atau logika internal program [25]. Pengujian dilakukan dengan cara memberikan masukan (*input*) data tertentu pada antarmuka sistem dan memverifikasi apakah keluaran (*output*) dan perilaku sistem telah sesuai dengan spesifikasi kebutuhan perangkat lunak yang direncanakan (*functional requirements*) [26].

---

## DAFTAR PUSTAKA (Format IEEE)

[1] F. P. Pratama and T. Khristianto, "Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati," *INTECOMS: Journal of Information Technology and Computer Science*, vol. 7, no. 1, pp. 64–70, Jan. 2024, doi: [10.31539/intecoms.v7i1.8181](https://doi.org/10.31539/intecoms.v7i1.8181).  
[2] Irwansa and N. Huda, "Pemanfaatan QR Code Dalam Pemesanan Makanan & Minuman Pada Rumah Makan Kejora Jaya Menggunakan Metode User Centered Design (UCD)," *INTECOMS: Journal of Information Technology and Computer Science*, vol. 7, no. 1, pp. 196–206, Feb. 2024, doi: [10.31539/intecoms.v7i1.8429](https://doi.org/10.31539/intecoms.v7i1.8429).  
[3] Y. A. Wulandari and I. Handayani, "Sistem Inventory dan Pemesanan Menu Berbasis Web dan Mobile pada Hana Chick," *INTECOMS: Journal of Information Technology and Computer Science*, vol. 7, no. 6, pp. 2291–2301, Dec. 2024, doi: [10.31539/intecoms.v7i6.13257](https://doi.org/10.31539/intecoms.v7i6.13257).  
[4] K. E. Kendall and J. E. Kendall, *Systems Analysis and Design*, 10th ed. Boston: Pearson, 2020.  
[5] I. Sommerville, *Software Engineering*, 10th ed. Harlow: Pearson Education, 2016.  
[6] R. S. Pressman and B. R. Maxim, *Software Engineering: A Practitioner's Approach*, 9th ed. New York: McGraw-Hill Education, 2020.  
[7] D. T. Susilo and M. Firmansyah, "Analisis Efektivitas Sistem Pemesanan Mandiri Berbasis Digital terhadap Kepuasan Pelanggan," *Jurnal Manajemen Bisnis dan Pelayanan*, vol. 8, no. 1, pp. 15–24, 2021.  
[8] C. Liu and Y. Wei, "Impact of Self-Service Technology on Restaurant Customer Experience: An Empirical Analysis," *Journal of Hospitality and Tourism Technology*, vol. 12, no. 3, pp. 450–465, 2021.  
[9] Denso Wave Incorporated, "About QR Code: History and Standardization," *Denso Wave Technical Report*, 2019. [Online]. Available: https://www.denso-wave.com/en/technology/vol1.html.  
[10] H. P. Utomo and F. Susanto, "Pemanfaatan Quick Response Code pada Sistem Antrean Digital," *Jurnal Teknologi Informasi dan Rekayasa Komputer*, vol. 3, no. 2, pp. 78–86, 2022.  
[11] M. A. Maulana and R. I. Wardhani, "Implementasi QR Code Dinamis pada Sistem Identifikasi Meja Restoran Berbasis Web," *Jurnal RESTI (Rekayasa Sistem dan Teknologi Informasi)*, vol. 6, no. 4, pp. 589–596, 2022.  
[12] Google Developers, "Progressive Web Apps Overview," *Web Fundamentals Documentation*, 2023. [Online]. Available: https://web.dev/progressive-web-apps/.  
[13] J. Musolesi, *Building Progressive Web Applications: Bringing Mobile Web to the Next Level*. Sebastopol: O'Reilly Media, 2018.  
[14] T. Arakawa, *Service Worker Lifecycle and Caching Strategies for Offline-First Architecture*. New York: Packt Publishing, 2020.  
[15] E. Marcotte, *Responsive Web Design*, 2nd ed. New York: A Book Apart, 2014.  
[16] T. Otwell, *Laravel: Up & Running: A Framework for Building Modern PHP Apps*, 2nd ed. Sebastopol: O'Reilly Media, 2019.  
[17] M. Bean, *Mastering Laravel 10: Building Robust and Scalable Web Applications*. Birmingham: Packt Publishing, 2023.  
[18] M. Fowler, *Patterns of Enterprise Application Architecture*. Boston: Addison-Wesley Professional, 2012.  
[19] C. J. Date, *An Introduction to Database Systems*, 8th ed. Boston: Addison-Wesley, 2014.  
[20] R. Elmasri and S. B. Navathe, *Fundamentals of Database Systems*, 7th ed. Boston: Pearson, 2017.  
[21] J. Rumbaugh, I. Jacobson, and G. Booch, *The Unified Modeling Language Reference Manual*, 2nd ed. Boston: Addison-Wesley, 2010.  
[22] A. Dennis, B. H. Wixom, and D. Tegarden, *Systems Analysis and Design: An Object-Oriented Approach with UML*, 6th ed. Hoboken: John Wiley & Sons, 2021.  
[23] H. M. Jogiyanto, *Analisis dan Desain Sistem Informasi: Pendekatan Terstruktur Teori dan Praktik Aplikasi Bisnis*, Yogyakarta: Andi Offset, 2018.  
[24] P. Rob and C. Coronel, *Database Systems: Design, Implementation, and Management*, 13th ed. Boston: Cengage Learning, 2019.  
[25] G. J. Myers, C. Sandler, and T. Badgett, *The Art of Software Testing*, 3rd ed. Hoboken: John Wiley & Sons, 2012.  
[26] P. C. Jorgensen, *Software Testing: A Craftsman's Approach*, 4th ed. Boca Raton: CRC Press, 2014.
