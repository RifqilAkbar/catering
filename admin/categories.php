<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();

$uniqueSlug = static function (string $slug, ?int $ignoreId = null) use ($db): string {
    $base = $slug !== '' ? $slug : 'kategori';
    $candidate = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM categories WHERE slug = ?';
        $params = [$candidate];
        if ($ignoreId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) {
            return $candidate;
        }
        $candidate = $base . '-' . $i;
        $i++;
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'Kategori berhasil dihapus.');
        }
        redirect('admin/categories.php');
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = slugify((string) ($_POST['slug'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $imagePath = trim((string) ($_POST['image_path'] ?? ''));
        $imageCurrent = trim((string) ($_POST['image_current'] ?? ''));

        $current = $imagePath !== '' ? $imagePath : ($imageCurrent !== '' ? $imageCurrent : null);
        $image = handle_image_upload('image_file', $current);

        if ($name === '') {
            flash('danger', 'Nama kategori wajib diisi.');
            redirect('admin/categories.php' . ($id ? '?edit=' . $id : ''));
        }

        if ($slug === '') {
            $slug = slugify($name);
        }
        $slug = $uniqueSlug($slug, $id ?: null);

        if ($id > 0) {
            $db->prepare('UPDATE categories SET name = ?, slug = ?, description = ?, image = ? WHERE id = ?')
               ->execute([$name, $slug, $description ?: null, $image, $id]);
            flash('success', 'Kategori berhasil diperbarui.');
        } else {
            $db->prepare('INSERT INTO categories (name, slug, description, image) VALUES (?, ?, ?, ?)')
               ->execute([$name, $slug, $description ?: null, $image]);
            flash('success', 'Kategori baru berhasil ditambahkan.');
        }
        redirect('admin/categories.php');
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$categories = $db->query(
    'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.id ASC'
)->fetchAll();

$pageTitle = 'Kelola Kategori';
$pageSubtitle = 'Tambah, ubah, atau hapus kategori menu';
$activeMenu = 'categories';
require __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-head">
        <h2><?= $editing ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
        <?php if ($editing): ?>
            <a href="<?= url('admin/categories.php') ?>" class="btn btn-light btn-sm">Batal Edit</a>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= url('admin/categories.php') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
            <input type="hidden" name="image_current" value="<?= e($editing['image'] ?? '') ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama Kategori <span style="color:var(--orange-dark)">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($editing['name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="slug">Slug (opsional)</label>
                    <input type="text" id="slug" name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="otomatis dari nama">
                </div>
                <div class="field">
                    <label for="image_path">Path Gambar (opsional)</label>
                    <input type="text" id="image_path" name="image_path" value="<?= e($editing['image'] ?? '') ?>" placeholder="images/bg1.jpg">
                    <div class="hint">Bisa memakai gambar yang sudah ada atau unggah file di samping.</div>
                </div>
                <div class="field">
                    <label for="image_file">Upload Gambar (maks 4MB)</label>
                    <input type="file" id="image_file" name="image_file" accept="image/*">
                </div>
                <div class="field full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="2"><?= e($editing['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $editing ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Daftar Kategori</h2></div>
    <div class="panel-body flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Gambar</th><th>Nama</th><th>Slug</th><th>Produk</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (!$categories): ?>
                        <tr><td colspan="5" class="empty-row">Belum ada kategori.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><img class="table-thumb" src="<?= e(product_image($category['image'])) ?>" alt="<?= e($category['name']) ?>"></td>
                                <td><strong><?= e($category['name']) ?></strong></td>
                                <td><code><?= e($category['slug']) ?></code></td>
                                <td><?= (int) $category['product_count'] ?></td>
                                <td class="text-right">
                                    <div class="row-actions" style="justify-content:flex-end">
                                        <a href="<?= url('admin/categories.php?edit=' . (int) $category['id']) ?>" class="btn btn-light btn-sm">Edit</a>
                                        <form method="post" action="<?= url('admin/categories.php') ?>" onsubmit="return confirm('Hapus kategori ini? Semua produk di dalamnya juga akan terhapus.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
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
