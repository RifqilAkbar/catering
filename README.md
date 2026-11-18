# Dapur Mbak Khotim

Website catering nasi kotak, tumpeng, dan ayam geprek. Situs ini sepenuhnya statis
(HTML, CSS, dan JavaScript) sehingga tidak membutuhkan PHP, MySQL, atau XAMPP.

**Demo online:** https://rifqilakbar.github.io/catering/

## Fitur

- Katalog, pencarian, filter kategori, dan detail produk
- Keranjang dan checkout
- Login/daftar pelanggan serta riwayat pesanan
- Panel admin: dashboard, produk, kategori, pesanan, pelanggan

Semua data demo disimpan di `localStorage` browser, jadi setiap pengunjung
memiliki data sendiri dan bisa dikembalikan ke awal dengan menghapus data situs
di browser. Tambah/edit/hapus produk dan kategori sengaja dinonaktifkan di demo.

## Akun demo

| Peran | Email | Password |
| --- | --- | --- |
| Pelanggan | `pelanggan@dapurcatering.com` | `pelanggan123` |
| Admin | `admin@dapurcatering.com` | `admin123` |

## Menjalankan

Cukup buka `index.html` di browser. Jika ingin memakai server lokal:

```bash
python -m http.server 8000
```

Lalu buka `http://localhost:8000`.

## Struktur

```
index.html            # halaman utama (SPA)
favicon.svg
assets/
  css/
    style.css         # tampilan utama
    ads.css           # landing page gaya iklan Instagram
    demo.css          # penyesuaian demo
    admin.css         # panel admin
  js/
    data.js           # data contoh & penyimpanan localStorage
    core.js           # utilitas, router, state
    views-public.js   # halaman publik (beranda, menu, keranjang, checkout)
    views-admin.js    # halaman admin
    app.js            # inisialisasi aplikasi
  images/             # foto menu
```

## GitHub Pages

Situs ini disajikan dari branch `main`, folder root (`/`). Setelah mengubah
file, push ke `main` dan GitHub Pages akan memperbarui situs dalam 1–2 menit.
