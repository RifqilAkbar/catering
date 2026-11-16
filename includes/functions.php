<?php
declare(strict_types=1);

/* =========================================================
| Helper umum
========================================================= */

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($amount): string
{
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function excerpt(?string $text, int $length = 110): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function product_image(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return url('assets/img/placeholder.svg');
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $absolute = __DIR__ . '/../' . ltrim($path, '/');
    if (is_file($absolute)) {
        return url($path);
    }
    return url('assets/img/placeholder.svg');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/**
 * Tangani upload gambar. Mengembalikan path relatif baru atau path lama.
 */
function handle_image_upload(string $field, ?string $current = null): ?string
{
    if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'] ?? '')) {
        return $current;
    }

    $tmp = $_FILES[$field]['tmp_name'];
    $size = (int) ($_FILES[$field]['size'] ?? 0);
    if ($size <= 0 || $size > 4 * 1024 * 1024) {
        return $current;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string) finfo_file($finfo, $tmp);
            finfo_close($finfo);
        }
    }
    if ($mime === '') {
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        $mime = $map[$ext] ?? '';
    }

    if (!isset($allowed[$mime])) {
        return $current;
    }

    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (move_uploaded_file($tmp, $uploadDir . '/' . $filename)) {
        return 'uploads/' . $filename;
    }

    return $current;
}

function status_class(string $status): string
{
    return match ($status) {
        'Menunggu'   => 'badge-warning',
        'Diproses'   => 'badge-info',
        'Dikirim'    => 'badge-primary',
        'Selesai'    => 'badge-success',
        'Dibatalkan' => 'badge-danger',
        default      => 'badge-muted',
    };
}

/* =========================================================
| Flash message
========================================================= */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* =========================================================
| CSRF
========================================================= */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('Sesi keamanan tidak valid. Silakan muat ulang halaman lalu coba lagi.');
    }
}

function csrf_valid(): bool
{
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && hash_equals(csrf_token(), $token);
}

/* =========================================================
| Autentikasi pelanggan
========================================================= */

function current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    if (!empty($_SESSION['user_id'])) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_user(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? url('account.php');
        flash('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
        redirect('login.php');
    }
}

/* =========================================================
| Autentikasi admin
========================================================= */

function current_admin(): ?array
{
    static $cached = false;
    static $admin = null;

    if ($cached) {
        return $admin;
    }
    $cached = true;

    if (!empty($_SESSION['admin_id'])) {
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
    }

    return $admin;
}

function is_admin_logged_in(): bool
{
    return current_admin() !== null;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('admin/login.php');
    }
}

/* =========================================================
| Keranjang belanja (disimpan di database)
========================================================= */

function cart_identity(): array
{
    $user = current_user();
    if ($user) {
        return ['user_id' => (int) $user['id'], 'session_id' => null];
    }

    if (empty($_SESSION['cart_session'])) {
        $_SESSION['cart_session'] = bin2hex(random_bytes(16));
    }

    return ['user_id' => null, 'session_id' => $_SESSION['cart_session']];
}

function get_cart_id(bool $create = true): ?int
{
    $db = db();
    $identity = cart_identity();

    if ($identity['user_id']) {
        $stmt = $db->prepare('SELECT id FROM carts WHERE user_id = ? ORDER BY id ASC LIMIT 1');
        $stmt->execute([$identity['user_id']]);
    } else {
        $stmt = $db->prepare('SELECT id FROM carts WHERE session_id = ? ORDER BY id ASC LIMIT 1');
        $stmt->execute([$identity['session_id']]);
    }

    $cartId = $stmt->fetchColumn();
    if ($cartId) {
        return (int) $cartId;
    }
    if (!$create) {
        return null;
    }

    if ($identity['user_id']) {
        $db->prepare('INSERT INTO carts (user_id) VALUES (?)')->execute([$identity['user_id']]);
    } else {
        $db->prepare('INSERT INTO carts (session_id) VALUES (?)')->execute([$identity['session_id']]);
    }

    return (int) $db->lastInsertId();
}

function cart_items(): array
{
    $cartId = get_cart_id(false);
    if (!$cartId) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT ci.id, ci.product_id, ci.variant_id, ci.addons, ci.unit_price, ci.quantity, ci.note,
                p.name, p.image, p.slug, p.stock, p.is_available,
                pv.name AS variant_name
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         LEFT JOIN product_variants pv ON pv.id = ci.variant_id
         WHERE ci.cart_id = ?
         ORDER BY ci.id ASC'
    );
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $item['addons_list'] = json_decode((string) ($item['addons'] ?? '[]'), true) ?: [];
        $item['line_total']   = (float) $item['unit_price'] * (int) $item['quantity'];
    }
    unset($item);

    return $items;
}

function cart_subtotal(): float
{
    $total = 0.0;
    foreach (cart_items() as $item) {
        $total += (float) $item['line_total'];
    }
    return $total;
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_items() as $item) {
        $count += (int) $item['quantity'];
    }
    return $count;
}

function clear_cart(): void
{
    $cartId = get_cart_id(false);
    if ($cartId) {
        db()->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
    }
}

function addon_signature(array $addons): string
{
    $ids = array_map(static fn ($a) => (int) ($a['id'] ?? 0), $addons);
    sort($ids);
    return implode(',', $ids);
}

function add_to_cart(int $productId, ?int $variantId, int $quantity, array $addonIds = [], ?string $note = null): array
{
    $db = db();
    $quantity = max(1, $quantity);

    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return [false, 'Produk tidak ditemukan.'];
    }
    if (!(int) $product['is_available']) {
        return [false, 'Produk sedang tidak tersedia.'];
    }

    $variant = null;
    if ($variantId) {
        $stmt = $db->prepare('SELECT * FROM product_variants WHERE id = ? AND product_id = ?');
        $stmt->execute([$variantId, $productId]);
        $variant = $stmt->fetch() ?: null;
        if (!$variant) {
            return [false, 'Pilihan paket tidak valid.'];
        }
    }

    $unitPrice = $variant ? (float) $variant['price'] : (float) $product['base_price'];

    $addonSnapshot = [];
    if ($addonIds) {
        $addonIds = array_values(array_unique(array_map('intval', $addonIds)));
        $placeholders = implode(',', array_fill(0, count($addonIds), '?'));
        $stmt = $db->prepare("SELECT id, name, price FROM product_addons WHERE product_id = ? AND id IN ($placeholders)");
        $stmt->execute(array_merge([$productId], $addonIds));
        foreach ($stmt->fetchAll() as $addon) {
            $addonSnapshot[] = [
                'id'    => (int) $addon['id'],
                'name'  => $addon['name'],
                'price' => (float) $addon['price'],
            ];
            $unitPrice += (float) $addon['price'];
        }
    }

    $note = $note !== null ? trim($note) : null;
    if ($note === '') {
        $note = null;
    }
    if ($note !== null) {
        $note = mb_substr($note, 0, 255);
    }

    $cartId = get_cart_id();
    $signature = addon_signature($addonSnapshot);

    $stmt = $db->prepare('SELECT id, addons, note, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND (variant_id <=> ?)');
    $stmt->execute([$cartId, $productId, $variantId]);

    foreach ($stmt->fetchAll() as $row) {
        $rowAddons = json_decode((string) ($row['addons'] ?? '[]'), true) ?: [];
        if (addon_signature($rowAddons) === $signature && (string) ($row['note'] ?? '') === (string) ($note ?? '')) {
            $db->prepare('UPDATE cart_items SET quantity = ?, unit_price = ? WHERE id = ?')
               ->execute([(int) $row['quantity'] + $quantity, $unitPrice, $row['id']]);
            return [true, 'Jumlah pesanan di keranjang diperbarui.'];
        }
    }

    $db->prepare(
        'INSERT INTO cart_items (cart_id, product_id, variant_id, addons, unit_price, quantity, note)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $cartId,
        $productId,
        $variantId,
        json_encode($addonSnapshot, JSON_UNESCAPED_UNICODE),
        $unitPrice,
        $quantity,
        $note,
    ]);

    return [true, 'Produk berhasil ditambahkan ke keranjang.'];
}

function update_cart_quantity(int $itemId, int $quantity): void
{
    $cartId = get_cart_id(false);
    if (!$cartId) {
        return;
    }

    if ($quantity <= 0) {
        remove_cart_item($itemId);
        return;
    }

    db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?')
        ->execute([$quantity, $itemId, $cartId]);
}

function remove_cart_item(int $itemId): void
{
    $cartId = get_cart_id(false);
    if (!$cartId) {
        return;
    }

    db()->prepare('DELETE FROM cart_items WHERE id = ? AND cart_id = ?')
        ->execute([$itemId, $cartId]);
}

/**
 * Gabungkan keranjang guest (session) ke keranjang user setelah login.
 */
function merge_guest_cart_into_user(int $userId): void
{
    if (empty($_SESSION['cart_session'])) {
        return;
    }

    $db = db();
    $stmt = $db->prepare('SELECT id FROM carts WHERE session_id = ? ORDER BY id ASC LIMIT 1');
    $stmt->execute([$_SESSION['cart_session']]);
    $guestCartId = $stmt->fetchColumn();

    if (!$guestCartId) {
        return;
    }

    $stmt = $db->prepare('SELECT id FROM carts WHERE user_id = ? ORDER BY id ASC LIMIT 1');
    $stmt->execute([$userId]);
    $userCartId = $stmt->fetchColumn();

    if (!$userCartId) {
        $db->prepare('UPDATE carts SET user_id = ?, session_id = NULL WHERE id = ?')
           ->execute([$userId, $guestCartId]);
    } else {
        $db->prepare('UPDATE cart_items SET cart_id = ? WHERE cart_id = ?')
           ->execute([$userCartId, $guestCartId]);
        $db->prepare('DELETE FROM carts WHERE id = ?')->execute([$guestCartId]);
    }
}

/* =========================================================
| Pesanan
========================================================= */

function generate_order_number(): string
{
    return 'DC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function get_order_by_number(string $orderNumber): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ?');
    $stmt->execute([$orderNumber]);
    return $stmt->fetch() ?: null;
}

function order_items(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    foreach ($items as &$item) {
        $item['addons_list'] = json_decode((string) ($item['addons'] ?? '[]'), true) ?: [];
    }
    unset($item);
    return $items;
}

/* =========================================================
| Katalog
========================================================= */

function get_categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY id ASC')->fetchAll();
}

function get_category_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Ambil produk beserta kategori dan rentang harga varian.
 */
function get_products(?string $categorySlug = null, ?string $search = null, bool $featuredOnly = false, ?int $limit = null): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (SELECT MIN(price) FROM product_variants v WHERE v.product_id = p.id) AS min_price,
                   (SELECT MAX(price) FROM product_variants v WHERE v.product_id = p.id) AS max_price
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_available = 1';
    $params = [];

    if ($categorySlug !== null && $categorySlug !== '') {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }
    if ($search !== null && $search !== '') {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    if ($featuredOnly) {
        $sql .= ' AND p.is_featured = 1';
    }

    $sql .= ' ORDER BY p.is_featured DESC, p.id ASC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_product_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p JOIN categories c ON c.id = p.category_id
         WHERE p.slug = ?'
    );
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function get_product_variants(int $productId): array
{
    $stmt = db()->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function get_product_addons(int $productId): array
{
    $stmt = db()->prepare('SELECT * FROM product_addons WHERE product_id = ? ORDER BY id ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function product_price_label(array $product): string
{
    $min = $product['min_price'] ?? $product['base_price'] ?? null;
    $max = $product['max_price'] ?? null;

    if ($min === null) {
        return rupiah($product['base_price'] ?? 0);
    }
    if ($max !== null && (float) $max > (float) $min) {
        return rupiah($min) . ' - ' . rupiah($max);
    }
    return rupiah($min);
}
