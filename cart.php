<?php
/**
 * TNM Official Marketplace — Dedicated Cart Page
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Keranjang Belanja';
$cart = getCart();
$subtotal = getCartTotal();
$shippingEst = empty($cart) ? 0 : 15000;
$grandTotal = $subtotal + $shippingEst;

include __DIR__ . '/includes/header.php';
?>

<div class="wrap" style="padding-top: 36px; padding-bottom: 80px;">
  <div style="margin-bottom: 28px;">
    <h1 style="font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 40px); font-weight: 800; color: var(--ink); margin: 0 0 8px;">
      Keranjang Belanja
    </h1>
    <p style="color: var(--muted); font-size: 14.5px; margin: 0;">
      Periksa kembali produk merchandise dan pesanan custom baju pilihanmu sebelum melanjutkan pembayaran.
    </p>
  </div>

  <?php if (empty($cart)): ?>
    <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-lg); padding: 60px 24px; text-align: center; max-width: 600px; margin: 40px auto;">
      <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--shell); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; color: var(--faint);">
        <svg class="ic" style="width:36px; height:36px;"><use href="#ph-shopping-bag"></use></svg>
      </div>
      <h2 style="font-size: 22px; font-weight: 800; color: var(--ink); margin: 0 0 10px;">Keranjangmu Masih Kosong</h2>
      <p style="color: var(--muted); font-size: 14px; max-width: 40ch; margin: 0 auto 28px; line-height: 1.6;">
        Belum ada merchandise atau baju kustom yang kamu masukkan. Mulai eksplorasi koleksi resmi TNM sekarang!
      </p>
      <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="catalog.php" class="btn red">Jelajahi Katalog</a>
        <a href="customizer.php" class="btn ghost">Kustom Baju Sendiri</a>
      </div>
    </div>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr; gap: 32px;" class="cart-layout-grid">
      <style>
        @media (min-width: 992px) {
          .cart-layout-grid {
            grid-template-columns: 1.6fr 1fr !important;
          }
        }
        @media (max-width: 640px) {
          .cart-item-row {
            flex-direction: row !important;
            align-items: flex-start !important;
            gap: 12px !important;
            padding: 14px 12px !important;
          }
          .cart-item-thumb-box {
            width: 68px !important;
            height: 68px !important;
            flex-shrink: 0 !important;
          }
          .cart-summary-box {
            padding: 20px 16px !important;
            position: static !important;
          }
        }
      </style>

      <!-- Left: Items Table / Cards -->
      <div style="display:flex; flex-direction:column; gap: 16px;">
        <?php foreach ($cart as $key => $item): ?>
          <div class="cart-item-row" data-key="<?= e($key) ?>" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 18px 20px; display:flex; gap: 18px; align-items:center; position:relative;">
            <!-- Thumbnail (3D Model Realistis) -->
            <div class="cart-item-thumb-box" style="width: 86px; height: 86px; border-radius: var(--radius-sm); background: var(--shell); border: 1px solid var(--ring); overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
              <img src="<?= e(getCartItemThumbnail($item)) ?>" alt="<?= e($item['product_name']) ?>" style="width:100%; height:100%; object-fit:contain;">
            </div>

            <!-- Details -->
            <div style="flex:1; min-width:0; width:100%;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; gap: 12px; margin-bottom: 6px;">
                <h3 style="font-size: 15.5px; font-weight: 800; color: var(--ink); margin: 0; line-height: 1.35; word-break: break-word;">
                  <?= e($item['product_name']) ?>
                </h3>
                <button type="button" class="btn-remove-item" data-key="<?= e($key) ?>" style="background:transparent; border:0; color:var(--faint); cursor:pointer; padding:4px;" title="Hapus Produk">
                  <svg class="ic" viewBox="0 0 256 256" style="width:18px;height:18px;"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Z"></path></svg>
                </button>
              </div>

              <!-- Badges/Specs -->
              <div style="display:flex; gap: 8px; flex-wrap:wrap; font-size: 12px; color: var(--muted); margin-bottom: 12px;">
                <span style="background:var(--shell); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--ring);">
                  Ukuran: <b><?= e($item['size'] ?? 'L') ?></b>
                </span>
                <?php if (!empty($item['is_custom'])): ?>
                  <span style="background: rgba(214,59,41,0.15); color: var(--red-ink); padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                    Kustom Desain Sendiri
                  </span>
                  <?php if (!empty($item['color'])): ?>
                    <span style="display:inline-flex; align-items:center; gap: 4px; background:var(--shell); padding: 2px 8px; border-radius: 4px;">
                      Warna: <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= e($item['color']) ?>; border:1px solid #666;"></span>
                    </span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>

              <!-- Price & Quantity -->
              <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 12px;">
                <span style="font-size: 16px; font-weight: 800; color: var(--ink);">
                  <?= formatRupiah($item['price'] * $item['qty']) ?>
                  <?php if ($item['qty'] > 1): ?>
                    <span style="font-size: 12px; color: var(--faint); font-weight: 500;">(@ <?= formatRupiah($item['price']) ?>)</span>
                  <?php endif; ?>
                </span>

                <!-- Stepper -->
                <div class="qty-stepper" style="margin:0;">
                  <button type="button" class="qty-btn" onclick="updateItemQuantity('<?= e($key) ?>', <?= $item['qty'] - 1 ?>)">−</button>
                  <span class="qty-val"><?= $item['qty'] ?></span>
                  <button type="button" class="qty-btn" onclick="updateItemQuantity('<?= e($key) ?>', <?= $item['qty'] + 1 ?>)">+</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 12px;">
          <a href="catalog.php" class="btn sm ghost" style="display:inline-flex; align-items:center; gap: 6px;">
            ← Lanjut Belanja
          </a>
          <button type="button" onclick="clearFullCart()" class="btn sm ghost" style="color:var(--faint);">
            Kosongkan Keranjang
          </button>
        </div>
      </div>

      <!-- Right: Summary & Checkout Card -->
      <div>
        <div class="cart-summary-box" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 28px; position: sticky; top: 96px;">
          <h2 style="font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--ring);">
            Ringkasan Belanja
          </h2>

          <div style="display:flex; flex-direction:column; gap: 14px; font-size: 14px; margin-bottom: 24px;">
            <div style="display:flex; justify-content:space-between; color: var(--muted);">
              <span>Total Harga (<?= getCartCount() ?> barang)</span>
              <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($subtotal) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; color: var(--muted);">
              <span>Estimasi Ongkos Kirim</span>
              <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($shippingEst) ?></span>
            </div>
            <div style="height: 1px; background: var(--ring); margin: 4px 0;"></div>
            <div style="display:flex; justify-content:space-between; font-size: 16px; font-weight: 800;">
              <span style="color: var(--ink);">Total Tagihan</span>
              <span style="color: var(--red-ink); font-size: 18px;"><?= formatRupiah($grandTotal) ?></span>
            </div>
          </div>

          <a href="checkout.php" class="btn red lg" style="width: 100%; justify-content: center; margin-bottom: 12px;">
            <span>Lanjut ke Checkout</span>
            <svg class="ic"><use href="#ph-arrow-right"></use></svg>
          </a>

          <p style="font-size: 12px; color: var(--faint); text-align: center; margin: 0; line-height: 1.5; display:flex; align-items:center; justify-content:center; gap:6px;">
            <svg class="ic" style="width:13px;height:13px;color:var(--faint);"><use href="#ph-check"></use></svg>
            <span>Pembayaran aman via Transfer Bank & QRIS setelah konfirmasi admin via WhatsApp.</span>
          </p>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function getCartCsrfToken() {
  return window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function updateItemQuantity(key, newQty) {
  try {
    const token = getCartCsrfToken();
    const res = await fetch('api/cart.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
      },
      body: JSON.stringify({ action: 'update', cart_key: key, qty: newQty, csrf_token: token })
    });
    const data = await res.json();
    if (data.success) {
      location.reload();
    }
  } catch (err) {
    console.error(err);
  }
}

async function clearFullCart() {
  if (!confirm('Yakin ingin mengosongkan seluruh keranjang belanja?')) return;
  try {
    const token = getCartCsrfToken();
    const res = await fetch('api/cart.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
      },
      body: JSON.stringify({ action: 'clear', csrf_token: token })
    });
    const data = await res.json();
    if (data.success) {
      location.reload();
    }
  } catch (err) {
    console.error(err);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-remove-item').forEach(btn => {
    btn.addEventListener('click', async () => {
      const key = btn.dataset.key;
      if (!confirm('Hapus item ini dari keranjang?')) return;
      try {
        const token = getCartCsrfToken();
        const res = await fetch('api/cart.php', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
          },
          body: JSON.stringify({ action: 'remove', cart_key: key, csrf_token: token })
        });
        const data = await res.json();
        if (data.success) {
          location.reload();
        }
      } catch (err) {
        console.error(err);
      }
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
