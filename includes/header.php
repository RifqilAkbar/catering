<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navUser = current_user();
$navCartCount = cart_count();
$flashes = get_flashes();

$navItems = [
    'index.php' => 'Beranda',
    'menu.php'  => 'Menu Catering',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <meta name="description" content="Dapur Catering menyediakan nasi kotak, tumpeng, dan ayam geprek untuk rapat, syukuran, ulang tahun, dan pernikahan. Pesan online, antar tepat waktu.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <a class="brand" href="<?= url('index.php') ?>">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 17h16"/>
                        <path d="M5 17a7 7 0 0 1 14 0"/>
                        <path d="M12 6V3"/>
                        <path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/>
                    </svg>
                </span>
                <span>Dapur Catering<small>Rasa Rumahan, Sajian Istimewa</small></span>
            </a>

            <div class="nav-menu" data-nav-menu>
                <ul class="nav-links">
                    <li><a href="<?= url('index.php') ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="<?= url('menu.php') ?>" class="<?= in_array($currentPage, ['menu.php', 'product.php'], true) ? 'active' : '' ?>">Menu Catering</a></li>
                    <li><a href="<?= url('index.php') ?>#tentang">Tentang Kami</a></li>
                    <li><a href="<?= url('index.php') ?>#kontak">Kontak</a></li>
                </ul>

                <div class="nav-actions">
                    <a class="icon-btn" href="<?= url('cart.php') ?>" aria-label="Keranjang belanja">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="20" r="1.4"/>
                            <circle cx="18" cy="20" r="1.4"/>
                            <path d="M2.5 3h2.2l2.2 11.2a1.6 1.6 0 0 0 1.6 1.3h9.1a1.6 1.6 0 0 0 1.6-1.3l1.4-7.2H6"/>
                        </svg>
                        <span>Keranjang</span>
                        <span class="cart-badge" data-cart-count data-empty="<?= $navCartCount > 0 ? 'false' : 'true' ?>"><?= (int) $navCartCount ?></span>
                    </a>

                    <?php if ($navUser): ?>
                        <a class="icon-btn" href="<?= url('account.php') ?>" title="Akun saya">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="3.5"/>
                                <path d="M5 20a7 7 0 0 1 14 0"/>
                            </svg>
                            <span><?= e(explode(' ', $navUser['name'])[0]) ?></span>
                        </a>
                        <a class="icon-btn" href="<?= url('logout.php') ?>">Logout</a>
                    <?php else: ?>
                        <a class="icon-btn" href="<?= url('login.php') ?>">Login</a>
                    <?php endif; ?>
                </div>
            </div>

            <button type="button" class="nav-toggle" data-nav-toggle aria-label="Buka menu navigasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </nav>
    </div>
</header>

<?php if ($flashes): ?>
    <div class="flash-wrap" data-flash-wrap>
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
