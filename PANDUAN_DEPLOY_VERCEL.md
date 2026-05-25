# Panduan Deploy Vercel + TiDB

## File yang disiapkan

- `vercel.json` menjalankan Laravel lewat `api/index.php` memakai runtime `vercel-php@0.6.2`.
- `api/index.php` menyiapkan storage sementara di `/tmp` saat berjalan di Vercel.
- `config/database.php` mendukung `MYSQL_ATTR_SSL_CA` relatif, misalnya `storage/certs/tidb-ca.pem`.
- `.env.example` sudah berisi contoh env production untuk Vercel + TiDB.

## Environment Variable Vercel

File `.env` di project ini sudah dirapikan menjadi format production untuk Vercel + TiDB. Cara paling mudah:

1. Buka Vercel Project Settings > Environment Variables.
2. Pilih import dari `.env` atau paste isi file `.env`.
3. Aktifkan untuk Production, Preview, dan Development jika semuanya boleh memakai database TiDB yang sama.
4. Ubah `APP_URL` jika domain Vercel/custom domain berbeda dari isi `.env`.

Isi variable minimalnya seperti ini:

```env
APP_NAME="KELAS CATUR"
APP_ENV=production
APP_KEY=base64:isi-dari-php-artisan-key-generate
APP_DEBUG=false
APP_URL=https://domain-vercel-atau-domain-custom

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com
DB_PORT=4000
DB_DATABASE=kelas_catur
DB_USERNAME=username_tidb
DB_PASSWORD=password_tidb
MYSQL_ATTR_SSL_CA=storage/certs/tidb-ca.pem
DB_CONNECT_TIMEOUT=10

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

AUTH_LOGIN_BYPASS=false
AUTH_LOGIN_BYPASS_NAME="ADMIN KELAS CATUR"
AUTH_LOGIN_BYPASS_EMAIL=adminkc@gmail.com

MAIL_MAILER=log
MAIL_FROM_ADDRESS="admin@kelascatur.local"
MAIL_FROM_NAME="${APP_NAME}"
VITE_APP_NAME="${APP_NAME}"
```

Pastikan `storage/certs/tidb-ca.pem` ikut masuk Git/deployment karena TiDB Cloud memakai koneksi SSL.

## Migrasi Database TiDB

Jalankan dari lokal dengan `.env` yang sudah mengarah ke TiDB:

```bash
php artisan migrate:status
php artisan migrate --force
php artisan db:seed --force
```

Kalau `migrate:status` menampilkan `Migration table not found`, koneksi TiDB sudah terbaca tetapi schema Laravel belum dibuat.

Kalau sudah punya data lokal MySQL/XAMPP dan ingin dipindahkan ke TiDB, export database lokal lalu import ke TiDB:

```bash
mysqldump -h 127.0.0.1 -P 3306 -u root kelas_catur > kelas_catur_local.sql
mysql -h gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com -P 4000 -u username_tidb -p --ssl-ca=storage/certs/tidb-ca.pem kelas_catur < kelas_catur_local.sql
```

Untuk database TiDB kosong dari migration Laravel, cukup pakai `php artisan migrate --force`.

## Deploy

```bash
vercel
vercel --prod
```

Setelah deploy, buka `/up` untuk health check Laravel, lalu coba login dan halaman dashboard.
