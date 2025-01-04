<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the checkout form
    $address = $_POST['address'];
    $paymentMethod = $_POST['paymentMethod'];

    // Get the user ID (replace hardcoded ID with session value if implemented)
    $userID = $_SESSION['userID'] ?? 1;

    // Database connection
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'comparts1';

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    // Calculate the total price of the cart
    $stmt = $conn->prepare("SELECT SUM(c.Quantity * p.Price) AS TotalPrice 
                            FROM cart c 
                            JOIN products p ON c.ProductID = p.ProductID 
                            WHERE c.UserID = ?");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalPrice = $row['TotalPrice'] ?? 0;

    // Insert the order into the orders table
    $stmt = $conn->prepare("INSERT INTO orders (UserID, TotalPrice, OrderDate, ShippingAddress, PaymentMethod, OrderStatus) 
                            VALUES (?, ?, NOW(), ?, ?, 'Pending')");
    $stmt->bind_param('idss', $userID, $totalPrice, $address, $paymentMethod);

    if ($stmt->execute()) {
        // Get the newly created OrderID
        $orderID = $stmt->insert_id;

        // Clear the cart for the user
        $clearCart = $conn->prepare("DELETE FROM cart WHERE UserID = ?");
        $clearCart->bind_param('i', $userID);
        $clearCart->execute();

        // Success message
        echo "<p>Order placed successfully! Your Order ID is: $orderID</p>";
        echo '<a href="index.php" class="btn btn-primary">Continue Shopping</a>';
    } else {
        echo "<p>Failed to place order. Please try again.</p>";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Checkout</h1>
    <form method="POST" action="checkout.php">
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                <option value="COD">Cash on Delivery (COD)</option>
                <option value="Bank">Bank Transfer</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
