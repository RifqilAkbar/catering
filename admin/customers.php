<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$sql = 'SELECT u.*,
               (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count,
               (SELECT COALESCE(SUM(total), 0) FROM orders o WHERE o.user_id = u.id AND o.status <> "Dibatalkan") AS total_spent,
               (SELECT MAX(created_at) FROM orders o WHERE o.user_id = u.id) AS last_order
        FROM users u WHERE 1 = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY u.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$guestOrders = (int) $db->query('SELECT COUNT(*) FROM orders WHERE user_id IS NULL')->fetchColumn();

$pageTitle = 'Data Pelanggan';
$pageSubtitle = 'Daftar pelanggan terdaftar dan riwayat belanjanya';
$activeMenu = 'customers';
require __DIR__ . '/includes/header.php';
?>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <span class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M17.5 13.5a6.5 6.5 0 0 1 4 6"/></svg>
        </span>
        <div><div class="label">Pelanggan Terdaftar</div><div class="value"><?= count($customers) ?></div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/></svg>
        </span>
        <div><div class="label">Pesanan Tamu</div><div class="value"><?= $guestOrders ?></div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 6.5c0-2-2.2-3.5-5-3.5S7 4.5 7 6.5 9.2 10 12 10s5 1.5 5 3.5S14.8 17 12 17s-5-1.5-5-3.5"/></svg>
        </span>
        <div><div class="label">Total Pelanggan</div><div class="value"><?= count($customers) + $guestOrders ?></div></div>
    </div>
</div>

<div class="toolbar">
    <form method="get" action="<?= url('admin/customers.php') ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari nama, email, telepon...">
        <button type="submit" class="btn btn-light btn-sm">Cari</button>
    </form>
</div>

<div class="panel">
    <div class="panel-head"><h2>Daftar Pelanggan (<?= count($customers) ?>)</h2></div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th>Pesanan</th>
                        <th>Total Belanja</th>
                        <th>Terakhir Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$customers): ?>
                        <tr><td colspan="6" class="empty-row">Belum ada pelanggan terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;gap:10px;align-items:center">
                                        <span class="admin-user"><span class="avatar"><?= e(strtoupper(mb_substr($customer['name'], 0, 1))) ?></span></span>
                                        <strong><?= e($customer['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= e($customer['email']) ?></td>
                                <td><?= e($customer['phone'] ?: '-') ?></td>
                                <td><?= (int) $customer['order_count'] ?> pesanan</td>
                                <td class="nowrap"><?= e(rupiah($customer['total_spent'])) ?></td>
                                <td class="nowrap"><?= $customer['last_order'] ? e(date('d M Y', strtotime($customer['last_order']))) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
