<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin/manage_items.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidenav" id="mySidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <div class="profile">
            <div class="profile-pic">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-name"><span>Admin</span></div>
        </div>
        <a href="dashboard.php" class="sidenav-item"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="order_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order status</a>
        <div class="dropdown">
            <a href="#" class="sidenav-item dropdown-btn"><i class="fas fa-box"></i> Product Maintenance <i class="fas fa-caret-down"></i></a>
            <div class="dropdown-content">
                <a href="product_maintenance.php">&bull; add item</a>
                <a href="manage_items.php">&bull; manage item</a>
                <a href="discount.php">&bull; create discount</a>
            </div>
        </div><a href="user_maintenance.php" class="sidenav-item"><i class="fas fa-users"></i> User Maintenance</a>
        <a href="analytics.php" class="sidenav-item"><i class="fas fa-file-alt"></i> Analytics & Sales and Transaction History</a>
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

    <div class="main-container">
        <span class="openbtn" onclick="openNav()">&#9776;</span>
            <div class="controls-container">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="dropdown-container">
                    <select id="categoryFilter">
                        <option value="all">All Categories</option>
                    </select>
                </div>
            </div>
            
            <div class="table-container">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Available Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Item</h3>
                <button id="closeEditModal" class="modal-close-btn">&times;</button>
            </div>
            <form id="editForm">
                <input type="hidden" id="editItemId">
                <div class="form-group">
                    <label for="editItemName">Item Name</label>
                    <input type="text" id="editItemName" required>
                </div>
                <div class="form-group">
                    <label for="editItemPrice">Price</label>
                    <input type="number" id="editItemPrice" step="0.01">
                </div>
                <div class="form-group">
                    <label for="editItemCategory">Category</label>
                    <select id="editItemCategory" required></select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="button save">Save Changes</button>
                    <button type="button" id="cancelEdit" class="button cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-button" id="closeDeleteModal">&times;</span>
            <h2>Delete Item</h2>
            <p>Are you sure you want to remove this item?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="button delete">Delete</button>
                <button id="cancelDelete" class="button cancel">Cancel</button>
            </div>
        </div>
    </div>

    <script src="../..javascript/sidebar.js"></script>
    <script src="../../javascript/admin/manage_items.js"></script>
</body>
</html>