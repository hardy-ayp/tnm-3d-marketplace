<?php
/**
 * TNM Marketplace — Admin Shared Header Layout
 * Optimized for Desktop & Fully Responsive Touch-Friendly Mobile App-Bar with Drawer
 */
require_once __DIR__ . '/../auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$admin = currentAdmin();
$hasBlog = file_exists(__DIR__ . '/../../blog_config.php');

$pageBadges = [
    'index.php'           => 'Dashboard',
    'articles.php'        => 'Catatan & Blog',
    'orders.php'          => 'Pesanan',
    'products.php'        => 'Katalog Produk',
    'apparel-builder.php' => 'Studio Baju',
    'designs.php'         => 'Preset Artwork'
];
$currentBadge = $pageBadges[$currentPage] ?? (isset($adminTitle) ? $adminTitle : 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= isset($adminTitle) ? e($adminTitle) . ' — ' : '' ?>Admin TNM Marketplace</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="../assets/css/marketplace.css">

  <!-- Security CSRF Protection -->
  <meta name="csrf-token" content="<?= e(getMarketplaceCsrfToken()) ?>">
  <script>
    window.TNM_CSRF_TOKEN = <?= json_encode(getMarketplaceCsrfToken()) ?>;
  </script>
  
  <!-- Anti-FOUC Instant Theme Restoration -->
  <script>
    (function(){
      try {
        var t = localStorage.getItem('tnm-theme') || localStorage.getItem('tnm_theme');
        if (t === 'light' || t === 'dark') {
          document.documentElement.setAttribute('data-theme', t);
        }
      } catch(e) {}
    })();
  </script>

  <style>
    /* Admin Specific Styling */
    :root {
      --admin-sidebar-w: 250px;
    }

    /* Prevent any unwanted horizontal scrollbars across all viewports */
    html, body {
      width: 100%;
      max-width: 100%;
      overflow-x: hidden;
      margin: 0;
      padding: 0;
    }

    body {
      background: var(--bg);
      color: var(--ink);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .admin-layout {
      display: flex;
      min-height: 100vh;
      width: 100%;
      max-width: 100%;
      overflow-x: hidden;
    }

    /* Desktop Sidebar */
    .admin-sidebar {
      width: var(--admin-sidebar-w);
      background: var(--surface);
      border-right: 1px solid var(--ring);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .admin-sidebar-brand {
      padding: 24px;
      border-bottom: 1px solid var(--ring);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-nav {
      padding: 16px 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
    }

    .admin-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 16px;
      border-radius: var(--radius-sm);
      color: var(--muted);
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 600;
      transition: all 0.2s;
    }

    .admin-nav-item:hover {
      background: var(--shell);
      color: var(--ink);
    }

    .admin-nav-item.active {
      background: rgba(214, 59, 41, 0.15);
      color: var(--red-ink);
      font-weight: 700;
    }

    .admin-nav-icon {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    .admin-user-box {
      padding: 16px 20px;
      border-top: 1px solid var(--ring);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--shell);
    }

    /* Main Workspace */
    .admin-main {
      flex: 1;
      padding: 32px 40px;
      overflow-x: hidden;
      min-width: 0;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
    }

    /* Responsive Data Tables */
    .admin-table-container {
      background: var(--surface);
      border: 1px solid var(--ring);
      border-radius: var(--radius-md);
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      margin-top: 20px;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
    }

    .admin-table {
      width: 100%;
      min-width: 780px;
      border-collapse: collapse;
      font-size: 13px;
      text-align: left;
    }

    .admin-table th {
      padding: 12px 16px;
      background: var(--shell);
      color: var(--ink);
      font-weight: 700;
      border-bottom: 1px solid var(--ring);
      white-space: nowrap;
      font-size: 12.5px;
      letter-spacing: 0.02em;
    }

    .admin-table td {
      padding: 12px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      color: var(--muted);
      vertical-align: middle;
      font-size: 13px;
      line-height: 1.4;
    }

    .admin-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
      color: var(--ink);
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .status-badge.pending { background: rgba(241, 196, 15, 0.15); color: #f1c40f; }
    .status-badge.processing { background: rgba(52, 152, 219, 0.15); color: #3498db; }
    .status-badge.shipped { background: rgba(155, 89, 182, 0.15); color: #9b59b6; }
    .status-badge.completed { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }
    .status-badge.cancelled { background: rgba(231, 76, 60, 0.15); color: #e74c3c; }

    /* =========================================================================
       Mobile Header & Drawer Architecture (Zero Horizontal Scrolling)
       ========================================================================= */
    .admin-mobile-header {
      display: none;
    }

    .admin-drawer-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 998;
      opacity: 0;
      transition: opacity 0.25s ease;
    }

    .admin-drawer-backdrop.active {
      display: block;
      opacity: 1;
    }

    .admin-mobile-drawer {
      position: fixed;
      top: 0;
      right: -320px;
      width: 85%;
      max-width: 310px;
      height: 100%;
      background: var(--surface);
      border-left: 1px solid var(--ring);
      box-shadow: -10px 0 36px rgba(0, 0, 0, 0.6);
      z-index: 999;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    }

    .admin-mobile-drawer.open {
      transform: translateX(-320px);
    }

    .admin-drawer-head {
      padding: 16px 20px;
      border-bottom: 1px solid var(--ring);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      background: var(--shell);
    }

    .admin-drawer-user {
      padding: 16px 20px;
      border-bottom: 1px solid var(--ring);
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(214, 59, 41, 0.05);
    }

    .admin-drawer-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--red);
      color: #fff;
      display: grid;
      place-items: center;
      font-weight: 800;
      font-size: 15px;
      flex-shrink: 0;
    }

    .admin-drawer-nav {
      padding: 14px 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
    }

    .admin-drawer-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      color: var(--muted);
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
      transition: all 0.2s;
    }

    .admin-drawer-nav a:hover,
    .admin-drawer-nav a.active {
      background: var(--shell);
      color: var(--ink);
    }

    .admin-drawer-nav a.active {
      background: rgba(214, 59, 41, 0.15);
      color: var(--red-ink);
    }

    .admin-drawer-divider {
      font-size: 10.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--faint);
      padding: 14px 16px 6px;
    }

    .admin-drawer-foot {
      padding: 16px 20px;
      border-top: 1px solid var(--ring);
      background: var(--shell);
    }

    /* Mobile Breakpoint (< 860px) */
    @media (max-width: 860px) {
      .admin-layout {
        flex-direction: column;
      }

      /* Hide static sidebar on mobile */
      .admin-sidebar {
        display: none !important;
      }

      /* Show sleek sticky App-Bar */
      .admin-mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        position: sticky;
        top: 0;
        z-index: 120;
        height: 60px;
        padding: 0 16px;
        background: var(--surface);
        border-bottom: 1px solid var(--ring);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
      }

      .admin-mobile-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
      }

      .admin-mobile-page-badge {
        font-size: 11px;
        font-weight: 800;
        color: var(--red-ink);
        background: rgba(214, 59, 41, 0.12);
        border: 1px solid rgba(214, 59, 41, 0.25);
        padding: 3px 8px;
        border-radius: 999px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
      }

      .admin-mobile-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
      }

      .admin-mobile-toggle-btn {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--ring);
        background: var(--shell);
        color: var(--ink);
        display: grid;
        place-items: center;
        cursor: pointer;
        padding: 0;
        transition: background 0.2s, transform 0.15s;
      }

      .admin-mobile-toggle-btn:active {
        transform: scale(0.94);
      }

      .admin-main {
        padding: 20px 14px 40px !important;
      }

      .admin-table-container {
        margin-top: 14px;
        border-radius: var(--radius-sm);
      }
    }

    /* Small Phone Screens (< 540px) */
    @media (max-width: 540px) {
      .admin-main {
        padding: 16px 12px 40px !important;
      }

      .admin-mobile-header {
        padding: 0 12px;
      }

      .admin-mobile-page-badge {
        max-width: 110px;
        font-size: 10.5px;
      }

      /* Auto-collapse multi-column form grids on narrow screens */
      [style*="grid-template-columns: 2fr 1fr"],
      [style*="grid-template-columns: 1fr 1fr 1fr"],
      [style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
      }
    }
  </style>
</head>
<body>

<!-- Mobile Header Bar (Sticky on Mobile, Hidden on Desktop) -->
<header class="admin-mobile-header">
  <div class="admin-mobile-left">
    <a href="index.php" class="mark" style="text-decoration:none; font-size:20px;">
      TNM<i></i>
      <span class="mark-sub">ADMIN</span>
    </a>
    <span class="admin-mobile-page-badge"><?= e($currentBadge) ?></span>
  </div>

  <div class="admin-mobile-right">
    <a href="../catalog.php" target="_blank" class="btn sm ghost" style="padding: 6px 10px; font-size:11.5px; font-weight:700;" title="Lihat Toko Depan">
      <span>Toko ↗</span>
    </a>
    <button type="button" class="admin-mobile-toggle-btn" id="btnAdminMobNav" aria-label="Buka Menu Navigasi Admin" aria-expanded="false">
      <svg viewBox="0 0 256 256" fill="currentColor" style="width:20px;height:20px;">
        <path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128ZM40,72H216a8,8,0,0,0,0-16H40a8,8,0,0,0,0,16ZM216,184H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Z"></path>
      </svg>
    </button>
  </div>
</header>

<!-- Mobile Navigation Drawer Backdrop & Panel -->
<div class="admin-drawer-backdrop" id="adminDrawerBackdrop"></div>

<aside class="admin-mobile-drawer" id="adminMobileDrawer" role="dialog" aria-modal="true" aria-label="Menu Admin TNM">
  <div class="admin-drawer-head">
    <a href="index.php" class="mark" style="text-decoration:none; font-size:20px;">
      TNM<i></i>
      <span class="mark-sub">ADMIN</span>
    </a>
    <button type="button" class="admin-mobile-toggle-btn" id="btnAdminCloseDrawer" style="width:34px; height:34px;" aria-label="Tutup Menu">
      
    </button>
  </div>

  <div class="admin-drawer-user">
    <div class="admin-drawer-avatar">
      <?= strtoupper(substr($admin['name'] ?? $admin['username'] ?? 'A', 0, 1)) ?>
    </div>
    <div style="min-width:0; flex:1;">
      <span style="font-size:13.5px; font-weight:800; color:var(--ink); display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
        <?= e($admin['name'] ?? 'Admin') ?>
      </span>
      <span style="font-size:11.5px; color:var(--muted); display:block;">
        @<?= e($admin['username'] ?? 'admin') ?> • Pengelola Toko
      </span>
    </div>
  </div>

  <nav class="admin-drawer-nav">
    <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,115.55V208a16,16,0,0,1-16,16H168a16,16,0,0,1-16-16V168a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v40a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V115.55a16,16,0,0,1,5.17-11.78l72-65.45a16,16,0,0,1,21.66,0l72,65.45A16,16,0,0,1,224,115.55Z"></path></svg>
      <span>Dashboard Overview</span>
    </a>

    <?php if ($hasBlog): ?>
    <a href="articles.php" class="<?= $currentPage === 'articles.php' ? 'active' : '' ?>">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"></path></svg>
      <span>Catatan & Blog</span>
    </a>
    <?php endif; ?>

    <a href="orders.php" class="<?= $currentPage === 'orders.php' ? 'active' : '' ?>">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,88H32a8,8,0,0,0-8,8V208a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V96A8,8,0,0,0,224,88ZM128,44a36,36,0,0,1,36,36H92A36,36,0,0,1,128,44Z"></path></svg>
      <span>Kelola Pesanan</span>
    </a>

    <a href="products.php" class="<?= $currentPage === 'products.php' ? 'active' : '' ?>">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></svg>
      <span>Kelola Produk</span>
    </a>

    <a href="apparel-builder.php" class="<?= $currentPage === 'apparel-builder.php' ? 'active' : '' ?>" style="display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; align-items:center; gap:12px;">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Z"></path></svg>
        <span>Studio Baju Jadi</span>
      </div>
      <span style="font-size:9.5px; font-weight:800; background:var(--red); color:#fff; padding:2px 7px; border-radius:999px;">STUDIO</span>
    </a>

    <a href="designs.php" class="<?= $currentPage === 'designs.php' ? 'active' : '' ?>">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM192,48V160l-37.66-37.66a8,8,0,0,0-11.31,0L96,169.37,74.34,147.72a8,8,0,0,0-11.31,0L48,162.69V48ZM48,185.31l20.69-20.68L96,192l53.66-53.66L192,180.69V208H48ZM140,84a12,12,0,1,1-12-12A12,12,0,0,1,140,84Z"></path></svg>
      <span>Preset Artwork</span>
    </a>

    <div class="admin-drawer-divider">Akses & Pengaturan</div>

    <a href="../index.php" target="_blank">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,104a8,8,0,0,1-16,0V59.31l-82.34,82.35a8,8,0,0,1-11.32-11.32L196.69,48H152a8,8,0,0,1,0-16h64a8,8,0,0,1,8,8Zm-40,88a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V80a8,8,0,0,1,8-8h64a8,8,0,0,1,0-16H48A24,24,0,0,0,24,80V192a24,24,0,0,0,24,24H176a24,24,0,0,0,24-24V128a8,8,0,0,1-16,0Z"></path></svg>
      <span>Lihat Toko Depan ↗</span>
    </a>

    <?php if ($hasBlog): ?>
    <a href="../../blog.php" target="_blank">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,104a8,8,0,0,1-16,0V59.31l-82.34,82.35a8,8,0,0,1-11.32-11.32L196.69,48H152a8,8,0,0,1,0-16h64a8,8,0,0,1,8,8Zm-40,88a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V80a8,8,0,0,1,8-8h64a8,8,0,0,1,0-16H48A24,24,0,0,0,24,80V192a24,24,0,0,0,24,24H176a24,24,0,0,0,24-24V128a8,8,0,0,1-16,0Z"></path></svg>
      <span>Lihat Catatan Blog ↗</span>
    </a>
    <?php endif; ?>

    <a href="change_password.php">
      <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-80-64a16,16,0,1,1-16-16A16,16,0,0,1,128,144Z"></path></svg>
      <span>Ganti Password Admin</span>
    </a>
  </nav>

  <div class="admin-drawer-foot">
    <a href="logout.php" class="btn sm" style="width:100%; justify-content:center; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#ef4444; font-weight:700;">
      <span>Keluar dari Admin</span>
    </a>
  </div>
</aside>

<!-- Main Admin Layout Wrapper -->
<div class="admin-layout">
  <!-- Desktop Left Sidebar (Only visible on screens > 860px) -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
      <a href="index.php" class="mark" style="text-decoration:none;">
        TNM<i></i>
        <span class="mark-sub">ADMIN</span>
      </a>
    </div>

    <nav class="admin-nav">
      <a href="index.php" class="admin-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,115.55V208a16,16,0,0,1-16,16H168a16,16,0,0,1-16-16V168a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v40a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V115.55a16,16,0,0,1,5.17-11.78l72-65.45a16,16,0,0,1,21.66,0l72,65.45A16,16,0,0,1,224,115.55Z"></path></svg>
        <span>Dashboard</span>
      </a>

      <?php if ($hasBlog): ?>
      <a href="articles.php" class="admin-nav-item <?= $currentPage === 'articles.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"></path></svg>
        <span>Catatan & Blog</span>
      </a>
      <?php endif; ?>

      <a href="orders.php" class="admin-nav-item <?= $currentPage === 'orders.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,88H32a8,8,0,0,0-8,8V208a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V96A8,8,0,0,0,224,88ZM128,44a36,36,0,0,1,36,36H92A36,36,0,0,1,128,44Z"></path></svg>
        <span>Kelola Pesanan</span>
      </a>

      <a href="products.php" class="admin-nav-item <?= $currentPage === 'products.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></svg>
        <span>Kelola Produk</span>
      </a>

      <a href="apparel-builder.php" class="admin-nav-item <?= $currentPage === 'apparel-builder.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Z"></path></svg>
        <span>Studio Baju Jadi</span>
        <span style="font-size: 9.5px; font-weight:800; background: var(--red); color: #fff; padding: 2px 7px; border-radius: 999px; margin-left: auto;">STUDIO</span>
      </a>

      <a href="designs.php" class="admin-nav-item <?= $currentPage === 'designs.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM192,48V160l-37.66-37.66a8,8,0,0,0-11.31,0L96,169.37,74.34,147.72a8,8,0,0,0-11.31,0L48,162.69V48ZM48,185.31l20.69-20.68L96,192l53.66-53.66L192,180.69V208H48ZM140,84a12,12,0,1,1-12-12A12,12,0,0,1,140,84Z"></path></svg>
        <span>Preset Artwork</span>
      </a>

      <div style="height:1px; background:var(--ring); margin: 12px 0;"></div>

      <a href="../index.php" target="_blank" class="admin-nav-item">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M224,104a8,8,0,0,1-16,0V59.31l-82.34,82.35a8,8,0,0,1-11.32-11.32L196.69,48H152a8,8,0,0,1,0-16h64a8,8,0,0,1,8,8Zm-40,88a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V80a8,8,0,0,1,8-8h64a8,8,0,0,1,0-16H48A24,24,0,0,0,24,80V192a24,24,0,0,0,24,24H176a24,24,0,0,0,24-24V128a8,8,0,0,1-16,0Z"></path></svg>
        <span>Lihat Toko Depan ↗</span>
      </a>

      <a href="change_password.php" class="admin-nav-item <?= $currentPage === 'change_password.php' ? 'active' : '' ?>">
        <svg class="admin-nav-icon" viewBox="0 0 256 256" fill="currentColor"><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-80-64a16,16,0,1,1-16-16A16,16,0,0,1,128,144Z"></path></svg>
        <span>Ganti Password</span>
      </a>
    </nav>

    <div class="admin-user-box">
      <div>
        <span style="font-size: 13px; font-weight: 700; color: var(--ink); display:block;"><?= e($admin['name'] ?? 'Admin') ?></span>
        <span style="font-size: 11px; color: var(--faint);">@<?= e($admin['username'] ?? 'admin') ?></span>
      </div>
      <a href="logout.php" title="Keluar" style="color: var(--muted); text-decoration:none; padding: 6px; border-radius: 4px; display:inline-flex;">
        <svg viewBox="0 0 256 256" fill="currentColor" style="width:16px;height:16px;"><path d="M120,216a8,8,0,0,1-8,8H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32h64a8,8,0,0,1,0,16H48V208h64A8,8,0,0,1,120,216Zm109.66-93.66-40-40a8,8,0,0,0-11.32,11.32L204.69,120H112a8,8,0,0,0,0,16h92.69l-26.35,26.34a8,8,0,0,0,11.32,11.32l40-40A8,8,0,0,0,229.66,122.34Z"></path></svg>
      </a>
    </div>
  </aside>
        <svg viewBox="0 0 256 256" fill="currentColor" style="width:16px;height:16px;"><path d="M120,216a8,8,0,0,1-8,8H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32h64a8,8,0,0,1,0,16H48V208h64A8,8,0,0,1,120,216Zm109.66-93.66-40-40a8,8,0,0,0-11.32,11.32L204.69,120H112a8,8,0,0,0,0,16h92.69l-26.35,26.34a8,8,0,0,0,11.32,11.32l40-40A8,8,0,0,0,229.66,122.34Z"></path></svg>
      </a>
    </div>
  </aside>

  <!-- Interactive Script for Mobile Drawer -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const btnToggle = document.getElementById('btnAdminMobNav');
      const btnClose = document.getElementById('btnAdminCloseDrawer');
      const drawer = document.getElementById('adminMobileDrawer');
      const backdrop = document.getElementById('adminDrawerBackdrop');

      function openDrawer() {
        if (!drawer || !backdrop) return;
        backdrop.classList.add('active');
        drawer.classList.add('open');
        if (btnToggle) btnToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      }

      function closeDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('open');
        backdrop.classList.remove('active');
        if (btnToggle) btnToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }

      if (btnToggle) btnToggle.addEventListener('click', openDrawer);
      if (btnClose) btnClose.addEventListener('click', closeDrawer);
      if (backdrop) backdrop.addEventListener('click', closeDrawer);

      // Close on Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
          closeDrawer();
        }
      });

      // Close drawer when clicking any link inside it
      if (drawer) {
        drawer.querySelectorAll('a').forEach(function(link) {
          link.addEventListener('click', function() {
            setTimeout(closeDrawer, 150);
          });
        });
      }
    });
  </script>

  <!-- Main Content Area -->
  <main class="admin-main">
