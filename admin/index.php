<?php
require_once __DIR__ . '/includes/auth.php';

$totalOrders = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue = (float) db()->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'Dibatalkan'")->fetchColumn();
$totalCustomers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalProducts = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$pendingOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'Menunggu'")->fetchColumn();

$recentOrders = db()->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();
$topProducts = db()->query(
    'SELECT product_name, SUM(quantity) AS qty, SUM(subtotal) AS revenue
     FROM order_items GROUP BY product_name ORDER BY qty DESC LIMIT 5'
)->fetchAll();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Ringkasan performa Dapur Catering';
$activeMenu = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/></svg>
        </span>
        <div>
            <div class="label">Total Pesanan</div>
            <div class="value"><?= $totalOrders ?></div>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 6.5c0-2-2.2-3.5-5-3.5S7 4.5 7 6.5 9.2 10 12 10s5 1.5 5 3.5S14.8 17 12 17s-5-1.5-5-3.5"/></svg>
        </span>
        <div>
            <div class="label">Total Pendapatan</div>
            <div class="value"><?= e(rupiah($revenue)) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M17.5 13.5a6.5 6.5 0 0 1 4 6"/></svg>
        </span>
        <div>
            <div class="label">Pelanggan</div>
            <div class="value"><?= $totalCustomers ?></div>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="m3 7.5 9 4.5 9-4.5"/><path d="M12 12v9"/></svg>
        </span>
        <div>
            <div class="label">Produk Menu</div>
            <div class="value"><?= $totalProducts ?></div>
        </div>
    </div>
</div>

<?php if ($pendingOrders > 0): ?>
    <div class="alert alert-warning">
        Ada <strong><?= $pendingOrders ?> pesanan</strong> dengan status <strong>Menunggu</strong> yang perlu diproses.
        <a href="<?= url('admin/orders.php?status=Menunggu') ?>">Lihat pesanan</a>
    </div>
<?php endif; ?>

<div class="grid-2">
    <div class="panel">
        <div class="panel-head">
            <h2>Pesanan Terbaru</h2>
            <a href="<?= url('admin/orders.php') ?>" class="btn btn-light btn-sm">Lihat Semua</a>
        </div>
        <div class="panel-body flush">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recentOrders): ?>
                            <tr><td colspan="5" class="empty-row">Belum ada pesanan masuk.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><a href="<?= url('admin/order.php?id=' . (int) $order['id']) ?>"><strong><?= e($order['order_number']) ?></strong></a></td>
                                    <td><?= e($order['customer_name']) ?></td>
                                    <td><?= e(rupiah($order['total'])) ?></td>
                                    <td><span class="badge <?= e(status_class($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                                    <td class="nowrap"><?= e(date('d M Y', strtotime($order['created_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Produk Terlaris</h2></div>
        <div class="panel-body flush">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Produk</th><th>Terjual</th><th>Pendapatan</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$topProducts): ?>
                            <tr><td colspan="3" class="empty-row">Belum ada data penjualan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td><strong><?= e($product['product_name']) ?></strong></td>
                                    <td><?= (int) $product['qty'] ?> pcs</td>
                                    <td><?= e(rupiah($product['revenue'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Akses Cepat</h2></div>
    <div class="panel-body" style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= url('admin/product-form.php') ?>" class="btn btn-primary">+ Tambah Produk</a>
        <a href="<?= url('admin/categories.php') ?>" class="btn btn-light">Kelola Kategori</a>
        <a href="<?= url('admin/orders.php') ?>" class="btn btn-light">Kelola Pesanan</a>
        <a href="<?= url('admin/customers.php') ?>" class="btn btn-light">Data Pelanggan</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
