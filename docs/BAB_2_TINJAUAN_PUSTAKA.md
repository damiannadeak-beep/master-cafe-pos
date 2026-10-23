# BAB II TINJAUAN PUSTAKA

## 2.1 Kajian Terdahulu

Kajian terdahulu merupakan penelusuran terhadap artikel jurnal ilmiah terakreditasi yang telah dipublikasikan secara resmi dengan topik yang relevan seputar sistem kasir (*Point of Sale*) dan sistem pemesanan mandiri (*Self-Service*). Kajian ini bertujuan untuk memetakan perkembangan teknologi yang telah dicapai oleh peneliti sebelumnya, mengevaluasi kelebihan dan keterbatasan dari solusi yang ada, serta mempertegas posisi kebaruan (*novelty*) dan kontribusi dari sistem yang dikembangkan pada Master Cafe Bengkalis.

Dalam penyusunan proposal Tugas Akhir ini, rujukan kajian terdahulu terdiri atas tepat 6 (enam) artikel jurnal ilmiah terverifikasi yang mencakup 3 (tiga) artikel jurnal internasional bereputasi (terindeks Scopus dan IEEE) serta 3 (tiga) artikel jurnal nasional terakreditasi pada Portal SINTA Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi (SINTA 3 dan SINTA 4) terbitan tahun 2022 hingga 2025 dengan rincian sebagai berikut:

1. **Penelitian oleh Hidayat, Rubianto, Nurkhasanah, dan Prasandy (2024) [1]**  
   * **Judul Asli:** *"Menu Ordering Application using QR Code With User Experience Analysis"*  
   * **Nama Jurnal & Reputasi:** *2024 9th International Conference on Business and Industrial Research (ICBIR)*, Bangkok, Thailand: IEEE, hlm. 1–6, Mei 2024. Terindeks **Scopus** dan **IEEE Xplore**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.1109/ICBIR61386.2024.10875903](https://doi.org/10.1109/ICBIR61386.2024.10875903)  
   * **Ringkasan Metode:** Menerapkan metodologi *Kanban* dalam rekayasa aplikasi pemesanan hidangan berbasis *mobile web* menggunakan pemindaian *QR Code* meja dan integrasi teknologi RFID guna melacak posisi meja pelanggan di area kedai kopi. Hasil pengujian fungsional dan *User Acceptance Testing* (UAT) menunjukkan skor performa sebesar 91,4%, di mana 65% responden menyatakan pemesanan hidangan berlangsung jauh lebih cepat dibandingkan cara konvensional.  
   * **Kelebihan:** Mampu memangkas antrean di meja kasir secara signifikan dan mempercepat alur data pesanan ke bagian dapur.  
   * **Kekurangan:** Sistem hanya berfokus pada alur pemesanan meja dan belum terintegrasi dengan modul kasir keuangan yang komprehensif, seperti manajemen buka-tutup shift kerja kasir, pencatatan kas modal/darurat, serta rekonsiliasi kas fisik harian.

2. **Penelitian oleh Wong, Chong, Chong, dan Law (2023) [2]**  
   * **Judul Asli:** *"QR Food Ordering System with Data Analytics"*  
   * **Nama Jurnal & Reputasi:** *Journal of Informatics and Web Engineering (JIWE)*, Vol. 2, No. 2, hlm. 248–261, September 2023. MMU Press. Terindeks **Scopus**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.33093/jiwe.2023.2.2.18](https://doi.org/10.33093/jiwe.2023.2.2.18)  
   * **Ringkasan Metode:** Merancang arsitektur sistem pemesanan makanan mandiri berbasis *QR Code* yang dihubungkan dengan modul visualisasi *data analytics* penjualan sektor *food and beverage* (F&B) untuk memantau menu terlaris, frekuensi transaksi, dan riwayat pesanan pelanggan.  
   * **Kelebihan:** Menyediakan laporan grafis analitik penjualan terperinci untuk mendukung pengambilan keputusan pemilik usaha.  
   * **Kekurangan:** Sistem belum menerapkan teknologi *Progressive Web App* (PWA), belum memiliki validasi radius lokasi pemesan (*geofencing GPS*) sehingga rawan pesanan palsu jika tautan meja tersebar ke luar kafe, serta belum dilengkapi modul audit pergantian shift kasir.

3. **Penelitian oleh Singh, Sonje, Salkar, dan Jadhav (2022) [3]**  
   * **Judul Asli:** *"Smart QR-based Restaurant Dine-in System with Sales Analysis"*  
   * **Nama Jurnal & Reputasi:** *ITM Web of Conferences*, Vol. 44, Art. No. 03014, hlm. 1–6, 2022. EDP Sciences. Terindeks **Scopus** dan **Web of Science**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.1051/itmconf/20224403014](https://doi.org/10.1051/itmconf/20224403014)  
   * **Ringkasan Metode:** Mengembangkan sistem *dine-in* restoran menggunakan *dynamic QR Code* pada setiap meja makan yang terhubung langsung ke bagian dapur dan kasir guna meniadakan penggunaan buku menu fisik dan nota kertas pascapandemi.  
   * **Kelebihan:** Mempercepat waktu pengiriman pesanan ke koki dan meminimalisir kontak fisik langsung antar-individu.  
   * **Kekurangan:** Sistem tidak memiliki proteksi koordinat fisik lokasi pelanggan dan belum menyediakan rekonsiliasi selisih kas fisik di akhir shift kerja kasir.

4. **Penelitian oleh Pratama dan Khristianto (2024) [4]**  
   * **Judul Asli:** *"Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati"*  
   * **Nama Jurnal & Akreditasi:** *INTECOMS: Journal of Information Technology and Computer Science*, Vol. 7, No. 1, hlm. 64–70, Januari 2024. Terakreditasi **SINTA 4**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.31539/intecoms.v7i1.8181](https://doi.org/10.31539/intecoms.v7i1.8181)  
   * **Ringkasan Metode:** Menerapkan metode Waterfall, bahasa pemrograman PHP, dan basis data MySQL untuk mengotomatiskan proses pemesanan menu kafe melalui pemindaian stiker *QR Code* di meja.  
   * **Kelebihan:** Mampu memangkas waktu tunggu pelanggan dan menghilangkan proses pelayan mendatangi meja untuk mencatat pesanan secara manual.  
   * **Kekurangan:** Sistem belum terintegrasi dengan modul kasir keuangan (pembukuan kas modal kasir, pencatatan kas keluar darurat, dan rekonsiliasi kas per shift), belum berbasis PWA, serta belum memiliki proteksi lokasi meja.

5. **Penelitian oleh Prayudha, Irwansyah, dan Anra (2024) [5]**  
   * **Judul Asli:** *"Rancang Bangun Aplikasi Point of Sale (POS App) Berbasis Progressive Web App untuk Usaha Mikro Kecil dan Menengah"*  
   * **Nama Jurnal & Akreditasi:** *JustIN (Jurnal Sistem dan Teknologi Informasi)*, Vol. 12, No. 2, hlm. 248–254, April 2024. Universitas Tanjungpura. Terakreditasi **SINTA 3**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.26418/justin.v12i2.76824](https://doi.org/10.26418/justin.v12i2.76824)  
   * **Ringkasan Metode:** Merancang dan membangun aplikasi kasir *Point of Sale* (POS) dengan mengimplementasikan teknologi *Progressive Web App* (PWA) agar aplikasi kasir UMKM dapat diakses secara responsif di berbagai perangkat gawai pintar, dapat dipasang langsung ke layar utama ponsel (*Add to Home Screen*), dan mendukung penyimpanan aset lokal melalui *service worker*.  
   * **Kelebihan:** Fleksibilitas operasional kasir UMKM tanpa ketergantungan toko aplikasi digital serta performa responsif di ponsel.  
   * **Kekurangan:** Sistem dirancang murni sebagai aplikasi kasir satu arah untuk staf kasir dan belum diintegrasikan dengan portal pemesanan mandiri meja pelanggan (*customer self-service*) secara terpadu.

6. **Penelitian oleh Fadhilah, Rindri, dan Wathan (2025) [6]**  
   * **Judul Asli:** *"Rancang Bangun Sistem Pemesanan Berbasis QR Code dan Aplikasi Kasir Mobile di Warung Kopi Rumah Seduh"*  
   * **Nama Jurnal & Akreditasi:** *KHARISMA Tech*, Vol. 20, No. 2, hlm. 132–146, 2025. Terakreditasi **SINTA 4**.  
   * **DOI / Tautan Aktif:** [https://doi.org/10.55645/kharismatech.v20i2.636](https://doi.org/10.55645/kharismatech.v20i2.636)  
   * **Ringkasan Metode:** Mengembangkan sistem pemesanan menu berbasis pemindaian *QR Code* meja yang disinkronisasikan langsung dengan aplikasi kasir *mobile web* pada kedai kopi Rumah Seduh guna mempercepat sinkronisasi transaksi antara barista, kasir, dan pelanggan.  
   * **Kelebihan:** Mempermudah pemrosesan pesanan masuk secara digital tanpa miskomunikasi.  
   * **Kekurangan:** Sistem belum memiliki modul manajemen pergantian shift kerja kasir, belum menyediakan pencatatan kas modal kasir dan kas darurat, serta belum memiliki proteksi koordinat geografis lokasi pemesan.

Perbandingan komparatif secara sistematis antara keenam penelitian terdahulu di atas dengan sistem yang diusulkan pada Master Cafe disajikan pada Tabel 2.1.

### Tabel 2.1 Perbandingan Penelitian Terdahulu dengan Sistem yang Diusulkan

| No | Peneliti & Tahun | Judul Penelitian & Publikasi | Kelebihan & Keterbatasan Solusi | Posisi & Kebaruan Sistem (Master Cafe) |
|:--:|:---|:---|:---|:---|
| 1 | Hidayat, Rubianto, Nurkhasanah, & Prasandy (2024) [1] | *Menu Ordering Application using QR Code With User Experience Analysis* (IEEE ICBIR - Scopus) | (+) Memangkas antrean kasir dan tingkat kepuasan *User Acceptance Testing* (UAT) mencapai 91,4%.<br>(-) Belum terintegrasi sistem kasir *Point of Sale* (POS), belum ada *shift* kasir harian, dan tanpa rekonsiliasi kas. | Mengintegrasikan fitur *self-service QR Code* meja dengan terminal POS kasir terpadu yang dilengkapi manajemen *shift* harian, rekonsiliasi uang fisik kasir, dan proteksi *geofencing GPS*. |
| 2 | Wong, Chong, Chong, & Law (2023) [2] | *QR Food Ordering System with Data Analytics* (JIWE - Scopus Indexed) | (+) Visualisasi tren menu terlaris dan analisis frekuensi pesanan berbasis *data analytics*.<br>(-) Fokus analitik pasca-penjualan, belum berbasis *Progressive Web App* (PWA), tanpa *geofencing*, dan tanpa audit kasir. | Menghadirkan arsitektur PWA *real-time* dengan verifikasi koordinat lokasi meja (*geofencing* Haversine) serta rekonsiliasi kas kasir per *shift* untuk mencegah *fraud* operasional. |
| 3 | Singh, Sonje, Salkar, & Jadhav (2022) [3] | *Smart QR-based Restaurant Dine-in System with Sales Analysis* (ITM Web of Conf. - Scopus) | (+) Meniadakan buku menu fisik dan mempercepat aliran pesanan ke dapur secara *dine-in*.<br>(-) Rawan diakses dari luar resto (tanpa validasi lokasi) dan tanpa pembukuan selisih kas. | Menyediakan sistem *Dine-In QR* terlindungi radius lokasi kafe, sinkronisasi stok porsi *real-time*, serta modul rekonsiliasi selisih kas fisik di laci kasir saat tutup *shift*. |
| 4 | Pratama & Khristianto (2024) [4] | *Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati* (INTECOMS - SINTA 4) | (+) Menghilangkan proses manual pelayan mencatat pesanan kertas ke meja.<br>(-) Belum ada pembukuan modal kasir, kas darurat, tutup *shift*, dan belum berbasis PWA. | Menggabungkan pemesanan mandiri *QR Code* berbasis PWA dengan sistem kasir POS lengkap yang memiliki pembukuan *shift* harian kasir dan cetak struk *thermal Bluetooth*. |
| 5 | Prayudha, Irwansyah, & Anra (2024) [5] | *Rancang Bangun Aplikasi Point of Sale (POS App) Berbasis Progressive Web App untuk UMKM* (JustIN - SINTA 3) | (+) POS responsif berbasis PWA, dapat diinstal ke layar utama (*Add to Home Screen*), dan *caching service worker*.<br>(-) Hanya kasir satu arah, belum terhubung portal *self-service* pelanggan di meja. | Menyediakan ekosistem *dual-channel*: kasir POS internal toko terhubung langsung dalam satu *database* dengan portal *self-service QR Code* di meja pelanggan tanpa instalasi. |
| 6 | Fadhilah, Rindri, & Wathan (2025) [6] | *Rancang Bangun Sistem Pemesanan Berbasis QR Code dan Aplikasi Kasir Mobile di Warung Kopi Rumah Seduh* (KHARISMA Tech - SINTA 4) | (+) Sinkronisasi digital pesanan *QR Code* meja ke kasir *mobile web* warung kopi.<br>(-) Tanpa manajemen *shift* kasir, tanpa kas modal/darurat, dan tanpa validasi radius meja. | Menyajikan arsitektur *multi-role* (Admin, Kasir, Waitress, Konsumen), verifikasi radius lokasi GPS kafe, serta rekonsiliasi kas fisik harian di akhir *shift* kasir. |

**Sumber:** (Data diolah dari kajian pustaka jurnal penelitian terdahulu [1] - [6], 2025).

Berdasarkan analisis komparatif pada Tabel 2.1 di atas, ditemukan adanya kesenjangan penelitian (*research gap*) di mana mayoritas penelitian terdahulu mengkaji sistem pemesanan *QR Code* dan sistem kasir keuangan secara terpisah. Sebagian penelitian berfokus pada kemudahan antarmuka pemesanan meja bagi pelanggan namun mengabaikan kebutuhan modul kasir operasional dan rekonsiliasi kas. Sebaliknya, penelitian kasir POS yang ada umumnya dirancang satu arah untuk petugas kasir tanpa menyediakan portal interaksi mandiri bagi pelanggan di meja makan.

Posisi dan kebaruan (*novelty*) dari penelitian Tugas Akhir pada Master Cafe Bengkalis ini adalah menghadirkan sistem terintegrasi menyeluruh (*end-to-end*) yang menggabungkan empat pilar keunggulan utama:
1. **Ekosistem Dual-Channel Terpadu:** Menyatukan sistem kasir operasional utama (*Point of Sale*) dengan portal pemesanan mandiri pelanggan (*Customer Self-Service*) berbasis *QR Code* meja dalam satu basis data terpusat, sehingga ketersediaan stok menu langsung tersinkronisasi secara *real-time*.
2. **Manajemen Shift Kerja dan Rekonsiliasi Kas Fisik:** Mengimplementasikan prosedur pembukuan kasir ketat yang mewajibkan pencatatan modal awal kasir (*cash float*), pencatatan kas keluar operasional darurat (*petty cash*), serta rekonsiliasi kas fisik di akhir shift kerja kasir guna mendeteksi dan mencegah kecurangan kasir (*cashier fraud*) maupun selisih uang harian.
3. **Proteksi Keamanan Pemesanan Geofencing GPS:** Menyediakan fitur verifikasi koordinat lokasi kafe menggunakan formula Haversine untuk memastikan pesanan meja (*Dine-In*) hanya dapat dikirimkan jika perangkat ponsel pelanggan secara fisik terdeteksi berada di dalam radius Master Cafe, sehingga mencegah risiko pesanan fiktif (*prank order*).
4. **Arsitektur Progressive Web App (PWA) dan Dukungan Perangkat Keras:** Antarmuka sistem dapat diakses secara instan oleh pelanggan tanpa perlu mengunduh aplikasi di toko digital, responsif di berbagai layar perangkat, serta terintegrasi langsung dengan pencetakan struk transaksi ke printer *thermal* Bluetooth (58mm dan 80mm).

---

## 2.2 Landasan Teori

Landasan teori memuat uraian teori-teori ilmiah, konsep dasar rekayasa perangkat lunak, arsitektur sistem, dan teknologi pendukung yang dikaji secara mendalam dari keenam artikel jurnal acuan utama [1]–[6] sebagai pedoman ilmiah dalam perancangan dan pembangunan Sistem Kasir Berbasis Web dengan Fitur *Self-Service* pada Master Cafe Bengkalis.

### 2.2.1 Sistem kasir (point of sale / pos)
*Point of Sale* (POS) didefinisikan sebagai sistem komputerisasi terintegrasi yang digunakan untuk mengotomatiskan pencatatan transaksi penjualan, mengelola persediaan stok barang secara langsung, serta merekam arus kas masuk dari pelanggan [5], [6]. Sebagaimana diuraikan oleh Prayudha dkk. [5] dan Fadhilah dkk. [6], penerapan sistem POS modern pada unit usaha kuliner mampu mentransformasikan pencatatan manual menjadi transaksi digital yang akurat dan terstruktur.

Fungsi utama sistem POS pada operasional kafe meliputi [5], [6]:  
a. Pencatatan transaksi penjualan cepat, mendukung pembayaran tunai maupun nontunai digital (QRIS).  
b. Pengelolaan pergantian shift kerja kasir, pencatatan modal awal, kas keluar darurat, serta rekonsiliasi kas fisik di akhir hari.  
c. Pengurangan kuantitas stok menu secara otomatis pada saat pesanan diverifikasi dan diselesaikan oleh kasir.  
d. Rekapitulasi laporan keuangan dan omzet harian secara otomatis guna mendukung evaluasi pemilik usaha.

### 2.2.2 Sistem pemesanan mandiri (self-service ordering system)
Sistem pemesanan mandiri (*self-service ordering system*) adalah model layanan digital yang memberikan wewenang penuh kepada pelanggan untuk menelusuri katalog menu, memilih varian hidangan, dan mengirimkan pesanan langsung melalui perangkat gawai pintar tanpa harus mengantre di kasir atau menunggu kedatangan staf pelayan ke meja [1], [3].

Hasil penelitian Hidayat dkk. [1] dan Singh dkk. [3] membuktikan bahwa mekanisme *self-service* berbasis meja makan mampu memangkas waktu tunggu pemesanan hidangan hingga lebih dari 65%, menghilangkan risiko salah catat nota pesanan (*human error*), serta mempercepat efisiensi alur penyajian hidangan di dapur kafe.

### 2.2.3 Quick response code (qr code)
*Quick Response Code* (QR Code) adalah jenis kode matriks dua dimensi yang mampu menyimpan data alfanumerik dalam kapasitas tinggi serta dapat dipindai secara cepat dari berbagai sudut sudut (360 derajat) menggunakan kamera ponsel pintar [1], [4].

Sebagaimana diterapkan dalam studi Hidayat dkk. [1] serta Pratama dan Khristianto [4], stiker QR Code ditempelkan pada masing-masing meja makan dengan memuat tautan (*Uniform Resource Locator* / URL) unik yang membawa parameter nomor meja. Ketika pelanggan memindai kode tersebut, sistem secara otomatis mengunci identitas meja pemesan tanpa mewajibkan proses pendaftaran akun yang rumit.

### 2.2.4 Progressive web app (pwa) dan responsive web design
*Progressive Web Application* (PWA) merupakan metodologi perancangan aplikasi berbasis web yang mengombinasikan keunggulan jangkauan fleksibel peramban web dengan kapabilitas fungsional aplikasi *mobile native* [5]. PWA dibangun menggunakan teknologi web standar (HTML5, CSS3, JavaScript) dengan penambahan komponen utama:  
a. Web App Manifest (*manifest.json*): Berkas konfigurasi yang memungkinkan aplikasi web dipasang langsung ke layar beranda (*Add to Home Screen*) dan dijalankan dalam mode layar penuh (*standalone*) layaknya aplikasi ponsel bawaan [5].  
b. Service Worker: Skrip latar belakang yang bertindak sebagai *network proxy* untuk mengelola penyimpanan memori *cache* lokal aset aplikasi, sehingga sistem dapat dibuka dengan sangat cepat dan tetap stabil saat jaringan internet melambat [5].

*Responsive Web Design* (RWD) memastikan antarmuka sistem kasir dan menu mandiri dapat menyesuaikan tata letak grafisnya secara otomatis pada berbagai ukuran layar gawai, mulai dari layar ponsel pintar pelanggan, tablet pelayan, hingga layar monitor kasir [5].

### 2.2.5 Framework laravel dan arsitektur model-view-controller (mvc)
Laravel adalah kerangka kerja (*framework*) pengembangan aplikasi web berbasis bahasa pemrograman PHP yang menerapkan pola arsitektur *Model-View-Controller* (MVC) [4], [5]. Pola arsitektur MVC memisahkan logika rekayasa perangkat lunak ke dalam tiga modul independen:  
a. **Model:** Menangani pemodelan struktur data, aturan validasi bisnis, dan komunikasi kueri data ke tabel basis data relasional.  
b. **View:** Menangani penyajian visual dan antarmuka pengguna grafis kepada kasir maupun pelanggan.  
c. **Controller:** Menangani penerimaan permintaan (*request*) pengguna, mengeksekusi logika pemrosesan data melalui Model, dan meneruskan hasilnya ke View yang bersangkutan.

### 2.2.6 Basis data relasional dan mysql
Basis data relasional (RDBMS) adalah sistem penyimpanan data terstruktur yang mengorganisasikan rekaman data ke dalam tabel-tabel dua dimensi yang saling terhubung menggunakan relasi kunci utama (*Primary Key*) dan kunci tamu (*Foreign Key*) [2], [4].

Dalam penelitian F&B terdahulu [2], [4], MySQL dipilih sebagai basis data utama karena keunggulannya dalam kecepatan pemrosesan transaksi kueri SQL, keandalan integritas data transaksi (prinsip ACID), serta dukungannya yang optimal pada lingkungan server berbasis PHP dan Laravel.

### 2.2.7 Unified modeling language (uml)
*Unified Modeling Language* (UML) adalah pemodelan visual grafis standar yang digunakan untuk merancang dan mendokumentasikan spesifikasi sistem perangkat lunak berorientasi objek [4], [5]. Diagram UML yang digunakan dalam perancangan sistem ini mencakup:  
a. **Use Case Diagram:** Mendefinisikan interaksi fungsional antara pengguna (Admin/Owner, Kasir, Waitress, dan Konsumen) dengan fungsi sistem.  
b. **Activity Diagram:** Memetakan urutan alur kerja (*workflow*) operasional pemesanan mandiri dan transaksi kasir secara bertahap.  
c. **Entity Relationship Diagram (ERD):** Merancang skema relasi logis antar-tabel basis data, seperti tabel transaksi, pesanan meja, detail menu, kasir shift, dan stok bahan.

### 2.2.8 Pengujian black box testing
*Black Box Testing* adalah teknik pengujian perangkat lunak yang berfokus pada pengujian kesesuaian fungsi-fungsi sistem tanpa harus meninjau struktur internal baris kode program [5], [6]. Pengujian dilakukan dengan cara memberikan serangkaian masukan (*input*) pada tombol dan formulir antarmuka, kemudian memvalidasi apakah keluaran (*output*) sistem telah berjalan sesuai dengan spesifikasi kebutuhan yang direncanakan [5], [6].

---

## DAFTAR PUSTAKA

[1] F. A. Hidayat, T. Rubianto, Nurkhasanah, and T. Prasandy, “Menu Ordering Application using QR Code With User Experience Analysis,” in *2024 9th International Conference on Business and Industrial Research (ICBIR)*, Bangkok, Thailand: IEEE, 2024, pp. 1–6, doi: 10.1109/ICBIR61386.2024.10875903.  
[2] C.-C. Wong, L.-Y. Chong, S.-C. Chong, and C.-Y. Law, “QR Food Ordering System with Data Analytics,” *Journal of Informatics and Web Engineering*, vol. 2, no. 2, pp. 248–261, Sep. 2023, doi: 10.33093/jiwe.2023.2.2.18.  
[3] R. Singh, R. Sonje, S. Salkar, and A. Jadhav, “Smart QR-based Restaurant Dine-in System with Sales Analysis,” *ITM Web of Conferences*, vol. 44, art. no. 03014, pp. 1–6, 2022, doi: 10.1051/itmconf/20224403014.  
[4] F. P. Pratama and T. Khristianto, “Sistem Informasi Pemesanan Makanan Dan Minuman Berbasis Qr Code Pada Brotherhood Coffee Co Pati,” *INTECOMS: Journal of Information Technology and Computer Science*, vol. 7, no. 1, pp. 64–70, Jan. 2024, doi: 10.31539/intecoms.v7i1.8181.  
[5] I. D. Prayudha, M. A. Irwansyah, and H. Anra, “Rancang Bangun Aplikasi Point of Sale (POS App) Berbasis Progressive Web App untuk Usaha Mikro Kecil dan Menengah,” *JustIN (Jurnal Sistem dan Teknologi Informasi)*, vol. 12, no. 2, pp. 248–254, Apr. 2024, doi: 10.26418/justin.v12i2.76824.  
[6] I. Fadhilah, Y. A. Rindri, and M. H. Wathan, “Rancang Bangun Sistem Pemesanan Berbasis QR Code dan Aplikasi Kasir Mobile di Warung Kopi Rumah Seduh,” *KHARISMA Tech*, vol. 20, no. 2, pp. 132–146, 2025, doi: 10.55645/kharismatech.v20i2.636.  
