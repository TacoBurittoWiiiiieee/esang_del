'use strict';

/* ==========================================================================
   Config
   ========================================================================== */

/** Toggle to use local mock data instead of API. */
const USE_MOCK_DATA = false;

/** API endpoints (used when USE_MOCK_DATA = false) */
const API = {
  ORDERS: '/public/api/order_manager_orders.php',
  COUNTS: '/public/api/get_order_counts.php',
  VERIFY: '/api/verify_payment.php',
  PROOF: (orderId) => `/api/get_payment_screenshot.php?order_id=${orderId}`,
};

/** Mock data (used when USE_MOCK_DATA = true) */
const MOCK = {
  orders: [
    {
      id: '#10345',
      customerName: 'John Doe',
      orderName: 'Cassava Box',
      quantity: 3,
      amount: 550,
      status: 'pending',
      payment: 'paid',
      paymentMethod: 'Gcash',
      proofOfPayment: 'https://placehold.co/150x150/d1d5db/000000?text=Proof+1',
      refNumber: '34568900',
      assignedRider: 'Not assigned'
    },
    {
      id: '#10346',
      customerName: 'Pm sq',
      orderName: 'Pichi Pichi',
      quantity: 3,
      amount: 65,
      status: 'pending',
      payment: 'paid',
      paymentMethod: 'Gcash',
      proofOfPayment: 'https://placehold.co/150x150/d1d5db/000000?text=Proof+2',
      refNumber: '34568900',
      assignedRider: 'Not assigned'
    },
    {
      id: '#10342',
      customerName: 'Alice Smith',
      orderName: 'Cassava Cake',
      quantity: 3,
      amount: 550,
      status: 'on going',
      payment: 'paid',
      paymentMethod: 'Gcash',
      proofOfPayment: 'https://placehold.co/150x150/d1d5db/000000?text=Proof+3',
      refNumber: '34568900',
      assignedRider: 'esang'
    },
    {
      id: '#10342',
      customerName: 'Sophia Reyes',
      orderName: 'chicken parmesan',
      quantity: 3,
      amount: 550,
      status: 'completed',
      payment: 'paid',
      paymentMethod: 'Gcash',
      proofOfPayment: 'https://placehold.co/150x150/d1d5db/000000?text=Proof+4',
      refNumber: '34568900',
      assignedRider: 'esang'
    },
    {
      id: '#10349',
      customerName: 'Marc Ramos',
      orderName: 'Macapuno',
      quantity: 3,
      amount: 200,
      status: 'pending',
      payment: 'unpaid',
      paymentMethod: 'Metrobank',
      proofOfPayment: '',
      refNumber: '500314',
      assignedRider: 'Not assigned'
    },
  ],
  riders: ['Not assigned', 'esang'],
  countsFromOrders(orders) {
    const norm = (s) => (s || '').toLowerCase();
    return orders.reduce(
      (acc, o) => {
        const s = norm(o.status);
        if (s === 'pending') acc.pending++;
        else if (s === 'on going' || s === 'out_for_delivery' || s === 'on_delivery') acc.ongoing++;
        else if (s === 'completed' || s === 'delivered') acc.completed++;
        return acc;
      },
      { pending: 0, ongoing: 0, delivery: 0, completed: 0, returned: 0 }
    );
  }
};

/* ==========================================================================
   DOM Cache
   ========================================================================== */
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

const DOM = {
  bodies: {
    pending:   () => $('#pending-orders-body'),
    ongoing:   () => $('#ongoing-orders-body'),
    delivery:  () => $('#delivery-orders-body'),
    completed: () => $('#completed-orders-body'),
    returned:  () => $('#returned-orders-body'),
  },
  counts: {
    pending:   () => $('#pending-count'),
    ongoing:   () => $('#ongoing-count'),
    delivery:  () => $('#delivery-count'),
    completed: () => $('#completed-count'),
    returned:  () => $('#returned-count'),
  },
  badges: {
    pending:   () => $('#pending-badge'),
    ongoing:   () => $('#ongoing-badge'),
    delivery:  () => $('#delivery-badge'),
    completed: () => $('#completed-badge'),
    returned:  () => $('#returned-badge'),
  },
  modal: {
    root:      () => $('#proof-modal'),
    title:     () => $('#proof-modal .modal-title'),
    body:      () => $('#proof-modal .modal-body'),
    img:       () => $('#modal-image'),
    closeBtns: () => $$('#proof-modal .btn-close, #proof-modal .btn-secondary'),
  },
  tabs: {
    buttons: () => $$('.tab-button'),
    panes:   () => $$('.tab-pane'),
  },
  statsCards: () => $$('.stat-card'),
};

/* ==========================================================================
   Utilities
   ========================================================================== */

/** Format PHP-ish order object created_at into short human-readable. */
const formatDateShort = (isoOrDate) => {
  const d = new Date(isoOrDate);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};

const peso = (n) => `₱${Number(n).toFixed(2)}`;

/** Normalize status to route tables properly. */
const normalizeStatus = (s) => (s || '').toLowerCase();

/* ==========================================================================
   Renderers (Live API data shape)
   ========================================================================== */

/**
 * Build a table row for non-returned orders coming from API.
 * @param {object} o - order object from API
 */
function renderRow(o) {
  const tr = document.createElement('tr');

  const customerName = o.customer_name || `Customer #${o.customer_id}`;

  // Build order name + total quantity
  let orderName = '—';
  let totalQty = 0;
  if (Array.isArray(o.order_items) && o.order_items.length) {
    const names = o.order_items.map((it) => {
      totalQty += parseInt(it.quantity) || 0;
      return it.product_name || 'Unknown Item';
    });
    orderName = names.length === 1 ? names[0] : `${names[0]} + ${names.length - 1} more`;
  }

  // Payment status
  let payStatus = 'N/A';
  if (o.payment_method === 'Cash on Delivery') {
    payStatus = '<span class="status-verified">✓ COD</span>';
  } else if (o.payment_verified) {
    payStatus = '<span class="status-verified">✓ Verified</span>';
  } else {
    payStatus = '<span class="status-pending-payment">⚠ Pending</span>';
  }

  const statusClass = `status-${normalizeStatus(o.status || 'pending').replace('_', '-')}`;
  const statusBadge = `<span class="status-badge ${statusClass}">${o.status || 'Pending'}</span>`;
  const createdDate = formatDateShort(o.created_at);

  // Proof of payment
  let proof = 'N/A';
  const method = o.payment_method || '';
  if (['GCash', 'Bank Transfer'].includes(method)) {
    proof = o.payment_screenshot_path
      ? `<button class="view-proof-btn" data-order-id="${o.order_id}" title="View Payment Screenshot">📷 View</button>`
      : '<span class="text-muted">No screenshot</span>';
  }

  const riderName = o.rider_name || '—';
  const isDeliveryTab = normalizeStatus(o.status).includes('delivery');

  // Actions
  let actions = '';
  if (o.payment_method === 'Cash on Delivery') {
    actions = `<button class="confirm-btn" data-id="${o.order_id}">Confirm Order</button>`;
  } else if (!o.payment_verified && o.payment_screenshot_path) {
    actions = `
      <button class="verify-payment-btn" data-id="${o.order_id}" title="Verify Payment">✓ Verify Payment</button>
      <button class="confirm-btn" data-id="${o.order_id}" disabled title="Verify payment first">Confirm Order</button>
    `;
  } else if (o.payment_verified) {
    actions = `<button class="confirm-btn" data-id="${o.order_id}">Confirm Order</button>`;
  } else {
    actions = `<button class="confirm-btn" data-id="${o.order_id}" disabled title="Waiting for payment screenshot">Confirm Order</button>`;
  }

  tr.innerHTML = isDeliveryTab
    ? `
      <td><strong>#${o.order_id}</strong></td>
      <td>
        <div style="font-weight:600;color:#1e293b;">${customerName}</div>
        <div style="font-size:.8rem;color:#64748b;">${o.customer_phone || ''}</div>
      </td>
      <td>
        <div style="font-weight:500;">${orderName}</div>
        <div style="font-size:.75rem;color:#64748b;">${createdDate}</div>
      </td>
      <td><span class="badge" style="background:#f1f5f9;color:#334155;">${totalQty}</span></td>
      <td><strong style="color:#059669;">${peso(o.total_amount)}</strong></td>
      <td>${statusBadge}</td>
      <td>${payStatus}</td>
      <td><span style="font-size:.8rem;">${method || 'N/A'}</span></td>
      <td><span style="font-size:.85rem;color:#64748b;">${riderName}</span></td>
      <td><div style="font-size:.8rem;font-weight:500;max-width:200px;">${o.delivery_address || '—'}</div></td>
      <td><button class="btn-success btn-sm" style="font-size:.75rem;">Mark Delivered</button></td>
    `
    : `
      <td><strong>#${o.order_id}</strong></td>
      <td>
        <div style="font-weight:600;color:#1e293b;">${customerName}</div>
        <div style="font-size:.8rem;color:#64748b;">${o.customer_phone || ''}</div>
      </td>
      <td>
        <div style="font-weight:500;">${orderName}</div>
        <div style="font-size:.75rem;color:#64748b;">${createdDate}</div>
      </td>
      <td><span class="badge" style="background:#f1f5f9;color:#334155;">${totalQty}</span></td>
      <td><strong style="color:#059669;">${peso(o.total_amount)}</strong></td>
      <td>${statusBadge}</td>
      <td>${payStatus}</td>
      <td><span style="font-size:.8rem;">${method || 'N/A'}</span></td>
      <td>${proof}</td>
      <td><span style="font-size:.85rem;color:#64748b;">${riderName}</span></td>
      <td>${actions}</td>
    `;

  return tr;
}

/**
 * Build a table row for "returned" orders (API).
 * Uses mock reason/status just like your original.
 */
function renderReturnedRow(o) {
  const tr = document.createElement('tr');

  const customerName = o.customer_name || `Customer #${o.customer_id}`;
  let orderName = '—';
  if (Array.isArray(o.order_items) && o.order_items.length) {
    const names = o.order_items.map((it) => it.product_name || 'Unknown Item');
    orderName = names.length === 1 ? names[0] : `${names[0]} + ${names.length - 1} more`;
  }

  const reasons = ['Damaged Product','Wrong Item Delivered','Customer Cancellation','Quality Issue','Delivery Failed'];
  const reason = reasons[Math.floor(Math.random() * reasons.length)];

  const refundStates = ['Pending','Processed','Completed'];
  const refund = refundStates[Math.floor(Math.random() * refundStates.length)];
  const refundClass =
    refund.toLowerCase() === 'completed' ? 'status-verified'
    : refund.toLowerCase() === 'processed' ? 'status-pending-payment'
    : 'text-muted';

  tr.innerHTML = `
    <td><strong>#${o.order_id}</strong></td>
    <td>
      <div style="font-weight:600;color:#1e293b;">${customerName}</div>
      <div style="font-size:.8rem;color:#64748b;">${o.customer_phone || ''}</div>
    </td>
    <td style="font-weight:500;">${orderName}</td>
    <td><strong style="color:#dc2626;">${peso(o.total_amount)}</strong></td>
    <td><span class="status-badge status-cancelled">${reason}</span></td>
    <td style="font-size:.85rem;">${formatDateShort(o.created_at)}</td>
    <td><span class="${refundClass}">${refund}</span></td>
    <td style="font-size:.8rem;color:#64748b;">Customer complaint</td>
    <td>
      <button class="btn-info btn-sm" style="font-size:.75rem;">View Details</button>
      <button class="btn-success btn-sm" style="font-size:.75rem;margin-left:.25rem;">Process Refund</button>
    </td>
  `;
  return tr;
}

/* ==========================================================================
   Renderers (Mock/demo table rows)
   ========================================================================== */

/**
 * Build a table row for the mock dataset (pending/ongoing/completed only).
 * Keeps your select rider + confirm button behavior.
 */
function renderMockRow(order, riders) {
  const row = document.createElement('tr');
  const riderOptions = riders
    .map(r => `<option value="${r}" ${order.assignedRider === r ? 'selected' : ''}>${r}</option>`)
    .join('');
  const isCompleted = order.status === 'completed';
  const proof = order.proofOfPayment
    ? `<button class="btn btn-link view-proof-btn" data-image="${order.proofOfPayment}">View</button>`
    : '';

  row.innerHTML = `
    <td>${order.id}</td>
    <td>${order.customerName}</td>
    <td>${order.orderName}</td>
    <td>${order.quantity}</td>
    <td>${peso(order.amount)}</td>
    <td>${order.status}</td>
    <td>${order.payment}</td>
    <td>${order.paymentMethod}</td>
    <td>${proof}</td>
    <td>${order.refNumber}</td>
    <td>
      <select class="form-select" ${isCompleted ? 'disabled' : ''}>
        ${riderOptions}
      </select>
    </td>
    <td>
      <button class="btn ${isCompleted ? 'btn-secondary disabled' : 'btn-primary'} confirm-order-btn" ${isCompleted ? 'disabled' : ''}>
        Confirm Order
      </button>
    </td>
  `;
  return row;
}

/* ==========================================================================
   Data Loaders
   ========================================================================== */

async function fetchJSON(url, options) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(`${res.status} ${res.statusText}`);
  return res.json();
}

async function loadOrderCounts() {
  if (USE_MOCK_DATA) {
    const counts = MOCK.countsFromOrders(MOCK.orders);
    updateOrderCounts(counts.pending, counts.ongoing, counts.delivery, counts.completed, counts.returned);
    return;
  }
  try {
    const data = await fetchJSON(API.COUNTS);
    if (data?.success) {
      const c = data.counts;
      updateOrderCounts(c.pending, c.ongoing, c.delivery, c.completed, c.returned);
    }
  } catch (e) {
    console.error('Failed to load order counts:', e);
  }
}

async function loadOrders() {
  // Clear tables first
  Object.values(DOM.bodies).forEach(get => { const el = get(); if (el) el.innerHTML = ''; });

  if (USE_MOCK_DATA) {
    const pending   = MOCK.orders.filter(o => o.status === 'pending');
    const ongoing   = MOCK.orders.filter(o => o.status === 'on going');
    const completed = MOCK.orders.filter(o => o.status === 'completed');

    pending.forEach(o => DOM.bodies.pending()?.appendChild(renderMockRow(o, MOCK.riders)));
    ongoing.forEach(o => DOM.bodies.ongoing()?.appendChild(renderMockRow(o, MOCK.riders)));
    completed.forEach(o => DOM.bodies.completed()?.appendChild(renderMockRow(o, MOCK.riders)));
    return;
  }

  // Live data
  const data = await fetchJSON(API.ORDERS);
  if (!data?.ok) return;

  let pending = 0, ongoing = 0, delivery = 0, completed = 0, returned = 0;

  (data.data || []).forEach((o) => {
    const status = normalizeStatus(o.status);
    let row;

    if (status === 'returned' || status === 'cancelled') {
      row = renderReturnedRow(o);
      DOM.bodies.returned()?.appendChild(row);
      returned++;
    } else {
      row = renderRow(o);
      if (status === 'pending') {
        DOM.bodies.pending()?.appendChild(row);
        pending++;
      } else if (status === 'out_for_delivery' || status === 'on_delivery') {
        DOM.bodies.delivery()?.appendChild(row);
        delivery++;
      } else if (status === 'completed' || status === 'delivered') {
        DOM.bodies.completed()?.appendChild(row);
        completed++;
      } else {
        DOM.bodies.ongoing()?.appendChild(row);
        ongoing++;
      }
    }
  });

  updateOrderCounts(pending, ongoing, delivery, completed, returned);
}

/* ==========================================================================
   UI Updaters
   ========================================================================== */

function updateOrderCounts(pending, ongoing, delivery, completed, returned) {
  // Stat cards
  if (DOM.counts.pending())   DOM.counts.pending().textContent = pending;
  if (DOM.counts.ongoing())   DOM.counts.ongoing().textContent = ongoing;
  if (DOM.counts.delivery())  DOM.counts.delivery().textContent = delivery;
  if (DOM.counts.completed()) DOM.counts.completed().textContent = completed;
  if (DOM.counts.returned())  DOM.counts.returned().textContent = returned;

  // Tab badges
  if (DOM.badges.pending())   DOM.badges.pending().textContent = pending;
  if (DOM.badges.ongoing())   DOM.badges.ongoing().textContent = ongoing;
  if (DOM.badges.delivery())  DOM.badges.delivery().textContent = delivery;
  if (DOM.badges.completed()) DOM.badges.completed().textContent = completed;
  if (DOM.badges.returned())  DOM.badges.returned().textContent = returned;
}

/* ==========================================================================
   Modal Controller
   ========================================================================== */

async function viewPaymentScreenshot(orderId) {
  const modal = DOM.modal.root();
  const title = DOM.modal.title();
  const body  = DOM.modal.body();

  if (!modal || !title || !body) return;

  try {
    modal.style.display = 'flex';
    title.textContent = `Loading Payment Screenshot - Order #${orderId}`;
    body.innerHTML = '<div class="loading-spinner"></div><p>Loading payment screenshot...</p>';

    const result = await fetchJSON(API.PROOF(orderId));

    if (result.success && result.data.has_screenshot) {
      title.textContent = `Payment Screenshot - Order #${orderId}`;
      const img = document.createElement('img');
      img.id = 'modal-image';
      img.alt = `Payment screenshot for order ${orderId}`;
      img.style.display = 'none';
      img.onload = () => { body.innerHTML = ''; body.appendChild(img); img.style.display = 'block'; };
      img.onerror = () => { body.innerHTML = '<div class="error-message">Failed to load payment screenshot image.</div>'; };
      img.src = result.data.screenshot.download_url;
    } else if (result.success && !result.data.has_screenshot) {
      title.textContent = `No Payment Screenshot - Order #${orderId}`;
      body.innerHTML = '<div class="error-message">No payment screenshot found for this order.</div>';
    } else {
      throw new Error(result.error || 'Unknown error occurred');
    }
  } catch (err) {
    console.error('Error viewing payment screenshot:', err);
    title.textContent = `Error Loading Screenshot - Order #${orderId}`;
    body.innerHTML = `<div class="error-message">Failed to load payment screenshot: ${err.message}</div>`;
  }
}

function wireModalClose() {
  const modal = DOM.modal.root();
  if (!modal) return;
  DOM.modal.closeBtns().forEach((btn) => btn.addEventListener('click', () => { modal.style.display = 'none'; }));
  // Click outside to close
  modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
}

/* ==========================================================================
   Actions
   ========================================================================== */

async function verifyPayment(orderId) {
  if (!confirm('Mark this payment as verified? This action cannot be undone.')) return;

  try {
    const result = await fetchJSON(API.VERIFY, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: orderId })
    });

    if (result.success) {
      alert('Payment verified successfully!');
      await loadOrders();
      await loadOrderCounts();
    } else {
      alert('Failed to verify payment: ' + (result.error || 'Unknown error'));
    }
  } catch (err) {
    console.error('Error verifying payment:', err);
    alert('Failed to verify payment. Please try again.');
  }
}

/* ==========================================================================
   Event Delegation (single listeners)
   ========================================================================== */

function wireGlobalClicks() {
  document.body.addEventListener('click', async (e) => {
    // Confirm order (API style)
    const confirmBtn = e.target.closest('.confirm-btn');
    if (confirmBtn && !confirmBtn.disabled) {
      const id = parseInt(confirmBtn.dataset.id, 10);
      if (!Number.isNaN(id)) {
        try {
          const result = await fetchJSON('/public/api/order_status_sync.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderId: id, action: 'complete' })
          });
          if (result.success) { await loadOrders(); await loadOrderCounts(); }
          else { alert('Failed to update order: ' + result.message); }
        } catch (err) { alert('Error updating order: ' + err.message); }
      }
      return;
    }

    // Verify payment
    const verifyBtn = e.target.closest('.verify-payment-btn');
    if (verifyBtn) {
      const id = parseInt(verifyBtn.dataset.id, 10);
      if (!Number.isNaN(id)) await verifyPayment(id);
      return;
    }

    // View proof (API style button)
    const viewProofBtn = e.target.closest('.view-proof-btn');
    if (viewProofBtn && viewProofBtn.dataset.orderId) {
      const orderId = parseInt(viewProofBtn.dataset.orderId, 10);
      if (!Number.isNaN(orderId)) await viewPaymentScreenshot(orderId);
      return;
    }

    // View proof (mock button with direct image)
    if (e.target.classList.contains('view-proof-btn') && e.target.dataset.image) {
      const modal = DOM.modal.root();
      const imgEl = DOM.modal.img();
      if (modal && imgEl) {
        imgEl.src = e.target.dataset.image;
        modal.classList.add('show'); // CSS provides .show display override
      }
      return;
    }

    // Mock confirm
    if (e.target.classList.contains('confirm-order-btn') && !e.target.disabled) {
      const row = e.target.closest('tr');
      const orderId = row?.querySelector('td:first-child')?.textContent?.trim();
      if (orderId) console.log(`Order ${orderId} confirmed.`);
    }

    // Returned orders quick actions (API)
    if (e.target.textContent === 'View Details') {
      alert('Order return details would be shown here.');
    } else if (e.target.textContent === 'Process Refund') {
      if (confirm('Process refund for this order?')) {
        alert('Refund processed successfully!');
        e.target.textContent = 'Refunded';
        e.target.disabled = true;
        e.target.style.background = '#6b7280';
      }
    }
  });

  // Close mock modal via outside click
  window.addEventListener('click', (e) => {
    const modal = DOM.modal.root();
    if (modal && e.target === modal) modal.classList.remove('show');
  });
}

function wireTabs() {
  const btns = DOM.tabs.buttons();
  const panes = DOM.tabs.panes();
  btns.forEach((button) => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      btns.forEach((b) => b.classList.remove('active'));
      panes.forEach((p) => p.classList.remove('active'));
      button.classList.add('active');
      const target = document.getElementById(button.dataset.target);
      if (target) target.classList.add('active');
      button.style.transform = 'scale(0.98)';
      setTimeout(() => { button.style.transform = 'scale(1)'; }, 150);
    });
  });

  // Stat cards = tab shorthands
  DOM.statsCards().forEach((card, index) => {
    card.addEventListener('click', () => {
      const btn = DOM.tabs.buttons()[index];
      if (btn) btn.click();
    });
  });
}

/* ==========================================================================
   Init
   ========================================================================== */

document.addEventListener('DOMContentLoaded', async () => {
  wireTabs();
  wireGlobalClicks();
  wireModalClose();

  await loadOrders();
  await loadOrderCounts();

  // Light polling like your original
  setInterval(loadOrderCounts, 30_000);
  setInterval(loadOrders, 120_000);
});
