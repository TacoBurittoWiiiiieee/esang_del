<?php

if (!isset($userName)) { $userName = 'Customer'; }
if (!isset($active))   { $active = ''; }

// Normalize profile image source
$imgSrc = '';

// 1) Prefer a fully-built URL passed in by the page (e.g. ../../uploads/xxx.jpg)
if (!empty($currentImgURL)) {
  $imgSrc = $currentImgURL;
}
// 2) Or a prebuilt URL stored in session (your profile page used $_SESSION['img'])
elseif (!empty($_SESSION['profile_image'])) {
  $imgSrc = $_SESSION['profile_image'];
}
// 3) Or a raw filename in session (build a relative URL from this component)
elseif (!empty($_SESSION['profile_image'])) {
  // Adjust path if your uploads folder differs relative to *this* file
  $imgSrc = '../../uploads/' . rawurlencode($_SESSION['profile_image']);
}

function active_class($slug, $active) {
  return $slug === $active ? ' active' : '';
}
?>
<div class="sidenav" id="mySidenav">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <div class="profile">
    <div class="profile-pic">
      <?php if (!empty($imgSrc)): ?>
        <img src="<?= htmlspecialchars($imgSrc) ?>"
             alt="Profile Image"
             style="border-radius:50%;object-fit:cover;">
      <?php else: ?>
        <i class="fas fa-user"></i>
      <?php endif; ?>
    </div>
    <div class="profile-name">
      <span id="userNameDisplay"><?= htmlspecialchars($userName) ?></span>
    </div>
  </div>

  <a href="dashboard.php" class="sidenav-item<?= active_class('dashboard', $active) ?>"><i class="fas fa-home"></i> Dashboard</a>
  <a href="orders.php" class="sidenav-item<?= active_class('orders', $active) ?>"><i class="fas fa-cart-shopping"></i> Orders</a>
  <a href="customer_billing.php" class="sidenav-item<?= active_class('invoices', $active) ?>"><i class="fas fa-credit-card"></i> Invoices</a>
  <a href="feedback.php" class="sidenav-item<?= active_class('feedback', $active) ?>"><i class="fas fa-comment"></i> Feedback</a>
  <a href="customer_order_status.php" class="sidenav-item<?= active_class('status', $active) ?>"><i class="fas fa-clipboard-list"></i> Order Status</a>
  <a href="order_history.php" class="sidenav-item<?= active_class('history', $active) ?>"><i class="fas fa-clock-rotate-left"></i> Order History</a>
  <a href="profile.php" class="sidenav-item<?= active_class('profile', $active) ?>"><i class="fas fa-user-circle"></i> Profile</a>
  <a href="../../auth/logout.php" class="sidenav-item" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>

<!-- Shared logout modal -->
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

<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',sans-serif;background:#f8f9fa;line-height:1.6}
  .main-content{margin-left:250px;padding:20px;min-height:100vh;transition:.3s}
  @media(max-width:768px){.main-content{margin-left:0;padding:15px}}

  .top-header{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:30px;padding:20px;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);flex-wrap:wrap}
  .greeting h2{font-size:1.8em;margin:0;color:#333;font-weight:600}
  @media(max-width:768px){.greeting h2{font-size:1.4em;text-align:center}}
  .header-icons{display:flex;align-items:center;gap:20px;flex-wrap:wrap;justify-content:flex-end}
  .search-bar{display:flex;align-items:center;border:2px solid #e9ecef;border-radius:25px;padding:8px 16px;background:#f8f9fa;min-width:260px;max-width:420px;width:100%}
  .search-bar:focus-within{border-color:#dc3545;background:#fff;box-shadow:0 0 0 3px rgba(220,53,69,.1)}
  .search-bar input{border:0;outline:0;background:transparent;flex:1}
  .search-bar .fa-search{color:#6c757d;margin-left:10px}

  .icon-link{position:relative;font-size:1.4em;color:#6c757d;cursor:pointer;background:#f8f9fa;border-radius:50%;width:50px;height:50px;display:flex;justify-content:center;align-items:center;border:2px solid transparent;transition:.2s}
  .icon-link:hover{color:#dc3545;background:#fff;border-color:#dc3545;transform:translateY(-2px);box-shadow:0 4px 15px rgba(220,53,69,.2)}
  .badge{position:absolute;top:-5px;right:-5px;background:#dc3545;color:#fff;border-radius:50%;padding:4px 8px;font-size:.7em;min-width:18px;text-align:center;font-weight:600;display:none}

  .category-block{margin-bottom:28px}
  .category-header{
    display:flex;align-items:center;justify-content:space-between;background:#fff;border-radius:12px;
    padding:12px 14px;box-shadow:0 1px 8px rgba(0,0,0,.06);margin-bottom:12px
  }
  .category-header h3{margin:0;font-size:1.1rem;font-weight:800;color:#333;display:flex;align-items:center;gap:10px}
  .category-meta{font-size:.9rem;color:#6c757d;font-weight:600;background:#f8f9fa;border:1px solid #e9ecef;border-radius:18px;padding:6px 10px}
  .carousel-controls{display:flex;gap:8px}
  .carousel-btn{
    background:#fff;border:1px solid #e9ecef;border-radius:8px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;
    cursor:pointer;transition:.15s
  }
  .carousel-btn:hover{border-color:#dc3545;color:#dc3545;box-shadow:0 2px 10px rgba(220,53,69,.15)}

  /* Horizontal product row */
  .carousel-row{
    display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;padding:2px 2px 8px 2px;background:#fff;border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.08)
  }
  .carousel-row::-webkit-scrollbar{height:10px}
  .carousel-row::-webkit-scrollbar-thumb{background:#e9ecef;border-radius:10px}
  .carousel-row::-webkit-scrollbar-track{background:transparent}

  .menu-card{
    flex:0 0 260px; /* card width */
    scroll-snap-align:start;
    display:flex;flex-direction:column;background:#fff;border:2px solid #f8f9fa;border-radius:12px;text-align:center;
    box-shadow:0 3px 12px rgba(0,0,0,.07);padding:16px;margin:8px 8px;transition:.2s;height:100%
  }
  @media (max-width:480px){ .menu-card{flex-basis:220px} }
  .menu-card:hover{border-color:#ffc107;box-shadow:0 8px 24px rgba(255,193,7,.2);transform:translateY(-3px)}
  .menu-card img{width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:12px}
  .item-details{display:flex;flex-direction:column;text-align:left;gap:8px;flex:1}
  .item-details h4{font-size:1.05rem;margin:0;color:#333;font-weight:700}
  .price{font-weight:800;color:#dc3545;font-size:1.15rem}
  .stock-ok{color:#28a745;font-size:.85rem}
  .stock-bad{color:#dc3545;font-size:.85rem}

  .add-to-cart-btn{
    background:linear-gradient(135deg,#28a745,#20c997);color:#fff;border:0;border-radius:8px;padding:10px 12px;font-weight:700;
    cursor:pointer;text-transform:uppercase;width:100%;margin-top:10px;transition:.15s;box-shadow:0 3px 10px rgba(40,167,69,.3)
  }
  .add-to-cart-btn:hover{background:linear-gradient(135deg,#218838,#1e7e34);transform:translateY(-1px)}
  .add-to-cart-btn:disabled{opacity:.65;cursor:not-allowed}

  /* Modal (cart) */
  .modal{display:none;position:fixed;z-index:1000;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(5px)}
  .modal-content{background:#fff;margin:5% auto;padding:24px;border-radius:15px;width:92%;max-width:720px;box-shadow:0 10px 30px rgba(0,0,0,.3);position:relative}
  .close-button{color:#999;position:absolute;right:18px;top:14px;font-size:28px;font-weight:700;cursor:pointer}
  .close-button:hover{color:#dc3545}
  .cart-item{display:flex;justify-content:space-between;align-items:flex-start;padding:12px 6px;border-bottom:1px solid #f1f1f1}
  .cart-actions{display:flex;gap:8px;margin-top:8px}
  .qty-btn{background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;width:30px;height:30px;cursor:pointer}
  .remove-btn{background:#dc3545;color:#fff;border:0;border-radius:6px;padding:6px 10px;cursor:pointer}
  .total-line{display:flex;justify-content:space-between;align-items:center;margin:18px 0}
  .checkout{width:100%}

  /* Toast */
  .notification{position:fixed;left:50%;transform:translateX(-50%);bottom:24px;background:#28a745;color:#fff;padding:10px 14px;border-radius:10px;display:none;z-index:1200;font-weight:700}
  .notification.show{display:block}
</style>
