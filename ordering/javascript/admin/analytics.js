// Data for charts and table
        const weeklyData = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Weekly Income',
                data: [19, 22, 24, 27],
                backgroundColor: 'rgba(59, 130, 246, 0.8)', /* Blue-500 */
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                borderRadius: 8,
                barPercentage: 0.6
            }]
        };

        const monthlyData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Income',
                data: [100, 150, 180, 170, 230, 250, 220, 210, 190, 200, 240, 245],
                fill: false,
                borderColor: 'rgba(59, 130, 246, 1)', /* Blue-500 */
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.4
            }]
        };

        const yearlyData = {
            labels: ['2021', '2022', '2023', '2024', '2025'],
            datasets: [{
                label: 'Yearly Income',
                data: [950, 1020, 1120, 1130, 1280],
                backgroundColor: 'rgba(16, 185, 129, 0.8)', /* Green-500 */
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1,
                borderRadius: 8,
                barPercentage: 0.6
            }]
        };

        const transactionData = [
            { username: 'Angelica Ribownes', paymentType: 'Gcash', date: '11/09/2025', amount: '₱70.00' },
            { username: 'Paolo Constancia', paymentType: 'Gcash', date: '11/09/2025', amount: '₱50.00' },
            { username: 'Harry Hernandez', paymentType: 'Bank Transfer', date: '11/09/2025', amount: '₱140.00' },
            { username: 'Yve Averica', paymentType: 'Bank Transfer', date: '11/09/2025', amount: '₱700.00' },
            { username: 'Quinnie Dela Rosa', paymentType: 'COD', date: '11/09/2025', amount: '₱500.00' },
            { username: 'Angelica Ribownes', paymentType: 'Gcash', date: '11/09/2025', amount: '₱70.00' },
            { username: 'Paolo Constancia', paymentType: 'Gcash', date: '11/09/2025', amount: '₱50.00' },
            { username: 'Harry Hernandez', paymentType: 'Bank Transfer', date: '11/09/2025', amount: '₱140.00' },
            { username: 'Yve Averica', paymentType: 'Bank Transfer', date: '11/09/2025', amount: '₱700.00' },
            { username: 'Quinnie Dela Rosa', paymentType: 'COD', date: '11/09/2025', amount: '₱500.00' }
        ];

        let chartInstance = null; // Variable to hold the chart instance

        // Function to create or update the chart
        function updateChart(type, data) {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            
            // Destroy the old chart instance if it exists
            if (chartInstance) {
                chartInstance.destroy();
            }

            // Create a new chart instance
            chartInstance = new Chart(ctx, {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Function to populate the transaction table
        function updateTable() {
            const tableBody = document.getElementById('transaction-table-body');
            tableBody.innerHTML = ''; // Clear existing rows

            transactionData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.username}</td>
                    <td>${item.paymentType}</td>
                    <td>${item.date}</td>
                    <td class="amount">${item.amount}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Function to handle button clicks and update the UI
        function handleNavClick(view) {
            // Remove 'active' class from all buttons
            document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));

            // Add 'active' class to the clicked button
            let selectedButton;
            switch (view) {
                case 'weekly':
                    selectedButton = document.getElementById('weekly-btn');
                    updateChart('bar', weeklyData);
                    break;
                case 'monthly':
                    selectedButton = document.getElementById('monthly-btn');
                    updateChart('line', monthlyData);
                    break;
                case 'yearly':
                    selectedButton = document.getElementById('yearly-btn');
                    updateChart('bar', yearlyData);
                    break;
            }
            if (selectedButton) {
                 selectedButton.classList.add('active');
            }
        }

        // Event listeners for navigation buttons
        document.getElementById('weekly-btn').addEventListener('click', () => handleNavClick('weekly'));
        document.getElementById('monthly-btn').addEventListener('click', () => handleNavClick('monthly'));
        document.getElementById('yearly-btn').addEventListener('click', () => handleNavClick('yearly'));
        
        // Initial setup on page load
        window.onload = function() {
            handleNavClick('weekly'); // Load weekly performance by default
            updateTable(); // Populate the transaction table
        };