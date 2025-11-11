document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const currentDateElement = document.getElementById('current-date');
  const searchBar = document.getElementById('search-bar');
  const orderList = document.getElementById('order-list');
  const totalPriceElement = document.getElementById('total-price');
  const paymentButtons = document.querySelectorAll('.payment-button');
  const paymentInputs = document.getElementById('payment-inputs');
  const cashAmountInput = document.getElementById('cash-amount');
  const cashChangeElement = document.getElementById('cash-change');
  const gcashAmountInput = document.getElementById('gcash-amount');
  const splitCashAmountInput = document.getElementById('split-cash-amount');
  const splitGcashAmountInput = document.getElementById('split-gcash-amount');
  const confirmPaymentButton = document.getElementById('confirm-payment-button');
  const clearOrderButton = document.querySelector('.clear-order-button');
  const receiptContent = document.getElementById('receipt-content');
  const printReceiptButton = document.getElementById('print-receipt-button');
  const downloadReceiptButton = document.getElementById('download-receipt-button');
  const allMenuButton = document.querySelector('.all-menu-button');
  const menuTabs = document.getElementById('menuTabs');
  const menuContents = document.getElementById('menuContents');

  // Date
  currentDateElement.textContent = new Date().toLocaleDateString('en-US', {
    weekday:'long', year:'numeric', month:'long', day:'numeric'
  });

  // State
  let order = [];
  let total = 0;
  let currentPaymentMethod = 'cash';

  // Seed menu (only if none exists)
  let menuData = JSON.parse(localStorage.getItem('menuData')) || [];
  if (menuData.length === 0) {
    menuData = [
      {
        id: 'rice-meals',
        name: 'Rice Meals',
        itemType: 'single-price',
        items: [
          { name: 'Pork Silog', price: 99, image: '' },
          { name: 'Tapsilog',   price: 109, image: '' },
          { name: 'Longsilog',  price: 95, image: '' },
          { name: 'Bangsilog',  price: 119, image: '' },
        ],
      },
      {
        id: 'bilao',
        name: 'Bilao',
        itemType: 'multi-price',
        items: [
          {
            name: 'Pancit Canton',
            prices: [
              { size: 'Small',  price: 499 },
              { size: 'Medium', price: 799 },
              { size: 'Large',  price: 999 },
            ],
            image: '',
          },
          {
            name: 'Palabok',
            prices: [
              { size: 'Small',  price: 549 },
              { size: 'Medium', price: 849 },
              { size: 'Large',  price: 1099 },
            ],
            image: '',
          },
        ],
      },
      {
        id: 'drinks',
        name: 'Drinks',
        itemType: 'single-price',
        items: [
          { name: 'Bottled Water', price: 25, image: '' },
          { name: 'Iced Tea',      price: 35, image: '' },
          { name: 'Soda',          price: 40, image: '' },
        ],
      },
    ];
    localStorage.setItem('menuData', JSON.stringify(menuData));
  }

  // Renderers
  function renderMenu() {
    menuTabs.innerHTML = '';
    menuContents.innerHTML = '';

    menuData.forEach((category, index) => {
      // Tab button
      const button = document.createElement('button');
      button.className = 'menu-button' + (index === 0 ? ' active' : '');
      button.dataset.category = category.id;
      button.innerHTML = `<i class="fa-solid fa-utensils"></i><span>${category.name}</span>`;
      menuTabs.appendChild(button);

      // Grid
      const grid = document.createElement('div');
      grid.id = `${category.id}-grid`;
      grid.className = 'menu-grid' + (index === 0 ? ' active-grid' : '');

      category.items.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'menu-item';
        itemDiv.dataset.name = item.name;

        if (category.itemType === 'single-price') {
          itemDiv.dataset.price = item.price;
          itemDiv.innerHTML = `
            <img src="${item.image || 'https://placehold.co/400x300?text=Food'}" alt="${item.name}">
            <h3>${item.name}</h3>
            <p class="price">₱${Number(item.price).toFixed(2)}</p>
          `;
        } else if (category.itemType === 'multi-price') {
          const first = item.prices[0]?.price || 0;
          itemDiv.dataset.price = first;
          const sizeButtons = item.prices.map(p =>
            `<button class="bilao-button" data-price="${p.price}">${p.size}</button>`
          ).join('');
          itemDiv.innerHTML = `
            <img src="${item.image || 'https://placehold.co/400x300?text=Bilao'}" alt="${item.name}">
            <h3>${item.name}</h3>
            ${sizeButtons}
            <p class="price">₱${Number(first).toFixed(2)}</p>
          `;
        }

        // Add to order
        itemDiv.addEventListener('click', () => {
          const price = parseFloat(itemDiv.dataset.price || '0');
          if (price > 0) addItemToOrder({ name: itemDiv.dataset.name, price });
        });

        grid.appendChild(itemDiv);
      });

      menuContents.appendChild(grid);
    });

    attachMenuEvents();
  }

  function attachMenuEvents() {
    // tab switching
    const menuButtons = document.querySelectorAll('.menu-button');
    const menuGrids = document.querySelectorAll('.menu-grid');
    menuButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        menuButtons.forEach(b => b.classList.remove('active'));
        menuGrids.forEach(g => g.classList.remove('active-grid'));
        btn.classList.add('active');
        document.getElementById(`${btn.dataset.category}-grid`).classList.add('active-grid');
      });
    });

    // bilao size click (don’t bubble to add order)
    document.querySelectorAll('.bilao-button').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const parent = btn.closest('.menu-item');
        const priceDisplay = parent.querySelector('.price');
        const newPrice = parseFloat(btn.dataset.price);
        parent.dataset.price = newPrice;
        priceDisplay.textContent = `₱${newPrice.toFixed(2)}`;
      });
    });
  }

  // Order logic
  function addItemToOrder(item) {
    const existing = order.find(i => i.name === item.name);
    if (existing) existing.quantity++;
    else order.push({ ...item, quantity: 1 });
    renderOrderSummary(); updateTotals(); updateReceiptPreview();
  }

  function renderOrderSummary() {
    orderList.innerHTML = '';
    order.forEach(item => {
      const row = document.createElement('div');
      row.className = 'order-item';
      row.innerHTML = `
        <div class="item-details">
          <h4>${item.name}</h4>
          <div class="quantity-controls">
            <button class="decrease-qty" data-name="${item.name}">-</button>
            <span>${item.quantity}</span>
            <button class="increase-qty" data-name="${item.name}">+</button>
          </div>
        </div>
        <span class="item-price">₱${(item.price * item.quantity).toFixed(2)}</span>
      `;
      orderList.appendChild(row);
    });
  }

  function updateTotals() {
    total = order.reduce((s, i) => s + i.price * i.quantity, 0);
    totalPriceElement.textContent = `₱${total.toFixed(2)}`;
    calculateChange();
  }

  function clearOrder() {
    order = [];
    renderOrderSummary(); updateTotals(); updateReceiptPreview();
    cashAmountInput.value = ''; gcashAmountInput.value = '';
    splitCashAmountInput.value = ''; splitGcashAmountInput.value = '';
  }

  function calculateChange() {
    const cashPaid = parseFloat(cashAmountInput.value) || 0;
    const change = cashPaid - total;
    cashChangeElement.textContent = `₱${change.toFixed(2)}`;
  }

  // Receipt
  function generateReceiptContent() {
    let content = 'ESANG DELICACIES\n\n';
    content += '----------------------------\n';
    order.forEach(item => {
      content += `${item.name} x${item.quantity}\n`;
      content += `   ₱${item.price.toFixed(2)} ea. = ₱${(item.price * item.quantity).toFixed(2)}\n`;
    });
    content += '----------------------------\n';
    content += `TOTAL: ₱${total.toFixed(2)}\n`;
    content += '----------------------------\n';
    content += `Payment Method: ${currentPaymentMethod}\n`;
    if (currentPaymentMethod === 'cash') {
      const cashPaid = parseFloat(cashAmountInput.value) || 0;
      const change = cashPaid - total;
      content += `Cash Paid: ₱${cashPaid.toFixed(2)}\n`;
      content += `Change Due: ₱${change.toFixed(2)}\n`;
    } else if (currentPaymentMethod === 'gcash') {
      const g = parseFloat(gcashAmountInput.value) || 0;
      content += `GCash Paid: ₱${g.toFixed(2)}\n`;
    } else {
      const c = parseFloat(splitCashAmountInput.value) || 0;
      const g = parseFloat(splitGcashAmountInput.value) || 0;
      content += `Cash Paid: ₱${c.toFixed(2)}\n`;
      content += `GCash Paid: ₱${g.toFixed(2)}\n`;
    }
    content += '----------------------------\n';
    content += 'Thank you for your purchase!\n';
    return content;
  }

  function updateReceiptPreview() {
    receiptContent.textContent = generateReceiptContent();
  }

  // Events
  searchBar.addEventListener('input', () => {
    const q = searchBar.value.toLowerCase();
    document.querySelectorAll('.menu-item').forEach(item => {
      const name = (item.dataset.name || '').toLowerCase();
      item.style.display = name.includes(q) ? 'block' : 'none';
    });
  });

  orderList.addEventListener('click', e => {
    const t = e.target;
    const name = t.dataset.name;
    if (!name) return;
    const item = order.find(i => i.name === name);
    if (!item) return;
    if (t.classList.contains('increase-qty')) item.quantity++;
    if (t.classList.contains('decrease-qty')) {
      item.quantity--;
      if (item.quantity <= 0) order = order.filter(i => i.name !== name);
    }
    renderOrderSummary(); updateTotals(); updateReceiptPreview();
  });

  clearOrderButton.addEventListener('click', clearOrder);

  paymentButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      paymentButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentPaymentMethod = btn.dataset.method;
      paymentInputs.querySelectorAll('.payment-input-group').forEach(g => g.classList.remove('active'));
      document.getElementById(`${currentPaymentMethod}-payment`).classList.add('active');
    });
  });

  cashAmountInput.addEventListener('input', calculateChange);

  confirmPaymentButton.addEventListener('click', () => {
    alert('Payment confirmed! Receipt is ready for download.');
  });

  downloadReceiptButton.addEventListener('click', () => {
    const blob = new Blob([generateReceiptContent()], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'receipt.txt';
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
  });

  // PRINT / SAVE AS PDF
  printReceiptButton.addEventListener('click', () => {
    // 1) build the latest text
    const content = generateReceiptContent();

    // 2) create a temporary printable container
    let printArea = document.getElementById('print-area');
    if (!printArea) {
      printArea = document.createElement('div');
      printArea.id = 'print-area';
      document.body.appendChild(printArea);
    }

    // 3) inject a simple printable layout
    printArea.innerHTML = `
      <div style="text-align:center; margin-bottom:12px;">
        <strong>ESANG DELICACIES</strong><br/>
        <span>${new Date().toLocaleString()}</span>
      </div>
      <pre>${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
    `;

    // 4) open print dialog (choose "Save as PDF")
    window.print();

    // 5) optional cleanup
    // printArea.remove();
  });

  allMenuButton.addEventListener('click', () => {
    const firstBtn = document.querySelector('.menu-button');
    if (firstBtn) firstBtn.click();
    searchBar.value = '';
    document.querySelectorAll('.menu-item').forEach(i => i.style.display = 'block');
  });

  // Init
  renderMenu();
  updateReceiptPreview();
});
