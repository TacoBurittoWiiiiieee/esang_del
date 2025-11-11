const form        = document.getElementById('profileForm');
const btnEdit     = document.getElementById('btnEdit');
const btnSave     = document.getElementById('btnSave');
const btnCancel   = document.getElementById('btnCancel');

const firstName   = document.getElementById('firstName');
const lastName    = document.getElementById('lastName');
const phone       = document.getElementById('phoneNumber');

// NEW: address fields
const houseNumber = document.getElementById('house_number');
const streetVill  = document.getElementById('street_village_sitio');
const barangay    = document.getElementById('barangay');
const city        = document.getElementById('city');

const photoCircle = document.getElementById('profilePhotoCircle');
const fileInput   = document.getElementById('profilePhoto');
const previewImg  = document.getElementById('profilePhotoPreview');
const photoIcon   = document.getElementById('photoIcon');

// One list for everything we toggle
const EDITABLE = [
  firstName, lastName, phone,
  houseNumber, streetVill, barangay, city
].filter(Boolean);

const initialState = {
  first: firstName?.value || '',
  last:  lastName?.value  || '',
  phone: phone?.value     || '',
  // NEW: keep originals so Cancel restores them
  house: houseNumber?.value || '',
  street: streetVill?.value || '',
  brgy:  barangay?.value    || '',
  city:  city?.value        || '',
  img:   previewImg?.src    || ''
};

function setEditable(on) {
  EDITABLE.forEach((el) => { el.disabled = !on; });
  btnSave.classList.toggle('hidden', !on);
  btnCancel.classList.toggle('hidden', !on);
  btnEdit.classList.toggle('hidden', on);
}

btnEdit.addEventListener('click', () => {
  setEditable(true);
  firstName?.focus();
});

btnCancel.addEventListener('click', () => {
  // revert text fields
  if (firstName)   firstName.value   = initialState.first;
  if (lastName)    lastName.value    = initialState.last;
  if (phone)       phone.value       = initialState.phone;
  if (houseNumber) houseNumber.value = initialState.house;
  if (streetVill)  streetVill.value  = initialState.street;
  if (barangay)    barangay.value    = initialState.brgy;
  if (city)        city.value        = initialState.city;

  // revert preview image
  if (initialState.img) {
    previewImg?.classList.remove('hidden');
    photoIcon?.classList.add('hidden');
    if (previewImg) previewImg.src = initialState.img;
  }
  if (fileInput) fileInput.value = '';

  setEditable(false);
});

// Image selection (allowed anytime)
photoCircle?.addEventListener('click', () => fileInput?.click());

fileInput?.addEventListener('change', () => {
  const file = fileInput.files && fileInput.files[0];
  if (!file) return;
  const maxBytes = 2 * 1024 * 1024;
  if (file.size > maxBytes) {
    alert('Image too large (max 2MB).');
    fileInput.value = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = (e) => {
    if (previewImg) {
      previewImg.src = e.target.result;
      previewImg.classList.remove('hidden');
    }
    photoIcon?.classList.add('hidden');
  };
  reader.readAsDataURL(file);

  // Picking a photo implies edit mode
  setEditable(true);
});

// Logout modal wiring
const logoutLink    = document.getElementById('logoutLink');
const logoutModal   = document.getElementById('logoutModal');
const cancelLogout  = document.getElementById('cancelLogout');
const confirmLogout = document.getElementById('confirmLogout');
const closeBtns     = document.querySelectorAll('#logoutModal .close-button');

logoutLink?.addEventListener('click', (e) => { e.preventDefault(); logoutModal.style.display = 'block'; });
cancelLogout?.addEventListener('click', () => { logoutModal.style.display = 'none'; });
confirmLogout?.addEventListener('click', () => { window.location.href = '../auth/logout.php'; });
closeBtns.forEach((btn) => btn.addEventListener('click', () => { logoutModal.style.display = 'none'; }));
window.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.style.display = 'none'; });
