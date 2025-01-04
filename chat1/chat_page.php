<?php
// signup.php

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Initialize variables
$error = null;
$success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = 'user'; // Default role for new users

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill out all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE Username = ? OR Email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert the user into the database
            $stmt = $conn->prepare("INSERT INTO users (Username, Email, Password, Role, CreatedAt) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param('ssss', $username, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Failed to create account. Please try again.';
            }

            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard & Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #live-chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            z-index: 1000;
        }

        #chat-box {
            height: 200px;
            overflow-y: auto;
            padding: 10px;
        }

        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
        }

        .message.admin {
            background-color: #f8d7da;
            text-align: right;
        }

        .message.client {
            background-color: #d1e7dd;
            text-align: left;
        }

        #minimize-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            cursor: pointer;
        }

        .dashboard {
            margin-bottom: 20px;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .search-bar {
            margin-left: auto;
        }

        .cart-container {
            margin-top: 20px;
        }

        .product {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .cart {
            margin-top: 20px;
        }

        .sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: 200px;
            height: 100%;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #ddd;
        }

        .content {
            margin-left: 220px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">ComParts</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="community_forum.php">Community Forum</a>
                </li>
            </ul>
            <a class="nav-link me-2" href="cart.php">Cart</a>
            <form class="d-flex search-bar">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
            <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
        </div>
    </div>
</nav>

    <div class="sidebar">
        <h5>Categories</h5>
        <ul class="list-group">
            <li class="list-group-item category-filter" data-category="all">All</li>
            <li class="list-group-item category-filter" data-category="system-unit">System Unit</li>
            <li class="list-group-item category-filter" data-category="monitor">Monitor</li>
            <li class="list-group-item category-filter" data-category="peripherals">Peripherals</li>
            <li class="list-group-item category-filter" data-category="accessories">Accessories</li>
        </ul>
    </div>

    <div class="container content">
        <div class="dashboard">
            <h2 class="text-center">Welcome to the Dashboard</h2>
            <p class="text-center">Here you can browse products, add them to your cart, and chat with us.</p>
        </div>

        <div class="cart-container">
            <h3>Products</h3>
            <div class="product" data-category="system-unit">
                <h5>CPU</h5>
                <p>Price: $500</p>
                <button class="btn btn-primary add-to-cart" data-product="CPU" data-price="500">Add to Cart</button>
            </div>
            <div class="product" data-category="monitor">
                <h5>24-inch Monitor</h5>
                <p>Price: $150</p>
                <button class="btn btn-primary add-to-cart" data-product="24-inch Monitor" data-price="150">Add to Cart</button>
            </div>
            <div class="product" data-category="peripherals">
                <h5>Mechanical Keyboard</h5>
                <p>Price: $100</p>
                <button class="btn btn-primary add-to-cart" data-product="Mechanical Keyboard" data-price="100">Add to Cart</button>
            </div>
            <div class="product" data-category="peripherals">
                <h5>Gaming Mouse</h5>
                <p>Price: $50</p>
                <button class="btn btn-primary add-to-cart" data-product="Gaming Mouse" data-price="50">Add to Cart</button>
            </div>
            <div class="product" data-category="accessories">
                <h5>Headset</h5>
                <p>Price: $80</p>
                <button class="btn btn-primary add-to-cart" data-product="Headset" data-price="80">Add to Cart</button>
            </div>
        </div>

        <div class="cart">
            <h3>Your Cart</h3>
            <ul id="cart-items" class="list-group">
                <!-- Cart items will appear here -->
            </ul>
            <h4 class="mt-3">Total: $<span id="cart-total">0</span></h4>
            <button class="btn btn-success mt-2" id="checkout">Checkout</button>
        </div>

        <div id="live-chat-container">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <span>Live Chat - Welcome, User</span>
                    <span id="minimize-btn" class="text-white">&minus;</span>
                </div>
                <div class="card-body" id="chat-box">
                    <!-- Messages will appear here -->
                </div>
                <div class="card-footer">
                    <form id="chat-form">
                        <div class="input-group">
                            <input type="text" class="form-control" id="message" placeholder="Type your message...">
                            <button class="btn btn-primary" type="submit">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            const cartItems = [];

            $(document).on('click', '.add-to-cart', function () {
    const product = $(this).data('product');
    const price = parseFloat($(this).data('price'));

    // Send product and price to the server
    $.post('add_to_cart.php', { product: product, price: price }, function (response) {
        if (response.success) {
            alert('Item added to cart!');
        } else {
            alert('Failed to add item to cart.');
        }
    }, 'json');
});


            function updateCart() {
                const cartList = $('#cart-items');
                const cartTotal = $('#cart-total');
                cartList.empty();
                let total = 0;

                cartItems.forEach(item => {
                    cartList.append(`<li class="list-group-item">${item.product} - $${item.price}</li>`);
                    total += item.price;
                });

                cartTotal.text(total.toFixed(2));
            }

            $('#checkout').click(function () {
                if (cartItems.length === 0) {
                    alert('Your cart is empty!');
                    return;
                }

                alert('Checkout complete!');
                cartItems.length = 0;
                updateCart();
            });

            $('.category-filter').click(function () {
                const category = $(this).data('category');

                if (category === 'all') {
                    $('.product').show();
                } else {
                    $('.product').hide();
                    $(`.product[data-category="${category}"]`).show();
                }
            });

            const chatBox = $('#chat-box');
            const chatContainer = $('#live-chat-container');
            const minimizeBtn = $('#minimize-btn');

            minimizeBtn.on('click', function () {
                chatContainer.toggleClass('minimized');
                if (chatContainer.hasClass('minimized')) {
                    chatBox.hide();
                    $('.card-footer').hide();
                    minimizeBtn.text('+');
                } else {
                    chatBox.show();
                    $('.card-footer').show();
                    minimizeBtn.text('−');
                }
            });

            function fetchMessages() {
                $.post('live_chat.php', { action: 'get_messages' }, function (data) {
                    chatBox.empty();
                    data.forEach(msg => {
                        const messageClass = msg.sender === 'admin' ? 'admin' : 'client';
                        chatBox.append(
                            `<div class="message ${messageClass}"><strong>${msg.sender}</strong> [${msg.timestamp}]<br>${msg.message}</div>`
                        );
                    });
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }, 'json');
            }

            $('#chat-form').submit(function (e) {
                e.preventDefault();
                const message = $('#message').val();
                if (message.trim()) {
                    $.post('live_chat.php', { action: 'send_message', message }, function (response) {
                        if (response.success) {
                            $('#message').val('');
                            fetchMessages();
                        }
                    }, 'json');
                }
            });

            // Fetch messages every 2 seconds
            setInterval(fetchMessages, 2000);

            // Initial fetch
            fetchMessages();
        });
    </script>
</body>
</html>
