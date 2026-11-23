<?php
/**
 * TNM Official Marketplace — Homepage
 */
require_once __DIR__ . '/config.php';
$pageTitle = "Official Store & Custom Apparel Studio";

$db = getDB();
$featuredProducts = $db->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY is_customizable DESC, id ASC")->fetchAll();
$allCategoriesCount = $db->query("SELECT category, count(*) as count FROM products GROUP BY category")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalProductCount = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<main class="wrap">
  <!-- 1. Hero Studio Banner -->
  <section class="shop-hero">
    <div class="shop-hero-card">
      <div class="shop-hero-copy">
        <span class="shop-hero-tag">
          <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
          <span>Interactive Bespoke Studio</span>
        </span>
        <h1 class="shop-hero-title">
          Kreasikan Baju Impianmu, <span>Desain Sesukamu</span>.
        </h1>
        <p class="shop-hero-desc">
          Bebas pilih model kaos atau hoodie, tentukan warna kain sesukamu, lalu tinggal <b>seret (drag & drop)</b> logo resmi TNM, grafis #CeritaSerem, atau unggah gambarmu sendiri langsung ke atas baju secara interaktif!
        </p>
        <div class="shop-hero-btns">
          <a href="customizer.php" class="btn red lg">
            <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
            <span>Kustom Baju Sendiri</span>
            <svg class="ic" aria-hidden="true"><use href="#ph-arrow-right"></use></svg>
          </a>
          <a href="catalog.php?cat=ready_apparel" class="btn lg">
            <svg class="ic" viewBox="0 0 256 256" style="width:18px;height:18px;"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z" fill="currentColor"></path></svg>
            <span>Lihat Baju Jadi (Ready-Made)</span>
          </a>
        </div>
      </div>

      <div class="shop-hero-visual">
        <div class="hero-mockup-frame">
          <img src="assets/img/mockups/tshirt-front.png" alt="TNM Custom Apparel 3D Mockup" style="filter: drop-shadow(0 20px 40px rgba(0,0,0,0.6));">
        </div>
      </div>
    </div>
  </section>

  <!-- 2. Value Props Bar -->
  <section style="margin-bottom: 48px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;" class="market-value-props">
      <div style="padding: 20px 24px; border-radius: var(--radius-md); background: var(--surface); box-shadow: inset 0 0 0 1px var(--ring); display: flex; gap: 14px; align-items: center;">
        <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--red-soft); color: var(--red-ink); display: grid; place-items: center; flex-shrink: 0;">
          <svg class="ic" aria-hidden="true"><use href="#ph-t-shirt"></use></svg>
        </div>
        <div>
          <h4 style="font-size: 15px; font-weight: 800; color: var(--ink-bright);">Studio Kustom Penuh</h4>
          <p style="font-size: 13px; color: var(--muted);">Bebas ubah warna, model, dan seret logo depan & belakang.</p>
        </div>
      </div>

      <div style="padding: 20px 24px; border-radius: var(--radius-md); background: var(--surface); box-shadow: inset 0 0 0 1px var(--ring); display: flex; gap: 14px; align-items: center;">
        <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--red-soft); color: var(--red-ink); display: grid; place-items: center; flex-shrink: 0;">
          <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
        </div>
        <div>
          <h4 style="font-size: 15px; font-weight: 800; color: var(--ink-bright);">Bahan Premium Nyaman</h4>
          <p style="font-size: 13px; color: var(--muted);">Cotton Combed 24s/30s & Heavy Fleece 330 Gsm berstandar distro.</p>
        </div>
      </div>

      <div style="padding: 20px 24px; border-radius: var(--radius-md); background: var(--surface); box-shadow: inset 0 0 0 1px var(--ring); display: flex; gap: 14px; align-items: center;">
        <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--red-soft); color: var(--red-ink); display: grid; place-items: center; flex-shrink: 0;">
          <svg class="ic" aria-hidden="true"><use href="#ph-check"></use></svg>
        </div>
        <div>
          <h4 style="font-size: 15px; font-weight: 800; color: var(--ink-bright);">Merchandise Lengkap</h4>
          <p style="font-size: 13px; color: var(--muted);">Bukan hanya baju! Tersedia tumbler, topi, tote bag & korek api.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. Featured Products Showcase -->
  <section style="margin-bottom: 64px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
      <div>
        <span style="font-size: 12px; font-weight: 800; color: var(--red-ink); text-transform: uppercase; letter-spacing: 0.08em;">Koleksi Resmi</span>
        <h2 style="font-size: clamp(24px, 3vw, 34px); font-weight: 800; color: var(--ink-bright); margin-top: 2px;">Produk Pilihan & Merchandise</h2>
      </div>
      <a href="catalog.php" class="btn sm">
        <span>Lihat Semua (<?= $totalProductCount ?> Barang)</span>
        <svg class="ic" aria-hidden="true"><use href="#ph-arrow-right"></use></svg>
      </a>
    </div>

    <div class="product-grid">
      <?php foreach ($featuredProducts as $prod): ?>
        <article class="prod-card" data-category="<?= e($prod['category']) ?>" data-name="<?= e($prod['name']) ?>">
          <div class="prod-core">
            <div class="prod-thumb">
              <img src="<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
              <?php if (!empty($prod['badge'])): ?>
                <span class="prod-badge <?= $prod['is_customizable'] ? 'custom' : '' ?>">
                  <?= e($prod['badge']) ?>
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
    </div>
  </section>

  <!-- 4. Banner Callout Studio Kustom Baju -->
  <section style="margin-bottom: 64px;">
    <div class="studio-callout-card">
      <div style="max-width: 60ch;">
        <span style="font-size: 11px; font-weight: 800; color: var(--red-ink); text-transform: uppercase; letter-spacing: 0.1em; background: var(--red-soft); padding: 4px 10px; border-radius: var(--radius-full); display: inline-block; margin-bottom: 12px;">
          Fitur Unggulan
        </span>
        <h3 style="font-size: clamp(24px, 3vw, 36px); font-weight: 800; color: var(--ink-bright); line-height: 1.15; margin-bottom: 12px;">
          Punya Desain Sendiri atau Mau Pakai Logo TNM?
        </h3>
        <p style="color: var(--muted); font-size: 15px; line-height: 1.6;">
          Masuk ke studio kustom interaktif kami. Kamu bisa seret logo official, ganti warna kaos, atau upload file gambar langsung dari HP/komputermu. Pratinjau langsung secara real-time sebelum order!
        </p>
      </div>

      <div>
        <a href="customizer.php" class="btn red lg">
          <svg class="ic" aria-hidden="true"><use href="#ph-sparkle"></use></svg>
          <span>Buka Studio Kustom Sekarang</span>
          <svg class="ic" aria-hidden="true"><use href="#ph-arrow-right"></use></svg>
        </a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
