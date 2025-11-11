<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Status - Tracking (Frontend Only)</title>

  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
  <link rel="stylesheet" href="../../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <style>
    body{font-family:sans-serif;margin:0}
    .main-content{padding:1.5rem;margin-left:250px}
    .container{max-width:900px;margin:0 auto}
    .order-selector,.order-items{background:#f8f9fa;padding:15px;border-radius:10px;margin:15px 0}
    .order-selector select{width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;font-size:16px}
    .item-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #ddd}
    .item-row:last-child{border-bottom:none}
    .action-buttons{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
    .btn{padding:10px 20px;border:none;border-radius:5px;cursor:pointer;font-weight:700;transition:all .3s}
    .btn:hover{opacity:.9;transform:translateY(-1px)}
    .btn-primary{background:#007bff;color:#fff}
    .btn-success{background:#28a745;color:#fff}
    .btn-warning{background:#ffc107;color:#212529}

    .customer-info{background:#fff;border:1px solid #eee;border-radius:10px;padding:15px}
    .info-row{display:flex;align-items:center;gap:10px}
    .customer-icon{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#eef2ff;color:#4f46e5}
    .order-badge{padding:5px 10px;border-radius:15px;font-size:.9em;margin-left:15px;color:#fff}
    .total-amount{margin-top:10px}

    /* progress bar */
    .progressbar{position:relative;display:flex;justify-content:space-between;align-items:center;margin:24px 0}
    .progressbar::before{content:"";position:absolute;top:50%;left:0;right:0;height:6px;background:#e5e7eb;transform:translateY(-50%);border-radius:9999px}
    .progress{position:absolute;top:50%;left:0;height:6px;background:#007bff;border-radius:9999px;transform:translateY(-50%);width:0}
    .progress-step{position:relative;z-index:1;width:34px;height:34px;border-radius:50%;background:#e5e7eb;color:#6b7280;display:flex;align-items:center;justify-content:center}
    .progress-step.active{background:#007bff;color:#fff}
    .progress-step::after{content:attr(data-title);position:absolute;top:42px;white-space:nowrap;font-size:.75rem;color:#374151}

    @media (max-width: 768px){
      .main-content{margin-left:0;padding:1rem}
    }
  </style>
</head>
<body>
  <!-- Minimal static sidenav shell (no PHP) -->
  <div class="sidenav" id="mySidenav">
    <a href="javascript:void(0)" class="closebtn">&times;</a>
    <div class="profile">
      <div class="profile-pic"><i class="fas fa-user"></i></div>
      <div class="profile-name"><span>Rider Name</span></div>
    </div>
    <a href="order_assignments.php" class="sidenav-item">
        <i class="fas fa-th-large"></i> Order Assignments</a>
    <a href="order_status.php" class="sidenav-item">
        <i class="fas fa-clipboard-list"></i> Order Status</a>
    <a href="rider_profile.php" class="sidenav-item">
        <i class="fas fa-user-circle"></i> Profile</a>
    <a href="#" class="sidenav-item"><i class="fas fa-sign-out-alt"></i> Log Out</a>
  </div>

  <div class="main-content">
    <div class="container">
      <!-- Order Selector -->
      <div class="order-selector">
        <label for="orderSelect"><i class="fas fa-search"></i> <strong>Select Order to Track:</strong></label>
        <select id="orderSelect">
          <option value="">-- Select an order --</option>
        </select>
      </div>

      <!-- Customer + Order Info -->
      <div id="orderBlock" class="customer-info" style="display:none">
        <div class="info-row">
          <span class="customer-icon"><i class="fas fa-user"></i></span>
          <span id="custName" class="customer-name"></span>
          <span id="orderBadge" class="order-badge">Order #</span>
        </div>
        <div class="info-details">
          <p><b>Delivery Address: </b><span id="deliveryAddress"></span></p>
          <p><b>Phone Number: </b><span id="customerPhone"></span></p>
          <p><b>Items: </b><span id="itemCount"></span> item(s)</p>
          <p><b>Order Date: </b><span id="orderDate"></span></p>
        </div>
        <div class="total-amount">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <strong>Total Amount: ₱<span id="totalAmount"></span></strong><br>
              <small>Delivery Fee: ₱<span id="deliveryFee"></span></small>
            </div>
            <div id="statusPill"
                 class="order-status-badge"
                 style="background:#6b7280;color:#fff;padding:8px 15px;border-radius:20px;font-weight:bold;">
              <!-- status text -->
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="progressbar">
          <div class="progress" id="progress" style="width:0"></div>

          <div class="progress-step" id="s-pending"   data-title="Order Placed"><i class="fas fa-box"></i></div>
          <div class="progress-step" id="s-confirmed" data-title="Order Confirmed"><i class="fas fa-clipboard-check"></i></div>
          <div class="progress-step" id="s-preparing" data-title="Being Prepared"><i class="fas fa-hourglass-half"></i></div>
          <div class="progress-step" id="s-ready_for_delivery" data-title="Ready for Pickup"><i class="fas fa-truck-pickup"></i></div>
          <div class="progress-step" id="s-on_delivery" data-title="On Delivery"><i class="fas fa-motorcycle"></i></div>
          <div class="progress-step" id="s-delivered" data-title="Delivered"><i class="fas fa-home"></i></div>
        </div>

        <!-- Items -->
        <div class="order-items">
          <h4><i class="fas fa-list"></i> Order Items</h4>
          <div id="items"></div>
        </div>

        <!-- Action buttons shown purely as UI (no backend) -->
        <div class="action-buttons" id="actions"></div>
      </div>

      <div id="emptyState" style="text-align:center;padding:40px;color:#666;">
        <i class="fas fa-inbox" style="font-size:3em;margin-bottom:15px;"></i>
        <h3>No Order Selected</h3>
        <p>Please select an order from the dropdown above to view its tracking status.</p>
      </div>
    </div>
  </div>

  <script>
    // ---------- Sample data (edit this) ----------
    const orders = [
      {
        order_id: 101,
        customer_name: "Juan Dela Cruz",
        delivery_address: "123 Mango St, QC",
        customer_phone: "0917 111 2222",
        order_status: "ready_for_delivery",
        order_date: "2025-11-08T10:35:00",
        total_amount: 780,
        delivery_fee: 50,
        items: [
          { item_name: "Bibingka", quantity: 2, subtotal: 240 },
          { item_name: "Puto Bumbong", quantity: 4, subtotal: 480 },
          { item_name: "Suman", quantity: 1, subtotal: 60 },
        ]
      },
      {
        order_id: 102,
        customer_name: "Maria Santos",
        delivery_address: "45 Sampaguita Rd, Pasig",
        customer_phone: "0918 333 4444",
        order_status: "on_delivery",
        order_date: "2025-11-09T09:10:00",
        total_amount: 520,
        delivery_fee: 40,
        items: [
          { item_name: "Kakanin Platter", quantity: 1, subtotal: 480 },
          { item_name: "Buko Juice", quantity: 2, subtotal: 40 },
        ]
      }
    ];

    // ---------- Frontend helpers (replacing PHP) ----------
    const pipeline = ['pending','confirmed','preparing','ready_for_delivery','on_delivery','delivered'];

    function getStatusColor(status){
      const map = {
        pending:'#6b7280', confirmed:'#0ea5e9', preparing:'#f59e0b',
        ready_for_delivery:'#007bff', picked_up:'#8b5cf6',
        on_delivery:'#f97316', delivered:'#22c55e', cancelled:'#ef4444'
      };
      return map[status] ?? '#6b7280';
    }
    function getStatusProgress(status){
      const i = pipeline.indexOf(status);
      if (i < 0) return '0%';
      return Math.round((i/(pipeline.length-1))*100)+'%';
    }
    function titleize(s){ return s.replace(/_/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); }

    // ---------- Render ----------
    const sel = document.getElementById('orderSelect');
    orders.forEach(o=>{
      const opt = document.createElement('option');
      opt.value = o.order_id;
      opt.textContent = `Order #${o.order_id} - ${o.customer_name} (${titleize(o.order_status)})`;
      sel.appendChild(opt);
    });

    sel.addEventListener('change', e=>{
      const id = Number(e.target.value);
      const order = orders.find(o=>o.order_id===id);
      render(order || null);
    });

    function render(order){
      const block = document.getElementById('orderBlock');
      const empty = document.getElementById('emptyState');
      if(!order){ block.style.display='none'; empty.style.display='block'; return; }
      block.style.display='block'; empty.style.display='none';

      // Fill text
      document.getElementById('custName').textContent = order.customer_name;
      const badge = document.getElementById('orderBadge');
      badge.textContent = `Order #${order.order_id}`;
      badge.style.background = '#007bff';

      document.getElementById('deliveryAddress').textContent = order.delivery_address;
      document.getElementById('customerPhone').textContent = order.customer_phone;
      document.getElementById('itemCount').textContent = order.items.length;
      document.getElementById('orderDate').textContent =
        new Date(order.order_date).toLocaleString();
      document.getElementById('totalAmount').textContent = order.total_amount.toFixed(2);
      document.getElementById('deliveryFee').textContent = order.delivery_fee.toFixed(2);

      // Status pill
      const pill = document.getElementById('statusPill');
      pill.textContent = titleize(order.order_status);
      pill.style.background = getStatusColor(order.order_status);

      // Progress bar + steps
      document.getElementById('progress').style.width = getStatusProgress(order.order_status);
      document.querySelectorAll('.progress-step').forEach(el=>el.classList.remove('active'));
      let reached = true;
      pipeline.forEach(s=>{
        const el = document.getElementById('s-'+s);
        if (!el) return;
        if (reached) el.classList.add('active');
        if (s === order.order_status) reached = false;
      });
      if (order.order_status==='delivered') {
        document.getElementById('s-delivered').classList.add('active');
      }

      // Items
      const itemsBox = document.getElementById('items');
      itemsBox.innerHTML = '';
      order.items.forEach(it=>{
        const row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
          <div><strong>${it.item_name}</strong> <span style="color:#666">x${it.quantity}</span></div>
          <div>₱${it.subtotal.toFixed(2)}</div>
        `;
        itemsBox.appendChild(row);
      });

      // Action buttons (frontend only)
      const actions = document.getElementById('actions');
      actions.innerHTML = '';
      if (order.order_status === 'ready_for_delivery') {
        actions.innerHTML = `<button class="btn btn-warning"><i class="fas fa-hand-paper"></i> Mark as Picked Up</button>`;
      } else if (order.order_status === 'picked_up') {
        actions.innerHTML = `<button class="btn btn-primary"><i class="fas fa-motorcycle"></i> Start Delivery</button>`;
      } else if (order.order_status === 'on_delivery') {
        actions.innerHTML = `
          <button class="btn btn-success"><i class="fas fa-check-circle"></i> Mark as Delivered</button>
          <button class="btn btn-primary"><i class="fas fa-phone"></i> Call Customer</button>`;
      }
    }

    // initial state
    render(null);
  </script>
</body>
</html>
