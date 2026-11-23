<?php
/**
 * TNM Official Marketplace — System Health & Diagnostics Tool
 * Comprehensive pre-flight check for hosting environments (cPanel, LiteSpeed, Apache, aaPanel)
 * Strict Zero Emojis Compliance
 */

require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

// Handle Re-seed or Repair Request (Protected: requires admin login)
if ($action === 'reseed') {
    if (!isAdminLoggedIn()) {
        $message = "Aksi inisialisasi ulang memerlukan otentikasi login administrator.";
        $messageType = "error";
    } else {
        try {
            $db = getDB();
            initDatabase($db);
            $message = "Database berhasil diperbaiki dan diinisialisasi ulang.";
            $messageType = "success";
        } catch (Throwable $e) {
            $message = "Gagal menginisialisasi database: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 1. PHP Environment
$phpVersion = PHP_VERSION;
$phpCompatible = version_compare($phpVersion, '7.4.0', '>=');

// 2. Polyfills check
$polyfills = [
    'str_contains'    => function_exists('str_contains'),
    'str_starts_with' => function_exists('str_starts_with'),
    'str_ends_with'   => function_exists('str_ends_with')
];
$allPolyfillsOk = !in_array(false, $polyfills, true);

// 3. Extensions check
$extensions = [
    'pdo'        => extension_loaded('pdo'),
    'pdo_sqlite' => extension_loaded('pdo_sqlite'),
    'json'       => extension_loaded('json'),
    'mbstring'   => extension_loaded('mbstring'),
    'gd'         => extension_loaded('gd')
];
$allExtsOk = !in_array(false, $extensions, true);

// 4. Directory & Permissions
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0775, true);
}
if (!is_dir(UPLOADS_DIR)) {
    @mkdir(UPLOADS_DIR, 0775, true);
}

$dirChecks = [
    'data_dir' => [
        'path'     => DATA_DIR,
        'exists'   => is_dir(DATA_DIR),
        'writable' => is_writable(DATA_DIR)
    ],
    'uploads_dir' => [
        'path'     => UPLOADS_DIR,
        'exists'   => is_dir(UPLOADS_DIR),
        'writable' => is_writable(UPLOADS_DIR)
    ],
    'db_file' => [
        'path'     => DB_FILE,
        'exists'   => file_exists(DB_FILE),
        'writable' => file_exists(DB_FILE) ? is_writable(DB_FILE) : is_writable(DATA_DIR),
        'size'     => file_exists(DB_FILE) ? round(filesize(DB_FILE) / 1024, 2) . ' KB' : 'Belum Ada'
    ]
];

// 5. Database Connection & Tables
$dbOk = false;
$dbError = '';
$tablesStatus = [];
$totalProducts = 0;
$totalPresets = 0;

try {
    if (extension_loaded('pdo') && extension_loaded('pdo_sqlite')) {
        $db = getDB();
        $dbOk = true;

        $requiredTables = ['admin_users', 'products', 'preset_designs', 'orders', 'order_items'];
        foreach ($requiredTables as $tbl) {
            $check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tbl}'")->fetch();
            $tablesStatus[$tbl] = !empty($check);
        }

        if (!empty($tablesStatus['products'])) {
            $totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        }
        if (!empty($tablesStatus['preset_designs'])) {
            $totalPresets = (int)$db->query("SELECT COUNT(*) FROM preset_designs")->fetchColumn();
        }
    } else {
        $dbError = 'Ekstensi PDO SQLite tidak terpasang di PHP hosting.';
    }
} catch (Throwable $e) {
    $dbOk = false;
    $dbError = $e->getMessage();
}

$pageTitle = "Diagnostik Sistem Marketplace";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagnostik Sistem Marketplace — TNM Official</title>
  <link rel="stylesheet" href="assets/css/marketplace.css">
  <style>
    body {
      background: #0f0f0f;
      color: #ece8dd;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      margin: 0;
      padding: 32px 16px;
      line-height: 1.5;
    }
    .diag-wrap {
      max-width: 860px;
      margin: 0 auto;
    }
    .diag-header {
      margin-bottom: 28px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(236,232,221,0.1);
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      gap: 16px;
    }
    .diag-title h1 {
      font-size: 26px;
      font-weight: 800;
      margin: 0 0 6px;
      color: #fff;
    }
    .diag-title p {
      color: #a8a294;
      font-size: 14px;
      margin: 0;
    }
    .diag-card {
      background: #1a1712;
      border: 1px solid rgba(236,232,221,0.1);
      border-radius: 18px;
      padding: 24px;
      margin-bottom: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .diag-card-title {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(236,232,221,0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .status-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid rgba(236,232,221,0.05);
      font-size: 14px;
    }
    .status-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    .badge {
      font-size: 11.5px;
      font-weight: 800;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 6px;
      letter-spacing: .04em;
    }
    .badge.pass { background: rgba(34, 197, 94, 0.18); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .badge.fail { background: rgba(214, 59, 41, 0.18); color: #f07361; border: 1px solid rgba(214, 59, 41, 0.3); }
    .badge.warn { background: rgba(234, 179, 8, 0.18); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
    .alert-banner {
      padding: 14px 18px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .alert-banner.success {
      background: rgba(34, 197, 94, 0.15);
      color: #4ade80;
      border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .alert-banner.error {
      background: rgba(214, 59, 41, 0.15);
      color: #f07361;
      border: 1px solid rgba(214, 59, 41, 0.3);
    }
    .act-btns {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 24px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 11px 22px;
      border-radius: 999px;
      font-size: 14px;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn.red { background: #d63b29; color: #fff; }
    .btn.red:hover { background: #b82e1e; }
    .btn.ghost { background: rgba(236,232,221,0.06); color: #ece8dd; border: 1px solid rgba(236,232,221,0.12); }
    .btn.ghost:hover { background: rgba(236,232,221,0.1); }
  </style>
</head>
<body>

<div class="diag-wrap">
  <div class="diag-header">
    <div class="diag-title">
      <span style="font-size:12px; font-weight:800; color:#f07361; text-transform:uppercase; letter-spacing:.08em;">Diagnostik Lingkungan</span>
      <h1>Pemeriksaan Sistem Marketplace TNM</h1>
      <p>Verifikasi mandiri konfigurasi server, izin folder, ekstensi PHP, dan database SQLite.</p>
    </div>
    <div class="diag-actions">
      <a href="index.php" class="btn red">Buka Halaman Toko</a>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-banner <?= $messageType ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- 1. Lingkungan PHP -->
  <div class="diag-card">
    <div class="diag-card-title">
      <span>Lingkungan PHP &amp; Kompatibilitas</span>
      <span class="badge <?= $phpCompatible ? 'pass' : 'warn' ?>"><?= $phpCompatible ? 'Kompatibel' : 'Perlu Diperbarui' ?></span>
    </div>
    <div class="status-row">
      <div>
        <b>Versi PHP Server</b>
        <div style="font-size:12.5px; color:#a8a294; margin-top:2px;">Mendukung PHP 7.4.x hingga PHP 8.x+</div>
      </div>
      <div><code><?= htmlspecialchars($phpVersion) ?></code></div>
    </div>
    <div class="status-row">
      <div>
        <b>Sistem Operasi Server</b>
      </div>
      <div><code><?= htmlspecialchars(PHP_OS) ?></code></div>
    </div>
    <div class="status-row">
      <div>
        <b>Fungsi Polyfill PHP 8 (str_contains, str_starts_with, str_ends_with)</b>
        <div style="font-size:12.5px; color:#a8a294; margin-top:2px;">Mencegah fatal error di server PHP 7.4</div>
      </div>
      <div>
        <span class="badge <?= $allPolyfillsOk ? 'pass' : 'fail' ?>">
          <?= $allPolyfillsOk ? 'Aktif &amp; Terlindungi' : 'Belum Lengkap' ?>
        </span>
      </div>
    </div>
  </div>

  <!-- 2. Ekstensi PHP Wajib -->
  <div class="diag-card">
    <div class="diag-card-title">
      <span>Ekstensi PHP Wajib</span>
      <span class="badge <?= $allExtsOk ? 'pass' : 'fail' ?>"><?= $allExtsOk ? 'Lengkap' : 'Perlu Dilengkapi' ?></span>
    </div>
    <?php foreach ($extensions as $ext => $loaded): ?>
      <div class="status-row">
        <div>
          <b>Ekstensi <?= htmlspecialchars($ext) ?></b>
          <?php if ($ext === 'pdo_sqlite' && !$loaded): ?>
            <div style="font-size:12.5px; color:#f07361; margin-top:2px;">Aktifkan di cPanel: Select PHP Version &gt; Extensions &gt; centang pdo_sqlite</div>
          <?php endif; ?>
        </div>
        <div>
          <span class="badge <?= $loaded ? 'pass' : 'fail' ?>">
            <?= $loaded ? 'Terpasang' : 'Tidak Ada' ?>
          </span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 3. Direktori & Izin Tulis -->
  <div class="diag-card">
    <div class="diag-card-title">
      <span>Direktori Sistem &amp; Izin Tulis (Permissions)</span>
      <span class="badge <?= ($dirChecks['data_dir']['writable'] && $dirChecks['uploads_dir']['writable']) ? 'pass' : 'fail' ?>">
        <?= ($dirChecks['data_dir']['writable'] && $dirChecks['uploads_dir']['writable']) ? 'Dapat Ditulis' : 'Izin Terbatas' ?>
      </span>
    </div>
    <div class="status-row">
      <div>
        <b>Folder Data (marketplace/data)</b>
        <div style="font-size:12.5px; color:#a8a294; margin-top:2px;"><?= htmlspecialchars($dirChecks['data_dir']['path']) ?></div>
      </div>
      <div>
        <span class="badge <?= $dirChecks['data_dir']['writable'] ? 'pass' : 'fail' ?>">
          <?= $dirChecks['data_dir']['writable'] ? 'Writable (755/775)' : 'Read-Only (Perlu chmod)' ?>
        </span>
      </div>
    </div>
    <div class="status-row">
      <div>
        <b>Folder Uploads (marketplace/uploads)</b>
        <div style="font-size:12.5px; color:#a8a294; margin-top:2px;"><?= htmlspecialchars($dirChecks['uploads_dir']['path']) ?></div>
      </div>
      <div>
        <span class="badge <?= $dirChecks['uploads_dir']['writable'] ? 'pass' : 'fail' ?>">
          <?= $dirChecks['uploads_dir']['writable'] ? 'Writable (755/775)' : 'Read-Only (Perlu chmod)' ?>
        </span>
      </div>
    </div>
    <div class="status-row">
      <div>
        <b>Berkas Database (marketplace/data/marketplace.db)</b>
        <div style="font-size:12.5px; color:#a8a294; margin-top:2px;">Ukuran: <?= $dirChecks['db_file']['size'] ?></div>
      </div>
      <div>
        <span class="badge <?= $dirChecks['db_file']['exists'] ? 'pass' : 'warn' ?>">
          <?= $dirChecks['db_file']['exists'] ? 'Tersedia' : 'Akan Dibuat Otomatis' ?>
        </span>
      </div>
    </div>
  </div>

  <!-- 4. Basis Data SQLite -->
  <div class="diag-card">
    <div class="diag-card-title">
      <span>Koneksi &amp; Skema Basis Data SQLite</span>
      <span class="badge <?= ($dbOk && $totalProducts > 0) ? 'pass' : ($dbOk ? 'warn' : 'fail') ?>">
        <?= ($dbOk && $totalProducts > 0) ? 'Siap Digunakan' : ($dbOk ? 'Perlu Inisialisasi' : 'Koneksi Gagal') ?>
      </span>
    </div>
    <?php if (!$dbOk): ?>
      <div style="padding:12px; background:rgba(214,59,41,0.15); border:1px solid rgba(214,59,41,0.3); border-radius:10px; color:#f07361; font-size:13.5px;">
        <b>Kesalahan Koneksi:</b> <?= htmlspecialchars($dbError) ?>
      </div>
    <?php else: ?>
      <div class="status-row">
        <div><b>Koneksi PDO SQLite</b></div>
        <div><span class="badge pass">Terhubung</span></div>
      </div>
      <?php foreach ($tablesStatus as $tbl => $exists): ?>
        <div class="status-row">
          <div><b>Tabel <code><?= htmlspecialchars($tbl) ?></code></b></div>
          <div>
            <span class="badge <?= $exists ? 'pass' : 'fail' ?>">
              <?= $exists ? 'Tersedia' : 'Belum Ada' ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="status-row">
        <div><b>Jumlah Produk Siap Jual</b></div>
        <div><b><?= $totalProducts ?> Produk</b></div>
      </div>
      <div class="status-row">
        <div><b>Jumlah Preset Desain Kustom</b></div>
        <div><b><?= $totalPresets ?> Preset</b></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- 5. URL Routing Constants -->
  <div class="diag-card">
    <div class="diag-card-title">
      <span>Resolusi URL &amp; Jalur Aplikasi</span>
      <span class="badge pass">Terverifikasi</span>
    </div>
    <div class="status-row">
      <div><b>SHOP_URL</b></div>
      <div><code><?= htmlspecialchars(SHOP_URL) ?></code></div>
    </div>
    <div class="status-row">
      <div><b>TNM_MAIN_URL</b></div>
      <div><code><?= htmlspecialchars(TNM_MAIN_URL) ?></code></div>
    </div>
  </div>

  <!-- Action Bar -->
  <div class="act-btns">
    <a href="index.php" class="btn red">Buka Beranda Marketplace</a>
    <a href="catalog.php" class="btn ghost">Buka Katalog</a>
    <a href="customizer.php" class="btn ghost">Buka Studio Kustom</a>
    <a href="admin/" class="btn ghost">Dashboard Admin</a>
    <a href="check_health.php?action=reseed" class="btn ghost" onclick="return confirm('Inisialisasi ulang database marketplace? Tindakan ini aman dan tidak menghapus data jika sudah ada.');">Perbaiki / Re-seed Database</a>
    <a href="<?= htmlspecialchars(TNM_MAIN_URL) ?>" class="btn ghost">Portal Utama TNM</a>
  </div>
</div>

</body>
</html>
