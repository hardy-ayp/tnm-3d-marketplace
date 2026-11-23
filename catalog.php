<?php
/**
 * TNM Official Marketplace — Catalog Page
 * Displays both Ready-to-Wear Apparel (Official Drops) and Custom Studio Apparel, plus Accessories & Collectibles.
 */
require_once __DIR__ . '/config.php';
$pageTitle = "Katalog Produk & Merchandise";

$db = getDB();

$currentCat = $_GET['cat'] ?? 'all';
$query = "SELECT * FROM products";
$params = [];

if ($currentCat === 'ready_apparel') {
    $query .= " WHERE category = 'apparel' AND is_customizable = 0";
} elseif ($currentCat === 'custom_apparel') {
    $query .= " WHERE is_customizable = 1";
} elseif ($currentCat === 'apparel') {
    $query .= " WHERE category = 'apparel'";
} elseif (in_array($currentCat, ['accessories', 'collectibles'])) {
    $query .= " WHERE category = ?";
    $params[] = $currentCat;
}
$query .= " ORDER BY is_customizable DESC, id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<main class="wrap" style="padding-top: 16px; padding-bottom: 64px;">
  <!-- Header Title -->
  <div style="margin-bottom: 24px;">
    <span style="font-size: 12px; font-weight: 800; color: var(--red-ink); text-transform: uppercase; letter-spacing: 0.08em;">Official Merchandise</span>
    <h1 style="font-size: clamp(28px, 3.5vw, 42px); font-weight: 800; color: var(--ink-bright); margin-top: 4px;">Katalog Produk TNM</h1>
    <p style="color: var(--muted); font-size: 15px; margin-top: 4px;">
      Pilih dari koleksi pakaian siap pakai resmi, studio kustom baju interaktif, aksesoris eksklusif, hingga merchandise kolektor.
    </p>
  </div>

  <!-- Dual Option Info Banner: Baju Jadi vs Baju Kustom -->
  <div class="catalog-banners-grid">
    <!-- Box 1: Baju & Hoodie Jadi -->
    <div class="catalog-banner-card">
      <div class="catalog-banner-icon ready">
        <svg class="ic" viewBox="0 0 256 256" style="width:22px;height:22px;"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z" fill="currentColor"></path></svg>
      </div>
      <div class="catalog-banner-info">
        <h4>Baju Jadi Resmi (Ready-to-Wear)</h4>
        <p>Desain eksklusif TNM siap kirim. Cukup pilih ukuran (S sampai 3XL) dan langsung beli.</p>
      </div>
    </div>

    <!-- Box 2: Baju Kustom Sendiri -->
    <div class="catalog-banner-card highlight">
      <div class="catalog-banner-icon custom">
        <svg class="ic" aria-hidden="true" style="width:22px;height:22px;"><use href="#ph-sparkle"></use></svg>
      </div>
      <div class="catalog-banner-info">
        <h4>Studio Kustom Baju Interaktif</h4>
        <p>Mau kombinasi warna kain atau logo sendiri? Masuk ke studio dan pasang sesukamu!</p>
      </div>
      <a href="customizer.php" class="btn red sm banner-action-btn">Buka Studio</a>
    </div>
  </div>

  <!-- Catalog Toolbar -->
  <div class="shop-toolbar">
    <!-- Category Tabs -->
    <div class="category-tabs">
      <button type="button" class="cat-pill <?= $currentCat === 'all' ? 'active' : '' ?>" data-cat="all">Semua Produk</button>
      <button type="button" class="cat-pill <?= $currentCat === 'ready_apparel' ? 'active' : '' ?>" data-cat="ready_apparel"> Baju Jadi (Ready-Made)</button>
      <button type="button" class="cat-pill <?= $currentCat === 'custom_apparel' ? 'active' : '' ?>" data-cat="custom_apparel"> Kustom Baju Sendiri</button>
      <button type="button" class="cat-pill <?= $currentCat === 'accessories' ? 'active' : '' ?>" data-cat="accessories">Aksesoris</button>
      <button type="button" class="cat-pill <?= $currentCat === 'collectibles' ? 'active' : '' ?>" data-cat="collectibles">Koleksi Khusus</button>
    </div>

    <!-- Search Input -->
    <div class="toolbar-actions">
      <div class="search-box">
        <svg class="ic search-icon" aria-hidden="true"><use href="#ph-search"></use></svg>
        <input type="text" id="catalogSearchInput" class="search-input" placeholder="Cari kaos, hoodie, aksesoris...">
      </div>
    </div>
  </div>

  <!-- Products Grid -->
  <div class="product-grid" id="catalogProductGrid">
    <?php if (empty($products)): ?>
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--muted);">
        <p>Tidak ada produk yang ditemukan untuk kategori ini.</p>
      </div>
    <?php else: ?>
      <?php foreach ($products as $prod): ?>
        <article class="prod-card" data-category="<?= e($prod['category']) ?>" data-custom="<?= $prod['is_customizable'] ? '1' : '0' ?>" data-name="<?= e($prod['name']) ?>">
          <div class="prod-core">
            <div class="prod-thumb">
              <img src="<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
              <?php if (!empty($prod['badge'])): ?>
                <span class="prod-badge <?= $prod['is_customizable'] ? 'custom' : '' ?>">
                  <?= e($prod['badge']) ?>
                </span>
              <?php elseif ($prod['category'] === 'apparel' && !$prod['is_customizable']): ?>
                <span class="prod-badge" style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.4);">
                  Siap Kirim
                </span>
              <?php endif; ?>
            </div>

            <div class="prod-info">
              <span class="prod-category">
                <?php if ($prod['category'] === 'apparel'): ?>
                  <?= $prod['is_customizable'] ? ' Studio Kustom Baju' : ' Baju Jadi (Ready-to-Wear)' ?>
                <?php else: ?>
                  <?= e($prod['category']) ?>
                <?php endif; ?>
              </span>
              <h3 class="prod-title">
                <a href="<?= $prod['is_customizable'] ? 'customizer.php?model=' . (str_contains(strtolower($prod['name']), 'hoodie') ? 'hoodie' : 'tshirt') : 'product.php?slug=' . e($prod['slug']) ?>">
                  <?= e($prod['name']) ?>
                </a>
              </h3>
              <p class="prod-desc-snippet"><?= e($prod['description']) ?></p>
            </div>

            <div class="prod-foot">
              <div class="prod-price-box">
                <span class="prod-price"><?= formatRupiah($prod['price']) ?></span>
                <?php if (!empty($prod['original_price'])): ?>
                  <span class="prod-old-price"><?= formatRupiah($prod['original_price']) ?></span>
                <?php endif; ?>
              </div>

              <?php if ($prod['is_customizable']): ?>
                <a href="customizer.php?model=<?= str_contains(strtolower($prod['name']), 'hoodie') ? 'hoodie' : 'tshirt' ?>" class="btn red sm">
                  <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
                  <span>Kustom Baju</span>
                </a>
              <?php elseif ($prod['category'] === 'apparel'): ?>
                <a href="product.php?slug=<?= e($prod['slug']) ?>" class="btn sm" style="background: var(--shell); color: var(--ink);">
                  <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
                  <span>Pilih Ukuran</span>
                </a>
              <?php else: ?>
                <button type="button" class="btn sm btn-quick-add" data-id="<?= $prod['id'] ?>" title="Tambah ke Keranjang">
                  <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
                  <span>Beli</span>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

      <!-- Dynamic Client-Side Empty Search State -->
      <div id="catalogEmptySearch" class="catalog-empty-state" style="display:none; grid-column: 1 / -1;">
        <div class="empty-icon-wrap">
          <svg class="ic" viewBox="0 0 256 256" style="width:36px;height:36px;"><path d="M228.24,219.76l-51.38-51.38a88.1,88.1,0,1,0-8.48,8.48l51.38,51.38a6,6,0,0,0,8.48-8.48ZM36,112a76,76,0,1,1,76,76A76.08,76.08,0,0,1,36,112Z" fill="currentColor"></path></svg>
        </div>
        <h3 style="font-size:18px; font-weight:800; color:var(--ink-bright); margin:12px 0 6px;">Tidak Ada Produk yang Cocok</h3>
        <p id="catalogEmptyMsg" style="font-size:13.5px; color:var(--muted); max-width:44ch; margin:0 auto 16px;">Tidak ditemukan produk untuk kriteria filter atau kata kunci pencarian Anda.</p>
        <button type="button" class="btn sm red" id="btnResetSearch">
          <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
          <span>Reset Filter & Tampilkan Semua</span>
        </button>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
