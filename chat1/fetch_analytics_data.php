<?php
// fetch_analytics_data.php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch Sales Data (Sales Over Time)
$salesData = $conn->query("
    SELECT DATE_FORMAT(OrderDate, '%Y-%m') AS month, SUM(TotalPrice) AS totalSales 
    FROM orders 
    GROUP BY DATE_FORMAT(OrderDate, '%Y-%m') 
    ORDER BY DATE_FORMAT(OrderDate, '%Y-%m') ASC
");

$sales = ['labels' => [], 'data' => []];
while ($row = $salesData->fetch_assoc()) {
    $sales['labels'][] = $row['month'];
    $sales['data'][] = (float) $row['totalSales'];
}

// Fetch Order Status Data
$statusData = $conn->query("
    SELECT OrderStatus, COUNT(*) AS count 
    FROM orders 
    GROUP BY OrderStatus
");

$orderStatus = ['labels' => [], 'data' => []];
while ($row = $statusData->fetch_assoc()) {
    $orderStatus['labels'][] = $row['OrderStatus'];
    $orderStatus['data'][] = (int) $row['count'];
}

// Return JSON response
echo json_encode([
    'sales' => $sales,
    'orderStatus' => $orderStatus
]);

$conn->close();
