<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect('account.php');
}

$form = ['name' => '', 'email' => '', 'phone' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['name'] = trim((string) ($_POST['name'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $form['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (mb_strlen($form['name']) < 2) {
        $errors['name'] = 'Nama minimal 2 karakter.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    if ($form['phone'] !== '' && !preg_match('/^[0-9+\-\s()]{8,20}$/', $form['phone'])) {
        $errors['phone'] = 'Nomor WhatsApp tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }
    if ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$form['email']]);
        if ($stmt->fetchColumn()) {
            $errors['email'] = 'Email sudah terdaftar. Silakan login.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $form['name'],
            $form['email'],
            $form['phone'] !== '' ? $form['phone'] : null,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        $_SESSION['user_id'] = (int) db()->lastInsertId();
        merge_guest_cart_into_user((int) $_SESSION['user_id']);
        flash('success', 'Akun berhasil dibuat. Selamat datang di Dapur Catering!');
        redirect('account.php');
    }
}

$pageTitle = 'Daftar Akun';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M5 17a7 7 0 0 1 14 0"/><path d="M12 6V3"/><path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/></svg>
            </span>
            <span>Dapur Catering</span>
        </a>
        <h1>Buat Akun</h1>
        <p class="subtitle">Daftar untuk memesan lebih mudah dan melihat riwayat pesanan.</p>

        <form method="post" action="<?= url('register.php') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="field" style="margin-bottom:16px">
                <label for="name">Nama Lengkap <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($form['name']) ?>" required>
                <?php if (!empty($errors['name'])): ?><div class="error-text"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="field" style="margin-bottom:16px">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required>
                <?php if (!empty($errors['email'])): ?><div class="error-text"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="field" style="margin-bottom:16px">
                <label for="phone">Nomor WhatsApp</label>
                <input type="tel" id="phone" name="phone" value="<?= e($form['phone']) ?>" placeholder="0812xxxxxxx">
                <?php if (!empty($errors['phone'])): ?><div class="error-text"><?= e($errors['phone']) ?></div><?php endif; ?>
            </div>
            <div class="field" style="margin-bottom:16px">
                <label for="password">Password <span class="req">*</span></label>
                <input type="password" id="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><div class="error-text"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>
            <div class="field" style="margin-bottom:16px">
                <label for="password_confirm">Konfirmasi Password <span class="req">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <?php if (!empty($errors['password_confirm'])): ?><div class="error-text"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
        </form>

        <p class="auth-alt">Sudah punya akun? <a href="<?= url('login.php') ?>">Login di sini</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
