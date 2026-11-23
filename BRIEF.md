# Project Brief: 3D Apparel Virtual Fitting Room & Marketplace

Dokumen ini menjelaskan latar belakang, tujuan, kebutuhan teknis (requirements), serta batasan ruang lingkup (scope) proyek untuk kontributor, 3D artist, dan web developer.

---

## 1. Project Overview & Background

### Latar Belakang
Modul ini adalah platform e-commerce merchandise dan studio apparel interaktif berbasis WebGL (Three.js) dan PHP/SQLite. Fitur unggulannya adalah **Virtual Fitting Room** (`fitting-room.php`), di mana pengguna dapat melihat karakter humanoid 3D (Andri) mencoba berbagai pakaian (T-Shirt, Hoodie, Celana/Jeans, dan Aksesori).

### Masalah Saat Ini
Aset pakaian 3D yang saat ini terpasang masih terlihat kaku dan menyerupai objek solid yang sekadar ditempelkan di atas permukaan kulit karakter. Pakaian belum memiliki volume fisik yang meyakinkan, lipatan/kerutan kain alami (cloth folds/wrinkles), dan deformasi yang luwes saat karakter berpose atau bergerak.

### Tujuan Utama
Membangun dan menyempurnakan sistem pakaian 3D (Garment Mesh, Cloth Physics/Simulation, Body Conforming, dan Rigging) agar pakaian terlihat benar-benar dikenakan oleh tubuh manusia secara proporsional dan realistis saat dirender di browser melalui Three.js.

---

## 2. Technical & Quality Requirements

### A. 3D Modeling & Garment Construction
1. **Garment Geometry & Volume:**
   - Pakaian harus dibuat sebagai geometri terpisah dengan ketebalan (thickness) wajar, bukan plane datar atau tekstur yang ditempel di kulit.
   - Memiliki struktur pakaian nyata: garis jahitan (seams), keliman (hems), kerah (collar), dan manset (cuffs) yang terdefinisi dengan rapi.
2. **Body Conforming (Kesesuaian Anatomi):**
   - Geometri pakaian harus mengikuti kontur anatomi karakter: bahu, dada, ketiak, punggung, pinggang, selangkangan, dan paha.
   - Wajib ada celah jarak wajar (offset) antara kain dan kulit tubuh untuk mencegah tembus (skin clipping).
3. **Karakteristik Material & Lipatan Kain:**
   - Memperlihatkan lipatan dan kerutan kain yang alami sesuai karakteristik bahan:
     - T-Shirt (Cotton): Lembut, jatuh mengikuti tubuh, lipatan halus di ketiak dan pinggang.
     - Hoodie (Fleece/Heavy Cotton): Bervolume tebal, lipatan tegas di lengan, saku kanguru bervolume, tudung kepala (hood) memiliki rongga natural.
     - Celana (Denim): Kain kokoh dengan lipatan khas di area selangkangan dan lekuk lutut.
4. **Rigging & Weight Painting:**
   - Setiap pakaian harus memiliki skinning / vertex weight yang terikat harmonis dengan armature karakter (`andri_fullbody_rigged`).
   - Tidak ada poligon yang saling bertabrakan atau merenggang rusak saat pose kamera berubah.

### B. WebGL & Browser Optimization
1. **Format Output:** Binary glTF (`.glb`) dengan master file Blender (`.blend`) terdokumentasi.
2. **Polygon Budget:**
   - Karakter tubuh: ~20.000 - 30.000 tris.
   - Pakaian (T-shirt/Hoodie/Celana): ~10.000 - 30.000 tris per item.
   - Total scene dalam viewport browser tidak melebihi 100.000 tris agar berjalan lancar pada 60 FPS di perangkat mobile dan laptop standar.
3. **PBR Materials & Textures:**
   - Menggunakan PBR Metallic-Roughness workflow standar.
   - Resolusi tekstur optimal: 1024x1024 hingga maksimal 2048x2048 (format PNG/WebP terkompresi).
4. **Decal / Stamping Compatibility:**
   - Mesh bagian depan dan belakang baju harus memiliki UV layout rapi (channel 0, non-overlapping) agar sistem penempelan sablon/decal di `customizer.php` dan `fitting-room.php` bekerja akurat.

---

## 3. Project Scope (Ruang Lingkup)

### In-Scope (Fokus Pengerjaan)
1. **Penyempurnaan Aset 3D Pakaian:**
   - Memperbaiki atau memodelkan ulang mesh pakaian:
     - `assets/models/tshirt.glb`
     - `assets/models/hoodie.glb`
     - Mesh celana panjang / jeans pada `andri_fullbody_rigged.glb`
   - Melakukan baking lipatan kain (normal map / baked cloth simulation) atau morph targets / blendshapes.
   - Weight transfer dan skinning pakaian ke armature karakter pada `assets/models/andri_fullbody_rigged.blend`.
2. **Integrasi Viewport Three.js:**
   - Memastikan model `.glb` baru terpanggil sempurna di `fitting-room.php` tanpa clipping atau visual error.
   - Optimasi pencahayaan dan material loader di Three.js jika dibutuhkan penyesuaian roughness/metalness.
3. **Dokumentasi & Reproducibility:**
   - Mencatat parameter modifier Blender atau teknik simpan yang digunakan agar dapat diterapkan pada variasi pakaian berikutnya.

### Out-of-Scope (Bukan Bagian Proyek Ini)
1. **Backend Portal Utama TNM:**
   - Seluruh logika website pusat (artikel, feed media sosial, CMS blog) di luar folder `marketplace/` berada di luar cakupan.
2. **Migrasi Framework Frontend:**
   - Arsitektur proyek menggunakan Three.js native ES Module, HTML5, dan Vanilla JS/CSS. Tidak diperkenankan merombak struktur dasar menjadi React, Next.js, atau framework berat lainnya karena modul harus tetap mandiri dan ringan.
3. **Server-Side Physics Computing:**
   - Tidak memerlukan server Node.js khusus untuk komputasi physics. Semua rendering dan animasi harus berjalan client-side pada GPU browser via WebGL.
4. **Sistem Payment Gateway Bank Otomatis:**
   - Alur checkout toko saat ini sengaja dirancang menggunakan verifikasi token dan WhatsApp direct order; integrasi payment gateway pihak ketiga (Midtrans, Xendit, dll.) bukan fokus tahap ini.
5. **AR Webcam / Face Tracking:**
   - Viewport interaktif dibatasi pada 3D orbital camera (rotasi, zoom, pan) dan preset kamera, bukan tracking kamera ponsel real-time.

---

## 4. Deliverables & Acceptance Criteria

Kontribusi dianggap selesai dan siap di-merge apabila:
1. File `.glb` pakaian dan master `.blend` telah diperbarui dan diuji di `fitting-room.php`.
2. Pakaian terlihat memiliki ketebalan nyata, lipatan kain proporsional, dan tidak menembus kulit model saat diamati dari sudut 360 derajat.
3. Frame rate rendering di browser stabil di kisaran 60 FPS pada perangkat pengujian umum.
4. Kode pull request bersih dari data privat, kredensial, atau path absolut lokal.
