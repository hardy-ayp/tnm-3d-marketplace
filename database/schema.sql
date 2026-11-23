-- ======================================================================
-- 3D Apparel Marketplace & WebGL Customizer Studio
-- Database Schema (SQLite 3 / MySQL Compatible DDL)
-- ======================================================================

PRAGMA foreign_keys = ON;

-- 1. Administrator Accounts
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    name TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Products Catalog
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

-- 3. Preset Designs & Vector Artworks for 3D Customizer
CREATE TABLE IF NOT EXISTS preset_designs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL,
    image_path TEXT NOT NULL,
    svg_content TEXT NULL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Customer Orders (Sanitized: No personal data hardcoded in repo)
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

-- 5. Order Items & Custom 3D Specifications
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

-- 6. Studio Apparel Drafts
CREATE TABLE IF NOT EXISTS apparel_drafts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    model TEXT NOT NULL DEFAULT 'tshirt',
    fabric TEXT NOT NULL DEFAULT 'combed24s',
    color TEXT NOT NULL DEFAULT '#141414',
    color_name TEXT NOT NULL DEFAULT 'Hitam Pekat',
    front_design TEXT,
    back_design TEXT,
    price REAL DEFAULT 149000,
    original_price REAL DEFAULT 199000,
    stock INTEGER DEFAULT 100,
    badge TEXT DEFAULT 'Official Drop',
    is_featured INTEGER DEFAULT 1,
    description TEXT,
    preview_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Performance Indexes
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_orders_number ON orders(order_number);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);
