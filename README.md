# Dapur Mbak Khotim — Prototipe Konsep

Versi dummy website catering untuk menjelaskan konsep kepada dosen.
Isinya hanya **HTML, CSS, dan JavaScript** — tanpa foto, PHP, MySQL, atau
pustaka eksternal.

**Demo online:** https://rifqilakbar.github.io/catering/

## Isi proyek

```
index.html   # struktur halaman dan penjelasan konsep
style.css    # tampilan minimalis dan responsif
script.js    # data menu dummy, filter, keranjang, simulasi checkout
```

## Bagian halaman

1. **Konsep** — ringkasan ide dan angka penting.
2. **Alur** — langkah pelanggan dan admin.
3. **Menu** — daftar menu dummy dengan filter kategori.
4. **Simulasi** — keranjang interaktif dan checkout dummy.
5. **Teknologi** — peran HTML, CSS, JavaScript, dan batasan versi dummy.

## Cara menjalankan

Cukup buka `index.html` di browser. Bisa juga memakai server lokal:

```bash
python -m http.server 8000
```

Lalu buka `http://localhost:8000`.

## Catatan presentasi

- Semua data menu adalah contoh dan ditulis langsung di `script.js`.
- Keranjang disimpan di `localStorage` browser, jadi tetap ada saat halaman
  dimuat ulang.
- Checkout hanya simulasi; tidak ada data yang dikirim ke server.
- Tidak ada file gambar sama sekali. Tampilan dibuat dengan CSS.
