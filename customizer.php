<?php
/**
 * TNM Bespoke Studio — Realistic 2.5D Custom Apparel Designer
 * Ultra-Accurate Fabric-Draped Decal Studio Engine
 * Full-Freedom Interactive Dragging, Transform Handles, Preset Zones, True Crease Multiply.
 */
require_once __DIR__ . '/config.php';
$pageTitle = "Studio Kustom Kaos & Hoodie TNM";
$extraCss = "assets/css/customizer.css";
$extraJs = "assets/js/customizer.js";

$db = getDB();
$presets = $db->query("SELECT * FROM preset_designs WHERE is_active = 1 ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- SVG Filter for Real Cloth Fiber Texture -->
<svg width="0" height="0" style="position:absolute; display:none;" aria-hidden="true">
  <defs>
    <filter id="clothTextureFilter" x="0%" y="0%" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" result="noise" />
      <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 0.12 0" in="noise" result="grain" />
      <feComposite in="SourceGraphic" in2="grain" operator="in" result="textured" />
      <feBlend in="SourceGraphic" in2="grain" mode="multiply" />
    </filter>
  </defs>
</svg>

<main class="wrap customizer-layout">
  <!-- Studio Header -->
  <div class="studio-head">
    <div class="studio-title-box">
      <h1>
        <svg class="ic" viewBox="0 0 256 256" style="width:26px;height:26px;color:var(--red-ink);"><use href="#ph-sparkle"></use></svg>
        <span>Bespoke Apparel Studio</span>
      </h1>
      <p>Kreasikan baju & hoodie dengan tampilan fotorealistik studio. Geser dan atur ukuran logo bebas langsung di atas pakaian — sablon menyatu dengan lekukan kain asli.</p>
    </div>

    <div class="studio-price-box">
      <div class="price-display">
        <span class="price-label">Estimasi Harga</span>
        <div class="price-amount" id="studioPrice">Rp 135.000</div>
      </div>

      <button type="button" class="btn red lg" id="btnStudioAddToCart">
        <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
        <span>Masukkan ke Keranjang</span>
      </button>
    </div>
  </div>

  <!-- Studio Main Grid -->
  <div class="studio-grid">
    <!-- Left Column: Realistic Mockup Stage -->
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
          <img class="garment-base-layer" id="garmentBaseImg" src="assets/img/mockups/tshirt-front.png" alt="Shading Pakaian">

          <!-- Safe Printable Zone Guideline (Akurat mengikuti batas badan model) -->
          <div class="garment-safe-zone" id="garmentSafeZone">
            <span class="safe-zone-badge" id="safeZoneBadge">Batas Cetak Model</span>
          </div>

          <!-- Layer 3: Clamped & Masked Graphic Layer (Strictly Clipped by 3D Model Silhouette) -->
          <div class="garment-masked-graphic-layer" id="garmentMaskedGraphicLayer">
            <div class="placed-graphic-item" id="placedGraphicItem" style="display:none;">
              <img id="activePlacedImg" class="placed-graphic-img" src="" alt="Desain Sablon">
            </div>
          </div>

          <!-- Layer 3b: Interactive Transform Frame Overlay (Draggable & Resizable anywhere within model) -->
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

          <!-- Layer 4: Top Crease Blend (Fabric Folds drape OVER the graphic naturally!) -->
          <img class="garment-crease-overlay" id="garmentCreaseImg" src="assets/img/mockups/tshirt-front.png" alt="Lipatan Kain">
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
        </div>
      </div>
    </div>

    <!-- Right Column: Multi-Tab Customization Controls -->
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
      <div class="layer-control-panel" id="layerControlPanel">
        <div class="layer-control-head">
          <span style="font-weight:700;font-size:13px;color:var(--ink-bright);" id="activeLayerTitle">Pengaturan Desain Aktif</span>
          <button type="button" class="btn-clear-layer" id="btnDeleteActiveLayer" title="Hapus desain pada sisi ini"> Hapus Desain</button>
        </div>
        <div class="layer-sliders-grid">
          <div class="slider-group">
            <div class="slider-header">
              <span>Ukuran Desain (Lebar)</span>
              <span id="scaleValueDisplay">30%</span>
            </div>
            <input type="range" id="sliderScale" min="10" max="65" value="30" class="custom-range">
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

      <!-- Tabs Header -->
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

        <button type="button" class="studio-tab-btn" data-tab="size">
          <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;"><path d="M232,88V208a16,16,0,0,1-16,16H40a16,16,0,0,1-16-16V88A16,16,0,0,1,40,72H216A16,16,0,0,1,232,88Z"></path></svg>
          <span>Ukuran</span>
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
            <?php foreach ($presets as $p): ?>
              <div class="preset-item" data-src="<?= e($p['image_path']) ?>" data-name="<?= e($p['name']) ?>" title="<?= e($p['name']) ?>">
                <div class="preset-thumb-box">
                  <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['name']) ?>">
                </div>
                <span class="preset-name"><?= e($p['name']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tab 3: Unggah Gambar Sendiri -->
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

      <!-- Tab 5: Ukuran & Selesai -->
      <div class="tab-pane" id="tab-size">
        <div class="opt-group">
          <label class="opt-label">Pilih Ukuran Baju:</label>
          <div class="sizes-row">
            <button type="button" class="size-chip" data-size="S">S</button>
            <button type="button" class="size-chip" data-size="M">M</button>
            <button type="button" class="size-chip active" data-size="L">L</button>
            <button type="button" class="size-chip" data-size="XL">XL</button>
            <button type="button" class="size-chip" data-size="XXL">XXL</button>
            <button type="button" class="size-chip" data-size="3XL">3XL</button>
          </div>
        </div>

        <div class="opt-group" style="padding:14px;border-radius:var(--radius-md);background:var(--shell);border:1px solid var(--ring);font-size:12.5px;color:var(--muted);line-height:1.6;">
          <h5 style="color:var(--ink-bright);font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
            <svg class="ic" style="color:var(--red-ink);width:16px;height:16px;"><use href="#ph-sparkle"></use></svg>
            <span>Spesifikasi Kualitas Sablon TNM:</span>
          </h5>
          <p>• Menggunakan teknologi <b>Direct Transfer Film (DTF) High Definition</b> dengan tinta elastis tahan cuci dan setrika terbalik.</p>
          <p>• Desain kustommu akan otomatis tersimpan dalam resolusi tinggi dan diperiksa oleh tim produksi sebelum proses cetak.</p>
        </div>
      </div>

      <!-- Studio Bottom Checkout Bar -->
      <div class="studio-checkout-bar" id="studioCheckoutBar">
        <div class="studio-checkout-info">
          <span class="studio-checkout-label">Spesifikasi Pilihan:</span>
          <b class="studio-checkout-summary">
            <span id="selectedSizeLabel">Size L (Standar)</span>
            <span class="studio-bottom-sep" style="color:var(--faint); margin:0 4px;">•</span>
            <span id="studioBottomPrice" style="color:var(--red-ink); font-weight:800;">Rp 135.000</span>
          </b>
        </div>
        <button type="button" class="btn red" id="btnStudioBottomAddToCart" onclick="document.getElementById('btnStudioAddToCart').click()">
          <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
          <span>Selesai & Tambah Keranjang</span>
        </button>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
