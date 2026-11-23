<?php
/**
 * TNM Marketplace — Admin Ready-Made Apparel Studio Builder
 * Admin creates pre-designed T-Shirts and Hoodies using the 2.5D Studio Mockup Engine.
 * Features: Multi-tab ergonomic layout, Instant Draft System, Snapshot Generator, and 1-Click Publishing.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Studio Pembuat Baju & Hoodie Jadi';
$db = getDB();

// Ensure drafts table exists
$db->exec("
    CREATE TABLE IF NOT EXISTS apparel_drafts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        model TEXT NOT NULL DEFAULT 'tshirt',
        fabric TEXT NOT NULL DEFAULT 'combed24s',
        color TEXT NOT NULL DEFAULT '#141414',
        color_name TEXT NOT NULL DEFAULT 'Hitam Pekat',
        front_design TEXT,
        back_design TEXT,
        price REAL DEFAULT 149000,
        original_price REAL DEFAULT 199000,
        stock INTEGER DEFAULT 100,
        badge TEXT DEFAULT 'Official Drop',
        is_featured INTEGER DEFAULT 1,
        description TEXT,
        preview_image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Strict CSRF verification for all AJAX mutations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (!verifyMarketplaceCsrfToken($csrf)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Sesi keamanan CSRF kedaluwarsa. Silakan muat ulang halaman.']);
        exit;
    }
}

// Handle AJAX Save Product (Publish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_apparel') {
    header('Content-Type: application/json');

    $name          = trim($_POST['name'] ?? '');
    $price         = floatval($_POST['price'] ?? 149000);
    $originalPrice = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock         = intval($_POST['stock'] ?? 100);
    $badge         = trim($_POST['badge'] ?? 'Official Drop');
    $description   = trim($_POST['description'] ?? '');
    $isFeatured    = isset($_POST['is_featured']) && $_POST['is_featured'] == '1' ? 1 : 0;
    $imageSnapshot = trim($_POST['image_snapshot'] ?? '');

    if (empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'error' => 'Nama produk dan harga valid wajib diisi.']);
        exit;
    }

    $slug = slugify($name);
    // Ensure slug uniqueness
    $stmtCheck = $db->prepare("SELECT id FROM products WHERE slug = ?");
    $stmtCheck->execute([$slug]);
    if ($stmtCheck->fetch()) {
        $slug .= '-' . time();
    }

    $imagePath = 'assets/img/mockups/tshirt-front.png'; // fallback

    // Process Base64 PNG snapshot
    if (!empty($imageSnapshot) && str_starts_with($imageSnapshot, 'data:image/')) {
        $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $imageSnapshot);
        $decoded = base64_decode($imgData);

        if ($decoded !== false) {
            $fileName = 'prod_drop_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.png';
            $destFile = UPLOADS_DIR . '/' . $fileName;

            if (file_put_contents($destFile, $decoded)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }
    }

    // Insert as ready-to-wear product (is_customizable = 0)
    $stmtInsert = $db->prepare("
        INSERT INTO products (name, slug, category, price, original_price, stock, description, image, is_customizable, is_featured, badge)
        VALUES (?, ?, 'apparel', ?, ?, ?, ?, ?, 0, ?, ?)
    ");
    $stmtInsert->execute([
        $name, $slug, $price, $originalPrice, $stock,
        $description, $imagePath, $isFeatured, $badge ?: null
    ]);

    $newId = $db->lastInsertId();

    echo json_encode([
        'success'   => true,
        'message'   => 'Baju jadi berhasil diterbitkan ke katalog!',
        'product_id'=> $newId,
        'slug'      => $slug,
        'redirect'  => 'products.php?msg=apparel_created'
    ]);
    exit;
}

// Handle AJAX Save Draft
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_draft') {
    header('Content-Type: application/json');

    $draftId       = !empty($_POST['draft_id']) ? intval($_POST['draft_id']) : null;
    $title         = trim($_POST['title'] ?? 'Draft Desain Baju');
    $model         = trim($_POST['model'] ?? 'tshirt');
    $fabric        = trim($_POST['fabric'] ?? 'combed24s');
    $color         = trim($_POST['color'] ?? '#141414');
    $colorName     = trim($_POST['color_name'] ?? 'Hitam Pekat');
    $frontDesign   = trim($_POST['front_design'] ?? '{}');
    $backDesign    = trim($_POST['back_design'] ?? '{}');
    $price         = floatval($_POST['price'] ?? 149000);
    $originalPrice = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock         = intval($_POST['stock'] ?? 100);
    $badge         = trim($_POST['badge'] ?? 'Official Drop');
    $isFeatured    = isset($_POST['is_featured']) && $_POST['is_featured'] == '1' ? 1 : 0;
    $description   = trim($_POST['description'] ?? '');
    $imageSnapshot = trim($_POST['image_snapshot'] ?? '');

    $previewImage = null;
    if (!empty($imageSnapshot) && str_starts_with($imageSnapshot, 'data:image/')) {
        $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $imageSnapshot);
        $decoded = base64_decode($imgData);
        if ($decoded !== false) {
            $fileName = 'draft_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.png';
            $destFile = UPLOADS_DIR . '/' . $fileName;
            if (file_put_contents($destFile, $decoded)) {
                $previewImage = 'uploads/' . $fileName;
            }
        }
    }

    if ($draftId) {
        // Update existing draft
        if ($previewImage) {
            $stmt = $db->prepare("
                UPDATE apparel_drafts
                SET title=?, model=?, fabric=?, color=?, color_name=?, front_design=?, back_design=?,
                    price=?, original_price=?, stock=?, badge=?, is_featured=?, description=?,
                    preview_image=?, updated_at=CURRENT_TIMESTAMP
                WHERE id=?
            ");
            $stmt->execute([
                $title, $model, $fabric, $color, $colorName, $frontDesign, $backDesign,
                $price, $originalPrice, $stock, $badge ?: null, $isFeatured, $description,
                $previewImage, $draftId
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE apparel_drafts
                SET title=?, model=?, fabric=?, color=?, color_name=?, front_design=?, back_design=?,
                    price=?, original_price=?, stock=?, badge=?, is_featured=?, description=?,
                    updated_at=CURRENT_TIMESTAMP
                WHERE id=?
            ");
            $stmt->execute([
                $title, $model, $fabric, $color, $colorName, $frontDesign, $backDesign,
                $price, $originalPrice, $stock, $badge ?: null, $isFeatured, $description,
                $draftId
            ]);
        }
    } else {
        // Insert new draft
        $stmt = $db->prepare("
            INSERT INTO apparel_drafts (
                title, model, fabric, color, color_name, front_design, back_design,
                price, original_price, stock, badge, is_featured, description,
                preview_image, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $title, $model, $fabric, $color, $colorName, $frontDesign, $backDesign,
            $price, $originalPrice, $stock, $badge ?: null, $isFeatured, $description,
            $previewImage
        ]);
        $draftId = $db->lastInsertId();
    }

    $allDrafts = $db->query("SELECT * FROM apparel_drafts ORDER BY updated_at DESC")->fetchAll();

    echo json_encode([
        'success'   => true,
        'message'   => 'Draft berhasil disimpan!',
        'draft_id'  => $draftId,
        'drafts'    => $allDrafts
    ]);
    exit;
}

// Handle AJAX Delete Draft
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_draft') {
    header('Content-Type: application/json');
    $draftId = intval($_POST['draft_id'] ?? 0);
    if ($draftId > 0) {
        $stmt = $db->prepare("SELECT preview_image FROM apparel_drafts WHERE id = ?");
        $stmt->execute([$draftId]);
        $d = $stmt->fetch();
        if ($d && !empty($d['preview_image'])) {
            $path = UPLOADS_DIR . '/' . basename($d['preview_image']);
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $db->prepare("DELETE FROM apparel_drafts WHERE id = ?")->execute([$draftId]);
    }

    $allDrafts = $db->query("SELECT * FROM apparel_drafts ORDER BY updated_at DESC")->fetchAll();
    echo json_encode([
        'success' => true,
        'message' => 'Draft berhasil dihapus',
        'drafts'  => $allDrafts
    ]);
    exit;
}

// Handle AJAX Get Drafts
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_drafts') {
    header('Content-Type: application/json');
    $allDrafts = $db->query("SELECT * FROM apparel_drafts ORDER BY updated_at DESC")->fetchAll();
    echo json_encode(['success' => true, 'drafts' => $allDrafts]);
    exit;
}

// Fetch all available preset artworks
$stmtPresets = $db->query("SELECT * FROM preset_designs WHERE is_active = 1 ORDER BY id ASC");
$presets = $stmtPresets->fetchAll();

// Fetch initial drafts
$drafts = $db->query("SELECT * FROM apparel_drafts ORDER BY updated_at DESC")->fetchAll();
$draftCount = count($drafts);

include __DIR__ . '/includes/header.php';
?>

<!-- SVG Filter & Sprite Icons for 2.5D Studio Fabric & UI -->
<svg width="0" height="0" style="position:absolute; display:none;" aria-hidden="true">
  <defs>
    <filter id="clothTextureFilter" x="0%" y="0%" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" result="noise" />
      <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 0.12 0" in="noise" result="grain" />
      <feComposite in="SourceGraphic" in2="grain" operator="in" result="textured" />
      <feBlend in="SourceGraphic" in2="grain" mode="multiply" />
    </filter>
  </defs>
  <symbol id="ph-shopping-bag" viewBox="0 0 256 256"><path d="M224,88H32a8,8,0,0,0-8,8V208a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V96A8,8,0,0,0,224,88Z" opacity="0.2"></path><path d="M224,80H180a52,52,0,0,0-104,0H32A16,16,0,0,0,16,96V208a24,24,0,0,0,24,24H216a24,24,0,0,0,24-24V96A16,16,0,0,0,224,80ZM128,44a36,36,0,0,1,36,36H92A36,36,0,0,1,128,44ZM224,216H32V96H76v24a8,8,0,0,0,16,0V96h72v24a8,8,0,0,0,16,0V96h44V216Z"></path></symbol>
  <symbol id="ph-sparkle" viewBox="0 0 256 256"><path d="M199,125.31l-49.88-18.39L130.69,57a19.92,19.92,0,0,0-37.38,0L74.92,106.92,25,125.31a19.92,19.92,0,0,0,0,37.38l49.88,18.39L93.31,231a19.92,19.92,0,0,0,37.38,0l18.39-49.88L199,162.69a19.92,19.92,0,0,0,0-37.38Zm-63.38,35.16a12,12,0,0,0-7.11,7.11L112,212.28l-16.47-44.7a12,12,0,0,0-7.11-7.11L43.72,144l44.7-16.47a12,12,0,0,0,7.11-7.11L112,75.72l16.47,44.7a12,12,0,0,0,7.11,7.11L180.28,144ZM140,40a12,12,0,0,1,12-12h12V16a12,12,0,0,1,24,0V28h12a12,12,0,0,1,0,24H188V64a12,12,0,0,1-24,0V52H152A12,12,0,0,1,140,40Z"></path></symbol>
  <symbol id="ph-t-shirt" viewBox="0 0 256 256"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></symbol>
  <symbol id="ph-eye" viewBox="0 0 256 256"><path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.5c.35.79,8.82,19.57,27.65,38.4C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.35c18.83-18.83,27.3-37.61,27.65-38.4A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-59.4-11.89-81.82-34.14C37.07,148.5,28.7,136.63,24.88,128c3.82-8.63,12.19-20.5,21.3-29.86C68.6,75.89,97.22,64,128,64s59.4,11.89,81.82,34.14C218.93,107.5,227.3,119.37,231.12,128c-3.82,8.63-12.19,20.5-21.3,29.86C187.4,180.11,158.78,192,128,192Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path></symbol>
</svg>

<!-- Load Shared Customizer Stylesheet -->
<link rel="stylesheet" href="../assets/css/customizer.css">

<style>
  /* Admin Studio Custom Adjustments */
  .apparel-admin-studio {
    padding: 0 0 60px;
  }
  .apparel-admin-studio .studio-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 28px;
    align-items: start;
  }
  @media (max-width: 1100px) {
    .apparel-admin-studio .studio-grid {
      grid-template-columns: 1fr;
    }
  }

  .admin-details-form-box {
    background: var(--surface);
    border: 1px solid var(--ring);
    border-radius: var(--radius-md);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .admin-details-form-box .form-control {
    background: var(--shell);
    border: 1px solid var(--ring);
    border-radius: var(--radius-sm);
    color: var(--ink);
    padding: 10px 14px;
    font-size: 13.5px;
    font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .admin-details-form-box .form-control:focus {
    border-color: var(--red);
    box-shadow: 0 0 0 2px rgba(214, 59, 41, 0.2);
    outline: none;
  }
  .studio-tabs {
    flex-wrap: wrap;
  }
  .studio-tab-btn {
    white-space: nowrap;
  }
  @media (max-width: 600px) {
    .admin-details-form-box [style*="grid-template-columns: 1fr 1fr 1fr"],
    .admin-details-form-box [style*="grid-template-columns: 1fr 1fr"] {
      grid-template-columns: 1fr !important;
      gap: 10px !important;
    }
    .placement-side-toggle {
      flex-direction: column;
    }
  }
</style>

<!-- Title & Back Nav -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 22px;">
  <div>
    <div style="display:flex; align-items:center; gap: 8px; margin-bottom: 4px;">
      <a href="products.php" style="color:var(--muted); font-size:13px; text-decoration:none;">← Katalog Produk</a>
      <span style="color:var(--faint); font-size:12px;">/</span>
      <span style="color:var(--red-ink); font-size:12px; font-weight:800; text-transform:uppercase;">Creator Studio</span>
    </div>
    <h1 style="font-family: var(--font-display); font-size: 26px; font-weight: 800; color: var(--ink); margin: 0 0 4px;">
      Studio Desain Baju & Hoodie Jadi (Ready-Made)
    </h1>
    <p style="color: var(--muted); font-size: 13.5px; margin: 0;">
      Rancang pakaian siap jual dengan studio fotorealistik 2.5D. Simpan rancangan ke draft atau langsung terbitkan ke katalog marketplace!
    </p>
  </div>

  <div style="display:flex; align-items:center; gap: 10px; flex-wrap:wrap;">
    <button type="button" class="btn sm" onclick="document.querySelector('.studio-tab-btn[data-tab=\'drafts\']')?.click()" style="background:var(--shell); border:1px solid var(--ring); color:var(--ink); font-weight:700;">
      <span> Buka Draft</span>
      <span id="topDraftCount" style="background:var(--red); color:#fff; font-size:11px; padding:1px 6px; border-radius:999px; margin-left:4px;"><?= $draftCount ?></span>
    </button>
    <a href="../catalog.php" target="_blank" class="btn sm ghost">Lihat Toko Depan ↗</a>
    <a href="products.php" class="btn sm ghost">Kembali ke Daftar</a>
  </div>
</div>

<!-- Main Customizer & Admin Publishing Container -->
<div class="customizer-layout apparel-admin-studio">
  <div class="studio-grid">

    <!-- Left Column: Realistic Mockup Stage (Identik Marketplace) -->
    <div class="stage-container" id="mockupStage">
      <!-- Top View Controls -->
      <div class="stage-top-controls">
        <div class="side-toggle" id="viewSideToggle">
          <button type="button" class="side-btn active" data-side="front" id="btnViewFront">
            <svg class="ic" viewBox="0 0 256 256" style="width:15px;height:15px;"><use href="#ph-eye"></use></svg>
            <span>Tampak Depan</span>
          </button>
          <button type="button" class="side-btn" data-side="back" id="btnViewBack">
            <svg class="ic" viewBox="0 0 256 256" style="width:15px;height:15px;"><use href="#ph-eye"></use></svg>
            <span>Tampak Belakang</span>
          </button>
        </div>

        <button type="button" class="guideline-toggle-btn active" id="btnToggleGuidelines" title="Tampilkan / Sembunyikan Frame Handle Desain">
          <span>Frame Desain</span>
        </button>
      </div>

      <!-- Garment Realistic Viewport -->
      <div class="mockup-viewport" id="mockupViewport">
        <div class="realistic-stage" id="realisticStage">
          <!-- Layer 1: Base Garment Silhouette & Dynamic Color -->
          <div class="garment-color-layer" id="garmentColorLayer"></div>

          <!-- Layer 2: Photographic Fabric Shading & Natural Folds -->
          <img class="garment-base-layer" id="garmentBaseImg" src="../assets/img/mockups/tshirt-front.png" alt="Shading Pakaian">

          <!-- Safe Printable Zone Guideline -->
          <div class="garment-safe-zone" id="garmentSafeZone">
            <span class="safe-zone-badge" id="safeZoneBadge">Batas Cetak Model</span>
          </div>

          <!-- Layer 3: Clamped & Masked Graphic Layer -->
          <div class="garment-masked-graphic-layer" id="garmentMaskedGraphicLayer">
            <div class="placed-graphic-item" id="placedGraphicItem" style="display:none;">
              <img id="activePlacedImg" class="placed-graphic-img" src="" alt="Desain Sablon">
            </div>
          </div>

          <!-- Layer 3b: Interactive Transform Frame Overlay -->
          <div class="transform-overlay-layer" id="transformOverlayLayer">
            <div class="interactive-transform-frame" id="interactiveTransformFrame" style="display:none;">
              <div class="rot-handle-stem"></div>
              <div class="handle handle-rot" data-action="rotate" title="Putar Desain">↻</div>
              <div class="handle handle-tl" data-action="resize-tl"></div>
              <div class="handle handle-tr" data-action="resize-tr"></div>
              <div class="handle handle-br" data-action="resize-br"></div>
              <div class="handle handle-bl" data-action="resize-bl"></div>
            </div>
          </div>

          <!-- Layer 4: Top Crease Blend -->
          <img class="garment-crease-overlay" id="garmentCreaseImg" src="../assets/img/mockups/tshirt-front.png" alt="Lipatan Kain">
        </div>
      </div>

      <!-- Quick Placement Presets Toolbar -->
      <div class="placement-presets-toolbar" id="placementPresetsToolbar">
        <span class="preset-pos-label">Posisi Cepat:</span>
        <button type="button" class="btn-pos-preset" data-pos="left-chest">Dada Kiri</button>
        <button type="button" class="btn-pos-preset active" data-pos="center-chest">Dada Tengah</button>
        <button type="button" class="btn-pos-preset" data-pos="full-front">Dada Penuh</button>
        <button type="button" class="btn-pos-preset" data-pos="upper-back">Punggung Atas</button>
        <button type="button" class="btn-pos-preset" data-pos="full-back">Punggung Penuh</button>
      </div>

      <!-- Quick Interaction Tip -->
      <div class="stage-hint-bar">
        <span>️ Geser logo langsung dengan mouse/jari • Tarik titik sudut untuk ubah ukuran • Desain menyatu mengikuti lekukan kain</span>
      </div>

      <!-- Color Swatches Bar -->
      <div class="stage-color-bar">
        <span class="color-bar-label">Pilihan Warna Kain:</span>
        <div class="swatches-row">
          <button type="button" class="color-swatch active" data-color="#141414" data-name="Hitam Pekat" style="background:#141414;" title="Hitam Pekat"></button>
          <button type="button" class="color-swatch" data-color="#F4F0E6" data-name="Putih Tulang" style="background:#F4F0E6;" title="Putih Tulang"></button>
          <button type="button" class="color-swatch" data-color="#D63B29" data-name="Merah TNM" style="background:#D63B29;" title="Merah TNM"></button>
          <button type="button" class="color-swatch" data-color="#2E2E2E" data-name="Misty Charcoal" style="background:#2E2E2E;" title="Misty Charcoal"></button>
          <button type="button" class="color-swatch" data-color="#182236" data-name="Deep Navy" style="background:#182236;" title="Deep Navy"></button>
          <button type="button" class="color-swatch" data-color="#C2B39B" data-name="Khaki Sand" style="background:#C2B39B;" title="Khaki Sand"></button>
          <button type="button" class="color-swatch" data-color="#1D3123" data-name="Forest Green" style="background:#1D3123;" title="Forest Green"></button>

          <label title="Warna Kustom" style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:var(--shell); border:1px solid var(--ring); cursor:pointer; position:relative;">
            <input type="color" id="bCustomColorPicker" value="#141414" style="opacity:0; width:0; height:0; position:absolute;">
            <span style="font-size:12px;"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Right Column: Refactored Compact Multi-Tab Layout with Drafts -->
    <div class="controls-container" id="controlsContainer">

      <!-- Target Placement Side Selector (Dada Depan vs Punggung Belakang) -->
      <div class="placement-side-card">
        <div class="placement-side-head">
          <span class="opt-label" style="margin:0;">Target Sisi Penempatan Desain:</span>
          <span class="active-side-badge" id="activeSideBadge">Tampak Depan</span>
        </div>
        <div class="placement-side-toggle">
          <button type="button" class="side-switch-btn active" data-target-side="front" id="switchSideFront">
            <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></svg>
            <span>Dada (Depan)</span>
          </button>
          <button type="button" class="side-switch-btn" data-target-side="back" id="switchSideBack">
            <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></svg>
            <span>Punggung (Belakang)</span>
          </button>
        </div>
      </div>

      <!-- Layer Active Adjuster Box (Ukuran, Posisi, Rotasi, Reset) -->
      <div class="layer-control-panel active" id="layerControlPanel">
        <div class="layer-control-head">
          <span style="font-weight:700;font-size:13px;color:var(--ink-bright);" id="activeLayerTitle">Pengaturan Desain Aktif</span>
          <button type="button" class="btn-clear-layer" id="btnDeleteActiveLayer" title="Hapus desain pada sisi ini"> Hapus Desain</button>
        </div>
        <div class="layer-sliders-grid">
          <div class="slider-group">
            <div class="slider-header">
              <span>Ukuran Desain (Lebar)</span>
              <span id="scaleValueDisplay">28%</span>
            </div>
            <input type="range" id="sliderScale" min="10" max="65" value="28" class="custom-range">
          </div>

          <div class="slider-group">
            <div class="slider-header">
              <span>Posisi Vertikal (Atas - Bawah)</span>
              <span id="posYValueDisplay">35%</span>
            </div>
            <input type="range" id="sliderPosY" min="15" max="75" value="35" class="custom-range">
          </div>

          <div class="slider-group">
            <div class="slider-header">
              <span>Posisi Horizontal (Kiri - Kanan)</span>
              <span id="posXValueDisplay">50%</span>
            </div>
            <input type="range" id="sliderPosX" min="15" max="85" value="50" class="custom-range">
          </div>

          <div class="slider-group">
            <div class="slider-header">
              <span>Sudut Rotasi</span>
              <span id="rotValueDisplay">0°</span>
            </div>
            <input type="range" id="sliderRot" min="-180" max="180" value="0" class="custom-range">
          </div>
        </div>
        <div class="layer-actions-row">
          <button type="button" class="btn sm" id="btnResetPlacement">Pusatkan ke Tengah</button>
        </div>
      </div>

      <!-- Studio Multi-Tabs Header -->
      <div class="studio-tabs">
        <button type="button" class="studio-tab-btn active" data-tab="model">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M247.59,61.22,195.83,33A8,8,0,0,0,192,32H160a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-8-8H64a8,8,0,0,0-3.84,1L8.41,61.22A15.76,15.76,0,0,0,1.82,82.48l19.27,36.81A16.37,16.37,0,0,0,35.67,128H56v80a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16V128h20.34a16.37,16.37,0,0,0,14.58-8.71l19.27-36.81A15.76,15.76,0,0,0,247.59,61.22Z"></path></svg>
          <span>Model</span>
        </button>

        <button type="button" class="studio-tab-btn" data-tab="graphics">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM156,88a12,12,0,1,1-12,12A12,12,0,0,1,156,88Z"></path></svg>
          <span>Grafis</span>
        </button>

        <button type="button" class="studio-tab-btn" data-tab="upload">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm-8,160H48V160l36.69-36.69a16,16,0,0,1,22.62,0L144,160l36.69-36.69a16,16,0,0,1,22.62,0L208,128.69V200Z"></path></svg>
          <span>Upload</span>
        </button>

        <button type="button" class="studio-tab-btn" data-tab="text">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M208,56V88a8,8,0,0,1-16,0V64H136V192h24a8,8,0,0,1,0,16H96a8,8,0,0,1,0-16h24V64H64V88a8,8,0,0,1-16,0V56a8,8,0,0,1,8-8H200A8,8,0,0,1,208,56Z"></path></svg>
          <span>Teks</span>
        </button>

        <button type="button" class="studio-tab-btn" data-tab="details" style="font-weight:800; color:var(--red-ink);">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><use href="#ph-sparkle"></use></svg>
          <span>Rincian Produk</span>
        </button>

        <button type="button" class="studio-tab-btn" data-tab="drafts">
          <span> Drafts (<span id="draftsCountBadge"><?= $draftCount ?></span>)</span>
        </button>
      </div>

      <!-- Tab 1: Model & Bahan -->
      <div class="tab-pane active" id="tab-model">
        <div class="opt-group">
          <label class="opt-label">Pilih Model Pakaian:</label>
          <div class="models-grid">
            <div class="model-card active" data-model="tshirt">
              <div class="model-card-ico">
                <svg class="ic" viewBox="0 0 256 256" style="width:28px;height:28px;color:var(--red-ink);"><use href="#ph-t-shirt"></use></svg>
              </div>
              <div class="model-card-text">
                <h4 class="model-card-name">Kaos Heavyweight</h4>
                <span class="model-card-price">Mulai Rp 135.000</span>
              </div>
            </div>

            <div class="model-card" data-model="hoodie">
              <div class="model-card-ico">
                <svg class="ic" viewBox="0 0 256 256" style="width:28px;height:28px;color:var(--red-ink);"><use href="#ph-t-shirt"></use></svg>
              </div>
              <div class="model-card-text">
                <h4 class="model-card-name">Hoodie Pullover</h4>
                <span class="model-card-price">Mulai Rp 265.000</span>
              </div>
            </div>
          </div>
        </div>

        <div class="opt-group">
          <label class="opt-label">Pilih Material Kain / Bahan:</label>
          <div class="fabrics-list">
            <div class="fabric-item active" data-fabric="combed24s">
              <div class="fabric-info">
                <h4>Cotton Combed 24s</h4>
                <p>Ketebalan sedang, serat halus, menyerap keringat maksimal.</p>
              </div>
              <span class="fabric-badge">Standar (+Rp 0)</span>
            </div>

            <div class="fabric-item" data-fabric="heavy16s">
              <div class="fabric-info">
                <h4>Heavyweight Cotton 16s</h4>
                <p>Kain tebal kaku bergaya streetwear boxy modern vintage.</p>
              </div>
              <span class="fabric-badge">+Rp 20.000</span>
            </div>

            <div class="fabric-item" data-fabric="fleece">
              <div class="fabric-info">
                <h4>Cotton Fleece 330 Gsm</h4>
                <p>Bahan tebal berbulu lembut di bagian dalam, khusus hoodie.</p>
              </div>
              <span class="fabric-badge">+Rp 35.000</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Grafis & Logo TNM -->
      <div class="tab-pane" id="tab-graphics">
        <div class="opt-group">
          <div class="opt-label">
            <span>Koleksi Logo & Artwork Resmi TNM:</span>
            <span class="note">Klik logo untuk menempelkan ke baju</span>
          </div>
          <div class="presets-grid">
            <?php foreach ($presets as $idx => $p): ?>
              <div class="preset-item <?= $idx === 2 ? 'active' : '' ?>" <?= $idx === 2 ? 'data-default="true"' : '' ?> data-src="../<?= e($p['image_path']) ?>" data-name="<?= e($p['name']) ?>" title="<?= e($p['name']) ?>">
                <div class="preset-thumb-box">
                  <img src="../<?= e($p['image_path']) ?>" alt="<?= e($p['name']) ?>">
                </div>
                <span class="preset-name"><?= e($p['name']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tab 3: Unggah File Grafis Sendiri -->
      <div class="tab-pane" id="tab-upload">
        <div class="opt-group">
          <label class="opt-label">Unggah File Desain / Logo Pribadi:</label>
          <label class="upload-dropzone">
            <div class="upload-icon">
              <svg class="ic" viewBox="0 0 256 256" style="width:28px;height:28px;"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM152,44l44,44H152ZM200,216H56V40h80V96a8,8,0,0,0,8,8h56V216Z"></path></svg>
            </div>
            <h4 style="font-size:15px;font-weight:800;color:var(--ink-bright);">Klik untuk Pilih File Gambar</h4>
            <p style="font-size:12.5px;color:var(--muted);max-width:32ch;">Mendukung format PNG transparan, JPG, WebP, atau SVG (Maksimal 10 MB).</p>
            <input type="file" id="customImageUpload" accept="image/png, image/jpeg, image/webp, image/svg+xml" style="display:none;">
          </label>
        </div>
      </div>

      <!-- Tab 4: Teks Kustom -->
      <div class="tab-pane" id="tab-text">
        <div class="text-tool-form">
          <div class="opt-group">
            <label class="opt-label">Tulisan / Kalimat:</label>
            <input type="text" id="customTextInput" class="form-input" placeholder="Ketik kata, nama atau quote..." maxlength="30">
          </div>

          <div class="opt-group">
            <label class="opt-label">Pilih Jenis Font:</label>
            <div class="font-picker">
              <div class="font-opt active" data-font="'Archivo Black', sans-serif" style="font-family:'Archivo Black';">Bold Impact</div>
              <div class="font-opt" data-font="'Plus Jakarta Sans', sans-serif" style="font-family:'Plus Jakarta Sans'; font-weight:800;">Modern Sans</div>
              <div class="font-opt" data-font="Georgia, serif" style="font-family:Georgia, serif; font-weight:bold;">Classic Serif</div>
              <div class="font-opt" data-font="'Courier New', monospace" style="font-family:monospace; font-weight:bold;">Retro Mono</div>
            </div>
          </div>

          <div class="opt-group" style="display:flex; gap:16px; align-items:center;">
            <div>
              <label class="opt-label" style="margin-bottom:6px;">Warna Teks:</label>
              <input type="color" id="customTextColor" value="#FFFFFF" style="width:48px;height:40px;border:0;background:transparent;cursor:pointer;">
            </div>
            <div style="flex-grow:1;">
              <button type="button" class="btn red" id="btnAddCustomText" style="width:100%;margin-top:14px;">
                <span>+ Pasang Teks ke Baju</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 5: Rincian & Formulir Penerbitan Produk Siap Jual (Dalam Tab Rapi) -->
      <div class="tab-pane" id="tab-details">
        <div class="admin-details-form-box">
          <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--ring); padding-bottom:10px;">
            <h3 style="font-size:15px; font-weight:800; color:var(--ink-bright); margin:0; display:flex; align-items:center; gap:8px;">
              <svg class="ic" viewBox="0 0 256 256" style="width:18px;height:18px;color:var(--red-ink);"><use href="#ph-sparkle"></use></svg>
              <span>Rincian & Pengaturan Katalog Marketplace</span>
            </h3>
            <span style="font-size:11.5px; color:var(--muted); font-weight:600;">Lengkapi info produk sebelum terbit</span>
          </div>

          <form id="builderPublishForm" style="display:flex; flex-direction:column; gap:14px;">
            <!-- Nama Produk -->
            <div class="form-group">
              <label style="display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px;">Nama Produk Baju Jadi *</label>
              <input type="text" id="bProdName" required class="form-control" style="width:100%;" value="Kaos Eksklusif TNM #CeritaSerem - Hitam Pekat">
            </div>

            <!-- Harga Jual, Harga Coret, Stok -->
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;">
              <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Harga Jual (Rp) *</label>
                <input type="number" id="bProdPrice" required class="form-control" style="width:100%;" value="149000">
              </div>
              <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Harga Coret (Rp)</label>
                <input type="number" id="bProdOriginalPrice" class="form-control" style="width:100%;" value="199000" placeholder="Opsional">
              </div>
              <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Stok Awal</label>
                <input type="number" id="bProdStock" class="form-control" style="width:100%;" value="100">
              </div>
            </div>

            <!-- Badge Tag & Status di Beranda -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
              <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Badge Tag Produk</label>
                <select id="bProdBadge" class="form-control" style="width:100%;">
                  <option value="Official Drop" selected>Official Drop</option>
                  <option value="Limited Edition">Limited Edition</option>
                  <option value="Bestseller">Bestseller</option>
                  <option value="New Release">New Release</option>
                  <option value="Midnight Exclusive">Midnight Exclusive</option>
                  <option value="">Tanpa Badge</option>
                </select>
              </div>
              <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Status di Beranda</label>
                <label style="display:flex; align-items:center; gap:8px; height:42px; cursor:pointer; font-size:13px; font-weight:700; color:var(--ink);">
                  <input type="checkbox" id="bProdFeatured" checked value="1">
                  <span>Tampilkan di Sorotan Utama</span>
                </label>
              </div>
            </div>

            <!-- Deskripsi Produk -->
            <div class="form-group">
              <label style="display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px;">Deskripsi Produk Siap Jual</label>
              <textarea id="bProdDesc" rows="3" class="form-control" style="width:100%; line-height:1.5;">Koleksi pakaian resmi TNM (Teman Ngobrol Malam). Didesain dengan presisi tinggi menggunakan teknik Sablon DTF Premium HD yang tajam dan tahan cuci berkali-kali di atas bahan kain katun distro pilihan yang super nyaman dan adem.</textarea>
            </div>

            <!-- Tombol Aksi: Simpan Draft & Terbitkan -->
            <div style="display:flex; gap:10px; margin-top:6px; flex-wrap:wrap;">
              <button type="button" class="btn lg" id="btnSaveDraftInTab" style="background:var(--shell); border:1px solid var(--ring); color:var(--ink); font-weight:700; padding:12px 18px;">
                <span> Simpan Draft</span>
              </button>
              <button type="submit" id="btnSubmitPublish" class="btn red lg" style="flex:1; justify-content:center; padding:12px 20px; font-size:14.5px; font-weight:800; box-shadow:0 10px 24px -10px rgba(214,59,41,0.6);">
                <svg class="ic" viewBox="0 0 256 256" style="width:18px;height:18px;"><path d="M227.31,73.37,182.63,28.68a16,16,0,0,0-22.63,0L36.69,152A15.86,15.86,0,0,0,32,163.31V208a16,16,0,0,0,16,16H92.69A15.86,15.86,0,0,0,104,219.31L227.31,96a16,16,0,0,0,0-22.63ZM92.69,208H48V163.31l88-88L180.69,120ZM192,108.68,147.31,64l24-24L216,84.68Z"></path></svg>
                <span> Terbitkan Baju Jadi ke Marketplace</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tab 6: Daftar Draft Tersimpan -->
      <div class="tab-pane" id="tab-drafts">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
          <div>
            <h3 style="font-size:15px; font-weight:800; color:var(--ink-bright); margin:0;">
              Draft Desain Tersimpan
            </h3>
            <span style="font-size:12px; color:var(--muted);">Lanjutkan rancangan pakaian yang pernah kamu simpan sebelumnya.</span>
          </div>
          <button type="button" class="btn sm" id="btnSaveNewDraft" style="background:var(--red-soft); color:var(--red-ink); border:1px solid rgba(214,59,41,0.3); font-weight:700;">
            <span>+ Simpan Rancangan Baru</span>
          </button>
        </div>

        <!-- Drafts Grid Container -->
        <div class="drafts-grid" id="draftsListGrid">
          <!-- Rendered via JS -->
        </div>
      </div>

      <!-- Sticky Bottom Action Bar (Ringkas & Cepat Diakses dari Semua Tab) -->
      <div class="studio-bottom-action-bar" id="studioBottomActionBar">
        <div class="bottom-bar-info">
          <span class="bottom-bar-summary" id="bottomBarSpecSummary">Kaos Heavyweight • Hitam Pekat</span>
          <span class="bottom-bar-price" id="bottomBarPrice">Rp 149.000</span>
        </div>
        <div class="bottom-bar-actions">
          <button type="button" class="btn sm ghost" id="btnQuickSaveDraft" title="Simpan desain saat ini sebagai draft">
            <span> Simpan Draft</span>
          </button>
          <button type="button" class="btn sm red" id="btnQuickOpenDetails">
            <span> Rincian & Terbitkan →</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal Dialog Sukses Penerbitan -->
<div id="builderModalNotice" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(5px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-md); padding:32px; max-width:440px; width:100%; text-align:center; box-shadow:0 24px 48px rgba(0,0,0,0.8);">
    <div style="width:54px; height:54px; border-radius:50%; background:rgba(46,204,113,0.15); color:#2ecc71; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
      <svg class="ic" style="width:28px;height:28px;"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z" fill="currentColor"></path></svg>
    </div>
    <h3 style="font-size:20px; font-weight:800; color:var(--ink); margin:0 0 8px;">Baju Jadi Berhasil Terbit!</h3>
    <p style="font-size:13.5px; color:var(--muted); margin:0 0 24px; line-height:1.5;">
      Snapshot fotorealistik resolusi tinggi telah dibuat dan produk otomatis ditambahkan ke katalog marketplace utama TNM.
    </p>
    <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
      <a id="btnModalViewProduct" href="../catalog.php" target="_blank" class="btn red sm">Lihat di Toko ↗</a>
      <a href="products.php" class="btn sm ghost">Ke Daftar Produk</a>
      <button type="button" onclick="document.getElementById('builderModalNotice').style.display='none';" class="btn sm ghost">Buat Lagi</button>
    </div>
  </div>
</div>

<!-- Load Customizer Engine with Relative Asset Base & Initial Drafts for Admin -->
<script>
  window.STUDIO_ASSET_BASE = '../assets/';
  window.INITIAL_DRAFTS = <?= json_encode($drafts) ?>;
</script>
<script src="../assets/js/customizer.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
