<?php
require_once __DIR__ . '/config/config.php';
require_user();

$user = current_user();

$stmt = db()->prepare(
    'SELECT o.*,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
            (SELECT COALESCE(SUM(quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS qty_total
     FROM orders o
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$totalSpent = 0.0;
$activeOrders = 0;
foreach ($orders as $order) {
    if ($order['status'] !== 'Dibatalkan') {
        $totalSpent += (float) $order['total'];
    }
    if (in_array($order['status'], ['Menunggu', 'Diproses', 'Dikirim'], true)) {
        $activeOrders++;
    }
}

$pageTitle = 'Akun Saya';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Beranda</a> / Akun Saya</div>
        <h1>Halo, <?= e($user['name']) ?></h1>
        <p>Kelola data akun dan pantau riwayat pesanan catering Anda.</p>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <div class="cart-layout" style="grid-template-columns:1fr 2.2fr">
            <aside class="summary-card" style="position:static">
                <h2>Profil Saya</h2>
                <div class="info-grid" style="grid-template-columns:1fr">
                    <div><span>Nama</span><strong><?= e($user['name']) ?></strong></div>
                    <div><span>Email</span><strong><?= e($user['email']) ?></strong></div>
                    <div><span>WhatsApp</span><strong><?= e($user['phone'] ?: '-') ?></strong></div>
                    <div><span>Bergabung</span><strong><?= e(date('d M Y', strtotime($user['created_at']))) ?></strong></div>
                </div>
                <div class="divider"></div>
                <div class="summary-row"><span>Total Pesanan</span><strong><?= count($orders) ?></strong></div>
                <div class="summary-row"><span>Pesanan Aktif</span><strong><?= $activeOrders ?></strong></div>
                <div class="summary-row total"><span>Total Belanja</span><strong><?= e(rupiah($totalSpent)) ?></strong></div>
                <a href="<?= url('menu.php') ?>" class="btn btn-primary btn-block mt-3">Pesan Menu Lagi</a>
                <a href="<?= url('logout.php') ?>" class="btn btn-ghost btn-block mt-2">Logout</a>
            </aside>

            <div>
                <div class="summary-card" style="position:static">
                    <h2 style="margin-bottom:6px">Riwayat Pesanan</h2>
                    <p class="muted" style="font-size:.86rem;margin-bottom:18px">Klik nomor pesanan untuk melihat detail lengkap.</p>

                    <?php if (!$orders): ?>
                        <div class="empty-state" style="border:0;padding:40px 10px">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/></svg>
                            </div>
                            <h3>Belum ada pesanan</h3>
                            <p>Pesanan Anda akan muncul di sini setelah checkout.</p>
                            <a href="<?= url('menu.php') ?>" class="btn btn-primary btn-sm mt-2">Mulai Pesan</a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Tanggal Pesan</th>
                                        <th>Item</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('order-success.php?order=' . urlencode($order['order_number'])) ?>"><strong><?= e($order['order_number']) ?></strong></a>
                                            </td>
                                            <td><?= e(date('d M Y', strtotime($order['created_at']))) ?></td>
                                            <td><?= (int) $order['qty_total'] ?> item</td>
                                            <td><?= e(rupiah($order['total'])) ?></td>
                                            <td><span class="badge <?= e(status_class($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
