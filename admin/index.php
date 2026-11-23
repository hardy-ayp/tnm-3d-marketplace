<?php
/**
 * TNM Dashboard — Unified Admin Dashboard Overview (Marketplace & Blog)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Dashboard Overview';
$db = getDB();

$hasBlog = file_exists(__DIR__ . '/../../blog_config.php');
$totalArticles = 0;
$totalViews = 0;
$recentArticles = [];

if ($hasBlog) {
    require_once __DIR__ . '/../../blog_config.php';
    if (function_exists('getBlogDB')) {
        try {
            $blogDb = getBlogDB();
            $totalArticles = $blogDb->query("SELECT COUNT(*) FROM posts")->fetchColumn() ?: 0;
            $totalViews = $blogDb->query("SELECT SUM(views) FROM posts")->fetchColumn() ?: 0;
            $stmtRecentBlog = $blogDb->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
            $recentArticles = $stmtRecentBlog->fetchAll();
        } catch (Throwable $e) {
            $hasBlog = false;
        }
    }
}

// Marketplace Analytics
$totalRevenue = $db->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn() ?: 0;
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
$totalPresets = $db->query("SELECT COUNT(*) FROM preset_designs")->fetchColumn() ?: 0;
$customOrdersCount = $db->query("SELECT COUNT(DISTINCT order_id) FROM order_items WHERE is_custom = 1")->fetchColumn() ?: 0;

// Fetch Recent Orders (5 items)
$stmtRecent = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recentOrders = $stmtRecent->fetchAll();

// Helper untuk asset gambar di admin
function blogAssetThumb($path) {
    if (empty($path)) return '../../assets/img/blog-dibalik-layar.svg';
    if (preg_match('~^https?://~i', $path)) return $path;
    return '../../' . ltrim($path, '/');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Dashboard Top Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 28px;">
  <div>
    <h1 style="font-family: var(--font-display); font-size: 26px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">
      Dashboard Terpadu TNM
    </h1>
    <p style="color: var(--muted); font-size: 13.5px; margin: 0;">
      Pusat kendali ekosistem toko merchandise, kustom baju studio, dan penerbitan catatan artikel TNM.
    </p>
  </div>

  <div style="display:flex; gap: 10px; flex-wrap:wrap;">
    <?php if ($hasBlog): ?>
    <a href="articles.php?action=create" class="btn sm ghost">
      <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"></path></svg>
      <span>Tulis Catatan</span>
    </a>
    <?php else: ?>
    <a href="apparel-builder.php" class="btn sm ghost">
      <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Z"></path></svg>
      <span>Studio Baju</span>
    </a>
    <?php endif; ?>
    <a href="products.php?action=create" class="btn red sm">
      <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
      <span>Tambah Produk</span>
    </a>
  </div>
</div>

<?php
// Check if admin is still using default credentials
$isDefaultPassword = false;
try {
    $curStmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $curStmt->execute([$admin['id'] ?? 1]);
    $curHash = $curStmt->fetchColumn();
    if ($curHash && (password_verify('admin123', $curHash) || password_verify('tnm2026', $curHash))) {
        $isDefaultPassword = true;
    }
} catch (Throwable $e) {}
?>

<?php if ($isDefaultPassword): ?>
<div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
  <div style="display:flex; align-items:center; gap:10px;">
    <svg style="width:20px;height:20px;color:#ef4444;flex-shrink:0;" viewBox="0 0 256 256" fill="currentColor"><path d="M236.8,188.09,149.35,36.22h0a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"></path></svg>
    <span style="font-size:13.5px; color:var(--ink);"><b>Peringatan Keamanan:</b> Akun administrator masih menggunakan kata sandi default. Segera ubah kata sandi untuk keamanan toko online Anda.</span>
  </div>
  <a href="change_password.php" class="btn sm red" style="white-space:nowrap;">Ganti Password Sekarang</a>
</div>
<?php endif; ?>

<!-- KPI Cards Grid -->
<style>
  .admin-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
  }
  @media (max-width: 600px) {
    .admin-kpi-grid {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 10px !important;
      margin-bottom: 24px !important;
    }
    .admin-kpi-card {
      padding: 14px 12px !important;
    }
    .admin-kpi-card .kpi-val {
      font-size: 17px !important;
    }
    .admin-kpi-card .kpi-lbl {
      font-size: 10.5px !important;
    }
    .admin-kpi-card:last-child {
      grid-column: span 2;
    }
  }
</style>

<div class="admin-kpi-grid">
  <!-- Card 1: Omset Total -->
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Total Pendapatan</span>
      <div style="width:30px; height:30px; border-radius:50%; background:rgba(46,204,113,0.15); color:#2ECC71; display:flex; align-items:center; justify-content:center;">
        <svg class="ic" style="width:15px;height:15px;"><use href="#ph-check"></use></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: var(--ink); font-family: var(--font-display); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
      <?= formatRupiah($totalRevenue) ?>
    </div>
    <span style="font-size: 11px; color: #2ECC71; margin-top: 4px; display:block;">
      Dari pesanan terverifikasi
    </span>
  </div>

  <!-- Card 2: Total Pesanan -->
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Total Pesanan</span>
      <div style="width:30px; height:30px; border-radius:50%; background:var(--shell); color:var(--ink); display:flex; align-items:center; justify-content:center;">
        <svg class="ic" style="width:15px;height:15px;"><use href="#ph-shopping-bag"></use></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: var(--ink); font-family: var(--font-display);">
      <?= number_format($totalOrders) ?>
    </div>
    <span style="font-size: 11px; color: var(--muted); margin-top: 4px; display:block;">
      Termasuk <b><?= $customOrdersCount ?></b> kustom baju
    </span>
  </div>

  <!-- Card 3: Pesanan Pending -->
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Pesanan Pending</span>
      <div style="width:30px; height:30px; border-radius:50%; background:rgba(241,196,15,0.15); color:#F1C40F; display:flex; align-items:center; justify-content:center;">
        <svg class="ic" style="width:15px;height:15px;"><use href="#ph-sparkle"></use></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: #F1C40F; font-family: var(--font-display);">
      <?= number_format($pendingOrders) ?>
    </div>
    <span style="font-size: 11px; color: var(--faint); margin-top: 4px; display:block;">
      Menunggu konfirmasi WhatsApp
    </span>
  </div>

  <!-- Card 4: Total Produk Merchandise -->
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Katalog Produk</span>
      <div style="width:30px; height:30px; border-radius:50%; background:var(--red-soft); color:var(--red-ink); display:flex; align-items:center; justify-content:center;">
        <svg class="ic" style="width:15px;height:15px;"><use href="#ph-t-shirt"></use></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: var(--ink); font-family: var(--font-display);">
      <?= number_format($totalProducts) ?>
    </div>
    <span style="font-size: 11px; color: var(--muted); margin-top: 4px; display:block;">
      Kaos, hoodie & merchandise
    </span>
  </div>

  <!-- Card 5: Kategori / Preset atau Blog -->
  <?php if ($hasBlog): ?>
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Artikel & Catatan</span>
      <div style="width:30px; height:30px; border-radius:50%; background:rgba(52,152,219,0.15); color:#3498db; display:flex; align-items:center; justify-content:center;">
        <svg viewBox="0 0 256 256" fill="currentColor" style="width:15px;height:15px;"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"></path></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: var(--ink); font-family: var(--font-display);">
      <?= number_format($totalArticles) ?>
    </div>
    <span style="font-size: 11px; color: #3498db; margin-top: 4px; display:block;">
      <?= number_format($totalViews) ?> Total Pembaca (Views)
    </span>
  </div>
  <?php else: ?>
  <div class="admin-kpi-card" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
      <span class="kpi-lbl" style="font-size: 11.5px; font-weight: 700; color: var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Preset Artwork</span>
      <div style="width:30px; height:30px; border-radius:50%; background:rgba(52,152,219,0.15); color:#3498db; display:flex; align-items:center; justify-content:center;">
        <svg viewBox="0 0 256 256" fill="currentColor" style="width:15px;height:15px;"><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM192,48V160l-37.66-37.66a8,8,0,0,0-11.31,0L96,169.37,74.34,147.72a8,8,0,0,0-11.31,0L48,162.69V48ZM48,185.31l20.69-20.68L96,192l53.66-53.66L192,180.69V208H48ZM140,84a12,12,0,1,1-12-12A12,12,0,0,1,140,84Z"></path></svg>
      </div>
    </div>
    <div class="kpi-val" style="font-size: 22px; font-weight: 800; color: var(--ink); font-family: var(--font-display);">
      <?= number_format($totalPresets) ?>
    </div>
    <span style="font-size: 11px; color: #3498db; margin-top: 4px; display:block;">
      Preset logo & grafis studio 3D
    </span>
  </div>
  <?php endif; ?>
</div>

<!-- Section: Pesanan Terbaru -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
  <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0;">
    Pesanan Masuk Terbaru
  </h2>
  <a href="orders.php" style="font-size: 13px; color: var(--red); font-weight:700; text-decoration:none;">Semua Pesanan →</a>
</div>

<div class="admin-table-container" style="margin-bottom: 36px;">
  <?php if (empty($recentOrders)): ?>
    <div style="padding: 32px; text-align: center; color: var(--muted); font-size:13.5px;">
      Belum ada transaksi pesanan yang masuk.
    </div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>No. Invoice</th>
          <th>Nama Pelanggan</th>
          <th>WhatsApp</th>
          <th>Kota</th>
          <th>Total Tagihan</th>
          <th>Status</th>
          <th>Waktu</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $ro): ?>
          <tr>
            <td>
              <b style="color:var(--ink); font-family:monospace;"><?= e($ro['order_number']) ?></b>
            </td>
            <td>
              <span style="font-weight:600; color:var(--ink);"><?= e($ro['customer_name']) ?></span>
            </td>
            <td>
              <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $ro['customer_phone'])) ?>" target="_blank" style="color:var(--muted); text-decoration:none;">
                <?= e($ro['customer_phone']) ?>
              </a>
            </td>
            <td><?= e($ro['city'] ?: '-') ?></td>
            <td>
              <b style="color:var(--ink);"><?= formatRupiah($ro['total_price']) ?></b>
            </td>
            <td>
              <span class="status-badge <?= e($ro['status']) ?>">
                <?= ucfirst($ro['status']) ?>
              </span>
            </td>
            <td style="font-size:12px;"><?= date('d M, H:i', strtotime($ro['created_at'])) ?></td>
            <td style="text-align:right;">
              <a href="orders.php?order_id=<?= $ro['id'] ?>" class="btn sm ghost" style="padding: 4px 10px; font-size: 12px;">
                Detail & Mockup
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if ($hasBlog): ?>
<!-- Section: Catatan & Artikel Terbaru -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
  <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0;">
    Catatan & Artikel Blog Terbaru
  </h2>
  <a href="articles.php" style="font-size: 13px; color: var(--red); font-weight:700; text-decoration:none;">Kelola Semua Catatan →</a>
</div>

<div class="admin-table-container">
  <?php if (empty($recentArticles)): ?>
    <div style="padding: 32px; text-align: center; color: var(--muted); font-size:13.5px;">
      Belum ada artikel catatan yang dibuat.
    </div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:60px;">Cover</th>
          <th>Judul Catatan</th>
          <th>Kategori</th>
          <th>Status</th>
          <th>Views</th>
          <th>Tanggal</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentArticles as $art): ?>
          <tr>
            <td>
              <img src="<?= e(blogAssetThumb($art['cover_image'])) ?>" alt=""
                   style="width:50px; height:34px; object-fit:cover; border-radius:4px; border:1px solid var(--ring); background:var(--shell); display:block;">
            </td>
            <td>
              <b style="color:var(--ink); font-size:13px; display:block;"><?= e($art['title']) ?></b>
            </td>
            <td>
              <span style="font-size:11.5px; font-weight:700; color:var(--ink); background:var(--shell); padding:2px 8px; border-radius:4px; border:1px solid var(--ring);">
                <?= e($art['category']) ?>
              </span>
            </td>
            <td>
              <span class="status-badge <?= $art['status'] === 'published' ? 'completed' : 'pending' ?>">
                <?= $art['status'] === 'published' ? 'Diterbitkan' : 'Draf' ?>
              </span>
            </td>
            <td>
              <span style="font-weight:700; color:var(--ink);"><?= number_format($art['views']) ?></span>
            </td>
            <td style="font-size:12px;"><?= formatBlogDate($art['created_at']) ?></td>
            <td style="text-align:right;">
              <a href="../../blog.php?slug=<?= urlencode($art['slug']) ?>" target="_blank" class="btn sm ghost" style="padding:4px 8px; font-size:12px;">
                Lihat ↗
              </a>
              <a href="articles.php?action=edit&edit=<?= $art['id'] ?>" class="btn sm ghost" style="padding:4px 10px; font-size:12px;">
                Sunting
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
