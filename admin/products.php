<?php
/**
 * TNM Marketplace — Admin Product Catalog Management (CRUD)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Kelola Produk';
$db = getDB();
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$editId = intval($_GET['edit'] ?? 0);

// Handle Delete
if (isset($_GET['delete'])) {
    if (!verifyMarketplaceCsrfToken($_GET['csrf_token'] ?? '')) {
        $error = 'Validasi token keamanan CSRF gagal saat menghapus produk.';
    } else {
        $delId = intval($_GET['delete']);
        $stmtDel = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmtDel->execute([$delId]);
        header('Location: products.php?msg=deleted');
        exit;
    }
}

// Handle Form Submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyMarketplaceCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi keamanan formulir (CSRF) kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $prodId         = intval($_POST['id'] ?? 0);
        $name           = trim($_POST['name'] ?? '');
        $category       = trim($_POST['category'] ?? 'apparel');
        $price          = floatval($_POST['price'] ?? 0);
        $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
        $stock          = intval($_POST['stock'] ?? 100);
        $description    = trim($_POST['description'] ?? '');
        $badge          = trim($_POST['badge'] ?? '');
        $is_customizable= isset($_POST['is_customizable']) ? 1 : 0;
        $is_featured    = isset($_POST['is_featured']) ? 1 : 0;
        $imagePath      = trim($_POST['image_path'] ?? 'assets/img/products/custom-shirt.svg');

        // Handle File Upload if provided (strictly safe raster images to prevent SVG XSS)
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $newFileName = 'prod_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
                $dest = UPLOADS_DIR . '/' . $newFileName;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $imagePath = 'uploads/' . $newFileName;
                }
            }
        }

    if (empty($name) || $price <= 0) {
        $error = 'Nama produk dan harga valid wajib diisi.';
    } else {
        $slug = slugify($name);

        if ($prodId > 0) {
            // Check slug uniqueness excluding current
            $stmtCheck = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $stmtCheck->execute([$slug, $prodId]);
            if ($stmtCheck->fetch()) {
                $slug .= '-' . $prodId;
            }

            $stmtUpdate = $db->prepare("
                UPDATE products 
                SET name = ?, slug = ?, category = ?, price = ?, original_price = ?, stock = ?, 
                    description = ?, image = ?, is_customizable = ?, is_featured = ?, badge = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([
                $name, $slug, $category, $price, $original_price, $stock,
                $description, $imagePath, $is_customizable, $is_featured, $badge ?: null,
                $prodId
            ]);
            header('Location: products.php?msg=updated');
            exit;
        } else {
            // New Product
            $stmtCheck = $db->prepare("SELECT id FROM products WHERE slug = ?");
            $stmtCheck->execute([$slug]);
            if ($stmtCheck->fetch()) {
                $slug .= '-' . time();
            }

            $stmtInsert = $db->prepare("
                INSERT INTO products (name, slug, category, price, original_price, stock, description, image, is_customizable, is_featured, badge)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([
                $name, $slug, $category, $price, $original_price, $stock,
                $description, $imagePath, $is_customizable, $is_featured, $badge ?: null
            ]);
            header('Location: products.php?msg=created');
            exit;
        }
    }
}
}

// Fetch single product for editing
$editProduct = null;
if ($action === 'edit' && $editId > 0) {
    $stmtE = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmtE->execute([$editId]);
    $editProduct = $stmtE->fetch();
}

// Filters
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM products";
$params = [];

if ($filter === 'ready_apparel') {
    $query .= " WHERE category = 'apparel' AND is_customizable = 0";
} elseif ($filter === 'custom_apparel') {
    $query .= " WHERE is_customizable = 1";
} elseif ($filter === 'accessories') {
    $query .= " WHERE category != 'apparel'";
}
$query .= " ORDER BY id DESC";

$stmtAll = $db->prepare($query);
$stmtAll->execute($params);
$products = $stmtAll->fetchAll();

// Counts for filter pills
$countAll    = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$countReady  = $db->query("SELECT COUNT(*) FROM products WHERE category = 'apparel' AND is_customizable = 0")->fetchColumn();
$countCustom = $db->query("SELECT COUNT(*) FROM products WHERE is_customizable = 1")->fetchColumn();
$countOther  = $db->query("SELECT COUNT(*) FROM products WHERE category != 'apparel'")->fetchColumn();

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = 'Produk baru berhasil ditambahkan!';
    if ($_GET['msg'] === 'apparel_created') $message = 'Baju Jadi (Ready-Made Drop) berhasil dibuat via Studio dan langsung terbit di katalog!';
    if ($_GET['msg'] === 'updated') $message = 'Data produk berhasil diperbarui!';
    if ($_GET['msg'] === 'deleted') $message = 'Produk berhasil dihapus dari database!';
}

include __DIR__ . '/includes/header.php';
?>

<!-- Title & Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 24px;">
  <div>
    <h1 style="font-family: var(--font-display); font-size: 28px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">
      Kelola Katalog Produk
    </h1>
    <p style="color: var(--muted); font-size: 14px; margin: 0;">
      Kelola koleksi baju jadi (ready-made), produk kustom studio, aksesoris, serta stok barang toko TNM.
    </p>
  </div>

  <?php if ($action !== 'create' && $action !== 'edit'): ?>
    <div style="display:flex; gap: 10px; flex-wrap:wrap;">
      <a href="apparel-builder.php" class="btn red sm">
        <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Z" fill="currentColor"></path></svg>
        <span> Studio Buat Baju Jadi</span>
      </a>
      <a href="products.php?action=create" class="btn sm ghost">
        <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
        <span>+ Tambah Produk Umum</span>
      </a>
    </div>
  <?php else: ?>
    <a href="products.php" class="btn sm ghost">← Kembali ke Daftar Produk</a>
  <?php endif; ?>
</div>

<?php if ($action !== 'create' && $action !== 'edit'): ?>
  <!-- Filter Tabs -->
  <div style="display:flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
    <a href="products.php?filter=all" class="btn sm <?= $filter === 'all' ? 'red' : 'ghost' ?>" style="font-size: 12.5px;">
      Semua Produk (<?= $countAll ?>)
    </a>
    <a href="products.php?filter=ready_apparel" class="btn sm <?= $filter === 'ready_apparel' ? 'red' : 'ghost' ?>" style="font-size: 12.5px;">
       Baju Jadi (Ready-Made) (<?= $countReady ?>)
    </a>
    <a href="products.php?filter=custom_apparel" class="btn sm <?= $filter === 'custom_apparel' ? 'red' : 'ghost' ?>" style="font-size: 12.5px;">
       Kustom Studio (<?= $countCustom ?>)
    </a>
    <a href="products.php?filter=accessories" class="btn sm <?= $filter === 'accessories' ? 'red' : 'ghost' ?>" style="font-size: 12.5px;">
       Aksesoris & Lainnya (<?= $countOther ?>)
    </a>
  </div>
<?php endif; ?>

<?php if (!empty($message)): ?>
  <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.4); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 24px; font-size: 13.5px; color: #2ecc71;">
     <?= e($message) ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(214, 59, 41, 0.15); border: 1px solid rgba(214, 59, 41, 0.4); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 24px; font-size: 13.5px; color: #ff8b7d;">
    ️ <?= e($error) ?>
  </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
  <!-- Form Add / Edit -->
  <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 32px; max-width: 800px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--ink); margin: 0 0 20px;">
      <?= $action === 'edit' ? 'Edit Data Produk #' . $editProduct['id'] : 'Tambah Produk Baru' ?>
    </h2>

    <form method="POST" action="products.php" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap: 20px;">
      <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">
      <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">

      <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Nama Produk *</label>
          <input type="text" name="name" required class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= e($editProduct['name'] ?? '') ?>" placeholder="Contoh: Kaos Oversize TNM Radio">
        </div>

        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Kategori *</label>
          <select name="category" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);">
            <option value="apparel" <?= ($editProduct['category'] ?? '') === 'apparel' ? 'selected' : '' ?>>Pakaian (Apparel)</option>
            <option value="accessories" <?= ($editProduct['category'] ?? '') === 'accessories' ? 'selected' : '' ?>>Aksesoris (Accessories)</option>
            <option value="collectibles" <?= ($editProduct['category'] ?? '') === 'collectibles' ? 'selected' : '' ?>>Koleksi Khusus (Collectibles)</option>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Harga Jual (Rp) *</label>
          <input type="number" name="price" required class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= $editProduct['price'] ?? 135000 ?>">
        </div>

        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Harga Coret (Diskon)</label>
          <input type="number" name="original_price" class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= $editProduct['original_price'] ?? '' ?>" placeholder="Opsional (misal: 185000)">
        </div>

        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Stok Tersedia</label>
          <input type="number" name="stock" class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= $editProduct['stock'] ?? 100 ?>">
        </div>
      </div>

      <div class="form-group">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Deskripsi Produk</label>
        <textarea name="description" rows="4" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink); line-height:1.5;"><?= e($editProduct['description'] ?? '') ?></textarea>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Badge Tag</label>
          <input type="text" name="badge" class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= e($editProduct['badge'] ?? '') ?>" placeholder="Contoh: Bestseller / New Release">
        </div>

        <div class="form-group">
          <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Path Gambar Default</label>
          <input type="text" name="image_path" class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" value="<?= e($editProduct['image'] ?? 'assets/img/products/custom-shirt.svg') ?>">
        </div>
      </div>

      <div class="form-group">
        <label style="display:block; font-size:13px; font-weight:700; color:var(--ink); margin-bottom:6px;">Atau Upload File Gambar Baru (.png, .jpg, .svg)</label>
        <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.svg,.webp" style="color:var(--muted);">
      </div>

      <!-- Feature Toggles -->
      <div style="display:flex; gap: 24px; padding: 14px; background:var(--shell); border-radius:var(--radius-sm);">
        <label style="display:flex; align-items:center; gap: 8px; cursor:pointer; font-size:13.5px; font-weight:700; color:var(--ink);">
          <input type="checkbox" name="is_customizable" value="1" <?= !empty($editProduct['is_customizable']) ? 'checked' : '' ?>>
          <span>Dapat Dikustomisasi di Studio Baju</span>
        </label>

        <label style="display:flex; align-items:center; gap: 8px; cursor:pointer; font-size:13.5px; font-weight:700; color:var(--ink);">
          <input type="checkbox" name="is_featured" value="1" <?= !empty($editProduct['is_featured']) ? 'checked' : '' ?>>
          <span>Tampilkan di Sorotan Utama (Featured)</span>
        </label>
      </div>

      <div style="display:flex; gap: 12px; margin-top: 10px;">
        <button type="submit" class="btn red">
          <span><?= $action === 'edit' ? 'Simpan Perubahan' : 'Terbitkan Produk' ?></span>
        </button>
        <a href="products.php" class="btn ghost">Batal</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <!-- Product Table List -->
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nama Produk</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th>Stok</th>
          <th>Tipe Produk</th>
          <th>Badge</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="width: 50px;">
              <div style="width: 44px; height: 44px; border-radius: 6px; background: var(--shell); overflow:hidden; display:flex; align-items:center; justify-content:center;">
                <img src="../<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" style="width:100%; height:100%; object-fit:contain;">
              </div>
            </td>
            <td style="max-width: 240px;">
              <b style="color:var(--ink); font-size:13.5px; line-height:1.35; display:block; word-break:break-word;"><?= e($p['name']) ?></b>
              <span style="font-size:11px; color:var(--faint); display:block; word-break:break-all; margin-top:2px;"><?= e($p['slug']) ?></span>
            </td>
            <td>
              <span style="text-transform:capitalize; font-size:12.5px;"><?= e($p['category']) ?></span>
            </td>
            <td>
              <b style="color:var(--ink);"><?= formatRupiah($p['price']) ?></b>
              <?php if (!empty($p['original_price'])): ?>
                <span style="font-size:11px; color:var(--faint); text-decoration:line-through; display:block;">
                  <?= formatRupiah($p['original_price']) ?>
                </span>
              <?php endif; ?>
            </td>
            <td>
              <span style="color: <?= $p['stock'] < 20 ? '#e74c3c' : 'inherit' ?>; font-weight:700;">
                <?= $p['stock'] ?> pcs
              </span>
            </td>
            <td>
              <?php if ($p['category'] === 'apparel' && !$p['is_customizable']): ?>
                <span style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; font-size:11px; font-weight:800; padding:3px 8px; border-radius:4px; display:inline-block;">
                   Baju Jadi (Siap Beli)
                </span>
              <?php elseif ($p['is_customizable']): ?>
                <span style="background: rgba(214, 59, 41, 0.15); color: var(--red-ink); font-size:11px; font-weight:800; padding:3px 8px; border-radius:4px; display:inline-block;">
                   Kustom Studio
                </span>
              <?php else: ?>
                <span style="background: var(--shell); color: var(--muted); font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; display:inline-block;">
                  Merchandise
                </span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($p['badge'])): ?>
                <span style="background:var(--shell); border:1px solid var(--ring); font-size:11px; padding:2px 8px; border-radius:4px; color:var(--ink);">
                  <?= e($p['badge']) ?>
                </span>
              <?php else: ?>
                <span style="color:var(--faint);">-</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right; white-space:nowrap;">
              <a href="<?= $p['is_customizable'] ? '../customizer.php' : '../product.php?slug=' . e($p['slug']) ?>" target="_blank" class="btn sm ghost" style="padding:4px 8px; font-size:11.5px;" title="Lihat Tampilan Toko">Lihat ↗</a>
              <a href="products.php?action=edit&edit=<?= $p['id'] ?>" class="btn sm ghost" style="padding:4px 8px; font-size:11.5px;">Edit</a>
              <a href="products.php?delete=<?= $p['id'] ?>&csrf_token=<?= urlencode(getMarketplaceCsrfToken()) ?>" onclick="return confirm('Hapus produk <?= e($p['name']) ?> secara permanen?');" class="btn sm ghost" style="padding:4px 8px; font-size:11.5px; color:#e74c3c;">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
