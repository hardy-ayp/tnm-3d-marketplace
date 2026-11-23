# Brief Proyek: 3D Apparel Virtual Fitting Room & Marketplace

*Bahasa: [English](BRIEF.md) | [Bahasa Indonesia](BRIEF_ID.md)*

Proyek ini adalah inisiatif sumber terbuka (open-source) yang membuka kesempatan luas bagi komunitas pengembang, 3D artist, dan praktisi keamanan web untuk berkontribusi.

Tujuan utama proyek ini adalah menyempurnakan platform toko pakaian interaktif 3D ini agar memiliki standar produksi tinggi, aman, andal, dan memiliki visual 3D yang realistis.

Kami menyambut kontribusi dalam **tiga pilar utama**:
1. **Aset 3D, Pemodelan Pakaian & Fisika Kain (Prioritas Utama)**
2. **Keamanan Web, Pengerasan Sistem & Pencegahan Kerentanan**
3. **Alur Checkout, Sistem Keranjang & Arsitektur Toko**

---

## Pilar 1: Aset 3D & Pemodelan Pakaian (Prioritas Utama)

### Sasaran
Meningkatkan kualitas model 3D karakter dan pakaian dari mesh statis kaku menjadi pakaian nyata yang membungkus anatomi tubuh secara proporsional dengan karakteristik kain alami, kerutan realistis, dan rigging yang luwes.

### Fokus Utama Pengerjaan:
1. **Konstruksi Geometri & Volume Pakaian:**
   - Memodelkan pakaian (`tshirt.glb`, `hoodie.glb`, celana jeans) dengan ketebalan fisik nyata (sekitar 1,5 mm - 2,5 mm), jahitan keliman, rusuk kerah leher, dan manset lengan.
   - Menghilangkan bidang datar tipis (plane) atau poligon yang sekadar tampak seperti cat di atas kulit tubuh.
2. **Kesesuaian Anatomi (Body Conforming) & Celah Bebas:**
   - Menyelaraskan kontur pakaian dengan lekuk tubuh karakter (bahu, dada, ketiak, pinggang, paha).
   - Mempertahankan jarak bebas (offset) yang aman antara pakaian dan kulit untuk melenyapkan masalah tembus kulit (skin clipping) saat kamera berputar 360 derajat.
3. **Fisika Kain & Kerutan Realistis (Cloth Simulation):**
   - Membedakan massa dan jatuhnya kain:
     - Kaos Cotton: Jatuh mengikuti gravitasi dengan kerutan halus di torso dan ketiak.
     - Hoodie Fleece: Siluet tebal bervolume, lipatan struktural di lengan, saku kanguru menggembung, dan tudung berongga alami.
     - Celana Jeans: Lipatan khas di selangkangan (whiskering), lipatan di belakang lutut, dan tumpukan kain di atas sepatu.
   - Melakukan baking kerutan simulasi kain ke dalam peta normal (normal maps) atau menggunakan shape keys / morph targets.
4. **Rigging & Weight Painting:**
   - Mengikat pakaian ke armature karakter (`assets/models/andri_fullbody_rigged.blend`) dengan bobot simpul (vertex weights) halus agar tidak rusak atau tertarik ekstrem saat bergerak.
5. **Performa WebGL:**
   - Mempertahankan jumlah poligon antara 10.000 hingga 30.000 triangles per pakaian.
   - Total scene viewport di bawah 100.000 triangles untuk menjamin kecepatan stabil 60 FPS pada Three.js di perangkat laptop dan ponsel pengguna.

---

## Pilar 2: Keamanan Web, Pengerasan Sistem & Perlindungan Data

### Sasaran
Melakukan audit dan pengerasan keamanan menyeluruh pada sisi backend (PHP) maupun frontend agar kebal terhadap kerentanan umum web (OWASP Top 10) serta menjaga kerahasiaan data pembeli dan pemilik toko.

### Fokus Utama Pengerjaan:
1. **Autentikasi & Keamanan Sesi Admin:**
   - Memperkuat manajemen sesi admin, pembatasan percobaan login (rate limiting), dan pengacakan password (bcrypt) di `admin/login.php` dan `admin/auth.php`.
   - Mencegah serangan brute-force, credential stuffing, dan session hijacking.
2. **Pencegahan Injeksi & Sanitasi Input:**
   - Mengaudit seluruh query database pada `config.php`, `admin/`, dan `api/` untuk memastikan kepatuhan 100% PDO prepared statements dengan parameter binding yang ketat.
   - Mencegah Cross-Site Scripting (XSS) pada formulir pelanggan, nama produk, detail pesanan, dan label desain custom.
3. **Perlindungan CSRF & Mutasi Data:**
   - Memastikan seluruh mutasi keranjang belanja, pengiriman pesanan, dan aksi admin divalidasi dengan token kriptografis CSRF yang valid.
4. **Pencegahan Akses Ilegal Pesanan (IDOR):**
   - Memastikan halaman invoice `order-success.php` dan API pesanan hanya dapat diakses menggunakan token unik acak (cryptographic access token), bukan ID sekuensial yang mudah ditebak.
5. **Pengamanan Unggahan Berkas (Upload Hardening):**
   - Validasi ketat pada unggahan gambar kustomizer (verifikasi MIME type asli, pemeriksaan magic bytes, pembatasan ukuran berkas, penamaan acak, serta pencegahan eksekusi skrip di folder upload).
6. **Perlindungan Server & Direktori:**
   - Mengoptimalkan aturan `.htaccess` untuk memblokir akses publik langsung ke berkas database SQLite (`.db`), dump SQL, berkas `.env`, log, dan folder include internal.

---

## Pilar 3: Alur Checkout, Sistem Keranjang & Arsitektur Toko

### Sasaran
Menyempurnakan alur pengalaman belanja pengguna (customer journey), meningkatkan kehandalan transaksi pesanan, serta memperkaya kontrol admin.

### Fokus Utama Pengerjaan:
1. **Pengelolaan Keranjang Belanja (Cart Management):**
   - Meningkatkan responsivitas keranjang (`cart.php`, `api/cart.php`, dan slide-over drawer).
   - Validasi stok real-time, pembaruan kuantitas produk yang mulus, serta penanganan otomatis jika stok produk habis.
2. **Alur Checkout & Pemesanan:**
   - Mengoptimalkan formulir checkout (`checkout.php`) agar cepat, ringkas, dan minim hambatan.
   - Menyempurnakan pembuat invoice otomatis ke WhatsApp dengan rincian rincian pesanan yang rapi, estimasi ongkos kirim, dan template konfirmasi ke penjual.
   - Menyiapkan arsitektur pembayaran modular (opsional untuk plugin gateway pembayaran masa depan sambil tetap menjaga mode default zero-dependency).
3. **Konfirmasi & Bukti Pembelian:**
   - Mempercantik halaman `order-success.php` dengan cetak struk (print-ready receipt), ringkasan unduhan, atau QR code verifikasi status pesanan.
4. **Dashboard Admin & Manajemen Katalog:**
   - Memperluas fitur pelacakan pesanan di `admin/orders.php` (filter status: Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan).
   - Meningkatkan manajemen katalog produk (`admin/products.php`) dengan dukungan multi-gambar, update stok massal, dan pengaturan variasi.
   - Memperbarui pengelola desain preset (`admin/designs.php`) dan studio apparel builder.
5. **Skalabilitas Basis Data:**
   - Mempertahankan kemudahan auto-bootstrap SQLite tanpa konfigurasi untuk uji coba lokal.
   - Menyediakan opsi dukungan database eksternal (MySQL/PostgreSQL) melalui konfigurasi lingkungan (`config.php`) untuk skala hosting yang lebih besar.

---

## Ringkasan Kriteria Penerimaan Kontribusi

Kontribusi dari komunitas diterima pada salah satu maupun gabungan ketiga pilar di atas. Setiap Pull Request diharapkan memenuhi standar:
1. Kode bersih, terstruktur, dan mengikuti konvensi yang ada.
2. Telah diuji pada server lokal PHP built-in (`php -S localhost:8000`).
3. Bebas dari hardcoded path lokal mesin, kredensial privat, dan data pelanggan riil.
4. Kontribusi aset 3D harus teruji berjalan lancar pada [fitting-room.php](file:///c:/laragon/www/tnm/marketplace/fitting-room.php) dan `customizer.php`.
