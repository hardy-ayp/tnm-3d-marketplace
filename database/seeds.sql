-- ======================================================================
-- 3D Apparel Marketplace — Initial Clean Seeds
-- Default Admin: admin / admin123 (Change immediately in production)
-- ======================================================================

-- 1. Default Admin Account
INSERT OR IGNORE INTO admin_users (id, username, password_hash, name) 
VALUES (1, 'admin', '$2y$10$Kb5TbezSJGKUnIc1iRdJvuHgDiXWHnaa0OhoygC07QCqL9PNdITka', 'Administrator');

-- 2. Initial Apparel & Merchandise Products
INSERT OR IGNORE INTO products (name, slug, category, price, original_price, stock, description, image, is_customizable, is_featured, badge) VALUES
('Kaos Kustom Studio 3D', 'kaos-kustom-studio-3d', 'apparel', 135000, 185000, 500, 'Kreasikan kaos impianmu sendiri! Bebas pilih model kaos (Regular/Oversize/Longsleeve/Polo), tentukan warna kain, serta drag & drop logo atau desain favoritmu langsung di studio interaktif 3D. Menggunakan bahan Premium Cotton Combed 24s/30s dengan sablon DTF HD tajam dan tahan lama.', 'assets/img/mockups/tshirt-front.png', 1, 1, 'Bisa Kustom Desain'),
('Hoodie Streetwear Kustom 3D', 'hoodie-streetwear-kustom-3d', 'apparel', 265000, 320000, 250, 'Hoodie pullover premium berbahan Cotton Fleece 330 Gsm tebal, hangat, dan sangat nyaman. Desain logo atau grafis dapat kamu atur posisinya secara bebas di bagian depan maupun belakang pada viewport 3D WebGL.', 'assets/img/mockups/hoodie-front.png', 1, 1, 'Bestseller'),
('Kaos Regular Midnight Radio', 'kaos-regular-midnight-radio', 'apparel', 139000, 179000, 85, 'Tipografi retro siaran malam dengan bahan adem yang sangat nyaman dipakai bersantai.', 'assets/img/products/tshirt-malamminggu.svg', 0, 1, 'Limited'),
('Hoodie Pullover Midnight - Deep Navy', 'hoodie-pullover-midnight-deep-navy', 'apparel', 265000, 320000, 80, 'Hoodie pullover edisi khusus berbahan Heavy Cotton Fleece 330 Gsm warna Deep Navy dengan sablon DTF HD presisi tinggi.', 'assets/img/mockups/hoodie-front.png', 0, 1, 'Official Drop'),
('Topi Snapback Bordir Dark Edition', 'topi-snapback-bordir-dark-edition', 'accessories', 85000, 110000, 75, 'Topi snapback bahan twill tebal dengan bordir 3D timbul logo di bagian depan. Pengatur ukuran premium di bagian belakang.', 'assets/img/products/cap-tnm.svg', 0, 0, NULL),
('Tote Bag Heavy Canvas', 'tote-bag-heavy-canvas', 'accessories', 65000, 89000, 110, 'Tas tote bag bahan kanvas greige super tebal dengan resleting penutup utama dan saku dalam untuk gawai/dompet.', 'assets/img/products/totebag-tnm.svg', 0, 0, NULL),
('Lanyard Eksklusif + Cardholder Kulit', 'lanyard-eksklusif-cardholder-kulit', 'accessories', 45000, 60000, 200, 'Tali lanyard printing dua sisi berkualitas tinggi lengkap dengan stopper putar dan cardholder kulit sintetis premium.', 'assets/img/products/lanyard-tnm.svg', 0, 0, NULL),
('Sticker Pack Hologram Die-Cut (12 Pcs)', 'sticker-pack-hologram-die-cut', 'collectibles', 35000, 50000, 350, 'Paket 12 stiker vinyl tahan air dengan lapisan kilau pelangi hologram.', 'assets/img/products/stickers-tnm.svg', 0, 1, 'Collectible'),
('Korek Api Custom Matte Black', 'korek-api-custom-matte-black', 'collectibles', 95000, 130000, 60, 'Korek api minyak bergaya Zippo dengan finishing logam matte black dan ukiran presisi. Bisa diisi ulang minyak dan batu api.', 'assets/img/products/lighter-tnm.svg', 0, 0, 'Kolektor'),
('Buku Jurnal Catatan Hardcover', 'buku-jurnal-catatan-hardcover', 'collectibles', 75000, 95000, 80, 'Buku agenda bersampul tebal (hardcover) warna hitam doff dengan pita pembatas merah. Kertas bookpaper tebal 90 gsm bergaris.', 'assets/img/products/journal-tnm.svg', 0, 0, NULL);

-- 3. Preset Designs for 3D Customizer
INSERT OR IGNORE INTO preset_designs (name, category, image_path) VALUES
('Logo Wordmark', 'logo', 'assets/img/presets/preset-logo-tnm.svg'),
('Badge Radio Emblem', 'badge', 'assets/img/presets/preset-badge-radio.svg'),
('Grafis Horror Bulan Sabit', 'artwork', 'assets/img/presets/preset-ceritaserem.svg'),
('Grafis Kaset Pita Retro', 'artwork', 'assets/img/presets/preset-malamminggu.svg'),
('Stempel Investigasi Khusus', 'badge', 'assets/img/presets/preset-perkara.svg'),
('Tipografi: Cerita Malam Ini', 'typography', 'assets/img/presets/preset-typo-cerita.svg'),
('Tipografi: Kisah Nyata Bukan Fiktif', 'typography', 'assets/img/presets/preset-typo-kisah.svg'),
('Emblem Persegi Merah', 'logo', 'assets/img/presets/preset-emblem-red.svg');
