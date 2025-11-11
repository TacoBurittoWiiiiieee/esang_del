
<?php
require_once '../../auth/session.php';
require_once '../../auth/database.php';

// Check if user is logged in as customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header('Location: ../../auth/LogIn.php');
    exit;
}

// Get user name and ID for display
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer';
$customerId = $_SESSION['customerId'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../VImages/favicon.jpg">
    <!-- Enhanced Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css" />
</head>

<body>
    
    <?php include '../../components/sidenav_customer.php'; ?>

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

    <div id="invoiceModal" class="modal">
        <div class="modal-content invoice-modal-content">
            <span class="close-button close-invoice-modal">&times;</span>
            <h2>Order Invoice</h2>
            <div id="invoice-preview-area" class="invoice-preview">
                </div>
            <div class="modal-actions invoice-actions">
                <button id="print-invoice-btn" class="button print-button"><i class="fas fa-print"></i> Print</button>
                <button id="download-invoice-btn" class="button download-button"><i class="fas fa-download"></i> Download PDF</button>
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content order-details-modal">
            <span class="close-button close-order-details">&times;</span>
            <h2>Order Details</h2>
            <div id="order-details-content" class="order-details-content">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-actions">
                <button id="close-details-btn" class="button secondary">Close</button>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <span class="openbtn" onclick="openNav()">&#9776;</span>
        
        <div class="dashboard-container">
            <h1 class="header">My Orders</h1>

            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button class="tab-button active" data-tab="cart">
                    <i class="fas fa-shopping-cart"></i> Cart
                </button>
                <button class="tab-button" data-tab="pending">
                    <i class="fas fa-hourglass-half"></i> Pending
                </button>
                <button class="tab-button" data-tab="ongoing">
                    <i class="fas fa-motorcycle"></i> Ongoing
                </button>
                <button class="tab-button" data-tab="completed">
                    <i class="fas fa-check-circle"></i> Completed
                </button>
            </div>

            <!-- Tab Content Containers -->
            <div id="tab-content" class="custom-scrollbar">
                <!-- Content will be injected here by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal" id="checkout-modal" role="dialog" aria-labelledby="checkoutModalLabel" aria-modal="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="checkoutModalLabel">Delivery & Payment Details</h2>
                    <button type="button" class="modal-close-btn" data-modal-close="checkout-modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Payment Method & Screenshot Upload -->
                    <div id="checkout-step-1" class="checkout-step">
                        <div class="step-header">
                            <h3 class="step-title">Step 1: Payment Method</h3>
                            <p class="step-description">Select your preferred payment method</p>
                        </div>
                        
                        <form id="payment-form" class="row-grid">
                            <!-- Payment Options -->
                            <div class="form-group">
                                <label class="form-label fw-semibold" for="payment-method-cod">Payment Option</label>
                                <div class="payment-options-group" role="radiogroup" aria-labelledby="payment-option-label">
                                    <label class="payment-option-label">
                                        <input type="radio" id="payment-method-cod" name="payment-method" value="Cash on Delivery" autocomplete="off" required>
                                        <span>Cash on Delivery</span>
                                    </label>
                                    <label class="payment-option-label">
                                        <input type="radio" id="payment-method-gcash" name="payment-method" value="GCash" autocomplete="off" required>
                                        <span>GCash</span>
                                    </label>
                                    <label class="payment-option-label">
                                        <input type="radio" id="payment-method-bank" name="payment-method" value="Bank Transfer" autocomplete="off" required>
                                        <span>Bank Transfer (Metrobank)</span>
                                    </label>
                                </div>
                            </div>
                            
                        </form>
                        
                        <!-- Step 1 Actions -->
                        <div class="modal-footer border-top pt-3">
                            <button type="button" class="btn btn-secondary" data-modal-close="checkout-modal">Cancel</button>
                            <button type="button" id="next-to-address-btn" class="btn btn-primary fw-bold">Next: Delivery Address</button>
                        </div>
                    </div>
                    
                    <!-- Step 2: Delivery Address -->
                    <div id="checkout-step-2" class="checkout-step" style="display: none;">
                        <div class="step-header">
                            <h3 class="step-title">Step 2: Delivery Address</h3>
                            <p class="step-description">Where should we deliver your order?</p>
                        </div>
                        
                        <form id="address-form" class="row-grid">
                            <!-- Selected Payment Method Display -->
                            <div class="form-group">
                                <div class="selected-payment-display" id="selected-payment-display">
                                    <div class="payment-summary">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Payment Method: <strong id="selected-payment-text"></strong></span>
                                        <button type="button" id="change-payment-btn" class="btn-link">Change</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Exact Address</label>
                                <input type="text" id="address" name="address" required class="form-control" autocomplete="street-address" placeholder="House/Unit number, street name">
                            </div>
                            
                            <div class="row-grid row-grid-2-col">
                                <div class="form-group" style="display: none;">
                                    <label for="region" class="form-label">Region</label>
                                    <select id="region" name="region" autocomplete="address-level1" class="form-select">
                                        <!-- Hidden - automatically set to Metro Manila -->
                                    </select>
                                </div>
                                <div class="form-group" style="display: none;">
                                    <label for="city" class="form-label">City</label>
                                    <select id="city" name="city" autocomplete="address-level2" class="form-select">
                                        <!-- Hidden - automatically set to Caloocan City -->
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Display info about fixed location -->
                            <div class="form-group">
                                <div style="background-color: #e9f7ef; border: 1px solid #27ae60; border-radius: 6px; padding: 15px; color: #27ae60;">
                                    <i class="fas fa-info-circle"></i> <strong>Delivery Location:</strong> Metro Manila - North Caloocan only
                                </div>
                            </div>

                            <div class="row-grid">
                                <div class="form-group" style="display: none;">
                                    <label for="district" class="form-label">District</label>
                                    <select id="district" name="district" autocomplete="off" class="form-select">
                                        <!-- Hidden - not used for simplified barangay selection -->
                                    </select>
                                </div>
                                <div class="form-group">
                                <label for="barangay" class="form-label">Barangay</label>
                                <select id="barangay" name="barangay" autocomplete="address-level3" required class="form-select">
                                    <option value="">Select Barangay</option>
                                </select>
                            </div>
                            </div>
                            
                        </form>
                        
                        <!-- Step 2 Actions -->
                        <div class="modal-footer border-top pt-3">
                            <button type="button" id="back-to-payment-btn" class="btn btn-secondary">Back to Payment</button>
                            <button type="button" id="next-to-screenshot-btn" class="btn btn-primary fw-bold" style="display: none;">Next: Payment Proof</button>
                            <button type="button" id="place-order-btn" class="btn btn-danger fw-bold">Place Order</button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Payment Screenshot Upload -->
                    <div id="checkout-step-3" class="checkout-step" style="display: none;">
                        <div class="step-header">
                            <h3 class="step-title">Step 3: Payment Proof</h3>
                            <p class="step-description">Upload your payment screenshot</p>
                        </div>
                        
                        <form id="screenshot-form" class="row-grid">
                            <!-- Selected Payment Method Display -->
                            <div class="form-group">
                                <div class="selected-payment-display" id="selected-payment-display-step3">
                                    <div class="payment-summary">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Payment Method: <strong id="selected-payment-text-step3"></strong></span>
                                        <button type="button" id="change-payment-step3-btn" class="btn-link">Change</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Details Form -->
                            <div class="form-group">
                                <label for="payment-name" class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                                <input type="text" id="payment-name" name="payment_name" required class="form-control" autocomplete="name" placeholder="Enter the name on your account">
                                <small class="text-muted">Enter the name associated with your GCash/Bank account</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment-reference" class="form-label fw-semibold">Reference Number <span class="text-danger">*</span></label>
                                <input type="text" id="payment-reference" name="payment_reference" required class="form-control" autocomplete="off" placeholder="Enter transaction reference number">
                                <small class="text-muted">Enter the reference/transaction ID from your payment</small>
                            </div>
                            
                            <!-- Payment Screenshot Upload -->
                            <div class="form-group" id="payment-screenshot-section">
                                <label for="payment-screenshot-input" class="form-label fw-semibold">Payment Screenshot <span class="text-danger">*</span></label>
                                <div class="file-upload-area" id="file-upload-area">
                                    <div class="file-upload-content">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #6c757d; margin-bottom: 10px;"></i>
                                        <p class="upload-text">Drag & drop your payment screenshot here</p>
                                        <p class="upload-subtext">or click to browse files</p>
                                        <p class="upload-requirements">JPG, JPEG, PNG files only (Max 5MB)</p>
                                        <input type="file" id="payment-screenshot-input" name="payment_screenshot" accept=".jpg,.jpeg,.png" autocomplete="off" style="display: none;">
                                    </div>
                                    <div class="file-preview" id="file-preview" style="display: none;">
                                        <div class="preview-content">
                                            <img id="preview-image" src="" alt="Payment Screenshot Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-bottom: 10px;">
                                            <p id="preview-filename" class="fw-semibold"></p>
                                            <p id="preview-filesize" class="text-muted small"></p>
                                            <button type="button" class="btn btn-secondary btn-sm" id="remove-file-btn">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-info" id="payment-info" style="margin-top: 15px; display: block;">
                                    <!-- Payment instructions will be populated here -->
                                </div>
                            </div>
                        </form>
                        
                        <!-- Step 3 Actions -->
                        <div class="modal-footer border-top pt-3">
                            <button type="button" id="back-to-address-btn" class="btn btn-secondary">Back to Address</button>
                            <button type="button" id="place-order-final-btn" class="btn btn-danger fw-bold">Place Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal" id="invoice-modal" role="dialog" aria-labelledby="invoiceModalLabel" aria-modal="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="invoiceModalLabel">Order Invoice Preview</h2>
                    <button type="button" class="modal-close-btn" data-modal-close="invoice-modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="invoice-content" class="custom-scrollbar">
                        <!-- Invoice content will be injected here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="printInvoice()" class="btn btn-primary fw-semibold print-hidden">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <button type="button" class="btn btn-secondary fw-semibold" data-modal-close="invoice-modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="toast-container">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-content">
                <div class="toast-body">
                    Message goes here
                </div>
                <button type="button" class="toast-close-btn" onclick="document.getElementById('toast').classList.remove('show')" aria-label="Close">&times;</button>
            </div>
        </div>
    </div>
    <script>
        // Pass user information to JavaScript
        window.customerData = {
            id: <?php echo $customerId; ?>,
            name: '<?php echo addslashes($userName); ?>'
        };
        
        // --- Enhanced Location Data and App State Management ---
        const ENHANCED_LOCATIONS = {
            "Metro Manila": {
                "North Caloocan": ["Barangay 165", "Barangay 166", "Barangay 167", "Barangay 168", "Barangay 169", "Barangay 170", "Barangay 171", "Barangay 172", "Barangay 173", "Barangay 174", "Barangay 175", "Barangay 176", "Barangay 177"]
            }
        };

        let appState = {
            activeTab: 'cart',
            cart: [],
            pending: [],
            ongoing: [],
            completed: [],
            nextOrderId: 1000,
            backendIntegrated: false
        };
        
        // Load cart items from localStorage (integration with customer dashboard)
        function loadCartFromLocalStorage() {
            console.log('Loading cart from localStorage...');
            console.log('Raw localStorage customerCart:', localStorage.getItem('customerCart'));
            
            let cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];
            console.log('Parsed cart items:', cartItems);
            
            if (cartItems.length === 0) {
                console.log('Cart is empty from localStorage');
                // Let's check if there are items in any other keys
                console.log('All localStorage keys:', Object.keys(localStorage));
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.toLowerCase().includes('cart')) {
                        console.log(`Found cart-related key: ${key} =`, localStorage.getItem(key));
                    }
                }
            }
            
            // Normalize structure
            appState.cart = cartItems.map((item, index) => {
                console.log(`Processing cart item ${index}:`, item);
                return {
                    id: item.id || index + 1,
                    name: item.name,
                    price: parseFloat(item.price) || 0,
                    quantity: parseInt(item.quantity) || 1,
                    size: item.size || null,
                    flavors: item.flavors || null,
                    packageDetails: item.packageDetails || null,
                    img: item.image || item.img || '../VImages/placeholder.jpg',
                    prodId: item.prodId || item.product_id || null
                };
            });
            
            console.log('Final normalized cart state:', appState.cart);
            console.log('Cart length after loading:', appState.cart.length);
        }
        
        // Load completed orders from backend
        async function loadCompletedOrdersFromBackend() {
            try {
            const response = await fetch('http://localhost:8080/public/api/get_customer_orders.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                if (result.success) {
                    appState.completed = result.orders.map(order => ({
                        orderId: order.orderNumber || order.id,
                        date: order.date || new Date().toLocaleDateString(),
                        items: order.items || [],
                        total: order.total || order.totalAmount || 0,
                        details: {
                            payment: order.payment || order.paymentMethod,
                            address: order.deliveryAddress || order.location
                        }
                    }));
                }
                appState.backendIntegrated = true;
            } catch (error) {
                console.error('Error loading completed orders:', error);
                // Keep sample data as fallback
                appState.completed = [
                    {
                        orderId: 'ORD999',
                        date: new Date().toLocaleDateString(),
                        items: [{name: "Sample Order", qty: 1, price: 120.00}],
                        total: 120.00,
                        details: {payment: 'Cash on Delivery', address: 'Sample Address'}
                    }
                ];
            }
        }

        // --- Enhanced Utility Functions ---

        /**
         * Shows a custom toast notification.
         */
        function showToast(message, duration = 3000) {
            const toastEl = document.getElementById('toast');
            if (toastEl) {
                toastEl.querySelector('.toast-body').textContent = message;
                
                // Show toast using custom CSS class
                toastEl.classList.add('show');
                
                // Hide toast after duration
                setTimeout(() => {
                    toastEl.classList.remove('show');
                }, duration);
            }
        }

        /**
         * Opens a custom modal by toggling CSS classes.
         */
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('visible');
                document.body.classList.add('modal-open');
            }
        }

        /**
         * Closes a custom modal by toggling CSS classes.
         */
        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('visible');
                document.body.classList.remove('modal-open');
            }
        }
        
        // Add event listener to modal close buttons (X and Cancel)
        document.addEventListener('click', (e) => {
            const closeTarget = e.target.closest('[data-modal-close]');
            if (closeTarget) {
                e.preventDefault();
                const modalId = closeTarget.getAttribute('data-modal-close');
                closeModal(modalId);
            }
        });

        function calculateTotal() {
            return appState.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        function adjustQuantity(itemId, change) {
            const itemIndex = appState.cart.findIndex(item => item.id === itemId);

            if (itemIndex !== -1) {
                appState.cart[itemIndex].quantity += change;
                
                if (appState.cart[itemIndex].quantity <= 0) {
                    appState.cart.splice(itemIndex, 1); // Remove if quantity is 0 or less
                    showToast(`Item removed from cart.`, 1500);
                }
                renderCart();
            }
        }

        function switchTab(tabName) {
            appState.activeTab = tabName;

            // Update button styles
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            const activeBtn = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Render content
            switch (tabName) {
                case 'cart':
                    renderCart();
                    break;
                case 'pending':
                    renderPending();
                    break;
                case 'ongoing':
                    renderOngoing();
                    break;
                case 'completed':
                    renderCompleted();
                    break;
            }
        }

        // --- Enhanced Rendering Functions ---

        function renderCart() {
            const contentDiv = document.getElementById('tab-content');
            const cartTotal = calculateTotal();
            console.log('Rendering cart with items:', appState.cart);
            console.log('Cart total:', cartTotal);
            console.log('Cart length:', appState.cart.length);
            let html = `
                <div id="cart-content">
                    <div class="cart-list">
                        ${appState.cart.length > 0 ? appState.cart.map(item => `
                            <div class="cart-item">
                                <div class="cart-item-left">
                                    <img src="${item.img || '../VImages/placeholder.jpg'}" alt="${item.name}" style="width: 60px; height: 60px; border-radius: 4px; object-fit: cover;">
                                    <div class="cart-item-details">
                                        <p class="fw-semibold mb-0">${item.name}</p>
                                        <p class="unit-price small mb-0">Unit Price: ₱${item.price.toFixed(2)}</p>
                                    </div>
                                </div>
                                
                                <div class="cart-item-right">
                                    <div class="quantity-control">
                                        <button onclick="adjustQuantity(${item.id}, -1)"><i class="fas fa-minus"></i></button>
                                        <span>${item.quantity}</span>
                                        <button onclick="adjustQuantity(${item.id}, 1)"><i class="fas fa-plus"></i></button>
                                    </div>
                                    <p class="item-total">₱${(item.price * item.quantity).toFixed(2)}</p>
                                </div>
                            </div>
                        `).join('') : '<p class="text-center py-5 text-muted fw-semibold" style="font-size: 1.25rem;"><i class="fas fa-box-open"></i> Your cart is empty.</p>'}
                    </div>

                    <!-- Cart Summary and Checkout Button -->
                    <div class="checkout-summary">
                        <p class="total-text mb-0">Total: <span class="total-amount">₱${cartTotal.toFixed(2)}</span></p>
                        <button onclick="startCheckout()" ${appState.cart.length === 0 ? 'disabled' : ''} class="btn btn-danger fw-bold" style="min-width: 200px; padding: 15px 30px;">
                            <i class="fas fa-credit-card"></i> Checkout
                        </button>
                    </div>
                </div>
            `;
            contentDiv.innerHTML = html;
        }

        function renderPending() {
            const contentDiv = document.getElementById('tab-content');
            let html = appState.pending.length > 0 ? appState.pending.map(order => createOrderCard(order, 'pending')).join('') : 
                '<p class="text-center py-5 text-muted fw-semibold" style="font-size: 1.25rem;"><i class="fas fa-hourglass-start"></i> No orders are currently pending approval.</p>';
            contentDiv.innerHTML = `<div class="order-list">${html}</div>`;
        }

        function renderOngoing() {
            const contentDiv = document.getElementById('tab-content');
            let html = appState.ongoing.length > 0 ? appState.ongoing.map(order => createOrderCard(order, 'ongoing')).join('') : 
                '<p class="text-center py-5 text-muted fw-semibold" style="font-size: 1.25rem;"><i class="fas fa-motorcycle"></i> No orders are currently being delivered.</p>';
            contentDiv.innerHTML = `<div class="order-list">${html}</div>`;
        }

        function renderCompleted() {
            const contentDiv = document.getElementById('tab-content');
            let html = appState.completed.length > 0 ? appState.completed.map(order => createOrderCard(order, 'completed')).join('') : 
                '<p class="text-center py-5 text-muted fw-semibold" style="font-size: 1.25rem;"><i class="fas fa-history"></i> No recent completed orders.</p>';
            contentDiv.innerHTML = `<div class="order-list">${html}</div>`;
        }

        function createOrderCard(order, status) {
            const statusClassMap = {
                'pending': 'status-pending',
                'ongoing': 'status-ongoing',
                'completed': 'status-completed',
            };
            const statusClass = statusClassMap[status];

            let actionButtons = '';
            if (status === 'ongoing') {
                actionButtons = `
                    <button onclick="completeOrder('${order.orderId}')" class="btn btn-success fw-semibold">
                        Simulate Delivery Complete
                    </button>
                `;
            } else if (status === 'completed') {
                actionButtons = `
                    <div class="action-group">
                        <button onclick="viewInvoice('${order.orderId}')" class="btn btn-primary fw-semibold">
                            <i class="fas fa-receipt"></i> View Invoice
                        </button>
                        <button onclick="window.location.href='feedback.php'" class="btn btn-purple fw-semibold">
                            <i class="fas fa-star"></i> Feedback
                        </button>
                        <button onclick="moveToHistory('${order.orderId}')" class="btn btn-dark fw-semibold">
                            <i class="fas fa-archive"></i> Order Complete
                        </button>
                    </div>
                `;
            }

            return `
                <div class="order-card ${statusClass}">
                    <div class="order-card-header">
                        <div>
                            <p class="order-id">Order #${order.orderId}</p>
                            <p class="order-date">Date Placed: ${order.date}</p>
                        </div>
                        <span class="order-status">${status.toUpperCase()}</span>
                    </div>

                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item-detail">
                                <span class="fw-semibold">${item.qty}x ${item.name}</span>
                                <span>₱${(item.price * item.qty).toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>

                    <div class="order-total-section">
                        <p class="order-total-label">Total Amount:</p>
                        <p class="order-total-amount">₱${order.total.toFixed(2)}</p>
                    </div>
                    
                    ${actionButtons ? `<div class="order-actions">${actionButtons}</div>` : ''}
                </div>
            `;
        }

        // --- Location Dropdown Logic ---

        function populateLocations() {
            const regionSelect = document.getElementById('region');
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const barangaySelect = document.getElementById('barangay');

            if (!regionSelect || !citySelect || !districtSelect || !barangaySelect) return;

            // Helper to clear and reset dropdowns
            const resetSelect = (select, placeholder, disabled) => {
                select.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                select.disabled = disabled;
                select.classList.toggle('disabled', disabled);
            };

            // Set Metro Manila and North Caloocan as fixed options (disabled dropdowns)
            regionSelect.innerHTML = '<option value="Metro Manila" selected>Metro Manila</option>';
            regionSelect.disabled = true;
            regionSelect.classList.add('disabled');

            citySelect.innerHTML = '<option value="North Caloocan" selected>North Caloocan</option>';
            citySelect.disabled = true;
            citySelect.classList.add('disabled');

            // Hide district field since we're not using it
            districtSelect.style.display = 'none';
            districtSelect.innerHTML = '<option value="N/A" selected>N/A</option>';
            districtSelect.disabled = true;

            // Populate barangays directly for North Caloocan
            resetSelect(barangaySelect, 'Select Barangay', false);
            ENHANCED_LOCATIONS["Metro Manila"]["North Caloocan"].forEach(barangay => {
                barangaySelect.innerHTML += `<option value="${barangay}">${barangay}</option>`;
            });
        }

        // --- Checkout & Place Order Logic ---

        function startCheckout() {
            console.log('startCheckout called - Cart length:', appState.cart.length);
            console.log('Cart contents:', appState.cart);
            
            if (appState.cart.length === 0) {
                console.warn('Cart is empty, cannot checkout');
                showToast("Your cart is empty. Please add items before checking out.", 3000);
                return;
            }
            
            console.log('Proceeding with checkout...');
            
            // Reset checkout state
            selectedPaymentMethod = null;
            selectedFile = null;
            currentCheckoutStep = 1;
            
            console.log('Opening checkout modal...');
            openModal('checkout-modal');
            showStep(1); // Always start with step 1
            
            console.log('Checkout modal should be open now');
        }

        // Two-step checkout management
        let selectedPaymentMethod = null;
        let currentCheckoutStep = 1;
        
        function initializeThreeStepCheckout() {
            const nextToAddressBtn = document.getElementById('next-to-address-btn');
            const nextToScreenshotBtn = document.getElementById('next-to-screenshot-btn');
            const backToPaymentBtn = document.getElementById('back-to-payment-btn');
            const backToAddressBtn = document.getElementById('back-to-address-btn');
            const placeOrderBtn = document.getElementById('place-order-btn');
            const placeOrderFinalBtn = document.getElementById('place-order-final-btn');
            const changePaymentBtn = document.getElementById('change-payment-btn');
            const changePaymentStep3Btn = document.getElementById('change-payment-step3-btn');
            
            // Step 1 to Step 2 transition
            if (nextToAddressBtn) {
                nextToAddressBtn.addEventListener('click', handleNextToAddress);
            }
            
            // Step 2 to Step 3 transition (for digital payments)
            if (nextToScreenshotBtn) {
                nextToScreenshotBtn.addEventListener('click', handleNextToScreenshot);
            }
            
            // Step 2 back to Step 1
            if (backToPaymentBtn) {
                backToPaymentBtn.addEventListener('click', () => showStep(1));
            }
            
            // Step 3 back to Step 2
            if (backToAddressBtn) {
                backToAddressBtn.addEventListener('click', () => showStep(2));
            }
            
            // Change payment method from step 2
            if (changePaymentBtn) {
                changePaymentBtn.addEventListener('click', () => showStep(1));
            }
            
            // Change payment method from step 3
            if (changePaymentStep3Btn) {
                changePaymentStep3Btn.addEventListener('click', () => showStep(1));
            }
            
            // Place order from step 2 (COD only)
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', handlePlaceOrder);
            }
            
            // Place order from step 3 (digital payments)
            if (placeOrderFinalBtn) {
                placeOrderFinalBtn.addEventListener('click', handlePlaceOrderFinal);
            }
        }
        
        function handleNextToAddress() {
            // Validate payment method selection
            const selectedMethod = document.querySelector('input[name="payment-method"]:checked');
            if (!selectedMethod) {
                showToast('Please select a payment method first.', 3000);
                return;
            }
            
            selectedPaymentMethod = selectedMethod.value;
            
            // Show step 2
            showStep(2);
            updatePaymentSummary();
        }
        
        function showStep(stepNumber) {
            const step1 = document.getElementById('checkout-step-1');
            const step2 = document.getElementById('checkout-step-2');
            const step3 = document.getElementById('checkout-step-3');
            
            // Hide all steps
            step1.style.display = 'none';
            step2.style.display = 'none';
            step3.style.display = 'none';
            
            if (stepNumber === 1) {
                step1.style.display = 'block';
                currentCheckoutStep = 1;
            } else if (stepNumber === 2) {
                step2.style.display = 'block';
                currentCheckoutStep = 2;
                populateLocations();
                updateStep2Buttons();
            } else if (stepNumber === 3) {
                step3.style.display = 'block';
                currentCheckoutStep = 3;
                updatePaymentSummaryStep3();
                showPaymentInstructions(selectedPaymentMethod);
            }
        }
        
        function updatePaymentSummary() {
            const paymentText = document.getElementById('selected-payment-text');
            if (paymentText && selectedPaymentMethod) {
                paymentText.textContent = selectedPaymentMethod;
            }
        }
        
        function updatePaymentSummaryStep3() {
            const paymentText = document.getElementById('selected-payment-text-step3');
            if (paymentText && selectedPaymentMethod) {
                paymentText.textContent = selectedPaymentMethod;
            }
        }
        
        function updateStep2Buttons() {
            const nextToScreenshotBtn = document.getElementById('next-to-screenshot-btn');
            const placeOrderBtn = document.getElementById('place-order-btn');
            
            if (selectedPaymentMethod === 'GCash' || selectedPaymentMethod === 'Bank Transfer') {
                // Show "Next: Payment Proof" for digital payments
                if (nextToScreenshotBtn) nextToScreenshotBtn.style.display = 'inline-block';
                if (placeOrderBtn) placeOrderBtn.style.display = 'none';
            } else {
                // Show "Place Order" for Cash on Delivery
                if (nextToScreenshotBtn) nextToScreenshotBtn.style.display = 'none';
                if (placeOrderBtn) placeOrderBtn.style.display = 'inline-block';
            }
        }
        
        function handleNextToScreenshot() {
            // Validate address form first
            const addressForm = document.getElementById('address-form');
            const formData = new FormData(addressForm);
            
            const exactAddress = formData.get('address');
            const barangay = formData.get('barangay');
            
            if (!exactAddress || !barangay) {
                showToast('Please fill out all required address fields first.', 3000);
                return;
            }
            
            // Move to step 3
            showStep(3);
        }
        
        async function handlePlaceOrder() {
            // This function is for COD orders from Step 2
            const addressForm = document.getElementById('address-form');
            const formData = new FormData(addressForm);
            
            const addressDetails = {
                exactAddress: formData.get('address'),
                region: 'Metro Manila', // Fixed for North Caloocan only
                city: 'North Caloocan', // Fixed for North Caloocan only
                district: 'N/A', // Not used for simplified barangay selection
                barangay: formData.get('barangay')
            };
            
            // Validate required address fields
            if (!addressDetails.exactAddress || !addressDetails.barangay) {
                showToast('Please fill out all required address fields.', 3000);
                return;
            }
            
            // Proceed with COD order submission
            await submitOrder(selectedPaymentMethod, addressDetails, null);
        }
        
        async function handlePlaceOrderFinal() {
            // This function is for digital payment orders from Step 3
            
            // Validate payment details
            const paymentName = document.getElementById('payment-name').value.trim();
            const paymentReference = document.getElementById('payment-reference').value.trim();
            
            if (!paymentName) {
                showToast('Please enter the account name.', 3000);
                return;
            }
            
            if (!paymentReference) {
                showToast('Please enter the reference number.', 3000);
                return;
            }
            
            // Validate screenshot upload
            if (!selectedFile) {
                showToast('Please upload your payment screenshot before placing the order.', 4000);
                return;
            }
            
            // Get address details from Step 2 (they should be stored)
            const addressForm = document.getElementById('address-form');
            const formData = new FormData(addressForm);
            
            const addressDetails = {
                exactAddress: formData.get('address'),
                region: 'Metro Manila',
                city: 'North Caloocan',
                district: 'N/A',
                barangay: formData.get('barangay')
            };
            
            // Proceed with digital payment order submission with payment details
            const paymentDetails = {
                accountName: paymentName,
                referenceNumber: paymentReference
            };
            
            await submitOrder(selectedPaymentMethod, addressDetails, paymentDetails);
        }
        
        // Enhanced form submission handler with backend integration
        async function submitOrder(paymentMethod, addressDetails, paymentDetails = null) {
            if (appState.cart.length === 0) {
                showToast("Your cart is empty. Please add items first.", 3000);
                return;
            }

            const fullAddress = `${addressDetails.exactAddress}, ${addressDetails.barangay}, ${addressDetails.city}, ${addressDetails.region}`;

            // Prepare backend payload
            const customerId = window.customerData?.id || 1;
            const orderManagerId = 1;
            const items = appState.cart.map(item => {
                const payload = {
                    quantity: item.quantity,
                    name: item.name
                };
                if (item.prodId) {
                    payload.prodId = item.prodId;
                    payload.product_id = item.prodId;
                }
                return payload;
            });

            const backendPayload = {
                customerId,
                orderManagerId,
                deliveryAddress: fullAddress,
                orderStatus: 'Pending',
                items,
                paymentMethod: paymentMethod
            };

            try {
                showToast("Processing your order...", 2000);
                
                // First place the order
                const response = await fetch('http://localhost:8080/public/api/order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(backendPayload)
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    const orderId = data.orderId;
                    
                    // If payment screenshot is required, upload it
                    if (selectedFile && (paymentMethod === 'GCash' || paymentMethod === 'Bank Transfer')) {
                        showToast("Uploading payment screenshot...", 2000);
                        
                        const formData = new FormData();
                        formData.append('payment_screenshot', selectedFile);
                        formData.append('order_id', orderId);
                        formData.append('customer_id', customerId);
                        
                        // Add payment details if available
                        if (paymentDetails) {
                            formData.append('payment_account_name', paymentDetails.accountName);
                            formData.append('payment_reference_number', paymentDetails.referenceNumber);
                        }
                        
                        const uploadResponse = await fetch('http://localhost:8080/api/upload_payment_screenshot.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const uploadResult = await uploadResponse.json();
                        
                        if (!uploadResult.success) {
                            showToast('Order placed but payment screenshot upload failed: ' + uploadResult.error, 5000);
                        }
                    }
                    // Create order object for local state
                    const newOrder = {
                        orderId: data.orderId || ('ORD' + Date.now()),
                        date: new Date().toISOString().split('T')[0],
                        items: appState.cart.map(item => ({
                            name: item.name,
                            qty: item.quantity,
                            price: item.price
                        })),
                        total: data.totalAmount || calculateTotal(),
                        details: {
                            payment: paymentMethod,
                            address: fullAddress
                        }
                    };

                    // Add to pending orders
                    appState.pending.unshift(newOrder);
                    
                    // Clear cart and localStorage
                    appState.cart = [];
                    localStorage.removeItem('customerCart');
                    localStorage.setItem('customerCODAddress', fullAddress);

                    closeModal('checkout-modal');
                    switchTab('pending');
                    showToast(`Order #${newOrder.orderId} placed successfully! It's now pending approval.`, 5000);

                    // Simulate order progression
                    setTimeout(() => {
                        simulateApproval(newOrder.orderId);
                    }, 3000);
                    
                } else {
                    showToast('Order failed: ' + (data.error || 'Unknown error'), 5000);
                }
                
            } catch (error) {
                console.error('Error placing order:', error);
                showToast('Error placing order. Please try again.', 5000);
            }
        }

        // --- Order Status Transitions ---

        function simulateApproval(orderId) {
            const index = appState.pending.findIndex(o => o.orderId === orderId);
            if (index !== -1) {
                const order = appState.pending.splice(index, 1)[0];
                order.status = 'ongoing';
                appState.ongoing.unshift(order);
                if (appState.activeTab === 'pending' || appState.activeTab === 'ongoing') {
                    switchTab(appState.activeTab);
                }
                showToast(`Order #${orderId} approved and moved to ONGOING! Delivery is starting.`, 5000);
            }
        }

        function completeOrder(orderId) {
            const index = appState.ongoing.findIndex(o => o.orderId === orderId);
            if (index !== -1) {
                const order = appState.ongoing.splice(index, 1)[0];
                order.status = 'completed';
                appState.completed.unshift(order);
                switchTab('completed');
                showToast(`Order #${orderId} completed! Please review and finalize the order.`, 5000);
            }
        }

        function moveToHistory(orderId) {
            const index = appState.completed.findIndex(o => o.orderId === orderId);
            if (index !== -1) {
                appState.completed.splice(index, 1);
                showToast(`Order #${orderId} moved to history.`, 2000);
                switchTab('completed');
            }
        }

        // --- Invoice Logic ---
        
        function generateInvoiceContent(order) {
            const date = new Date().toLocaleDateString();
            let itemsHtml = order.items.map(item => `
                <tr>
                    <td>${item.name}</td>
                    <td style="text-align: center;">${item.qty}</td>
                    <td style="text-align: right;">₱${item.price.toFixed(2)}</td>
                    <td style="text-align: right; font-weight: 600;">₱${(item.price * item.qty).toFixed(2)}</td>
                </tr>
            `).join('');

            return `
                <div class="invoice-content-inner">
                    <div class="invoice-header">
                        <h1 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-red); margin-bottom: 5px;">Esang Delicacies</h1>
                        <p class="small text-muted mb-0">Official Sales Invoice</p>
                    </div>

                    <div class="invoice-details">
                        <div>
                            <p><strong>Order ID:</strong> ${order.orderId}</p>
                            <p><strong>Date:</strong> ${date}</p>
                        </div>
                        <div style="text-align: right;">
                            <p><strong>Payment:</strong> ${order.details.payment}</p>
                            <p><strong>Address:</strong> ${order.details.address.split(',')[0]}...</p>
                        </div>
                    </div>

                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>

                    <div class="invoice-summary">
                        <div class="invoice-totals">
                            <div>
                                <span>Subtotal:</span>
                                <span class="fw-semibold">₱${order.total.toFixed(2)}</span>
                            </div>
                            <div class="total-row" style="border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: 5px;">
                                <span style="font-size: 1.15rem; font-weight: 700; color: var(--primary-red);">TOTAL DUE:</span>
                                <span style="font-size: 1.4rem; font-weight: 800; color: var(--primary-red);">₱${order.total.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function viewInvoice(orderId) {
            const order = appState.completed.find(o => o.orderId === orderId);
            if (order) {
                document.getElementById('invoice-content').innerHTML = generateInvoiceContent(order);
                openModal('invoice-modal');
            }
        }

        function printInvoice() {
            const content = document.getElementById('invoice-content').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Invoice</title>');
            printWindow.document.write('<style>body { font-family: "Inter", sans-serif; margin: 30px; font-size: 12px;} .invoice-content-inner { padding: 0; } .invoice-table th, .invoice-table td { padding: 8px 5px; border-bottom: 1px solid #ccc; } .invoice-table { width: 100%; border-collapse: collapse; } .text-muted { color: #666; } .fw-bold { font-weight: 700; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }

        // --- File Upload Functionality ---
        
        let selectedFile = null;
        
        function initializeFileUpload() {
            const fileUploadArea = document.getElementById('file-upload-area');
            const fileInput = document.getElementById('payment-screenshot-input');
            const filePreview = document.getElementById('file-preview');
            const uploadContent = document.querySelector('.file-upload-content');
            const removeBtn = document.getElementById('remove-file-btn');
            
            if (!fileUploadArea || !fileInput) return;
            
            // Click to upload
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                handleFileSelect(e.target.files[0]);
            });
            
            // Drag and drop events
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('dragover');
            });
            
            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('dragover');
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });
            
            // Remove file button
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    clearSelectedFile();
                });
            }
        }
        
        function handleFileSelect(file) {
            if (!file) return;
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Invalid file type. Please select a JPG, JPEG, or PNG file.', 4000);
                return;
            }
            
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showToast('File is too large. Maximum size is 5MB.', 4000);
                return;
            }
            
            selectedFile = file;
            displayFilePreview(file);
        }
        
        function displayFilePreview(file) {
            const uploadContent = document.querySelector('.file-upload-content');
            const filePreview = document.getElementById('file-preview');
            const previewImage = document.getElementById('preview-image');
            const previewFilename = document.getElementById('preview-filename');
            const previewFilesize = document.getElementById('preview-filesize');
            
            if (!uploadContent || !filePreview) return;
            
            // Read file for preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewFilename.textContent = file.name;
                previewFilesize.textContent = formatFileSize(file.size);
                
                // Show preview, hide upload area
                uploadContent.style.display = 'none';
                filePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        function clearSelectedFile() {
            selectedFile = null;
            const uploadContent = document.querySelector('.file-upload-content');
            const filePreview = document.getElementById('file-preview');
            const fileInput = document.getElementById('payment-screenshot-input');
            
            if (uploadContent && filePreview) {
                uploadContent.style.display = 'block';
                filePreview.style.display = 'none';
            }
            
            if (fileInput) {
                fileInput.value = '';
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // --- Payment Method Change Handler ---
        
        function handlePaymentMethodChange() {
            const paymentMethods = document.querySelectorAll('input[name="payment-method"]');
            const screenshotSection = document.getElementById('payment-screenshot-section');
            const paymentInfo = document.getElementById('payment-info');
            
            paymentMethods.forEach(method => {
                method.addEventListener('change', () => {
                    const selectedMethod = method.value;
                    
                    if (selectedMethod === 'GCash' || selectedMethod === 'Bank Transfer') {
                        screenshotSection.style.display = 'block';
                        showPaymentInstructions(selectedMethod);
                    } else {
                        screenshotSection.style.display = 'none';
                        clearSelectedFile();
                    }
                });
            });
        }
        
        function showPaymentInstructions(paymentMethod) {
            const paymentInfo = document.getElementById('payment-info');
            if (!paymentInfo) return;
            
            let instructions = '';
            
            if (paymentMethod === 'GCash') {
                instructions = `
                    <h4><i class="fab fa-google-pay"></i> GCash Payment Instructions</h4>
                    <p>1. Open your GCash app and send payment to:</p>
                    <div class="account-details">
                        <strong>GCash Number:</strong> 09XX-XXX-XXXX<br>
                        <strong>Account Name:</strong> Esang Delicacies
                    </div>
                    <p>2. Take a screenshot of the successful transaction</p>
                    <p>3. Upload the screenshot above before placing your order</p>
                `;
            } else if (paymentMethod === 'Bank Transfer') {
                instructions = `
                    <h4><i class="fas fa-university"></i> Metrobank Transfer Instructions</h4>
                    <p>1. Transfer payment to our Metrobank account:</p>
                    <div class="account-details">
                        <strong>Account Number:</strong> XXXX-XXXX-XXXX<br>
                        <strong>Account Name:</strong> Esang Delicacies<br>
                        <strong>Bank:</strong> Metrobank
                    </div>
                    <p>2. Take a screenshot or photo of the transfer receipt</p>
                    <p>3. Upload the proof of payment above before placing your order</p>
                `;
            }
            
            paymentInfo.innerHTML = instructions;
            paymentInfo.style.display = 'block';
        }
        
        // --- Initialization ---
        
        // DIRECT CART FIX - Override everything
        function renderDirectCart() {
            console.log('Rendering direct cart...');
            const tabContent = document.getElementById('tab-content');
            const cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];
            
            console.log('Direct cart items:', cartItems);
            
            let cartHTML = '';
            let total = 0;
            
            if (cartItems.length === 0) {
                cartHTML = '<p style="text-align: center; color: #666; padding: 40px;">Your cart is empty.</p>';
            } else {
                cartHTML = cartItems.map((item, index) => {
                    const itemTotal = (item.price || 0) * (item.quantity || 1);
                    total += itemTotal;
                    return `
                        <div class="cart-item" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="${item.image || '../VImages/placeholder.jpg'}" alt="${item.name}" style="width: 60px; height: 60px; border-radius: 4px; object-fit: cover;">
                                <div>
                                    <p style="margin: 0; font-weight: 600;">${item.name}</p>
                                    <p style="margin: 0; color: #666; font-size: 0.9rem;">Unit Price: ₱${(item.price || 0).toFixed(2)}</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <button onclick="adjustDirectQuantity(${index}, -1)" style="background: var(--primary-red); color: white; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span style="min-width: 30px; text-align: center; font-weight: 600;">${item.quantity || 1}</span>
                                    <button onclick="adjustDirectQuantity(${index}, 1)" style="background: var(--primary-red); color: white; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <p style="margin: 0; font-weight: 600; color: var(--primary-red); min-width: 80px; text-align: right;">₱${itemTotal.toFixed(2)}</p>
                            </div>
                        </div>
                    `;
                }).join('');
            }
            
            tabContent.innerHTML = `
                <div style="padding: 20px;">
                    <h3 style="margin-bottom: 20px;">Shopping Cart</h3>
                    <div>${cartHTML}</div>
                    <div style="border-top: 2px solid #ddd; padding: 20px; margin-top: 20px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                        <p style="font-size: 1.25rem; font-weight: 700; margin-bottom: 15px;">Total: <span style="color: var(--primary-red);">₱${total.toFixed(2)}</span></p>
                        <button onclick="directCheckout()" ${cartItems.length === 0 ? 'disabled' : ''} style="background: var(--primary-red); color: white; border: none; padding: 15px 30px; font-size: 1rem; font-weight: 600; border-radius: 6px; cursor: ${cartItems.length === 0 ? 'not-allowed' : 'pointer'}; opacity: ${cartItems.length === 0 ? '0.6' : '1'};">
                            <i class="fas fa-credit-card"></i> Checkout
                        </button>
                    </div>
                </div>
            `;
        }
        
        function adjustDirectQuantity(index, change) {
            console.log('Adjusting quantity for index:', index, 'change:', change);
            const cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];
            
            if (index >= 0 && index < cartItems.length) {
                cartItems[index].quantity = (cartItems[index].quantity || 1) + change;
                
                if (cartItems[index].quantity <= 0) {
                    cartItems.splice(index, 1);
                }
                
                localStorage.setItem('customerCart', JSON.stringify(cartItems));
                renderDirectCart();
            }
        }
        
        function directCheckout() {
            console.log('Direct checkout called');
            const cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];
            
            if (cartItems.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            console.log('Opening checkout modal...');
            const modal = document.getElementById('checkout-modal');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('visible');
                // Reset to step 1
                showCheckoutStep(1);
                console.log('Modal should be open now');
            } else {
                console.error('Checkout modal not found!');
            }
        }
        
        function setupCheckoutSteps() {
            console.log('Setting up checkout steps...');
            
            // Step navigation buttons
            const nextToAddressBtn = document.getElementById('next-to-address-btn');
            const backToPaymentBtn = document.getElementById('back-to-payment-btn');
            const nextToScreenshotBtn = document.getElementById('next-to-screenshot-btn');
            const backToAddressBtn = document.getElementById('back-to-address-btn');
            const placeOrderBtn = document.getElementById('place-order-btn');
            const placeOrderFinalBtn = document.getElementById('place-order-final-btn');
            
            // Step 1 to Step 2
            if (nextToAddressBtn) {
                nextToAddressBtn.addEventListener('click', () => {
                    console.log('Next to address clicked');
                    const selectedPayment = document.querySelector('input[name="payment-method"]:checked');
                    if (!selectedPayment) {
                        alert('Please select a payment method first.');
                        return;
                    }
                    
                    // Store selected payment method
                    window.selectedPaymentMethod = selectedPayment.value;
                    console.log('Selected payment method:', window.selectedPaymentMethod);
                    
                    // Update payment display in step 2
                    const paymentText = document.getElementById('selected-payment-text');
                    if (paymentText) {
                        paymentText.textContent = selectedPayment.value;
                    }
                    
                    showCheckoutStep(2);
                });
            }
            
            // Step 2 back to Step 1
            if (backToPaymentBtn) {
                backToPaymentBtn.addEventListener('click', () => {
                    console.log('Back to payment clicked');
                    showCheckoutStep(1);
                });
            }
            
            // Step 2 to Step 3 (for digital payments)
            if (nextToScreenshotBtn) {
                nextToScreenshotBtn.addEventListener('click', () => {
                    console.log('Next to screenshot clicked');
                    if (validateAddressForm()) {
                        showCheckoutStep(3);
                    }
                });
            }
            
            // Step 3 back to Step 2
            if (backToAddressBtn) {
                backToAddressBtn.addEventListener('click', () => {
                    console.log('Back to address clicked');
                    showCheckoutStep(2);
                });
            }
            
            // Place Order (Step 2 - COD)
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', () => {
                    console.log('Place order (COD) clicked');
                    if (validateAddressForm()) {
                        placeOrder(false); // false = no screenshot needed
                    }
                });
            }
            
            // Place Order Final (Step 3 - Digital payments)
            if (placeOrderFinalBtn) {
                placeOrderFinalBtn.addEventListener('click', () => {
                    console.log('Place order final (digital) clicked');
                    placeOrder(true); // true = screenshot validation needed
                });
            }
            
            // Setup barangay dropdown
            setupBarangayDropdown();
            
            // Setup file upload functionality
            setupFileUpload();
        }
        
        function showCheckoutStep(stepNumber) {
            console.log('Showing checkout step:', stepNumber);
            
            const step1 = document.getElementById('checkout-step-1');
            const step2 = document.getElementById('checkout-step-2');
            const step3 = document.getElementById('checkout-step-3');
            
            // Hide all steps
            if (step1) step1.style.display = 'none';
            if (step2) step2.style.display = 'none';
            if (step3) step3.style.display = 'none';
            
            // Show selected step
            if (stepNumber === 1 && step1) {
                step1.style.display = 'block';
            } else if (stepNumber === 2 && step2) {
                step2.style.display = 'block';
                updateStep2Buttons();
            } else if (stepNumber === 3 && step3) {
                step3.style.display = 'block';
                updatePaymentSummaryStep3();
            }
        }
        
        function updateStep2Buttons() {
            const nextToScreenshotBtn = document.getElementById('next-to-screenshot-btn');
            const placeOrderBtn = document.getElementById('place-order-btn');
            
            if (window.selectedPaymentMethod === 'GCash' || window.selectedPaymentMethod === 'Bank Transfer') {
                // Show "Next: Payment Proof" for digital payments
                if (nextToScreenshotBtn) nextToScreenshotBtn.style.display = 'inline-block';
                if (placeOrderBtn) placeOrderBtn.style.display = 'none';
            } else {
                // Show "Place Order" for Cash on Delivery
                if (nextToScreenshotBtn) nextToScreenshotBtn.style.display = 'none';
                if (placeOrderBtn) placeOrderBtn.style.display = 'inline-block';
            }
        }
        
        function updatePaymentSummaryStep3() {
            const paymentText = document.getElementById('selected-payment-text-step3');
            if (paymentText && window.selectedPaymentMethod) {
                paymentText.textContent = window.selectedPaymentMethod;
            }
        }
        
        function validateAddressForm() {
            const exactAddress = document.getElementById('address')?.value?.trim();
            const barangay = document.getElementById('barangay')?.value;
            
            if (!exactAddress) {
                alert('Please enter your exact address.');
                return false;
            }
            
            if (!barangay) {
                alert('Please select your barangay.');
                return false;
            }
            
            return true;
        }
        
        function setupBarangayDropdown() {
            const barangaySelect = document.getElementById('barangay');
            if (barangaySelect) {
                const barangays = ['Barangay 165', 'Barangay 166', 'Barangay 167', 'Barangay 168', 'Barangay 169', 'Barangay 170', 'Barangay 171', 'Barangay 172', 'Barangay 173', 'Barangay 174', 'Barangay 175', 'Barangay 176', 'Barangay 177'];
                
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;
                    barangaySelect.appendChild(option);
                });
            }
        }
        
        function setupFileUpload() {
            console.log('Setting up file upload functionality...');
            
            const fileUploadArea = document.getElementById('file-upload-area');
            const fileInput = document.getElementById('payment-screenshot-input');
            const filePreview = document.getElementById('file-preview');
            const uploadContent = fileUploadArea?.querySelector('.file-upload-content');
            
            if (!fileUploadArea || !fileInput) {
                console.warn('File upload elements not found');
                return;
            }
            
            // Click to select file
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    handleFileSelect(file);
                }
            });
            
            // Drag and drop events
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.style.backgroundColor = '#f0f0f0';
                fileUploadArea.style.borderColor = 'var(--primary-red)';
            });
            
            fileUploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                fileUploadArea.style.backgroundColor = '';
                fileUploadArea.style.borderColor = '';
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.style.backgroundColor = '';
                fileUploadArea.style.borderColor = '';
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });
            
            // Remove file button
            const removeBtn = document.getElementById('remove-file-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    clearSelectedFile();
                });
            }
        }
        
        function handleFileSelect(file) {
            console.log('File selected:', file.name);
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please select a JPG, JPEG, or PNG file.');
                return;
            }
            
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File is too large. Maximum size is 5MB.');
                return;
            }
            
            // Store the file globally
            window.selectedFile = file;
            displayFilePreview(file);
        }
        
        function displayFilePreview(file) {
            const uploadContent = document.querySelector('.file-upload-content');
            const filePreview = document.getElementById('file-preview');
            const previewImage = document.getElementById('preview-image');
            const previewFilename = document.getElementById('preview-filename');
            const previewFilesize = document.getElementById('preview-filesize');
            
            if (!uploadContent || !filePreview) return;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewImage) previewImage.src = e.target.result;
                if (previewFilename) previewFilename.textContent = file.name;
                if (previewFilesize) previewFilesize.textContent = formatFileSize(file.size);
                
                // Show preview, hide upload area
                uploadContent.style.display = 'none';
                filePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        function clearSelectedFile() {
            window.selectedFile = null;
            const uploadContent = document.querySelector('.file-upload-content');
            const filePreview = document.getElementById('file-preview');
            const fileInput = document.getElementById('payment-screenshot-input');
            
            if (uploadContent && filePreview) {
                uploadContent.style.display = 'block';
                filePreview.style.display = 'none';
            }
            
            if (fileInput) {
                fileInput.value = '';
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        async function placeOrder(requiresScreenshot) {
            console.log('Placing order, requires screenshot:', requiresScreenshot);
            
            const cartItems = JSON.parse(localStorage.getItem('customerCart')) || [];
            const exactAddress = document.getElementById('address')?.value?.trim();
            const barangay = document.getElementById('barangay')?.value;
            const fullAddress = `${exactAddress}, ${barangay}, North Caloocan, Metro Manila`;
            
            if (requiresScreenshot) {
                // For digital payments, validate screenshot upload
                const paymentName = document.getElementById('payment-name')?.value?.trim();
                const paymentReference = document.getElementById('payment-reference')?.value?.trim();
                
                if (!paymentName) {
                    alert('Please enter the account name.');
                    return;
                }
                
                if (!paymentReference) {
                    alert('Please enter the reference number.');
                    return;
                }
                
                if (!window.selectedFile) {
                    alert('Please upload your payment screenshot.');
                    return;
                }
            }
            
            // Prepare order data for database
            const customerId = <?php echo $customerId; ?>;
            const orderData = {
                customerId: customerId,
                orderManagerId: 1,
                deliveryAddress: fullAddress,
                orderStatus: 'Pending',
                paymentMethod: window.selectedPaymentMethod,
                items: cartItems.map(item => ({
                    prodId: item.prodId || item.product_id || 1,
                    quantity: item.quantity || 1,
                    name: item.name
                }))
            };
            
            console.log('Submitting order to database:', orderData);
            
            try {
                // Show loading
                const submitBtn = requiresScreenshot ? 
                    document.getElementById('place-order-final-btn') : 
                    document.getElementById('place-order-btn');
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';
                }
                
                // Submit order to database
                const response = await fetch('http://localhost:8080/public/api/order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });
                
                const result = await response.json();
                console.log('Order submission result:', result);
                
                if (result.ok) {
                    const orderId = result.orderId;
                    
                    // If payment screenshot is required, upload it
                    if (requiresScreenshot && window.selectedFile) {
                        console.log('Uploading payment screenshot...');
                        
                        const formData = new FormData();
                        formData.append('payment_screenshot', window.selectedFile);
                        formData.append('order_id', orderId);
                        formData.append('customer_id', customerId);
                        formData.append('payment_account_name', document.getElementById('payment-name').value);
                        formData.append('payment_reference_number', document.getElementById('payment-reference').value);
                        
                        try {
                            const uploadResponse = await fetch('http://localhost:8080/api/upload_payment_screenshot.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const uploadResult = await uploadResponse.json();
                            if (!uploadResult.success) {
                                console.warn('Payment screenshot upload failed:', uploadResult.error);
                                alert('Order placed but payment screenshot upload failed. Please contact support.');
                            }
                        } catch (uploadError) {
                            console.error('Upload error:', uploadError);
                            alert('Order placed but payment screenshot upload failed. Please contact support.');
                        }
                    }
                    
                    // Success!
                    alert(`Order #${orderId} placed successfully!\n\nPayment: ${window.selectedPaymentMethod}\nAddress: ${fullAddress}\nItems: ${cartItems.length}`);
                    
                    // Close modal and clear cart
                    const modal = document.getElementById('checkout-modal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.classList.remove('visible');
                    }
                    
                    // Clear cart and refresh
                    localStorage.removeItem('customerCart');
                    window.selectedFile = null;
                    renderDirectCart();
                    
                    // Show ongoing orders tab
                    switchToOngoingTab();
                    
                } else {
                    throw new Error(result.error || 'Unknown error occurred');
                }
                
            } catch (error) {
                console.error('Order placement error:', error);
                alert('Error placing order: ' + error.message);
            } finally {
                // Reset button
                const submitBtn = requiresScreenshot ? 
                    document.getElementById('place-order-final-btn') : 
                    document.getElementById('place-order-btn');
                
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-credit-card"></i> Place Order';
                }
            }
        }
        
        async function loadOrdersByStatus(status) {
            console.log('Loading orders for status:', status);
            
            const tabContent = document.getElementById('tab-content');
            tabContent.innerHTML = `
                <div style="padding: 40px; text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-red); margin-bottom: 15px;"></i>
                    <p>Loading ${status} orders...</p>
                </div>
            `;
            
            try {
                const customerId = <?php echo $customerId; ?>;
                const response = await fetch(`http://localhost:8080/public/api/get_customer_orders.php?customer_id=${customerId}&status=${status}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                console.log('Orders loaded:', result);
                
                if (result.success && result.orders) {
                    renderOrdersByStatus(result.orders, status);
                } else {
                    renderEmptyOrders(status, result.message || 'No orders found');
                }
                
            } catch (error) {
                console.error('Error loading orders:', error);
                renderEmptyOrders(status, 'Error loading orders: ' + error.message);
            }
        }
        
        function renderOrdersByStatus(orders, status) {
            console.log('Rendering orders for status:', status, orders);
            
            const tabContent = document.getElementById('tab-content');
            
            if (orders.length === 0) {
                renderEmptyOrders(status);
                return;
            }
            
            const ordersHTML = orders.map(order => {
                const orderDate = new Date(order.orderDate || order.created_at || Date.now()).toLocaleDateString();
                const orderTotal = parseFloat(order.totalAmount || order.total || 0).toFixed(2);
                const orderItems = order.items || [];
                
                // Status-specific styling
                let statusClass = '';
                let statusIcon = '';
                let actionButtons = '';
                
                switch (status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusIcon = 'fas fa-hourglass-half';
                        break;
                    case 'ongoing':
                        statusClass = 'status-ongoing';
                        statusIcon = 'fas fa-motorcycle';
                        actionButtons = `
                            <div class="order-actions">
                                <button onclick="trackOrder('${order.orderId || order.order_id}')" class="btn btn-primary btn-sm">
                                    <i class="fas fa-map-marker-alt"></i> Track Order
                                </button>
                            </div>
                        `;
                        break;
                    case 'completed':
                        statusClass = 'status-completed';
                        statusIcon = 'fas fa-check-circle';
                        actionButtons = `
                            <div class="order-actions">
                                <button onclick="viewInvoice('${order.orderId || order.order_id}')" class="btn btn-primary btn-sm">
                                    <i class="fas fa-receipt"></i> View Invoice
                                </button>
                                <button onclick="leaveFeedback('${order.orderId || order.order_id}')" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-star"></i> Feedback
                                </button>
                                <button onclick="reorder('${order.orderId || order.order_id}')" class="btn btn-success btn-sm">
                                    <i class="fas fa-redo"></i> Reorder
                                </button>
                            </div>
                        `;
                        break;
                }
                
                const itemsDisplay = orderItems.length > 0 ? 
                    orderItems.map(item => `<div class="order-item-detail">
                        <span>${item.quantity || 1}x ${item.name || item.product_name || 'Unknown Item'}</span>
                        <span>₱${((item.price || 0) * (item.quantity || 1)).toFixed(2)}</span>
                    </div>`).join('') :
                    '<div class="order-item-detail"><span>Order details</span><span>₱' + orderTotal + '</span></div>';
                
                return `
                    <div class="order-card ${statusClass}">
                        <div class="order-card-header">
                            <div class="order-info">
                                <h4 class="order-id"><i class="${statusIcon}"></i> Order #${order.orderId || order.order_id}</h4>
                                <p class="order-date">Date: ${orderDate}</p>
                                <p class="order-payment">Payment: ${order.paymentMethod || order.payment_method || 'N/A'}</p>
                            </div>
                            <div class="order-total-section">
                                <p class="order-total-amount">₱${orderTotal}</p>
                                <span class="order-status-badge ${statusClass}">${status.toUpperCase()}</span>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            ${itemsDisplay}
                        </div>
                        
                        <div class="order-details">
                            <p><strong>Delivery Address:</strong> ${order.deliveryAddress || 'N/A'}</p>
                        </div>
                        
                        ${actionButtons}
                    </div>
                `;
            }).join('');
            
            tabContent.innerHTML = `
                <div style="padding: 20px;">
                    <h3 style="margin-bottom: 20px; text-transform: capitalize;">
                        <i class="${getStatusIcon(status)}"></i> ${status} Orders (${orders.length})
                    </h3>
                    <div class="order-list">
                        ${ordersHTML}
                    </div>
                </div>
            `;
        }
        
        function renderEmptyOrders(status, message) {
            const tabContent = document.getElementById('tab-content');
            const statusMessages = {
                pending: 'No pending orders. Your new orders will appear here.',
                ongoing: 'No orders currently being processed or delivered.',
                completed: 'No completed orders yet. Your finished orders will appear here.'
            };
            
            tabContent.innerHTML = `
                <div style="padding: 60px 20px; text-align: center; color: #666;">
                    <i class="${getStatusIcon(status)}" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3 style="margin-bottom: 10px; text-transform: capitalize;">${status} Orders</h3>
                    <p style="font-size: 1.1rem; margin-bottom: 20px;">
                        ${message || statusMessages[status] || 'No orders found.'}
                    </p>
                    <button onclick="switchToCartTab()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Items to Cart
                    </button>
                </div>
            `;
        }
        
        function getStatusIcon(status) {
            const icons = {
                pending: 'fas fa-hourglass-half',
                ongoing: 'fas fa-motorcycle', 
                completed: 'fas fa-check-circle'
            };
            return icons[status] || 'fas fa-list';
        }
        
        function switchToCartTab() {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelector('.tab-button[data-tab="cart"]').classList.add('active');
            renderDirectCart();
        }
        
        function switchToOngoingTab() {
            // Switch to pending tab to show the new order (since orders start as pending)
            const pendingTab = document.querySelector('.tab-button[data-tab="pending"]');
            if (pendingTab) {
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                pendingTab.classList.add('active');
                loadOrdersByStatus('pending');
            }
        }
        
        // Order action functions
        function trackOrder(orderId) {
            alert(`Order tracking for #${orderId} - This feature will redirect to order tracking page.`);
            // In real implementation: window.location.href = `customer_order_status.php?order_id=${orderId}`;
        }
        
        function viewInvoice(orderId) {
            alert(`View invoice for order #${orderId} - This will open invoice modal.`);
            // In real implementation: open invoice modal with order details
        }
        
        function leaveFeedback(orderId) {
            alert(`Leave feedback for order #${orderId} - This will redirect to feedback page.`);
            // In real implementation: window.location.href = `feedback.php?order_id=${orderId}`;
        }
        
        function reorder(orderId) {
            if (confirm(`Reorder items from order #${orderId}?`)) {
                alert('Items will be added to cart and you will be redirected to dashboard.');
                // In real implementation: add items to cart and redirect
            }
        }
        
        // Completely override any other orders system
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('DOM Content Loaded - Starting DIRECT CART FIX');
            
            // Disable any other orders systems
            window.enhancedOrdersLoaded = true;
            window.ordersSystemLoaded = true;
            
            // Render direct cart immediately
            renderDirectCart();
            
            // Setup tab switching with real database data
            document.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    
                    const tab = button.getAttribute('data-tab');
                    if (tab === 'cart') {
                        renderDirectCart();
                    } else {
                        loadOrdersByStatus(tab);
                    }
                });
            });
            
            // Setup checkout modal close functionality
            const checkoutModal = document.getElementById('checkout-modal');
            const closeModalBtns = document.querySelectorAll('[data-modal-close="checkout-modal"]');
            
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    if (checkoutModal) {
                        checkoutModal.style.display = 'none';
                        checkoutModal.classList.remove('visible');
                    }
                });
            });
            
            // Close modal when clicking outside
            if (checkoutModal) {
                checkoutModal.addEventListener('click', (e) => {
                    if (e.target === checkoutModal) {
                        checkoutModal.style.display = 'none';
                        checkoutModal.classList.remove('visible');
                    }
                });
            }
            
            // Setup checkout step navigation
            setupCheckoutSteps();
            
            console.log('Direct cart fix complete - Cart should work now!');
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="../VJavaScript/sidebar.js"></script>
    <!-- Removed orders.js to prevent conflicts with inline system -->
</body>
</html>