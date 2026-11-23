<?php
/**
 * TNM Official Marketplace — Product Detail Page
 */
require_once __DIR__ . '/config.php';

$db = getDB();
$slug = trim($_GET['slug'] ?? '');
$id = intval($_GET['id'] ?? 0);

$product = null;
if (!empty($slug)) {
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: catalog.php');
    exit;
}

$pageTitle = $product['name'];
$categoryLabels = [
    'apparel'     => 'Pakaian & Apparel',
    'accessories' => 'Aksesoris Eksklusif',
    'collectibles'=> 'Koleksi Khusus'
];
$catLabel = $categoryLabels[$product['category']] ?? 'Merchandise';

// Fetch Related Products (same category, excluding current)
$stmtRel = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$stmtRel->execute([$product['category'], $product['id']]);
$relatedProducts = $stmtRel->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="wrap" style="padding-top: 32px; padding-bottom: 80px;">
  <!-- Breadcrumbs -->
  <nav class="breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--muted); margin-bottom: 28px; flex-wrap: wrap; line-height: 1.5;">
    <a href="index.php" style="color:var(--muted); text-decoration:none;">Shop</a>
    <span>/</span>
    <a href="catalog.php?cat=<?= e($product['category']) ?>" style="color:var(--muted); text-decoration:none;"><?= e($catLabel) ?></a>
    <span>/</span>
    <span style="color:var(--ink); font-weight:600;"><?= e($product['name']) ?></span>
  </nav>

  <style>
    @media (max-width: 860px) {
      .product-detail-layout {
        grid-template-columns: 1fr !important;
        gap: 24px !important;
      }
      .product-gallery-sticky {
        position: static !important;
      }
      .product-buy-actions {
        flex-direction: column !important;
      }
      .product-buy-actions .btn {
        width: 100% !important;
        min-width: 100% !important;
      }
    }
  </style>

  <!-- Product Detail Showcase Grid -->
  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap: 40px; align-items: start;" class="product-detail-layout">
    
    <!-- Left: Gallery Showcase -->
    <div class="product-gallery-sticky" style="position: sticky; top: 96px;">
      <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-lg); overflow: hidden; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <?php if (!empty($product['badge'])): ?>
          <span style="position:absolute; top: 16px; left: 16px; background: var(--red); color: #fff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 12px; border-radius: 999px; z-index: 2;">
            <?= e($product['badge']) ?>
          </span>
        <?php endif; ?>

        <div style="aspect-ratio: 1/1; display:flex; align-items:center; justify-content:center; padding: 24px; background: radial-gradient(circle at center, rgba(214,59,41,0.08) 0%, transparent 70%);">
          <img id="mainProductImg" src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 14px 28px rgba(0,0,0,0.5)); transition: transform 0.4s ease;">
        </div>

        <?php if ($product['is_customizable']): ?>
          <div style="background: var(--red-soft); border-top: 1px solid var(--ring); padding: 14px 18px; display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
            <div style="display:flex; align-items:center; gap: 10px;">
              <svg class="ic" style="color:var(--red-ink); width:18px; height:18px;"><use href="#ph-sparkle"></use></svg>
              <span style="font-size: 13px; font-weight: 800; color: var(--red-ink);">Baju ini bisa kamu kustom sesukamu!</span>
            </div>
            <a href="customizer.php?model=<?= str_contains(strtolower($product['name']), 'hoodie') ? 'hoodie' : 'tshirt' ?>" class="btn red sm" style="white-space:nowrap; padding: 6px 14px; font-size: 12px;">
              Kustom Sekarang
            </a>
          </div>
        <?php elseif ($product['category'] === 'apparel'): ?>
          <div style="background: rgba(46, 204, 113, 0.12); border-top: 1px solid rgba(46, 204, 113, 0.3); padding: 12px 18px; display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
            <div style="display:flex; align-items:center; gap: 8px;">
              <span style="font-size:12px; font-weight:800; color:#2ecc71;"> Baju Jadi Siap Kirim (Official Drop)</span>
            </div>
            <a href="customizer.php?model=<?= str_contains(strtolower($product['name']), 'hoodie') ? 'hoodie' : 'tshirt' ?>" style="font-size:12px; color:var(--red-ink); font-weight:700; text-decoration:none;">
              Mau kustom desain sendiri? Masuk Studio ↗
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: Information & Purchase Controls -->
    <div>
      <div style="display:flex; flex-direction:column; gap: 16px;">
        <span style="font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--red);">
          <?= $product['category'] === 'apparel' ? ($product['is_customizable'] ? ' Studio Kustom Baju' : ' Baju Jadi Resmi (Ready-to-Wear)') : e($catLabel) ?>
        </span>

        <h1 style="font-family: var(--font-display); font-size: clamp(24px, 3vw, 34px); font-weight: 800; line-height: 1.2; color: var(--ink); margin: 0; word-break: break-word; overflow-wrap: break-word;">
          <?= e($product['name']) ?>
        </h1>

        <!-- Price Section -->
        <div style="display:flex; align-items:baseline; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--ring); flex-wrap: wrap;">
          <span style="font-size: 26px; font-weight: 800; color: var(--ink);">
            <?= formatRupiah($product['price']) ?>
          </span>
          <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
            <span style="font-size: 15px; color: var(--faint); text-decoration: line-through;">
              <?= formatRupiah($product['original_price']) ?>
            </span>
            <span style="font-size: 11.5px; font-weight: 800; color: var(--red); background: var(--red-soft); padding: 3px 8px; border-radius: 6px;">
              Hemat <?= round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) ?>%
            </span>
          <?php endif; ?>
        </div>

        <!-- Stock Status -->
        <div style="display:flex; align-items:center; gap: 8px; font-size: 13px; color: #2ECC71;">
          <span style="width: 8px; height: 8px; background: #2ECC71; border-radius: 50%; display:inline-block;"></span>
          <span style="font-weight: 600;">Stok Tersedia (<?= $product['stock'] ?> unit siap kirim)</span>
        </div>

        <!-- Description -->
        <p style="font-size: 14px; line-height: 1.65; color: var(--muted); margin: 4px 0 14px; word-break: break-word;">
          <?= nl2br(e($product['description'])) ?>
        </p>

        <!-- Product Purchase Form -->
        <form id="productBuyForm" style="display:flex; flex-direction:column; gap: 20px; background: var(--surface); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--ring);">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

          <?php if ($product['category'] === 'apparel'): ?>
            <!-- Size Options -->
            <div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
                <label style="font-size: 13.5px; font-weight: 700; color: var(--ink);">Pilih Ukuran Baju</label>
                <a href="#sizeChartModal" onclick="event.preventDefault(); document.getElementById('sizeChartModal').style.display='flex';" style="font-size: 12px; color: var(--red); text-decoration:none; font-weight:600;">
                  Panduan Ukuran (Size Chart)
                </a>
              </div>
              <div style="display:flex; gap: 8px; flex-wrap: wrap;" id="sizeSelectorGroup">
                <?php foreach (['S', 'M', 'L', 'XL', '2XL', '3XL'] as $size): ?>
                  <label style="cursor:pointer; flex: 1; min-width: 44px;">
                    <input type="radio" name="size" value="<?= $size ?>" <?= $size === 'L' ? 'checked' : '' ?> style="display:none;" class="size-radio-input">
                    <span class="size-pill" style="display:flex; align-items:center; justify-content:center; width: 100%; height: 42px; border-radius: 8px; background: var(--shell); border: 1px solid var(--ring); font-size: 13px; font-weight: 700; color: var(--muted); transition: all 0.2s;">
                      <?= $size ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <input type="hidden" name="size" value="All Size">
          <?php endif; ?>

          <!-- Quantity Selector -->
          <div>
            <label style="font-size: 13.5px; font-weight: 700; color: var(--ink); margin-bottom: 10px; display:block;">Jumlah Pesanan</label>
            <div style="display:inline-flex; align-items:center; border: 1px solid var(--ring); border-radius: 8px; background: var(--shell); overflow:hidden;">
              <button type="button" id="btnQtyDec" style="width: 40px; height: 40px; border:0; background:transparent; color:var(--ink); font-size:18px; cursor:pointer;">−</button>
              <input type="number" name="qty" id="inputProductQty" value="1" min="1" max="<?= $product['stock'] ?>" style="width: 50px; text-align:center; background:transparent; border:0; color:var(--ink); font-size:15px; font-weight:700;">
              <button type="button" id="btnQtyInc" style="width: 40px; height: 40px; border:0; background:transparent; color:var(--ink); font-size:18px; cursor:pointer;">+</button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="product-buy-actions" style="display:flex; gap: 12px; flex-wrap: wrap; margin-top: 6px;">
            <button type="submit" class="btn red lg" id="btnAddToCartDetail" style="flex: 1; min-width: 180px; justify-content:center;">
              <svg class="ic"><use href="#ph-shopping-bag"></use></svg>
              <span>Tambah ke Keranjang</span>
            </button>

            <?php if ($product['is_customizable']): ?>
              <a href="customizer.php?model=<?= str_contains(strtolower($product['name']), 'hoodie') ? 'hoodie' : 'tshirt' ?>" class="btn lg ghost" style="flex: 1; min-width: 180px; justify-content:center; border-color: rgba(214,59,41,0.4); color: var(--red-ink);">
                <svg class="ic"><use href="#ph-sparkle"></use></svg>
                <span>Buka di Studio Kustom</span>
              </a>
            <?php endif; ?>
          </div>
        </form>

        <!-- Value Badges -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-top: 14px;">
          <div style="padding: 12px; background: var(--shell); border-radius: var(--radius-sm); border: 1px solid var(--ring); text-align:center;">
            <div style="width:30px; height:30px; border-radius:50%; background:rgba(46,204,113,0.15); color:#2ECC71; display:flex; align-items:center; justify-content:center; margin:0 auto 6px;">
              <svg class="ic" style="width:14px; height:14px;"><use href="#ph-check"></use></svg>
            </div>
            <span style="font-size: 11.5px; font-weight: 700; color: var(--ink); display:block;">100% Original</span>
            <span style="font-size: 11px; color: var(--faint);">Official Merch</span>
          </div>
          <div style="padding: 12px; background: var(--shell); border-radius: var(--radius-sm); border: 1px solid var(--ring); text-align:center;">
            <div style="width:30px; height:30px; border-radius:50%; background:var(--shell-hover); color:var(--ink); display:flex; align-items:center; justify-content:center; margin:0 auto 6px;">
              <svg class="ic" style="width:14px; height:14px;"><use href="#ph-shopping-bag"></use></svg>
            </div>
            <span style="font-size: 11.5px; font-weight: 700; color: var(--ink); display:block;">Kirim Se-Indonesia</span>
            <span style="font-size: 11px; color: var(--faint);">J&T, JNE, SiCepat</span>
          </div>
          <div style="padding: 12px; background: var(--shell); border-radius: var(--radius-sm); border: 1px solid var(--ring); text-align:center;">
            <div style="width:30px; height:30px; border-radius:50%; background:var(--red-soft); color:var(--red-ink); display:flex; align-items:center; justify-content:center; margin:0 auto 6px;">
              <svg class="ic" style="width:14px; height:14px;"><use href="#ph-sparkle"></use></svg>
            </div>
            <span style="font-size: 11.5px; font-weight: 700; color: var(--ink); display:block;">Bahan Premium</span>
            <span style="font-size: 11px; color: var(--faint);">Combed 24s / SUS 304</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Related Products Section -->
  <?php if (!empty($relatedProducts)): ?>
    <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--ring);">
      <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 24px;">
        <div>
          <span style="font-size: 12px; font-weight: 700; color: var(--red); text-transform: uppercase; letter-spacing: 0.05em;">Rekomendasi Lainnya</span>
          <h2 style="font-size: 24px; font-weight: 800; color: var(--ink); margin: 4px 0 0;">Produk Serupa untuk Koleksimu</h2>
        </div>
        <a href="catalog.php?cat=<?= e($product['category']) ?>" class="btn sm ghost">Lihat Semua</a>
      </div>

      <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
        <?php foreach ($relatedProducts as $rel): ?>
          <article class="pcard">
            <?php if (!empty($rel['badge'])): ?>
              <span class="pcard-badge"><?= e($rel['badge']) ?></span>
            <?php endif; ?>
            <a href="product.php?slug=<?= urlencode($rel['slug']) ?>" class="pcard-thumb-wrap">
              <img src="<?= e($rel['image']) ?>" alt="<?= e($rel['name']) ?>" class="pcard-thumb" loading="lazy">
            </a>
            <div class="pcard-body">
              <span class="pcard-cat"><?= e($rel['category']) ?></span>
              <h3 class="pcard-title">
                <a href="product.php?slug=<?= urlencode($rel['slug']) ?>"><?= e($rel['name']) ?></a>
              </h3>
              <div class="pcard-footer">
                <span class="pcard-price"><?= formatRupiah($rel['price']) ?></span>
                <a href="product.php?slug=<?= urlencode($rel['slug']) ?>" class="btn sm red" style="padding: 6px 12px; font-size: 12px;">Detail</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Panduan Ukuran (Size Chart) -->
<div id="sizeChartModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
  <div style="background:var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); max-width: 540px; width:100%; padding: 28px; position:relative; box-shadow: 0 24px 48px rgba(0,0,0,0.8);">
    <button type="button" onclick="document.getElementById('sizeChartModal').style.display='none';" style="position:absolute; top: 16px; right: 16px; background: transparent; border:0; color:var(--muted); font-size: 20px; cursor:pointer;"></button>
    <h3 style="font-size: 20px; font-weight: 800; color: var(--ink); margin-top:0; margin-bottom: 12px;">Panduan Ukuran (Size Chart) TNM</h3>
    <p style="font-size: 13.5px; color: var(--muted); margin-bottom: 20px;">Standar ukuran kaos dan hoodie lokal Indonesia (toleransi ± 1 - 2 cm).</p>
    
    <table style="width:100%; border-collapse: collapse; font-size: 13.5px; text-align:left;">
      <thead>
        <tr style="border-bottom: 1px solid var(--ring); color: var(--ink);">
          <th style="padding: 10px 8px;">Ukuran</th>
          <th style="padding: 10px 8px;">Lebar Dada (cm)</th>
          <th style="padding: 10px 8px;">Panjang Badan (cm)</th>
          <th style="padding: 10px 8px;">Estimasi BB (kg)</th>
        </tr>
      </thead>
      <tbody style="color: var(--muted);">
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);"><td style="padding: 8px; font-weight:700; color:var(--ink);">S</td><td style="padding: 8px;">48</td><td style="padding: 8px;">68</td><td style="padding: 8px;">45 - 55 kg</td></tr>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);"><td style="padding: 8px; font-weight:700; color:var(--ink);">M</td><td style="padding: 8px;">50</td><td style="padding: 8px;">70</td><td style="padding: 8px;">55 - 65 kg</td></tr>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);"><td style="padding: 8px; font-weight:700; color:var(--ink);">L</td><td style="padding: 8px;">53</td><td style="padding: 8px;">73</td><td style="padding: 8px;">65 - 75 kg</td></tr>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);"><td style="padding: 8px; font-weight:700; color:var(--ink);">XL</td><td style="padding: 8px;">56</td><td style="padding: 8px;">75</td><td style="padding: 8px;">75 - 85 kg</td></tr>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);"><td style="padding: 8px; font-weight:700; color:var(--ink);">2XL</td><td style="padding: 8px;">59</td><td style="padding: 8px;">77</td><td style="padding: 8px;">85 - 95 kg</td></tr>
        <tr><td style="padding: 8px; font-weight:700; color:var(--ink);">3XL</td><td style="padding: 8px;">62</td><td style="padding: 8px;">79</td><td style="padding: 8px;">95 - 110 kg</td></tr>
      </tbody>
    </table>

    <div style="margin-top: 24px; text-align:right;">
      <button type="button" class="btn sm red" onclick="document.getElementById('sizeChartModal').style.display='none';">Tutup Panduan</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Size selection highlight
  const sizeRadios = document.querySelectorAll('.size-radio-input');
  function updateSizePills() {
    sizeRadios.forEach(radio => {
      const pill = radio.parentElement.querySelector('.size-pill');
      if (radio.checked) {
        pill.style.background = 'var(--red)';
        pill.style.color = '#fff';
        pill.style.borderColor = 'var(--red)';
      } else {
        pill.style.background = 'var(--shell)';
        pill.style.color = 'var(--muted)';
        pill.style.borderColor = 'var(--ring)';
      }
    });
  }
  sizeRadios.forEach(r => r.addEventListener('change', updateSizePills));
  updateSizePills();

  // Qty Increment/Decrement
  const qtyInput = document.getElementById('inputProductQty');
  const btnInc = document.getElementById('btnQtyInc');
  const btnDec = document.getElementById('btnQtyDec');

  if (btnInc && qtyInput) {
    btnInc.addEventListener('click', () => {
      let max = parseInt(qtyInput.getAttribute('max')) || 999;
      let val = parseInt(qtyInput.value) || 1;
      if (val < max) qtyInput.value = val + 1;
    });
  }

  if (btnDec && qtyInput) {
    btnDec.addEventListener('click', () => {
      let val = parseInt(qtyInput.value) || 1;
      if (val > 1) qtyInput.value = val - 1;
    });
  }

  // Handle Add to Cart submission
  const form = document.getElementById('productBuyForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btnAddToCartDetail');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span>Menambahkan...</span>';

      const formData = new FormData(form);
      const csrfToken = window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const payload = {
        action: 'add_product',
        product_id: formData.get('product_id'),
        size: formData.get('size'),
        qty: parseInt(formData.get('qty')) || 1,
        csrf_token: csrfToken
      };

      try {
        const res = await fetch('api/cart.php', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
          if (window.TnmToast) window.TnmToast.show('Produk berhasil masuk ke keranjang!');
          if (window.TnmCart) {
            await window.TnmCart.refresh();
            window.TnmCart.openDrawer();
          }
        } else {
          alert(data.message || 'Gagal menambahkan produk');
        }
      } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
