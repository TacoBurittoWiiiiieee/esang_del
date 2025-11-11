<?php
require_once '../../auth/session.php';
require_once '../../auth/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
  header('Location: ../auth/LogIn.php');
  exit;
}

/* ---------- Resolve display name ---------- */
$userName = $_SESSION['user_name'] ?? 'Customer';
if ($userName === 'Customer' && !empty($_SESSION['user_id'])) {
  if ($stmt = $con->prepare("
    SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', first_name, last_name)), ''), email)
    FROM users
    WHERE user_id = ?
    LIMIT 1
  ")) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($n);
    if ($stmt->fetch() && $n) $userName = $n;
    $stmt->close();
  }
}

// echo '<script>alert("'.$_SESSION['profile_image'].'")</script>';

/* ---------- Load products grouped by category (categories + inventory) ---------- */
/*
  categories: category_id INT, category_name VARCHAR(25)
  inventory : product_id INT, product_name VARCHAR(100), product_description VARCHAR(255),
              product_image VARCHAR(50), unit_price DOUBLE, stock INT, category INT (FK -> categories.category_id)
*/
$groups = []; // ['Category Name' => [rows...]]
if ($stmt = $con->prepare("
  SELECT
    c.category_id,
    c.category_name,
    i.product_id,
    i.product_name,
    i.product_description,
    i.product_image,
    i.unit_price,
    i.stock
  FROM categories c
  JOIN inventory i ON i.category = c.category_id
  ORDER BY c.category_name, i.product_name
")) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) {
    $cat = $r['category_name'] ?: 'Others';
    $groups[$cat][] = $r;
  }
  $stmt->close();
}

function cat_id(string $s): string {
  return strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($s)));
}
function img_src(?string $s): string {
  $ph = 'https://placehold.co/320x200/efefef/333?text=Product';
  if (!$s) return $ph;
  $s = trim($s);
  if (stripos($s, 'http') === 0 || stripos($s, 'data:') === 0) return $s;
  return '../VImages/' . ltrim($s, '/'); // adjust if your images live elsewhere
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Customer Dashboard - Esang Delicacies</title>
  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <!-- Your existing site stylesheet -->
  <link rel="stylesheet" href="../../assets/css/style.css" />
</head>
<body>
  <?php include '../../components/sidenav_customer.php'; ?>

  <div class="main-content">
    <div class="top-header">
      <span class="openbtn" onclick="openNav()">&#9776;</span>
      <div class="greeting">
        <h2>
          Hello, <span style="color:#d9534f;"><?= htmlspecialchars($userName) ?></span>!
          <br />What would you like to order today?
        </h2>
      </div>
      <div class="header-icons">
        <div class="search-bar">
          <input id="searchInput" type="text" placeholder="Search for delicious items..." />
          <i class="fas fa-search"></i>
        </div>
        <div class="icon-link" id="cartIcon" title="View Cart" aria-label="View Cart">
          <i class="fas fa-shopping-cart"></i>
          <span id="cart-count" class="badge">0</span>
        </div>
      </div>
    </div>

    <div class="menu-contents">
      <?php if (empty($groups)): ?>
        <div class="category-block">
          <div class="category-header">
            <h3><i class="fa-solid fa-utensils"></i> Menu</h3>
            <span class="category-meta">0 items</span>
          </div>
          
        </div>
      <?php else: ?>
        <?php foreach ($groups as $catName => $items): ?>
          <?php $cid = cat_id($catName); ?>
          <section class="category-block" id="<?= $cid ?>">
            <div class="category-header">
              <h3><i class="fa-solid fa-utensils"></i> <?= htmlspecialchars($catName) ?></h3>
              <div class="carousel-controls" data-row="<?= $cid ?>">
                <button type="button" class="carousel-btn prev" aria-label="Scroll left">
                  <i class="fa-solid fa-chevron-left"></i>
                </button>
                <span class="category-meta"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?></span>
                <button type="button" class="carousel-btn next" aria-label="Scroll right">
                  <i class="fa-solid fa-chevron-right"></i>
                </button>
              </div>
            </div>

            <div class="carousel-row" id="row-<?= $cid ?>">
              <?php foreach ($items as $p): ?>
                <?php
                  $pid   = (int) $p['product_id'];
                  $name  = $p['product_name'];
                  $desc  = $p['product_description'] ?? '';
                  $price = (float) $p['unit_price'];
                  $img   = img_src($p['product_image']);
                  $stock = (int) $p['stock'];
                ?>
                <article class="menu-card" data-name="<?= htmlspecialchars($name) ?>">
                  <img
                    src="<?= htmlspecialchars($img) ?>"
                    alt="<?= htmlspecialchars($name) ?>"
                    onerror="this.src='https://placehold.co/320x200/efefef/333?text=Product'"
                  />
                  <div class="item-details">
                    <h4><?= htmlspecialchars($name) ?></h4>
                    <?php if ($desc): ?>
                      <div class="desc"><?= htmlspecialchars($desc) ?></div>
                    <?php endif; ?>
                    <div class="price">₱<?= number_format($price, 2) ?></div>
                    <?php if ($stock > 0): ?>
                      <div class="stock-ok">
                        <i class="fas fa-check-circle"></i> <?= $stock ?> in stock
                      </div>
                    <?php else: ?>
                      <div class="stock-bad">
                        <i class="fas fa-times-circle"></i> Out of stock
                      </div>
                    <?php endif; ?>
                  </div>
                  <button
                    class="add-to-cart-btn"
                    data-product-id="<?= $pid ?>"
                    data-name="<?= htmlspecialchars($name, ENT_QUOTES) ?>"
                    data-price="<?= number_format($price, 2, '.', '') ?>"
                    data-image="<?= htmlspecialchars($img, ENT_QUOTES) ?>"
                    data-stock="<?= $stock ?>"
                    <?= $stock <= 0 ? 'disabled' : '' ?>
                  >
                    <i class="fas fa-cart-plus"></i> Add to Cart
                  </button>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Cart Modal -->
  <div id="cartModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <span class="close-button" id="closeCartModal" aria-label="Close">&times;</span>
      <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>
      <div id="cart-items-container">
        <p style="text-align:center;color:#6c757d;padding:20px;">
          Your cart is empty. Start adding some delicious items!
        </p>
      </div>
      <div class="total-line">
        <span style="font-size:1.1rem;font-weight:700;">Total:</span>
        <span id="cart-total" style="font-size:1.4rem;font-weight:800;color:#dc3545;">₱0.00</span>
      </div>
      <button class="add-to-cart-btn checkout" id="checkoutButton">
        <i class="fas fa-credit-card"></i> Proceed to Checkout
      </button>
    </div>
  </div>

  <div id="toast" class="notification"></div>

  <script defer src="../../javascript/cart.js"></script>
</body>
</html>
