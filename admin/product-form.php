<?php
require_once __DIR__ . '/includes/auth.php';

$db = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

$product = [
    'id' => 0,
    'category_id' => 0,
    'name' => '',
    'slug' => '',
    'description' => '',
    'image' => '',
    'base_price' => '',
    'stock' => 0,
    'is_available' => 1,
    'is_featured' => 0,
];
$variants = [];
$addons = [];

if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('danger', 'Produk tidak ditemukan.');
        redirect('admin/products.php');
    }
    $product = $found;

    $stmt = $db->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC');
    $stmt->execute([$id]);
    $variants = $stmt->fetchAll();

    $stmt = $db->prepare('SELECT * FROM product_addons WHERE product_id = ? ORDER BY id ASC');
    $stmt->execute([$id]);
    $addons = $stmt->fetchAll();
}

$categories = get_categories();

$uniqueSlug = static function (string $slug, ?int $ignoreId = null) use ($db): string {
    $base = $slug !== '' ? $slug : 'produk';
    $candidate = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM products WHERE slug = ?';
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

    $product['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $product['name'] = trim((string) ($_POST['name'] ?? ''));
    $product['slug'] = slugify((string) ($_POST['slug'] ?? ''));
    $product['description'] = trim((string) ($_POST['description'] ?? ''));
    $product['base_price'] = (string) (float) ($_POST['base_price'] ?? 0);
    $product['stock'] = (int) ($_POST['stock'] ?? 0);
    $product['is_available'] = !empty($_POST['is_available']) ? 1 : 0;
    $product['is_featured'] = !empty($_POST['is_featured']) ? 1 : 0;

    $imagePath = trim((string) ($_POST['image_path'] ?? ''));
    $imageCurrent = trim((string) ($_POST['image_current'] ?? ''));
    $current = $imagePath !== '' ? $imagePath : ($imageCurrent !== '' ? $imageCurrent : null);
    $product['image'] = handle_image_upload('image_file', $current);

    $variantNames = $_POST['variant_name'] ?? [];
    $variantPrices = $_POST['variant_price'] ?? [];
    $addonNames = $_POST['addon_name'] ?? [];
    $addonPrices = $_POST['addon_price'] ?? [];

    $errors = [];
    if ($product['name'] === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }
    if ($product['category_id'] <= 0) {
        $errors[] = 'Kategori harus dipilih.';
    }
    if ((float) $product['base_price'] < 0) {
        $errors[] = 'Harga dasar tidak boleh negatif.';
    }
    if ($product['stock'] < 0) {
        $errors[] = 'Stok tidak boleh negatif.';
    }

    if (!$errors) {
        if ($product['slug'] === '') {
            $product['slug'] = slugify($product['name']);
        }
        $product['slug'] = $uniqueSlug($product['slug'], $id ?: null);

        try {
            $db->beginTransaction();

            if ($id > 0) {
                $db->prepare(
                    'UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?, image = ?,
                        base_price = ?, stock = ?, is_available = ?, is_featured = ? WHERE id = ?'
                )->execute([
                    $product['category_id'],
                    $product['name'],
                    $product['slug'],
                    $product['description'] ?: null,
                    $product['image'],
                    (float) $product['base_price'],
                    $product['stock'],
                    $product['is_available'],
                    $product['is_featured'],
                    $id,
                ]);
                $productId = $id;
                $db->prepare('DELETE FROM product_variants WHERE product_id = ?')->execute([$productId]);
                $db->prepare('DELETE FROM product_addons WHERE product_id = ?')->execute([$productId]);
            } else {
                $db->prepare(
                    'INSERT INTO products (category_id, name, slug, description, image, base_price, stock, is_available, is_featured)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([
                    $product['category_id'],
                    $product['name'],
                    $product['slug'],
                    $product['description'] ?: null,
                    $product['image'],
                    (float) $product['base_price'],
                    $product['stock'],
                    $product['is_available'],
                    $product['is_featured'],
                ]);
                $productId = (int) $db->lastInsertId();
                $id = $productId;
            }

            $insertVariant = $db->prepare('INSERT INTO product_variants (product_id, name, price) VALUES (?, ?, ?)');
            foreach ($variantNames as $index => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }
                $price = (float) ($variantPrices[$index] ?? 0);
                $insertVariant->execute([$productId, $name, $price]);
            }

            $insertAddon = $db->prepare('INSERT INTO product_addons (product_id, name, price) VALUES (?, ?, ?)');
            foreach ($addonNames as $index => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }
                $price = (float) ($addonPrices[$index] ?? 0);
                $insertAddon->execute([$productId, $name, $price]);
            }

            $db->commit();
            flash('success', $id > 0 ? 'Produk berhasil disimpan.' : 'Produk baru berhasil ditambahkan.');
            redirect('admin/products.php');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = 'Gagal menyimpan produk: ' . $e->getMessage();
        }
    }

    if ($errors) {
        foreach ($errors as $error) {
            flash('danger', $error);
        }
    }

    $variants = [];
    foreach ($variantNames as $index => $name) {
        $variants[] = ['name' => (string) $name, 'price' => (string) ($variantPrices[$index] ?? '')];
    }
    $addons = [];
    foreach ($addonNames as $index => $name) {
        $addons[] = ['name' => (string) $name, 'price' => (string) ($addonPrices[$index] ?? '')];
    }
}

$pageTitle = $id > 0 ? 'Edit Produk' : 'Tambah Produk';
$pageSubtitle = 'Lengkapi informasi produk, varian, dan tambahan';
$activeMenu = 'products';
require __DIR__ . '/includes/header.php';
?>

<form method="post" action="<?= url('admin/product-form.php' . ($id > 0 ? '?id=' . $id : '')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="image_current" value="<?= e($product['image']) ?>">

    <div class="panel">
        <div class="panel-head">
            <h2>Informasi Produk</h2>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-light btn-sm">&larr; Kembali</a>
        </div>
        <div class="panel-body">
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama Produk <span style="color:var(--orange-dark)">*</span></label>
                    <input type="text" id="name" name="name" value="<?= e($product['name']) ?>" required>
                </div>
                <div class="field">
                    <label for="category_id">Kategori <span style="color:var(--orange-dark)">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">— Pilih kategori —</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="slug">Slug (opsional)</label>
                    <input type="text" id="slug" name="slug" value="<?= e($product['slug']) ?>" placeholder="otomatis dari nama">
                </div>
                <div class="field">
                    <label for="base_price">Harga Dasar (Rp)</label>
                    <input type="number" id="base_price" name="base_price" value="<?= e((string) $product['base_price']) ?>" min="0" step="500">
                    <div class="hint">Dipakai jika produk tidak memiliki varian.</div>
                </div>
                <div class="field">
                    <label for="stock">Stok</label>
                    <input type="number" id="stock" name="stock" value="<?= (int) $product['stock'] ?>" min="0">
                </div>
                <div class="field">
                    <label for="image_path">Path Gambar</label>
                    <input type="text" id="image_path" name="image_path" value="<?= e($product['image']) ?>" placeholder="images/13k.jpg">
                </div>
                <div class="field">
                    <label for="image_file">Upload Gambar Baru (maks 4MB)</label>
                    <input type="file" id="image_file" name="image_file" accept="image/*">
                </div>
                <div class="field">
                    <label>Status</label>
                    <label class="check-row" style="margin-bottom:8px"><input type="checkbox" name="is_available" value="1" <?= (int) $product['is_available'] ? 'checked' : '' ?>> Tersedia untuk dipesan</label>
                    <label class="check-row"><input type="checkbox" name="is_featured" value="1" <?= (int) $product['is_featured'] ? 'checked' : '' ?>> Tampilkan sebagai menu terlaris</label>
                </div>
                <div class="field full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"><?= e($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <?php if (!empty($product['image'])): ?>
                <div class="mt-3">
                    <span class="muted" style="font-size:.8rem">Gambar saat ini:</span><br>
                    <img src="<?= e(product_image($product['image'])) ?>" alt="Gambar produk" style="width:150px;height:110px;object-fit:cover;border-radius:10px;border:1px solid var(--line);margin-top:6px">
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" data-repeat>
        <div class="panel-head">
            <h2>Varian / Paket &amp; Ukuran</h2>
            <button type="button" class="btn btn-light btn-sm" data-repeat-add>+ Tambah Varian</button>
        </div>
        <div class="panel-body">
            <div data-repeat-list>
                <?php if ($variants): ?>
                    <?php foreach ($variants as $variant): ?>
                        <div class="repeat-row">
                            <input type="text" name="variant_name[]" value="<?= e($variant['name']) ?>" placeholder="Nama paket / ukuran">
                            <input type="number" name="variant_price[]" value="<?= e((string) $variant['price']) ?>" placeholder="Harga" min="0" step="500">
                            <button type="button" class="btn-icon" data-repeat-remove aria-label="Hapus">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <template>
                <div class="repeat-row">
                    <input type="text" name="variant_name[]" placeholder="Nama paket / ukuran">
                    <input type="number" name="variant_price[]" placeholder="Harga" min="0" step="500">
                    <button type="button" class="btn-icon" data-repeat-remove aria-label="Hapus">&times;</button>
                </div>
            </template>
            <p class="muted" style="font-size:.78rem;margin-bottom:0">Contoh: Reguler / Jumbo, Level 1-5, atau Standar / Premium.</p>
        </div>
    </div>

    <div class="panel" data-repeat>
        <div class="panel-head">
            <h2>Tambahan (Addon)</h2>
            <button type="button" class="btn btn-light btn-sm" data-repeat-add>+ Tambah Addon</button>
        </div>
        <div class="panel-body">
            <div data-repeat-list>
                <?php if ($addons): ?>
                    <?php foreach ($addons as $addon): ?>
                        <div class="repeat-row">
                            <input type="text" name="addon_name[]" value="<?= e($addon['name']) ?>" placeholder="Nama tambahan">
                            <input type="number" name="addon_price[]" value="<?= e((string) $addon['price']) ?>" placeholder="Harga" min="0" step="500">
                            <button type="button" class="btn-icon" data-repeat-remove aria-label="Hapus">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <template>
                <div class="repeat-row">
                    <input type="text" name="addon_name[]" placeholder="Nama tambahan">
                    <input type="number" name="addon_price[]" placeholder="Harga" min="0" step="500">
                    <button type="button" class="btn-icon" data-repeat-remove aria-label="Hapus">&times;</button>
                </div>
            </template>
            <p class="muted" style="font-size:.78rem;margin-bottom:0">Contoh: Extra Ayam, Tambah Nasi, Topping Keju, Es Teh.</p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $id > 0 ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
        <a href="<?= url('admin/products.php') ?>" class="btn btn-outline">Batal</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
