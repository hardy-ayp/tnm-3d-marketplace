# Brief Proyek: 3D Apparel Virtual Fitting Room & Marketplace

*Bahasa: [English](BRIEF.md) | [Bahasa Indonesia](BRIEF_ID.md)*

Dokumen ini mendefinisikan latar belakang, sasaran, kebutuhan teknis (requirements), serta batasan ruang lingkup (scope) proyek bagi 3D artist, rigger, dan pengembang WebGL yang ingin berkontribusi pada repositori ini.

---

## 1. Ikhtisar & Latar Belakang Proyek

### Konteks
Repositori ini berisi platform e-commerce pakaian dan studio customizer 3D mandiri berbasis Three.js (WebGL), PHP, dan SQLite. Fitur utamanya adalah **Virtual Fitting Room** (`fitting-room.php`), yang merender avatar 3D humanoid ber-rigging (Andri) yang dapat mencoba pakaian modular seperti Kaos (T-Shirt), Hoodie, Celana Panjang/Jeans, serta aksesori modular (Tas Selempang).

### Masalah yang Dihadapi Saat Ini
Aset pakaian 3D saat ini masih tampak kaku dan berperilaku layaknya benda padat yang sekadar ditempelkan pada permukaan kulit tubuh karakter. Pakaian belum memiliki volume fisik yang meyakinkan, lipatan/kerutan kain alami (cloth folds/wrinkles), serta deformasi organik ketika sudut pandang kamera berputar atau karakter berpose.

### Sasaran Utama
Membangun dan mematangkan kembali sistem pakaian 3D (Garment Mesh Geometry, Cloth Physics/Simulation, Body Conforming, dan Rigging/Weight Painting) agar pakaian terlihat benar-benar membungkus tubuh karakter secara proporsional dengan lekukan dan sifat kain yang realistis saat dirender di browser via Three.js.

---

## 2. Kebutuhan Teknis & Standar Kualitas (Requirements)

### A. Konstruksi Geometri & 3D Modeling Pakaian
1. **Ketebalan & Volume Fisik Pakaian:**
   - Pakaian harus dibuat sebagai geometri pakaian terpisah dengan ketebalan fisik yang wajar (sekitar 1,5 mm - 2,5 mm), bukan berupa bidang datar tipis (plane) atau kulit tubuh yang hanya digandakan poligonnya.
   - Menyertakan detail jahitan yang nyata: kerutan rusuk kerah leher (collar rib), manset lengan (cuffs), keliman bawah (hems), dan garis jahitan sambungan (seams).
2. **Kesesuaian Anatomi Tubuh (Body Conforming):**
   - Geometri pakaian harus mengikuti kontur lekuk tubuh karakter: bahu, dada, ketiak, punggung, pinggang, selangkangan, dan paha.
   - Wajib menjaga celah jarak bebas (offset) yang aman antara sisi dalam pakaian dan permukaan kulit karakter untuk mencegah tembus (skin clipping).
3. **Karakteristik & Dinamika Lipatan Kain:**
   - Membedakan massa dan sifat jatuh masing-masing jenis bahan:
     - **Kaos (Cotton):** Bahan lembut, jatuh mengikuti gravitasi tubuh dengan kerutan halus alami di sekitar dada dan ketiak.
     - **Hoodie (Fleece / French Terry Tebal):** Siluet tebal bervolume, lipatan tegas pada lengan, saku depan kanguru yang menggembung nyata, serta tudung kepala (hood) yang memiliki rongga terbuka alami.
     - **Celana Panjang (Denim Tebal):** Struktur kain kokoh dengan lipatan khas (whiskering) di area selangkangan, lipatan di belakang lutut, serta tumpukan kain wajar di atas sepatu (ankle stacking).
4. **Rigging & Weight Painting:**
   - Setiap pakaian harus memiliki pembagian bobot simpul (vertex weights) yang bersih agar dapat berdeformasi secara luwes mengikuti armature tulang karakter (`andri_fullbody_rigged`).
   - Simpul/vertex tidak boleh tertarik ekstrem (spike), gepeng, atau kolaps saat viewport diputar.

### B. Optimasi WebGL & Performa Browser
1. **Format Berkas:** Binary glTF (`.glb`) yang diekspor bersama berkas master proyek Blender (`.blend`) yang rapi dan terstruktur.
2. **Budget Poligon (Polygon Count):**
   - Tubuh Karakter: Sekitar 20.000 - 30.000 triangles.
   - Pakaian (Kaos, Hoodie, atau Celana): Sekitar 10.000 - 30.000 triangles per item.
   - Total scene viewport keseluruhan tidak boleh melebihi 100.000 triangles guna menjamin performa stabil 60 FPS pada perangkat mobile dan laptop standar pengguna.
3. **PBR Materials & Peta Tekstur:**
   - Menggunakan alur kerja PBR Metallic-Roughness standar.
   - Resolusi tekstur (BaseColor, Normal, Roughness) antara 1024x1024 hingga maksimal 2048x2048 dalam format PNG atau WebP terkompresi.
4. **Kompatibilitas Sablon / Decal:**
   - Pemetaan UV bagian depan dan belakang dada/torso harus rapi pada UV channel 0 (non-overlapping dan minim distorsi), sehingga sistem penempelan sablon/vektor di `customizer.php` dan `fitting-room.php` bekerja akurat.

---

## 3. Ruang Lingkup Proyek (Scope)

### In-Scope (Fokus Utama Pengerjaan)
1. **Penyempurnaan & Pemodelan Ulang Aset 3D Pakaian:**
   - Memperbaiki atau membangun ulang geometri pakaian:
     - `assets/models/tshirt.glb`
     - `assets/models/hoodie.glb`
     - Mesh celana panjang / jeans pada `assets/models/andri_fullbody_rigged.glb`
   - Menerapkan kerutan kain nyata melalui baking simulasi kain (baked cloth simulation / normal map high-to-low) atau shape keys / morph targets.
   - Melakukan weight painting dan pengikatan pakaian ke armature pada berkas `assets/models/andri_fullbody_rigged.blend`.
2. **Integrasi Viewport Three.js:**
   - Memastikan berkas `.glb` yang diekspor dapat dimuat tanpa kendala di `fitting-room.php` tanpa ada bagian tubuh yang tembus keluar (clipping), material hilang, atau galat shader.
   - Menyelaraskan pengaturan material Three.js (roughness, metalness) agar karakter visual kain serasi dengan pencahayaan studio yang tersedia.
3. **Dokumentasi Pipeline:**
   - Mencatat alur modifier Blender atau pengaturan ekspor yang digunakan agar pakaian drop berikutnya dapat menggunakan standar yang sama.

### Out-of-Scope (Bukan Bagian Proyek Ini / Jangan Diubah)
1. **Backend Portal Utama TNM:**
   - Seluruh logika website luar, artikel portal, dan database di luar direktori `marketplace/` berada di luar batasan proyek ini.
2. **Perombakan Framework Frontend:**
   - Proyek ini sengaja dibangun menggunakan native Three.js ES Modules, HTML5, dan Vanilla JS/CSS yang ringan. Dilarang merombak proyek menjadi Next.js, React, Vue, atau dependensi node-backend yang rumit.
3. **Engine Physics Berbasis Server:**
   - Tidak memerlukan server backend simulasi terpisah. Seluruh kalkulasi pencahayaan dan render harus berjalan di sisi klien (GPU browser via WebGL).
4. **Integrasi Payment Gateway Bank Otomatis:**
   - Alur pemesanan saat ini dirancang menggunakan verifikasi token kriptografis dan order langsung via WhatsApp. Penambahan Midtrans atau payment gateway pihak ketiga bukan merupakan fokus tahap ini.
5. **AR Webcam / Face & Body Tracking:**
   - Ruang fitting berfokus penuh pada orbital 3D viewport dengan kontrol orbit dan sudut kamera preset, bukan pelacakan gestur tubuh menggunakan kamera ponsel/webcam.

---

## 4. Kriteria Penerimaan Kontribusi (Acceptance Criteria)

Kontribusi dianggap selesai dan siap digabungkan (merge) apabila:
1. Berkas `.glb` pakaian dan master `.blend` telah diperbarui dan berjalan mulus di `fitting-room.php`.
2. Pakaian menunjukkan volume nyata, lipatan kain proporsional, dan tidak menembus kulit model saat dilihat dari berbagai sudut kamera 360 derajat.
3. Viewport browser mampu mempertahankan performa 60 FPS pada perangkat pengujian umum.
4. Pull request tidak memuat kredensial privat, data pelanggan, atau path absolut mesin lokal.
