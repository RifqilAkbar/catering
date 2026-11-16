<?php
require_once __DIR__ . '/config/config.php';

$categorySlug = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$categories = get_categories();
$activeCategory = $categorySlug !== '' ? get_category_by_slug($categorySlug) : null;
if ($categorySlug !== '' && !$activeCategory) {
    $categorySlug = '';
}

$products = get_products($categorySlug !== '' ? $categorySlug : null, $search !== '' ? $search : null);

$pageTitle = 'Menu Catering';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Menu Catering</div>
        <h1><?= $activeCategory ? e($activeCategory['name']) : 'Menu Catering' ?></h1>
        <p><?= $activeCategory ? e($activeCategory['description'] ?? '') : 'Pilih menu favorit Anda, tentukan paket, jumlah, dan tambahan sesuai kebutuhan acara.' ?></p>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <div class="toolbar">
            <div class="filter-chips">
                <a class="chip <?= $categorySlug === '' ? 'active' : '' ?>" href="<?= url('menu.php') ?>">Semua</a>
                <?php foreach ($categories as $category): ?>
                    <a class="chip <?= $categorySlug === $category['slug'] ? 'active' : '' ?>"
                       href="<?= url('menu.php?category=' . urlencode($category['slug'])) ?>">
                        <?= e($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form class="search-form" method="get" action="<?= url('menu.php') ?>">
                <?php if ($categorySlug !== ''): ?>
                    <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
                <?php endif; ?>
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari menu..." aria-label="Cari menu">
                <button class="btn btn-primary btn-sm" type="submit">Cari</button>
            </form>
        </div>

        <?php if ($search !== ''): ?>
            <p class="muted" style="margin-top:-12px;margin-bottom:24px">
                Hasil pencarian untuk <strong><?= e($search) ?></strong> &middot; <?= count($products) ?> menu ditemukan.
                <a href="<?= url('menu.php' . ($categorySlug !== '' ? '?category=' . urlencode($categorySlug) : '')) ?>">Hapus pencarian</a>
            </p>
        <?php endif; ?>

        <?php if (!$products): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                </div>
                <h3>Menu tidak ditemukan</h3>
                <p>Coba kata kunci lain atau lihat semua menu yang tersedia.</p>
                <a href="<?= url('menu.php') ?>" class="btn btn-primary mt-2">Lihat Semua Menu</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
