<?php
/**
 * TNM Marketplace — Admin Order Management & Custom Mockup Inspection
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

$adminTitle = 'Kelola Pesanan';
$db = getDB();
$message = '';

// Update Order Status handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyMarketplaceCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF gagal. Silakan muat ulang halaman.';
    } else {
        $orderId = intval($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? 'pending');
        
        $stmtUp = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $orderId]);
        header("Location: orders.php?order_id={$orderId}&msg=status_updated");
        exit;
    }
}

// Check single order view
$viewOrderId = intval($_GET['order_id'] ?? 0);
$singleOrder = null;
$orderItems = [];

if ($viewOrderId > 0) {
    $stmtO = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmtO->execute([$viewOrderId]);
    $singleOrder = $stmtO->fetch();

    if ($singleOrder) {
        $stmtI = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtI->execute([$viewOrderId]);
        $orderItems = $stmtI->fetchAll();
    }
}

// Filter Status for List
$filterStatus = trim($_GET['status'] ?? 'all');
$whereClause = "";
$params = [];
if ($filterStatus !== 'all' && in_array($filterStatus, ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
    $whereClause = "WHERE status = ?";
    $params[] = $filterStatus;
}

$stmtOrders = $db->prepare("SELECT * FROM orders {$whereClause} ORDER BY created_at DESC");
$stmtOrders->execute($params);
$orders = $stmtOrders->fetchAll();

if (isset($_GET['msg']) && $_GET['msg'] === 'status_updated') {
    $message = 'Status pesanan berhasil diperbarui!';
}

include __DIR__ . '/includes/header.php';
?>

<!-- Title Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 16px; margin-bottom: 28px;">
  <div>
    <h1 style="font-family: var(--font-display); font-size: 28px; font-weight: 800; color: var(--ink); margin: 0 0 6px;">
      <?= $singleOrder ? 'Detail Pesanan #' . e($singleOrder['order_number']) : 'Kelola Pesanan Masuk' ?>
    </h1>
    <p style="color: var(--muted); font-size: 14px; margin: 0;">
      <?= $singleOrder ? 'Periksa rincian pesanan dan preview mockup custom desain untuk produksi sablon.' : 'Pantau dan kelola seluruh pesanan merchandise dan apparel kustom.' ?>
    </p>
  </div>

  <?php if ($singleOrder): ?>
    <a href="orders.php" class="btn sm ghost">← Kembali ke Daftar Pesanan</a>
  <?php endif; ?>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.4); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 24px; font-size: 13.5px; color: #2ecc71;">
     <?= e($message) ?>
  </div>
<?php endif; ?>

<?php if ($singleOrder): ?>
  <!-- SINGLE ORDER DETAIL VIEW -->
  <div style="display:grid; grid-template-columns: 1fr; gap: 28px;" class="order-detail-grid">
    <style>
      @media (min-width: 992px) {
        .order-detail-grid { grid-template-columns: 1.6fr 1fr !important; }
      }
    </style>

    <!-- Left: Items & Custom Design Preview -->
    <div style="display:flex; flex-direction:column; gap: 20px;">
      
      <!-- Order Items Card -->
      <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
        <h2 style="font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--ring);">
          Daftar Barang yang Dipesan
        </h2>

        <div style="display:flex; flex-direction:column; gap: 24px;">
          <?php foreach ($orderItems as $item): ?>
            <?php
              $specs = !empty($item['custom_specs']) ? json_decode($item['custom_specs'], true) : [];
              $hasCustom = !empty($item['is_custom']);
              $snapshot = $specs['snapshot'] ?? null;
            ?>
            <div style="padding: 18px; background: var(--shell); border: 1px solid var(--ring); border-radius: var(--radius-sm);">
              <div style="display:flex; gap: 16px; align-items:flex-start;">
                <!-- Product/Custom Thumb -->
                <div style="width: 80px; height: 80px; border-radius: var(--radius-sm); background: #000; border: 1px solid var(--ring); overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                  <img src="<?= e($snapshot ?? '../assets/img/products/custom-shirt.svg') ?>" alt="<?= e($item['product_name']) ?>" style="width:100%; height:100%; object-fit:contain;">
                </div>

                <div style="flex:1; min-width:0;">
                  <div style="display:flex; justify-content:space-between; align-items:baseline; gap: 12px;">
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--ink); margin: 0;">
                      <?= e($item['product_name']) ?>
                    </h3>
                    <span style="font-size: 15px; font-weight: 800; color: var(--ink);">
                      <?= formatRupiah($item['price'] * $item['quantity']) ?>
                    </span>
                  </div>

                  <div style="display:flex; gap: 8px; flex-wrap:wrap; font-size: 12px; color: var(--muted); margin-top: 6px;">
                    <span style="background:var(--surface); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--ring);">
                      Ukuran: <b><?= e($item['size']) ?></b>
                    </span>
                    <span style="background:var(--surface); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--ring);">
                      Qty: <b><?= $item['quantity'] ?> pcs</b>
                    </span>
                    <span style="background:var(--surface); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--ring);">
                      Harga Satuan: <?= formatRupiah($item['price']) ?>
                    </span>
                  </div>

                  <?php if ($hasCustom): ?>
                    <!-- Custom Apparel Blueprint Section -->
                    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--ring);">
                      <div style="display:flex; align-items:center; gap: 8px; margin-bottom: 8px;">
                        <span style="background: var(--red); color:#fff; font-size:10.5px; font-weight:800; text-transform:uppercase; padding:2px 8px; border-radius:4px;">
                          PRODUK CUSTOM APPAREL
                        </span>
                        <span style="font-size: 12px; color: var(--muted);">Spesifikasi Sablon & Produksi:</span>
                      </div>

                      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; font-size: 12px; margin-bottom: 12px;">
                        <?php if (!empty($specs['model'])): ?>
                          <div style="background:var(--surface); padding: 6px 10px; border-radius:4px;">
                            <span style="color:var(--faint);">Model:</span> <b><?= ucfirst(e($specs['model'])) ?></b>
                          </div>
                        <?php endif; ?>

                        <?php if (!empty($specs['fabric'])): ?>
                          <div style="background:var(--surface); padding: 6px 10px; border-radius:4px;">
                            <span style="color:var(--faint);">Bahan:</span> <b><?= ucfirst(e($specs['fabric'])) ?></b>
                          </div>
                        <?php endif; ?>

                        <?php if (!empty($specs['color'])): ?>
                          <div style="background:var(--surface); padding: 6px 10px; border-radius:4px; display:flex; align-items:center; gap: 6px;">
                            <span style="color:var(--faint);">Warna Kain:</span>
                            <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:<?= e($specs['color']) ?>; border:1px solid #777;"></span>
                            <b><?= e($specs['color']) ?></b>
                          </div>
                        <?php endif; ?>
                      </div>

                      <?php if (!empty($snapshot)): ?>
                        <!-- Mockup Preview Box -->
                        <div style="background: #000; border: 1px solid var(--ring); border-radius: var(--radius-sm); padding: 16px; text-align:center;">
                          <span style="display:block; font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;">
                            Render Snapshot Desain Pelanggan (Siap Sablon)
                          </span>
                          <img src="<?= e($snapshot) ?>" alt="Customer Mockup" style="max-height: 280px; width: auto; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.8));">
                          <div style="margin-top: 14px;">
                            <a href="<?= e($snapshot) ?>" download="TNM-CUST-<?= e($singleOrder['order_number']) ?>.png" class="btn sm red" style="display:inline-flex; align-items:center; gap: 6px;">
                              <span>Unduh Mockup HD (.PNG)</span>
                            </a>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Financial Summary -->
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--ring); display:flex; flex-direction:column; gap: 8px; font-size: 13.5px;">
          <div style="display:flex; justify-content:space-between; color:var(--muted);">
            <span>Subtotal Produk</span>
            <span style="color:var(--ink); font-weight:700;"><?= formatRupiah($singleOrder['subtotal']) ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; color:var(--muted);">
            <span>Ongkir (<?= e($singleOrder['courier']) ?>)</span>
            <span style="color:var(--ink); font-weight:700;"><?= formatRupiah($singleOrder['shipping_cost']) ?></span>
          </div>
          <div style="height:1px; background:var(--ring); margin:4px 0;"></div>
          <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800;">
            <span style="color:var(--ink);">Total Tagihan</span>
            <span style="color:var(--red-ink);"><?= formatRupiah($singleOrder['total_price']) ?></span>
          </div>
        </div>

      </div>
    </div>

    <!-- Right: Customer Info & Status Updater -->
    <div style="display:flex; flex-direction:column; gap: 20px;">
      
      <!-- Status Management Card -->
      <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--ink); margin: 0 0 16px;">
          Kelola Status Pesanan
        </h3>

        <form method="POST" action="orders.php">
          <input type="hidden" name="csrf_token" value="<?= e(getMarketplaceCsrfToken()) ?>">
          <input type="hidden" name="update_status" value="1">
          <input type="hidden" name="order_id" value="<?= $singleOrder['id'] ?>">

          <div style="margin-bottom: 16px;">
            <label style="display:block; font-size:12.5px; font-weight:700; color:var(--muted); margin-bottom:6px;">Status Saat Ini:</label>
            <select name="status" class="form-control" style="width:100%; padding:10px 14px; background:var(--shell); border:1px solid var(--ring); border-radius:var(--radius-sm); color:var(--ink); font-size:14px; font-weight:700;">
              <option value="pending" <?= $singleOrder['status'] === 'pending' ? 'selected' : '' ?>>Pending (Menunggu Pembayaran)</option>
              <option value="processing" <?= $singleOrder['status'] === 'processing' ? 'selected' : '' ?>>Processing (Sedang Disablon / Dikemas)</option>
              <option value="shipped" <?= $singleOrder['status'] === 'shipped' ? 'selected' : '' ?>>Shipped (Telah Dikirim)</option>
              <option value="completed" <?= $singleOrder['status'] === 'completed' ? 'selected' : '' ?>>Completed (Selesai)</option>
              <option value="cancelled" <?= $singleOrder['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled (Dibatalkan)</option>
            </select>
          </div>

          <button type="submit" class="btn red sm" style="width:100%; justify-content:center;">
            <span>Simpan Perubahan Status</span>
          </button>
        </form>
      </div>

      <!-- Customer & Shipping Details Card -->
      <div style="background: var(--surface); border: 1px solid var(--ring); border-radius: var(--radius-md); padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--ink); margin: 0 0 16px;">
          Data Pengiriman Pelanggan
        </h3>

        <div style="display:flex; flex-direction:column; gap: 14px; font-size: 13.5px;">
          <div>
            <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">Nama Lengkap</span>
            <span style="font-weight:700; color:var(--ink); font-size:15px;"><?= e($singleOrder['customer_name']) ?></span>
          </div>

          <div>
            <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">WhatsApp / Telepon</span>
            <div style="display:flex; align-items:center; gap: 8px; margin-top:2px;">
              <span style="font-weight:700; color:var(--ink);"><?= e($singleOrder['customer_phone']) ?></span>
              <?php
                $cleanPhone = preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $singleOrder['customer_phone']));
                $waChatUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode("Halo kak {$singleOrder['customer_name']}, kami dari Admin Marketplace TNM terkait pesanan #{$singleOrder['order_number']}...");
              ?>
              <a href="<?= $waChatUrl ?>" target="_blank" class="btn sm" style="padding: 2px 8px; font-size:11px; background:#25D366; color:#000; font-weight:800; border:0;">
                Chat WA ↗
              </a>
            </div>
          </div>

          <?php if (!empty($singleOrder['customer_email'])): ?>
            <div>
              <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">Email</span>
              <span style="color:var(--muted);"><?= e($singleOrder['customer_email']) ?></span>
            </div>
          <?php endif; ?>

          <div>
            <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">Alamat Pengiriman</span>
            <p style="margin: 2px 0 0; color:var(--ink); line-height:1.5;">
              <?= nl2br(e($singleOrder['shipping_address'])) ?><br>
              <?= e($singleOrder['city']) ?> <?= e($singleOrder['postal_code']) ?>
            </p>
          </div>

          <div>
            <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">Kurir Pengiriman</span>
            <span style="color:var(--ink); font-weight:700;"><?= e($singleOrder['courier']) ?></span>
          </div>

          <?php if (!empty($singleOrder['notes'])): ?>
            <div>
              <span style="color:var(--faint); font-size:11.5px; text-transform:uppercase; font-weight:700; display:block;">Catatan Pembeli</span>
              <span style="color:var(--muted); font-style:italic;">"<?= e($singleOrder['notes']) ?>"</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

<?php else: ?>
  <!-- ORDERS TABLE LIST -->
  
  <!-- Filter Tabs -->
  <div style="display:flex; gap: 8px; flex-wrap:wrap; margin-bottom: 20px;">
    <?php
      $tabs = [
        'all'        => 'Semua Pesanan',
        'pending'    => 'Menunggu (Pending)',
        'processing' => 'Diproses (Sablon/Packing)',
        'shipped'    => 'Dikirim (Shipped)',
        'completed'  => 'Selesai (Completed)',
        'cancelled'  => 'Dibatalkan'
      ];
      foreach ($tabs as $k => $label):
    ?>
      <a href="orders.php?status=<?= $k ?>" class="btn sm <?= $filterStatus === $k ? 'red' : 'ghost' ?>">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="admin-table-container">
    <?php if (empty($orders)): ?>
      <div style="padding: 48px; text-align: center; color: var(--muted);">
        Tidak ada pesanan dengan status terpilih.
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>No. Invoice</th>
            <th>Nama Pembeli</th>
            <th>WhatsApp</th>
            <th>Kota Tujuan</th>
            <th>Kurir</th>
            <th>Total Tagihan</th>
            <th>Status</th>
            <th>Waktu Masuk</th>
            <th style="text-align:right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td style="white-space:nowrap;">
                <b style="color:var(--ink); font-family:monospace;"><?= e($o['order_number']) ?></b>
              </td>
              <td>
                <span style="font-weight:700; color:var(--ink); max-width: 150px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle;" title="<?= e($o['customer_name']) ?>"><?= e($o['customer_name']) ?></span>
              </td>
              <td style="white-space:nowrap;"><?= e($o['customer_phone']) ?></td>
              <td>
                <span style="max-width: 130px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle;" title="<?= e($o['city'] ?: '-') ?>"><?= e($o['city'] ?: '-') ?></span>
              </td>
              <td style="white-space:nowrap;"><?= e($o['courier']) ?></td>
              <td style="white-space:nowrap;">
                <b style="color:var(--ink);"><?= formatRupiah($o['total_price']) ?></b>
              </td>
              <td>
                <span class="status-badge <?= e($o['status']) ?>">
                  <?= ucfirst($o['status']) ?>
                </span>
              </td>
              <td style="font-size:12px;"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
              <td style="text-align:right; white-space:nowrap;">
                <a href="orders.php?order_id=<?= $o['id'] ?>" class="btn sm ghost" style="padding: 4px 10px; font-size:12px;">
                  Detail & Mockup
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
