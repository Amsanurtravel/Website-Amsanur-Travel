# Website Amsa Nur Tour & Travel

Situs PT Amsa Nur Indah Mandiri (umroh dan haji khusus). Dibangun dengan Laravel 13 (PHP 8.5), panel admin Filament 5 di `/admin`, Tailwind 4, dan SQLite.

## Menjalankan di komputer

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Buat akun admin dengan `php artisan make:filament-user`.

## Pratinjau untuk klien (Vercel)

Vercel tidak menjalankan PHP. Karena itu, yang tayang di <https://websiteamsanurtravel.vercel.app> adalah salinan statis halaman publik di folder `preview/`, dibuat oleh `php artisan site:export`.

- Panel admin tidak ikut. Isi diubah lewat admin di komputer, lalu diekspor ulang.
- Tombol WhatsApp langsung ke wa.me, jadi klik tidak tercatat di statistik admin.
- Pratinjau tidak diindeks Google (header `X-Robots-Tag: noindex` di `vercel.json` dan `robots.txt`).
- Tanggal membeku saat ekspor, misalnya "Keberangkatan terdekat". Ekspor ulang setelah ada keberangkatan yang lewat.

Memperbarui pratinjau:

```bash
npm run build
php artisan site:export
git add -A
git commit -m "Perbarui pratinjau"
git push
```

Vercel menayangkan versi baru otomatis setelah `git push`.

## Tes

```bash
php artisan test
```
