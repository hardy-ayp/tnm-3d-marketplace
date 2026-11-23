# 3D Apparel Studio and E-Commerce Marketplace

An open-source, web-based 3D interactive custom apparel studio and merchandise e-commerce platform built with Three.js (WebGL), PHP, and SQLite.

This repository contains the standalone marketplace and customization studio module, completely decoupled from any external portal dependencies. It is self-contained, portable, and ready for deployment or community contributions.

For detailed development goals, technical requirements, and project scope for 3D artists and developers, please read the [Project Brief](BRIEF.md).

---

## Key Features

1. **Interactive 3D WebGL Customizer (`customizer.php`)**
   - Real-time 3D viewport powered by Three.js and GLTFLoader.
   - Supports realistic apparel models (`tshirt.glb`, `hoodie.glb`) with realistic fabric draping.
   - Dynamic graphic stamping: drag, resize, rotate, and position vector badges or uploaded artworks.
   - Dual-side view switching (Front / Back) with smooth camera transitions.
   - Real-time fabric color picker and material roughness simulation.
   - Client-side 2D/3D snapshot generator for order thumbnails and production mockups.

2. **3D Character Virtual Fitting Room (`fitting-room.php`)**
   - Humanoid 3D character avatar (`andri_fullbody_rigged.glb`) with rigged skeleton and realistic proporstions.
   - Modular wardrobe switching between T-Shirts, Hoodies, Jeans, and modular accessories (`accessory_slingbag.glb`).
   - Dynamic camera presets: Full Body, Close-Up Portrait, Torso Apparel, and Shoes.
   - Studio lighting controls: Studio Warm, Midnight Blue, and Crimson Horror modes.
   - High-resolution reference photos (`andri1.png`, `andri2.png`, `andriuwo.png`) and facial texture maps included in `assets/img/references/` for community 3D modellers and riggers.

3. **Merchandise Storefront (`index.php`, `catalog.php`, `product.php`)**
   - Responsive modern UI supporting both dark and light modes with anti-FOUC state restoration.
   - Product catalog with category filtering (Apparel, Accessories, Collectibles).
   - Detailed product view with size selector, color variations, stock counter, and image gallery.

4. **Cart & Secure Checkout (`cart.php`, `checkout.php`, `order-success.php`)**
   - Slide-over cart drawer and dedicated cart page.
   - CSRF token verification across all cart mutations and order submissions.
   - Order verification via cryptographically secure per-order access tokens to prevent IDOR attacks.
   - Automated WhatsApp invoice dispatch generator for direct merchant confirmation.

5. **Standalone Admin Dashboard (`admin/`)**
   - Self-contained authentication with brute-force rate-limiting and bcrypt password hashing.
   - Sales analytics and KPI tracking (Revenue, Orders, Customizer volume, Catalog count).
   - Order management with customizer design snapshot inspection.
   - Product CRUD management (pricing, stock, image upload, customizability toggles).
   - Preset artwork manager for the 3D customizer studio.
   - Studio Apparel Builder for creating pre-designed drops.
   - Administrative password change module.

6. **Zero-Configuration SQLite Database**
   - Automatic database file generation and schema initialization on first run.
   - Bundled with sample merchandise and vector preset artworks.
   - Production-ready foreign key constraints, indexes, and write-ahead logging (WAL) PRAGMA optimizations.

---

## Architecture Overview

```
User Browser (WebGL / Canvas)
    |
    +---> Three.js Engine (OrbitControls, GLTFLoader, Decal Canvas)
    |         |
    |         +---> assets/models/*.glb (3D Mesh Geometry)
    |         +---> assets/img/presets/*.svg (Decal Artwork)
    |
    +---> REST API Endpoints (api/cart.php, api/order.php)
              |
              +---> PHP Session & CSRF Guard
              |
              +---> SQLite Database (data/marketplace.db)
```

---

## Directory Structure

```
marketplace/
├── .htaccess                 # Apache routing and file access security
├── .gitignore                # Git ignore rules for database and user uploads
├── .env.example              # Environment variables template
├── config.php                # Database bootstrapper and application settings
├── config.example.php        # Reference configuration template
├── index.php                 # Marketplace storefront landing page
├── catalog.php               # Product catalog with category filter
├── product.php               # Individual product details page
├── customizer.php            # Three.js 3D WebGL apparel customizer studio
├── fitting-room.php          # 3D Character virtual fitting room & wardrobe simulator
├── cart.php                  # Shopping cart management
├── checkout.php              # Shipping form and payment checkout
├── order-success.php         # Order confirmation with cryptographic token
├── check_health.php          # Server environment diagnostic tool
├── admin/                    # Administrative management panel
│   ├── index.php             # Admin overview and KPI dashboard
│   ├── login.php             # Standalone admin login page
│   ├── logout.php            # Admin session termination
│   ├── change_password.php   # Admin password update form
│   ├── products.php          # Product catalog CRUD management
│   ├── orders.php            # Order fulfillment and design viewer
│   ├── designs.php           # Preset artwork manager
│   ├── apparel-builder.php   # Studio apparel creator
│   ├── auth.php              # Admin authentication guard
│   └── includes/             # Shared admin header and drawer
├── api/                      # Backend AJAX API handlers
│   ├── cart.php              # Cart item manipulation (add, update, remove)
│   └── order.php             # Order placement and validation
├── assets/
│   ├── css/                  # Marketplace, fitting room and customizer stylesheets
│   ├── js/                   # Frontend logic and customizer engine
│   │   ├── customizer.js     # Three.js customizer logic
│   │   ├── marketplace.js    # Storefront theme and cart UI
│   │   └── vendor/           # Three.js ES module & addons, OrbitControls, GLTFLoader
│   ├── img/                  # Product graphics, presets, mockups & character references
│   │   ├── andri1.png        # Character reference photo 1
│   │   ├── andri2.png        # Character reference photo 2
│   │   ├── andriuwo.png      # Character reference photo 3
│   │   └── references/       # High-res face textures & reference portraits
│   └── models/               # 3D assets (.glb / .blend)
│       ├── andri_fullbody_rigged.glb   # Rigged humanoid character model
│       ├── andri_fullbody_rigged.blend # Master Blender project file
│       ├── tshirt.glb                  # Realistic cotton t-shirt mesh
│       ├── hoodie.glb                  # Realistic streetwear fleece hoodie
│       └── accessory_slingbag.glb      # Modular slingbag accessory
├── data/                     # SQLite database storage (ignored by git)
├── database/                 # SQL schemas and seed files
│   ├── schema.sql            # Table definitions and indexes
│   └── seeds.sql             # Default products and preset artworks
└── uploads/                  # User customized snapshots (ignored by git)
```

---

## Installation & Quick Start

### Prerequisites
- PHP 7.4 or PHP 8.x
- PHP Extensions: `pdo`, `pdo_sqlite`, `json`, `mbstring`, `gd`
- Web Server: Apache (with `mod_rewrite`), Nginx, or PHP Built-in Server

### Option A: Using PHP Built-in Web Server
1. Clone the repository:
   ```bash
   git clone https://github.com/hardy-ayp/tnm-3d-marketplace.git
   cd tnm-3d-marketplace
   ```
2. Start the local server:
   ```bash
   php -S localhost:8000
   ```
3. Open your browser at `http://localhost:8000`. The database will automatically initialize on the first request.

### Option B: Using Laragon or XAMPP
1. Move or clone the directory into your server document root:
   - Laragon: `C:\laragon\www\marketplace`
   - XAMPP: `C:\xampp\htdocs\marketplace`
2. Ensure directory write permissions for:
   - `data/`
   - `uploads/`
3. Access the application in your browser:
   - `http://localhost/marketplace/`

---

## Administrator Access

- **Login URL:** `http://localhost/marketplace/admin/login.php`
- **Default Username:** `admin`
- **Default Password:** `admin123`

*Security Notice:* Change the default administrator password immediately after first login via the `Ganti Password` menu.

---

## Contributing 3D Models

We welcome community contributions of new 3D apparel and merchandise assets (e.g., jackets, caps, jerseys, tote bags).

### 3D Asset Guidelines
1. **File Format:** Binary glTF (`.glb`).
2. **Scale & Units:** Metric meters (1 Blender unit = 1 meter). Centered at origin `(0, 0, 0)`.
3. **Polygon Budget:** Keep vertex count between 10,000 and 60,000 vertices for smooth browser rendering.
4. **UV Unwrapping:** Clean, non-overlapping UV layout on UVMap channel 0.
5. **Materials:** PBR Metallic-Roughness workflow with embedded textures.
6. **Decal Projection Target:** Ensure front and back torso meshes are properly named (e.g., `tshirt_mesh`, `hoodie_body`) for raycasting decal attachments.

---

## Security Considerations

- All database operations utilize PDO prepared statements with strict parameter binding.
- Forms and AJAX state mutations require valid CSRF tokens.
- Order access is protected using cryptographically random access tokens rather than sequential IDs.
- Direct web access to `data/`, `includes/`, and sensitive extensions (`.db`, `.sql`, `.log`, `.env`) is blocked via `.htaccess`.
- Binary databases and uploaded customer files are excluded from git version control via `.gitignore`.

---

## License

This project is licensed under the permissive open-source license detailed in the [LICENSE](LICENSE) file.
