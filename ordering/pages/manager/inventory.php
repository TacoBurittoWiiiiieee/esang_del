<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Inventory Management — Demo Frontend</title>

  <!-- Your existing styles -->
  <link rel="stylesheet" href="../VCSS/Order_Manager_CSS/inventory.css" />
  <link rel="stylesheet" href="../VCSS/Common_CSS/sidebar.css" />
  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body>
  <!-- Header -->
  <header class="page-header">
    <div class="header-top">
      <button class="openbtn"><i class="fas fa-bars"></i></button>
      <div class="page-title">
        <h1><i class="fas fa-boxes"></i> Inventory Management</h1>
        <p class="page-subtitle">Track and manage your product inventory efficiently</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-refresh" id="refreshBtn" title="Refresh Data"><i class="fas fa-sync-alt"></i></button>
        <button class="btn btn-export" id="exportBtn"  title="Export CSV"><i class="fas fa-download"></i></button>
      </div>
    </div>
  </header>

  <!-- Stats -->
  <section class="dashboard-stats">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total-products"><i class="fas fa-cube"></i></div>
        <div class="stat-details">
          <span class="stat-value" id="totalProducts">0</span>
          <span class="stat-label">Total Products</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon low-stock"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-details">
          <span class="stat-value" id="lowStock">0</span>
          <span class="stat-label">Low Stock Items</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon out-stock"><i class="fas fa-times-circle"></i></div>
        <div class="stat-details">
          <span class="stat-value" id="outOfStock">0</span>
          <span class="stat-label">Out of Stock</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon total-value"><i class="fas fa-peso-sign"></i></div>
        <div class="stat-details">
          <span class="stat-value" id="totalValue">₱0</span>
          <span class="stat-label">Inventory Value</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Controls -->
  <section class="controls-section">
    <div class="controls-container">
      <div class="search-filters">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input id="searchBar" type="text" placeholder="Search products..." />
        </div>
        <div class="filter-group">
          <select id="categoryFilter" class="filter-select">
            <option value="all">All Categories</option>
          </select>
          <select id="statusFilter" class="filter-select">
            <option value="all">All Status</option>
            <option value="high">In Stock</option>
            <option value="medium">Medium Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
          </select>
        </div>
      </div>

      <div class="view-buttons">
        <button id="inventoryBtn" class="tab-btn active"><i class="fas fa-boxes"></i><span>Inventory</span></button>
        <button id="dailyBtn"    class="tab-btn"><i class="fas fa-calendar-day"></i><span>Daily</span></button>
        <button id="weeklyBtn"   class="tab-btn"><i class="fas fa-calendar-week"></i><span>Weekly</span></button>
        <button id="monthlyBtn"  class="tab-btn"><i class="fas fa-calendar-alt"></i><span>Monthly</span></button>
        <button id="yearlyBtn"   class="tab-btn"><i class="fas fa-calendar"></i><span>Yearly</span></button>
      </div>
    </div>
  </section>

  <main class="content-main">
    <!-- Inventory -->
    <section id="inventoryTable" class="data-section active">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <h2><i class="fas fa-boxes"></i> Product Inventory</h2>
            <span class="item-count">Showing <span id="inventoryCount">0</span> items</span>
          </div>
        </div>

        <div class="table-container">
          <div class="table-wrapper">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="sortable" data-sort="product_id">ID <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="name">Product <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="category">Category <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="price">Price <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="stock">Stock <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="min_stock">Min Level <i class="fas fa-sort"></i></th>
                  <th class="sortable" data-sort="status">Status <i class="fas fa-sort"></i></th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="inventoryTableBody"></tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination-container">
            <div class="pagination-info">
              <span>Showing <span id="startItem">0</span> to <span id="endItem">0</span> of <span id="totalItems">0</span> entries</span>
            </div>
            <div class="pagination-controls">
              <button class="btn btn-pagination" id="prevPage"><i class="fas fa-chevron-left"></i></button>
              <div class="page-numbers" id="pageNumbers"></div>
              <button class="btn btn-pagination" id="nextPage"><i class="fas fa-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Daily -->
    <section id="dailyTable" class="data-section">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <h2><i class="fas fa-calendar-day"></i> Daily Overview</h2>
            <span class="date-info">Today: <span id="currentDate"></span></span>
          </div>
        </div>
        <div class="table-container">
          <div class="table-wrapper">
            <table class="modern-table">
              <thead><tr><th>Date</th><th>Item</th><th>Stock</th><th>Remaining</th><th>Sold</th><th>Status</th></tr></thead>
              <tbody id="dailyTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Weekly -->
    <section id="weeklyTable" class="data-section">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <h2><i class="fas fa-calendar-week"></i> Weekly Overview</h2>
            <span class="date-range">Week of <span id="currentDateW"></span></span>
          </div>
        </div>
        <div class="table-container">
          <div class="table-wrapper">
            <table class="modern-table">
              <thead><tr><th>Week</th><th>Item</th><th>Start</th><th>End</th><th>Revenue</th></tr></thead>
              <tbody id="weeklyTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Monthly -->
    <section id="monthlyTable" class="data-section">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <h2><i class="fas fa-calendar-alt"></i> Monthly Overview</h2>
            <span class="month-year">This month</span>
          </div>
        </div>
        <div class="table-container">
          <div class="table-wrapper">
            <table class="modern-table">
              <thead><tr><th>Week</th><th>Total Sales</th><th>Total Sold</th><th>Stocks Left</th><th>Best Seller</th></tr></thead>
              <tbody id="monthlyTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Yearly -->
    <section id="yearlyTable" class="data-section">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <h2><i class="fas fa-calendar"></i> Yearly Overview</h2>
            <span class="year-info">Year: <span id="currentYear"></span></span>
          </div>
        </div>
        <div class="table-container">
          <div class="table-wrapper">
            <table class="modern-table">
              <thead><tr><th>Month</th><th>Total Sales</th><th>Best Seller</th></tr></thead>
              <tbody id="yearlyTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Minimal Stock Modal (required by editStock/updateStock) -->
  <div id="stockModal" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h2><i class="fa-solid fa-box"></i> Update Stock — <span id="productName">Item</span></h2>
        <button class="modal-close" id="closeStockModal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="stockForm" class="modern-form">
          <input type="hidden" id="productId" />
          <div class="form-row">
            <div class="form-group">
              <label class="form-label"><i class="fa-solid fa-layer-group"></i> Current Stock</label>
              <input id="currentStock" type="number" class="form-input" min="0" required />
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fa-solid fa-triangle-exclamation"></i> Min Stock</label>
              <input id="minStockLevel" type="number" class="form-input" min="0" required />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-note-sticky"></i> Notes (optional)</label>
            <textarea id="stockNote" class="form-textarea" placeholder="Reason / details..."></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelStock">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Your existing script (kept external) -->
  <script src="../javascript/manager/inventory.js"></script>
  <script src="../javascript/sidebar.js"></script>
  <!-- If you’re using the DEMO override for sample data, include it AFTER inventory.js -->
  <!-- <script src="../VJavaScript/inventory.demo.override.js"></script> -->
</body>
</html>
