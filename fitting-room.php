<?php
/**
 * TNM (Teman Ngobrol Malam) — 3D Fitting Room & Wardrobe Simulator
 * Konsep 4: Interactive 3D Merchandise Dressing Room & Fan Photobooth
 * 
 * Powered by Three.js (WebGL PBR) + Modular 3D Accessories + Instant Polaroid Photobooth.
 * Strict Zero Emojis Compliance.
 */

require_once __DIR__ . '/config.php';

$pageTitle = "3D Fitting Room & Wardrobe Simulator — TNM";
$shopUrl   = "catalog.php";
$homeUrl   = "index.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Archivo+Black&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="assets/css/style.css">
  
  <style>
    :root {
      --panel-w: 420px;
    }
    
    body {
      margin: 0;
      padding: 0;
      background-color: var(--bg);
      color: var(--ink);
      font-family: var(--body);
      overflow: hidden;
      height: 100vh;
      width: 100vw;
    }

    /* App Shell */
    .app-container {
      display: flex;
      height: 100vh;
      width: 100vw;
      position: relative;
    }

    /* 3D Viewport Area */
    .viewport-area {
      flex: 1;
      height: 100%;
      position: relative;
      background: radial-gradient(circle at 50% 40%, #1c1815 0%, #0c0b0a 75%, #050505 100%);
      overflow: hidden;
    }

    #webgl-canvas {
      width: 100%;
      height: 100%;
      display: block;
    }

    /* Floating Top Bar */
    .top-bar {
      position: absolute;
      top: 16px;
      left: 20px;
      right: calc(var(--panel-w) + 20px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      pointer-events: none;
      z-index: 10;
    }

    .top-bar > * {
      pointer-events: auto;
    }

    .brand-pill {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(26, 23, 18, 0.85);
      backdrop-filter: blur(12px);
      border: 1px solid var(--ring-strong);
      padding: 8px 16px;
      border-radius: 999px;
      box-shadow: var(--shadow);
      text-decoration: none;
      color: var(--ink-bright);
    }

    .brand-badge {
      background: var(--red);
      color: #fff;
      font-family: var(--mark);
      font-size: 11px;
      padding: 2px 7px;
      border-radius: 4px;
      letter-spacing: 0.05em;
    }

    .brand-title {
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .top-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .action-btn {
      background: rgba(26, 23, 18, 0.85);
      backdrop-filter: blur(12px);
      border: 1px solid var(--ring);
      color: var(--ink);
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s var(--ease);
      text-decoration: none;
    }

    .action-btn:hover {
      border-color: var(--red-ink);
      color: var(--ink-bright);
      transform: translateY(-1px);
    }

    /* Viewport Floating Controls */
    .camera-presets {
      position: absolute;
      left: 20px;
      bottom: 24px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      z-index: 10;
    }

    .preset-btn {
      background: rgba(26, 23, 18, 0.82);
      backdrop-filter: blur(8px);
      border: 1px solid var(--ring);
      color: var(--muted);
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      text-align: left;
      transition: all 0.2s var(--ease);
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-width: 120px;
    }

    .preset-btn.active, .preset-btn:hover {
      background: var(--surface-2);
      color: var(--ink-bright);
      border-color: var(--red-ink);
    }

    .lighting-presets {
      position: absolute;
      left: 160px;
      bottom: 24px;
      display: flex;
      gap: 6px;
      z-index: 10;
    }

    .light-chip {
      background: rgba(26, 23, 18, 0.82);
      backdrop-filter: blur(8px);
      border: 1px solid var(--ring);
      color: var(--muted);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s var(--ease);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .light-chip.active, .light-chip:hover {
      background: var(--surface-2);
      color: var(--ink-bright);
      border-color: var(--red-ink);
    }

    .light-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    .shutter-btn {
      position: absolute;
      right: calc(var(--panel-w) + 24px);
      bottom: 24px;
      background: var(--red);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 10px 25px -5px rgba(214, 59, 41, 0.5);
      transition: all 0.2s var(--ease);
      z-index: 10;
    }

    .shutter-btn:hover {
      background: var(--red-hover);
      transform: scale(1.04);
    }

    /* Right Sidebar Panel */
    .wardrobe-panel {
      width: var(--panel-w);
      height: 100%;
      background: var(--surface);
      border-left: 1px solid var(--ring-strong);
      display: flex;
      flex-direction: column;
      z-index: 20;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
    }

    .panel-header {
      padding: 20px;
      border-bottom: 1px solid var(--ring);
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .panel-sub {
      font-size: 11px;
      font-weight: 800;
      color: var(--red-ink);
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .panel-title {
      font-size: 20px;
      font-weight: 800;
      color: var(--ink-bright);
      margin: 0;
    }

    /* Tabs Navigation */
    .wardrobe-tabs {
      display: flex;
      border-bottom: 1px solid var(--ring);
      background: var(--surface-card);
    }

    .tab-btn {
      flex: 1;
      background: none;
      border: none;
      padding: 12px 8px;
      font-size: 12px;
      font-weight: 700;
      color: var(--muted);
      cursor: pointer;
      text-align: center;
      transition: all 0.2s;
      border-bottom: 2px solid transparent;
    }

    .tab-btn.active {
      color: var(--ink-bright);
      border-bottom-color: var(--red);
      background: var(--surface);
    }

    /* Scrollable Tab Content */
    .panel-body {
      flex: 1;
      overflow-y: auto;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .panel-tab-pane {
      display: none;
      flex-direction: column;
      gap: 14px;
    }

    .panel-tab-pane.active {
      display: flex;
    }

    .section-label {
      font-size: 12px;
      font-weight: 800;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 2px;
    }

    /* Cards Grid */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .item-card {
      background: var(--surface-card);
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s var(--ease);
      display: flex;
      flex-direction: column;
      gap: 8px;
      text-align: left;
    }

    .item-card:hover {
      border-color: var(--ring-strong);
      transform: translateY(-2px);
    }

    .item-card.active {
      border-color: var(--red);
      background: rgba(214, 59, 41, 0.08);
      box-shadow: 0 0 0 1px var(--red);
    }

    .item-swatch {
      width: 100%;
      height: 48px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 800;
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .item-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .item-name {
      font-size: 13px;
      font-weight: 700;
      color: var(--ink-bright);
      line-height: 1.25;
    }

    .item-price {
      font-size: 12px;
      font-weight: 800;
      color: var(--red-ink);
    }

    /* Accessory Toggle Items */
    .acc-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--surface-card);
      border: 1px solid var(--ring);
      border-radius: 10px;
      padding: 12px 14px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .acc-row:hover {
      border-color: var(--ring-strong);
    }

    .acc-row.active {
      border-color: var(--red);
      background: rgba(214, 59, 41, 0.08);
    }

    .acc-name {
      font-size: 13px;
      font-weight: 700;
      color: var(--ink-bright);
    }

    .acc-desc {
      font-size: 11px;
      color: var(--muted);
      margin-top: 1px;
    }

    .acc-switch {
      width: 38px;
      height: 20px;
      background: var(--surface-2);
      border-radius: 999px;
      position: relative;
      transition: all 0.2s;
    }

    .acc-switch::after {
      content: "";
      position: absolute;
      top: 2px;
      left: 2px;
      width: 16px;
      height: 16px;
      background: var(--muted);
      border-radius: 50%;
      transition: all 0.2s;
    }

    .acc-row.active .acc-switch {
      background: var(--red);
    }

    .acc-row.active .acc-switch::after {
      left: 20px;
      background: #fff;
    }

    /* Sub-Tabs for Tops (Official Merch vs Bespoke Customizer) */
    .sub-tabs-bar {
      display: flex;
      gap: 6px;
      background: var(--surface-card);
      padding: 4px;
      border-radius: 8px;
      border: 1px solid var(--ring);
      margin-bottom: 12px;
    }

    .sub-tab-btn {
      flex: 1;
      padding: 8px 6px;
      border-radius: 6px;
      border: none;
      background: none;
      color: var(--muted);
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      transition: all 0.2s var(--ease);
    }

    .sub-tab-btn.active {
      background: var(--surface-2);
      color: var(--ink-bright);
      box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    }

    .sub-pane {
      display: none;
      flex-direction: column;
      gap: 12px;
    }

    .sub-pane.active {
      display: flex;
    }

    /* Custom Apparel Model Selector (Kaos vs Hoodie) */
    .custom-type-selector {
      display: flex;
      gap: 6px;
      margin-bottom: 8px;
    }

    .type-pill-btn {
      flex: 1;
      padding: 8px 6px;
      border-radius: 6px;
      border: 1px solid var(--ring);
      background: var(--surface-card);
      color: var(--muted);
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      transition: all 0.2s var(--ease);
    }

    .type-pill-btn:hover {
      border-color: var(--ring-strong);
      color: var(--ink-bright);
    }

    .type-pill-btn.active {
      border-color: var(--red-ink);
      background: rgba(214, 59, 41, 0.12);
      color: var(--ink-bright);
    }

    /* Color Palette Row */
    .color-swatch-row {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 8px;
    }

    .color-chip {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: 2px solid var(--ring);
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
    }

    .color-chip:hover {
      transform: scale(1.1);
      border-color: var(--ring-strong);
    }

    .color-chip.active {
      border-color: var(--red-ink);
      box-shadow: 0 0 0 2px var(--red);
    }

    /* Presets Marketplace Grid */
    .preset-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }

    .preset-card {
      background: var(--surface-card);
      border: 1px solid var(--ring);
      border-radius: 8px;
      padding: 8px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      text-align: center;
      transition: all 0.2s;
    }

    .preset-card:hover {
      border-color: var(--ring-strong);
      transform: translateY(-1px);
    }

    .preset-card.active {
      border-color: var(--red);
      background: rgba(214, 59, 41, 0.1);
    }

    .preset-preview-box {
      width: 100%;
      height: 48px;
      background: #111;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4px;
    }

    .preset-preview-box img {
      max-width: 100%;
      max-height: 40px;
      object-fit: contain;
    }

    .preset-name {
      font-size: 11px;
      font-weight: 700;
      color: var(--ink-bright);
      line-height: 1.2;
    }

    /* Custom Upload Box */
    .custom-upload-box {
      background: var(--surface-card);
      border: 1px dashed var(--ring-strong);
      border-radius: 8px;
      padding: 12px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
    }

    .custom-upload-box:hover {
      border-color: var(--red-ink);
    }

    .upload-label {
      font-size: 12px;
      font-weight: 700;
      color: var(--ink);
      cursor: pointer;
      display: block;
    }

    .upload-sub {
      font-size: 10px;
      color: var(--muted);
      margin-top: 2px;
    }

    /* Custom Text Row */
    .custom-text-row {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .custom-input {
      flex: 1;
      background: var(--surface-card);
      border: 1px solid var(--ring);
      color: var(--ink-bright);
      font-family: inherit;
      font-size: 12px;
      padding: 8px 10px;
      border-radius: 6px;
      outline: none;
    }

    .custom-input:focus {
      border-color: var(--red-ink);
    }

    .btn-apply-text {
      background: var(--surface-2);
      border: 1px solid var(--ring);
      color: var(--ink-bright);
      padding: 8px 12px;
      font-size: 11px;
      font-weight: 700;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-apply-text:hover {
      border-color: var(--red-ink);
      background: var(--red);
      color: #fff;
    }

    /* Sticky Bottom Checkout Hub */
    .panel-footer {
      padding: 16px 20px;
      border-top: 1px solid var(--ring);
      background: var(--surface-card);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .summary-box {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sum-label {
      font-size: 12px;
      color: var(--muted);
      font-weight: 600;
    }

    .sum-val {
      font-size: 18px;
      font-weight: 800;
      color: var(--ink-bright);
    }

    .buy-btn {
      background: var(--red);
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      display: block;
      transition: all 0.2s;
    }

    .buy-btn:hover {
      background: var(--red-hover);
      transform: translateY(-1px);
    }

    .custom-link {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      text-align: center;
      text-decoration: underline;
      cursor: pointer;
    }

    .custom-link:hover {
      color: var(--ink-bright);
    }

    /* Loading Overlay */
    .loader-overlay {
      position: absolute;
      inset: 0;
      background: #0f0f0f;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 16px;
      z-index: 100;
      transition: opacity 0.5s var(--ease), visibility 0.5s var(--ease);
    }

    .loader-overlay.fade-out {
      opacity: 0;
      pointer-events: none;
      visibility: hidden;
    }

    .spinner {
      width: 48px;
      height: 48px;
      border: 3px solid rgba(236, 232, 221, 0.1);
      border-top-color: var(--red);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .load-text {
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
      letter-spacing: 0.05em;
      text-align: center;
      max-width: 80%;
    }

    .load-progress-bar {
      width: 240px;
      height: 6px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 999px;
      overflow: hidden;
      border: 1px solid var(--ring);
    }

    .load-progress-fill {
      width: 10%;
      height: 100%;
      background: var(--red-ink);
      transition: width 0.3s ease;
    }

    .btn-skip-loading {
      background: rgba(26, 23, 18, 0.9);
      border: 1px solid var(--ring-strong);
      color: var(--ink-bright);
      padding: 8px 18px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 6px;
      transition: all 0.2s var(--ease);
    }

    .btn-skip-loading:hover {
      border-color: var(--red-ink);
      background: var(--surface-2);
    }

    /* Polaroid Modal Popup */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(10px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 200;
      padding: 20px;
    }

    .modal-overlay.active {
      display: flex;
    }

    .polaroid-frame {
      background: #ffffff;
      padding: 16px 16px 28px 16px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
      border-radius: 4px;
      max-width: 420px;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 12px;
      transform: rotate(-1.5deg);
      animation: popIn 0.3s var(--ease-out);
    }

    @keyframes popIn {
      from { transform: scale(0.9) rotate(0deg); opacity: 0; }
      to { transform: scale(1) rotate(-1.5deg); opacity: 1; }
    }

    .polaroid-img-box {
      width: 100%;
      aspect-ratio: 1 / 1;
      background: #111;
      overflow: hidden;
      border-radius: 2px;
    }

    .polaroid-img-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .polaroid-caption {
      display: flex;
      flex-direction: column;
      gap: 4px;
      font-family: 'Courier New', Courier, monospace;
      color: #1a1712;
    }

    .polaroid-tag {
      font-size: 12px;
      font-weight: bold;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #d63b29;
    }

    .polaroid-title {
      font-size: 16px;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .polaroid-meta {
      font-size: 11px;
      color: #666;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      margin-top: 6px;
    }

    .btn-download {
      flex: 1;
      background: #111;
      color: #fff;
      border: none;
      padding: 10px;
      font-size: 13px;
      font-weight: 800;
      border-radius: 4px;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
    }

    .btn-close-modal {
      background: #eee;
      color: #333;
      border: none;
      padding: 10px 16px;
      font-size: 13px;
      font-weight: 700;
      border-radius: 4px;
      cursor: pointer;
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
      .app-container {
        flex-direction: column;
      }
      .viewport-area {
        height: 55vh;
      }
      .wardrobe-panel {
        width: 100%;
        height: 45vh;
      }
      .top-bar {
        right: 20px;
      }
      .shutter-btn {
        right: 20px;
        bottom: 16px;
      }
      .camera-presets {
        display: none;
      }
      .lighting-presets {
        left: 20px;
      }
    }
  </style>

  <!-- Global script error catcher before module load -->
  <script>
    window.__fittingRoomError = null;
    window.addEventListener('error', function(e) {
      console.error('Fitting room runtime error:', e);
      window.__fittingRoomError = e.message || 'Error occurred';
      const lt = document.getElementById('loadText');
      if (lt) {
        lt.textContent = 'Status: ' + (e.message || 'Gagal memuat modul');
      }
      const btnSkip = document.getElementById('btnSkipLoading');
      if (btnSkip) btnSkip.style.display = 'inline-block';
    });
  </script>

  <!-- Three.js and Addons via local vendor paths -->
  <script type="importmap">
    {
      "imports": {
        "three": "./assets/js/vendor/three/three.module.js",
        "three/addons/": "./assets/js/vendor/three/addons/"
      }
    }
  </script>
</head>
<body>

  <!-- Loading Screen -->
  <div class="loader-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="load-text" id="loadText">Memuat Studio 3D Andri...</div>
    <div class="load-progress-bar">
      <div class="load-progress-fill" id="loadProgressFill"></div>
    </div>
    <button type="button" class="btn-skip-loading" id="btnSkipLoading" style="display:none;">
      Masuk ke Studio
    </button>
  </div>

  <div class="app-container">
    
    <!-- 3D Viewport Stage -->
    <div class="viewport-area">
      
      <!-- Top Bar -->
      <div class="top-bar">
        <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="brand-pill" title="Kembali ke Beranda">
          <span class="brand-badge">TNM 3D</span>
          <span class="brand-title">Fitting Room & Studio Wardrobe</span>
        </a>

        <div class="top-actions">
          <button type="button" class="action-btn" id="btnAudioToggle" title="Nyalakan/Matikan Atmosfer Malam">
            <span id="audioStatusText">Suara Malam: OFF</span>
          </button>
          <a href="<?php echo htmlspecialchars($shopUrl); ?>" class="action-btn" title="Buka Toko Merchandise">
            <span>Katalog Toko</span>
          </a>
        </div>
      </div>

      <!-- Camera Presets -->
      <div class="camera-presets">
        <button type="button" class="preset-btn active" data-cam="full">Full Body</button>
        <button type="button" class="preset-btn" data-cam="portrait">Close-Up Wajah</button>
        <button type="button" class="preset-btn" data-cam="chest">Fokus Baju</button>
        <button type="button" class="preset-btn" data-cam="shoes">Fokus Sepatu</button>
      </div>

      <!-- Studio Lighting Presets -->
      <div class="lighting-presets">
        <button type="button" class="light-chip active" data-light="warm">
          <span class="light-dot" style="background:#f59e0b;"></span>
          <span>Studio Warm</span>
        </button>
        <button type="button" class="light-chip" data-light="blue">
          <span class="light-dot" style="background:#06b6d4;"></span>
          <span>Midnight Blue</span>
        </button>
        <button type="button" class="light-chip" data-light="red">
          <span class="light-dot" style="background:#ef4444;"></span>
          <span>Crimson Horor</span>
        </button>
      </div>

      <!-- Shutter Button -->
      <button type="button" class="shutter-btn" id="btnShutter">
        <span>Foto Fit Check</span>
      </button>

      <!-- WebGL Canvas Container -->
      <canvas id="webgl-canvas"></canvas>
    </div>

    <!-- Right Wardrobe Panel -->
    <aside class="wardrobe-panel">
      <div class="panel-header">
        <span class="panel-sub">Interactive 3D Merch</span>
        <h2 class="panel-title">Ganti Kostum Andri</h2>
      </div>

      <!-- Tabs -->
      <nav class="wardrobe-tabs">
        <button type="button" class="tab-btn active" data-tab="tab-tops">Atasan</button>
        <button type="button" class="tab-btn" data-tab="tab-bottoms">Bawahan</button>
        <button type="button" class="tab-btn" data-tab="tab-acc">Aksesoris</button>
      </nav>

      <!-- Panel Body -->
      <div class="panel-body">
        
        <!-- Tab 1: Tops / Kaos & Hoodie -->
        <div class="panel-tab-pane active" id="tab-tops">
          
          <!-- Sub-tabs: Official Merch vs Marketplace Bespoke Customizer -->
          <div class="sub-tabs-bar">
            <button type="button" class="sub-tab-btn active" data-sub="sub-official">Koleksi Toko Resmi</button>
            <button type="button" class="sub-tab-btn" data-sub="sub-custom">Sablon Kustom Marketplace</button>
          </div>

          <!-- Sub-Pane 1: Official Catalog -->
          <div class="sub-pane active" id="sub-official">
            <span class="section-label">Koleksi Kaos & Baju Resmi</span>
            <div class="cards-grid">
              
              <div class="item-card active" data-slot="top" data-apparel="tshirt" data-id="top-signature" data-name="Kaos Signature TNM" data-price="135000" data-color="#141416" data-preset="assets/img/presets/preset-logo-tnm.svg" data-text="">
                <div class="item-swatch" style="background: #141416; border: 1px solid #d63b29;">TNM</div>
                <div class="item-info">
                  <span class="item-name">Kaos Signature TNM</span>
                  <span class="item-price">Rp 135.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="tshirt" data-id="top-malamminggu" data-name="Kaos Regular #MalamMinggu" data-price="139000" data-color="#182236" data-preset="assets/img/presets/preset-malamminggu.svg" data-text="">
                <div class="item-swatch" style="background: #182236; color:#38bdf8; border: 1px solid #38bdf8;">RADIO</div>
                <div class="item-info">
                  <span class="item-name">Kaos #MalamMinggu</span>
                  <span class="item-price">Rp 139.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="tshirt" data-id="top-wordmark" data-name="Kaos Eksklusif TNM Wordmark" data-price="149000" data-color="#141416" data-preset="assets/img/presets/preset-logo-tnm.svg" data-text="TEMAN NGOBROL MALAM">
                <div class="item-swatch" style="background: #141416; color:#fff; border: 1px solid #888;">TNM HD</div>
                <div class="item-info">
                  <span class="item-name">Kaos Wordmark TNM</span>
                  <span class="item-price">Rp 149.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="tshirt" data-id="top-cerita" data-name="Kaos #CeritaSerem" data-price="145000" data-color="#0a0a0b" data-preset="assets/img/presets/preset-ceritaserem.svg" data-text="">
                <div class="item-swatch" style="background: #0a0a0b; color:#eb2d1e; border: 1px solid #eb2d1e;">#SEREM</div>
                <div class="item-info">
                  <span class="item-name">Kaos #CeritaSerem</span>
                  <span class="item-price">Rp 145.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="tshirt" data-id="top-white" data-name="Kaos Vintage Ivory" data-price="130000" data-color="#f0ece1" data-preset="assets/img/presets/preset-badge-radio.svg" data-text="">
                <div class="item-swatch" style="background: #f0ece1; color:#1a1712; border: 1px solid #ccc;">IVORY</div>
                <div class="item-info">
                  <span class="item-name">Kaos Vintage Ivory</span>
                  <span class="item-price">Rp 130.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="tshirt" data-id="top-genderuwo" data-name="Kaos Mistik Genderuwo" data-price="150000" data-color="#0e0d0e" data-preset="assets/img/presets/preset-emblem-red.svg" data-text="">
                <div class="item-swatch" style="background: #0e0d0e; color:#ff3b3b; border: 1px solid #991b1b;">MISTIK</div>
                <div class="item-info">
                  <span class="item-name">Kaos Genderuwo</span>
                  <span class="item-price">Rp 150.000</span>
                </div>
              </div>

            </div>

            <span class="section-label" style="margin-top: 14px;">Koleksi Hoodie Pullover Streetwear</span>
            <div class="cards-grid">
              
              <div class="item-card" data-slot="top" data-apparel="hoodie" data-id="top-hoodie-streetwear" data-name="Hoodie Streetwear TNM" data-price="265000" data-color="#141416" data-preset="assets/img/presets/preset-logo-tnm.svg" data-text="">
                <div class="item-swatch" style="background: #141416; border: 1px solid #d63b29;">HOODIE</div>
                <div class="item-info">
                  <span class="item-name">Hoodie Streetwear TNM</span>
                  <span class="item-price">Rp 265.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="hoodie" data-id="top-hoodie-navy" data-name="Hoodie #MalamMinggu Navy" data-price="265000" data-color="#182236" data-preset="assets/img/presets/preset-malamminggu.svg" data-text="">
                <div class="item-swatch" style="background: #182236; color:#38bdf8; border: 1px solid #38bdf8;">NAVY</div>
                <div class="item-info">
                  <span class="item-name">Hoodie #MalamMinggu</span>
                  <span class="item-price">Rp 265.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="hoodie" data-id="top-hoodie-maroon" data-name="Hoodie Maroon Cerita Serem" data-price="275000" data-color="#6b1d1d" data-preset="assets/img/presets/preset-ceritaserem.svg" data-text="">
                <div class="item-swatch" style="background: #6b1d1d; color:#fff; border: 1px solid #e11d48;">MAROON</div>
                <div class="item-info">
                  <span class="item-name">Hoodie Maroon Serem</span>
                  <span class="item-price">Rp 275.000</span>
                </div>
              </div>

              <div class="item-card" data-slot="top" data-apparel="hoodie" data-id="top-hoodie-charcoal" data-name="Hoodie Charcoal Perkara" data-price="265000" data-color="#2e2e2e" data-preset="assets/img/presets/preset-perkara.svg" data-text="">
                <div class="item-swatch" style="background: #2e2e2e; color:#ccc; border: 1px solid #555;">MISTY</div>
                <div class="item-info">
                  <span class="item-name">Hoodie Misty Charcoal</span>
                  <span class="item-price">Rp 265.000</span>
                </div>
              </div>

            </div>
          </div>

          <!-- Sub-Pane 2: Marketplace Bespoke Customizer Engine -->
          <div class="sub-pane" id="sub-custom">
            
            <span class="section-label">1. Pilih Model Pakaian Kustom</span>
            <div class="custom-type-selector">
              <button type="button" class="type-pill-btn active" data-type="tshirt">Kaos Heavyweight (Rp 135.000)</button>
              <button type="button" class="type-pill-btn" data-type="hoodie">Hoodie Pullover (Rp 265.000)</button>
            </div>

            <span class="section-label" style="margin-top:6px;">2. Warna Dasar Kain</span>
            <div class="color-swatch-row">
              <div class="color-chip active" data-color="#141416" style="background:#141416;" title="Hitam Midnight"></div>
              <div class="color-chip" data-color="#f0ece1" style="background:#f0ece1;" title="Vintage Ivory"></div>
              <div class="color-chip" data-color="#8b1515" style="background:#8b1515;" title="Merah Crimson"></div>
              <div class="color-chip" data-color="#182236" style="background:#182236;" title="Midnight Navy"></div>
              <div class="color-chip" data-color="#2e2e2e" style="background:#2e2e2e;" title="Misty Charcoal"></div>
              <div class="color-chip" data-color="#253327" style="background:#253327;" title="Forest Green"></div>
            </div>

            <span class="section-label" style="margin-top:6px;">3. Pilih Sablon Marketplace</span>
            <div class="preset-grid">
              
              <div class="preset-card active" data-preset="assets/img/presets/preset-logo-tnm.svg" data-name="Logo Resmi TNM">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-logo-tnm.svg" alt="Logo TNM">
                </div>
                <span class="preset-name">Logo TNM</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-badge-radio.svg" data-name="Badge Radio Malam">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-badge-radio.svg" alt="Radio Malam">
                </div>
                <span class="preset-name">Radio Malam</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-ceritaserem.svg" data-name="#CeritaSerem Typo">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-ceritaserem.svg" alt="#CeritaSerem">
                </div>
                <span class="preset-name">#CeritaSerem</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-emblem-red.svg" data-name="Emblem Horor Merah">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-emblem-red.svg" alt="Emblem Merah">
                </div>
                <span class="preset-name">Emblem Merah</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-malamminggu.svg" data-name="Malam Minggu Mistik">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-malamminggu.svg" alt="Malam Minggu">
                </div>
                <span class="preset-name">Malam Minggu</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-perkara.svg" data-name="Bedah Perkara">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-perkara.svg" alt="Bedah Perkara">
                </div>
                <span class="preset-name">Bedah Perkara</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-typo-cerita.svg" data-name="Kisah Misteri">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-typo-cerita.svg" alt="Kisah Misteri">
                </div>
                <span class="preset-name">Kisah Misteri</span>
              </div>

              <div class="preset-card" data-preset="assets/img/presets/preset-typo-kisah.svg" data-name="Teman Malam">
                <div class="preset-preview-box">
                  <img src="assets/img/presets/preset-typo-kisah.svg" alt="Teman Malam">
                </div>
                <span class="preset-name">Teman Malam</span>
              </div>

            </div>

            <span class="section-label" style="margin-top:6px;">4. Upload Desain Sendiri</span>
            <div class="custom-upload-box" id="uploadBox">
              <input type="file" id="customDecalInput" accept="image/png, image/jpeg, image/svg+xml" style="display:none;">
              <span class="upload-label">+ Pilih Logo / Desain Sendiri</span>
              <span class="upload-sub">Format PNG transparan, JPG, atau SVG</span>
            </div>

            <span class="section-label" style="margin-top:6px;">5. Teks Sablon Dada</span>
            <div class="custom-text-row">
              <input type="text" id="customTextVal" class="custom-input" placeholder="Teks kustom (contoh: @andriTNM)" maxlength="20">
              <button type="button" id="btnApplyText" class="btn-apply-text">Pasang</button>
            </div>

          </div>

        </div>

        <!-- Tab 2: Bottoms & Shoes -->
        <div class="panel-tab-pane" id="tab-bottoms">
          <span class="section-label">Pilih Celana</span>
          <div class="cards-grid">
            <div class="item-card active" data-slot="bottom" data-id="bot-denim" data-name="Denim Jeans Indigo" data-price="175000" data-color="#3d506d">
              <div class="item-swatch" style="background: #3d506d;">DENIM</div>
              <div class="item-info">
                <span class="item-name">Denim Jeans</span>
                <span class="item-price">Rp 175.000</span>
              </div>
            </div>

            <div class="item-card" data-slot="bottom" data-id="bot-cargo" data-name="Cargo Pants Midnight" data-price="185000" data-color="#18181b">
              <div class="item-swatch" style="background: #18181b; border: 1px solid #333;">CARGO</div>
              <div class="item-info">
                <span class="item-name">Cargo Midnight</span>
                <span class="item-price">Rp 185.000</span>
              </div>
            </div>

            <div class="item-card" data-slot="bottom" data-id="bot-khaki" data-name="Chino Sand Khaki" data-price="165000" data-color="#a3907c">
              <div class="item-swatch" style="background: #a3907c; color:#1a1712;">CHINO</div>
              <div class="item-info">
                <span class="item-name">Chino Khaki</span>
                <span class="item-price">Rp 165.000</span>
              </div>
            </div>

            <div class="item-card" data-slot="bottom" data-id="bot-black" data-name="Chino Jet Black" data-price="165000" data-color="#111113">
              <div class="item-swatch" style="background: #111113; border: 1px solid #333;">BLACK</div>
              <div class="item-info">
                <span class="item-name">Chino Black</span>
                <span class="item-price">Rp 165.000</span>
              </div>
            </div>
          </div>

          <span class="section-label" style="margin-top: 10px;">Pilih Sepatu</span>
          <div class="cards-grid">
            <div class="item-card active" data-slot="shoes" data-id="shoe-canvas" data-name="Classic Canvas Sneakers" data-price="120000" data-color="#2a2a2e">
              <div class="item-swatch" style="background: #2a2a2e; border: 1px solid #555;">CANVAS</div>
              <div class="item-info">
                <span class="item-name">Canvas Sneakers</span>
                <span class="item-price">Rp 120.000</span>
              </div>
            </div>

            <div class="item-card" data-slot="shoes" data-id="shoe-white" data-name="Retro White High-Tops" data-price="150000" data-color="#f4f4f5">
              <div class="item-swatch" style="background: #f4f4f5; color:#111;">WHITE</div>
              <div class="item-info">
                <span class="item-name">Retro White</span>
                <span class="item-price">Rp 150.000</span>
              </div>
            </div>

            <div class="item-card" data-slot="shoes" data-id="shoe-crimson" data-name="Crimson Low Sneakers" data-price="155000" data-color="#8b1515">
              <div class="item-swatch" style="background: #8b1515; color:#fff;">CRIMSON</div>
              <div class="item-info">
                <span class="item-name">Crimson Low</span>
                <span class="item-price">Rp 155.000</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 3: Accessories 3D (Tas Selempang Tactical Only) -->
        <div class="panel-tab-pane" id="tab-acc">
          <span class="section-label">Aksesoris 3D Pilihan</span>
          
          <div class="acc-row" data-acc="slingbag">
            <div>
              <div class="acc-name">Tas Selempang Tactical TNM</div>
              <div class="acc-desc">Sling bag cordura hitam dengan aksen label merah resmi TNM</div>
            </div>
            <div class="acc-switch"></div>
          </div>

        </div>

      </div>

      <!-- Panel Footer / Sticky Checkout Hub -->
      <div class="panel-footer">
        <div class="summary-box">
          <div class="sum-label" id="activeOutfitName">Kaos Signature TNM + Denim</div>
          <div class="sum-val" id="activeTotalPrice">Rp 310.000</div>
        </div>

        <a href="<?php echo htmlspecialchars($shopUrl); ?>" class="buy-btn" id="btnBuyMarketplace">
          Beli Set Ini di Marketplace
        </a>

        <a href="customizer.php" class="custom-link">
          Atau desain sablon sendiri di Studio Kustom
        </a>
      </div>
    </aside>

  </div>

  <!-- Polaroid Photobooth Modal -->
  <div class="modal-overlay" id="polaroidModal">
    <div class="polaroid-frame">
      <div class="polaroid-img-box">
        <img id="polaroidImage" src="" alt="Fit Check Andri TNM">
      </div>
      <div class="polaroid-caption">
        <span class="polaroid-tag">TNM OFFICIAL FIT CHECK</span>
        <div class="polaroid-title" id="polaroidTitle">Kaos Signature TNM</div>
        <div class="polaroid-meta" id="polaroidDate">Diposting malam ini • @andriTNM</div>
      </div>
      <div class="modal-actions">
        <a id="btnDownloadPolaroid" class="btn-download" download="tnm_fit_check_andri.png">Simpan Foto (PNG)</a>
        <button type="button" class="btn-close-modal" id="btnCloseModal">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Client-side Audio Synthesizer (Zero External Dependencies) -->
  <script>
    const AudioEngine = {
      ctx: null,
      ambientOsc: null,
      ambientGain: null,
      isPlaying: false,

      init() {
        if (!this.ctx) {
          const AudioContext = window.AudioContext || window.webkitAudioContext;
          this.ctx = new AudioContext();
        }
      },

      playClick() {
        this.init();
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = "sine";
        osc.frequency.setValueAtTime(800, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(300, this.ctx.currentTime + 0.05);
        gain.gain.setValueAtTime(0.12, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.05);
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.05);
      },

      playShutter() {
        this.init();
        // Camera shutter noise click
        const bufferSize = this.ctx.sampleRate * 0.08;
        const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) {
          data[i] = Math.random() * 2 - 1;
        }
        const noise = this.ctx.createBufferSource();
        noise.buffer = buffer;
        const gain = this.ctx.createGain();
        gain.gain.setValueAtTime(0.3, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.08);
        noise.connect(gain);
        gain.connect(this.ctx.destination);
        noise.start();
      },

      toggleAmbient(callback) {
        this.init();
        if (this.ctx.state === 'suspended') {
          this.ctx.resume();
        }

        if (!this.isPlaying) {
          // Night ambient drone (deep soft sine + pink noise hum)
          this.ambientOsc = this.ctx.createOscillator();
          this.ambientGain = this.ctx.createGain();
          this.ambientOsc.type = "sine";
          this.ambientOsc.frequency.setValueAtTime(65, this.ctx.currentTime);
          this.ambientGain.gain.setValueAtTime(0.05, this.ctx.currentTime);
          this.ambientOsc.connect(this.ambientGain);
          this.ambientGain.connect(this.ctx.destination);
          this.ambientOsc.start();
          this.isPlaying = true;
        } else {
          if (this.ambientOsc) {
            this.ambientOsc.stop();
            this.ambientOsc.disconnect();
          }
          this.isPlaying = false;
        }
        if (callback) callback(this.isPlaying);
      }
    };
  </script>

  <!-- Main Three.js Fitting Room Engine -->
  <script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

    // State
    const state = {
      apparelType: 'tshirt',
      topMode: 'official',
      top: {
        id: 'top-signature',
        name: 'Kaos Signature TNM',
        price: 135000,
        apparel: 'tshirt',
        color: '#141416',
        presetSvg: 'assets/img/presets/preset-logo-tnm.svg',
        text: ''
      },
      customTop: {
        type: 'tshirt',
        baseColor: '#141416',
        presetSvg: 'assets/img/presets/preset-logo-tnm.svg',
        presetName: 'Logo TNM',
        customImage: null,
        text: 'ANDRI 2026',
        textColor: '#f5f5f7',
        price: 135000
      },
      bottom: { id: 'bot-denim', name: 'Denim Jeans Indigo', price: 175000, color: '#3d506d' },
      shoes: { id: 'shoe-canvas', name: 'Classic Canvas Sneakers', price: 120000, color: '#2a2a2e' },
      accessories: {
        slingbag: false
      },
      lightMode: 'warm'
    };

    // DOM Elements
    const canvas = document.getElementById('webgl-canvas');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadText = document.getElementById('loadText');
    const activeOutfitName = document.getElementById('activeOutfitName');
    const activeTotalPrice = document.getElementById('activeTotalPrice');
    const btnBuyMarketplace = document.getElementById('btnBuyMarketplace');
    const btnAudioToggle = document.getElementById('btnAudioToggle');
    const audioStatusText = document.getElementById('audioStatusText');
    const btnShutter = document.getElementById('btnShutter');
    const polaroidModal = document.getElementById('polaroidModal');
    const polaroidImage = document.getElementById('polaroidImage');
    const polaroidTitle = document.getElementById('polaroidTitle');
    const polaroidDate = document.getElementById('polaroidDate');
    const btnDownloadPolaroid = document.getElementById('btnDownloadPolaroid');
    const btnCloseModal = document.getElementById('btnCloseModal');

    // Three.js Scene Setup
    const scene = new THREE.Scene();
    scene.background = null;

    function getViewportDims() {
      const parent = canvas.parentElement;
      const w = (parent && parent.clientWidth > 0) ? parent.clientWidth : Math.max(window.innerWidth - 420, 320);
      const h = (parent && parent.clientHeight > 0) ? parent.clientHeight : window.innerHeight;
      return { w, h };
    }
    const dims = getViewportDims();

    const camera = new THREE.PerspectiveCamera(45, dims.w / dims.h, 0.1, 100);
    camera.position.set(0.0, 0.05, 2.70);

    const renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      antialias: true,
      preserveDrawingBuffer: true,
      alpha: true,
      powerPreference: 'high-performance'
    });
    renderer.setSize(dims.w, dims.h);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.35;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.06;
    controls.minDistance = 0.8;
    controls.maxDistance = 4.5;
    controls.maxPolarAngle = Math.PI / 2 + 0.05;
    controls.target.set(0.0, -0.05, 0.0);

    // Studio Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.85);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xfff3e0, 2.8);
    keyLight.position.set(2.0, 3.0, 2.5);
    keyLight.castShadow = true;
    keyLight.shadow.mapSize.width = 1024;
    keyLight.shadow.mapSize.height = 1024;
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0xe0f2fe, 1.6);
    fillLight.position.set(-2.5, 1.5, 1.5);
    scene.add(fillLight);

    const rimLight = new THREE.DirectionalLight(0xffeedd, 2.2);
    rimLight.position.set(0.5, 2.5, -2.5);
    scene.add(rimLight);

    // Studio Pedestal Floor
    const pedestalGeo = new THREE.CylinderGeometry(0.75, 0.80, 0.05, 48);
    const pedestalMat = new THREE.MeshStandardMaterial({
      color: 0x141210,
      roughness: 0.25,
      metalness: 0.4
    });
    const pedestal = new THREE.Mesh(pedestalGeo, pedestalMat);
    pedestal.position.set(0, -0.98, 0);
    pedestal.receiveShadow = true;
    scene.add(pedestal);

    // Red Accent Ring on Pedestal
    const goldRingGeo = new THREE.TorusGeometry(0.76, 0.008, 16, 48);
    const goldRingMat = new THREE.MeshStandardMaterial({
      color: 0xd63b29,
      roughness: 0.2,
      metalness: 0.9
    });
    const goldRing = new THREE.Mesh(goldRingGeo, goldRingMat);
    goldRing.rotation.x = Math.PI / 2;
    goldRing.position.set(0, -0.955, 0);
    scene.add(goldRing);

    // Loaders
    const gltfLoader = new GLTFLoader();
    const textureLoader = new THREE.TextureLoader();

    let characterModel = null;
    let hoodieModel = null;
    let hoodieMaterial = null;
    const characterMaterials = {};
    const loadedAccessories = {};

    function dismissLoader() {
      if (loadingOverlay) {
        loadingOverlay.classList.add('fade-out');
        loadingOverlay.style.opacity = '0';
        loadingOverlay.style.pointerEvents = 'none';
        setTimeout(() => {
          loadingOverlay.style.display = 'none';
        }, 150);
      }
    }

    setTimeout(() => {
      dismissLoader();
    }, 6500);

    setTimeout(() => {
      const btnSkip = document.getElementById('btnSkipLoading');
      if (btnSkip && loadingOverlay && !loadingOverlay.classList.contains('fade-out')) {
        btnSkip.style.display = 'inline-block';
      }
    }, 2500);

    const btnSkip = document.getElementById('btnSkipLoading');
    if (btnSkip) {
      btnSkip.addEventListener('click', dismissLoader);
    }

    // Dynamic Canvas Texture Baker for Apparel (Kaos & Hoodie)
    const bakerCanvas = document.createElement('canvas');
    bakerCanvas.width = 1024;
    bakerCanvas.height = 1024;
    const bakerCtx = bakerCanvas.getContext('2d');
    let shirtCanvasTexture = null;
    let hoodieCanvasTexture = null;

    function bakeAndApplyApparel() {
      const isHoodie = state.apparelType === 'hoodie';
      const targetMat = isHoodie ? characterMaterials['M_Andri_Hoodie'] : characterMaterials['M_Andri_TShirt'];

      if (!targetMat) return;

      bakerCtx.clearRect(0, 0, 1024, 1024);

      // 1. Base fabric color
      const baseColor = state.topMode === 'custom' 
        ? state.customTop.baseColor 
        : (state.top.color || '#141416');
      bakerCtx.fillStyle = baseColor;
      bakerCtx.fillRect(0, 0, 1024, 1024);

      // 2. Fabric shading & depth gradient
      const grad = bakerCtx.createLinearGradient(0, 0, 0, 1024);
      grad.addColorStop(0, 'rgba(255, 255, 255, 0.08)');
      grad.addColorStop(0.5, 'rgba(0, 0, 0, 0.0)');
      grad.addColorStop(1, 'rgba(0, 0, 0, 0.25)');
      bakerCtx.fillStyle = grad;
      bakerCtx.fillRect(0, 0, 1024, 1024);

      // Vertical cloth ribbing
      bakerCtx.fillStyle = 'rgba(255, 255, 255, 0.015)';
      for (let x = 0; x < 1024; x += 8) {
        bakerCtx.fillRect(x, 0, 4, 1024);
      }

      // Placement on front chest:
      // UV center of chest is U = 0.75 (X = 768 on 1024w canvas).
      // UV V for T-shirt is 0.505 (Y ~ 510), for Hoodie is 0.451 (Y ~ 460).
      // U is mirrored relative to front viewer, so scale(-1, 1) around cx renders front-facing.
      const cx = 768;
      const cy = isHoodie ? 460 : 510;
      const maxDim = isHoodie ? 220 : 250;

      const finishBake = () => {
        // Render text if present
        const textStr = state.topMode === 'custom' ? state.customTop.text : (state.top.text || '');
        if (textStr && textStr.trim() !== '') {
          bakerCtx.save();
          bakerCtx.translate(cx, cy + (isHoodie ? 135 : 145));
          bakerCtx.scale(-1, 1);
          bakerCtx.font = '900 32px "Cinzel", "Montserrat", sans-serif';
          bakerCtx.textAlign = 'center';
          bakerCtx.textBaseline = 'middle';
          bakerCtx.fillStyle = state.topMode === 'custom' ? (state.customTop.textColor || '#ffffff') : '#ffffff';
          bakerCtx.shadowColor = 'rgba(0,0,0,0.8)';
          bakerCtx.shadowBlur = 8;
          bakerCtx.shadowOffsetY = 4;
          bakerCtx.fillText(textStr.toUpperCase(), 0, 0);
          bakerCtx.restore();
        }

        if (isHoodie) {
          if (!hoodieCanvasTexture) {
            hoodieCanvasTexture = new THREE.CanvasTexture(bakerCanvas);
            hoodieCanvasTexture.flipY = false;
            hoodieCanvasTexture.colorSpace = THREE.SRGBColorSpace;
          } else {
            hoodieCanvasTexture.needsUpdate = true;
          }
          targetMat.map = hoodieCanvasTexture;
          targetMat.color.set(0xffffff);
          targetMat.roughness = 0.85;
          targetMat.needsUpdate = true;
        } else {
          if (!shirtCanvasTexture) {
            shirtCanvasTexture = new THREE.CanvasTexture(bakerCanvas);
            shirtCanvasTexture.flipY = false;
            shirtCanvasTexture.colorSpace = THREE.SRGBColorSpace;
          } else {
            shirtCanvasTexture.needsUpdate = true;
          }
          targetMat.map = shirtCanvasTexture;
          targetMat.color.set(0xffffff);
          targetMat.roughness = 0.75;
          targetMat.needsUpdate = true;
        }
      };

      const activeImage = state.topMode === 'custom' ? state.customTop.customImage : null;
      const activePreset = state.topMode === 'custom' ? state.customTop.presetSvg : state.top.presetSvg;

      if (activeImage) {
        const aspect = (activeImage.naturalWidth || activeImage.width) / ((activeImage.naturalHeight || activeImage.height) || 1);
        let dw = maxDim;
        let dh = maxDim;
        if (aspect > 1) {
          dh = maxDim / aspect;
        } else {
          dw = maxDim * aspect;
        }
        bakerCtx.save();
        bakerCtx.translate(cx, cy);
        bakerCtx.scale(-1, 1);
        bakerCtx.drawImage(activeImage, -dw / 2, -dh / 2, dw, dh);
        bakerCtx.restore();
        finishBake();
      } else if (activePreset) {
        const pImg = new Image();
        pImg.crossOrigin = 'anonymous';
        pImg.onload = () => {
          const aspect = (pImg.naturalWidth || pImg.width) / ((pImg.naturalHeight || pImg.height) || 1);
          let dw = maxDim;
          let dh = maxDim;
          if (aspect > 1) {
            dh = maxDim / aspect;
          } else {
            dw = maxDim * aspect;
          }
          bakerCtx.save();
          bakerCtx.translate(cx, cy);
          bakerCtx.scale(-1, 1);
          bakerCtx.drawImage(pImg, -dw / 2, -dh / 2, dw, dh);
          bakerCtx.restore();
          finishBake();
        };
        pImg.onerror = () => {
          finishBake();
        };
        pImg.src = activePreset;
      } else {
        finishBake();
      }
    }

    function setApparelType(type) {
      state.apparelType = type;
      const isHoodie = type === 'hoodie';
      const tshirtMesh = characterModel ? characterModel.getObjectByName('Andri_TShirt') : null;
      const hoodieMesh = characterModel ? characterModel.getObjectByName('Andri_Hoodie') : null;
      const armsMesh = characterModel ? (characterModel.getObjectByName('Andri_Arms') || characterModel.getObjectByName('Andri_Forearms')) : null;
      const handsMesh = characterModel ? characterModel.getObjectByName('Andri_Hands') : null;

      if (tshirtMesh) tshirtMesh.visible = !isHoodie;
      if (hoodieMesh) hoodieMesh.visible = isHoodie;
      if (armsMesh) armsMesh.visible = !isHoodie;
      if (handsMesh) handsMesh.visible = isHoodie;

      bakeAndApplyApparel();
    }

    // Load Master 3D Model
    gltfLoader.load(
      'assets/models/andri_fullbody_rigged.glb?v=20261124_v5',
      (gltf) => {
        characterModel = gltf.scene;
        characterModel.position.set(0, -0.95, 0);
        characterModel.traverse((child) => {
          if (child.isMesh) {
            child.castShadow = true;
            child.receiveShadow = true;
            const mats = Array.isArray(child.material) ? child.material : [child.material];
            mats.forEach((m) => {
              if (m && m.name) {
                characterMaterials[m.name] = m;
              }
            });
          }
        });

        // Hide duplicate GLB pedestal if present so JS pedestal controls it cleanly
        const glbPed = characterModel.getObjectByName('Pedestal_Base');
        if (glbPed) glbPed.visible = false;
        const glbRing = characterModel.getObjectByName('Pedestal_Gold_Ring');
        if (glbRing) glbRing.visible = false;

        scene.add(characterModel);
        
        // Initial setup for apparel, pants, and shoes with URL param support
        const urlParams = new URLSearchParams(window.location.search);
        const initApparel = urlParams.get('apparel') || state.apparelType;
        const initCam = urlParams.get('cam');

        state.apparelType = initApparel;
        setApparelType(initApparel);
        applyBottomColor(state.bottom.color);
        applyShoeColor(state.shoes.color);

        if (initCam === 'portrait') {
          camera.position.set(0.0, 0.74, 0.82);
          controls.target.set(0.0, 0.74, 0.0);
          controls.update();
        } else if (initCam === 'chest') {
          camera.position.set(0.0, 0.32, 1.30);
          controls.target.set(0.0, 0.32, 0.0);
          controls.update();
        } else if (initCam === 'shoes') {
          camera.position.set(0.0, -0.70, 1.25);
          controls.target.set(0.0, -0.80, 0.0);
          controls.update();
        } else if (initCam === 'full') {
          camera.position.set(0.0, 0.05, 2.70);
          controls.target.set(0.0, -0.05, 0.0);
          controls.update();
        }
        
        dismissLoader();
      },
      (xhr) => {
        if (xhr.lengthComputable && xhr.total > 0) {
          const pct = Math.round((xhr.loaded / xhr.total) * 100);
          loadText.textContent = `Memuat Karakter 3D Andri (${pct}%)...`;
          const fill = document.getElementById('loadProgressFill');
          if (fill) fill.style.width = `${pct}%`;
        }
      },
      (error) => {
        console.error('Error loading character model:', error);
        loadText.textContent = 'Gagal memuat model 3D: ' + (error.message || 'Periksa file');
        setTimeout(dismissLoader, 1500);
      }
    );

    // Preload & Attach Tas Selempang
    gltfLoader.load(
      'assets/models/accessory_slingbag.glb',
      (gltf) => {
        const obj = gltf.scene;
        obj.position.set(0, -0.95, 0);
        obj.visible = false;
        obj.traverse((child) => {
          if (child.isMesh) {
            child.castShadow = true;
            child.receiveShadow = true;
          }
        });
        scene.add(obj);
        loadedAccessories['slingbag'] = obj;
      },
      undefined,
      (err) => {
        console.warn('Gagal memuat aksesoris tas selempang:', err);
      }
    );

    function applyBottomColor(hexColor) {
      const mat = characterMaterials['M_Andri_Jeans'];
      if (mat) {
        mat.color.set(hexColor);
        mat.needsUpdate = true;
      }
    }

    function applyShoeColor(hexColor) {
      const mat = characterMaterials['M_Andri_Shoe_Upper'];
      if (mat) {
        mat.color.set(hexColor);
        mat.needsUpdate = true;
      }
    }

    // Camera Animate Helper
    function tweenCamera(targetPos, targetLookAt) {
      const startPos = camera.position.clone();
      const startLook = controls.target.clone();
      const startTime = performance.now();
      const duration = 650;
      
      function animateCam(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1.0);
        const ease = progress < 0.5 ? 2 * progress * progress : -1 + (4 - 2 * progress) * progress;
        camera.position.lerpVectors(startPos, targetPos, ease);
        controls.target.lerpVectors(startLook, targetLookAt, ease);
        controls.update();
        if (progress < 1.0) {
          requestAnimationFrame(animateCam);
        }
      }
      requestAnimationFrame(animateCam);
    }

    // UI Event Listeners: Main Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.panel-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.dataset.tab;
        const targetPane = document.getElementById(target);
        if (targetPane) targetPane.classList.add('active');
      });
    });

    // Sub-Tabs: Official vs Custom
    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sub-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.dataset.sub;
        const targetPane = document.getElementById(target);
        if (targetPane) targetPane.classList.add('active');

        if (target === 'sub-official') {
          state.topMode = 'official';
          setApparelType(state.top.apparel || 'tshirt');
        } else if (target === 'sub-custom') {
          state.topMode = 'custom';
          setApparelType(state.customTop.type || 'tshirt');
        }
        updateSummary();
      });
    });

    // Sub-Custom: Apparel Model Pills (Kaos vs Hoodie)
    document.querySelectorAll('.type-pill-btn').forEach(pill => {
      pill.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.type-pill-btn').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        
        const type = pill.dataset.type;
        state.customTop.type = type;
        state.customTop.price = type === 'hoodie' ? 265000 : 135000;
        state.topMode = 'custom';
        setApparelType(type);
        updateSummary();
      });
    });

    // Item Selection: Tops, Bottoms, Shoes
    document.querySelectorAll('.item-card').forEach(card => {
      card.addEventListener('click', () => {
        AudioEngine.playClick();
        const slot = card.dataset.slot;
        
        document.querySelectorAll(`.item-card[data-slot="${slot}"]`).forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        if (slot === 'top') {
          const apparel = card.dataset.apparel || 'tshirt';
          state.topMode = 'official';
          state.top = {
            id: card.dataset.id,
            name: card.dataset.name,
            price: parseInt(card.dataset.price, 10),
            apparel: apparel,
            color: card.dataset.color || '#141416',
            presetSvg: card.dataset.preset || 'assets/img/presets/preset-logo-tnm.svg',
            text: card.dataset.text || ''
          };
          setApparelType(apparel);
        } else if (slot === 'bottom') {
          state.bottom = {
            id: card.dataset.id,
            name: card.dataset.name,
            price: parseInt(card.dataset.price, 10),
            color: card.dataset.color
          };
          applyBottomColor(state.bottom.color);
        } else if (slot === 'shoes') {
          state.shoes = {
            id: card.dataset.id,
            name: card.dataset.name,
            price: parseInt(card.dataset.price, 10),
            color: card.dataset.color
          };
          applyShoeColor(state.shoes.color);
        }

        updateSummary();
      });
    });

    // Customizer: Color Chips
    document.querySelectorAll('.color-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.color-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        state.customTop.baseColor = chip.dataset.color;
        state.topMode = 'custom';
        bakeAndApplyApparel();
        updateSummary();
      });
    });

    // Customizer: Preset Logos
    document.querySelectorAll('.preset-card').forEach(pCard => {
      pCard.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.preset-card').forEach(c => c.classList.remove('active'));
        pCard.classList.add('active');
        state.customTop.customImage = null;
        state.customTop.presetSvg = pCard.dataset.preset;
        state.customTop.presetName = pCard.dataset.name;
        state.topMode = 'custom';
        bakeAndApplyApparel();
        updateSummary();
      });
    });

    // Customizer: File Upload
    const decalInput = document.getElementById('customDecalInput');
    const uploadBox = document.getElementById('uploadBox');
    if (uploadBox && decalInput) {
      uploadBox.addEventListener('click', () => decalInput.click());
    }
    if (decalInput) {
      decalInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (file) {
          AudioEngine.playClick();
          const reader = new FileReader();
          reader.onload = (evt) => {
            const img = new Image();
            img.onload = () => {
              state.customTop.customImage = img;
              state.customTop.presetName = file.name.substring(0, 16);
              document.querySelectorAll('.preset-card').forEach(c => c.classList.remove('active'));
              state.topMode = 'custom';
              bakeAndApplyApparel();
              updateSummary();
            };
            img.src = evt.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }

    // Customizer: Text Decal
    const btnApplyText = document.getElementById('btnApplyText');
    const customTextVal = document.getElementById('customTextVal');
    if (btnApplyText && customTextVal) {
      btnApplyText.addEventListener('click', () => {
        AudioEngine.playClick();
        state.customTop.text = customTextVal.value.trim();
        state.topMode = 'custom';
        bakeAndApplyApparel();
        updateSummary();
      });
      customTextVal.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          btnApplyText.click();
        }
      });
    }

    // Accessory Toggles (Tas Selempang Tactical)
    document.querySelectorAll('.acc-row').forEach(row => {
      row.addEventListener('click', () => {
        AudioEngine.playClick();
        const key = row.dataset.acc;
        state.accessories[key] = !state.accessories[key];
        row.classList.toggle('active', state.accessories[key]);

        if (loadedAccessories[key]) {
          loadedAccessories[key].visible = state.accessories[key];
        }
        updateSummary();
      });
    });

    // Summary Updater
    function updateSummary() {
      const topName = state.topMode === 'custom'
        ? `${state.customTop.type === 'hoodie' ? 'Hoodie Kustom' : 'Kaos Kustom'}: ${state.customTop.presetName || 'Desain Sendiri'}`
        : state.top.name;
      const topPrice = state.topMode === 'custom'
        ? state.customTop.price
        : state.top.price;

      const bottomName = state.bottom.name.split(' ')[0];
      activeOutfitName.textContent = `${topName} + ${bottomName}`;

      let total = topPrice + state.bottom.price + state.shoes.price;
      if (state.accessories.slingbag) {
        total += 85000;
      }
      activeTotalPrice.textContent = `Rp ${total.toLocaleString('id-ID')}`;

      if (btnBuyMarketplace) {
        if (state.topMode === 'custom') {
          btnBuyMarketplace.textContent = 'Lanjut Cetak Sablon di Marketplace';
          btnBuyMarketplace.href = 'customizer.php';
        } else {
          btnBuyMarketplace.textContent = 'Beli Set Ini di Marketplace';
          btnBuyMarketplace.href = '<?php echo htmlspecialchars($shopUrl); ?>';
        }
      }
    }

    // Camera Presets
    document.querySelectorAll('.preset-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const camType = btn.dataset.cam;
        if (camType === 'full') {
          tweenCamera(new THREE.Vector3(0.0, 0.05, 2.70), new THREE.Vector3(0.0, -0.05, 0.0));
        } else if (camType === 'portrait') {
          tweenCamera(new THREE.Vector3(0.0, 0.74, 0.82), new THREE.Vector3(0.0, 0.74, 0.0));
        } else if (camType === 'chest') {
          tweenCamera(new THREE.Vector3(0.0, 0.32, 1.30), new THREE.Vector3(0.0, 0.32, 0.0));
        } else if (camType === 'shoes') {
          tweenCamera(new THREE.Vector3(0.0, -0.70, 1.25), new THREE.Vector3(0.0, -0.80, 0.0));
        }
      });
    });

    // Lighting Mode Switches
    document.querySelectorAll('.light-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        AudioEngine.playClick();
        document.querySelectorAll('.light-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');

        const mode = chip.dataset.light;
        state.lightMode = mode;

        if (mode === 'warm') {
          keyLight.color.set(0xfff3e0);
          keyLight.intensity = 2.2;
          fillLight.color.set(0xe0f2fe);
          rimLight.color.set(0xffeedd);
          goldRingMat.color.set(0xd63b29);
        } else if (mode === 'blue') {
          keyLight.color.set(0x06b6d4);
          keyLight.intensity = 1.8;
          fillLight.color.set(0x1e3a8a);
          rimLight.color.set(0x38bdf8);
          goldRingMat.color.set(0x06b6d4);
        } else if (mode === 'red') {
          keyLight.color.set(0xef4444);
          keyLight.intensity = 2.4;
          fillLight.color.set(0x450a0a);
          rimLight.color.set(0xf87171);
          goldRingMat.color.set(0xff2222);
        }
      });
    });

    // Audio Atmosphere Toggle
    btnAudioToggle.addEventListener('click', () => {
      AudioEngine.toggleAmbient((isPlaying) => {
        audioStatusText.textContent = isPlaying ? "Suara Malam: ON" : "Suara Malam: OFF";
        btnAudioToggle.style.borderColor = isPlaying ? "var(--red-ink)" : "var(--ring)";
      });
    });

    // Shutter Polaroid Snapshot
    btnShutter.addEventListener('click', () => {
      AudioEngine.playShutter();
      
      renderer.render(scene, camera);
      const dataUrl = canvas.toDataURL('image/png');

      polaroidImage.src = dataUrl;
      const topTitle = state.topMode === 'custom' 
        ? `${state.customTop.type === 'hoodie' ? 'Hoodie Kustom' : 'Kaos Kustom'}: ${state.customTop.presetName}` 
        : state.top.name;
      polaroidTitle.textContent = topTitle;
      
      const now = new Date();
      const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      polaroidDate.textContent = `Studio Malam • ${dateStr} • @andriTNM`;
      
      btnDownloadPolaroid.href = dataUrl;
      polaroidModal.classList.add('active');
    });

    btnCloseModal.addEventListener('click', () => {
      AudioEngine.playClick();
      polaroidModal.classList.remove('active');
    });

    // Window Resize Handler
    window.addEventListener('resize', () => {
      const { w, h } = getViewportDims();
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    });

    // Animation Loop
    function animate() {
      requestAnimationFrame(animate);
      controls.update();

      if (characterModel) {
        const time = Date.now() * 0.0015;
        const bob = Math.sin(time) * 0.003;
        characterModel.position.y = -0.95 + bob;
        if (loadedAccessories['slingbag']) {
          loadedAccessories['slingbag'].position.y = -0.95 + bob;
        }
      }

      renderer.render(scene, camera);
    }
    animate();
  </script>

</body>
</html>
