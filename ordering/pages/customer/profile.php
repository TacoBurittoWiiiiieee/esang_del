<?php
require_once '../../auth/session.php';
require_once '../../auth/database.php';

// Require customer session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header('Location: ../auth/LogIn.php');
    exit;
}

$userId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$userName = $_SESSION['user_name'] ?? 'Customer';

/* ------------------ Load current profile from users ------------------ */
$current = [
    'first_name'     => '',
    'last_name'      => '',
    'phone_number'   => '',
    'profile_image'  => '',
];

if ($userId > 0) {
    if ($stmt = $con->prepare("
        SELECT first_name, last_name, phone_number, profile_image,
               COALESCE(NULLIF(TRIM(CONCAT_WS(' ', first_name, last_name)), ''), email) AS display_name
        FROM users
        WHERE user_id = ? LIMIT 1
    ")) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($fn,$ln,$ph,$pi,$display);
        if ($stmt->fetch()) {
            $current['first_name']     = (string)$fn;
            $current['last_name']      = (string)$ln;
            $current['phone_number']   = (string)$ph;
            $current['profile_image']  = (string)$pi;
            if (!empty($display)) $userName = $display;
        }
        $stmt->close();
    }
}

/* ------------------ Load current address from customer_details ------------------ */
$address = [
    'house_number'         => '',
    'street_village_sitio' => '',
    'barangay'             => '',
    'city'                 => '',
];

if ($userId > 0) {
    if ($stmt = $con->prepare("
        SELECT house_number, street_village_sitio, barangay, city
        FROM customer_details
        WHERE user_id = ? LIMIT 1
    ")) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($h,$s,$b,$c);
        if ($stmt->fetch()) {
            $address['house_number']         = (string)$h;
            $address['street_village_sitio'] = (string)$s;
            $address['barangay']             = (string)$b;
            $address['city']                 = (string)$c;
        }
        $stmt->close();
    }
}

/* ------------------ Handle POST: update users + upsert customer_details ------------------ */
$flash = ['type' => '', 'msg' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inputs
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');

    $houseNum   = trim($_POST['house_number'] ?? '');
    $streetVill = trim($_POST['street_village_sitio'] ?? '');
    $barangay   = trim($_POST['barangay'] ?? '');
    $city       = trim($_POST['city'] ?? '');

    // ---- File upload (optional) ----
    $uploadsDirFS  = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    $uploadsDirURL = '../../uploads/';

    if (!is_dir($uploadsDirFS)) { @mkdir($uploadsDirFS, 0775, true); }

    $newFileName = null;
    $maxBytes    = 2 * 1024 * 1024;
    $allowedExt  = ['jpg','jpeg','png','webp','gif'];

    if (isset($_FILES['profile_photo']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
        $err  = $_FILES['profile_photo']['error'];
        $size = (int)$_FILES['profile_photo']['size'];
        $tmp  = $_FILES['profile_photo']['tmp_name'];
        $orig = $_FILES['profile_photo']['name'];

        if ($err === UPLOAD_ERR_OK) {
            if ($size > $maxBytes) {
                $flash = ['type'=>'danger','msg'=>'Image too large (max 2MB).'];
            } else {
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    $flash = ['type'=>'danger','msg'=>'Invalid image type. Allowed: jpg, jpeg, png, webp, gif.'];
                } else {
                    $stamp      = date('Ymd_His');
                    $newFileName= "user_{$userId}_{$stamp}.{$ext}";
                    $destFS     = $uploadsDirFS . $newFileName;
                    if (!@move_uploaded_file($tmp, $destFS)) {
                        $flash = ['type'=>'danger','msg'=>'Failed to upload image.'];
                        $newFileName = null;
                    }
                }
            }
        } else {
            $flash = ['type'=>'danger','msg'=>'Upload error. Please try again.'];
        }
    }

    if ($flash['type'] === '') {
        try {
            $con->begin_transaction();

            // --- Update users table
            if ($newFileName) {
                $sql = "UPDATE users
                        SET first_name = ?, last_name = ?, phone_number = ?, profile_image = ?
                        WHERE user_id = ? LIMIT 1";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('ssssi', $firstName, $lastName, $phone, $newFileName, $userId);
            } else {
                $sql = "UPDATE users
                        SET first_name = ?, last_name = ?, phone_number = ?
                        WHERE user_id = ? LIMIT 1";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('sssi', $firstName, $lastName, $phone, $userId);
            }
            if (!$stmt->execute()) { throw new Exception('Failed to update profile.'); }
            $stmt->close();

            // --- Upsert into customer_details by user_id (insert if none, else update)
            $exists = false;
            if ($stmt = $con->prepare("SELECT customer_details_id FROM customer_details WHERE user_id = ? LIMIT 1")) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->bind_result($cid);
                $exists = (bool)$stmt->fetch();
                $stmt->close();
            }

            if ($exists) {
                $sql = "UPDATE customer_details
                        SET house_number = ?, street_village_sitio = ?, barangay = ?, city = ?
                        WHERE user_id = ? LIMIT 1";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('ssssi', $houseNum, $streetVill, $barangay, $city, $userId);
            } else {
                $sql = "INSERT INTO customer_details (house_number, street_village_sitio, barangay, city, user_id)
                        VALUES (?,?,?,?,?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('ssssi', $houseNum, $streetVill, $barangay, $city, $userId);
            }
            if (!$stmt->execute()) { throw new Exception('Failed to save address.'); }
            $stmt->close();

            $con->commit();

            // Refresh current values in-memory/session for re-render
            $current['first_name']     = $firstName;
            $current['last_name']      = $lastName;
            $current['phone_number']   = $phone;
            if ($newFileName) $current['profile_image'] = $newFileName;

            $address['house_number']         = $houseNum;
            $address['street_village_sitio'] = $streetVill;
            $address['barangay']             = $barangay;
            $address['city']                 = $city;

            $_SESSION['user_name'] = trim($firstName.' '.$lastName) ?: ($_SESSION['user_name'] ?? 'Customer');

            $flash = ['type'=>'success','msg'=>'Profile & address updated successfully.'];
        } catch (Throwable $e) {
            $con->rollback();
            $flash = ['type'=>'danger','msg'=>'Could not save changes.'];
        }
    }
}

// Build current image URL (if any)
$currentImgURL = '';
if (!empty($current['profile_image'])) {
    $currentImgURL = '../../uploads/' . rawurlencode($current['profile_image']);
    $_SESSION['profile_image'] = $currentImgURL;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Settings</title>
  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style.css"/>
</head>
<body>
  <?php include '../../components/sidenav_customer.php'; ?>

  <!-- Logout Modal (kept from your pattern) -->
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
    <div class="topbar">
      <span class="openbtn" onclick="openNav()">&#9776;</span>
      <h1 class="title">Profile Settings</h1>
      <span></span>
    </div>

    <div class="container">
      <?php if ($flash['type']): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <form id="profileForm" method="POST" enctype="multipart/form-data">
        <!-- Profile Photo -->
        <div class="form-group profile-photo-section">
          <label class="required">Profile photo</label>
          <div class="profile-photo-circle" id="profilePhotoCircle" title="Click to upload">
            <input type="file" id="profilePhoto" name="profile_photo" accept="image/*" class="hidden">
            <?php if ($currentImgURL): ?>
              <img id="profilePhotoPreview" src="<?= htmlspecialchars($currentImgURL) ?>" alt="Profile Photo">
            <?php else: ?>
              <img id="profilePhotoPreview" src="" class="hidden" alt="Profile Photo">
              <i class="fas fa-camera" id="photoIcon" style="font-size:28px;color:#6c757d;"></i>
            <?php endif; ?>
          </div>
          <p class="upload-text">Click the circle to upload a photo (max 2MB: jpg, jpeg, png, webp, gif)</p>
        </div>

        <!-- Names + Phone -->
        <div class="form-group">
          <label for="firstName" class="required">First Name</label>
          <input type="text" id="firstName" name="first_name"
                 value="<?= htmlspecialchars($current['first_name']) ?>"
                 placeholder="Enter your first name" disabled required>
        </div>
        <div class="form-group">
          <label for="lastName" class="required">Last Name</label>
          <input type="text" id="lastName" name="last_name"
                 value="<?= htmlspecialchars($current['last_name']) ?>"
                 placeholder="Enter your last name" disabled required>
        </div>
        <div class="form-group">
          <label for="phoneNumber" class="required">Phone Number (+63)</label>
          <input type="tel" id="phoneNumber" name="phone_number"
                 value="<?= htmlspecialchars($current['phone_number']) ?>"
                 placeholder="9XXXXXXXXX" pattern="^9\d{9}$"
                 title="Enter 10 digits starting with 9 (e.g., 9xxxxxxxxx)" disabled required>
        </div>

        <!-- Address (customer_details) -->
        <hr />
        <h3 class="header">Address</h3>

        <div class="form-group">
          <label for="house_number" class="required">House / Unit No.</label>
          <input type="text" id="house_number" name="house_number"
                 value="<?= htmlspecialchars($address['house_number']) ?>"
                 placeholder="e.g., 123-B" disabled required>
        </div>

        <div class="form-group">
          <label for="street_village_sitio" class="required">Street / Village / Sitio</label>
          <input type="text" id="street_village_sitio" name="street_village_sitio"
                 value="<?= htmlspecialchars($address['street_village_sitio']) ?>"
                 placeholder="e.g., Mabini St., Villa Maria Subd., Sitio Green" disabled required>
        </div>

        <div class="form-group">
          <label for="barangay" class="required">Barangay</label>
          <input type="text" id="barangay" name="barangay"
                 value="<?= htmlspecialchars($address['barangay']) ?>"
                 placeholder="e.g., Brgy. San Isidro" disabled required>
        </div>

        <div class="form-group">
          <label for="city" class="required">City / Municipality</label>
          <input type="text" id="city" name="city"
                 value="<?= htmlspecialchars($address['city']) ?>"
                 placeholder="e.g., Quezon City" disabled required>
        </div>

        <!-- Button row -->
        <div class="button-bar">
          <button type="button" id="btnEdit" class="btn btn-edit">
            <i class="fa-solid fa-pen"></i> Edit Profile
          </button>
          <button type="submit" id="btnSave" class="btn btn-save hidden">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>
          <button type="button" id="btnCancel" class="btn btn-cancel hidden">
            <i class="fa-solid fa-rotate-left"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  <script defer src="../../javascript/profile.js"></script>
</body>
</html>
