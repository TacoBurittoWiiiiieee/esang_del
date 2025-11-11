<?php 
require_once "session.php";
require_once __DIR__ . "/../../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require('PHPMailer/Exception.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/PHPMailer.php');

if (!isset($_SESSION['email']) || !filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
    header("Location: /app/views/auth/SignUp.php");
    exit;
}

$mail = new PHPMailer(true);

$message = "";
$error = 0;

$resetcode = (string)random_int(100000, 999999);

if (empty($_SESSION['otp_sent'])) {
    sendEmailAndPersistOtp($_SESSION['email'], $resetcode, $con, $mail);
    $_SESSION['otp_sent'] = true;
}

function sendEmailAndPersistOtp(string $email, string $otp, mysqli $con, PHPMailer $mail): void
{
    $stmt = $con->prepare("SELECT email FROM `users` WHERE email = ? LIMIT 1");
    if (!$stmt) {
        error_log('2FA: prepare users lookup failed: ' . $con->error);
        return;
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log('2FA: execute users lookup failed: ' . $stmt->error);
        $stmt->close();
        return;
    }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return;
    }

    $stmt = $con->prepare("UPDATE `users` SET otp = ? WHERE email = ? LIMIT 1");
    if (!$stmt) {
        error_log('2FA: prepare update otp failed: ' . $con->error);
        return;
    }
    $stmt->bind_param("ss", $otp, $email);
    if (!$stmt->execute()) {
        error_log('2FA: execute update otp failed: ' . $stmt->error);
        $stmt->close();
        return;
    }
    $stmt->close();

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ecarenet.ph@gmail.com';
        $mail->Password   = 'yqlbnhwjwffljesz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('ecarenet.ph@gmail.com', 'Esang Delicacies - No Reply');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = '2FA Code';
        $mail->Body = "
            <p>Good day!</p>
            <p>Your 2FA code is: <strong style='color:#007bff;'>{$otp}</strong></p>
            <p>Please do not share this code with anyone.</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log('2FA mail error: ' . $e->getMessage());
    }
}

if (isset($_POST['ver'])) {
    $code  = trim($_POST['otp'] ?? '');
    $email = strtolower(trim($_SESSION['email'] ?? ''));

    if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
        $_SESSION["message"] = "Invalid code format.";
        $error = 2;
    } else {
        $stmt = $con->prepare("SELECT user_id FROM `users` WHERE email = ? AND otp = ? LIMIT 1");
        if (!$stmt) {
            error_log('2FA: prepare select otp failed: ' . $con->error);
            $_SESSION["message"] = "Server error. Please try again.";
            $error = 2;
        } else {
            $stmt->bind_param("ss", $email, $code);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                $user = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                if ($user) {
                    $stmt2 = $con->prepare("UPDATE `users` SET email_verified = 1, otp = NULL, updated_at = NOW() WHERE email = ? LIMIT 1");
                    if ($stmt2) {
                        $stmt2->bind_param("s", $email);
                        if ($stmt2->execute() && $stmt2->affected_rows > 0) {
                            $_SESSION["success"] = 1;
                            $_SESSION["message"] = "Verification complete. You may now log in to your account.";
                            header("Location: ./LogIn.php");
                            exit;
                        } else {
                            $_SESSION["message"] = "Verification saved, but update failed. Please contact support.";
                            $error = 2;
                        }
                        $stmt2->close();
                    } else {
                        error_log('2FA: prepare update verified failed: ' . $con->error);
                        $_SESSION["message"] = "Server error. Please try again.";
                        $error = 2;
                    }
                } else {
                    $_SESSION["message"] = "Invalid Code";
                    $error = 2;
                }
            } else {
                error_log('2FA: execute select otp failed: ' . $stmt->error);
                $_SESSION["message"] = "Server error. Please try again.";
                $error = 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Esang Delicacies - 2FA</title>
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
      <div class="w-100" style="max-width: 450px;">
        <h1 class="text-center mb-4 fw-bold text-danger">Account Verification</h1>
        <h6 class="text-center mb-4">Enter the 2FA code we sent to your email: <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></h6>

        <?php if ($error===1): ?>
          <div class="alert alert-success text-center"><?php echo htmlspecialchars($_SESSION["message"]); ?></div>
        <?php elseif($error===2): ?>
          <div class="alert alert-danger text-center"><?php echo htmlspecialchars($_SESSION["message"]); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="row g-3">
          <div class="col-12">
            <div class="form-group">
                <input type="text" class="form-control form-control-lg" name="otp" placeholder="2FA Code" required>
            </div>
          </div>
          <div class="col-12 d-grid mt-3">
            <input type="submit" name="ver" class="btn btn-warning fw-bold py-2" value="Verify Account">
          </div>
          <p class="text-center mt-3">Can't access this email? <a href="SignUp.php" class="text-danger fw-bold">Sign Up</a> again.</p>
        </form>
      </div>
    </div>

    <div class="col-lg-6 d-none d-lg-block p-0">
        <img src="../VImages/Food Poster.png" 
            alt="Esang Delicacies" 
            class="w-100 p-1" 
            style="height: 100vh; object-fit: center; object-position: center;">
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
