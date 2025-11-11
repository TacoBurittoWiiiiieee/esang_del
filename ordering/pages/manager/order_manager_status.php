
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Bar</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css">
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/order_manager_status.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <?php include 'order_manager_nav.php'; ?>

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

    <div class="main-container">
        <div class="header">
            <h1>Order Status Management</h1>
            <p>Manage and update customer order statuses</p>
        </div>
        
        <div class="orders-table-container">
            <table class="orders-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Current Status</th>
                        <th>Rider</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Orders will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <div class="loading" id="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Loading orders...</p>
        </div>
        
        <div class="no-orders" id="noOrders" style="display: none;">
            <i class="fas fa-clipboard-list"></i>
            <p>No pending orders found</p>
        </div>
    </div>
    
    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeOrderModal">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetailContent">
                <!-- Order details will be populated here -->
            </div>
        </div>
    </div>
  <script src="../VJavaScript/sidebar.js"></script>
  <script src="../VJavaScript/order_sync_service.js"></script>
  <script src="../VJavaScript/order_manager_status.js"></script>
</body>
</html>