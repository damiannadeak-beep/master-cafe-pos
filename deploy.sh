#!/bin/bash
set -e

echo "=== Memulai Update & Deployment Master Cafe POS ==="

# 1. Masuk ke direktori repository
cd /home/nadp3189/repositories/master-cafe-pos

# 2. Tarik kode terbaru dari GitHub
git checkout master
git pull origin master

# 3. Kopi isi folder public (termasuk CSS/JS build terbaru) ke Document Root
echo "Menyinkronkan aset public..."
cp -Rf public/* /home/nadp3189/public_html/mastercafe.nadeak.net/ 2>/dev/null || true

# 4. Sesuaikan path index.php di Document Root
sed -i "s|__DIR__.'/../vendor/autoload.php'|__DIR__.'/../../repositories/master-cafe-pos/vendor/autoload.php'|g" /home/nadp3189/public_html/mastercafe.nadeak.net/index.php
sed -i "s|__DIR__.'/../bootstrap/app.php'|__DIR__.'/../../repositories/master-cafe-pos/bootstrap/app.php'|g" /home/nadp3189/public_html/mastercafe.nadeak.net/index.php

# 5. Atur permission standar cPanel
find storage bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null || true

# 6. Refresh cache Laravel
php artisan view:cache
php artisan route:cache
php artisan config:cache

echo "=== Deployment Selesai! Web kembali rapi & normal. ==="
