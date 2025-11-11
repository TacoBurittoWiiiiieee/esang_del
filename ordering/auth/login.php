<?php
require_once "session.php";
require_once "database.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $error = 'Please enter all fields.';
    } else {
        try {
            $sql  = "SELECT user_id, user_type, email, email_verified, first_name, last_name, profile_image
                     FROM users
                     WHERE email = ? AND password_hash = MD5(?)
                     LIMIT 1";
            $stmt = $con->prepare($sql);
            if (!$stmt) { throw new Exception('Prepare failed: '.$con->error); }
            $stmt->bind_param('ss', $email, $pass);
            $stmt->execute();
            $stmt->bind_result($uid, $role, $em, $verified, $first, $last, $img);

            if ($stmt->fetch()) {
                $stmt->close();

                if ((int)$verified !== 1) {
                    $_SESSION['success'] = 2;
                    $_SESSION['message'] = 'Account verification failed';
                    header("Refresh:0");
                    exit;
                }

                $_SESSION['role']     = strtoupper($role);
                $_SESSION['email']    = $em;
                $_SESSION['user_id']  = (int)$uid;
                $_SESSION['profile_img'] = $img;
                $full = trim(($first ?? '').' '.($last ?? ''));
                $_SESSION['user_name'] = ($full !== '' && $full !== ' ') ? $full : 'User';

                if ($role === 'CUSTOMER')      $_SESSION['customerId']    = (int)$uid;
                if ($role === 'RIDER')         { $_SESSION['riderId'] = (int)$uid; $_SESSION['rider_id'] = (int)$uid; }
                if ($role === 'CASHIER')       $_SESSION['cashierId']     = (int)$uid;
                if ($role === 'ADMIN')         $_SESSION['adminId']       = (int)$uid;
                if ($role === 'ORDER_MANAGER') $_SESSION['orderManagerId'] = (int)$uid;

                $redir = [
                    'CUSTOMER'      => '../pages/customer/dashboard.php',
                    'RIDER'         => '../rider/order_assignments.php',
                    'CASHIER'       => '../../views/cashier/cashier_walk_in.php',
                    'ADMIN'         => '../../views/admin/admin_dashboard.php',
                    'ORDER_MANAGER' => '../../views/order_manager/order_management.php',
                ];
                header('Location: '.$redir[$_SESSION['role']]);
                exit;
            } else {
                $stmt->close();
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            error_log('Login error: '.$e->getMessage());
            $error = 'Database error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Log In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #fffdf9; }
    .btn-warning { background-color: #ffcc00; border: none; }
    .btn-warning:hover { background-color: #e6b800; }
    .text-danger { color: #d32f2f !important; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row min-vh-100">
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-4">
      <div class="w-100" style="max-width: 400px;">
        <h1 class="text-center mb-4 fw-bold text-danger">Log In</h1>

        <?php if ($error !== ''): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
          <?php $type = ($_SESSION['success'] == 1 ? 'success' : 'danger'); ?>
          <div class="alert alert-<?= $type ?> text-center">
            <?= htmlspecialchars($_SESSION['message'] ?? '') ?>
          </div>
          <?php unset($_SESSION['success'], $_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" action="" class="row g-3">
          <div class="col-12">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
          </div>
          <div class="col-12">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
          </div>
          <div class="col-12 d-grid">
            <button type="submit" class="btn btn-warning fw-bold py-2">Log In</button>
          </div>
        </form>

        <p class="text-center mt-3">
          No account? <a href="../auth/SignUp.php" class="text-danger fw-bold">Sign Up</a>
        </p>
      </div>
    </div>

    <div class="col-lg-6 d-none d-lg-block p-0">
      <img src="../assets/images/login_image.png" alt="Esang Delicacies" class="w-100 p-1" style="height: 100vh; object-fit: center; object-position: center;">
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
