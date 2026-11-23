# Project Brief: 3D Apparel Virtual Fitting Room & Marketplace

*Languages: [English](BRIEF.md) | [Bahasa Indonesia](BRIEF_ID.md)*

This project is an open-source initiative welcoming contributions from the community. The overarching goal is to transform this platform into a production-grade, highly secure, and visually stunning 3D interactive apparel marketplace.

We are actively seeking contributions across **three core pillars**:
1. **3D Assets, Modeling & Garment Physics (Top Priority)**
2. **Web Security, Hardening & Vulnerability Prevention**
3. **Checkout Workflow, Cart & System Architecture**

---

## Pillar 1: 3D Assets & Garment Models (Top Priority)

### Objective
Elevate the 3D character and clothing assets from basic static meshes into realistic, anatomically fitted garments with authentic fabric behavior, natural wrinkles, and clean rigging.

### Key Focus Areas:
1. **Garment Construction & Volume:**
   - Model apparel (`tshirt.glb`, `hoodie.glb`, pants/jeans) with realistic thickness (1.5mm - 2.5mm), hem stitching, ribbed collars, and sleeve cuffs.
   - Eliminate flat planes or paper-thin geometry that looks painted onto the skin.
2. **Body Conforming & Clearance:**
   - Align garment geometry to the character's body contours (shoulders, chest, armpits, waist, thighs).
   - Maintain a natural physical offset to permanently eliminate skin clipping during 360-degree rotation and camera zoom.
3. **Cloth Physics & Wrinkle Simulation:**
   - Differentiate fabric weight and draping:
     - Cotton T-Shirts: Soft gravity draping and fine armpit/torso folds.
     - Fleece Hoodies: Thick, structured folds, volumetric kangaroo pouch, and open hood.
     - Denim Jeans: Heavy creasing behind knees, natural whisker lines, and ankle breaks.
   - Bake cloth simulation wrinkles into normal maps or implement shape keys.
4. **Rigging & Weight Painting:**
   - Bind clothing items to the character armature (`assets/models/andri_fullbody_rigged.blend`) with smooth vertex weights that do not spike or collapse.
5. **WebGL Performance:**
   - Keep polygon count between 10,000 and 30,000 triangles per garment.
   - Total scene count under 100,000 triangles to guarantee 60 FPS in Three.js on consumer laptops and mobile devices.

---

## Pillar 2: Web Security, Hardening & Data Protection

### Objective
Perform comprehensive security hardening across the backend and frontend to ensure the application is resilient against common web vulnerabilities (OWASP Top 10) and protects user and store data.

### Key Focus Areas:
1. **Authentication & Session Security:**
   - Review and strengthen admin session management, login rate limiting, and password hashing in `admin/login.php` and `admin/auth.php`.
   - Prevent brute-force, credential stuffing, and session hijacking attacks.
2. **Injection & Input Sanitization:**
   - Audit all SQL queries across `config.php`, `admin/`, and `api/` to guarantee 100% PDO prepared statement coverage with strict typing.
   - Prevent Cross-Site Scripting (XSS) on customer input fields, product names, order details, and customized image labels.
3. **CSRF & State Mutation Protection:**
   - Ensure all cart modifications, order placements, and administrative actions enforce cryptographic CSRF token validation.
4. **Direct Object Reference (IDOR) & Order Privacy:**
   - Ensure customer orders and customized artwork snapshots in `order-success.php` and `api/order.php` can only be viewed using unguessable cryptographic tokens, preventing sequential ID enumeration.
5. **File Upload Hardening:**
   - Validate and sanitize custom artwork uploads (MIME type verification, magic byte checks, file size caps, random naming, and preventing executable file execution in upload directories).
6. **Server & Route Protection:**
   - Review `.htaccess` rules to strictly block public HTTP access to SQLite databases (`.db`), SQL dumps, `.env` files, logs, and sensitive include directories.

---

## Pillar 3: Checkout Workflow, Cart & System Architecture

### Objective
Streamline the purchasing journey, increase checkout reliability, and expand system modularity and admin control.

### Key Focus Areas:
1. **Cart & State Management:**
   - Improve shopping cart responsiveness (`cart.php`, `api/cart.php`, slide-over drawer).
   - Real-time stock validation, quantity updating, and graceful handling of out-of-stock items.
2. **Checkout & Order Flow:**
   - Optimize the customer checkout form (`checkout.php`) for speed and minimal friction.
   - Enhance the automated WhatsApp invoice generator with clearer order breakdowns, shipping estimation, and merchant notification templates.
   - Optional extensible payment gateway adapters (e.g. readying the architecture for modular gateway plug-ins while keeping default zero-dependency mode).
3. **Order Confirmation & Invoicing:**
   - Refine `order-success.php` with printable receipts, downloadable order summaries, or QR code order tracking.
4. **Admin Dashboard & Catalog Operations:**
   - Expand order tracking features in `admin/orders.php` (status filters: Pending, Processing, Shipped, Completed, Cancelled).
   - Improve product catalog management (`admin/products.php`) with multi-image support, stock batch updates, and category controls.
   - Enhance preset artwork manager (`admin/designs.php`) and studio apparel builder.
5. **Database & Infrastructure Scalability:**
   - Maintain seamless zero-config SQLite auto-bootstrapping for local testing.
   - Support optional database drivers (MySQL/PostgreSQL) via configuration flags in `config.php` for larger deployments.

---

## Summary of Deliverables & Acceptance Criteria

Community contributions are welcome in any or all of the three pillars above. Pull requests should adhere to:
1. Clean, documented code following existing conventions.
2. Verification on local PHP built-in server (`php -S localhost:8000`).
3. Zero hardcoded local machine paths, zero leaked credentials, and zero private customer data.
4. 3D contributions must verify cleanly in `fitting-room.php` and `customizer.php`.
