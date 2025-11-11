document.addEventListener('DOMContentLoaded', () => {
    // Data for the pie chart
    const orderData = {
        labels: ['Ongoing', 'Completed', 'Pending'],
        datasets: [{
            data: [80, 10, 35], // Values from your image
            backgroundColor: [
                '#007bff', // Blue for Ongoing (Processed)
                '#ffc107', // Yellow for Completed
                '#28a745'  // Green for Pending
            ],
            hoverOffset: 4
        }]
    };

    // Configuration for the pie chart
    const orderConfig = {
        type: 'doughnut', // Doughnut chart looks similar to your image
        data: orderData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We'll create a custom legend in HTML
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    };

    // Render the pie chart
    const orderPieChart = new Chart(
        document.getElementById('orderPieChart'),
        orderConfig
    );

    // Menu Items Data (replace with your actual data)
    const menuItems = [
        { name: "Baked ube halaya with Leche flan", price: "₱150.00", isPopular: true, image: "../VImages/Full Menu/Delicacies/Baked Ube Halaya with Leche Flan.png" },
        { name: "Palitaw with Yema filling", price: "₱50.00", isPopular: false, image: "../VImages/Full Menu/Delicacies/Palitaw with Yema Filling.png" },
        { name: "Palitaw with Yema filling", price: "₱50.00", isPopular: false, image: "../VImages/Full Menu/Delicacies/Palitaw with Yema Filling.png" },
    ];

    const menuGrid = document.getElementById('menuGrid');

    // Function to render menu items
    function renderMenuItems() {
        menuGrid.innerHTML = ''; // Clear existing items
        menuItems.forEach(item => {
            const menuItemDiv = document.createElement('div');
            menuItemDiv.classList.add('menu-item');

            if (item.isPopular) {
                const popularTag = document.createElement('span');
                popularTag.classList.add('popular-tag');
                popularTag.textContent = 'Popular Choice';
                menuItemDiv.appendChild(popularTag);
            }

            const img = document.createElement('img');
            img.src = item.image;
            img.alt = item.name;
            menuItemDiv.appendChild(img);

            const name = document.createElement('h3');
            name.textContent = item.name;
            menuItemDiv.appendChild(name);

            const price = document.createElement('p');
            price.classList.add('price');
            price.textContent = item.price;
            menuItemDiv.appendChild(price);

            menuGrid.appendChild(menuItemDiv);
        });
    }

    renderMenuItems(); // Initial render
});