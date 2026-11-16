<?php
require_once __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect('account.php');
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
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            merge_guest_cart_into_user((int) $user['id']);

            $redirectTo = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);

            flash('success', 'Selamat datang kembali, ' . explode(' ', $user['name'])[0] . '!');
            if ($redirectTo) {
                header('Location: ' . $redirectTo);
                exit;
            }
            redirect('account.php');
        }

        $error = 'Email atau password salah.';
    }
}

$pageTitle = 'Login';
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
        <h1>Login Pelanggan</h1>
        <p class="subtitle">Masuk untuk melanjutkan pemesanan dan melihat riwayat.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="box-shadow:none;margin-bottom:18px"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('login.php') ?>" novalidate>
            <?= csrf_field() ?>
            <div class="field" style="margin-bottom:16px">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="field" style="margin-bottom:20px">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="auth-alt">Belum punya akun? <a href="<?= url('register.php') ?>">Daftar gratis</a></p>

        <div class="demo-note">
            <strong>Akun demo pelanggan:</strong><br>
            Email: pelanggan@dapurcatering.com<br>
            Password: pelanggan123
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
