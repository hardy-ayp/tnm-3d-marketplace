<?php
/**
 * TNM Marketplace — Process Order Checkout API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

// Strict CSRF Token Verification for Order Placement
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? ($_POST['csrf_token'] ?? ''));
if (!verifyMarketplaceCsrfToken($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
    exit;
}

$cart = getCart();
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang Anda kosong']);
    exit;
}

$name     = strip_tags(trim($data['name'] ?? ''));
$phone    = preg_replace('/[^\d+]/', '', trim($data['phone'] ?? ''));
$email    = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$address  = strip_tags(trim($data['address'] ?? ''));
$city     = strip_tags(trim($data['city'] ?? ''));
$postal   = strip_tags(trim($data['postal'] ?? ''));
$courier  = strip_tags(trim($data['courier'] ?? 'J&T Express'));
$notes    = strip_tags(trim($data['notes'] ?? ''));

if (empty($name) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Nama, nomor WhatsApp/HP, dan alamat wajib diisi']);
    exit;
}

$db = getDB();

$subtotal = getCartTotal();
$shippingCost = 15000; // Flat rate shipping for demo
$total = $subtotal + $shippingCost;

// Generate unique invoice number: TNM-YYYYMMDD-XXXX
$orderNum = 'TNM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

// Generate cryptographically secure access token per order to prevent IDOR
$accessToken = bin2hex(random_bytes(24));

try {
    $db->beginTransaction();

    $stmtOrder = $db->prepare("
        INSERT INTO orders (order_number, access_token, customer_name, customer_phone, customer_email, shipping_address, city, postal_code, courier, notes, subtotal, shipping_cost, total_price, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmtOrder->execute([
        $orderNum, $accessToken, $name, $phone, $email, $address, $city, $postal, $courier, $notes,
        $subtotal, $shippingCost, $total
    ]);

    $orderId = $db->lastInsertId();

    $stmtItem = $db->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, price, quantity, size, color, is_custom, custom_specs)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $waItemsText = "";

    foreach ($cart as $item) {
        $specsData = [];
        if (!empty($item['custom_specs'])) {
            if (is_string($item['custom_specs'])) {
                $specsData = json_decode($item['custom_specs'], true) ?: ['raw' => $item['custom_specs']];
            } elseif (is_array($item['custom_specs'])) {
                $specsData = $item['custom_specs'];
            }
        }
        if (!empty($item['snapshot'])) {
            $specsData['snapshot'] = $item['snapshot'];
        }
        if (!empty($item['model'])) {
            $specsData['model'] = $item['model'];
        }
        if (!empty($item['fabric'])) {
            $specsData['fabric'] = $item['fabric'];
        }
        $specsJson = !empty($specsData) ? json_encode($specsData) : null;

        $stmtItem->execute([
            $orderId,
            $item['product_id'] ?? null,
            $item['product_name'],
            $item['price'],
            $item['qty'],
            $item['size'] ?? 'L',
            $item['color'] ?? null,
            $item['is_custom'] ?? 0,
            $specsJson
        ]);

        $customTag = (!empty($item['is_custom'])) ? " [CUSTOM DESIGN]" : "";
        $waItemsText .= "• {$item['product_name']}{$customTag} ({$item['size']}) x {$item['qty']} = Rp " . number_format($item['price'] * $item['qty'], 0, ',', '.') . "\n";
    }

    $db->commit();
    clearCart();

    // Store in session so this buyer can access order-success.php securely
    if (!isset($_SESSION['tnm_buyer_orders']) || !is_array($_SESSION['tnm_buyer_orders'])) {
        $_SESSION['tnm_buyer_orders'] = [];
    }
    $_SESSION['tnm_buyer_orders'][$orderNum] = $accessToken;

    // Prepare direct WhatsApp confirmation link
    $waAdminPhone = "6281234567890";
    $waMessage = "Halo Admin Marketplace TNM,\n\nSaya ingin konfirmasi pesanan baru:\n*Invoice:* {$orderNum}\n*Nama:* {$name}\n*No. HP:* {$phone}\n*Alamat:* {$address}, {$city}\n*Ekspedisi:* {$courier}\n\n*Rincian Barang:*\n{$waItemsText}\n*Ongkir:* Rp " . number_format($shippingCost, 0, ',', '.') . "\n*Total:* Rp " . number_format($total, 0, ',', '.') . "\n\nMohon informasi rekening pembayaran. Terima kasih!";
    
    $waUrl = "https://api.whatsapp.com/send?phone={$waAdminPhone}&text=" . urlencode($waMessage);

    echo json_encode([
        'success'      => true,
        'order_number' => $orderNum,
        'access_token' => $accessToken,
        'order_id'     => $orderId,
        'total'        => $total,
        'whatsapp_url' => $waUrl
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Gagal memproses pesanan: ' . $e->getMessage()]);
}
