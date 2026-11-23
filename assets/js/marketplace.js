/**
 * TNM Official Marketplace — Storefront & Cart Engine
 * Pure Vanilla JavaScript • High Performance AJAX Cart & Interactive Drawer
 */

// Global Toast System
window.TnmToast = {
  show(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <svg class="ic" viewBox="0 0 256 256" style="width:16px;height:16px;color:${type === 'success' ? '#2ECC71' : '#D63B29'};">
        ${type === 'success' 
          ? '<path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"></path>' 
          : '<path d="M208.49,191.51a12,12,0,0,1-17,17L128,145,64.49,208.49a12,12,0,0,1-17-17L111,128,47.51,64.49a12,12,0,0,1,17-17L128,111l63.51-63.51a12,12,0,0,1,17,17L145,128Z"></path>'}
      </svg>
      <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(12px)';
      toast.style.transition = 'all 0.3s';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }
};

// Global Cart Manager
window.TnmCart = {
  init() {
    this.drawer = document.getElementById('cartDrawerBackdrop');
    this.closeBtn = document.getElementById('cartCloseBtn');
    this.triggers = document.querySelectorAll('.cart-trigger');
    this.badge = document.getElementById('cartBadgeCount');
    this.itemsList = document.getElementById('cartItemsList');
    this.subtotalEl = document.getElementById('cartSubtotalAmount');
    this.checkoutBtn = document.getElementById('btnCartCheckout');

    this.bindEvents();
    this.refresh();
  },

  bindEvents() {
    this.triggers.forEach(t => {
      t.addEventListener('click', (e) => {
        e.preventDefault();
        this.openDrawer();
      });
    });

    if (this.closeBtn) {
      this.closeBtn.addEventListener('click', () => this.closeDrawer());
    }

    if (this.drawer) {
      this.drawer.addEventListener('click', (e) => {
        if (e.target === this.drawer) this.closeDrawer();
      });
    }

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeDrawer();
    });

    // Delegate Cart item actions (+, -, delete)
    if (this.itemsList) {
      this.itemsList.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const cartKey = btn.dataset.key;
        if (!cartKey) return;

        if (btn.classList.contains('qty-plus')) {
          const currentQty = parseInt(btn.dataset.qty) || 1;
          await this.updateQty(cartKey, currentQty + 1);
        } else if (btn.classList.contains('qty-minus')) {
          const currentQty = parseInt(btn.dataset.qty) || 1;
          await this.updateQty(cartKey, currentQty - 1);
        } else if (btn.classList.contains('cart-del-btn')) {
          await this.removeItem(cartKey);
        }
      });
    }
  },

  openDrawer() {
    if (this.drawer) this.drawer.classList.add('open');
    document.body.style.overflow = 'hidden';
  },

  closeDrawer() {
    if (this.drawer) this.drawer.classList.remove('open');
    document.body.style.overflow = '';
  },

  async refresh() {
    try {
      const res = await fetch('api/cart.php?action=get');
      const data = await res.json();

      if (data.success) {
        // Update badge
        if (this.badge) {
          this.badge.textContent = data.count;
          this.badge.style.display = data.count > 0 ? 'grid' : 'none';
        }

        // Update Subtotal
        if (this.subtotalEl) {
          this.subtotalEl.textContent = 'Rp ' + data.total.toLocaleString('id-ID');
        }

        // Render Items List
        if (this.itemsList) {
          if (data.items.length === 0) {
            this.itemsList.innerHTML = `
              <div class="cart-empty">
                <svg class="ic" viewBox="0 0 256 256" style="width:48px;height:48px;color:var(--faint);"><path d="M224,88H32a8,8,0,0,0-8,8V208a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V96A8,8,0,0,0,224,88Z" opacity="0.2"></path><path d="M224,80H180a52,52,0,0,0-104,0H32A16,16,0,0,0,16,96V208a24,24,0,0,0,24,24H216a24,24,0,0,0,24-24V96A16,16,0,0,0,224,80ZM128,44a36,36,0,0,1,36,36H92A36,36,0,0,1,128,44Z"></path></svg>
                <p>Keranjang belanja masih kosong.</p>
                <a href="catalog.php" class="btn sm" onclick="window.TnmCart.closeDrawer()">Mulai Belanja</a>
              </div>
            `;
            if (this.checkoutBtn) this.checkoutBtn.classList.add('disabled');
          } else {
            if (this.checkoutBtn) this.checkoutBtn.classList.remove('disabled');

            this.itemsList.innerHTML = data.items.map(item => {
              const nameLower = (item.product_name || '').toLowerCase();
              const modelLower = (item.model || '').toLowerCase();
              const imgLower = (item.image || '').toLowerCase();
              let thumb = item.thumbnail || item.snapshot || item.image;

              // Guarantee 3D Model mockup for apparel and custom items
              if (!thumb || thumb.includes('.svg') || item.is_custom) {
                if (modelLower === 'hoodie' || nameLower.includes('hoodie') || imgLower.includes('hoodie')) {
                  thumb = 'assets/img/mockups/hoodie-front.png';
                } else if (modelLower === 'tshirt' || item.is_custom || nameLower.includes('kaos') || nameLower.includes('shirt')) {
                  thumb = 'assets/img/mockups/tshirt-front.png';
                }
              }
              if (!thumb) thumb = 'assets/img/mockups/tshirt-front.png';

              return `
              <div class="cart-item">
                <div class="cart-item-thumb">
                  <img src="${thumb}" alt="${item.product_name}">
                </div>
                <div class="cart-item-info">
                  <div>
                    <h4 class="cart-item-title">${item.product_name}</h4>
                    <span class="cart-item-meta">
                      ${item.size ? 'Ukuran ' + item.size : ''} 
                      ${item.is_custom ? '· Kustom Baju 3D' : ''}
                    </span>
                  </div>
                  <div class="cart-item-actions">
                    <span class="cart-item-price">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</span>
                    <div class="qty-stepper">
                      <button type="button" class="qty-btn qty-minus" data-key="${item.cart_key}" data-qty="${item.qty}">−</button>
                      <span class="qty-val">${item.qty}</span>
                      <button type="button" class="qty-btn qty-plus" data-key="${item.cart_key}" data-qty="${item.qty}">+</button>
                    </div>
                    <button type="button" class="cart-del-btn" data-key="${item.cart_key}" title="Hapus">
                      <svg class="ic" viewBox="0 0 256 256" style="width:14px;height:14px;"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Z"></path></svg>
                    </button>
                  </div>
                </div>
              </div>
            `;
            }).join('');
          }
        }
      }
    } catch (err) {
      console.error('Cart refresh error:', err);
    }
  },

  getCsrfToken() {
    return window.TNM_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  },

  async updateQty(cartKey, qty) {
    try {
      const token = this.getCsrfToken();
      const res = await fetch('api/cart.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': token
        },
        body: JSON.stringify({ action: 'update', cart_key: cartKey, qty: qty, csrf_token: token })
      });
      const data = await res.json();
      if (data.success) {
        this.refresh();
      }
    } catch (err) {
      console.error(err);
    }
  },

  async removeItem(cartKey) {
    try {
      const token = this.getCsrfToken();
      const res = await fetch('api/cart.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': token
        },
        body: JSON.stringify({ action: 'remove', cart_key: cartKey, csrf_token: token })
      });
      const data = await res.json();
      if (data.success) {
        window.TnmToast.show('Item dihapus dari keranjang.');
        this.refresh();
      }
    } catch (err) {
      console.error(err);
    }
  },

  async addStandardProduct(productId, size = 'L') {
    try {
      const token = this.getCsrfToken();
      const res = await fetch('api/cart.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-Token': token
        },
        body: JSON.stringify({ action: 'add_product', product_id: productId, size: size, qty: 1, csrf_token: token })
      });
      const data = await res.json();
      if (data.success) {
        window.TnmToast.show('Produk berhasil masuk keranjang!');
        this.refresh();
        this.openDrawer();
      } else {
        alert(data.message || 'Gagal menambahkan produk');
      }
    } catch (err) {
      console.error(err);
    }
  }
};

// Initialize Cart on Page Load
document.addEventListener('DOMContentLoaded', () => {
  window.TnmCart.init();

  // Quick Add Button Listener on Product Cards
  document.querySelectorAll('.btn-quick-add').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const prodId = btn.dataset.id;
      window.TnmCart.addStandardProduct(prodId);
    });
  });

  // Category Filtering on Catalog Page
  const catPills = document.querySelectorAll('.cat-pill');
  const prodCards = document.querySelectorAll('.prod-card');
  const searchInput = document.getElementById('catalogSearchInput');
  const emptySearchBox = document.getElementById('catalogEmptySearch');
  const emptyMsgEl = document.getElementById('catalogEmptyMsg');
  const btnResetSearch = document.getElementById('btnResetSearch');

  if (catPills.length > 0 && prodCards.length > 0) {
    function filterProducts() {
      const activeCat = document.querySelector('.cat-pill.active')?.dataset.cat || 'all';
      const query = (searchInput?.value || '').toLowerCase().trim();
      let visibleCount = 0;

      prodCards.forEach(card => {
        const cat = card.dataset.category;
        const isCustom = card.dataset.custom === '1';
        const name = (card.dataset.name || '').toLowerCase();

        let matchCat = false;
        if (activeCat === 'all') {
          matchCat = true;
        } else if (activeCat === 'ready_apparel') {
          matchCat = (cat === 'apparel' && !isCustom);
        } else if (activeCat === 'custom_apparel') {
          matchCat = isCustom;
        } else if (activeCat === 'apparel') {
          matchCat = (cat === 'apparel');
        } else {
          matchCat = (cat === activeCat);
        }

        const matchQuery = (!query || name.includes(query));
        const isVisible = (matchCat && matchQuery);
        card.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
      });

      if (emptySearchBox) {
        if (visibleCount === 0) {
          emptySearchBox.style.display = 'block';
          if (emptyMsgEl) {
            emptyMsgEl.textContent = query 
              ? `Tidak ditemukan produk dengan kata kunci "${query}".`
              : 'Tidak ada produk dalam kategori yang dipilih.';
          }
        } else {
          emptySearchBox.style.display = 'none';
        }
      }
    }

    catPills.forEach(pill => {
      pill.addEventListener('click', () => {
        catPills.forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        filterProducts();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', filterProducts);
    }

    if (btnResetSearch) {
      btnResetSearch.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        catPills.forEach(p => p.classList.remove('active'));
        const allPill = document.querySelector('.cat-pill[data-cat="all"]');
        if (allPill) allPill.classList.add('active');
        filterProducts();
      });
    }
  }

  // Mobile Nav Drawer Toggle
  const mobileToggle = document.getElementById('mobileNavToggle');
  const mobileDrawerBackdrop = document.getElementById('mobileMenuBackdrop');
  const mobileDrawerClose = document.getElementById('mobileMenuClose');

  if (mobileToggle && mobileDrawerBackdrop) {
    function openMobileMenu() {
      mobileDrawerBackdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
      mobileDrawerBackdrop.classList.remove('open');
      document.body.style.overflow = '';
    }

    mobileToggle.addEventListener('click', (e) => {
      e.preventDefault();
      openMobileMenu();
    });

    if (mobileDrawerClose) {
      mobileDrawerClose.addEventListener('click', closeMobileMenu);
    }

    const mobileLinks = mobileDrawerBackdrop.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        closeMobileMenu();
      });
    });

    mobileDrawerBackdrop.addEventListener('click', (e) => {
      if (e.target === mobileDrawerBackdrop) closeMobileMenu();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileDrawerBackdrop.classList.contains('open')) {
        closeMobileMenu();
      }
    });
  }

  // User & Admin Profile Dropdown Pill
  initUserDropdown();

  // Initialize Theme Switcher (Synchronized with TNM Portal)
  initThemeSwitcher();
});

/* ==========================================================================
   User & Admin Profile Dropdown
   ========================================================================== */
function initUserDropdown() {
  const dropdown = document.getElementById('navUserDropdown');
  const trigger = document.getElementById('navUserBtn');
  if (!dropdown || !trigger) return;

  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    const willOpen = !dropdown.classList.contains('active');
    dropdown.classList.toggle('active', willOpen);
    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  });

  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      dropdown.classList.remove('active');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });
}

/* ==========================================================================
   Theme Switcher Engine (Synchronized with indexmockup.html & TNM Portal)
   ========================================================================== */
function initThemeSwitcher() {
  const toggleBtn = document.getElementById('themeToggleBtn');
  const themeIcon = document.getElementById('themeIcon');
  const lightBtn = document.getElementById('t-light');
  const darkBtn = document.getElementById('t-dark');
  const mLightBtn = document.getElementById('m-t-light');
  const mDarkBtn = document.getElementById('m-t-dark');

  function getTheme() {
    const saved = localStorage.getItem('tnm-theme') || localStorage.getItem('tnm_theme');
    if (saved === 'dark' || saved === 'light') return saved;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);

    // Update navbar toggle icon (shows the opposite icon or current mode)
    if (themeIcon) {
      themeIcon.innerHTML = `<use href="#${theme === 'dark' ? 'ph-sun' : 'ph-moon'}"/>`;
    }
    if (toggleBtn) {
      toggleBtn.setAttribute('title', theme === 'dark' ? 'Ganti ke Tema Terang' : 'Ganti ke Tema Gelap');
    }

    // Update footer seg buttons
    if (lightBtn && darkBtn) {
      lightBtn.setAttribute('aria-pressed', theme === 'light');
      darkBtn.setAttribute('aria-pressed', theme === 'dark');
    }

    // Update mobile menu drawer seg buttons
    if (mLightBtn && mDarkBtn) {
      mLightBtn.setAttribute('aria-pressed', theme === 'light');
      mDarkBtn.setAttribute('aria-pressed', theme === 'dark');
    }

    // Sync across localStorage keys for ecosystem compatibility
    try {
      localStorage.setItem('tnm-theme', theme);
      localStorage.setItem('tnm_theme', theme);
    } catch (e) {}
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const current = document.documentElement.getAttribute('data-theme') || getTheme();
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }

  if (lightBtn) lightBtn.addEventListener('click', () => applyTheme('light'));
  if (darkBtn) darkBtn.addEventListener('click', () => applyTheme('dark'));
  if (mLightBtn) mLightBtn.addEventListener('click', () => applyTheme('light'));
  if (mDarkBtn) mDarkBtn.addEventListener('click', () => applyTheme('dark'));

  // Listen to system preference changes if user hasn't explicitly set a preference
  if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      const saved = localStorage.getItem('tnm-theme') || localStorage.getItem('tnm_theme');
      if (!saved) {
        applyTheme(e.matches ? 'dark' : 'light');
      }
    });
  }

  // Initial paint
  applyTheme(getTheme());
}
