<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../config/config.php';
}
$admin = $admin ?? current_admin();
$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$activeMenu = $activeMenu ?? 'dashboard';
$flashes = get_flashes();

$icon = static function (string $name): string {
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'products'  => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="m3 7.5 9 4.5 9-4.5"/><path d="M12 12v9"/>',
        'categories'=> '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
        'orders'    => '<path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/><path d="M8 12h8M8 16h5"/>',
        'customers' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M17.5 13.5a6.5 6.5 0 0 1 4 6"/>',
        'site'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18Z"/>',
        'logout'    => '<path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? '') . '</svg>';
};

$nav = [
    'dashboard'  => ['index.php', 'Dashboard', 'dashboard'],
    'products'   => ['products.php', 'Produk', 'products'],
    'categories' => ['categories.php', 'Kategori', 'categories'],
    'orders'     => ['orders.php', 'Pesanan', 'orders'],
    'customers'  => ['customers.php', 'Pelanggan', 'customers'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; Admin <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <a class="admin-brand" href="<?= url('admin/index.php') ?>">
            <span class="brand-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M5 17a7 7 0 0 1 14 0"/><path d="M12 6V3"/><path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/></svg>
            </span>
            <span>Dapur Catering<small>Admin Panel</small></span>
        </a>

        <nav class="admin-nav">
            <span class="admin-nav-label">Menu Utama</span>
            <?php foreach ($nav as $key => $item): ?>
                <a href="<?= url('admin/' . $item[0]) ?>" class="<?= $activeMenu === $key ? 'active' : '' ?>">
                    <?= $icon($item[2]) ?><span><?= e($item[1]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="<?= url('index.php') ?>" target="_blank" rel="noopener"><?= $icon('site') ?><span>Lihat Situs</span></a>
            <a href="<?= url('admin/logout.php') ?>"><?= $icon('logout') ?><span>Logout</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-toggle" data-admin-toggle aria-label="Menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div>
                <h1><?= e($pageTitle) ?></h1>
                <?php if ($pageSubtitle): ?><div class="sub"><?= e($pageSubtitle) ?></div><?php endif; ?>
            </div>
            <div class="admin-user">
                <span class="avatar"><?= e(strtoupper(mb_substr($admin['name'] ?? 'A', 0, 1))) ?></span>
                <span class="name"><?= e($admin['name'] ?? 'Admin') ?></span>
            </div>
        </header>

        <main class="admin-content">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
