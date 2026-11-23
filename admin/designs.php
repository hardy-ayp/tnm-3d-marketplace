<?php
/**
 * TNM Marketplace — Admin Preset Designs Management for Customizer
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Preset Desain Studio';
$db = getDB();
$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    if (!verifyMarketplaceCsrfToken($_GET['csrf_token'] ?? '')) {
        $error = 'Validasi token keamanan CSRF gagal saat menghapus preset desain.';
    } else {
        $delId = intval($_GET['delete']);
        $stmtDel = $db->prepare("DELETE FROM preset_designs WHERE id = ?");
        $stmtDel->execute([$delId]);
        header('Location: designs.php?msg=deleted');
        exit;
    }
}

// Handle Status Toggle
if (isset($_GET['toggle'])) {
    if (!verifyMarketplaceCsrfToken($_GET['csrf_token'] ?? '')) {
        $error = 'Validasi token keamanan CSRF gagal saat mengubah status preset.';
    } else {
        $toggleId = intval($_GET['toggle']);
        $stmtT = $db->prepare("UPDATE preset_designs SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmtT->execute([$toggleId]);
        header('Location: designs.php?msg=updated');
        exit;
    }
}

// Handle Add Preset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_preset'])) {
    if (!verifyMarketplaceCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi keamanan formulir (CSRF) kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'logo');
        $imagePath= trim($_POST['image_path'] ?? '');

        // Handle Upload (strictly safe raster images to prevent SVG XSS)
        if (!empty($_FILES['preset_file']['name']) && $_FILES['preset_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['preset_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'webp'];
            if (in_array($ext, $allowed)) {
                $destName = 'preset_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
                $destPath = MARKETPLACE_ROOT . '/assets/img/presets/' . $destName;
                if (move_uploaded_file($_FILES['preset_file']['tmp_name'], $destPath)) {
                    $imagePath = 'assets/img/presets/' . $destName;
                }
            }
        }

        if (empty($name) || empty($imagePath)) {
            $error = 'Nama preset dan file/path gambar wajib disediakan.';
        } else {
            $stmtIns = $db->prepare("INSERT INTO preset_designs (name, category, image_path, is_active) VALUES (?, ?, ?, 1)");
            $stmtIns->execute([$name, $category, $imagePath]);
            header('Location: designs.php?msg=created');
            exit;
        }
    }
}

// Fetch all presets
$stmtP = $db->query("SELECT * FROM preset_designs ORDER BY id DESC");
$presets = $stmtP->fetchAll();

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') $message = 'Preset desain baru berhasil ditambahkan!';
    if ($_GET['msg'] === 'updated') $message = 'Status preset berhasil diperbarui!';
    if ($_GET['msg'] === 'deleted') $message = 'Preset desain berhasil dihapus!';
}

include __DIR__ . '/includes/header.php';
?>

<!-- Title Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 28px;">
  <div>
    <h1 style="font-family: var(--font-display); font-size: 28px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">
      Preset Desain Studio Kustom
    </h1>
    <p style="color: var(--muted); font-size: 14px; margin: 0;">
      Kelola daftar logo, badge, dan grafis default yang dapat ditarik (drag & drop) oleh pengguna pada baju di Studio Kustom.
    </p>
  </div>

  <a href="../customizer.php" target="_blank" class="btn sm ghost">
    <span>Buka Studio Kustom ↗</span>
  </a>
</div>

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

<!-- Two column layout: Add Form + Presets Grid -->
<div style="display:grid; grid-template-columns: 1fr; gap: 32px;" class="designs-layout-grid">
  <style>
    @media (min-width: 900px) {
      .designs-layout-grid { grid-template-columns: 340px 1fr !important; }
    }
  </style>

  <!-- Left: Add Form -->
  <div>
    <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px; position: sticky; top: 24px;">
      <h3 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 16px;">
        Tambah Preset Baru
      </h3>

      <form method="POST" action="designs.php" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap: 16px;">
        <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">
        <input type="hidden" name="add_preset" value="1">

        <div>
          <label style="display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px;">Nama Artwork / Logo *</label>
          <input type="text" name="name" required class="form-control" style="width:100%; padding:10px 12px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);" placeholder="Contoh: Emblem Seram 2026">
        </div>

        <div>
          <label style="display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px;">Kategori Artwork</label>
          <select name="category" style="width:100%; padding:10px 12px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink);">
            <option value="logo">Logo & Identitas</option>
            <option value="artwork">Artwork & Ilustrasi</option>
            <option value="badge">Badge & Stempel</option>
            <option value="typography">Tipografi & Quotes</option>
          </select>
        </div>

        <div>
          <label style="display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px;">Upload File (.svg / .png transparan)</label>
          <input type="file" name="preset_file" accept=".svg,.png,.webp,.jpg" style="font-size:12px; color:var(--muted);">
        </div>

        <div>
          <label style="display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px;">Atau Path File Internal</label>
          <input type="text" name="image_path" class="form-control" style="width:100%; padding:10px 12px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink); font-size:12px;" placeholder="assets/img/presets/preset-logo-tnm.svg">
        </div>

        <button type="submit" class="btn red sm" style="width:100%; justify-content:center; margin-top:8px;">
          <span>Tambahkan Preset</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Right: Preset Cards Grid -->
  <div>
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
      <?php foreach ($presets as $pr): ?>
        <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 16px; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="aspect-ratio: 1/1; background: #000; border-radius: var(--radius-sm); border: 1px solid var(--ring); display:flex; align-items:center; justify-content:center; padding: 16px; margin-bottom: 12px;">
              <img src="../<?= e($pr['image_path']) ?>" alt="<?= e($pr['name']) ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
              <span style="font-size: 11px; text-transform:uppercase; color:var(--red); font-weight:800;"><?= e($pr['category']) ?></span>
              <span style="font-size: 10px; font-weight:700; padding:2px 6px; border-radius:4px; <?= $pr['is_active'] ? 'background:#2ECC71; color:#000;' : 'background:var(--ring); color:var(--muted);' ?>">
                <?= $pr['is_active'] ? 'AKTIF' : 'NONAKTIF' ?>
              </span>
            </div>

            <h4 style="font-size: 13.5px; font-weight:700; color:var(--ink); margin: 0 0 6px;">
              <?= e($pr['name']) ?>
            </h4>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--ring);">
            <a href="designs.php?toggle=<?= $pr['id'] ?>&csrf_token=<?= urlencode(getMarketplaceCsrfToken()) ?>" style="font-size:12px; color:var(--muted); text-decoration:none;">
              <?= $pr['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
            </a>
            <a href="designs.php?delete=<?= $pr['id'] ?>&csrf_token=<?= urlencode(getMarketplaceCsrfToken()) ?>" onclick="return confirm('Hapus preset ini?');" style="font-size:12px; color:#e74c3c; text-decoration:none;">
              Hapus
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
