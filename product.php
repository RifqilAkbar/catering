<?php
require_once __DIR__ . '/config/config.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$product = $slug !== '' ? get_product_by_slug($slug) : null;

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Menu Tidak Ditemukan';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="section section--cream">
        <div class="container">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                </div>
                <h3>Menu tidak ditemukan</h3>
                <p>Menu yang Anda cari mungkin sudah tidak tersedia.</p>
                <a href="<?= url('menu.php') ?>" class="btn btn-primary mt-2">Kembali ke Menu</a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$variants = get_product_variants((int) $product['id']);
$addons = get_product_addons((int) $product['id']);
$defaultVariant = $variants[0] ?? null;
$displayPrice = $defaultVariant ? (float) $defaultVariant['price'] : (float) $product['base_price'];
$priceLabel = product_price_label(array_merge($product, [
    'min_price' => $variants ? min(array_map(static fn ($v) => (float) $v['price'], $variants)) : (float) $product['base_price'],
    'max_price' => $variants ? max(array_map(static fn ($v) => (float) $v['price'], $variants)) : (float) $product['base_price'],
]));

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('index.php') ?>">Beranda</a> /
            <a href="<?= url('menu.php?category=' . urlencode($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a> /
            <?= e($product['name']) ?>
        </div>
        <h1><?= e($product['name']) ?></h1>
        <p>Atur paket, jumlah, dan tambahan sesuai kebutuhan pesanan Anda.</p>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <div class="product-detail">
            <div class="detail-media reveal">
                <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>">
            </div>

            <div class="reveal">
                <span class="detail-cat"><?= e($product['category_name']) ?></span>
                <h1 class="detail-title"><?= e($product['name']) ?></h1>
                <div class="detail-price">
                    <?= e($priceLabel) ?>
                    <small><?= $variants ? 'sesuai paket' : 'per porsi' ?></small>
                </div>
                <p class="detail-desc"><?= nl2br(e($product['description'] ?? '')) ?></p>

                <?php if (!(int) $product['is_available'] || (int) $product['stock'] === 0): ?>
                    <div class="alert alert-warning" style="box-shadow:none">Menu ini sedang tidak tersedia. Silakan pilih menu lain.</div>
                <?php else: ?>
                <form
                    data-product-form
                    data-base-price="<?= e((string) $displayPrice) ?>"
                    data-api="<?= url('api/cart.php') ?>"
                    data-checkout="<?= url('checkout.php') ?>"
                    method="post"
                    action="<?= url('cart.php') ?>"
                >
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                    <?php if ($variants): ?>
                        <div class="option-group">
                            <label class="option-label">Pilih Paket / Ukuran</label>
                            <div class="choice-grid">
                                <?php foreach ($variants as $index => $variant): ?>
                                    <label class="choice">
                                        <input type="radio" name="variant_id" value="<?= (int) $variant['id'] ?>"
                                               data-price="<?= e((string) $variant['price']) ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                        <span>
                                            <?= e($variant['name']) ?>
                                            <small><?= e(rupiah($variant['price'])) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($addons): ?>
                        <div class="option-group">
                            <label class="option-label">Tambahan (opsional)</label>
                            <div class="choice-grid">
                                <?php foreach ($addons as $addon): ?>
                                    <label class="choice">
                                        <input type="checkbox" name="addons[]" value="<?= (int) $addon['id'] ?>"
                                               data-price="<?= e((string) $addon['price']) ?>">
                                        <span>
                                            <?= e($addon['name']) ?>
                                            <small>+<?= e(rupiah($addon['price'])) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="option-group">
                        <label class="option-label" for="product-qty">Jumlah Pesanan</label>
                        <div class="qty-stepper">
                            <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
                            <input id="product-qty" type="number" name="quantity" value="1" min="1" data-qty-input>
                            <button type="button" data-step="1" aria-label="Tambah">+</button>
                        </div>
                    </div>

                    <div class="option-group">
                        <label class="option-label" for="product-note">Catatan (opsional)</label>
                        <textarea id="product-note" class="detail-note" name="note" rows="2" placeholder="Contoh: sambal dipisah, tanpa mie, dll."></textarea>
                    </div>

                    <div class="detail-total">
                        <span>Total estimasi</span>
                        <strong data-detail-total><?= e(rupiah($displayPrice)) ?></strong>
                    </div>

                    <div class="detail-actions">
                        <button type="submit" name="buy_now" value="0" class="btn btn-ghost">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.2l2.2 11.2a1.6 1.6 0 0 0 1.6 1.3h9.1a1.6 1.6 0 0 0 1.6-1.3l1.4-7.2H6"/></svg>
                            Tambah ke Keranjang
                        </button>
                        <button type="submit" name="buy_now" value="1" class="btn btn-accent">Pesan Sekarang</button>
                    </div>

                    <ul class="meta-list">
                        <li><span>Kategori</span><strong><?= e($product['category_name']) ?></strong></li>
                        <li><span>Ketersediaan</span><strong>Stok tersedia (<?= (int) $product['stock'] ?>)</strong></li>
                        <li><span>Pemesanan</span><strong>Minimal 1 hari sebelum acara</strong></li>
                    </ul>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
