<?php
/**
 * TNM Official Marketplace — Checkout Page
 */
require_once __DIR__ . '/config.php';

$cart = getCart();
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Checkout Pesanan';
$subtotal = getCartTotal();
$shippingFee = 15000;
$grandTotal = $subtotal + $shippingFee;

include __DIR__ . '/includes/header.php';
?>

<div class="wrap" style="padding-top: 36px; padding-bottom: 80px;">
  <!-- Header -->
  <div style="margin-bottom: 32px;">
    <h1 style="font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 40px); font-weight: 800; color: var(--ink); margin: 0 0 8px;">
      Checkout Pesanan
    </h1>
    <p style="color: var(--muted); font-size: 14.5px; margin: 0;">
      Lengkapi data pengiriman dan konfirmasi pesananmu untuk diproses oleh tim TNM Merchandise.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: 1fr; gap: 36px;" class="checkout-grid">
    <style>
      @media (min-width: 992px) {
        .checkout-grid {
          grid-template-columns: 1.4fr 1fr !important;
        }
      }
      .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
      }
      .form-group label {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--ink);
      }
      .form-control {
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--ring);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        color: var(--ink);
        font-family: var(--font-display);
        font-size: 15px;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
      }
      .form-control:focus {
        border-color: var(--red);
      }
      @media (max-width: 768px) {
        .form-control {
          font-size: 16px; /* Prevents iOS Safari auto-zoom */
        }
        .checkout-summary-sticky {
          position: static !important;
        }
        .checkout-card-box {
          padding: 18px 16px !important;
        }
      }
    </style>

    <!-- Left: Shipping & Customer Form -->
    <div>
      <form id="checkoutForm" style="display:flex; flex-direction:column; gap: 24px;">
        
        <!-- Section: Identitas Pembeli -->
        <div class="checkout-card-box" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
          <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 18px; display:flex; align-items:center; gap: 10px;">
            <span style="width:24px; height:24px; border-radius:50%; background:var(--red); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:12px;">1</span>
            Data Penerima Paket
          </h2>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            <div class="form-group">
              <label for="custName">Nama Lengkap Penerima <span style="color:var(--red);">*</span></label>
              <input type="text" id="custName" name="name" class="form-control" placeholder="Contoh: Andri Pratama" required>
            </div>

            <div class="form-group">
              <label for="custPhone">Nomor WhatsApp Aktif <span style="color:var(--red);">*</span></label>
              <input type="tel" id="custPhone" name="phone" class="form-control" placeholder="081234567890" required>
              <span style="font-size: 11.5px; color: var(--faint);">Invoice & no. resi pengiriman akan dikirim via WhatsApp</span>
            </div>
          </div>

          <div class="form-group" style="margin-top: 16px;">
            <label for="custEmail">Alamat Email (Opsional)</label>
            <input type="email" id="custEmail" name="email" class="form-control" placeholder="nama@email.com">
          </div>
        </div>

        <!-- Section: Alamat Pengiriman -->
        <div class="checkout-card-box" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
          <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 18px; display:flex; align-items:center; gap: 10px;">
            <span style="width:24px; height:24px; border-radius:50%; background:var(--red); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:12px;">2</span>
            Alamat Pengiriman
          </h2>

          <div class="form-group">
            <label for="custAddress">Alamat Lengkap <span style="color:var(--red);">*</span></label>
            <textarea id="custAddress" name="address" rows="3" class="form-control" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan" required></textarea>
          </div>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
            <div class="form-group">
              <label for="custCity">Kota / Kabupaten & Provinsi <span style="color:var(--red);">*</span></label>
              <input type="text" id="custCity" name="city" class="form-control" placeholder="Contoh: Jakarta Selatan, DKI Jakarta" required>
            </div>

            <div class="form-group">
              <label for="custPostal">Kode Pos</label>
              <input type="text" id="custPostal" name="postal" class="form-control" placeholder="12345">
            </div>
          </div>

          <!-- Pilihan Kurir -->
          <div class="form-group" style="margin-top: 16px;">
            <label>Pilihan Jasa Ekspedisi (Flat Rate Rp 15.000 Seluruh P. Jawa / Reguler)</label>
            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 6px;">
              <?php foreach (['J&T Express', 'JNE Reguler', 'SiCepat', 'Anteraja'] as $cIndex => $courier): ?>
                <label style="cursor:pointer;">
                  <input type="radio" name="courier" value="<?= $courier ?>" <?= $cIndex === 0 ? 'checked' : '' ?> style="display:none;" class="courier-radio">
                  <div class="courier-card" style="padding: 12px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); text-align:center; transition:all 0.2s;">
                    <span style="display:block; font-size:13px; font-weight:700; color:var(--ink);"><?= $courier ?></span>
                    <span style="font-size:11px; color:var(--faint);">Estimasi 2-4 Hari</span>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group" style="margin-top: 16px;">
            <label for="orderNotes">Catatan untuk Penjual / Kurir (Opsional)</label>
            <input type="text" id="orderNotes" name="notes" class="form-control" placeholder="Contoh: Titipkan ke satpam jika tidak ada orang di rumah">
          </div>
        </div>

        <!-- Section: Metode Pembayaran -->
        <div class="checkout-card-box" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
          <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 14px; display:flex; align-items:center; gap: 10px;">
            <span style="width:24px; height:24px; border-radius:50%; background:var(--red); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:12px;">3</span>
            Metode Pembayaran
          </h2>
          <div style="padding: 16px; background: var(--shell); border-radius: var(--radius-sm); border: 1px solid var(--ring);">
            <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 8px;">
              <span style="width:28px; height:28px; border-radius:50%; background:var(--red-soft); color:var(--red-ink); display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg class="ic" style="width:14px; height:14px;"><use href="#ph-shopping-bag"></use></svg>
              </span>
              <span style="font-size: 14px; font-weight: 700; color: var(--ink);">Transfer Bank / QRIS Otomatis & Terverifikasi</span>
            </div>
            <p style="font-size: 13px; color: var(--muted); margin: 0; line-height: 1.5;">
              Setelah menekan tombol Buat Pesanan di bawah, sistem akan menerbitkan nomor invoice resmi dan mengarahkanmu ke WhatsApp Official TNM untuk konfirmasi instan dan penerimaan instruksi transfer (BCA / Mandiri / QRIS Semua E-Wallet).
            </p>
          </div>
        </div>

        <button type="submit" id="btnSubmitOrder" class="btn red lg" style="width:100%; justify-content:center; font-size:16px;">
          <span>Konfirmasi & Buat Pesanan Sekarang</span>
          <svg class="ic"><use href="#ph-arrow-right"></use></svg>
        </button>
      </form>
    </div>

    <!-- Right: Order Summary Breakdown -->
    <div>
      <div class="checkout-card-box checkout-summary-sticky" style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px; position: sticky; top: 96px;">
        <h2 style="font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid var(--ring);">
          Ringkasan Pesanan (<?= getCartCount() ?> Item)
        </h2>

        <!-- Items list -->
        <div style="display:flex; flex-direction:column; gap: 14px; max-height: 380px; overflow-y:auto; padding-right: 4px; margin-bottom: 20px;">
          <?php foreach ($cart as $it): ?>
            <div style="display:flex; gap: 12px; align-items:center; padding-bottom: 12px; border-bottom: 1px solid var(--ring);">
              <div style="width: 54px; height: 54px; border-radius: var(--radius-sm); background: var(--shell); border: 1px solid var(--ring); overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                <img src="<?= e(getCartItemThumbnail($it)) ?>" alt="<?= e($it['product_name']) ?>" style="width:100%; height:100%; object-fit:contain;">
              </div>
              <div style="flex:1; min-width:0;">
                <h4 style="font-size: 13.5px; font-weight: 700; color: var(--ink); margin: 0 0 3px; line-height: 1.35; word-break: break-word; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                  <?= e($it['product_name']) ?>
                </h4>
                <span style="font-size: 12px; color: var(--muted); display:block;">
                  Ukuran: <?= e($it['size'] ?? 'L') ?> × <?= $it['qty'] ?>
                  <?= !empty($it['is_custom']) ? ' · <b style="color:var(--red-ink);">Kustom</b>' : '' ?>
                </span>
              </div>
              <div style="font-size: 13.5px; font-weight: 800; color: var(--ink); text-align:right;">
                <?= formatRupiah($it['price'] * $it['qty']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Totals calculation -->
        <div style="display:flex; flex-direction:column; gap: 10px; font-size: 13.5px;">
          <div style="display:flex; justify-content:space-between; color: var(--muted);">
            <span>Subtotal Produk</span>
            <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($subtotal) ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; color: var(--muted);">
            <span>Biaya Pengiriman</span>
            <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($shippingFee) ?></span>
          </div>
          <div style="height: 1px; background: var(--ring); margin: 4px 0;"></div>
          <div style="display:flex; justify-content:space-between; font-size: 17px; font-weight: 800;">
            <span style="color: var(--ink);">Total Tagihan</span>
            <span style="color: var(--red-ink);"><?= formatRupiah($grandTotal) ?></span>
          </div>
        </div>

        <div style="margin-top: 20px; padding: 12px; background: var(--shell); border-radius: var(--radius-sm); border: 1px solid var(--ring); display:flex; align-items:center; gap: 10px;">
          <span style="width:24px; height:24px; border-radius:50%; background:rgba(46,204,113,0.15); color:#2ECC71; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg class="ic" style="width:13px; height:13px;"><use href="#ph-check"></use></svg>
          </span>
          <span style="font-size: 11.5px; color: var(--muted); line-height: 1.4;">
            Garansi kepuasan & produk cacat diganti 100% gratis oleh Official TNM Merch.
          </span>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Courier Selector Style
  const courierRadios = document.querySelectorAll('.courier-radio');
  function updateCouriers() {
    courierRadios.forEach(radio => {
      const card = radio.parentElement.querySelector('.courier-card');
      if (radio.checked) {
        card.style.borderColor = 'var(--red)';
        card.style.background = 'rgba(214,59,41,0.1)';
      } else {
        card.style.borderColor = 'var(--ring)';
        card.style.background = 'var(--shell)';
      }
    });
  }
  courierRadios.forEach(r => r.addEventListener('change', updateCouriers));
  updateCouriers();

  // Handle Form Submit
  const checkoutForm = document.getElementById('checkoutForm');
  const btnSubmit = document.getElementById('btnSubmitOrder');

  checkoutForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span>Membuat Pesanan...</span>';

    const formData = new FormData(checkoutForm);
    const payload = {
      name: formData.get('name'),
      phone: formData.get('phone'),
      email: formData.get('email'),
      address: formData.get('address'),
      city: formData.get('city'),
      postal: formData.get('postal'),
      courier: formData.get('courier'),
      notes: formData.get('notes')
    };

    try {
      const csrfToken = window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('api/order.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(Object.assign({}, payload, { csrf_token: csrfToken }))
      });
      const data = await res.json();

      if (data.success) {
        // Redirect to order success page with secure access token
        const tokenParam = data.access_token ? `&token=${encodeURIComponent(data.access_token)}` : '';
        window.location.href = `order-success.php?order=${encodeURIComponent(data.order_number)}${tokenParam}&wa=${encodeURIComponent(data.whatsapp_url || '')}`;
      } else {
        alert(data.message || 'Gagal membuat pesanan');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
      }
    } catch (err) {
      console.error(err);
      alert('Terjadi kesalahan koneksi');
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = originalText;
    }
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
