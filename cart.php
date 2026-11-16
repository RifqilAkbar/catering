<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'add') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int) $_POST['variant_id'] : null;
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $note = isset($_POST['note']) ? (string) $_POST['note'] : null;
        $addonIds = $_POST['addons'] ?? [];
        if (!is_array($addonIds)) {
            $addonIds = [];
        }

        [$success, $message] = add_to_cart($productId, $variantId, $quantity, $addonIds, $note);
        flash($success ? 'success' : 'danger', $message);

        if (!$success) {
            redirect('menu.php');
        }
        if (($_POST['buy_now'] ?? '0') === '1') {
            redirect('checkout.php');
        }
        redirect('cart.php');
    }

    if ($action === 'update') {
        update_cart_quantity((int) ($_POST['item_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
        redirect('cart.php');
    }

    if ($action === 'remove') {
        remove_cart_item((int) ($_POST['item_id'] ?? 0));
        flash('success', 'Item berhasil dihapus dari keranjang.');
        redirect('cart.php');
    }

    if ($action === 'clear') {
        clear_cart();
        flash('success', 'Keranjang berhasil dikosongkan.');
        redirect('cart.php');
    }

    redirect('cart.php');
}

$items = cart_items();
$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float) $item['line_total'];
}

$pageTitle = 'Keranjang Belanja';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Keranjang</div>
        <h1>Keranjang Belanja</h1>
        <p>Periksa kembali pesanan Anda sebelum melanjutkan ke checkout.</p>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <?php if (!$items): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.2l2.2 11.2a1.6 1.6 0 0 0 1.6 1.3h9.1a1.6 1.6 0 0 0 1.6-1.3l1.4-7.2H6"/></svg>
                </div>
                <h3>Keranjang Anda masih kosong</h3>
                <p>Yuk pilih menu catering favorit Anda terlebih dahulu.</p>
                <a href="<?= url('menu.php') ?>" class="btn btn-primary mt-2">Lihat Menu Catering</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-list">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <img src="<?= e(product_image($item['image'])) ?>" alt="<?= e($item['name']) ?>">
                            <div>
                                <h3><a href="<?= url('product.php?slug=' . urlencode($item['slug'])) ?>"><?= e($item['name']) ?></a></h3>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <div class="variant">Paket: <?= e($item['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['addons_list'])): ?>
                                    <div class="addons">
                                        Tambahan:
                                        <?= e(implode(', ', array_map(static fn ($a) => $a['name'], $item['addons_list']))) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($item['note'])): ?>
                                    <div class="note">Catatan: <?= e($item['note']) ?></div>
                                <?php endif; ?>
                                <div class="unit"><?= e(rupiah($item['unit_price'])) ?> / item</div>
                            </div>
                            <div class="cart-item-side">
                                <div class="line-total"><?= e(rupiah($item['line_total'])) ?></div>
                                <form method="post" action="<?= url('cart.php') ?>" data-cart-qty>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                    <div class="qty-form">
                                        <button type="button" data-cart-dec aria-label="Kurangi">&minus;</button>
                                        <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" aria-label="Jumlah">
                                        <button type="button" data-cart-inc aria-label="Tambah">+</button>
                                    </div>
                                    <button type="submit" class="btn btn-ghost btn-sm" style="margin-top:8px">Perbarui</button>
                                </form>
                                <form method="post" action="<?= url('cart.php') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="link-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:6px">
                        <a href="<?= url('menu.php') ?>" class="btn btn-ghost btn-sm">&larr; Lanjut Belanja</a>
                        <form method="post" action="<?= url('cart.php') ?>" onsubmit="return confirm('Kosongkan seluruh keranjang?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="link-danger">Kosongkan Keranjang</button>
                        </form>
                    </div>
                </div>

                <aside class="summary-card">
                    <h2>Ringkasan Belanja</h2>
                    <div class="summary-row"><span>Jumlah item</span><span><?= (int) cart_count() ?> item</span></div>
                    <div class="summary-row"><span>Subtotal</span><span><?= e(rupiah($subtotal)) ?></span></div>
                    <div class="summary-row"><span>Pengiriman</span><span>Dihitung saat konfirmasi</span></div>
                    <div class="summary-row total"><span>Total</span><strong><?= e(rupiah($subtotal)) ?></strong></div>
                    <a href="<?= url('checkout.php') ?>" class="btn btn-accent btn-block mt-3">Lanjut ke Checkout</a>
                    <p class="muted text-center" style="font-size:.78rem;margin:14px 0 0">Pembayaran aman &middot; Konfirmasi pesanan via WhatsApp</p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
