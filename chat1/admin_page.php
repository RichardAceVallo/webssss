<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#sell-dashboard">Sell Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#analytics-dashboard">Sell Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_chat.php">Admin Chat</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-danger ms-auto">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Sell Dashboard Section -->
        <section id="sell-dashboard">
            <h2>Sell Dashboard</h2>
            <p>View all client orders below:</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client ID</th>
                        <th>Total Price</th>
                        <th>Shipping Address</th>
                        <th>Payment Method</th>
                        <th>Order Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM orders");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['OrderID']) ?></td>
                                <td><?= htmlspecialchars($row['UserID']) ?></td>
                                <td>$<?= number_format($row['TotalPrice'], 2) ?></td>
                                <td><?= htmlspecialchars($row['ShippingAddress']) ?></td>
                                <td><?= htmlspecialchars($row['PaymentMethod']) ?></td>
                                <td><?= htmlspecialchars($row['OrderStatus']) ?></td>
                                <td><?= htmlspecialchars($row['OrderDate']) ?></td>
                            </tr>
                        <?php endwhile;
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Analytics Dashboard Section -->
        <section id="analytics-dashboard" class="mt-5">
            <h2>Sell Analytics</h2>
            <p>Visualize sales trends and order statistics:</p>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fetch analytics data
        fetch('fetch_analytics_data.php')
            .then(response => response.json())
            .then(data => {
                // Monthly Sales Line Graph
                const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
                new Chart(monthlySalesCtx, {
                    type: 'line',
                    data: {
                        labels: data.sales.labels,
                        datasets: [{
                            label: 'Monthly Sales',
                            data: data.sales.data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true }
                });

                // Order Status Doughnut Chart
                const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.orderStatus.labels,
                        datasets: [{
                            data: data.orderStatus.data,
                            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
                        }]
                    },
                    options: { responsive: true }
                });
            })
            .catch(error => console.error('Error fetching analytics data:', error));
    </script>
</body>
</html>
