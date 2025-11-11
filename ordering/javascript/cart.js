// cart.js — vanilla JS cart for categories + inventory
(function () {
    'use strict';
  
    // ---------- Helpers ----------
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);
  
    // ---------- Elements ----------
    const cartIcon = $('#cartIcon');
    const cartModal = $('#cartModal');
    const closeCart = $('#closeCartModal');
    const cartList = $('#cart-items-container');
    const cartTotal = $('#cart-total');
    const cartBadge = $('#cart-count');
    const searchEl = $('#searchInput');
  
    // ---------- State ----------
    const CURRENCY = '₱';
    const LS_KEY = 'customerCart';
    let cart = [];
    try {
      const raw = localStorage.getItem(LS_KEY);
      cart = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(cart)) cart = [];
    } catch {
      cart = [];
    }
  
    // ---------- Utils ----------
    const toastEl = $('#toast');
    function toast(msg, ok = true) {
      if (!toastEl) return;
      toastEl.textContent = msg;
      toastEl.style.background = ok ? '#28a745' : '#dc3545';
      toastEl.classList.add('show');
      setTimeout(() => toastEl.classList.remove('show'), 2000);
    }
    const fmt = (n) => `${CURRENCY}${Number(n || 0).toFixed(2)}`;
    const save = () => localStorage.setItem(LS_KEY, JSON.stringify(cart));
    const count = () => cart.reduce((s, i) => s + (i.quantity || 1), 0);
    const sum = () => cart.reduce((s, i) => s + (i.quantity || 1) * Number(i.price || 0), 0);
  
    function updateBadge() {
      if (!cartBadge) return;
      const c = count();
      cartBadge.textContent = c;
      cartBadge.style.display = c > 0 ? 'block' : 'none';
    }
  
    function renderCart() {
      if (!cartList || !cartTotal) return;
  
      if (!cart.length) {
        cartList.innerHTML =
          '<p style="text-align:center;color:#6c757d;padding:20px;">Your cart is empty. Start adding some delicious items!</p>';
        cartTotal.textContent = fmt(0);
        return;
      }
  
      cartList.innerHTML = cart
        .map(
          (it, idx) => `
          <div class="cart-item">
            <div style="flex:1;padding-right:10px;">
              <strong>${it.name || 'Item'}</strong><br/>
              <small>${fmt(it.price)} each</small>
              <div class="cart-actions">
                <button class="qty-btn" data-act="dec" data-idx="${idx}">-</button>
                <span style="min-width:32px;text-align:center;font-weight:700">${it.quantity || 1}</span>
                <button class="qty-btn" data-act="inc" data-idx="${idx}">+</button>
                <button class="remove-btn" data-act="rm" data-idx="${idx}">Remove</button>
              </div>
            </div>
            <div style="text-align:right;min-width:110px;">
              <div style="font-weight:800;color:#28a745">${fmt(
                (it.quantity || 1) * Number(it.price || 0)
              )}</div>
            </div>
          </div>
        `
        )
        .join('');
  
      cartTotal.textContent = fmt(sum());
    }
  
    // ---------- Modal ----------
    function openCart() {
        if (!cartModal) return;
        cartModal.classList.add('visible');
        cartModal.classList.add('open');
        cartModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        renderCart();
      }
      
      function closeCartModal() {
        if (!cartModal) return;
        cartModal.classList.remove('visible');
        cartModal.classList.remove('open');
        cartModal.setAttribute('aria-hidden', 'true');
        cartModal.style.removeProperty('display');
        document.body.classList.remove('modal-open');
      }
  
    // ---------- Bindings ----------
    cartIcon?.addEventListener('click', openCart);
    closeCart?.addEventListener('click', closeCartModal);
    window.addEventListener('click', (e) => {
      if (e.target === cartModal) closeCartModal();
    });
  
    // Carousel controls
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.carousel-btn');
      if (!btn) return;
      const wrap = btn.parentElement;
      const id = 'row-' + (wrap?.getAttribute('data-row') || '');
      const row = document.getElementById(id);
      if (!row) return;
      const card = row.querySelector('.menu-card');
      const step = card ? (card.getBoundingClientRect().width + 16) * 1.2 : 300;
      row.scrollBy({
        left: btn.classList.contains('next') ? step : -step,
        behavior: 'smooth',
      });
    });
  
    // Add to cart
    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('.add-to-cart-btn');
      if (!addBtn || !addBtn.dataset.productId) return;
  
      const stock = parseInt(addBtn.dataset.stock || '0', 10);
      if (stock <= 0) {
        toast('This item is out of stock.', false);
        return;
      }
  
      const pid = String(addBtn.dataset.productId);
      const idx = cart.findIndex((x) => String(x.product_id) === pid);
  
      if (idx > -1) {
        const next = (cart[idx].quantity || 1) + 1;
        if (stock && next > stock) {
          toast(`Only ${stock} available.`, false);
          return;
        }
        cart[idx].quantity = next;
      } else {
        cart.push({
          product_id: pid,
          name: addBtn.dataset.name || 'Item',
          price: parseFloat(addBtn.dataset.price || '0'),
          image: addBtn.dataset.image || '',
          quantity: 1,
          stock_quantity: stock,
        });
      }
  
      save();
      updateBadge();
      renderCart();
      toast('Added to cart');
    });
  
    // Cart actions
    cartList?.addEventListener('click', (e) => {
      const act = e.target.dataset.act;
      const idx = parseInt(e.target.dataset.idx || '-1', 10);
      if (idx < 0 || !act) return;
  
      if (act === 'inc') {
        const st = cart[idx].stock_quantity || 0;
        const next = (cart[idx].quantity || 1) + 1;
        if (st && next > st) {
          toast(`Only ${st} available.`, false);
          return;
        }
        cart[idx].quantity = next;
      } else if (act === 'dec') {
        const next = (cart[idx].quantity || 1) - 1;
        if (next <= 0) cart.splice(idx, 1);
        else cart[idx].quantity = next;
      } else if (act === 'rm') {
        cart.splice(idx, 1);
      }
  
      save();
      updateBadge();
      renderCart();
    });
  
    // Search filter
    searchEl?.addEventListener('input', () => {
      const q = searchEl.value.toLowerCase();
      $$('.menu-card').forEach((card) => {
        const name = (card.querySelector('h4')?.textContent || '').toLowerCase();
        card.style.display = name.includes(q) ? 'flex' : 'none';
      });
    });
  
    // Sidebar helpers (keeps your existing functions working)
    window.openNav = () => {
      const s = document.getElementById('mySidenav');
      if (s) s.style.width = '250px';
    };
    window.closeNav = () => {
      const s = document.getElementById('mySidenav');
      if (s) s.style.width = '0';
    };
  
    // Checkout
    $('#checkoutButton')?.addEventListener('click', () => {
      if (!cart.length) {
        toast('Your cart is empty.', false);
        return;
      }
      window.location.href = 'orders.php';
    });
  
    // Init
    updateBadge();
  
    // Optional small API for debugging
    window.CartAPI = {
      open: openCart,
      close: closeCartModal,
      get: () => JSON.parse(JSON.stringify(cart)),
      clear: () => {
        cart = [];
        save();
        updateBadge();
        renderCart();
      },
    };
  })();
  