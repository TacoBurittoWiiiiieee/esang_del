document.addEventListener('DOMContentLoaded', () => {
    // --- UI Elements ---
    const welcomeGreeting = document.getElementById('welcome-greeting');
    const currentDateElement = document.getElementById('current-date');
    const invoicesContainer = document.getElementById('invoices-container');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const editSelectedBtn = document.getElementById('edit-selected-btn');
    const editModal = document.getElementById('editModal');
    const requestModifyBtn = document.getElementById('requestModifyBtn');
    const modifyInvoiceBtn = document.getElementById('modifyInvoiceBtn');
    const closeEditModal = document.getElementById('closeEditModal');
    const closeEditCancel = document.getElementById('closeEditCancel');

    // --- State Variables ---
    let invoices = [];
    let selectedInvoices = new Set();

    // --- Initial Setup ---
    const today = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    welcomeGreeting.textContent = `Invoices`;
    currentDateElement.textContent = today.toLocaleDateString('en-US', options);

    // --- Mock Data Generation ---
    function generateMockInvoices() {
        const mockData = [];
        const items = [
            { name: "yema ube biko", quantity: 2, price: 60.00 },
            { name: "leche flan", quantity: 4, price: 75.00 },
            { name: "maja blanca", quantity: 1, price: 65.00 }
        ];

        // Daily (last 3 days)
        for (let i = 0; i < 3; i++) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            mockData.push({
                id: `W00${10 + i}`,
                date: date.toISOString().split('T')[0],
                items: items.map(item => ({ ...item })),
                total: items.reduce((sum, item) => sum + item.quantity * item.price, 0)
            });
        }
        
        // Monthly (last 30 days)
        for (let i = 0; i < 10; i++) {
            const date = new Date();
            date.setDate(date.getDate() - (i + 4));
            mockData.push({
                id: `W00${20 + i}`,
                date: date.toISOString().split('T')[0],
                items: items.map(item => ({ ...item })),
                total: items.reduce((sum, item) => sum + item.quantity * item.price, 0)
            });
        }

        // Yearly (last 365 days)
        for (let i = 0; i < 50; i++) {
            const date = new Date();
            date.setDate(date.getDate() - (i + 35));
            mockData.push({
                id: `W00${30 + i}`,
                date: date.toISOString().split('T')[0],
                items: items.map(item => ({ ...item })),
                total: items.reduce((sum, item) => sum + item.quantity * item.price, 0)
            });
        }

        return mockData;
    }

    invoices = generateMockInvoices();

    // --- Rendering Functions ---
    const renderInvoices = (invoicesToRender) => {
        invoicesContainer.innerHTML = '';
        if (invoicesToRender.length === 0) {
            invoicesContainer.innerHTML = '<p class="no-invoices">No invoices found for this period.</p>';
            return;
        }

        invoicesToRender.forEach(invoice => {
            const isSelected = selectedInvoices.has(invoice.id);
            const card = document.createElement('div');
            card.className = `invoice-card ${isSelected ? 'selected' : ''}`;
            card.dataset.id = invoice.id;
            
            let itemsHtml = invoice.items.map(item => `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>₱${item.price.toFixed(2)}</td>
                    <td>₱${(item.quantity * item.price).toFixed(2)}</td>
                </tr>
            `).join('');

            card.innerHTML = `
                <input type="checkbox" class="invoice-checkbox" ${isSelected ? 'checked' : ''}>
                <div class="invoice-header">
                    <h3>Invoice #${invoice.id}</h3>
                    <span>${new Date(invoice.date).toLocaleDateString()}</span>
                </div>
                <div class="invoice-details">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
                <div class="invoice-total">Total Amount: ₱${invoice.total.toFixed(2)}</div>
            `;
            invoicesContainer.appendChild(card);
        });
        
    updateEditButtonState();
    };

    // --- Filtering Logic ---
    const filterInvoices = (period) => {
        const now = new Date();
        let filtered = [];

        if (period === 'daily') {
            filtered = invoices.filter(invoice => {
                const invoiceDate = new Date(invoice.date);
                return invoiceDate.toDateString() === now.toDateString();
            });
        } else if (period === 'monthly') {
            const monthAgo = new Date();
            monthAgo.setMonth(now.getMonth() - 1);
            filtered = invoices.filter(invoice => {
                const invoiceDate = new Date(invoice.date);
                return invoiceDate >= monthAgo && invoiceDate <= now;
            });
        } else if (period === 'yearly') {
            const yearAgo = new Date();
            yearAgo.setFullYear(now.getFullYear() - 1);
            filtered = invoices.filter(invoice => {
                const invoiceDate = new Date(invoice.date);
                return invoiceDate >= yearAgo && invoiceDate <= now;
            });
        }
        
        renderInvoices(filtered);
    };

    // --- Edit Logic (replaces bulk delete) ---
    const updateEditButtonState = () => {
        // Require exactly one invoice to be selected to edit
        if (editSelectedBtn) editSelectedBtn.disabled = selectedInvoices.size !== 1;
    };

    const openEditModalForInvoice = (invoiceId) => {
        const invoice = invoices.find(inv => inv.id === invoiceId);
        const idEl = document.getElementById('editInvoiceId');
        const statusEl = document.getElementById('editCurrentStatus');
        if (idEl) idEl.textContent = `#${invoiceId}`;
        if (statusEl) statusEl.textContent = (invoice && invoice.status) ? invoice.status : 'No Request Sent';
        if (editModal) editModal.style.display = 'flex';
    };

    // --- Event Listeners ---
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            filterInvoices(button.dataset.period);
        });
    });

    invoicesContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('invoice-checkbox')) {
            const card = e.target.closest('.invoice-card');
            const invoiceId = card.dataset.id;
            if (e.target.checked) {
                selectedInvoices.add(invoiceId);
                card.classList.add('selected');
            } else {
                selectedInvoices.delete(invoiceId);
                card.classList.remove('selected');
            }
            updateEditButtonState();
        }
    });

    // Edit button behaviour - open popup only when one invoice is selected
    if (editSelectedBtn) {
        editSelectedBtn.addEventListener('click', () => {
            if (selectedInvoices.size === 1) {
                const invoiceId = Array.from(selectedInvoices)[0];
                openEditModalForInvoice(invoiceId);
            } else {
                alert('Please select exactly one invoice to edit.');
            }
        });
    }

    // Modal button actions (client-side simulated)
    if (requestModifyBtn) {
        requestModifyBtn.addEventListener('click', async () => {
            if (selectedInvoices.size === 1) {
                const invoiceId = Array.from(selectedInvoices)[0];
                try {
                    const form = new URLSearchParams();
                    form.append('action', 'request_modify');
                    form.append('orderId', invoiceId);

                    const resp = await fetch('/app/views/cashier/CashierPopUp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: form.toString()
                    });
                    const result = await resp.json();
                    if (result.success) {
                        const inv = invoices.find(i => i.id === invoiceId);
                        if (inv) inv.status = result.status || 'pending';
                        const statusEl = document.getElementById('editCurrentStatus');
                        if (statusEl) statusEl.textContent = result.status || 'pending';
                        alert('Modification request submitted. Waiting for admin approval.');
                    } else {
                        alert('Failed to submit request: ' + (result.message || 'Unknown'));
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error submitting modification request.');
                }
            }
        });
    }

    if (modifyInvoiceBtn) {
        modifyInvoiceBtn.addEventListener('click', async () => {
            if (selectedInvoices.size === 1) {
                const invoiceId = Array.from(selectedInvoices)[0];
                try {
                    const form = new URLSearchParams();
                    form.append('action', 'modify_invoice');
                    form.append('orderId', invoiceId);

                    const resp = await fetch('/app/views/cashier/CashierPopUp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: form.toString()
                    });
                    const result = await resp.json();
                    if (result.success && result.allowed) {
                        alert(result.message || 'You can now modify the invoice.');
                    } else {
                        alert(result.message || 'Modification not allowed at the moment.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error checking modification status.');
                }
            }
        });
    }

    if (closeEditModal) {
        closeEditModal.addEventListener('click', () => { if (editModal) editModal.style.display = 'none'; });
    }

    if (closeEditCancel) {
        closeEditCancel.addEventListener('click', () => { if (editModal) editModal.style.display = 'none'; });
    }

    // Initial render
    filterInvoices('daily');
});