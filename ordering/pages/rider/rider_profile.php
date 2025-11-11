<!-- rider_profile.php (frontend-only) -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rider Profile Settings</title>
  <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
  <link rel="stylesheet" href="../../assets/css/rider/r_profile.css">
  <link rel="stylesheet" href="../../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="sidenav" id="mySidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="profile">
      <div class="profile-pic"><i class="fas fa-user"></i></div>
      <div class="profile-name"><span id="riderName">Rider Name</span></div>
    </div>
    <a href="order_assignments.php" class="sidenav-item"><i class="fas fa-th-large"></i> Order Assignments</a>
    <a href="order_status.php" class="sidenav-item"><i class="fas fa-clipboard-list"></i> Order Status</a>
    <a href="rider_profile.php" class="sidenav-item"><i class="fas fa-user-circle"></i> Profile</a>
    <a href="../../../rider_logout.php" class="sidenav-item" id="logoutLink"><i class="fas fa-sign-out-alt"></i> Log Out</a>
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

  <div class="main-content">
    <span class="openbtn" onclick="openNav()">&#9776;</span>
    <div class="container">
      <h1 class="title">Rider Profile Settings</h1>

      <!-- Rider Statistics Section -->
      <div class="rider-stats" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:20px;border-radius:15px;margin-bottom:30px;">
        <h3 style="margin-bottom:15px;"><i class="fas fa-chart-line"></i> Your Performance</h3>
        <div style="display:flex;justify-content:space-around;flex-wrap:wrap;gap:15px;">
          <div style="text-align:center;">
            <div id="avgRating" style="font-size:2em;font-weight:bold;">0.0</div>
            <div><i class="fas fa-star" style="color:#ffc107;"></i> Rating</div>
          </div>
          <div style="text-align:center;">
            <div id="deliveries" style="font-size:2em;font-weight:bold;">0</div>
            <div><i class="fas fa-truck"></i> Deliveries</div>
          </div>
          <div style="text-align:center;">
            <div id="earnings" style="font-size:2em;font-weight:bold;">₱0</div>
            <div><i class="fas fa-peso-sign"></i> Total Earnings</div>
          </div>
          <div style="text-align:center;">
            <div id="acctStatus" style="font-size:1.5em;font-weight:bold;padding:8px 15px;background:rgba(255,255,255,.2);border-radius:20px;">Active</div>
            <div><i class="fas fa-user-check"></i> Status</div>
          </div>
        </div>
      </div>

      <!-- Profile Form -->
      <form id="profileForm">
        <div class="form-group profile-photo-section">
          <label for="profilePhoto" class="required">Profile photo</label>
          <div class="profile-photo-circle" id="profilePhotoCircle">
            <input type="file" id="profilePhoto" accept="image/*" style="display:none;">
            <img id="profilePhotoPreview" src="#" alt="Profile Photo Preview" class="hidden">
            <i class="fas fa-camera" style="font-size:2em;color:#ccc;"></i>
          </div>
          <p class="upload-text">Click to upload photo</p>
        </div>

        <div class="form-group">
          <label for="firstName" class="required">First Name: </label>
          <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
        </div>
        <div class="form-group">
          <label for="lastName" class="required">Last Name: </label>
          <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
        </div>
        <div class="form-group">
          <label for="email">Email: </label>
          <input type="email" id="email" placeholder="Email address" readonly style="background:#f8f9fa;color:#6c757d;">
          <small style="color:#6c757d;">Email cannot be changed</small>
        </div>
        <div class="form-group">
          <label for="phoneNumber" class="required">Phone Number:</label>
          <input type="tel" id="phoneNumber" name="phone" placeholder="09xxxxxxxxx" required>
        </div>
        <div class="form-group">
          <label for="licensePlate">Driver's License Plate:</label>
          <input type="text" id="licenseplate" name="license_plate" placeholder="License plate">
        </div>

        <!-- Account Information -->
        <div style="background:#f8f9fa;padding:15px;border-radius:10px;margin:20px 0;">
          <h4><i class="fas fa-info-circle"></i> Account Information</h4>
          <p><strong>Rider ID:</strong> <span id="riderId">—</span></p>
          <p><strong>Member Since:</strong> <span id="memberSince">—</span></p>
          <p><strong>Account Status:</strong>
            <span id="accountStatusPill" style="background:#28a745;color:#fff;padding:3px 8px;border-radius:10px;font-size:.9em;">
              Active
            </span>
          </p>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button type="submit" class="save-button" style="background:#28a745;flex:1;min-width:150px;">
            <i class="fas fa-save"></i> Save Profile
          </button>
          <button type="button" onclick="showPasswordModal()" class="save-button" style="background:#ffc107;color:#212529;flex:1;min-width:150px;">
            <i class="fas fa-key"></i> Change Password
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Password Change Modal -->
  <div id="passwordModal" class="modal" style="display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);">
    <div class="modal-content" style="background:#fff;margin:15% auto;padding:20px;border-radius:10px;width:90%;max-width:400px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h3><i class="fas fa-key"></i> Change Password</h3>
        <span onclick="hidePasswordModal()" style="cursor:pointer;font-size:1.5em;color:#999;">&times;</span>
      </div>

      <form id="passwordForm">
        <div class="form-group">
          <label for="currentPassword">Current Password:</label>
          <input type="password" id="currentPassword" name="current_password" required>
        </div>
        <div class="form-group">
          <label for="newPassword">New Password:</label>
          <input type="password" id="newPassword" name="new_password" minlength="6" required>
        </div>
        <div class="form-group">
          <label for="confirmPassword">Confirm New Password:</label>
          <input type="password" id="confirmPassword" name="confirm_password" minlength="6" required>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
          <button type="button" onclick="hidePasswordModal()" style="background:#6c757d;color:#fff;border:none;padding:10px 15px;border-radius:5px;cursor:pointer;">Cancel</button>
          <button type="submit" style="background:#28a745;color:#fff;border:none;padding:10px 15px;border-radius:5px;cursor:pointer;">
            <i class="fas fa-save"></i> Update Password
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Minimal JS just for UI (no backend calls) -->
  <script>
    // Sidebar toggles so onclicks don't error
    function openNav(){ const s=document.getElementById('mySidenav'); if(s){ s.style.width='250px'; } }
    function closeNav(){ const s=document.getElementById('mySidenav'); if(s){ s.style.width='0'; } }

    // Fake data to populate the page (replace with real API later)
    const rider = {
      id: 321,
      firstName: 'Juan',
      lastName: 'Dela Cruz',
      email: 'juan@example.com',
      phone: '09171234567',
      license_number: 'N/A',
      status: 'active',
      created_at: '2024-03-15',
      name: function(){ return `${this.firstName} ${this.lastName}`; }
    };
    const stats = { average_rating: 4.8, completed_deliveries: 127, total_earnings: 58230 };

    // Fill UI from fake data
    function initProfile(){
      document.getElementById('riderName').textContent = rider.name();
      document.getElementById('firstName').value = rider.firstName;
      document.getElementById('lastName').value = rider.lastName;
      document.getElementById('email').value = rider.email;
      document.getElementById('phoneNumber').value = rider.phone;
      document.getElementById('licenseNumber').value = rider.license_number || '';

      document.getElementById('riderId').textContent = rider.id;
      document.getElementById('memberSince').textContent = new Date(rider.created_at).toLocaleDateString();
      document.getElementById('accountStatusPill').textContent = rider.status.charAt(0).toUpperCase()+rider.status.slice(1);
      document.getElementById('acctStatus').textContent = document.getElementById('accountStatusPill').textContent;

      document.getElementById('avgRating').textContent = stats.average_rating.toFixed(1);
      document.getElementById('deliveries').textContent = stats.completed_deliveries;
      document.getElementById('earnings').textContent = '₱'+stats.total_earnings.toLocaleString();
    }

    // Profile photo upload preview
    document.addEventListener('DOMContentLoaded', () => {
      initProfile();

      document.getElementById('profilePhotoCircle').addEventListener('click', () => {
        document.getElementById('profilePhoto').click();
      });

      document.getElementById('profilePhoto').addEventListener('change', (e) => {
        if (e.target.files && e.target.files[0]) {
          const reader = new FileReader();
          reader.onload = (ev) => {
            const preview = document.getElementById('profilePhotoPreview');
            preview.src = ev.target.result;
            preview.classList.remove('hidden');
            preview.style.display = 'block';
            const cam = document.querySelector('#profilePhotoCircle i');
            if (cam) cam.style.display = 'none';
          };
          reader.readAsDataURL(e.target.files[0]);
        }
      });

      // Save profile — frontend only
      document.getElementById('profileForm').addEventListener('submit', (event) => {
        event.preventDefault();
        showNotification('Profile saved (frontend only). Hook this up to your API later.', 'success');
      });

      // Password modal
      document.getElementById('passwordForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const newPass = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmPassword').value;
        if (newPass !== confirm) {
          showNotification('New passwords do not match!', 'error'); return;
        }
        showNotification('Password updated (frontend only).', 'success');
        hidePasswordModal();
      });

      // Phone validation
      document.getElementById('phoneNumber').addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g,'');
        if (v.length>11) v=v.slice(0,11);
        e.target.value = v;
      });
    });

    function showPasswordModal(){ document.getElementById('passwordModal').style.display='block'; }
    function hidePasswordModal(){ document.getElementById('passwordModal').style.display='none'; document.getElementById('passwordForm').reset(); }

    // Simple notifications
    function showNotification(msg, type='info'){
      const old = document.querySelector('.notification'); if (old) old.remove();
      const n = document.createElement('div');
      n.className='notification';
      n.style.cssText='position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:5px;color:#fff;font-weight:bold;z-index:10001;max-width:300px;box-shadow:0 4px 12px rgba(0,0,0,.3)';
      n.style.backgroundColor = {success:'#28a745',error:'#dc3545',warning:'#ffc107',info:'#007bff'}[type] || '#007bff';
      if (type==='warning') n.style.color='#212529';
      n.textContent = msg;
      document.body.appendChild(n);
      setTimeout(()=>{ if(n.parentNode){ n.remove(); } }, 4000);
    }

    // Close password modal when clicking outside
    window.addEventListener('click', (e) => {
      const modal = document.getElementById('passwordModal');
      if (e.target === modal) hidePasswordModal();
    });
  </script>
</body>
</html>
