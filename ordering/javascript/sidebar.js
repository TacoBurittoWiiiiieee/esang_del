document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const logoutLink = document.getElementById('logoutLink');
    const logoutModal = document.getElementById('logoutModal');
    const closeButton = document.querySelector('.close-button');
    const cancelLogoutButton = document.getElementById('cancelLogout');
    const confirmLogoutButton = document.getElementById('confirmLogout');
    const dropdown = document.querySelector('.dropdown-btn');

    // Show the logout modal when "Log Out" is clicked
    if (logoutLink) {
        logoutLink.addEventListener('click', function(event) {
            event.preventDefault();
            if (logoutModal) {
                logoutModal.classList.add('show');
            }
        });
    }

    // Hide the modal when the close button is clicked
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            if (logoutModal) {
                logoutModal.classList.remove('show');
            }
        });
    }

    // Hide the modal when the "Cancel" button is clicked
    if (cancelLogoutButton) {
        cancelLogoutButton.addEventListener('click', function() {
            if (logoutModal) {
                logoutModal.classList.remove('show');
            }
        });
    }

    // Handle the "Log Out" confirmation
    if (confirmLogoutButton) {
        confirmLogoutButton.addEventListener('click', function() {
            alert('Logging out...');
            window.location.href = 'login.php';
        });
    }

    // Handle dropdown menu
    if (dropdown) {
        dropdown.addEventListener('click', function() {
            this.classList.toggle('active');
            const dropdownContent = this.nextElementSibling;
            if (dropdownContent) {
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                } else {
                    dropdownContent.style.display = 'block';
                }
            }
        });
    }

    // Hide the modal if the user clicks anywhere outside of the modal content
    window.addEventListener('click', function(event) {
        if (event.target === logoutModal) {
            logoutModal.classList.remove('show');
        }
    });
});

/* Set the width of the side navigation to 250px */
function openNav() {
    const sidenav = document.getElementById("mySidenav");
    const mainContent = document.querySelector(".main-content");
    if (sidenav) sidenav.style.width = "250px";
    if (mainContent) mainContent.style.marginLeft = "250px";
}

/* Set the width of the side navigation to 0 */
function closeNav() {
    const sidenav = document.getElementById("mySidenav");
    const mainContent = document.querySelector(".main-content");
    if (sidenav) sidenav.style.width = "0";
    if (mainContent) mainContent.style.marginLeft = "0";
}