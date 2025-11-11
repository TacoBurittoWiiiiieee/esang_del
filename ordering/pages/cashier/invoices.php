<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Invoices</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../../assets/css/cashier/invoices.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><span>Cashier</span></div>
        </div>
        <a href="walk_in.php" class="sidenav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="invoices.php" class="sidenav-item"><i class="fas fa-receipt"></i> Invoices</a>
        <a href="#" class="sidenav-item" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

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

    <div class="header">
        <div class="header-left">
            <h1 id="welcome-greeting">Invoices</h1>
        </div>
        <div class="header-right">
            <span id="current-date"></span>
        </div>
    </div>

    <main>
        <div class="invoices-header">
            <div class="filter-buttons">
                <button class="filter-btn active" data-period="daily">Daily</button>
                <button class="filter-btn" data-period="monthly">Monthly</button>
                <button class="filter-btn" data-period="yearly">Yearly</button>
            </div>
            <button id="edit-selected-btn" class="edit-btn" disabled>Edit</button>
        </div>
        <div id="invoices-container">
            </div>
    </main>

    <!-- Mini Edit Modal modeled after CashierPopUp.php (client-side) -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeEditModal">&times;</span>
            <h2>Invoice Edit</h2>
            <p>Invoice <strong id="editInvoiceId">#N/A</strong></p>
            <div style="margin:10px 0;">
                <button id="requestModifyBtn" class="button primary">Request Invoice Modification</button>
                <button id="modifyInvoiceBtn" class="button warning">Modify Invoice</button>
            </div>
            <p>Current Status: <b id="editCurrentStatus">No Request Sent</b></p>
            <div class="modal-actions">
                <button id="closeEditCancel" class="button cancel">Close</button>
            </div>
        </div>
    </div>
    <script src="../../javascript/cashier/invoices.js"></script>
    <script src="../../javascript/sidebar.js"></script>
</body>
</html>