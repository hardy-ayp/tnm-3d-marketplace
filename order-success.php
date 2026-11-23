<?php
/**
 * TNM Official Marketplace — Order Success & Invoice Page
 */
require_once __DIR__ . '/config.php';

$orderNum = trim($_GET['order'] ?? '');
if (empty($orderNum)) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNum]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// IDOR Security Protection:
// Allow access ONLY IF:
// 1. Current user is logged-in Administrator
// 2. Or buyer's active session holds the matching order token
// 3. Or URL provides the cryptographically verified access token
$token = trim($_GET['token'] ?? '');
$isOwnerBySession = !empty($_SESSION['tnm_buyer_orders'][$orderNum]) && 
                    !empty($order['access_token']) && 
                    hash_equals((string)$order['access_token'], (string)$_SESSION['tnm_buyer_orders'][$orderNum]);
$isOwnerByToken   = !empty($token) && 
                    !empty($order['access_token']) && 
                    hash_equals((string)$order['access_token'], $token);
$isAdmin          = isAdminLoggedIn();

if (!$isOwnerBySession && !$isOwnerByToken && !$isAdmin) {
    http_response_code(403);
    $pageTitle = 'Akses Invoice Dibatasi';
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="wrap" style="padding: 80px 20px; text-align:center; max-width: 580px; margin: 0 auto;">
      <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(214,59,41,0.12); color: var(--red); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
        <svg viewBox="0 0 256 256" fill="currentColor" style="width:32px;height:32px;"><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-80-64a16,16,0,1,1-16-16A16,16,0,0,1,128,144Z"></path></svg>
      </div>
      <h1 style="font-family: var(--font-display); font-size: 24px; font-weight: 800; color: var(--ink); margin: 0 0 12px;">Akses Rincian Pesanan Dibatasi</h1>
      <p style="color: var(--muted); font-size: 14.5px; line-height: 1.6; margin: 0 0 24px;">Demi privasi dan keamanan data pelanggan, rincian invoice ini hanya dapat dilihat oleh pemilik pesanan melalui tautan konfirmasi resmi atau akun administrator.</p>
      <a href="catalog.php" class="btn red" style="display:inline-flex;">Kembali ke Katalog</a>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Order Items
$stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

$pageTitle = 'Pesanan Berhasil #' . $order['order_number'];

// Generate or retrieve WhatsApp link
$waUrl = $_GET['wa'] ?? '';
if (empty($waUrl)) {
    $waAdminPhone = "6281234567890";
    $waMsg = "Halo Admin Marketplace TNM,\n\nSaya ingin konfirmasi pesanan:\n*Invoice:* {$order['order_number']}\n*Nama:* {$order['customer_name']}\n*Total:* Rp " . number_format($order['total_price'], 0, ',', '.') . "\n\nMohon info pembayaran. Terima kasih!";
    $waUrl = "https://api.whatsapp.com/send?phone={$waAdminPhone}&text=" . urlencode($waMsg);
}

include __DIR__ . '/includes/header.php';
?>

<div class="wrap" style="padding-top: 40px; padding-bottom: 80px; max-width: 860px;">
  
  <!-- Success Banner -->
  <div style="text-align: center; margin-bottom: 36px;">
    <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(46, 204, 113, 0.15); color: #2ECC71; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
      <svg class="ic" viewBox="0 0 256 256" style="width: 36px; height: 36px;"><use href="#ph-check"></use></svg>
    </div>
    <span style="display:block; font-size:12px; font-weight:800; letter-spacing:0.08em; text-transform:uppercase; color:#2ECC71; margin-bottom:6px;">
      Pesanan Diterima
    </span>
    <h1 style="font-family: var(--font-display); font-size: clamp(26px, 3.2vw, 36px); font-weight: 800; color: var(--ink); margin: 0 0 10px;">
      Terima Kasih, <?= e($order['customer_name']) ?>!
    </h1>
    <p style="color: var(--muted); font-size: 14.5px; max-width: 52ch; margin: 0 auto; line-height: 1.6;">
      Pesananmu dengan nomor invoice <b><?= e($order['order_number']) ?></b> telah berhasil dicatat. Silakan lakukan konfirmasi via WhatsApp untuk verifikasi pembayaran.
    </p>

    <!-- Prominent WhatsApp Confirmation CTA -->
    <div style="margin-top: 24px;">
      <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="btn red lg" style="display:inline-flex; align-items:center; gap: 10px; font-size: 15px; padding: 14px 28px; box-shadow: 0 12px 28px rgba(214,59,41,0.4);">
        <span>Konfirmasi Pesanan ke WhatsApp Admin</span>
        <svg class="ic"><use href="#ph-arrow-right"></use></svg>
      </a>
    </div>
  </div>

  <!-- Invoice Container -->
  <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); overflow:hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
    
    <!-- Invoice Top Bar -->
    <div style="padding: 20px 24px; background: var(--shell); border-bottom: 1px solid var(--ring); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 12px;">
      <div>
        <span style="font-size: 11.5px; color: var(--faint); text-transform:uppercase; letter-spacing:0.05em; display:block;">Nomor Invoice</span>
        <span style="font-size: 16px; font-weight: 800; color: var(--ink); font-family: monospace;"><?= e($order['order_number']) ?></span>
      </div>
      <div style="text-align:right;">
        <span style="font-size: 11.5px; color: var(--faint); text-transform:uppercase; letter-spacing:0.05em; display:block;">Waktu Pesanan</span>
        <span style="font-size: 13.5px; font-weight: 600; color: var(--muted);"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</span>
      </div>
    </div>

    <!-- Customer & Shipping Summary -->
    <div style="padding: 24px; border-bottom: 1px solid var(--ring); display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
      <div>
        <h4 style="font-size: 12.5px; font-weight: 800; text-transform:uppercase; letter-spacing:0.05em; color: var(--red); margin: 0 0 10px;">
          Penerima & Kontak
        </h4>
        <p style="margin: 0; font-size: 14px; color: var(--ink); font-weight: 700;"><?= e($order['customer_name']) ?></p>
        <p style="margin: 4px 0 0; font-size: 13px; color: var(--muted);">WhatsApp: <?= e($order['customer_phone']) ?></p>
        <?php if (!empty($order['customer_email'])): ?>
          <p style="margin: 2px 0 0; font-size: 13px; color: var(--muted);">Email: <?= e($order['customer_email']) ?></p>
        <?php endif; ?>
      </div>

      <div>
        <h4 style="font-size: 12.5px; font-weight: 800; text-transform:uppercase; letter-spacing:0.05em; color: var(--red); margin: 0 0 10px;">
          Alamat Pengiriman
        </h4>
        <p style="margin: 0; font-size: 13.5px; color: var(--ink); line-height: 1.5;">
          <?= nl2br(e($order['shipping_address'])) ?><br>
          <?= e($order['city']) ?> <?= e($order['postal_code']) ?>
        </p>
        <p style="margin: 6px 0 0; font-size: 12.5px; color: var(--muted);">
          Ekspedisi: <b><?= e($order['courier']) ?></b>
        </p>
        <?php if (!empty($order['notes'])): ?>
          <p style="margin: 4px 0 0; font-size: 12px; color: var(--faint); font-style:italic;">
            Catatan: "<?= e($order['notes']) ?>"
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Items Breakdown -->
    <div style="padding: 24px;">
      <h4 style="font-size: 12.5px; font-weight: 800; text-transform:uppercase; letter-spacing:0.05em; color: var(--red); margin: 0 0 16px;">
        Rincian Barang yang Dipesan
      </h4>

      <div style="display:flex; flex-direction:column; gap: 16px;">
        <?php foreach ($items as $item): ?>
          <?php
            $specs = !empty($item['custom_specs']) ? json_decode($item['custom_specs'], true) : [];
            $snapshotImg = $specs['snapshot'] ?? null;
          ?>
          <div style="display:flex; gap: 16px; align-items:flex-start; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <!-- Thumb / Snapshot preview -->
            <div style="width: 72px; height: 72px; border-radius: var(--radius-sm); background: var(--shell); border: 1px solid var(--ring); overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
              <img src="<?= e($snapshotImg ?? 'assets/img/products/custom-shirt.svg') ?>" alt="<?= e($item['product_name']) ?>" style="width:100%; height:100%; object-fit:contain;">
            </div>

            <div style="flex:1; min-width:0;">
              <div style="display:flex; justify-content:space-between; align-items:baseline; gap: 12px;">
                <h5 style="font-size: 14.5px; font-weight: 700; color: var(--ink); margin: 0;">
                  <?= e($item['product_name']) ?>
                </h5>
                <span style="font-size: 14px; font-weight: 800; color: var(--ink);">
                  <?= formatRupiah($item['price'] * $item['quantity']) ?>
                </span>
              </div>

              <div style="display:flex; gap: 8px; flex-wrap:wrap; font-size: 12px; color: var(--muted); margin-top: 4px;">
                <span>Ukuran: <b><?= e($item['size']) ?></b></span>
                <span>Jumlah: <b><?= $item['quantity'] ?> pcs</b></span>
                <?php if ($item['is_custom']): ?>
                  <span style="color: var(--red-ink); font-weight: 700;"> Desain Kustom Studio</span>
                <?php endif; ?>
              </div>

              <?php if (!empty($snapshotImg)): ?>
                <div style="margin-top: 8px;">
                  <a href="<?= e($snapshotImg) ?>" target="_blank" download="TNM-Custom-<?= e($order['order_number']) ?>.png" class="btn sm ghost" style="padding: 4px 10px; font-size: 11px;">
                    Unduh Mockup Desain Kustom (.PNG)
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Financial Calculations -->
      <div style="display:flex; flex-direction:column; gap: 10px; margin-top: 20px; font-size: 14px; padding-top: 16px;">
        <div style="display:flex; justify-content:space-between; color: var(--muted);">
          <span>Subtotal Barang</span>
          <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($order['subtotal']) ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; color: var(--muted);">
          <span>Ongkos Kirim (<?= e($order['courier']) ?>)</span>
          <span style="color: var(--ink); font-weight: 700;"><?= formatRupiah($order['shipping_cost']) ?></span>
        </div>
        <div style="height: 1px; background: var(--ring); margin: 6px 0;"></div>
        <div style="display:flex; justify-content:space-between; font-size: 18px; font-weight: 800;">
          <span style="color: var(--ink);">Total Tagihan Pembayaran</span>
          <span style="color: var(--red-ink);"><?= formatRupiah($order['total_price']) ?></span>
        </div>
      </div>
    </div>

    <!-- Bank Payment Instructions Box -->
    <div style="padding: 24px; background: var(--shell); border-top: 1px solid var(--ring);">
      <h4 style="font-size: 13.5px; font-weight: 800; color: var(--ink); margin: 0 0 12px; display:flex; align-items:center; gap: 8px;">
        <svg class="ic" style="color:var(--red-ink);"><use href="#ph-shopping-bag"></use></svg>
        <span>Panduan Transfer Pembayaran</span>
      </h4>
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; font-size: 13px;">
        <div style="background:var(--surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--ring);">
          <span style="font-size: 11px; color: var(--faint); text-transform:uppercase; font-weight:700;">Bank BCA</span>
          <p style="margin: 4px 0 2px; font-family: monospace; font-size: 15px; font-weight:800; color: var(--ink);">873-091-2345</p>
          <span style="font-size: 11.5px; color: var(--muted);">a.n. TNM Official Merchandise</span>
        </div>
        <div style="background:var(--surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--ring);">
          <span style="font-size: 11px; color: var(--faint); text-transform:uppercase; font-weight:700;">Bank Mandiri</span>
          <p style="margin: 4px 0 2px; font-family: monospace; font-size: 15px; font-weight:800; color: var(--ink);">137-00-1982736-1</p>
          <span style="font-size: 11.5px; color: var(--muted);">a.n. TNM Official Merchandise</span>
        </div>
      </div>
      <p style="margin: 14px 0 0; font-size: 12px; color: var(--muted); line-height: 1.5;">
        Setelah melakukan transfer, silakan kirimkan bukti transfer ke WhatsApp admin kami untuk konfirmasi dan proses pengiriman langsung.
      </p>
    </div>

  </div>

  <div style="display:flex; justify-content:center; gap: 12px; margin-top: 36px; flex-wrap: wrap;">
    <a href="catalog.php" class="btn ghost" style="min-width: 200px; justify-content:center;">Kembali ke Katalog Belanja</a>
    <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="btn red" style="min-width: 200px; justify-content:center;">Buka Chat WhatsApp Admin</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
