<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $db->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        flash('success', 'Produk berhasil dihapus.');
        redirect('admin/products.php');
    }

    if ($action === 'toggle' && $id > 0) {
        $db->prepare('UPDATE products SET is_available = IF(is_available = 1, 0, 1) WHERE id = ?')->execute([$id]);
        flash('success', 'Status ketersediaan produk diperbarui.');
        redirect('admin/products.php');
    }

    if ($action === 'feature' && $id > 0) {
        $db->prepare('UPDATE products SET is_featured = IF(is_featured = 1, 0, 1) WHERE id = ?')->execute([$id]);
        flash('success', 'Status menu terlaris diperbarui.');
        redirect('admin/products.php');
    }
}

$categoryFilter = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$sql = 'SELECT p.*, c.name AS category_name,
               (SELECT MIN(price) FROM product_variants v WHERE v.product_id = p.id) AS min_price,
               (SELECT MAX(price) FROM product_variants v WHERE v.product_id = p.id) AS max_price,
               (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id) AS variant_count,
               (SELECT COUNT(*) FROM product_addons a WHERE a.product_id = p.id) AS addon_count
        FROM products p JOIN categories c ON c.id = p.category_id
        WHERE 1 = 1';
$params = [];

if ($categoryFilter > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryFilter;
}
if ($search !== '') {
    $sql .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY p.id DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = get_categories();

$pageTitle = 'Kelola Produk';
$pageSubtitle = 'Atur menu, harga, varian, dan ketersediaan';
$activeMenu = 'products';
require __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <form method="get" action="<?= url('admin/products.php') ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari nama produk...">
        <select name="category">
            <option value="0">Semua Kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $categoryFilter === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light btn-sm">Filter</button>
    </form>
    <a href="<?= url('admin/product-form.php') ?>" class="btn btn-primary">+ Tambah Produk</a>
</div>

<div class="panel">
    <div class="panel-head"><h2>Daftar Produk (<?= count($products) ?>)</h2></div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$products): ?>
                        <tr><td colspan="6" class="empty-row">Belum ada produk yang cocok.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;gap:12px;align-items:center">
                                        <img class="table-thumb" src="<?= e(product_image($product['image'])) ?>" alt="<?= e($product['name']) ?>">
                                        <div>
                                            <strong><?= e($product['name']) ?></strong>
                                            <?php if ((int) $product['is_featured']): ?>
                                                <span class="badge badge-warning" style="margin-left:4px">Terlaris</span>
                                            <?php endif; ?>
                                            <div class="muted" style="font-size:.76rem"><?= (int) $product['variant_count'] ?> varian &middot; <?= (int) $product['addon_count'] ?> addon</div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($product['category_name']) ?></td>
                                <td class="nowrap"><?= e(product_price_label($product)) ?></td>
                                <td><?= (int) $product['stock'] ?></td>
                                <td>
                                    <?php if ((int) $product['is_available']): ?>
                                        <span class="badge badge-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="row-actions" style="justify-content:flex-end">
                                        <a href="<?= url('product.php?slug=' . urlencode($product['slug'])) ?>" target="_blank" rel="noopener" class="btn btn-light btn-sm">Lihat</a>
                                        <a href="<?= url('admin/product-form.php?id=' . (int) $product['id']) ?>" class="btn btn-light btn-sm">Edit</a>
                                        <form method="post" action="<?= url('admin/products.php') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="feature">
                                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                            <button type="submit" class="btn btn-light btn-sm"><?= (int) $product['is_featured'] ? 'Batal Terlaris' : 'Jadikan Terlaris' ?></button>
                                        </form>
                                        <form method="post" action="<?= url('admin/products.php') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                            <button type="submit" class="btn btn-light btn-sm"><?= (int) $product['is_available'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                        </form>
                                        <form method="post" action="<?= url('admin/products.php') ?>" onsubmit="return confirm('Hapus produk ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
