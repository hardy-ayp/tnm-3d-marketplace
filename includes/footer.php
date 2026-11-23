<?php
/**
 * TNM Marketplace — Shared Footer & Cart Drawer Slide-Over
 */
$hasPortal = file_exists(__DIR__ . '/../../index.php');
?>
  <!-- Slide-Over Cart Drawer Backdrop -->
  <div class="cart-backdrop" id="cartDrawerBackdrop">
    <aside class="cart-drawer" aria-label="Keranjang Belanja">
      <div class="cart-header">
        <h3>
          <svg class="ic" aria-hidden="true"><use href="#ph-shopping-bag"></use></svg>
          <span>Keranjang Belanja</span>
        </h3>
        <button type="button" class="cart-close-btn" id="cartCloseBtn" aria-label="Tutup Keranjang"></button>
      </div>

      <!-- Items List -->
      <div class="cart-items-list" id="cartItemsList">
        <!-- Rendered dynamically via marketplace.js -->
      </div>

      <!-- Footer & Checkout CTA -->
      <div class="cart-footer">
        <div class="cart-subtotal-row">
          <span style="color:var(--muted);font-weight:600;">Subtotal</span>
          <span class="cart-subtotal-val" id="cartSubtotalAmount">Rp 0</span>
        </div>
        <a href="checkout.php" class="btn red lg" id="btnCartCheckout" style="width:100%;">
          <span>Lanjut ke Checkout</span>
          <svg class="ic" aria-hidden="true"><use href="#ph-arrow-right"></use></svg>
        </a>
      </div>
    </aside>
  </div>

  <!-- Toast Notification Container -->
  <div class="toast-container"></div>

  <!-- Shared Footer -->
  <footer class="shop-footer">
    <div class="wrap">
      <div class="footer-grid">
        <div class="footer-col">
          <a href="index.php" class="mark" style="margin-bottom:12px;">
            TNM<i></i>
            <span class="mark-sub">MARKET</span>
          </a>
          <p style="color:var(--muted);font-size:13.5px;max-width:34ch;line-height:1.6;margin-top:8px;">
            Merchandise resmi dan apparel kustom eksklusif persembahan channel <b>TNM (Teman Ngobrol Malam)</b>. Dibuat dengan material premium untuk menemani setiap malammu.
          </p>
        </div>

        <div class="footer-col">
          <h4>Kategori</h4>
          <ul>
            <li><a href="customizer.php">Studio Kustom Baju</a></li>
            <li><a href="catalog.php?cat=apparel">Kaos & Hoodie</a></li>
            <li><a href="catalog.php?cat=accessories">Aksesoris Eksklusif</a></li>
            <li><a href="catalog.php?cat=collectibles">Koleksi Khusus</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Bantuan & Layanan</h4>
          <ul>
            <li><a href="<?= $hasPortal ? '../#tentang' : '#tentang' ?>">Tentang TNM</a></li>
            <li><a href="catalog.php">Panduan Belanja</a></li>
            <li><a href="customizer.php">Panduan Ukuran (Size Chart)</a></li>
            <li><a href="https://api.whatsapp.com/send?phone=6281234567890&text=Halo%20Admin%20TNM" target="_blank" rel="noopener">WhatsApp CS</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Navigasi Ekosistem</h4>
          <ul>
            <?php if ($hasPortal): ?>
              <li><a href="../">Beranda Portal TNM</a></li>
              <?php if (is_dir(__DIR__ . '/../../vote')): ?>
                <li><a href="../vote/">Platform Voting Cerita</a></li>
              <?php endif; ?>
            <?php else: ?>
              <li><a href="index.php">Beranda Toko</a></li>
              <li><a href="catalog.php">Katalog Merchandise</a></li>
            <?php endif; ?>
            <li><a href="https://www.youtube.com/@andriTNM" target="_blank" rel="noopener">YouTube @andriTNM</a></li>
            <li><a href="admin/">Login Admin Toko</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
          <span>&copy; <?= date('Y') ?> Official Marketplace TNM (@andriTNM). All rights reserved.</span>
          <!-- Theme Switcher Segment (from indexmockup.html) -->
          <div class="seg" role="group" aria-label="Pilihan Tema Tampilan">
            <button id="t-light" aria-pressed="false" type="button">
              <svg class="ic" style="width:14px;height:14px;"><use href="#ph-sun"></use></svg>
              <span>Terang</span>
            </button>
            <button id="t-dark" aria-pressed="true" type="button">
              <svg class="ic" style="width:14px;height:14px;"><use href="#ph-moon"></use></svg>
              <span>Gelap</span>
            </button>
          </div>
        </div>
        <span>Secure Checkout • Kualitas Bahan Terjamin • Pengiriman Seluruh Indonesia</span>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="assets/js/marketplace.js"></script>
  <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
    <?php foreach ($extraScripts as $s): ?>
      <script src="<?= e($s) ?>"></script>
    <?php endforeach; ?>
  <?php elseif (isset($extraJs)): ?>
    <script src="<?= e($extraJs) ?>"></script>
  <?php endif; ?>
</body>
</html>
