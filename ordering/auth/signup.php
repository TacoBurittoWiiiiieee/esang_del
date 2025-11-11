<?php
require_once "session.php";
require_once __DIR__ . "/../../config/database.php"; // provides $con (mysqli)

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first    = trim($_POST['first_name'] ?? '');
    $last     = trim($_POST['last_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $role     = trim($_POST['user_role'] ?? 'customer');

    $_SESSION['email'] = $email;

    if ($password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif ($email === '' || $password === '' || $first === '' || $last === '') {
        $error = 'First name, last name, email, and password are required';
    } else {
        // make sure $con exists and is a mysqli connection
        if (!isset($con) || !($con instanceof mysqli)) {
            error_log('SignUp: $con is not a valid mysqli connection');
            $error = 'We are experiencing a database connection issue. Please try again later.';
        } else {
            // Map form role to ENUM in users.user_type
            $roleMap = [
                'customer'      => 'CUSTOMER',
                'rider'         => 'RIDER',
                'cashier'       => 'CASHIER',
                'admin'         => 'ADMIN',
                'order_manager' => 'ORDER_MANAGER',
            ];
            $roleKey  = strtolower($role);
            $roleEnum = $roleMap[$roleKey] ?? null;

            if ($roleEnum === null) {
                $error = 'Invalid role';
            } else {
                $_SESSION['role'] = $roleKey;

                // 1) email uniqueness on `users`
                $check = $con->prepare('SELECT user_id FROM `users` WHERE email = ? LIMIT 1');
                if (!$check) {
                    error_log('SignUp: Prepare failed (users email check): ' . $con->error);
                    $error = 'A server error occurred. Please try again later.';
                } else {
                    $check->bind_param('s', $email);
                    $check->execute();
                    $check->store_result();
                    if ($check->num_rows > 0) {
                        $error = 'Email already registered.';
                    }
                    $check->close();
                }

                // 2) insert into `users` if no errors
                if (empty($error)) {
                    // md5 to match your existing table contents
                    $passwordHash = md5($password);

                    $stmt = $con->prepare(
                        'INSERT INTO `users`
                         (first_name, last_name, email, phone_number, password_hash, user_type, status, email_verified, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, "active", 0, NOW(), NOW())'
                    );

                    if (!$stmt) {
                        error_log('SignUp: Prepare failed (users insert): ' . $con->error);
                        $error = 'A server error occurred. Please try again later.';
                    } else {
                        $stmt->bind_param('ssssss', $first, $last, $email, $phone, $passwordHash, $roleEnum);

                        if ($stmt->execute()) {
                            $_SESSION['user_name'] = $first . ' ' . $last;
                            $_SESSION['user_id']   = $stmt->insert_id;
                            $_SESSION['otp_sent']  = false;

                            $message = 'Account created. Redirecting you to the account verification page.';
                            echo "<script>
                                    setTimeout(function() {
                                        window.location.href = './2FA.php';
                                    }, 3000);
                                  </script>";
                        } else {
                            $error = 'Failed to create account: ' . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #fffdf9; }
    .btn-warning { background-color: #ffcc00; border: none; }
    .btn-warning:hover { background-color: #e6b800; }
    .text-danger { color: #d32f2f !important; }
    .image-side img { object-fit: cover; height: 100vh; width: 100%; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row min-vh-100">
    
    <!-- Left: Form -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center p-5 bg-white">
      <div class="w-100" style="max-width: 450px;">
        <h1 class="text-center mb-4 fw-bold text-danger">Sign Up</h1>

        <?php if ($message !== ''): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="row g-3">
          <div class="col-md-6">
            <input type="text" name="first_name" class="form-control" placeholder="First name" required>
          </div>
          <div class="col-md-6">
            <input type="text" name="last_name" class="form-control" placeholder="Last name" required>
          </div>
          <div class="col-12">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
          </div>
          <div class="col-12">
            <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
          </div>
          <div class="col-md-6">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
          </div>
          <div class="col-md-6">
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
          </div>
          <div class="col-12">
            <select name="user_role" id="user_role" class="form-select" required>
              <option value="customer">Customer</option>
              <option value="rider">In-house Rider</option>
              <option value="cashier">Cashier</option>
              <option value="admin">Admin</option>
              <option value="order_manager">Order Manager</option>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-center align-items-center" id="customer-terms-container" style="display:none;">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agree-terms" name="agree-terms" required>
              <label class="form-check-label" for="agree-terms">
                I agree to the <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
              </label>
            </div>
          </div>
          <div class="col-12 d-grid">
            <button type="submit" class="btn btn-warning fw-bold py-2">Sign Up</button>
          </div>
        </form>

        <p class="text-center mt-3">
          Already have an account? <a href="../auth/LogIn.php" class="text-danger fw-bold">Log in</a>
        </p>
      </div>
    </div>

    <!-- Right: Image -->
    <div class="col-lg-6 d-none d-lg-block p-0">
        <img src="../VImages/Food Poster.png" 
            alt="Esang Delicacies" 
            class="w-100 p-1" 
            style="height: 100vh; object-fit: center; object-position: center;">
    </div>

  </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Terms and Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ol>
          <li><strong>Use of the Online Ordering System</strong>
            <ul>
              <li>You must provide accurate, complete, and up-to-date information when placing an order.</li>
              <li>Our system is intended only for personal and non-commercial use.</li>
            </ul>
          </li>
          <li><strong>Orders and Acceptance</strong>
            <ul><li>All orders are subject to availability and acceptance by Esang Delicacies.</li></ul>
          </li>
          <li><strong>Prices and Payment</strong>
            <ul>
              <li>Prices are in peso and may change without prior notice.</li>
              <li>Accepted methods: COD, GCash, Bank Transfer.</li>
              <li>Online payments must be confirmed before processing.</li>
            </ul>
          </li>
          <li><strong>Delivery and Pick-Up</strong>
            <ul>
              <li>Delivery times may vary due to traffic, weather, or other factors.</li>
              <li>Charges may apply and will be disclosed before order confirmation.</li>
              <li>Pick-up orders must be collected on schedule for freshness.</li>
            </ul>
          </li>
          <li><strong>Cancellations and Refunds</strong>
            <ul>
              <li>Refunds follow our policy and may take 1 business day.</li>
              <li>Incorrect delivery details are the customer’s responsibility.</li>
            </ul>
          </li>
          <li><strong>Privacy and Data Protection</strong>
            <ul>
              <li>Your information is used only to process and deliver your orders.</li>
              <li>We do not share details without consent, except as required by law.</li>
            </ul>
          </li>
        </ol>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('user_role').addEventListener('change', function() {
  document.getElementById('customer-terms-container').style.display =
    this.value === 'customer' ? 'block' : 'none';
});
</script>
</body>
</html>
