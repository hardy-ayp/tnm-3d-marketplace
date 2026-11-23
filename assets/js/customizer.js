/**
 * TNM Bespoke Studio — Realistic 2.5D Custom Apparel Designer
 * Free Drag & Transform Decal Engine with Strict 3D Model Silhouette Masking
 * True Fabric Draping, Safe Zone Clamping, Crease Multiply, and Instant Cart Integration.
 */

(function () {
  'use strict';

  // Base assets path (supports frontend 'assets/' and admin '../assets/')
  const ASSET_BASE = (typeof window.STUDIO_ASSET_BASE !== 'undefined') ? window.STUDIO_ASSET_BASE : 'assets/';

  // Global Toast fallback
  function showToast(msg, type = 'info') {
    if (typeof window.showToast === 'function') {
      window.showToast(msg, type);
      return;
    }
    let t = document.getElementById('studioToast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'studioToast';
      t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1a1712;color:#ece8dd;border:1px solid #d63b29;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:700;z-index:99999;box-shadow:0 10px 30px rgba(0,0,0,0.5);transition:opacity 0.3s;pointer-events:none;';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.opacity = '0'; }, 3000);
  }

  let autoUpdateAdminProductName = null;

  // State Management
  const state = {
    currentDraftId: null,
    model: 'tshirt',        // 'tshirt' or 'hoodie'
    fabric: 'combed24s',
    size: 'L',
    color: '#141414',
    colorName: 'Hitam Pekat',
    activeSide: 'front',    // 'front' or 'back'
    showGuidelines: true,
    prices: {
      tshirt: 135000,
      hoodie: 265000
    },
    fabricExtra: {
      combed24s: 0,
      heavy16s: 20000,
      fleece: 35000
    },
    // Coordinates as percentages of the garment viewport (0 to 100)
    frontDesign: {
      src: '',
      name: '',
      x: 50,         // Center X (percent)
      y: 35,         // Center Y (percent)
      width: 28,     // Width (percent of stage)
      rot: 0,        // Rotation (deg)
      aspectRatio: 1 // w / h
    },
    backDesign: {
      src: '',
      name: '',
      x: 50,
      y: 36,
      width: 32,
      rot: 0,
      aspectRatio: 1
    }
  };

  // Safe Printable Zone Boundaries (Sesuai kontur fisik model 3D)
  const SAFE_BOUNDS = {
    tshirt: {
      front: { minX: 28, maxX: 72, minY: 22, maxY: 72, maxW: 46, zoneTop: '20%', zoneLeft: '26%', zoneW: '48%', zoneH: '54%', label: 'Batas Cetak Dada Kaos' },
      back:  { minX: 26, maxX: 74, minY: 20, maxY: 74, maxW: 50, zoneTop: '18%', zoneLeft: '24%', zoneW: '52%', zoneH: '58%', label: 'Batas Cetak Punggung Kaos' }
    },
    hoodie: {
      front: { minX: 28, maxX: 72, minY: 26, maxY: 56, maxW: 40, zoneTop: '24%', zoneLeft: '26%', zoneW: '48%', zoneH: '34%', label: 'Batas Dada Hoodie (Di Atas Saku)' },
      back:  { minX: 26, maxX: 74, minY: 22, maxY: 72, maxW: 48, zoneTop: '20%', zoneLeft: '24%', zoneW: '52%', zoneH: '54%', label: 'Batas Cetak Punggung Hoodie' }
    }
  };

  // Preset Placements (Sesuai posisi ergonomis model 3D)
  const PRESET_COORDS = {
    tshirt: {
      'left-chest':   { x: 39, y: 30, width: 14, rot: 0 },
      'center-chest': { x: 50, y: 35, width: 28, rot: 0 },
      'full-front':   { x: 50, y: 44, width: 42, rot: 0 },
      'upper-back':   { x: 50, y: 28, width: 22, rot: 0 },
      'full-back':    { x: 50, y: 44, width: 46, rot: 0 }
    },
    hoodie: {
      'left-chest':   { x: 38, y: 34, width: 13, rot: 0 },
      'center-chest': { x: 50, y: 38, width: 26, rot: 0 }, // tepat di atas saku kanguru
      'full-front':   { x: 50, y: 40, width: 34, rot: 0 },
      'upper-back':   { x: 50, y: 32, width: 24, rot: 0 },
      'full-back':    { x: 50, y: 44, width: 44, rot: 0 }
    }
  };

  // DOM Elements
  const garmentColorLayer = document.getElementById('garmentColorLayer');
  const garmentBaseImg = document.getElementById('garmentBaseImg');
  const garmentCreaseImg = document.getElementById('garmentCreaseImg');
  const garmentSafeZone = document.getElementById('garmentSafeZone');
  const safeZoneBadge = document.getElementById('safeZoneBadge');
  const garmentMaskedGraphicLayer = document.getElementById('garmentMaskedGraphicLayer');
  const placedGraphicItem = document.getElementById('placedGraphicItem');
  const activePlacedImg = document.getElementById('activePlacedImg');
  const transformOverlayLayer = document.getElementById('transformOverlayLayer');
  const interactiveTransformFrame = document.getElementById('interactiveTransformFrame');

  const studioPrice = document.getElementById('studioPrice');
  const studioBottomPrice = document.getElementById('studioBottomPrice');
  const selectedSizeLabel = document.getElementById('selectedSizeLabel');
  const activeSideBadge = document.getElementById('activeSideBadge');

  const layerControlPanel = document.getElementById('layerControlPanel');
  const activeLayerTitle = document.getElementById('activeLayerTitle');
  const sliderScale = document.getElementById('sliderScale');
  const sliderPosY = document.getElementById('sliderPosY');
  const sliderPosX = document.getElementById('sliderPosX');
  const sliderRot = document.getElementById('sliderRot');
  const scaleValueDisplay = document.getElementById('scaleValueDisplay');
  const posYValueDisplay = document.getElementById('posYValueDisplay');
  const posXValueDisplay = document.getElementById('posXValueDisplay');
  const rotValueDisplay = document.getElementById('rotValueDisplay');

  // Drag & Transform Tracking Variables
  let isDragging = false;
  let activeHandle = null;
  let dragStartX = 0;
  let dragStartY = 0;
  let initBoxX = 0;
  let initBoxY = 0;
  let initBoxWidth = 0;

  // Initialize
  document.addEventListener('DOMContentLoaded', () => {
    updateGarmentVisuals();
    updateTransformBoxVisual();
    setupEventListeners();
    setupDirectInteraction();
    updatePrice();

    // If admin mode and no design loaded, auto-select active or first preset
    if (document.getElementById('builderPublishForm') && !state.frontDesign.src) {
      const defaultItem = document.querySelector('.preset-item.active') || document.querySelector('.preset-item');
      if (defaultItem) {
        defaultItem.classList.add('active');
        const src = defaultItem.getAttribute('data-src');
        const name = defaultItem.getAttribute('data-name');
        applyGraphicToActiveSide(src, name);
      }
    }
  });

  /* --------------------------------------------------------------------------
     1. Garment Visual Updates
     -------------------------------------------------------------------------- */
  function updateGarmentVisuals() {
    const mockupPath = `${ASSET_BASE}img/mockups/${state.model}-${state.activeSide}.png`;

    // 1. Color Silhouette Mask (Lapisan Warna Dasar)
    if (garmentColorLayer) {
      garmentColorLayer.style.backgroundColor = state.color;
      garmentColorLayer.style.maskImage = `url('${mockupPath}')`;
      garmentColorLayer.style.webkitMaskImage = `url('${mockupPath}')`;
    }

    // 2. STRICT 3D SILHOUETTE CLIPPING:
    // Mask the graphic layer so NO design can ever bleed outside the 3D model!
    if (garmentMaskedGraphicLayer) {
      garmentMaskedGraphicLayer.style.maskImage = `url('${mockupPath}')`;
      garmentMaskedGraphicLayer.style.webkitMaskImage = `url('${mockupPath}')`;
    }

    // 3. Photographic Base Shading & Top Crease Overlay
    if (garmentBaseImg) garmentBaseImg.src = mockupPath;
    if (garmentCreaseImg) garmentCreaseImg.src = mockupPath;

    // 4. Update Safe Printable Zone Dimensions
    const b = SAFE_BOUNDS[state.model]?.[state.activeSide];
    if (b && garmentSafeZone) {
      garmentSafeZone.style.top = b.zoneTop;
      garmentSafeZone.style.left = b.zoneLeft;
      garmentSafeZone.style.width = b.zoneW;
      garmentSafeZone.style.height = b.zoneH;
      if (safeZoneBadge) safeZoneBadge.textContent = b.label;
      garmentSafeZone.classList.toggle('hidden', !state.showGuidelines);
    }

    // 5. Update Preset Toolbar active buttons
    updatePresetToolbarHighlights();

    // 6. Update Graphic for active side
    updateTransformBoxVisual();
  }

  function updateTransformBoxVisual() {
    const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
    const hasDesign = !!cur.src;

    if (!placedGraphicItem || !interactiveTransformFrame || !activePlacedImg) return;

    if (hasDesign) {
      placedGraphicItem.style.display = 'flex';
      interactiveTransformFrame.style.display = 'block';
      activePlacedImg.src = cur.src;

      const stageW = transformOverlayLayer ? transformOverlayLayer.clientWidth : 480;
      const boxW = (cur.width / 100) * stageW;
      const boxH = boxW / (cur.aspectRatio || 1);

      // Position & Size placed graphic (inside masked layer)
      placedGraphicItem.style.width = `${boxW}px`;
      placedGraphicItem.style.height = `${boxH}px`;
      placedGraphicItem.style.left = `calc(${cur.x}% - ${boxW / 2}px)`;
      placedGraphicItem.style.top = `calc(${cur.y}% - ${boxH / 2}px)`;
      placedGraphicItem.style.transform = `rotate(${cur.rot}deg)`;

      // Position & Size interactive transform frame (overlaid directly above)
      interactiveTransformFrame.style.width = `${boxW}px`;
      interactiveTransformFrame.style.height = `${boxH}px`;
      interactiveTransformFrame.style.left = `calc(${cur.x}% - ${boxW / 2}px)`;
      interactiveTransformFrame.style.top = `calc(${cur.y}% - ${boxH / 2}px)`;
      interactiveTransformFrame.style.transform = `rotate(${cur.rot}deg)`;

      // Frame visibility
      interactiveTransformFrame.classList.toggle('hidden', !state.showGuidelines);
    } else {
      placedGraphicItem.style.display = 'none';
      interactiveTransformFrame.style.display = 'none';
      activePlacedImg.src = '';
    }

    // Update Side Panel Controls
    if (layerControlPanel) {
      layerControlPanel.classList.toggle('active', hasDesign);
    }

    if (activeLayerTitle) {
      activeLayerTitle.textContent = hasDesign
        ? `Desain: ${cur.name || 'Aktif'} (${state.activeSide === 'front' ? 'Depan' : 'Belakang'})`
        : 'Pengaturan Desain';
    }

    if (sliderScale) {
      sliderScale.value = Math.round(cur.width);
      if (scaleValueDisplay) scaleValueDisplay.textContent = Math.round(cur.width) + '%';
    }
    if (sliderPosY) {
      sliderPosY.value = Math.round(cur.y);
      if (posYValueDisplay) posYValueDisplay.textContent = Math.round(cur.y) + '%';
    }
    if (sliderPosX) {
      sliderPosX.value = Math.round(cur.x);
      if (posXValueDisplay) posXValueDisplay.textContent = Math.round(cur.x) + '%';
    }
    if (sliderRot) {
      sliderRot.value = Math.round(cur.rot);
      if (rotValueDisplay) rotValueDisplay.textContent = Math.round(cur.rot) + '°';
    }
  }

  function updatePresetToolbarHighlights() {
    const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
    const presets = PRESET_COORDS[state.model] || {};

    document.querySelectorAll('.btn-pos-preset').forEach(btn => {
      const pKey = btn.getAttribute('data-pos');
      const p = presets[pKey];
      if (!p) return;

      const isMatch = Math.abs(cur.x - p.x) < 3 && Math.abs(cur.y - p.y) < 3;
      btn.classList.toggle('active', isMatch);
    });
  }

  /* --------------------------------------------------------------------------
     2. Direct Interactive Mouse & Touch Dragging Engine with Boundary Clamping
     -------------------------------------------------------------------------- */
  function setupDirectInteraction() {
    if (!transformOverlayLayer || !interactiveTransformFrame) return;

    transformOverlayLayer.addEventListener('pointerdown', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      if (!cur.src) return;

      const handle = e.target.closest('.handle');
      if (handle) {
        activeHandle = handle.getAttribute('data-action');
        isDragging = false;
      } else if (e.target.closest('#interactiveTransformFrame')) {
        isDragging = true;
        activeHandle = null;
      } else {
        return;
      }

      e.preventDefault();
      transformOverlayLayer.setPointerCapture(e.pointerId);

      dragStartX = e.clientX;
      dragStartY = e.clientY;
      initBoxX = cur.x;
      initBoxY = cur.y;
      initBoxWidth = cur.width;
    });

    transformOverlayLayer.addEventListener('pointermove', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      if (!cur.src) return;

      const rect = transformOverlayLayer.getBoundingClientRect();
      const stageW = rect.width;
      const stageH = rect.height;

      // Get safe boundary limits for current model & side
      const bounds = SAFE_BOUNDS[state.model]?.[state.activeSide] || { minX: 25, maxX: 75, minY: 20, maxY: 75, maxW: 50 };

      if (isDragging) {
        // Move with strict garment boundaries
        const deltaX = ((e.clientX - dragStartX) / stageW) * 100;
        const deltaY = ((e.clientY - dragStartY) / stageH) * 100;

        cur.x = Math.max(bounds.minX, Math.min(bounds.maxX, initBoxX + deltaX));
        cur.y = Math.max(bounds.minY, Math.min(bounds.maxY, initBoxY + deltaY));

        updateTransformBoxVisual();
        updatePresetToolbarHighlights();
      } else if (activeHandle) {
        if (activeHandle === 'rotate') {
          // Rotate around center
          const boxRect = interactiveTransformFrame.getBoundingClientRect();
          const cx = boxRect.left + boxRect.width / 2;
          const cy = boxRect.top + boxRect.height / 2;
          const rad = Math.atan2(e.clientY - cy, e.clientX - cx);
          let deg = rad * (180 / Math.PI) + 90;
          if (deg > 180) deg -= 360;
          if (deg < -180) deg += 360;
          cur.rot = Math.round(deg);
          updateTransformBoxVisual();
        } else if (activeHandle.startsWith('resize')) {
          // Scale within safe maximum width
          const deltaX = Math.abs(e.clientX - dragStartX);
          const factor = e.clientX > dragStartX ? (1 + deltaX / 100) : (1 - deltaX / 100);
          const newW = initBoxWidth * factor;
          cur.width = Math.max(10, Math.min(bounds.maxW, newW));
          updateTransformBoxVisual();
        }
      }
    });

    const stopDrag = (e) => {
      if (isDragging || activeHandle) {
        isDragging = false;
        activeHandle = null;
        try {
          transformOverlayLayer.releasePointerCapture(e.pointerId);
        } catch (_) {}
      }
    };

    transformOverlayLayer.addEventListener('pointerup', stopDrag);
    transformOverlayLayer.addEventListener('pointercancel', stopDrag);
  }

  /* --------------------------------------------------------------------------
     3. Event Listeners & UI Controls
     -------------------------------------------------------------------------- */
  function setupEventListeners() {
    // 1. Toggle Side (Front / Back)
    const btnViewFront = document.getElementById('btnViewFront');
    const btnViewBack = document.getElementById('btnViewBack');
    const switchSideFront = document.getElementById('switchSideFront');
    const switchSideBack = document.getElementById('switchSideBack');

    function setActiveSide(side) {
      state.activeSide = side;
      btnViewFront?.classList.toggle('active', side === 'front');
      btnViewBack?.classList.toggle('active', side === 'back');
      switchSideFront?.classList.toggle('active', side === 'front');
      switchSideBack?.classList.toggle('active', side === 'back');

      if (activeSideBadge) {
        activeSideBadge.textContent = side === 'front' ? 'Tampak Depan' : 'Tampak Belakang';
      }

      // Quick flip animation
      const stage = document.getElementById('realisticStage');
      if (stage) {
        stage.classList.add('flipping');
        setTimeout(() => {
          updateGarmentVisuals();
          stage.classList.remove('flipping');
        }, 120);
      } else {
        updateGarmentVisuals();
      }
    }

    btnViewFront?.addEventListener('click', () => setActiveSide('front'));
    btnViewBack?.addEventListener('click', () => setActiveSide('back'));
    switchSideFront?.addEventListener('click', () => setActiveSide('front'));
    switchSideBack?.addEventListener('click', () => setActiveSide('back'));

    // 2. Toggle Frame Guidelines & Safe Zone
    const btnToggleGuidelines = document.getElementById('btnToggleGuidelines');
    btnToggleGuidelines?.addEventListener('click', () => {
      state.showGuidelines = !state.showGuidelines;
      btnToggleGuidelines.classList.toggle('active', state.showGuidelines);
      if (interactiveTransformFrame) {
        interactiveTransformFrame.classList.toggle('hidden', !state.showGuidelines);
      }
      if (garmentSafeZone) {
        garmentSafeZone.classList.toggle('hidden', !state.showGuidelines);
      }
    });

    // 3. Quick Placement Presets Buttons
    document.querySelectorAll('.btn-pos-preset').forEach(btn => {
      btn.addEventListener('click', () => {
        const posKey = btn.getAttribute('data-pos');
        const presets = PRESET_COORDS[state.model] || {};
        const target = presets[posKey];
        if (!target) return;

        // Auto switch side if preset belongs to opposite side
        if (posKey.includes('back') && state.activeSide !== 'back') {
          setActiveSide('back');
        } else if (!posKey.includes('back') && state.activeSide !== 'front') {
          setActiveSide('front');
        }

        const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
        cur.x = target.x;
        cur.y = target.y;
        cur.width = target.width;
        cur.rot = target.rot;

        updateTransformBoxVisual();
        updatePresetToolbarHighlights();

        if (window.showToast) {
          window.showToast(`Posisi "${btn.textContent.trim()}" diterapkan`, 'info');
        }
      });
    });

    // 4. Color Swatches
    document.querySelectorAll('.color-swatch').forEach(swatch => {
      swatch.addEventListener('click', () => {
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
        swatch.classList.add('active');

        state.color = swatch.getAttribute('data-color');
        state.colorName = swatch.getAttribute('data-name') || swatch.getAttribute('title') || 'Custom';
        if (garmentColorLayer) {
          garmentColorLayer.style.backgroundColor = state.color;
        }
        if (typeof autoUpdateAdminProductName === 'function') {
          autoUpdateAdminProductName();
        }
      });
    });

    const customPicker = document.getElementById('bCustomColorPicker');
    if (customPicker) {
      customPicker.addEventListener('input', (e) => {
        document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
        state.color = e.target.value;
        state.colorName = 'Custom (' + e.target.value + ')';
        if (garmentColorLayer) {
          garmentColorLayer.style.backgroundColor = state.color;
        }
        if (typeof autoUpdateAdminProductName === 'function') {
          autoUpdateAdminProductName();
        }
      });
    }

    // 5. Model Selection
    document.querySelectorAll('.model-card').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.model-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        state.model = card.getAttribute('data-model');
        
        // Auto select appropriate fabric default
        if (state.model === 'hoodie') {
          selectFabric('fleece');
        } else if (state.fabric === 'fleece') {
          selectFabric('combed24s');
        }

        updateGarmentVisuals();
        updatePrice();
        if (typeof autoUpdateAdminProductName === 'function') {
          autoUpdateAdminProductName();
        }
      });
    });

    // 6. Fabric Selection
    document.querySelectorAll('.fabric-item').forEach(item => {
      item.addEventListener('click', () => {
        selectFabric(item.getAttribute('data-fabric'));
      });
    });

    function selectFabric(fabKey) {
      state.fabric = fabKey;
      document.querySelectorAll('.fabric-item').forEach(f => {
        f.classList.toggle('active', f.getAttribute('data-fabric') === fabKey);
      });
      updatePrice();
    }

    // 7. Tabs Switching
    document.querySelectorAll('.studio-tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetTab = btn.getAttribute('data-tab');
        document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById(`tab-${targetTab}`)?.classList.add('active');
      });
    });

    // 8. Preset Designs Click
    document.querySelectorAll('.preset-item').forEach(item => {
      item.addEventListener('click', () => {
        document.querySelectorAll('.preset-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        const src = item.getAttribute('data-src');
        const name = item.getAttribute('data-name');
        applyGraphicToActiveSide(src, name);
      });
    });

    // 9. Custom Image Upload
    const customImageUpload = document.getElementById('customImageUpload');
    customImageUpload?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;

      if (file.size > 10 * 1024 * 1024) {
        if (window.showToast) window.showToast('Ukuran file maksimal 10 MB', 'error');
        return;
      }

      const reader = new FileReader();
      reader.onload = (evt) => {
        applyGraphicToActiveSide(evt.target.result, file.name);
      };
      reader.readAsDataURL(file);
    });

    // 10. Custom Text Generator
    const btnAddCustomText = document.getElementById('btnAddCustomText');
    const customTextInput = document.getElementById('customTextInput');
    const customTextColor = document.getElementById('customTextColor');
    let selectedFont = "'Archivo Black', sans-serif";

    document.querySelectorAll('.font-opt').forEach(opt => {
      opt.addEventListener('click', () => {
        document.querySelectorAll('.font-opt').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        selectedFont = opt.getAttribute('data-font');
      });
    });

    btnAddCustomText?.addEventListener('click', () => {
      const text = (customTextInput.value || '').trim();
      if (!text) {
        if (window.showToast) window.showToast('Ketik tulisan teks terlebih dahulu', 'warning');
        return;
      }

      const textColor = customTextColor.value || '#FFFFFF';
      const textImgData = renderTextToImage(text, selectedFont, textColor);
      applyGraphicToActiveSide(textImgData, `Teks "${text}"`, 1200 / 400);
    });

    // 11. Sliders Manual Fine-Tuning with Safe Bounds
    sliderScale?.addEventListener('input', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      const bounds = SAFE_BOUNDS[state.model]?.[state.activeSide] || { maxW: 50 };
      cur.width = Math.min(bounds.maxW, parseInt(e.target.value, 10));
      if (scaleValueDisplay) scaleValueDisplay.textContent = cur.width + '%';
      updateTransformBoxVisual();
    });

    sliderPosY?.addEventListener('input', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      const bounds = SAFE_BOUNDS[state.model]?.[state.activeSide] || { minY: 20, maxY: 75 };
      cur.y = Math.max(bounds.minY, Math.min(bounds.maxY, parseInt(e.target.value, 10)));
      if (posYValueDisplay) posYValueDisplay.textContent = cur.y + '%';
      updateTransformBoxVisual();
    });

    sliderPosX?.addEventListener('input', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      const bounds = SAFE_BOUNDS[state.model]?.[state.activeSide] || { minX: 25, maxX: 75 };
      cur.x = Math.max(bounds.minX, Math.min(bounds.maxX, parseInt(e.target.value, 10)));
      if (posXValueDisplay) posXValueDisplay.textContent = cur.x + '%';
      updateTransformBoxVisual();
    });

    sliderRot?.addEventListener('input', (e) => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      cur.rot = parseInt(e.target.value, 10);
      if (rotValueDisplay) rotValueDisplay.textContent = cur.rot + '°';
      updateTransformBoxVisual();
    });

    // Reset placement
    document.getElementById('btnResetPlacement')?.addEventListener('click', () => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      cur.x = 50;
      cur.y = state.model === 'hoodie' ? 38 : 35;
      cur.width = 28;
      cur.rot = 0;
      updateTransformBoxVisual();
      updatePresetToolbarHighlights();
      if (window.showToast) window.showToast('Posisi dipusatkan ke tengah', 'info');
    });

    // Delete active design
    document.getElementById('btnDeleteActiveLayer')?.addEventListener('click', () => {
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      cur.src = '';
      cur.name = '';
      cur.x = 50;
      cur.y = 35;
      cur.width = 28;
      cur.rot = 0;
      updateTransformBoxVisual();
      updatePrice();
      document.querySelectorAll('.preset-item').forEach(i => i.classList.remove('active'));
      if (typeof autoUpdateAdminProductName === 'function') {
        autoUpdateAdminProductName();
      }
      showToast(`Desain ${state.activeSide === 'front' ? 'Dada' : 'Punggung'} dihapus`, 'info');
    });

    // 12. Size Selection
    document.querySelectorAll('.size-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        document.querySelectorAll('.size-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        state.size = chip.getAttribute('data-size');
        updatePrice();
      });
    });

    // 13. Add to Cart Button
    document.getElementById('btnStudioAddToCart')?.addEventListener('click', handleAddToCart);

    // 14. Admin Publish Form Setup (if present)
    const publishForm = document.getElementById('builderPublishForm');
    if (publishForm) {
      const prodNameInput = document.getElementById('bProdName');
      const prodPriceInput = document.getElementById('bProdPrice');
      const prodOrigPriceInput = document.getElementById('bProdOriginalPrice');

      if (prodNameInput) {
        prodNameInput.addEventListener('input', () => {
          prodNameInput.dataset.userEdited = 'true';
        });
      }
      if (prodPriceInput) {
        prodPriceInput.addEventListener('input', () => {
          prodPriceInput.dataset.userEdited = 'true';
          const bp = document.getElementById('bottomBarPrice');
          if (bp) {
            const v = Number(prodPriceInput.value) || 0;
            bp.textContent = 'Rp ' + v.toLocaleString('id-ID');
          }
        });
      }
      if (prodOrigPriceInput) {
        prodOrigPriceInput.addEventListener('input', () => {
          prodOrigPriceInput.dataset.userEdited = 'true';
        });
      }

      autoUpdateAdminProductName = function () {
        if (prodNameInput && prodNameInput.dataset.userEdited !== 'true') {
          const modelLabel = state.model === 'hoodie' ? 'Hoodie Pullover' : 'Kaos Eksklusif';
          const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
          let artLabel = cur.name ? cur.name.replace(/^Grafis\s*/i, '').replace(/^Tipografi:\s*/i, '').replace(/^Stempel\s*/i, '') : 'Official Drop';
          prodNameInput.value = `${modelLabel} TNM ${artLabel} - ${state.colorName}`;
        }

        if (prodPriceInput && !prodPriceInput.dataset.userEdited) {
          prodPriceInput.value = state.model === 'hoodie' ? 265000 : 149000;
        }
        if (prodOrigPriceInput && !prodOrigPriceInput.dataset.userEdited) {
          prodOrigPriceInput.value = state.model === 'hoodie' ? 320000 : 199000;
        }
      };

      // Initial auto-fill
      autoUpdateAdminProductName();

      publishForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPublish');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<span>⏳ Memproses Snapshot Studio & Menerbitkan...</span>';
        }

        try {
          // Render side that has design (front if front has design, otherwise back or active)
          let sideToRender = state.activeSide;
          if (state.frontDesign.src) {
            sideToRender = 'front';
          } else if (state.backDesign.src) {
            sideToRender = 'back';
          }

          const snapshotBase64 = await generateCompositeSnapshot(sideToRender);

          const formData = new FormData();
          formData.append('action', 'save_apparel');
          formData.append('name', prodNameInput ? prodNameInput.value.trim() : '');
          formData.append('price', prodPriceInput ? prodPriceInput.value.trim() : '149000');
          formData.append('original_price', prodOrigPriceInput ? prodOrigPriceInput.value.trim() : '');
          formData.append('stock', document.getElementById('bProdStock')?.value.trim() || '100');
          formData.append('badge', document.getElementById('bProdBadge')?.value.trim() || '');
          formData.append('description', document.getElementById('bProdDesc')?.value.trim() || '');
          formData.append('is_featured', document.getElementById('bProdFeatured')?.checked ? '1' : '0');
          formData.append('image_snapshot', snapshotBase64);
          formData.append('csrf_token', window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

          const resp = await fetch('apparel-builder.php', {
            method: 'POST',
            headers: {
              'X-CSRF-Token': window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
          });

          const res = await resp.json();
          if (res.success) {
            const modal = document.getElementById('builderModalNotice');
            if (modal) {
              modal.style.display = 'flex';
              const viewBtn = document.getElementById('btnModalViewProduct');
              if (viewBtn && res.slug) {
                viewBtn.href = `../product.php?slug=${res.slug}`;
              }
            } else {
              window.location.href = res.redirect || 'products.php?msg=apparel_created';
            }
          } else {
            alert('Gagal: ' + (res.error || 'Terjadi kesalahan sistem'));
          }
        } catch (err) {
          console.error(err);
          alert('Terjadi kesalahan saat memproses gambar studio: ' + err.message);
        } finally {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
          }
        }
      });

      // 15. Draft Management & Quick Action Handlers
      let currentDraftsCache = window.INITIAL_DRAFTS || [];

      function renderDraftsGrid(drafts) {
        currentDraftsCache = drafts || [];
        const count = currentDraftsCache.length;
        const countBadge = document.getElementById('draftsCountBadge');
        if (countBadge) countBadge.textContent = count;
        const topCount = document.getElementById('topDraftCount');
        if (topCount) topCount.textContent = count;

        const container = document.getElementById('draftsListGrid');
        if (!container) return;

        if (count === 0) {
          container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 36px 20px; background: var(--shell); border: 1.5px dashed var(--ring); border-radius: var(--radius-md);">
              <div style="font-size: 32px; margin-bottom: 8px;"></div>
              <h4 style="font-size: 14.5px; font-weight: 800; color: var(--ink-bright); margin: 0 0 6px;">Belum Ada Draft Tersimpan</h4>
              <p style="font-size: 12.5px; color: var(--muted); max-width: 36ch; margin: 0 auto 16px; line-height: 1.5;">Simpan rancangan desainmu saat ini agar bisa dilanjutkan atau diterbitkan ke marketplace kapan saja.</p>
              <button type="button" class="btn sm red" id="btnEmptySaveDraft">+ Simpan Desain Saat Ini Sebagai Draft</button>
            </div>
          `;
          document.getElementById('btnEmptySaveDraft')?.addEventListener('click', () => handleSaveDraft(true));
          return;
        }

        container.innerHTML = currentDraftsCache.map(d => {
          const isActive = state.currentDraftId == d.id;
          const thumbSrc = d.preview_image ? `../${d.preview_image}` : `../assets/img/mockups/${d.model}-front.png`;
          const modelLabel = d.model === 'hoodie' ? 'Hoodie' : 'Kaos';
          const priceFormatted = 'Rp ' + Number(d.price || 149000).toLocaleString('id-ID');
          const timeFormatted = d.updated_at ? new Date(d.updated_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : 'Baru saja';

          return `
            <div class="draft-card ${isActive ? 'active-draft' : ''}" data-draft-id="${d.id}" style="background:var(--surface); border:1.5px solid ${isActive ? 'var(--red)' : 'var(--ring)'}; border-radius:var(--radius-sm); padding:12px; display:flex; flex-direction:column; gap:10px; transition:all 0.2s;">
              <div style="position:relative; width:100%; aspect-ratio:1; border-radius:6px; overflow:hidden; background:#12100e;">
                <img src="${thumbSrc}" alt="${d.title}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='../assets/img/mockups/tshirt-front.png'">
                ${isActive ? `<span style="position:absolute; top:6px; left:6px; background:var(--red); color:#fff; font-size:10px; font-weight:800; padding:2px 6px; border-radius:4px;">Aktif Diedit</span>` : ''}
              </div>
              <div style="flex:1;">
                <h4 style="font-size:13px; font-weight:800; color:var(--ink-bright); margin:0 0 4px; line-height:1.3; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;" title="${d.title}">${d.title}</h4>
                <div style="display:flex; align-items:center; gap:6px; font-size:11px; color:var(--muted); margin-bottom:4px;">
                  <span style="font-weight:700; color:var(--ink);">${modelLabel}</span>
                  <span>•</span>
                  <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:${d.color}; border:1px solid rgba(255,255,255,0.2);"></span>
                  <span>${d.color_name}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <span style="font-size:12px; font-weight:800; color:var(--red-ink);">${priceFormatted}</span>
                  <span style="font-size:10.5px; color:var(--faint);">${timeFormatted}</span>
                </div>
              </div>
              <div style="display:flex; gap:6px; padding-top:8px; border-top:1px solid var(--ring);">
                <button type="button" class="btn sm btn-load-draft" data-id="${d.id}" style="flex:1; justify-content:center; padding:6px 10px; font-size:11.5px; background:var(--shell); border:1px solid var(--ring); color:var(--ink); font-weight:700;"> Buka</button>
                <button type="button" class="btn sm ghost btn-delete-draft" data-id="${d.id}" title="Hapus Draft" style="padding:6px 10px; font-size:11.5px; color:#ef4444;">️</button>
              </div>
            </div>
          `;
        }).join('');
      }

      async function handleSaveDraft(asNew = false) {
        const btnSave = document.getElementById('btnQuickSaveDraft') || document.getElementById('btnSaveDraftInTab');
        const origText = btnSave ? btnSave.innerHTML : '';
        if (btnSave) {
          btnSave.disabled = true;
          btnSave.innerHTML = '<span>⏳ Menyimpan...</span>';
        }

        try {
          let sideToRender = state.activeSide;
          if (state.frontDesign.src) {
            sideToRender = 'front';
          } else if (state.backDesign.src) {
            sideToRender = 'back';
          }

          const snapshotBase64 = await generateCompositeSnapshot(sideToRender);

          const draftTitle = prodNameInput ? prodNameInput.value.trim() : 'Draft Desain Baju';
          const draftId = asNew ? '' : (state.currentDraftId || '');

          const formData = new FormData();
          formData.append('action', 'save_draft');
          if (draftId) formData.append('draft_id', draftId);
          formData.append('title', draftTitle);
          formData.append('model', state.model);
          formData.append('fabric', state.fabric);
          formData.append('color', state.color);
          formData.append('color_name', state.colorName);
          formData.append('front_design', JSON.stringify(state.frontDesign));
          formData.append('back_design', JSON.stringify(state.backDesign));
          formData.append('price', prodPriceInput ? prodPriceInput.value.trim() : '149000');
          formData.append('original_price', prodOrigPriceInput ? prodOrigPriceInput.value.trim() : '');
          formData.append('stock', document.getElementById('bProdStock')?.value.trim() || '100');
          formData.append('badge', document.getElementById('bProdBadge')?.value.trim() || '');
          formData.append('description', document.getElementById('bProdDesc')?.value.trim() || '');
          formData.append('is_featured', document.getElementById('bProdFeatured')?.checked ? '1' : '0');
          formData.append('image_snapshot', snapshotBase64);
          formData.append('csrf_token', window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

          const resp = await fetch('apparel-builder.php', {
            method: 'POST',
            headers: {
              'X-CSRF-Token': window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
          });

          const res = await resp.json();
          if (res.success) {
            state.currentDraftId = res.draft_id;
            renderDraftsGrid(res.drafts || []);
            showToast(`Draft "${draftTitle}" berhasil disimpan!`, 'success');
          } else {
            showToast(res.error || 'Gagal menyimpan draft', 'error');
          }
        } catch (err) {
          console.error(err);
          showToast('Terjadi kesalahan saat menyimpan draft', 'error');
        } finally {
          if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = origText;
          }
        }
      }

      function handleLoadDraft(draftId) {
        const d = currentDraftsCache.find(item => item.id == draftId);
        if (!d) return;

        state.currentDraftId = d.id;
        state.model = d.model || 'tshirt';
        state.fabric = d.fabric || 'combed24s';
        state.color = d.color || '#141414';
        state.colorName = d.color_name || 'Hitam Pekat';

        try {
          state.frontDesign = typeof d.front_design === 'string' ? JSON.parse(d.front_design) : (d.front_design || {});
        } catch (_) { state.frontDesign = {}; }

        try {
          state.backDesign = typeof d.back_design === 'string' ? JSON.parse(d.back_design) : (d.back_design || {});
        } catch (_) { state.backDesign = {}; }

        // Populate Form Fields
        if (prodNameInput) {
          prodNameInput.value = d.title || '';
          prodNameInput.dataset.userEdited = 'true';
        }
        if (prodPriceInput) {
          prodPriceInput.value = d.price || (state.model === 'hoodie' ? 265000 : 149000);
          prodPriceInput.dataset.userEdited = 'true';
        }
        if (prodOrigPriceInput) {
          prodOrigPriceInput.value = d.original_price || '';
          prodOrigPriceInput.dataset.userEdited = 'true';
        }
        const bStock = document.getElementById('bProdStock');
        if (bStock) bStock.value = d.stock || 100;
        const bBadge = document.getElementById('bProdBadge');
        if (bBadge) bBadge.value = d.badge || '';
        const bDesc = document.getElementById('bProdDesc');
        if (bDesc) bDesc.value = d.description || '';
        const bFeat = document.getElementById('bProdFeatured');
        if (bFeat) bFeat.checked = d.is_featured == 1;

        // Sync DOM visual controls
        document.querySelectorAll('.model-card').forEach(c => {
          c.classList.toggle('active', c.getAttribute('data-model') === state.model);
        });
        document.querySelectorAll('.fabric-item').forEach(f => {
          f.classList.toggle('active', f.getAttribute('data-fabric') === state.fabric);
        });
        document.querySelectorAll('.color-swatch').forEach(s => {
          s.classList.toggle('active', s.getAttribute('data-color') === state.color);
        });

        // Switch to model tab
        document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelector('.studio-tab-btn[data-tab="model"]')?.classList.add('active');
        document.getElementById('tab-model')?.classList.add('active');

        updateGarmentVisuals();
        updateTransformBoxVisual();
        updatePrice();
        renderDraftsGrid(currentDraftsCache);

        showToast(`Draft "${d.title}" berhasil dimuat ke studio!`, 'success');
      }

      async function handleDeleteDraft(draftId) {
        if (!confirm('Yakin ingin menghapus draft ini?')) return;

        try {
          const csrfToken = window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const formData = new FormData();
          formData.append('action', 'delete_draft');
          formData.append('draft_id', draftId);
          formData.append('csrf_token', csrfToken);

          const resp = await fetch('apparel-builder.php', {
            method: 'POST',
            headers: {
              'X-CSRF-Token': csrfToken
            },
            body: formData
          });

          const res = await resp.json();
          if (res.success) {
            if (state.currentDraftId == draftId) state.currentDraftId = null;
            renderDraftsGrid(res.drafts || []);
            showToast('Draft berhasil dihapus', 'info');
          } else {
            showToast(res.error || 'Gagal menghapus draft', 'error');
          }
        } catch (err) {
          console.error(err);
          showToast('Terjadi kesalahan saat menghapus draft', 'error');
        }
      }

      // Quick Actions Event Listeners
      document.getElementById('btnQuickOpenDetails')?.addEventListener('click', () => {
        document.querySelectorAll('.studio-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelector('.studio-tab-btn[data-tab="details"]')?.classList.add('active');
        document.getElementById('tab-details')?.classList.add('active');
        document.getElementById('bProdName')?.focus();
      });

      document.getElementById('btnQuickSaveDraft')?.addEventListener('click', () => handleSaveDraft(false));
      document.getElementById('btnSaveDraftInTab')?.addEventListener('click', () => handleSaveDraft(false));
      document.getElementById('btnSaveNewDraft')?.addEventListener('click', () => handleSaveDraft(true));

      // Delegated events for drafts list grid
      document.getElementById('draftsListGrid')?.addEventListener('click', (e) => {
        const loadBtn = e.target.closest('.btn-load-draft');
        if (loadBtn) {
          const id = loadBtn.getAttribute('data-id');
          handleLoadDraft(id);
          return;
        }

        const delBtn = e.target.closest('.btn-delete-draft');
        if (delBtn) {
          const id = delBtn.getAttribute('data-id');
          handleDeleteDraft(id);
          return;
        }
      });

      // Render initial drafts if passed from server
      if (window.INITIAL_DRAFTS) {
        renderDraftsGrid(window.INITIAL_DRAFTS);
      }
    }
  }

  /* --------------------------------------------------------------------------
     4. Graphic Handling & Image Aspect Calculation
     -------------------------------------------------------------------------- */
  function applyGraphicToActiveSide(src, name, explicitRatio) {
    const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
    cur.src = src;
    cur.name = name;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      cur.aspectRatio = explicitRatio || (img.width / img.height);
      updateTransformBoxVisual();
      updatePrice();

      if (typeof autoUpdateAdminProductName === 'function') {
        autoUpdateAdminProductName();
      }

      showToast(`Logo "${name}" berhasil ditempelkan di ${state.activeSide === 'front' ? 'Dada' : 'Punggung'}`, 'success');
    };
    img.src = src;
  }

  function renderTextToImage(text, font, color) {
    const canvas = document.createElement('canvas');
    canvas.width = 1200;
    canvas.height = 400;
    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.font = `bold 120px ${font}`;
    ctx.fillStyle = color;
    ctx.fillText(text, canvas.width / 2, canvas.height / 2);

    return canvas.toDataURL('image/png');
  }

  function updatePrice() {
    const base = state.prices[state.model] || 135000;
    const fabricExtra = state.fabricExtra[state.fabric] || 0;
    const hasFront = !!state.frontDesign.src;
    const hasBack = !!state.backDesign.src;
    const twoSidesExtra = (hasFront && hasBack) ? 25000 : 0;

    const total = base + fabricExtra + twoSidesExtra;
    const formatted = 'Rp ' + total.toLocaleString('id-ID');

    if (studioPrice) studioPrice.textContent = formatted;
    if (studioBottomPrice) studioBottomPrice.textContent = formatted;

    if (selectedSizeLabel) {
      const modelName = state.model === 'hoodie' ? 'Hoodie' : 'Kaos';
      const sideText = (hasFront && hasBack) ? 'Dua Sisi' : (hasFront ? 'Dada' : (hasBack ? 'Punggung' : 'Polos'));
      selectedSizeLabel.textContent = `${modelName} • ${state.colorName} • Size ${state.size} (${sideText})`;
    }

    // Refresh bottom bar spec and price summary (if present in Admin)
    const bottomSpec = document.getElementById('bottomBarSpecSummary');
    const bottomPrice = document.getElementById('bottomBarPrice');
    if (bottomSpec) {
      const modelName = state.model === 'hoodie' ? 'Hoodie Pullover' : 'Kaos Heavyweight';
      const cur = state.activeSide === 'front' ? state.frontDesign : state.backDesign;
      const artText = cur.name ? cur.name.replace(/^Grafis\s*/i, '').replace(/^Tipografi:\s*/i, '').replace(/^Stempel\s*/i, '') : 'Polos';
      bottomSpec.textContent = `${modelName} • ${state.colorName} • ${artText}`;
    }
    if (bottomPrice) {
      const prodPriceInput = document.getElementById('bProdPrice');
      const priceVal = prodPriceInput && prodPriceInput.value ? Number(prodPriceInput.value) : (state.model === 'hoodie' ? 265000 : 149000);
      bottomPrice.textContent = 'Rp ' + (priceVal || 149000).toLocaleString('id-ID');
    }

    return total;
  }

  /* --------------------------------------------------------------------------
     5. High-Resolution Composite Snapshot with Garment Silhouette Masking
     -------------------------------------------------------------------------- */
  async function handleAddToCart() {
    const btn = document.getElementById('btnStudioAddToCart');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="stage-spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:8px;"></span><span>Menyiapkan Mockup...</span>`;

    try {
      const snapshotBase64 = await generateCompositeSnapshot();

      const totalPrice = updatePrice();
      const modelName = state.model === 'hoodie' ? 'Hoodie Pullover' : 'Kaos Heavyweight';
      const fabricLabel = state.fabric === 'fleece' ? 'Cotton Fleece 330 Gsm' : (state.fabric === 'heavy16s' ? 'Cotton 16s Heavyweight' : 'Cotton Combed 24s');

      const payload = {
        action: 'add_custom',
        product_name: `Custom ${modelName} (${state.colorName})`,
        price: totalPrice,
        size: state.size,
        color: state.colorName,
        model: state.model,
        fabric: fabricLabel,
        snapshot: snapshotBase64,
        specs: JSON.stringify({
          model: state.model,
          fabric: state.fabric,
          color: state.color,
          colorName: state.colorName,
          size: state.size,
          frontDesign: state.frontDesign.name || null,
          backDesign: state.backDesign.name || null,
          twoSides: !!(state.frontDesign.src && state.backDesign.src)
        })
      };

      const csrfToken = window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await fetch('api/cart.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(Object.assign({}, payload, { csrf_token: csrfToken }))
      });

      const res = await resp.json();
      if (res.success) {
        showToast('Baju kustom berhasil ditambahkan ke keranjang!', 'success');
        if (window.refreshCartBadge) window.refreshCartBadge();
        if (window.openCartDrawer) window.openCartDrawer();
      } else {
        throw new Error(res.error || 'Gagal menyimpan ke keranjang');
      }
    } catch (err) {
      console.error(err);
      showToast(err.message || 'Terjadi kesalahan sistem', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = origHtml;
    }
  }

  function generateCompositeSnapshot(sideToRender = state.activeSide) {
    return new Promise((resolve) => {
      const canvas = document.createElement('canvas');
      canvas.width = 1000;
      canvas.height = 1000;
      const ctx = canvas.getContext('2d');

      const mockupPath = `${ASSET_BASE}img/mockups/${state.model}-${sideToRender}.png`;
      const baseImg = new Image();
      baseImg.crossOrigin = 'anonymous';

      baseImg.onload = () => {
        // A. Background Studio Tone
        ctx.fillStyle = '#141210';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const grad = ctx.createRadialGradient(500, 500, 100, 500, 500, 600);
        grad.addColorStop(0, 'rgba(255,255,255,0.06)');
        grad.addColorStop(1, 'rgba(0,0,0,0.65)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // B. Garment Silhouette with Selected Color
        const garmentCanvas = document.createElement('canvas');
        garmentCanvas.width = 1000;
        garmentCanvas.height = 1000;
        const gCtx = garmentCanvas.getContext('2d');

        gCtx.fillStyle = state.color;
        gCtx.fillRect(0, 0, 1000, 1000);
        gCtx.globalCompositeOperation = 'destination-in';
        gCtx.drawImage(baseImg, 0, 0, 1000, 1000);

        ctx.drawImage(garmentCanvas, 0, 0);

        // C. Photographic Base Shading with Multiply
        ctx.globalCompositeOperation = 'multiply';
        ctx.drawImage(baseImg, 0, 0, 1000, 1000);

        // D. Placed Graphic (CLIPPED TO EXACT GARMENT SILHOUETTE)
        ctx.globalCompositeOperation = 'source-over';
        const cur = sideToRender === 'front' ? state.frontDesign : state.backDesign;

        if (cur.src) {
          const gImg = new Image();
          gImg.crossOrigin = 'anonymous';
          gImg.onload = () => {
            // Draw graphic to a temporary sub-canvas
            const graphicCanvas = document.createElement('canvas');
            graphicCanvas.width = 1000;
            graphicCanvas.height = 1000;
            const grCtx = graphicCanvas.getContext('2d');

            const cx = (cur.x / 100) * 1000;
            const cy = (cur.y / 100) * 1000;
            const dw = (cur.width / 100) * 1000;
            const dh = dw / (cur.aspectRatio || 1);

            grCtx.save();
            grCtx.translate(cx, cy);
            if (cur.rot) {
              grCtx.rotate((cur.rot * Math.PI) / 180);
            }
            grCtx.drawImage(gImg, -dw / 2, -dh / 2, dw, dh);
            grCtx.restore();

            // Mask graphic canvas with garment silhouette alpha so no pixel escapes!
            grCtx.globalCompositeOperation = 'destination-in';
            grCtx.drawImage(baseImg, 0, 0, 1000, 1000);

            // Draw strictly clipped graphic to main canvas
            ctx.drawImage(graphicCanvas, 0, 0);

            // E. Top Crease Drape (Fabric Wrinkles over the Graphic)
            ctx.globalCompositeOperation = 'multiply';
            ctx.globalAlpha = 0.45;
            ctx.drawImage(baseImg, 0, 0, 1000, 1000);
            ctx.globalAlpha = 1.0;
            ctx.globalCompositeOperation = 'source-over';

            resolve(canvas.toDataURL('image/png', 0.92));
          };
          gImg.onerror = () => {
            resolve(canvas.toDataURL('image/png', 0.92));
          };
          gImg.src = cur.src;
        } else {
          ctx.globalCompositeOperation = 'multiply';
          ctx.globalAlpha = 0.45;
          ctx.drawImage(baseImg, 0, 0, 1000, 1000);
          ctx.globalAlpha = 1.0;
          ctx.globalCompositeOperation = 'source-over';
          resolve(canvas.toDataURL('image/png', 0.92));
        }
      };

      baseImg.onerror = () => {
        resolve(canvas.toDataURL('image/png', 0.92));
      };
      baseImg.src = mockupPath;
    });
  }

  // Expose studio API globally
  window.TNMStudio = {
    state,
    generateCompositeSnapshot,
    applyGraphicToActiveSide,
    updateGarmentVisuals,
    updateTransformBoxVisual
  };
})();
