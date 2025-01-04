<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$userID = $_SESSION['userID'] ?? 1; // Replace with session-based user ID

// Fetch cart items
$stmt = $conn->prepare("SELECT c.CartID, c.Quantity, p.Name AS ProductName, p.Price 
                        FROM cart c
                        JOIN products p ON c.ProductID = p.ProductID
                        WHERE c.UserID = ?");
$stmt->bind_param('i', $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Your Cart</h1>
    <?php if ($result->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandTotal = 0;
                while ($row = $result->fetch_assoc()):
                    $total = $row['Quantity'] * $row['Price'];
                    $grandTotal += $total;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ProductName']) ?></td>
                        <td><?= htmlspecialchars($row['Quantity']) ?></td>
                        <td>$<?= number_format($row['Price'], 2) ?></td>
                        <td>$<?= number_format($total, 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h4>Total: $<?= number_format($grandTotal, 2) ?></h4>
        <a href="checkout.php" class="btn btn-success mt-3">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
