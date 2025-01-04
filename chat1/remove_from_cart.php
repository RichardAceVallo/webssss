<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartID'])) {
    $cartID = $_POST['cartID'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE CartID = ?");
    $stmt->bind_param('i', $cartID);

    if ($stmt->execute()) {
        header('Location: cart.php');
        exit;
    } else {
        echo "Failed to remove item.";
    }

    $stmt->close();
}

$conn->close();
?>
