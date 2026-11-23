<?php
/**
 * TNM Marketplace — Shared Header
 */
require_once __DIR__ . '/../config.php';
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
$hasPortal = file_exists(__DIR__ . '/../../index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>TNM Official Marketplace</title>
  <meta name="description" content="Official Merchandise & Custom Apparel Studio TNM (Teman Ngobrol Malam). Kreasikan baju impianmu sendiri dengan studio desain interaktif atau belanja merchandise resmi.">

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

  <!-- Favicon Resmi Logo TNM -->
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">

  <!-- Security CSRF Meta Token -->
  <meta name="csrf-token" content="<?= e(getCsrfToken()) ?>">
  <script>
    window.TNM_CSRF_TOKEN = <?= json_encode(getCsrfToken()) ?>;
  </script>

  <!-- Google Fonts: Plus Jakarta Sans & Archivo Black -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/css/marketplace.css">
  <?php if (isset($extraCss)): ?>
    <link rel="stylesheet" href="<?= e($extraCss) ?>">
  <?php endif; ?>
</head>
<body>

  <!-- SVG Sprite Definitions (Crisp Phosphor & Duotone Art) -->
  <svg width="0" height="0" style="position:absolute; display:none;" aria-hidden="true">
    <symbol id="ph-shopping-bag" viewBox="0 0 256 256"><path d="M224,88H32a8,8,0,0,0-8,8V208a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V96A8,8,0,0,0,224,88Z" opacity="0.2"></path><path d="M224,80H180a52,52,0,0,0-104,0H32A16,16,0,0,0,16,96V208a24,24,0,0,0,24,24H216a24,24,0,0,0,24-24V96A16,16,0,0,0,224,80ZM128,44a36,36,0,0,1,36,36H92A36,36,0,0,1,128,44ZM224,216H32V96H76v24a8,8,0,0,0,16,0V96h72v24a8,8,0,0,0,16,0V96h44V216Z"></path></symbol>
    <symbol id="ph-sparkle" viewBox="0 0 256 256"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Zm-63.38,35.16a12,12,0,0,0-7.11,7.11L112,212.28l-16.47-44.7a12,12,0,0,0-7.11-7.11L43.72,144l44.7-16.47a12,12,0,0,0,7.11-7.11L112,75.72l16.47,44.7a12,12,0,0,0,7.11,7.11L180.28,144ZM140,40a12,12,0,0,1,12-12h12V16a12,12,0,0,1,24,0V28h12a12,12,0,0,1,0,24H188V64a12,12,0,0,1-24,0V52H152A12,12,0,0,1,140,40Z"></path></symbol>
    <symbol id="ph-arrow-right" viewBox="0 0 256 256"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"></path></symbol>
    <symbol id="ph-t-shirt" viewBox="0 0 256 256"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></symbol>
    <symbol id="ph-check" viewBox="0 0 256 256"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"></path></symbol>
    <symbol id="ph-search" viewBox="0 0 256 256"><path d="M228.24,219.76l-51.38-51.38a88.1,88.1,0,1,0-8.48,8.48l51.38,51.38a6,6,0,0,0,8.48-8.48ZM36,112a76,76,0,1,1,76,76A76.08,76.08,0,0,1,36,112Z"></path></symbol>
    <symbol id="ph-sliders" viewBox="0 0 256 256"><path d="M120,40V72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h72A8,8,0,0,1,120,40ZM216,48H136a8,8,0,0,0,0,16h80a8,8,0,0,0,0-16Z"></path></symbol>
    <symbol id="ph-user" viewBox="0 0 256 256"><path d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"></path></symbol>
    <symbol id="ph-sun" viewBox="0 0 256 256"><path d="M116,36V20a12,12,0,0,1,24,0V36a12,12,0,0,1-24,0Zm80,92a68,68,0,1,1-68-68A68.07,68.07,0,0,1,196,128Zm-24,0a44,44,0,1,0-44,44A44.05,44.05,0,0,0,172,128ZM51.51,68.49a12,12,0,1,0,17-17l-12-12a12,12,0,0,0-17,17Zm0,119-12,12a12,12,0,0,0,17,17l12-12a12,12,0,1,0-17-17ZM196,72a12,12,0,0,0,8.49-3.51l12-12a12,12,0,0,0-17-17l-12,12A12,12,0,0,0,196,72Zm8.49,115.51a12,12,0,0,0-17,17l12,12a12,12,0,0,0,17-17ZM48,128a12,12,0,0,0-12-12H20a12,12,0,0,0,0,24H36A12,12,0,0,0,48,128Zm80,80a12,12,0,0,0-12,12v16a12,12,0,0,0,24,0V220A12,12,0,0,0,128,208Zm108-92H220a12,12,0,0,0,0,24h16a12,12,0,0,0,0-24Z"></path></symbol>
    <symbol id="ph-moon" viewBox="0 0 256 256"><path d="M236.37,139.4a12,12,0,0,0-12-3A84.07,84.07,0,0,1,119.6,31.59a12,12,0,0,0-15-15A108.86,108.86,0,0,0,49.69,55.07,108,108,0,0,0,136,228a107.09,107.09,0,0,0,64.93-21.69,108.86,108.86,0,0,0,38.44-54.94A12,12,0,0,0,236.37,139.4Zm-49.88,47.74A84,84,0,0,1,68.86,69.51,84.93,84.93,0,0,1,92.27,48.29Q92,52.13,92,56A108.12,108.12,0,0,0,200,164q3.87,0,7.71-.27A84.79,84.79,0,0,1,186.49,187.14Z"></path></symbol>
    <symbol id="ph-gauge" viewBox="0 0 256 256"><path d="M224,128A96,96,0,0,0,60.2,60.2a8,8,0,0,0,11.32,11.32A80,80,0,1,1,48,128a79.52,79.52,0,0,1,8.39-35.71,8,8,0,0,0-14.39-7A96,96,0,1,0,224,128ZM128,112a16,16,0,0,0-16,16,15.74,15.74,0,0,0,3.32,9.75l-26.66,26.66a8,8,0,0,0,11.32,11.32l26.66-26.66A15.74,15.74,0,0,0,136,152a16,16,0,0,0-8-40Z"></path></symbol>
    <symbol id="ph-lock" viewBox="0 0 256 256"><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-80-64a16,16,0,1,1-16-16A16,16,0,0,1,128,144Z"></path></symbol>
    <symbol id="ph-sign-out" viewBox="0 0 256 256"><path d="M120,216a8,8,0,0,1-8,8H48a16,16,0,0,1-16-16V48A16,16,0,0,1,48,32h64a8,8,0,0,1,0,16H48V208h64A8,8,0,0,1,120,216Zm109.66-93.66-40-40a8,8,0,0,0-11.32,11.32L204.69,120H112a8,8,0,0,0,0,16h92.69l-26.35,26.34a8,8,0,0,0,11.32,11.32l40-40A8,8,0,0,0,229.66,122.34Z"></path></symbol>
  </svg>

  <!-- Floating Island Navigation -->
  <div class="navwrap">
    <nav class="nav" aria-label="Navigasi Toko">
      <!-- Logo TNM Marketplace -->
      <a href="index.php" class="mark" aria-label="TNM Marketplace">
        TNM<i></i>
        <span class="mark-sub">MARKET</span>
      </a>

      <!-- Desktop Links -->
      <div class="links">
        <?php if ($hasPortal): ?>
          <a href="../" title="Kembali ke Portal TNM">Portal Utama</a>
        <?php endif; ?>
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Shop</a>
        <a href="customizer.php" class="highlight <?= $currentPage === 'customizer.php' ? 'active' : '' ?>">
          Studio Kustom
        </a>
        <a href="catalog.php" class="<?= $currentPage === 'catalog.php' ? 'active' : '' ?>">Katalog</a>
        <a href="fitting-room.php" class="<?= $currentPage === 'fitting-room.php' ? 'active' : '' ?>" style="color:var(--red-ink); font-weight:700;">Fitting Room 3D</a>
      </div>

      <!-- Actions -->
      <div class="navr">
        <!-- Theme Toggle Button -->
        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Ganti Tema Terang/Gelap" title="Ganti Tema Terang/Gelap">
          <svg class="ic" id="themeIcon" aria-hidden="true"><use href="#ph-sun"></use></svg>
        </button>

        <!-- Cart Trigger Button -->
        <button class="cart-trigger" aria-label="Lihat Keranjang Belanja">
          <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
          <span class="cart-badge" id="cartBadgeCount" style="<?= $cartCount > 0 ? '' : 'display:none;' ?>">
            <?= $cartCount ?>
          </span>
        </button>

        <!-- CTA Studio Button -->
        <a href="customizer.php" class="btn red sm nav-cta-kustom">
          <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
          <span>Kustom Baju</span>
        </a>

        <!-- Admin Portal Dropdown / Login Button (Desktop) -->
        <?php if (isAdminLoggedIn()): 
          $adminUser = currentAdmin();
          $adminDisplayName = htmlspecialchars($adminUser['name'] ?? 'Admin TNM');
          $adminUsername = htmlspecialchars($adminUser['username'] ?? 'andri');
          $adminInitial = strtoupper(substr($adminDisplayName, 0, 1));
        ?>
          <div class="nav-user-dropdown" id="navUserDropdown">
            <button class="nav-user-btn" id="navUserBtn" type="button" aria-expanded="false" aria-haspopup="true">
              <span class="nav-user-avatar"><?= $adminInitial ?></span>
              <span class="nav-user-name"><?= $adminDisplayName ?></span>
              <svg class="chevron" viewBox="0 0 256 256" width="11" height="11" aria-hidden="true">
                <path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z" fill="currentColor"></path>
              </svg>
            </button>
            <div class="user-dropdown-menu" id="userDropdownMenu" role="menu">
              <div class="user-dropdown-head">
                <b><?= $adminDisplayName ?></b>
                <span>@<?= $adminUsername ?> &bull; Pengelola</span>
              </div>
              <div class="user-dropdown-divider"></div>
              <a href="admin/" class="user-dropdown-item" role="menuitem">
                <svg class="ic"><use href="#ph-gauge"></use></svg>
                <span>Dashboard Toko &amp; Blog</span>
              </a>
              <a href="admin/change_password.php" class="user-dropdown-item" role="menuitem">
                <svg class="ic"><use href="#ph-lock"></use></svg>
                <span>Ganti Kata Sandi</span>
              </a>
              <div class="user-dropdown-divider"></div>
              <a href="admin/logout.php" class="user-dropdown-item danger" role="menuitem">
                <svg class="ic"><use href="#ph-sign-out"></use></svg>
                <span>Keluar (Logout)</span>
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="admin/login.php" class="btn sm ghost nav-admin-btn" title="Dashboard Admin Marketplace">
            <svg class="ic" aria-hidden="true"><use href="#ph-user"></use></svg>
            <span style="display:none;">Admin</span>
          </a>
        <?php endif; ?>

        <!-- Mobile Hamburger Toggle Button -->
        <button type="button" class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Buka Menu Navigasi">
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
        </button>
      </div>
    </nav>
  </div>

  <!-- Mobile Navigation Drawer Overlay -->
  <div class="mobile-menu-backdrop" id="mobileMenuBackdrop">
    <div class="mobile-menu-drawer" id="mobileMenuDrawer">
      <div class="mobile-menu-head">
        <a href="index.php" class="mark">
          TNM<i></i>
          <span class="mark-sub">MARKET</span>
        </a>
        <button type="button" class="mobile-menu-close" id="mobileMenuClose" aria-label="Tutup Menu">&times;</button>
      </div>

      <?php if (isAdminLoggedIn()): 
        $admin = currentAdmin();
        $adminName = htmlspecialchars($admin['name'] ?? 'Admin TNM');
        $adminInit = strtoupper(substr($adminName, 0, 1));
      ?>
        <div style="margin: 0 0 16px; padding: 12px 14px; background: var(--shell); border: 1px solid var(--ring); border-radius: var(--radius-sm); display: flex; align-items: center; gap: 12px;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--red); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 14px; flex-shrink: 0;"><?= $adminInit ?></div>
          <div style="min-width: 0; flex: 1;">
            <div style="font-weight: 700; font-size: 13.5px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $adminName ?></div>
            <div style="font-size: 11px; color: var(--muted); font-weight: 600;">Pengelola Marketplace</div>
          </div>
        </div>
      <?php endif; ?>

      <div class="mobile-menu-links">
        <?php if ($hasPortal): ?>
        <a href="../" class="mobile-link">
          <span class="mobile-link-icon">
            <svg class="ic" viewBox="0 0 256 256"><path d="M224,115.55V208a16,16,0,0,1-16,16H168a16,16,0,0,1-16-16V168a8,8,0,0,0-8-8H112a8,8,0,0,0-8,8v40a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V115.55a16,16,0,0,1,5.17-11.78l72-65.45a16,16,0,0,1,21.66,0l72,65.45A16,16,0,0,1,224,115.55Z"></path></svg>
          </span>
          <span class="mobile-link-text">
            <b>Portal Utama TNM</b>
            <small>Kembali ke beranda utama channel</small>
          </span>
        </a>
        <?php endif; ?>
        <a href="index.php" class="mobile-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
          <span class="mobile-link-icon">
            <svg class="ic"><use href="#ph-shopping-bag"></use></svg>
          </span>
          <span class="mobile-link-text">
            <b>Shop Beranda</b>
            <small>Merchandise & koleksi terlaris</small>
          </span>
        </a>
        <a href="customizer.php" class="mobile-link highlight <?= $currentPage === 'customizer.php' ? 'active' : '' ?>">
          <span class="mobile-link-icon">
            <svg class="ic" style="color:var(--red-ink);"><use href="#ph-sparkle"></use></svg>
          </span>
          <span class="mobile-link-text">
            <b>Studio Kustom Baju</b>
            <small>Desain kaos & hoodie sesukamu</small>
          </span>
        </a>
        <a href="fitting-room.php" class="mobile-link <?= $currentPage === 'fitting-room.php' ? 'active' : '' ?>">
          <span class="mobile-link-icon">
            <svg class="ic" style="color:var(--red);"><use href="#ph-sparkle"></use></svg>
          </span>
          <span class="mobile-link-text">
            <b>Fitting Room 3D</b>
            <small>Coba langsung pakaian di karakter 3D</small>
          </span>
        </a>
        <a href="catalog.php" class="mobile-link <?= $currentPage === 'catalog.php' ? 'active' : '' ?>">
          <span class="mobile-link-icon">
            <svg class="ic"><use href="#ph-t-shirt"></use></svg>
          </span>
          <span class="mobile-link-text">
            <b>Katalog Lengkap</b>
            <small>Apparel, tumbler, aksesoris & stiker</small>
          </span>
        </a>
        <a href="cart.php" class="mobile-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>">
          <span class="mobile-link-icon">
            <svg class="ic"><use href="#ph-shopping-bag"></use></svg>
          </span>
          <span class="mobile-link-text">
            <b>Keranjang Belanja</b>
            <small>Lihat pesanan yang siap checkout</small>
          </span>
        </a>
      </div>

      <div class="mobile-menu-footer">
        <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--faint); font-weight:800; margin-bottom:8px;">Pilihan Tema</div>
        <div class="seg" role="group" aria-label="Pilihan Tema Tampilan" style="width:100%; justify-content:center; margin-bottom:16px;">
          <button id="m-t-light" aria-pressed="false" type="button" style="flex:1; justify-content:center;">
            <svg class="ic" style="width:14px;height:14px;"><use href="#ph-sun"></use></svg>
            <span>Terang</span>
          </button>
          <button id="m-t-dark" aria-pressed="true" type="button" style="flex:1; justify-content:center;">
            <svg class="ic" style="width:14px;height:14px;"><use href="#ph-moon"></use></svg>
            <span>Gelap</span>
          </button>
        </div>

        <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--faint); font-weight:800; margin-bottom:8px;">Ekosistem &amp; Manajemen</div>
        <div style="display:flex; flex-direction:column; gap:6px;">
          <?php if ($hasPortal && is_dir(__DIR__ . '/../../vote')): ?>
            <a href="../vote/" class="mobile-sublink">Platform Voting Cerita &nearr;</a>
          <?php endif; ?>
          <a href="https://www.youtube.com/@andriTNM" target="_blank" rel="noopener" class="mobile-sublink">YouTube Channel @andriTNM &nearr;</a>
          <?php if (isAdminLoggedIn()): ?>
            <a href="admin/" class="mobile-sublink" style="color:var(--red-ink); font-weight:700;">Dashboard Toko &amp; Blog &nearr;</a>
            <a href="admin/change_password.php" class="mobile-sublink">Ganti Kata Sandi &nearr;</a>
            <a href="admin/logout.php" class="mobile-sublink" style="color:#f87171;">Keluar (Logout) &nearr;</a>
          <?php else: ?>
            <a href="admin/login.php" class="mobile-sublink">Portal Admin Toko &nearr;</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
