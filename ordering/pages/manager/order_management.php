<?php
// absolutely first thing in the file:

// start session BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// (optional) start output buffering to avoid accidental output issues
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Management</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg" />
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/order_management.css" />
    <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- NEW: extracted inline styles -->
    <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/order_management.extra.css" />
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

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Order Management Dashboard</h1>
            <div class="page-subtitle">Manage and track all customer orders</div>
        </div>

        <div class="stats-cards">
            <div class="stat-card pending">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-number" id="pending-count">0</div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>
            <div class="stat-card ongoing">
                <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                <div class="stat-info">
                    <div class="stat-number" id="ongoing-count">0</div>
                    <div class="stat-label">Ongoing Orders</div>
                </div>
            </div>
            <div class="stat-card delivery">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <div class="stat-info">
                    <div class="stat-number" id="delivery-count">0</div>
                    <div class="stat-label">Out for Delivery</div>
                </div>
            </div>
            <div class="stat-card completed">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-number" id="completed-count">0</div>
                    <div class="stat-label">Completed Orders</div>
                </div>
            </div>
            <div class="stat-card returned">
                <div class="stat-icon"><i class="fas fa-undo"></i></div>
                <div class="stat-info">
                    <div class="stat-number" id="returned-count">0</div>
                    <div class="stat-label">Returned Orders</div>
                </div>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tab-navigation">
                <button class="tab-button active" data-target="pending-table-container">
                    <i class="fas fa-clock"></i>
                    <span>Pending Orders</span>
                    <span class="badge" id="pending-badge">0</span>
                </button>
                <button class="tab-button" data-target="ongoing-table-container">
                    <i class="fas fa-spinner"></i>
                    <span>Ongoing Orders</span>
                    <span class="badge" id="ongoing-badge">0</span>
                </button>
                <button class="tab-button" data-target="delivery-table-container">
                    <i class="fas fa-truck"></i>
                    <span>Out for Delivery</span>
                    <span class="badge" id="delivery-badge">0</span>
                </button>
                <button class="tab-button" data-target="completed-table-container">
                    <i class="fas fa-check-circle"></i>
                    <span>Completed Orders</span>
                    <span class="badge" id="completed-badge">0</span>
                </button>
                <button class="tab-button" data-target="returned-table-container">
                    <i class="fas fa-undo"></i>
                    <span>Returned Orders</span>
                    <span class="badge" id="returned-badge">0</span>
                </button>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="pending-table-container">
                    <div class="table-header">
                        <h3 class="table-title">Pending Orders</h3>
                        <div class="table-filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search orders..." id="pending-search" />
                            </div>
                            <select class="filter-select" id="pending-filter">
                                <option value="all">All Payment Methods</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="GCash">GCash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Method</th>
                                    <th>Proof</th>
                                    <th>Rider</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending-orders-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane" id="ongoing-table-container">
                    <div class="table-header">
                        <h3 class="table-title">Ongoing Orders</h3>
                        <div class="table-filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search orders..." id="ongoing-search" />
                            </div>
                            <select class="filter-select" id="ongoing-filter">
                                <option value="all">All Statuses</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready">Ready</option>
                                <option value="on_delivery">On Delivery</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Method</th>
                                    <th>Proof</th>
                                    <th>Rider</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ongoing-orders-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane" id="delivery-table-container">
                    <div class="table-header">
                        <h3 class="table-title">Out for Delivery</h3>
                        <div class="table-filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search deliveries..." id="delivery-search" />
                            </div>
                            <select class="filter-select" id="delivery-filter">
                                <option value="all">All Riders</option>
                                <option value="assigned">Assigned</option>
                                <option value="unassigned">Unassigned</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Method</th>
                                    <th>Rider</th>
                                    <th>Delivery Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="delivery-orders-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane" id="completed-table-container">
                    <div class="table-header">
                        <h3 class="table-title">Completed Orders</h3>
                        <div class="table-filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search orders..." id="completed-search" />
                            </div>
                            <select class="filter-select" id="completed-filter">
                                <option value="all">All Payment Methods</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="GCash">GCash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Method</th>
                                    <th>Proof</th>
                                    <th>Rider</th>
                                    <th>Completed At</th>
                                </tr>
                            </thead>
                            <tbody id="completed-orders-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane" id="returned-table-container">
                    <div class="table-header">
                        <h3 class="table-title">Returned Orders</h3>
                        <div class="table-filters">
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search returned orders..." id="returned-search" />
                            </div>
                            <select class="filter-select" id="returned-filter">
                                <option value="all">All Return Reasons</option>
                                <option value="damaged">Damaged Product</option>
                                <option value="wrong_item">Wrong Item</option>
                                <option value="customer_cancellation">Customer Cancellation</option>
                                <option value="delivery_failed">Delivery Failed</option>
                                <option value="quality_issue">Quality Issue</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Return Reason</th>
                                    <th>Return Date</th>
                                    <th>Refund Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="returned-orders-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Proof of Payment -->
    <div class="modal-overlay" id="proof-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof of Payment</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modal-image" src="" alt="Proof of Payment" style="width: 100%; height: auto; display: block;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script src="../VJavaScript/sidebar.js"></script>
    <!-- NEW: extracted inline script -->
    <script src="../javascript/manager/order_management.js"></script>
</body>
</html>
