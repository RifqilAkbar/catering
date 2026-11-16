<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Beranda';
$categories = get_categories();
$featuredProducts = get_products(null, null, true, 8);

$countStmt = db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = ? AND is_available = 1');

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-inner">
            <span class="eyebrow">Catering Rumahan, Kualitas Restoran</span>
            <h1>Solusi Catering <em>Lezat</em> untuk Setiap Acara</h1>
            <p>Dari rapat kantor, syukuran keluarga, hingga pernikahan — kami siapkan sajian segar, higienis, dan tepat waktu. Pesan online, kami antar ke lokasi Anda.</p>
            <div class="hero-actions">
                <a href="<?= url('menu.php') ?>" class="btn btn-accent">Pesan Sekarang</a>
                <a href="#kategori" class="btn btn-outline">Lihat Kategori</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><strong>500+</strong><span>Pesanan Selesai</span></div>
                <div class="hero-stat"><strong>4.9/5</strong><span>Rating Pelanggan</span></div>
                <div class="hero-stat"><strong>3</strong><span>Kategori Favorit</span></div>
            </div>
        </div>
    </div>
    <div class="hero-card">
        <span class="round-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div>
            <strong>Pesan hari ini, antar besok</strong>
            <span>Gratis konsultasi paket via WhatsApp</span>
        </div>
    </div>
</section>

<section class="section section--cream" id="kategori">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Pilihan Kategori</span>
            <h2>Sajian untuk Berbagai Momen</h2>
            <p>Pilih kategori yang sesuai kebutuhan acara Anda. Semua menu dapat disesuaikan dengan jumlah tamu dan selera.</p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <?php
                $countStmt->execute([$category['id']]);
                $productCount = (int) $countStmt->fetchColumn();
                ?>
                <a class="category-card reveal" href="<?= url('menu.php?category=' . urlencode($category['slug'])) ?>">
                    <img src="<?= e(product_image($category['image'])) ?>" alt="<?= e($category['name']) ?>" loading="lazy">
                    <div class="category-body">
                        <h3><?= e($category['name']) ?></h3>
                        <p><?= e(excerpt($category['description'] ?? '', 90)) ?></p>
                        <span class="text-link"><?= $productCount ?> pilihan menu &rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--white" id="menu-terlaris">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Menu Terlaris</span>
            <h2>Paling Sering Dipesan</h2>
            <p>Menu favorit pelanggan Dapur Catering yang siap memanjakan lidah para tamu Anda.</p>
        </div>

        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= url('menu.php') ?>" class="btn btn-primary">Lihat Semua Menu</a>
        </div>
    </div>
</section>

<section class="section section--cream" id="tentang">
    <div class="container">
        <div class="split">
            <div class="split-media reveal">
                <img src="<?= e(product_image('images/bg7.jpg')) ?>" alt="Dapur Catering menyiapkan hidangan">
                <div class="split-badge">
                    <strong>Sejak 2018</strong>
                    <span>Melayani dengan sepenuh hati</span>
                </div>
            </div>
            <div class="reveal">
                <span class="eyebrow">Tentang Kami</span>
                <h2>Dapur Rumahan dengan Standar Profesional</h2>
                <p>Dapur Catering berawal dari dapur kecil keluarga yang senang berbagi masakan rumahan. Kini kami melayani ratusan pesanan setiap bulan dengan dapur yang bersih, bahan segar, dan tim yang berpengalaman.</p>
                <ul class="check-list">
                    <li>Bahan segar dipilih setiap hari, tanpa pengawet.</li>
                    <li>Dikemas higienis dan rapi, siap disajikan.</li>
                    <li>Pengiriman tepat waktu sesuai jadwal acara Anda.</li>
                    <li>Harga transparan dan bisa disesuaikan dengan anggaran.</li>
                </ul>
                <a href="<?= url('menu.php') ?>" class="btn btn-primary">Jelajahi Menu Kami</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--white" id="keunggulan">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Mengapa Memilih Kami</span>
            <h2>Keunggulan Layanan Dapur Catering</h2>
            <p>Kami tidak hanya menyajikan makanan, tetapi juga ketenangan pikiran untuk acara Anda.</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.4-3 7.4-7 9-4-1.6-7-4.6-7-9V6l7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <h3>Bahan Segar &amp; Halal</h3>
                <p>Kami menggunakan bahan pilihan yang segar dan halal untuk setiap hidangan.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </div>
                <h3>Tepat Waktu</h3>
                <p>Pesanan diantar sesuai jadwal yang Anda tentukan, tanpa membuat tamu menunggu.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10l8-6 8 6v10"/><path d="M9 20v-6h6v6"/></svg>
                </div>
                <h3>Harga Terjangkau</h3>
                <p>Paket fleksibel mulai dari nasi kotak ekonomis hingga tumpeng premium.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>
                </div>
                <h3>Pelayanan Ramah</h3>
                <p>Tim kami siap membantu konsultasi menu dan kebutuhan acara Anda.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--cream" id="testimoni">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Testimoni</span>
            <h2>Apa Kata Pelanggan Kami</h2>
            <p>Kepuasan pelanggan adalah prioritas utama kami di setiap pesanan.</p>
        </div>

        <div class="testimonial-grid">
            <div class="testimonial-card reveal">
                <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"Nasi kotaknya enak dan porsinya pas. Acara rapat kantor kami berjalan lancar karena pengirimannya tepat waktu."</p>
                <div class="testimonial-author">
                    <div class="avatar">RN</div>
                    <div><strong>Rina Nurhaliza</strong><span>Rapat Kantor</span></div>
                </div>
            </div>
            <div class="testimonial-card reveal">
                <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"Tumpengnya cantik dan rasanya luar biasa. Keluarga besar puas, dekorasinya juga rapi. Pasti pesan lagi."</p>
                <div class="testimonial-author">
                    <div class="avatar">BS</div>
                    <div><strong>Budi Santoso</strong><span>Syukuran Keluarga</span></div>
                </div>
            </div>
            <div class="testimonial-card reveal">
                <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <p>"Ayam gepreknya pedasnya pas dan masih hangat saat sampai. Paket komplitnya worthed banget untuk makan siang."</p>
                <div class="testimonial-author">
                    <div class="avatar">DA</div>
                    <div><strong>Dewi Anggraini</strong><span>Pelanggan Setia</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="cta-band reveal">
            <div>
                <h2>Siap memesan untuk acara Anda?</h2>
                <p>Konsultasikan kebutuhan catering Anda sekarang, kami bantu pilih paket terbaik.</p>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <a href="<?= url('menu.php') ?>" class="btn btn-light">Pesan Sekarang</a>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="btn btn-outline" style="color:#fff">Chat WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
