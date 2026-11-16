<?php
/** @var array $product */
$link = url('product.php?slug=' . urlencode($product['slug']));
?>
<article class="product-card reveal">
    <div class="product-thumb">
        <a href="<?= $link ?>" aria-label="<?= e($product['name']) ?>">
            <img src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        </a>
        <?php if (!empty($product['is_featured'])): ?>
            <span class="product-flag">Terlaris</span>
        <?php endif; ?>
    </div>
    <div class="product-body">
        <span class="product-cat"><?= e($product['category_name'] ?? '') ?></span>
        <h3><a href="<?= $link ?>"><?= e($product['name']) ?></a></h3>
        <p class="product-desc"><?= e(excerpt($product['description'] ?? '', 90)) ?></p>
        <div class="product-price">
            <small>mulai dari</small><br>
            <?= e(product_price_label($product)) ?>
        </div>
        <div class="product-actions">
            <a class="btn btn-primary btn-sm" href="<?= $link ?>">Pesan Sekarang</a>
        </div>
    </div>
</article>
