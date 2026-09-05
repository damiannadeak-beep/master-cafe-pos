# Panduan Workflow Git & Deployment cPanel (Master Cafe)

Dokumen ini berisi langkah-langkah standar untuk pengembangan fitur baru, perbaikan bug (trial-error di local), hingga deployment ke server cPanel live dengan **Keamanan Standar Industri**.

---

## 1. Pengembangan & Trial-Error di Laptop (Localhost)

Kerjakan seluruh eksperimen dan uji coba di laptop (Local Herd/DBngin) menggunakan **Branch**.

`ash
# 1. Pindah ke branch utama & pastikan kode lokal terbaru
git checkout master
git pull origin master

# 2. Buat branch baru untuk fitur/bug tertentu
# Format: fitur/nama-fitur  atau  fix/nama-bug
git checkout -b fitur-stok-otomatis

# 3. Bebas koding dan trial-error di local (http://master-cafe-pos.test)
# Setelah fitur berhasil & lulus uji coba, simpan perubahan:
git add -A
git commit -m "feat: implementasi fitur stok otomatis"

# 4. Push branch fitur ke GitHub
git push --set-upstream origin fitur-stok-otomatis
`

---

## 2. Penggabungan Kode (Merge ke Master)

Setelah fitur di branch diuji dan tidak ada error:

`ash
# Pindah kembali ke branch master lokal
git checkout master

# Gabungkan kode dari branch fitur ke master
git merge fitur-stok-otomatis

# Push master terbaru ke GitHub
git push origin master

# (Opsional) Hapus branch fitur lokal jika sudah selesai
git branch -d fitur-stok-otomatis
`

---

## 3. Deployment ke Server cPanel Live (Secure Architecture)

**Arsitektur Baru:** File inti Laravel (app, routes, .env) **TIDAK AKAN** dipindahkan ke public_html. Mereka akan tetap terkunci aman di /home/nadp3189/repositories/master-cafe-pos. Kita HANYA mengkopi folder public ke internet.

Buka **Terminal cPanel** pada server hosting, lalu salin dan jalankan seluruh blok perintah ini sekaligus (mulai dari cd sampai config:cache):

`ash
# 1. Masuk ke folder repository server
cd /home/nadp3189/repositories/master-cafe-pos && \

# 2. Tarik kode terbaru dari GitHub
git checkout master && \
git pull origin master && \

# 3. Kopi HANYA isi folder public ke Document Root domain
cp -Rf public/* /home/nadp3189/public_html/mastercafe.nadeak.net/ 2>/dev/null || true && \

# 4. [SANGAT PENTING] Suntikkan (Modifikasi) index.php agar mengarah ke folder repositories yang aman
# Langkah ini WAJIB dijalankan setiap kali menyalin isi folder public, agar tidak muncul Error 500!
sed -i "s|__DIR__.'/../vendor/autoload.php'|__DIR__.'/../../repositories/master-cafe-pos/vendor/autoload.php'|g" /home/nadp3189/public_html/mastercafe.nadeak.net/index.php && \
sed -i "s|__DIR__.'/../bootstrap/app.php'|__DIR__.'/../../repositories/master-cafe-pos/bootstrap/app.php'|g" /home/nadp3189/public_html/mastercafe.nadeak.net/index.php && \

# 5. Pastikan izin keamanan standar cPanel terpenuhi (Bukan 777 agar tidak dicekal oleh suPHP)
find storage bootstrap/cache -type d -exec chmod 755 {} \; && \
find storage bootstrap/cache -type f -exec chmod 644 {} \; && \

# 6. Kompilasi ulang Cache agar loading web sangat cepat dan menghindari permission error saat render
php artisan view:cache && \
php artisan route:cache && \
php artisan config:cache
`

---

## Catatan Penting
- **Jangan pernah koding langsung di server live.** Selalu gunakan branch lokal untuk uji coba.
- **Jangan pernah menggunakan chmod 777** pada cPanel/WHM karena akan memicu Security Violation (HTTP Error 500). Gunakan 755 untuk folder dan 644 untuk file.
- **Perintah Deployment:** Selalu jalankan *satu blok perintah di atas secara utuh*, jangan dilewati satupun (terutama bagian sed -i).
- **Folder Gambar:** Folder storage di server adalah folder fisik asli (bukan symlink) untuk menghindari pemblokiran keamanan CageFS cPanel.
