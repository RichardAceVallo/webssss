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

// Add to cart logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'], $_POST['quantity'])) {
    $userID = $_SESSION['userID'] ?? 1; // Hardcoded user ID for testing (replace with session-based user ID)
    $productID = (int)$_POST['productID'];
    $quantity = (int)$_POST['quantity'];

    // Check if the product already exists in the cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE UserID = ? AND ProductID = ?");
    $stmt->bind_param('ii', $userID, $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if the product already exists
        $stmt = $conn->prepare("UPDATE cart SET Quantity = Quantity + ? WHERE UserID = ? AND ProductID = ?");
        $stmt->bind_param('iii', $quantity, $userID, $productID);
    } else {
        // Insert a new row
        $stmt = $conn->prepare("INSERT INTO cart (UserID, ProductID, Quantity) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $userID, $productID, $quantity);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
