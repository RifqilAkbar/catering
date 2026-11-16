<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();
$statuses = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

$statusFilter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
if ($statusFilter !== '' && !in_array($statusFilter, $statuses, true)) {
    $statusFilter = '';
}
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$sql = 'SELECT o.*,
               (SELECT COALESCE(SUM(quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS qty_total,
               (SELECT status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status
        FROM orders o WHERE 1 = 1';
$params = [];

if ($statusFilter !== '') {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}
if ($search !== '') {
    $sql .= ' AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY o.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$counts = [];
foreach ($db->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
$totalAll = array_sum($counts);

$pageTitle = 'Kelola Pesanan';
$pageSubtitle = 'Pantau dan ubah status pesanan pelanggan';
$activeMenu = 'orders';
require __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <form method="get" action="<?= url('admin/orders.php') ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari no. pesanan / nama / telepon...">
        <select name="status">
            <option value="">Semua Status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light btn-sm">Filter</button>
    </form>
</div>

<div class="toolbar" style="justify-content:flex-start">
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-light' ?>">Semua (<?= $totalAll ?>)</a>
    <?php foreach ($statuses as $status): ?>
        <a href="<?= url('admin/orders.php?status=' . urlencode($status)) ?>" class="btn btn-sm <?= $statusFilter === $status ? 'btn-primary' : 'btn-light' ?>"><?= e($status) ?> (<?= (int) ($counts[$status] ?? 0) ?>)</a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-head"><h2>Daftar Pesanan (<?= count($orders) ?>)</h2></div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Diantar</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$orders): ?>
                        <tr><td colspan="8" class="empty-row">Belum ada pesanan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('admin/order.php?id=' . (int) $order['id']) ?>"><strong><?= e($order['order_number']) ?></strong></a>
                                    <div class="muted" style="font-size:.74rem"><?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></div>
                                </td>
                                <td>
                                    <?= e($order['customer_name']) ?>
                                    <div class="muted" style="font-size:.74rem"><?= e($order['customer_phone']) ?></div>
                                </td>
                                <td class="nowrap">
                                    <?= e(date('d M Y', strtotime($order['delivery_date']))) ?>
                                    <div class="muted" style="font-size:.74rem"><?= e(date('H:i', strtotime($order['delivery_time']))) ?> WIB</div>
                                </td>
                                <td><?= (int) $order['qty_total'] ?> pcs</td>
                                <td class="nowrap"><?= e(rupiah($order['total'])) ?></td>
                                <td><span class="badge <?= ($order['payment_status'] ?? '') === 'Lunas' ? 'badge-success' : 'badge-muted' ?>"><?= e($order['payment_status'] ?: 'Belum Dibayar') ?></span></td>
                                <td><span class="badge <?= e(status_class($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                                <td class="text-right"><a href="<?= url('admin/order.php?id=' . (int) $order['id']) ?>" class="btn btn-light btn-sm">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
