<?php
/**
 * TNM Marketplace — Cart AJAX API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Helper to calculate custom apparel price securely on server
function calculateCustomApparelPrice(string $model, string $fabric, $specs = null): float {
    $basePrices = [
        'tshirt' => 135000.0,
        'hoodie' => 265000.0
    ];
    $m = strtolower(trim($model)) === 'hoodie' ? 'hoodie' : 'tshirt';
    $base = $basePrices[$m];

    $fabricExtras = [
        'combed24s' => 0.0,
        'heavy16s'  => 20000.0,
        'fleece'    => 35000.0
    ];
    $fabKey = 'combed24s';
    $fLower = strtolower(trim($fabric));
    if (str_contains($fLower, 'heavy') || str_contains($fLower, '16s')) {
        $fabKey = 'heavy16s';
    } elseif (str_contains($fLower, 'fleece') || str_contains($fLower, '330')) {
        $fabKey = 'fleece';
    }
    $extraFabric = $fabricExtras[$fabKey] ?? 0.0;

    $extraSides = 0.0;
    if (!empty($specs)) {
        $specArr = is_array($specs) ? $specs : json_decode($specs, true);
        if (is_array($specArr)) {
            $hasFront = !empty($specArr['frontDesign']) || !empty($specArr['front_art']);
            $hasBack  = !empty($specArr['backDesign'])  || !empty($specArr['back_art']);
            if ($hasFront && $hasBack) {
                $extraSides = 25000.0;
            }
        }
    }

    return $base + $extraFabric + $extraSides;
}

// Helper to sanitize snapshot image string against stored XSS
function sanitizeSnapshot(?string $snapshot, string $fallback): string {
    if (empty($snapshot)) {
        return $fallback;
    }
    $trimmed = trim($snapshot);
    // Allow safe base64 Data URIs (png, jpeg, webp)
    if (preg_match('/^data:image\/(png|jpe?g|webp);base64,[A-Za-z0-9+\/=\s]+$/i', $trimmed)) {
        return $trimmed;
    }
    // Allow safe relative paths
    if (preg_match('/^[a-zA-Z0-9_\-\/]+\.(png|jpe?g|webp|svg)$/i', $trimmed) && !str_contains($trimmed, '..')) {
        return $trimmed;
    }
    return $fallback;
}

if ($method === 'GET' && $action === 'get') {
    $cart = getCart();
    $items = [];
    foreach ($cart as $it) {
        $it['thumbnail'] = getCartItemThumbnail($it);
        $items[] = $it;
    }
    echo json_encode([
        'success' => true,
        'count'   => getCartCount(),
        'total'   => getCartTotal(),
        'items'   => $items
    ]);
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    // Strict CSRF Token Verification for all cart state modifications
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? ($_POST['csrf_token'] ?? ''));
    if (!verifyMarketplaceCsrfToken($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
        exit;
    }

    $postAction = $data['action'] ?? '';

    if ($postAction === 'add_custom') {
        $model = ($data['model'] ?? 'tshirt') === 'hoodie' ? 'hoodie' : 'tshirt';
        $fabric = strip_tags($data['fabric'] ?? 'combed24s');
        $specs = $data['specs'] ?? '';

        // Strictly recalculate price on server: do NOT trust client price
        $price = calculateCustomApparelPrice($model, $fabric, $specs);

        $modelDefault3D = $model === 'hoodie' ? 'assets/img/mockups/hoodie-front.png' : 'assets/img/mockups/tshirt-front.png';
        $snapshot = sanitizeSnapshot($data['snapshot'] ?? null, $modelDefault3D);

        $defaultProdName = $model === 'hoodie' ? 'Hoodie Kustom TNM' : 'Kaos Kustom TNM';
        $prodName = !empty($data['product_name']) ? strip_tags($data['product_name']) : $defaultProdName;
        $size = preg_replace('/[^a-zA-Z0-9_\-]/', '', $data['size'] ?? 'L');
        $color = strip_tags($data['color'] ?? '#141414');

        $item = [
            'product_name' => $prodName,
            'price'        => $price,
            'qty'          => 1,
            'size'         => $size,
            'color'        => $color,
            'model'        => $model,
            'fabric'       => $fabric,
            'is_custom'    => 1,
            'image'        => $modelDefault3D,
            'snapshot'     => $snapshot,
            'custom_specs' => is_string($specs) ? $specs : json_encode($specs)
        ];

        $key = addToCart($item);
        echo json_encode([
            'success'  => true,
            'message'  => 'Desain kustom berhasil ditambahkan',
            'cart_key' => $key,
            'count'    => getCartCount()
        ]);
        exit;
    }

    if ($postAction === 'add_product') {
        $prodId = intval($data['product_id'] ?? 0);
        $size = $data['size'] ?? 'L';
        $qty = intval($data['qty'] ?? 1);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$prodId]);
        $prod = $stmt->fetch();

        if (!$prod) {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
            exit;
        }

        $image = $prod['image'];
        $nameLower = strtolower($prod['name']);
        if (str_contains($nameLower, 'hoodie') || str_contains($image, 'hoodie')) {
            $image = 'assets/img/mockups/hoodie-front.png';
        } elseif ($prod['is_customizable'] || $prod['category'] === 'apparel' || str_contains($image, 'shirt')) {
            if (str_contains($image, '.svg') || empty($image)) {
                $image = 'assets/img/mockups/tshirt-front.png';
            }
        }

        $item = [
            'product_id'   => $prod['id'],
            'product_name' => $prod['name'],
            'price'        => floatval($prod['price']),
            'qty'          => max(1, $qty),
            'size'         => $size,
            'image'        => $image,
            'is_custom'    => $prod['is_customizable'] ? 1 : 0
        ];

        $key = addToCart($item);
        echo json_encode([
            'success'  => true,
            'message'  => 'Produk berhasil ditambahkan',
            'cart_key' => $key,
            'count'    => getCartCount()
        ]);
        exit;
    }

    if ($postAction === 'update') {
        $key = $data['cart_key'] ?? '';
        $qty = intval($data['qty'] ?? 1);
        updateCartQty($key, $qty);

        echo json_encode([
            'success' => true,
            'count'   => getCartCount(),
            'total'   => getCartTotal()
        ]);
        exit;
    }

    if ($postAction === 'remove') {
        $key = $data['cart_key'] ?? '';
        removeFromCart($key);

        echo json_encode([
            'success' => true,
            'count'   => getCartCount(),
            'total'   => getCartTotal()
        ]);
        exit;
    }

    if ($postAction === 'clear') {
        clearCart();
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
