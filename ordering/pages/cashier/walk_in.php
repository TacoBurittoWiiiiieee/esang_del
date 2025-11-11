<?php /* frontend-only: no sessions */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cashier Walk-In</title>

  <!-- Icons -->
  <link rel="preconnect" href="https://cdnjs.cloudflare.com"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- Keep your existing sidebar CSS -->
  <link rel="stylesheet" href="../../assets/css/sidebar.css">

  <!-- Page CSS -->
  <link rel="stylesheet" href="../../assets/css/cashier/walk_in.css">
</head>
<body>
  <!-- SIDEBAR (ids/classes preserved for sidebar.js) -->
  <aside class="sidenav" id="mySidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

    <div class="profile">
      <div class="profile-pic"><i class="fa-solid fa-user"></i></div>
      <div class="profile-name"><span>Cashier</span></div>
    </div>

    <a href="walk_in.php" class="sidenav-item"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="invoices.php" class="sidenav-item"><i class="fa-solid fa-receipt"></i> Invoices</a>
    <!-- IMPORTANT: many sidebar.js files expect this exact id -->
    <a href="#" class="sidenav-item" id="logoutLink"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
  </aside>

  <!-- OPTIONAL: Logout confirmation modal (so sidebar.js handlers won’t crash) -->
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

  <!-- HEADER -->
  <header class="header">
    <span style="font-size:24px;cursor:pointer" onclick="openNav()">&#9776;</span>
    <div class="header-left">
      <h1 id="welcome-greeting">Welcome, Cashier</h1>
      <input type="text" id="search-bar" placeholder="Search menu items..."/>
    </div>
    <div class="header-right">
      <span id="current-date"></span>
      <button class="all-menu-button" type="button">All Menu</button>
    </div>
  </header>

  <!-- MAIN -->
  <main>
    <div class="main-container">
      <section class="menu-contents">
        <div class="menu-tab" id="menuTabs"></div>
        <div id="menuContents"></div>
      </section>

      <aside class="order-summary-panel">
        <div class="summary-header">
          <h2>Order Summary</h2>
          <button class="clear-order-button" type="button">Clear Order</button>
        </div>

        <div id="order-list"></div>

        <div class="payment-summary">
          <div class="total-summary">
            <span>Total:</span>
            <span id="total-price">₱0.00</span>
          </div>

          <div class="payment-methods">
            <button class="payment-button active" data-method="cash" type="button">Cash</button>
            <button class="payment-button" data-method="gcash" type="button">GCash</button>
            <button class="payment-button" data-method="split" type="button">Split Payment</button>
          </div>

          <div id="payment-inputs">
            <div id="cash-payment" class="payment-input-group active">
              <label for="cash-amount">Cash Amount:</label>
              <input type="number" id="cash-amount" placeholder="Enter cash amount"/>
              <p class="change-due">Change due: <span id="cash-change">₱0.00</span></p>
            </div>

            <div id="gcash-payment" class="payment-input-group">
              <label for="gcash-amount">GCash Amount:</label>
              <input type="number" id="gcash-amount" placeholder="Enter GCash amount"/>
            </div>

            <div id="split-payment" class="payment-input-group">
              <label for="split-cash-amount">Cash Amount:</label>
              <input type="number" id="split-cash-amount" placeholder="Enter cash amount"/>
              <label for="split-gcash-amount">GCash Amount:</label>
              <input type="number" id="split-gcash-amount" placeholder="Enter GCash amount"/>
            </div>
          </div>

          <button id="confirm-payment-button" type="button">Confirm Payment</button>
        </div>

        <div class="receipt-preview">
          <h2>Receipt Preview</h2>
          <div id="receipt-content"></div>
          <div class="receipt-actions">
            <button id="download-receipt-button" type="button">Download TXT</button>
            <button id="print-receipt-button" type="button">Print / Save as PDF</button>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <!-- Your existing sidebar JS -->
  <script src="../../javascript/sidebar.js"></script>

  <!-- Page JS (corrected relative path to avoid 404) -->
  <script src="../../javascript/walk_in.js"></script>
</body>
</html>
