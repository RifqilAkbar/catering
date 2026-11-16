<?php
require_once __DIR__ . '/../config/config.php';

if (is_admin_logged_in()) {
    redirect('admin/index.php');
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Email dan password wajib diisi dengan benar.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = (int) $admin['id'];
            flash('success', 'Selamat datang, ' . $admin['name'] . '.');
            redirect('admin/index.php');
        }

        $error = 'Email atau password admin salah.';
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin &middot; <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-auth">
    <div class="admin-auth-card">
        <a class="admin-brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M5 17a7 7 0 0 1 14 0"/><path d="M12 6V3"/><path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/></svg>
            </span>
            <span>Dapur Catering<small>Admin Panel</small></span>
        </a>

        <h1>Login Admin</h1>
        <p class="sub">Masuk untuk mengelola produk, pesanan, dan pelanggan.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php foreach (get_flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <form method="post" action="<?= url('admin/login.php') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="email">Email Admin</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login ke Dashboard</button>
        </form>

        <div class="demo-note">
            <strong>Akun admin default:</strong><br>
            Email: admin@dapurcatering.com<br>
            Password: admin123
        </div>
    </div>
</div>
</body>
</html>
