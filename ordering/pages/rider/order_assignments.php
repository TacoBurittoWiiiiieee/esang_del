<?php
// -------- FRONT-END SAFE DEFAULTS --------
// This block MUST be at the very top of the file (no spaces/BOM before it).

// Force mocks ON by default for front-end testing.
// Set ?mock=0 to turn off and rely on backend variables if you have them.
$mock = isset($_GET['mock']) ? (int)$_GET['mock'] : 1;

// If backend already set variables, keep them. Otherwise fill with mocks.
if ($mock || !isset($riderName))   $riderName   = 'Juan Dela Cruz';
if ($mock || !isset($riderId))     $riderId     = 1024;
if ($mock || !isset($riderStatus)) $riderStatus = 'active'; // 'active'|'inactive'

if ($mock || !isset($stats) || !is_array($stats)) {
  $stats = [
    'rating'           => 4.8,
    'total_deliveries' => 127,
    'today_deliveries' => 3,
    'today_earnings'   => 385.50,
  ];
}

if ($mock || !isset($activeOrders) || !is_array($activeOrders)) {
  $activeOrders = [
    [
      'order_id'         => 5012,
      'customer_name'    => 'Maria Santos',
      'delivery_address' => '123 Sampaguita St, Quezon City',
      'customer_phone'   => '0917 123 4567',
      'order_status'     => 'on_delivery', // on_delivery | picked_up | delivered
      'total_amount'     => 549.00,
      'delivery_fee'     => 65.00,
    ],
  ];
}

if ($mock || !isset($availableOrders) || !is_array($availableOrders)) {
  $availableOrders = [
    [
      'order_id'             => 5013,
      'customer_name'        => 'Jose Rizal',
      'delivery_address'     => 'Intramuros, Manila',
      'customer_phone'       => '0918 000 0000',
      'special_instructions' => 'Call upon arrival.',
      'item_count'           => 3,
      'total_amount'         => 799.00,
      'delivery_fee'         => 75.00,
      'order_date'           => date('Y-m-d H:i:s', strtotime('-10 minutes')),
    ],
    [
      'order_id'             => 5014,
      'customer_name'        => 'Andres Bonifacio',
      'delivery_address'     => 'Monumento, Caloocan',
      'customer_phone'       => '0918 123 4567',
      'special_instructions' => 'Leave at guard house.',
      'item_count'           => 2,
      'total_amount'         => 420.00,
      'delivery_fee'         => 60.00,
      'order_date'           => date('Y-m-d H:i:s', strtotime('-25 minutes')),
    ],
  ];
}
// -----------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Assignment</title>

  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg" />

  <!-- Your styles -->
  <link rel="stylesheet" href="../../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../../assets/css/rider/OA.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
</head>
<body
  data-rider-status="<?php echo htmlspecialchars($riderStatus, ENT_QUOTES); ?>"
  data-rider-name="<?php echo htmlspecialchars($riderName, ENT_QUOTES); ?>"
  data-rider-id="<?php echo htmlspecialchars((string)$riderId, ENT_QUOTES); ?>"
>
  <!-- Sidenav -->
  <div class="sidenav" id="mySidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="profile">
      <div class="profile-pic"><i class="fas fa-user"></i></div>
      <div class="profile-name"><span><?php echo htmlspecialchars($riderName); ?></span></div>
    </div>
    <a href="order_assignments.php" class="sidenav-item"><i class="fas fa-th-large"></i> Order Assignments</a>
    <a href="order_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order Status</a>
    <a href="rider_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
    <a href="../../../rider_logout.php" class="sidenav-item" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Log Out</a>
  </div>

  <!-- Logout Modal -->
  <div id="logoutModal" class="modal">
    <div class="modal-content">
      <span class="close-button">&times;</span>
      <h2>Log Out</h2>
      <p>Are you sure you want to log out?</p>
      <div class="modal-actions">
        <button id="cancelLogout" class="button cancel">Cancel</button>
        <button id="confirmLogout" class="button logout">Log Out</button>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <span class="openbtn" onclick="openNav()">&#9776;</span>

    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left" style="display:flex;align-items:center;">
        <i class="fas fa-utensils"></i>
        <h2 style="margin:0;">Order Assignments</h2>
      </div>

      <div class="rider-info">
        <span class="rider-id">ID: <?php echo htmlspecialchars((string)$riderId); ?></span>
        <span class="rider-status status-<?php echo htmlspecialchars($riderStatus); ?>" id="riderStatus">
          Status: <?php echo htmlspecialchars(ucfirst($riderStatus)); ?>
        </span>
        <button class="status-toggle-btn" onclick="toggleStatus()">
          <i class="fas fa-power-off"></i> Toggle
        </button>
      </div>
    </div>

    <!-- Status Dashboard -->
    <div class="rider-dashboard">
      <div class="stat-card">
        <i class="fas fa-truck icon-accent-success"></i>
        <h3><?php echo (int)($stats['total_deliveries'] ?? 0); ?></h3>
        <p>Total Deliveries</p>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-day icon-accent-primary"></i>
        <h3><?php echo (int)($stats['today_deliveries'] ?? 0); ?></h3>
        <p>Today's Orders</p>
      </div>
      <div class="stat-card">
        <i class="fas fa-peso-sign icon-accent-success"></i>
        <h3>₱<?php echo number_format((float)($stats['today_earnings'] ?? 0), 2); ?></h3>
        <p>Today's Earnings</p>
      </div>
    </div>

    <!-- Connection Status -->
    <div class="connection-status">
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>Connected!</strong> Rider: <?php echo htmlspecialchars($riderName); ?>
      </div>
    </div>

    <!-- Active Orders -->
    <?php if (!empty($activeOrders ?? [])): ?>
      <div class="active-orders-section">
        <h3 class="section-title danger">
          <i class="fas fa-truck-loading"></i>
          My Active Orders (<?php echo count($activeOrders ?? []); ?>)
        </h3>

        <?php foreach (($activeOrders ?? []) as $order): ?>
          <div class="order-card active-order card-accent-danger">
            <div class="customer-info-section">
              <div class="customer-profile-pic"><i class="fas fa-user"></i></div>
              <div class="customer-details">
                <p class="customer-name"><b>Name: </b><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></p>
                <p class="customer-address"><b>Address: </b><?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?></p>
                <p class="customer-phone"><b>Phone: </b><?php echo htmlspecialchars($order['customer_phone'] ?? ''); ?></p>
                <p class="order-status"><b>Status: </b>
                  <span class="status-badge status-<?php echo htmlspecialchars($order['order_status'] ?? ''); ?>">
                    <?php
                      $st = (string)($order['order_status'] ?? '');
                      echo htmlspecialchars(ucfirst(str_replace('_',' ',$st)));
                    ?>
                  </span>
                </p>
              </div>
            </div>
            <div class="order-summary-section">
              <p class="order-id"><b>Order ID: </b>#<?php echo htmlspecialchars((string)($order['order_id'] ?? '')); ?></p>
              <p class="order-total-amount">Total: ₱<?php echo number_format((float)($order['total_amount'] ?? 0), 2); ?></p>
              <p class="delivery-fee">Delivery Fee: ₱<?php echo number_format((float)($order['delivery_fee'] ?? 0), 2); ?></p>
              <button class="btn complete-btn" onclick="updateOrderStatus(<?php echo (int)($order['order_id'] ?? 0); ?>, 'delivered')">
                <i class="fas fa-check"></i> Mark as Delivered
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Available Orders -->
    <div class="order-assignments-list">
      <h3 class="section-title success">
        <i class="fas fa-clipboard-list"></i>
        Available Orders (<?php echo count($availableOrders ?? []); ?>)
      </h3>

      <?php if (empty($availableOrders ?? [])): ?>
        <div class="no-orders">
          <i class="fas fa-inbox empty-icon"></i>
          <h4>No orders available</h4>
          <p>Check back later for new delivery opportunities!</p>
          <button class="btn refresh-btn" onclick="refreshOrders()">
            <i class="fas fa-sync-alt"></i> Refresh Orders
          </button>
        </div>
      <?php else: ?>
        <?php foreach (($availableOrders ?? []) as $order): ?>
          <div class="order-card available-order card-accent-success">
            <div class="customer-info-section">
              <div class="customer-profile-pic"><i class="fas fa-user"></i></div>
              <div class="customer-details">
                <p class="customer-name"><b>Name: </b><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></p>
                <p class="customer-address"><b>Address: </b><?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?></p>
                <p class="customer-phone"><b>Phone: </b><?php echo htmlspecialchars($order['customer_phone'] ?? ''); ?></p>
                <?php if (!empty($order['special_instructions'] ?? '')): ?>
                  <p class="special-instructions"><b>Special Instructions: </b>
                    <?php echo htmlspecialchars($order['special_instructions']); ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
            <div class="order-summary-section">
              <p class="order-id"><b>Order ID: </b>#<?php echo htmlspecialchars((string)($order['order_id'] ?? '')); ?></p>
              <p class="order-quantity"><b>Items: </b><?php echo (int)($order['item_count'] ?? 0); ?></p>
              <p class="order-total-amount">Total: ₱<?php echo number_format((float)($order['total_amount'] ?? 0), 2); ?></p>
              <p class="delivery-fee">Delivery Fee: ₱<?php echo number_format((float)($order['delivery_fee'] ?? 0), 2); ?></p>
              <p class="order-time"><b>Ordered: </b>
                <?php
                  $dt = $order['order_date'] ?? null;
                  echo $dt ? date('M j, Y g:i A', strtotime($dt)) : '';
                ?>
              </p>
              <button class="btn accept-btn" onclick="acceptOrder(<?php echo (int)($order['order_id'] ?? 0); ?>)">
                <i class="fas fa-check-circle"></i> Accept Order
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../../javascript/sidebar.js"></script>
  <script src="../../javascript/rider/oa.js"></script>
</body>
</html>
