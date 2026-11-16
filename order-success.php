<?php
require_once __DIR__ . '/config/config.php';

$orderNumber = isset($_GET['order']) ? trim((string) $_GET['order']) : '';
$order = $orderNumber !== '' ? get_order_by_number($orderNumber) : null;
$user = current_user();

$authorized = $order && (
    (($_SESSION['last_order_number'] ?? null) === $orderNumber) ||
    ($user && (int) $order['user_id'] === (int) $user['id'])
);

if (!$authorized) {
    http_response_code(404);
    $pageTitle = 'Pesanan Tidak Ditemukan';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="section section--cream">
        <div class="container">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/></svg>
                </div>
                <h3>Pesanan tidak ditemukan</h3>
                <p>Kami tidak menemukan detail pesanan tersebut. Jika Anda sudah login, silakan cek riwayat pesanan.</p>
                <a href="<?= url($user ? 'account.php' : 'menu.php') ?>" class="btn btn-primary mt-2"><?= $user ? 'Lihat Riwayat Pesanan' : 'Kembali ke Menu' ?></a>
            </div>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$orderItems = order_items((int) $order['id']);
$payment = db()->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$payment->execute([$order['id']]);
$paymentRow = $payment->fetch() ?: null;

$pageTitle = 'Pesanan Berhasil';
require __DIR__ . '/includes/header.php';
?>

<section class="section section--cream">
    <div class="container">
        <div class="success-wrap">
            <div class="text-center">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <h1 style="margin-bottom:6px">Pesanan Berhasil Dibuat!</h1>
                <p class="muted">Terima kasih, <?= e($order['customer_name']) ?>. Pesanan Anda sudah kami terima dan akan segera diproses.</p>
            </div>

            <div class="order-box">
                <div class="order-number">
                    <span>Nomor Pesanan</span>
                    <strong><?= e($order['order_number']) ?></strong>
                </div>

                <div class="info-grid">
                    <div><span>Nama Pemesan</span><strong><?= e($order['customer_name']) ?></strong></div>
                    <div><span>Nomor WhatsApp</span><strong><?= e($order['customer_phone']) ?></strong></div>
                    <div><span>Tanggal Pengiriman</span><strong><?= e(date('d M Y', strtotime($order['delivery_date']))) ?></strong></div>
                    <div><span>Jam Pengiriman</span><strong><?= e(date('H:i', strtotime($order['delivery_time']))) ?> WIB</strong></div>
                    <div><span>Jenis Pesanan</span><strong><?= e($order['order_type']) ?></strong></div>
                    <div><span>Status Pesanan</span><strong><span class="badge <?= e(status_class($order['status'])) ?>"><?= e($order['status']) ?></span></strong></div>
                    <div><span>Metode Pembayaran</span><strong><?= e($order['payment_method']) ?></strong></div>
                    <div><span>Status Pembayaran</span><strong><?= e($paymentRow['status'] ?? 'Belum Dibayar') ?></strong></div>
                    <div style="grid-column:1/-1"><span>Alamat Pengiriman</span><strong><?= e($order['address']) ?></strong></div>
                    <?php if (!empty($order['notes'])): ?>
                        <div style="grid-column:1/-1"><span>Catatan</span><strong><?= e($order['notes']) ?></strong></div>
                    <?php endif; ?>
                </div>

                <div class="divider"></div>

                <h3>Detail Pesanan</h3>
                <ul class="order-items">
                    <?php foreach ($orderItems as $item): ?>
                        <li>
                            <div>
                                <strong><?= (int) $item['quantity'] ?>x</strong> <?= e($item['product_name']) ?>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <div class="oi-addons">Paket: <?= e($item['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['addons_list'])): ?>
                                    <div class="oi-addons">+ <?= e(implode(', ', array_map(static fn ($a) => $a['name'], $item['addons_list']))) ?></div>
                                <?php endif; ?>
                            </div>
                            <span><?= e(rupiah($item['subtotal'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="summary-row total"><span>Total Pembayaran</span><strong><?= e(rupiah($order['total'])) ?></strong></div>

                <?php if ($order['payment_method'] === 'Transfer Bank'): ?>
                    <div class="demo-note" style="margin-top:20px">
                        Silakan transfer sesuai total ke <strong>BCA 1234567890</strong> a.n. Dapur Catering, lalu konfirmasi bukti transfer via WhatsApp.
                    </div>
                <?php elseif ($order['payment_method'] === 'QRIS'): ?>
                    <div class="demo-note" style="margin-top:20px">
                        Pembayaran QRIS akan dikirim admin melalui WhatsApp setelah pesanan dikonfirmasi.
                    </div>
                <?php else: ?>
                    <div class="demo-note" style="margin-top:20px">
                        Pembayaran dilakukan tunai (COD) saat pesanan diantar. Mohon siapkan uang pas.
                    </div>
                <?php endif; ?>

                <div class="detail-actions" style="margin-top:24px">
                    <a href="https://wa.me/6281234567890?text=<?= rawurlencode('Halo, saya ingin konfirmasi pesanan ' . $order['order_number']) ?>" target="_blank" rel="noopener" class="btn btn-primary">Konfirmasi via WhatsApp</a>
                    <?php if ($user): ?>
                        <a href="<?= url('account.php') ?>" class="btn btn-ghost">Riwayat Pesanan</a>
                    <?php else: ?>
                        <a href="<?= url('menu.php') ?>" class="btn btn-ghost">Pesan Lagi</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
