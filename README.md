# Dapur Catering

Website catering nasi kotak, tumpeng, dan ayam geprek. Repositori ini berisi **dua versi**:

| Versi | Lokasi | Kebutuhan |
| --- | --- | --- |
| Demo statis | `docs/` | Browser saja (tanpa XAMPP) |
| Aplikasi PHP + MySQL | root proyek | XAMPP / PHP 8 + MySQL |

## Demo online (GitHub Pages)

**https://rifqilakbar.github.io/catering/**

Demo statis berjalan sepenuhnya di browser:

- Katalog, pencarian, filter kategori, detail produk
- Keranjang dan checkout
- Login/daftar pelanggan dan riwayat pesanan
- Panel admin: dashboard, produk, kategori, pesanan, pelanggan

Data demo disimpan di `localStorage` browser, jadi setiap pengunjung memiliki data sendiri dan bisa dikembalikan ke awal dengan menghapus data situs di browser. Tambah/edit/hapus produk dan kategori sengaja dinonaktifkan di demo.

### Akun demo

| Peran | Email | Password |
| --- | --- | --- |
| Pelanggan | `pelanggan@dapurcatering.com` | `pelanggan123` |
| Admin | `admin@dapurcatering.com` | `admin123` |

> GitHub Pages hanya menyajikan file statis, sehingga PHP dan MySQL tidak berjalan di sana. Versi statis di `docs/` adalah tiruan fungsi utama aplikasi.

## Menjalankan aplikasi PHP di XAMPP

1. Salin folder proyek ke `C:\xampp\htdocs\dapur-mbak-khotim`.
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, lalu impor:
   - `database/schema.sql`
   - `database/seed.sql`
4. Buka `http://localhost/dapur-mbak-khotim/`.

Konfigurasi database default ada di `config/config.php`:

```php
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = dapur_catering
DB_USER = root
DB_PASS = (kosong)
```

Jika MySQL memakai password, sesuaikan `DB_PASS`. Halaman admin: `/admin/login.php`.

## Struktur penting

```
docs/               # demo statis untuk GitHub Pages
  index.html
  assets/css|js/
  images/
admin/              # panel admin PHP
assets/             # CSS/JS aplikasi PHP
config/             # koneksi database & konfigurasi
database/           # schema.sql dan seed.sql
includes/           # fungsi, header, footer, komponen
*.php               # halaman publik (menu, keranjang, checkout, akun)
```
