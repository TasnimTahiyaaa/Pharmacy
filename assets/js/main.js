// Noor Pharmacy - Main JavaScript

// ===== TOAST =====
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  const icon = type === 'success'
    ? '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>'
    : '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>';
  toast.innerHTML = `${icon} ${message}`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(60px)'; toast.style.transition = 'all .3s'; setTimeout(() => toast.remove(), 300); }, 3500);
}

// ===== NAVBAR DROPDOWN =====
document.addEventListener('DOMContentLoaded', function () {
  const userBtn = document.getElementById('userMenuBtn');
  const dropdown = document.getElementById('userDropdown');
  if (userBtn && dropdown) {
    userBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('show');
    });
    document.addEventListener('click', function () {
      dropdown.classList.remove('show');
    });
  }

  // Mobile navbar
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', function () {
      mobileNav.classList.toggle('show');
    });
  }

  // Admin sidebar toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  const adminMain = document.getElementById('adminMain');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('collapsed');
      if (adminMain) adminMain.classList.toggle('collapsed');
    });
  }

  // Mobile sidebar toggle
  const mobileSidebarBtn = document.getElementById('mobileSidebarBtn');
  if (mobileSidebarBtn && sidebar) {
    mobileSidebarBtn.addEventListener('click', function () {
      sidebar.classList.toggle('mobile-open');
    });
  }

  // Auto-dismiss alerts
  document.querySelectorAll('.alert').forEach(function (el) {
    setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
  });

  // Quick login cards (fill form)
  document.querySelectorAll('.quick-card').forEach(function (card) {
    card.addEventListener('click', function () {
      const email = this.dataset.email;
      const pass = this.dataset.pass;
      const emailInput = document.getElementById('email');
      const passInput = document.getElementById('password');
      if (emailInput) emailInput.value = email;
      if (passInput) passInput.value = pass;
      document.querySelectorAll('.quick-card').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
    });
  });

  // Password toggle
  const togglePass = document.getElementById('togglePass');
  const passInput = document.getElementById('password');
  if (togglePass && passInput) {
    togglePass.addEventListener('click', function () {
      const type = passInput.type === 'password' ? 'text' : 'password';
      passInput.type = type;
      this.innerHTML = type === 'password'
        ? '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
        : '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>';
    });
  }

  // Modal open/close
  document.querySelectorAll('[data-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = this.dataset.modal;
      const modal = document.getElementById(id);
      if (modal) modal.classList.add('show');
    });
  });
  document.querySelectorAll('.modal-close, .modal-overlay').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (e.target === this || this.classList.contains('modal-close')) {
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
      }
    });
  });
  document.querySelectorAll('.modal').forEach(function (modal) {
    modal.addEventListener('click', function (e) { e.stopPropagation(); });
  });

  // Category tabs
  document.querySelectorAll('.cat-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const cat = this.dataset.cat;
      const url = new URL(window.location);
      if (cat === 'all') url.searchParams.delete('category');
      else url.searchParams.set('category', cat);
      url.searchParams.delete('page');
      window.location = url.toString();
    });
  });

  // Payment method toggle
  const paymentSelect = document.getElementById('payment_method');
  const bkashField = document.getElementById('bkash_field');
  if (paymentSelect && bkashField) {
    function toggleBkash() {
      bkashField.style.display = paymentSelect.value === 'bkash' ? 'block' : 'none';
    }
    paymentSelect.addEventListener('change', toggleBkash);
    toggleBkash();
  }

  // Expandable order rows
  document.querySelectorAll('.order-header').forEach(function (header) {
    header.addEventListener('click', function () {
      const id = this.dataset.id;
      const details = document.getElementById('order-details-' + id);
      const icon = this.querySelector('.expand-icon');
      if (details) {
        const showing = details.style.display !== 'none';
        details.style.display = showing ? 'none' : 'block';
        if (icon) icon.style.transform = showing ? 'rotate(0)' : 'rotate(90deg)';
      }
    });
  });

  // Confirm dialogs
  document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

  // Admin form modal openers
  document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = this.dataset.openModal;
      const modal = document.getElementById(id);
      if (modal) modal.classList.add('show');
    });
  });

  // Check flash messages and show toast
  const flash = document.getElementById('flash-data');
  if (flash) {
    const msg = flash.dataset.msg;
    const type = flash.dataset.type || 'success';
    if (msg) showToast(msg, type);
  }
});

// ===== CART QUANTITY =====
function changeQty(itemId, delta) {
  const span = document.getElementById('qty-' + itemId);
  if (!span) return;
  let qty = parseInt(span.textContent) + delta;
  if (qty < 1) qty = 1;
  span.textContent = qty;
  // Update via form submit
  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&item_id=${itemId}&quantity=${qty}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      updateCartTotals(data.cart);
    }
  }).catch(() => {});
}

function removeCartItem(itemId) {
  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&item_id=${itemId}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      const row = document.getElementById('cart-row-' + itemId);
      if (row) row.remove();
      updateCartTotals(data.cart);
      showToast('Item removed from cart');
      if (data.cart.item_count === 0) location.reload();
    }
  }).catch(() => {});
}

function updateCartTotals(cart) {
  const totalEl = document.getElementById('cart-total');
  const countEl = document.getElementById('cart-count');
  const navBadge = document.getElementById('nav-cart-badge');
  if (totalEl) totalEl.textContent = '৳ ' + parseFloat(cart.total).toFixed(2);
  if (countEl) countEl.textContent = cart.item_count + ' item(s)';
  if (navBadge) navBadge.textContent = cart.item_count;
}

function addToCart(medicineId, name) {
  const qtyEl = document.getElementById('med-qty-' + medicineId);
  const qty = qtyEl ? parseInt(qtyEl.textContent) : 1;
  fetch('cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&medicine_id=${medicineId}&quantity=${qty}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      showToast(name + ' added to cart!');
      const badge = document.getElementById('nav-cart-badge');
      if (badge) badge.textContent = data.cart_count;
    } else {
      showToast(data.message || 'Could not add to cart', 'error');
    }
  }).catch(() => showToast('Could not add to cart', 'error'));
}

function changeMedQty(medId, delta) {
  const el = document.getElementById('med-qty-' + medId);
  if (!el) return;
  let val = parseInt(el.textContent) + delta;
  if (val < 1) val = 1;
  el.textContent = val;
}

// ===== MINI PIE CHART (canvas) =====
function drawPie(canvasId, data, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const total = data.reduce((s, d) => s + d.value, 0);
  if (total === 0) { ctx.fillStyle = '#e5e7eb'; ctx.beginPath(); ctx.arc(75, 75, 70, 0, Math.PI * 2); ctx.fill(); return; }
  let angle = -Math.PI / 2;
  data.forEach((seg, i) => {
    const slice = (seg.value / total) * Math.PI * 2;
    ctx.beginPath(); ctx.moveTo(75, 75);
    ctx.arc(75, 75, 70, angle, angle + slice);
    ctx.closePath(); ctx.fillStyle = colors[i % colors.length]; ctx.fill();
    angle += slice;
  });
  ctx.beginPath(); ctx.arc(75, 75, 35, 0, Math.PI * 2); ctx.fillStyle = '#fff'; ctx.fill();
}

// ===== MINI BAR CHART =====
function drawBars(containerId, data, maxValue) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const max = maxValue || Math.max(...data.map(d => d.value), 1);
  container.innerHTML = data.map(d => `
    <div class="bar-row">
      <div class="bar-label" title="${d.label}">${d.label}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${(d.value/max*100).toFixed(1)}%"></div></div>
      <div class="bar-val">${d.value}</div>
    </div>
  `).join('');
}
