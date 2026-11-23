<?php
/**
 * TNM Official Marketplace — Global Configuration & Database Bootstrapper
 * Powered by PHP + SQLite (Zero Config, Portable, High Performance)
 */

// Global Polyfills for PHP 7.x compatibility (PHP 8.0 string functions)
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return (string)$needle === '' || strpos((string)$haystack, (string)$needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle === '' || strncmp((string)$haystack, (string)$needle, strlen((string)$needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return (string)$needle === '' || substr((string)$haystack, -strlen((string)$needle)) === (string)$needle;
    }
}

// Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                    (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'] ?? '', '"scheme":"https"') !== false);
        if ($isSecure) {
            ini_set('session.cookie_secure', '1');
        }
    }
    session_start();
}

// CSRF Protection Helpers
if (empty($_SESSION['tnm_market_csrf'])) {
    $_SESSION['tnm_market_csrf'] = bin2hex(random_bytes(32));
}

if (!function_exists('getMarketplaceCsrfToken')) {
    function getMarketplaceCsrfToken(): string {
        if (empty($_SESSION['tnm_market_csrf'])) {
            $_SESSION['tnm_market_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['tnm_market_csrf'];
    }
}

if (!function_exists('verifyMarketplaceCsrfToken')) {
    function verifyMarketplaceCsrfToken(?string $token): bool {
        if (empty($token) || empty($_SESSION['tnm_market_csrf'])) {
            return false;
        }
        return hash_equals($_SESSION['tnm_market_csrf'], $token);
    }
}

if (!function_exists('getCsrfToken')) {
    function getCsrfToken(): string {
        return getMarketplaceCsrfToken();
    }
}

date_default_timezone_set('Asia/Jakarta');

define('MARKETPLACE_ROOT', __DIR__);
define('DATA_DIR', MARKETPLACE_ROOT . '/data');
define('UPLOADS_DIR', MARKETPLACE_ROOT . '/uploads');
define('DB_FILE', DATA_DIR . '/marketplace.db');

// Detect Protocol & Host safely (supporting Cloudflare & reverse proxies)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
           (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false);

$isDebug = (!empty($_GET['debug']) && $_GET['debug'] === 'tnm_debug_2026') || 
           in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);

if ($isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0755, true);
    }
    ini_set('error_log', DATA_DIR . '/marketplace_error.log');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
if (stripos($host, 'tnmofficials.id') !== false) {
    $host = 'tnmofficials.id';
    $protocol = 'https://';
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$cleanDir = preg_replace('~/(admin|api)(/.*)?$~i', '', $scriptDir);
$cleanDir = rtrim($cleanDir, '/');

if (preg_match('~/marketplace$~i', $cleanDir)) {
    $shopPath = $cleanDir;
    $rootPath = substr($cleanDir, 0, -strlen('/marketplace'));
} elseif ($cleanDir === '' || $cleanDir === '/') {
    $shopPath = '';
    $rootPath = '';
} else {
    $shopPath = $cleanDir . '/marketplace';
    $rootPath = $cleanDir;
}

define('SHOP_URL', rtrim($protocol . $host . $shopPath, '/'));
define('TNM_MAIN_URL', rtrim($protocol . $host . ($rootPath ?: ''), '/') ?: ($protocol . $host));

// Graceful Database Error Handler
function handleDBError($message, $exception = null) {
    global $isDebug;
    error_log("[TNM Marketplace DB Error] " . $message . ($exception ? ": " . $exception->getMessage() : ""));
    http_response_code(500);

    $detailHtml = '';
    if ($isDebug && $exception) {
        $detailHtml = '<div style="background:#151515; border:1px solid #333; padding:12px; border-radius:8px; font-family:monospace; font-size:12px; color:#f87171; text-align:left; overflow-x:auto; margin-top:16px;">'
            . htmlspecialchars($exception->getMessage()) . '<br><br>'
            . nl2br(htmlspecialchars($exception->getTraceAsString()))
            . '</div>';
    }

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Layanan Marketplace — TNM</title>
      <style>
        body { background: #0f0f0f; color: #ece8dd; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: grid; place-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .err-card { max-width: 580px; width: 100%; background: #1a1712; border: 1px solid rgba(236,232,221,0.1); border-radius: 20px; padding: 36px 28px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.6); }
        .err-tag { display: inline-block; padding: 4px 14px; border-radius: 999px; background: rgba(214,59,41,0.18); color: #f07361; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; }
        h1 { font-size: 24px; font-weight: 800; margin: 0 0 10px; color: #fff; }
        p { color: #a8a294; font-size: 14.5px; line-height: 1.6; margin: 0 0 20px; }
        .steps-box { background: rgba(236,232,221,0.03); border: 1px solid rgba(236,232,221,0.08); border-radius: 12px; padding: 16px; text-align: left; margin: 18px 0; font-size: 13.5px; color: #ece8dd; }
        .steps-box b { color: #f07361; }
        .steps-box ol { margin: 8px 0 0 18px; padding: 0; }
        .steps-box li { margin-bottom: 6px; }
        .btn-row { display: flex; gap: 10px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
        .btn-act { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 999px; text-decoration: none; font-size: 14px; font-weight: 700; }
        .btn-act.red { background: #d63b29; color: #fff; }
        .btn-act.ghost { background: rgba(236,232,221,0.05); color: #ece8dd; border: 1px solid rgba(236,232,221,0.1); }
      </style>
    </head>
    <body>
      <div class="err-card">
        <span class="err-tag">Pemulihan Sistem Database</span>
        <h1>Koneksi Marketplace Sedang Dipersiapkan</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <div class="steps-box">
          <b>Panduan Pemeriksaan Singkat:</b>
          <ol>
            <li>Pastikan ekstensi <code>pdo_sqlite</code> aktif di cPanel (menu <i>Select PHP Version &rarr; Extensions</i>).</li>
            <li>Pastikan folder <code>marketplace/data</code> dan <code>marketplace/uploads</code> memiliki izin tulis (chmod 755 atau 775).</li>
            <li>Buka halaman diagnostik: <a href="' . SHOP_URL . '/check_health.php" style="color:#f07361; text-decoration:underline;">' . SHOP_URL . '/check_health.php</a> untuk melihat status lengkap sistem.</li>
          </ol>
        </div>
        ' . $detailHtml . '
        <div class="btn-row">
          <a href="' . TNM_MAIN_URL . '" class="btn-act ghost">Kembali ke Beranda Utama</a>
          <a href="' . SHOP_URL . '/check_health.php" class="btn-act red">Periksa Status Server</a>
        </div>
      </div>
    </body>
    </html>';
    exit;
}

// SQLite PDO Connection
function getDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    if (!extension_loaded('pdo')) {
        handleDBError("Ekstensi PHP PDO tidak aktif pada server hosting.");
    }
    if (!extension_loaded('pdo_sqlite')) {
        handleDBError("Ekstensi PHP pdo_sqlite belum diaktifkan pada server hosting. Silakan centang pdo_sqlite melalui menu cPanel > Select PHP Version > Extensions.");
    }

    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0775, true);
    }
    if (!is_dir(UPLOADS_DIR)) {
        @mkdir(UPLOADS_DIR, 0775, true);
    }

    if (is_dir(DATA_DIR) && !is_writable(DATA_DIR)) {
        @chmod(DATA_DIR, 0775);
    }

    try {
        $initNeeded = !file_exists(DB_FILE) || filesize(DB_FILE) === 0;

        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Concurrency & SQLite performance PRAGMAs
        $pdo->exec("PRAGMA journal_mode = WAL;");
        $pdo->exec("PRAGMA synchronous = NORMAL;");
        $pdo->exec("PRAGMA busy_timeout = 5000;");

        if ($initNeeded) {
            initDatabase($pdo);
        }
    } catch (PDOException $e) {
        handleDBError("Tidak dapat mengakses berkas database SQLite: " . $e->getMessage(), $e);
    } catch (Exception $e) {
        handleDBError("Terjadi kesalahan sistem database: " . $e->getMessage(), $e);
    } catch (Throwable $e) {
        handleDBError("Terjadi kesalahan sistem: " . $e->getMessage(), $e);
    }

    return $pdo;
}

// Database Schema Initialization & Seeding
function initDatabase($db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL,
            original_price REAL NULL,
            stock INTEGER DEFAULT 100,
            description TEXT,
            image TEXT,
            is_customizable INTEGER DEFAULT 0,
            is_featured INTEGER DEFAULT 0,
            badge TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS preset_designs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            image_path TEXT NOT NULL,
            svg_content TEXT NULL,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT UNIQUE NOT NULL,
            access_token TEXT,
            customer_name TEXT NOT NULL,
            customer_phone TEXT NOT NULL,
            customer_email TEXT NOT NULL,
            shipping_address TEXT NOT NULL,
            city TEXT,
            postal_code TEXT,
            courier TEXT NOT NULL,
            notes TEXT,
            subtotal REAL NOT NULL,
            shipping_cost REAL NOT NULL,
            total_price REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NULL,
            product_name TEXT NOT NULL,
            price REAL NOT NULL,
            quantity INTEGER NOT NULL,
            size TEXT NULL,
            color TEXT NULL,
            is_custom INTEGER DEFAULT 0,
            custom_specs TEXT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
    ");

    // Dynamic schema migration: ensure access_token column exists on orders table
    try {
        $cols = $db->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_ASSOC);
        $hasAccessToken = false;
        foreach ($cols as $col) {
            if (($col['name'] ?? '') === 'access_token') {
                $hasAccessToken = true;
                break;
            }
        }
        if (!$hasAccessToken) {
            $db->exec("ALTER TABLE orders ADD COLUMN access_token TEXT");
        }
    } catch (Throwable $e) {}

    // Seed default admin user if table is empty (supports env config)
    $adminCount = (int)$db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($adminCount === 0) {
        $defaultUser = getenv('ADMIN_USERNAME') ?: 'admin';
        $defaultPass = getenv('ADMIN_PASSWORD') ?: 'admin123';
        $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash, name) VALUES (?, ?, ?)");
        $stmt->execute([$defaultUser, password_hash($defaultPass, PASSWORD_BCRYPT), 'Administrator']);
    }

    // Seed default products if empty
    $prodCount = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($prodCount === 0) {
        $products = [
            [
                'name'            => 'Kaos Kustom Studio TNM',
                'slug'            => 'kaos-kustom-studio-tnm',
                'category'        => 'apparel',
                'price'           => 135000,
                'original_price'  => 185000,
                'stock'           => 500,
                'description'     => 'Kreasikan kaos impianmu sendiri! Bebas pilih model kaos (Regular/Oversize/Longsleeve/Polo), tentukan warna kain, serta drag & drop logo atau desain favoritmu langsung di studio interaktif kami. Menggunakan bahan Premium Cotton Combed 24s/30s dengan sablon DTF HD tajam dan tahan lama.',
                'image'           => 'assets/img/mockups/tshirt-front.png',
                'is_customizable' => 1,
                'is_featured'     => 1,
                'badge'           => 'Bisa Kustom Desain'
            ],
            [
                'name'            => 'Hoodie Streetwear Kustom TNM',
                'slug'            => 'hoodie-streetwear-kustom-tnm',
                'category'        => 'apparel',
                'price'           => 265000,
                'original_price'  => 320000,
                'stock'           => 250,
                'description'     => 'Hoodie pullover premium berbahan Cotton Fleece 330 Gsm tebal, hangat, dan sangat nyaman menemani malam dinginmu. Desain logo atau grafis dapat kamu atur posisinya secara bebas di bagian depan maupun belakang.',
                'image'           => 'assets/img/mockups/hoodie-front.png',
                'is_customizable' => 1,
                'is_featured'     => 1,
                'badge'           => 'Bestseller'
            ],
            [
                'name'            => 'Kaos Regular #MalamMinggu Midnight Radio',
                'slug'            => 'kaos-malam-minggu-midnight-radio',
                'category'        => 'apparel',
                'price'           => 139000,
                'original_price'  => 179000,
                'stock'           => 85,
                'description'     => 'Menemani akhir pekanmu yang penuh cerita hangat dan drama kehidupan. Tipografi retro siaran malam dengan bahan adem yang sangat nyaman dipakai bersantai.',
                'image'           => 'assets/img/products/tshirt-malamminggu.svg',
                'is_customizable' => 0,
                'is_featured'     => 1,
                'badge'           => 'Limited'
            ],
            [
                'name'            => 'Hoodie Pullover Midnight Radio #MalamMinggu - Deep Navy',
                'slug'            => 'hoodie-pullover-midnight-radio-malamminggu-deep-navy',
                'category'        => 'apparel',
                'price'           => 265000,
                'original_price'  => 320000,
                'stock'           => 80,
                'description'     => 'Hoodie pullover edisi khusus siaran malam #MalamMinggu TNM berbahan Heavy Cotton Fleece 330 Gsm warna Deep Navy dengan sablon DTF HD presisi tinggi.',
                'image'           => 'assets/img/mockups/hoodie-front.png',
                'is_customizable' => 0,
                'is_featured'     => 1,
                'badge'           => 'Official Drop'
            ],
            [
                'name'            => 'Topi Snapback Bordir TNM Dark Edition',
                'slug'            => 'topi-snapback-bordir-tnm',
                'category'        => 'accessories',
                'price'           => 85000,
                'original_price'  => 110000,
                'stock'           => 75,
                'description'     => 'Topi snapback bahan twill tebal dengan bordir 3D timbul logo TNM di bagian depan. Pengatur ukuran premium di bagian belakang, pas untuk semua ukuran kepala.',
                'image'           => 'assets/img/products/cap-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 0,
                'badge'           => null
            ],
            [
                'name'            => 'Tote Bag Heavy Canvas "Teman Ngobrol Malam"',
                'slug'            => 'tote-bag-heavy-canvas-tnm',
                'category'        => 'accessories',
                'price'           => 65000,
                'original_price'  => 89000,
                'stock'           => 110,
                'description'     => 'Tas tote bag bahan kanvas greige super tebal dengan resleting penutup utama dan saku dalam untuk gawai/dompet. Sablon awet dengan quotes khas TNM.',
                'image'           => 'assets/img/products/totebag-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 0,
                'badge'           => null
            ],
            [
                'name'            => 'Lanyard Eksklusif TNM + Cardholder Kulit',
                'slug'            => 'lanyard-eksklusif-tnm',
                'category'        => 'accessories',
                'price'           => 45000,
                'original_price'  => 60000,
                'stock'           => 200,
                'description'     => 'Tali lanyard printing dua sisi berkualitas tinggi lengkap dengan stopper putar dan cardholder kulit sintetis premium untuk kartu identitas atau e-money.',
                'image'           => 'assets/img/products/lanyard-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 0,
                'badge'           => null
            ],
            [
                'name'            => 'Sticker Pack Hologram Die-Cut (Isi 12 Pcs)',
                'slug'            => 'sticker-pack-hologram-tnm',
                'category'        => 'collectibles',
                'price'           => 35000,
                'original_price'  => 50000,
                'stock'           => 350,
                'description'     => 'Paket 12 stiker vinyl tahan air dengan lapisan kilau pelangi hologram. Berisi artwork #CeritaSerem, logo TNM, #Perkara, dan kutipan favorit penonton.',
                'image'           => 'assets/img/products/stickers-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 1,
                'badge'           => 'Collectible'
            ],
            [
                'name'            => 'Korek Api Custom Matte Black TNM',
                'slug'            => 'korek-api-custom-matte-black-tnm',
                'category'        => 'collectibles',
                'price'           => 95000,
                'original_price'  => 130000,
                'stock'           => 60,
                'description'     => 'Korek api minyak bergaya Zippo dengan finishing logam matte black dan ukiran presisi logo TNM. Bisa diisi ulang minyak dan batu api.',
                'image'           => 'assets/img/products/lighter-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 0,
                'badge'           => 'Kolektor'
            ],
            [
                'name'            => 'Buku Jurnal Catatan Malam Hardcover',
                'slug'            => 'buku-jurnal-catatan-malam-hardcover',
                'category'        => 'collectibles',
                'price'           => 75000,
                'original_price'  => 95000,
                'stock'           => 80,
                'description'     => 'Buku agenda bersampul tebal (hardcover) warna hitam doff dengan pita pembatas merah. Kertas bookpaper tebal 90 gsm bergaris, cocok untuk menuliskan kisah dan idemu.',
                'image'           => 'assets/img/products/journal-tnm.svg',
                'is_customizable' => 0,
                'is_featured'     => 0,
                'badge'           => null
            ]
        ];

        $stmtP = $db->prepare("
            INSERT OR IGNORE INTO products (name, slug, category, price, original_price, stock, description, image, is_customizable, is_featured, badge)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($products as $p) {
            $stmtP->execute([
                $p['name'], $p['slug'], $p['category'], $p['price'], $p['original_price'],
                $p['stock'], $p['description'], $p['image'], $p['is_customizable'],
                $p['is_featured'], $p['badge']
            ]);
        }
    }

    // Seed Preset Designs for Customizer if empty
    $presetCount = (int)$db->query("SELECT COUNT(*) FROM preset_designs")->fetchColumn();
    if ($presetCount === 0) {
        $presets = [
            ['name' => 'Logo TNM Wordmark', 'category' => 'logo', 'image_path' => 'assets/img/presets/preset-logo-tnm.svg'],
            ['name' => 'Badge Teman Ngobrol Malam', 'category' => 'badge', 'image_path' => 'assets/img/presets/preset-badge-radio.svg'],
            ['name' => 'Grafis #CeritaSerem Tengkorak & Bulan', 'category' => 'artwork', 'image_path' => 'assets/img/presets/preset-ceritaserem.svg'],
            ['name' => 'Grafis #MalamMinggu Kaset Pita', 'category' => 'artwork', 'image_path' => 'assets/img/presets/preset-malamminggu.svg'],
            ['name' => 'Stempel Investigasi #Perkara', 'category' => 'badge', 'image_path' => 'assets/img/presets/preset-perkara.svg'],
            ['name' => 'Tipografi: Cerita Malam Ini', 'category' => 'typography', 'image_path' => 'assets/img/presets/preset-typo-cerita.svg'],
            ['name' => 'Tipografi: Kisah Nyata Bukan Fiktif', 'category' => 'typography', 'image_path' => 'assets/img/presets/preset-typo-kisah.svg'],
            ['name' => 'Emblem Persegi Merah TNM', 'category' => 'logo', 'image_path' => 'assets/img/presets/preset-emblem-red.svg']
        ];

        $stmtD = $db->prepare("INSERT OR IGNORE INTO preset_designs (name, category, image_path) VALUES (?, ?, ?)");
        foreach ($presets as $d) {
            $stmtD->execute([$d['name'], $d['category'], $d['image_path']]);
        }
    }
}

// Format Rupiah Helper
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Slug generator
if (!function_exists('slugify')) {
    function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'n-a');
    }
}

// Sanitize HTML string
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// Cart Session Management & 3D Model Thumbnail Resolution
function getCartItemThumbnail($item) {
    if (!empty($item['snapshot']) && str_starts_with($item['snapshot'], 'data:image/')) {
        return $item['snapshot'];
    }
    if (!empty($item['snapshot']) && file_exists(MARKETPLACE_ROOT . '/' . ltrim($item['snapshot'], '/'))) {
        return $item['snapshot'];
    }

    $image = $item['image'] ?? '';
    $name = strtolower($item['product_name'] ?? '');
    $model = strtolower($item['model'] ?? '');

    // Check if it's a hoodie
    if ($model === 'hoodie' || str_contains($name, 'hoodie') || str_contains($image, 'hoodie')) {
        return 'assets/img/mockups/hoodie-front.png';
    }

    // Check if it's a shirt or custom apparel
    if ($model === 'tshirt' || !empty($item['is_custom']) || str_contains($name, 'kaos') || str_contains($name, 'shirt') || str_contains($image, 'shirt') || str_contains($image, 'tshirt')) {
        return 'assets/img/mockups/tshirt-front.png';
    }

    // Non-apparel product (e.g. tumbler, cap, totebag, etc.)
    if (!empty($image)) {
        return $image;
    }

    return 'assets/img/mockups/tshirt-front.png';
}

function getCart() {
    if (!isset($_SESSION['tnm_cart']) || !is_array($_SESSION['tnm_cart'])) {
        $_SESSION['tnm_cart'] = [];
    }
    // Auto-upgrade any stale 2D SVG paths in active session cart to realistic 3D model mockups
    foreach ($_SESSION['tnm_cart'] as $k => &$it) {
        $img = $it['image'] ?? '';
        $name = strtolower($it['product_name'] ?? '');
        $model = strtolower($it['model'] ?? '');
        if (str_contains($img, 'custom-shirt') || str_contains($img, 'custom-hoodie') || (empty($it['snapshot']) && !empty($it['is_custom']))) {
            if ($model === 'hoodie' || str_contains($name, 'hoodie')) {
                $it['image'] = 'assets/img/mockups/hoodie-front.png';
            } else {
                $it['image'] = 'assets/img/mockups/tshirt-front.png';
            }
        }
    }
    unset($it);
    return $_SESSION['tnm_cart'];
}

function getCartCount() {
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += ($item['qty'] ?? 1);
    }
    return $count;
}

function getCartTotal() {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += ($item['price'] * ($item['qty'] ?? 1));
    }
    return $total;
}

function addToCart($item) {
    if (!isset($_SESSION['tnm_cart'])) {
        $_SESSION['tnm_cart'] = [];
    }

    $cartKey = $item['cart_key'] ?? (
        ($item['product_id'] ?? 'custom') . '_' . 
        ($item['size'] ?? 'L') . '_' . 
        ($item['color'] ?? 'black') . '_' . 
        (isset($item['is_custom']) && $item['is_custom'] ? md5(json_encode($item['custom_specs'] ?? '')) : '')
    );

    if (isset($_SESSION['tnm_cart'][$cartKey])) {
        $_SESSION['tnm_cart'][$cartKey]['qty'] += ($item['qty'] ?? 1);
    } else {
        $item['cart_key'] = $cartKey;
        $_SESSION['tnm_cart'][$cartKey] = $item;
    }
    return $cartKey;
}

function updateCartQty($cartKey, $qty) {
    if (isset($_SESSION['tnm_cart'][$cartKey])) {
        if ($qty <= 0) {
            unset($_SESSION['tnm_cart'][$cartKey]);
        } else {
            $_SESSION['tnm_cart'][$cartKey]['qty'] = $qty;
        }
    }
}

function removeFromCart($cartKey) {
    if (isset($_SESSION['tnm_cart'][$cartKey])) {
        unset($_SESSION['tnm_cart'][$cartKey]);
    }
}

function clearCart() {
    $_SESSION['tnm_cart'] = [];
}

// Admin Authentication Helpers
function isAdminLoggedIn() {
    return !empty($_SESSION['tnm_admin_user']) || !empty($_SESSION['tnm_blog_auth']);
}

function currentAdmin() {
    if (!empty($_SESSION['tnm_admin_user'])) {
        return $_SESSION['tnm_admin_user'];
    }
    if (!empty($_SESSION['tnm_blog_auth'])) {
        return [
            'id'       => 1,
            'username' => $_SESSION['tnm_blog_user'] ?? 'admin',
            'name'     => $_SESSION['tnm_blog_name'] ?? 'Administrator'
        ];
    }
    return null;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SHOP_URL . '/admin/login.php');
        exit;
    }
}
