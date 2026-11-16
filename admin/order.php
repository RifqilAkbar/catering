<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $db->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash('danger', 'Pesanan tidak ditemukan.');
    redirect('admin/orders.php');
}

$statuses = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
$paymentStatuses = ['Belum Dibayar', 'Menunggu Konfirmasi', 'Lunas', 'Gagal'];

$orderItems = order_items($id);

$stmt = $db->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$id]);
$payment = $stmt->fetch() ?: null;

$customer = null;
if (!empty($order['user_id'])) {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$order['user_id']]);
    $customer = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_status') {
        $newStatus = (string) ($_POST['status'] ?? '');
        if (in_array($newStatus, $statuses, true)) {
            $db->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
            flash('success', 'Status pesanan diubah menjadi ' . $newStatus . '.');
        } else {
            flash('danger', 'Status pesanan tidak valid.');
        }
        redirect('admin/order.php?id=' . $id);
    }

    if ($action === 'update_payment') {
        $newPayment = (string) ($_POST['payment_status'] ?? '');
        if (in_array($newPayment, $paymentStatuses, true) && $payment) {
            $paidAt = $newPayment === 'Lunas' ? date('Y-m-d H:i:s') : null;
            $db->prepare('UPDATE payments SET status = ?, paid_at = ? WHERE order_id = ?')
               ->execute([$newPayment, $paidAt, $id]);
            flash('success', 'Status pembayaran diperbarui menjadi ' . $newPayment . '.');
        } else {
            flash('danger', 'Status pembayaran tidak valid.');
        }
        redirect('admin/order.php?id=' . $id);
    }
}

$waNumber = preg_replace('/\D+/', '', $order['customer_phone']);
if (str_starts_with($waNumber, '0')) {
    $waNumber = '62' . substr($waNumber, 1);
}

$pageTitle = 'Detail Pesanan';
$pageSubtitle = $order['order_number'];
$activeMenu = 'orders';
require __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-light btn-sm">&larr; Semua Pesanan</a>
    <a href="https://wa.me/<?= e($waNumber) ?>?text=<?= rawurlencode('Halo ' . $order['customer_name'] . ', mengenai pesanan ' . $order['order_number']) ?>" target="_blank" rel="noopener" class="btn btn-accent btn-sm">Hubungi Pelanggan</a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <div>
            <div class="label">Total Pesanan</div>
            <div class="value"><?= e(rupiah($order['total'])) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="label">Status Pesanan</div>
            <div class="value" style="font-size:1.1rem"><span class="badge <?= e(status_class($order['status'])) ?>"><?= e($order['status']) ?></span></div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="label">Pembayaran</div>
            <div class="value" style="font-size:1.1rem"><?= e($payment['status'] ?? 'Belum Dibayar') ?></div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div>
        <div class="panel">
            <div class="panel-head"><h2>Item Pesanan</h2></div>
            <div class="panel-body flush">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>Produk</th><th>Harga</th><th>Qty</th><th class="text-right">Subtotal</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($item['product_name']) ?></strong>
                                        <?php if (!empty($item['variant_name'])): ?>
                                            <div class="muted" style="font-size:.76rem">Paket: <?= e($item['variant_name']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['addons_list'])): ?>
                                            <div class="muted" style="font-size:.76rem">+ <?= e(implode(', ', array_map(static fn ($a) => $a['name'], $item['addons_list']))) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e(rupiah($item['price'])) ?></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td class="text-right"><?= e(rupiah($item['subtotal'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel-body" style="border-top:1px solid var(--line)">
                    <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:6px"><span class="muted">Subtotal</span><span><?= e(rupiah($order['subtotal'])) ?></span></div>
                    <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem"><span>Total</span><span><?= e(rupiah($order['total'])) ?></span></div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Data Pengiriman</h2></div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field"><label>Nama Pemesan</label><div><?= e($order['customer_name']) ?></div></div>
                    <div class="field"><label>Nomor WhatsApp</label><div><?= e($order['customer_phone']) ?></div></div>
                    <div class="field"><label>Tanggal Pengiriman</label><div><?= e(date('d M Y', strtotime($order['delivery_date']))) ?></div></div>
                    <div class="field"><label>Jam Pengiriman</label><div><?= e(date('H:i', strtotime($order['delivery_time']))) ?> WIB</div></div>
                    <div class="field"><label>Jenis Pesanan</label><div><?= e($order['order_type']) ?></div></div>
                    <div class="field"><label>Metode Pembayaran</label><div><?= e($order['payment_method']) ?></div></div>
                    <div class="field full"><label>Alamat</label><div><?= e($order['address']) ?></div></div>
                    <?php if (!empty($order['notes'])): ?>
                        <div class="field full"><label>Catatan</label><div><?= e($order['notes']) ?></div></div>
                    <?php endif; ?>
                </div>
                <?php if ($customer): ?>
                    <div class="alert alert-info" style="margin-top:16px;margin-bottom:0">
                        Pesanan dibuat oleh akun <strong><?= e($customer['name']) ?></strong> (<?= e($customer['email']) ?>).
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" style="margin-top:16px;margin-bottom:0">Pesanan tamu (tanpa akun).</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-head"><h2>Ubah Status Pesanan</h2></div>
            <div class="panel-body">
                <form method="post" action="<?= url('admin/order.php?id=' . $id) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_status">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-2">Simpan Status</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Status Pembayaran</h2></div>
            <div class="panel-body">
                <form method="post" action="<?= url('admin/order.php?id=' . $id) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_payment">
                    <div class="field">
                        <label for="payment_status">Pembayaran</label>
                        <select id="payment_status" name="payment_status">
                            <?php foreach ($paymentStatuses as $ps): ?>
                                <option value="<?= e($ps) ?>" <?= ($payment['status'] ?? '') === $ps ? 'selected' : '' ?>><?= e($ps) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Metode</label>
                        <div><?= e($order['payment_method']) ?></div>
                    </div>
                    <?php if (!empty($payment['paid_at'])): ?>
                        <div class="field"><label>Dibayar pada</label><div><?= e(date('d M Y H:i', strtotime($payment['paid_at']))) ?></div></div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-accent btn-block mt-2">Simpan Pembayaran</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Ringkasan</h2></div>
            <div class="panel-body">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Dibuat</span><span><?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></span></div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Jumlah item</span><span><?= array_sum(array_map(static fn ($i) => (int) $i['quantity'], $orderItems)) ?> pcs</span></div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Tipe</span><span><?= e($order['order_type']) ?></span></div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
