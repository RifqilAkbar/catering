<?php
require_once __DIR__ . '/config/config.php';

$user = current_user();
$items = cart_items();

if (!$items) {
    flash('warning', 'Keranjang Anda kosong. Silakan pilih menu terlebih dahulu.');
    redirect('menu.php');
}

$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float) $item['line_total'];
}

$orderTypes = ['Acara Pribadi', 'Rapat', 'Ulang Tahun', 'Pernikahan', 'Lainnya'];
$paymentMethods = ['Transfer Bank', 'QRIS', 'COD'];

$form = [
    'customer_name'  => $user['name'] ?? '',
    'customer_phone' => $user['phone'] ?? '',
    'address'        => '',
    'delivery_date'  => date('Y-m-d', strtotime('+1 day')),
    'delivery_time'  => '10:00',
    'order_type'     => 'Acara Pribadi',
    'notes'          => '',
    'payment_method' => 'Transfer Bank',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach (array_keys($form) as $key) {
        if (isset($_POST[$key])) {
            $form[$key] = trim((string) $_POST[$key]);
        }
    }

    if (mb_strlen($form['customer_name']) < 2) {
        $errors['customer_name'] = 'Nama lengkap minimal 2 karakter.';
    }
    if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $form['customer_phone'])) {
        $errors['customer_phone'] = 'Nomor WhatsApp tidak valid (8-20 digit).';
    }
    if (mb_strlen($form['address']) < 10) {
        $errors['address'] = 'Alamat pengiriman minimal 10 karakter.';
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $form['delivery_date']);
    $today = new DateTime('today');
    if (!$dateObj || $dateObj->format('Y-m-d') !== $form['delivery_date']) {
        $errors['delivery_date'] = 'Tanggal pengiriman tidak valid.';
    } elseif ($dateObj < $today) {
        $errors['delivery_date'] = 'Tanggal pengiriman tidak boleh di masa lalu.';
    }

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $form['delivery_time'])) {
        $errors['delivery_time'] = 'Jam pengiriman tidak valid.';
    }
    if (!in_array($form['order_type'], $orderTypes, true)) {
        $errors['order_type'] = 'Jenis pesanan tidak valid.';
    }
    if (!in_array($form['payment_method'], $paymentMethods, true)) {
        $errors['payment_method'] = 'Metode pembayaran tidak valid.';
    }
    if (mb_strlen($form['notes']) > 500) {
        $errors['notes'] = 'Catatan maksimal 500 karakter.';
    }

    if (!$errors) {
        $db = db();
        try {
            $db->beginTransaction();

            $orderNumber = generate_order_number();
            $stmt = $db->prepare('SELECT id FROM orders WHERE order_number = ?');
            $attempts = 0;
            while ($attempts < 5) {
                $stmt->execute([$orderNumber]);
                if (!$stmt->fetchColumn()) {
                    break;
                }
                $orderNumber = generate_order_number();
                $attempts++;
            }

            $db->prepare(
                'INSERT INTO orders
                    (order_number, user_id, customer_name, customer_phone, address, delivery_date,
                     delivery_time, order_type, notes, subtotal, total, payment_method, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $orderNumber,
                $user['id'] ?? null,
                $form['customer_name'],
                $form['customer_phone'],
                $form['address'],
                $form['delivery_date'],
                $form['delivery_time'],
                $form['order_type'],
                $form['notes'] !== '' ? $form['notes'] : null,
                $subtotal,
                $subtotal,
                $form['payment_method'],
                'Menunggu',
            ]);

            $orderId = (int) $db->lastInsertId();

            $insertItem = $db->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, product_name, variant_name, addons, price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $updateStock = $db->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');

            foreach ($items as $item) {
                $insertItem->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['variant_name'] ?? null,
                    json_encode($item['addons_list'], JSON_UNESCAPED_UNICODE),
                    $item['unit_price'],
                    $item['quantity'],
                    $item['line_total'],
                ]);
                $updateStock->execute([(int) $item['quantity'], $item['product_id']]);
            }

            $db->prepare(
                'INSERT INTO payments (order_id, method, amount, status) VALUES (?, ?, ?, ?)'
            )->execute([$orderId, $form['payment_method'], $subtotal, 'Belum Dibayar']);

            clear_cart();
            $db->commit();

            $_SESSION['last_order_number'] = $orderNumber;
            $_SESSION['last_order_guest'] = $user ? false : true;

            redirect('order-success.php?order=' . urlencode($orderNumber));
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors['general'] = 'Gagal membuat pesanan: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('index.php') ?>">Beranda</a> /
            <a href="<?= url('cart.php') ?>">Keranjang</a> / Checkout
        </div>
        <h1>Checkout Pesanan</h1>
        <p>Lengkapi data pengiriman dan konfirmasi pesanan catering Anda.</p>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" style="margin-bottom:20px"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <div class="cart-layout">
            <form class="form-card" method="post" action="<?= url('checkout.php') ?>" novalidate>
                <?= csrf_field() ?>

                <div class="form-section">
                    <h2 class="fieldset-title">Data Pemesan</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="customer_name">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" value="<?= e($form['customer_name']) ?>" required>
                            <?php if (!empty($errors['customer_name'])): ?><div class="error-text"><?= e($errors['customer_name']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="customer_phone">Nomor WhatsApp <span class="req">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" value="<?= e($form['customer_phone']) ?>" placeholder="0812xxxxxxx" required>
                            <?php if (!empty($errors['customer_phone'])): ?><div class="error-text"><?= e($errors['customer_phone']) ?></div><?php endif; ?>
                        </div>
                        <div class="field full">
                            <label for="address">Alamat Pengiriman <span class="req">*</span></label>
                            <textarea id="address" name="address" rows="3" required placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, dan patokan"><?= e($form['address']) ?></textarea>
                            <?php if (!empty($errors['address'])): ?><div class="error-text"><?= e($errors['address']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="fieldset-title">Jadwal &amp; Detail Acara</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="delivery_date">Tanggal Pengiriman <span class="req">*</span></label>
                            <input type="date" id="delivery_date" name="delivery_date" value="<?= e($form['delivery_date']) ?>" min="<?= e(date('Y-m-d')) ?>" required>
                            <?php if (!empty($errors['delivery_date'])): ?><div class="error-text"><?= e($errors['delivery_date']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="delivery_time">Jam Pengiriman <span class="req">*</span></label>
                            <input type="time" id="delivery_time" name="delivery_time" value="<?= e($form['delivery_time']) ?>" required>
                            <?php if (!empty($errors['delivery_time'])): ?><div class="error-text"><?= e($errors['delivery_time']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="order_type">Jenis Pesanan <span class="req">*</span></label>
                            <select id="order_type" name="order_type" required>
                                <?php foreach ($orderTypes as $type): ?>
                                    <option value="<?= e($type) ?>" <?= $form['order_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['order_type'])): ?><div class="error-text"><?= e($errors['order_type']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="payment_method">Metode Pembayaran <span class="req">*</span></label>
                            <select id="payment_method" name="payment_method" required>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?= e($method) ?>" <?= $form['payment_method'] === $method ? 'selected' : '' ?>><?= e($method) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['payment_method'])): ?><div class="error-text"><?= e($errors['payment_method']) ?></div><?php endif; ?>
                        </div>
                        <div class="field full">
                            <label for="notes">Catatan Tambahan</label>
                            <textarea id="notes" name="notes" rows="3" maxlength="500" placeholder="Contoh: tanpa sambal, tambah sendok, dekorasi warna hijau, dll."><?= e($form['notes']) ?></textarea>
                            <?php if (!empty($errors['notes'])): ?><div class="error-text"><?= e($errors['notes']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!$user): ?>
                    <div class="demo-note" style="margin-top:24px">
                        Anda memesan sebagai tamu. Ingin menyimpan riwayat pesanan?
                        <a href="<?= url('login.php') ?>">Login</a> atau <a href="<?= url('register.php') ?>">daftar akun</a>.
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-accent btn-block mt-4">
                    Buat Pesanan &middot; <?= e(rupiah($subtotal)) ?>
                </button>
            </form>

            <aside class="summary-card">
                <h2>Ringkasan Pesanan</h2>
                <ul class="order-items">
                    <?php foreach ($items as $item): ?>
                        <li>
                            <div>
                                <strong><?= (int) $item['quantity'] ?>x</strong> <?= e($item['name']) ?>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <div class="oi-addons">Paket: <?= e($item['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['addons_list'])): ?>
                                    <div class="oi-addons">+ <?= e(implode(', ', array_map(static fn ($a) => $a['name'], $item['addons_list']))) ?></div>
                                <?php endif; ?>
                            </div>
                            <span><?= e(rupiah($item['line_total'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="summary-row" style="margin-top:10px"><span>Subtotal</span><span><?= e(rupiah($subtotal)) ?></span></div>
                <div class="summary-row"><span>Pengiriman</span><span>Konfirmasi admin</span></div>
                <div class="summary-row total"><span>Total</span><strong><?= e(rupiah($subtotal)) ?></strong></div>
                <a href="<?= url('cart.php') ?>" class="btn btn-ghost btn-block mt-3">Ubah Keranjang</a>
            </aside>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
