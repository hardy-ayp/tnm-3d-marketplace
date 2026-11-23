<?php
/**
 * 3D Apparel Marketplace & WebGL Customizer Studio
 * Example Configuration File (config.example.php)
 * 
 * Copy this file to `config.php` or use environment variables to customize your setup.
 */

// Application Environment ('production', 'development', 'local')
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true' || APP_ENV === 'development');

// Store & Brand Configuration
define('STORE_NAME', getenv('STORE_NAME') ?: 'Official Apparel Marketplace');
define('STORE_WHATSAPP', getenv('STORE_WHATSAPP') ?: '6281234567890');
define('CURRENCY_SYMBOL', 'Rp ');

// Database Path Configuration (Zero-Config SQLite)
define('MARKETPLACE_ROOT', __DIR__);
define('DATA_DIR', MARKETPLACE_ROOT . '/data');
define('UPLOADS_DIR', MARKETPLACE_ROOT . '/uploads');
define('DB_FILE', DATA_DIR . '/marketplace.db');

// Default Admin Credentials (Used only on initial first-run database seeding)
define('DEFAULT_ADMIN_USER', getenv('ADMIN_USERNAME') ?: 'admin');
define('DEFAULT_ADMIN_PASS', getenv('ADMIN_PASSWORD') ?: 'admin123');

// Shipping Configuration
define('DEFAULT_SHIPPING_COST', 15000);
define('DEFAULT_COURIER', 'J&T Express');

// Security & Session Settings
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        if ($isSecure) {
            ini_set('session.cookie_secure', '1');
        }
    }
    session_start();
}
