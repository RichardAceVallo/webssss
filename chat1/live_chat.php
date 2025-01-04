<?php
// live_chat.php
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

// Initialize error variable
$error = null;

// Check if form is submitted for login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usernameOrEmail'])) {
    $usernameOrEmail = $_POST['usernameOrEmail'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Please fill out all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (Username = ? OR Email = ?)");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                session_regenerate_id(true);
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['sender'] = $user['Username'];
                $_SESSION['Role'] = $user['Role'];

                header('Location: live_chat.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'User not found.';
        }
        $stmt->close();
    }
}

// Handle AJAX requests for sending and receiving messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'send_message') {
        $message = $_POST['message'] ?? '';
        $sender = $_SESSION['sender'] ?? 'user';
        $userID = $_SESSION['UserID'] ?? 0;

        if ($message) {
            $stmt = $conn->prepare("INSERT INTO messages (sender, message, UserID) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $sender, $message, $userID);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        }
        exit;
    } elseif ($action === 'get_messages') {
        $stmt = $conn->prepare("SELECT sender, message, timestamp FROM messages WHERE UserID = ? OR sender = 'admin' ORDER BY timestamp ASC");
        $stmt->bind_param("i", $_SESSION['UserID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];

        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        $stmt->close();
        echo json_encode($messages);
        exit;
    }
}

// Redirect to login if not logged in
if (!isset($_SESSION['sender'])) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <!-- Display error message if any -->
                        '; if ($error) { echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; } echo '
                        <!-- Login Form -->
                        <form method="POST" action="live_chat.php">
                            <div class="mb-3">
                                <label for="usernameOrEmail" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="usernameOrEmail" name="usernameOrEmail" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p>&copy; 2024 Your Company</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
    </style>
</head>
<body>
    <div id="live-chat-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                Live Chat - Welcome, <?php echo ucfirst($_SESSION['sender']); ?>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            const chatBox = $('#chat-box');

            function fetchMessages() {
                $.post('live_chat.php', { action: 'get_messages' }, function (data) {
                    chatBox.empty();
                    data.forEach(msg => {
                        const senderClass = msg.sender === 'admin' ? 'text-danger' : 'text-primary';
                        chatBox.append(
                            `<div><strong class="${senderClass}">${msg.sender}</strong> [${msg.timestamp}]: ${msg.message}</div>`
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
