<?php
// admin_chat.php
session_start();

// Set the sender to 'admin'
$_SESSION['sender'] = 'admin';

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'comparts1';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Handle AJAX requests for retrieving and replying to messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'get_messages') {
        $stmt = $conn->prepare("SELECT id, sender, message, timestamp, UserID FROM messages ORDER BY timestamp ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];

        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        $stmt->close();
        echo json_encode($messages);
        exit;
    } elseif ($action === 'send_message') {
        $message = $_POST['message'] ?? '';
        $userID = $_POST['userID'] ?? 0;

        if ($message && $userID) {
            $stmt = $conn->prepare("INSERT INTO messages (sender, message, UserID) VALUES ('admin', ?, ?)");
            $stmt->bind_param('si', $message, $userID);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Message and UserID are required']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #admin-chat-container {
            margin: 20px auto;
            max-width: 800px;
        }

        #chat-box {
            height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div id="admin-chat-container">
        <h1 class="text-center">Admin Chat Interface</h1>
        <div id="chat-box" class="border p-3"></div>
        <form id="reply-form">
            <div class="mb-3">
                <label for="userID" class="form-label">Replying to User ID</label>
                <input type="number" class="form-control" id="userID" placeholder="Enter User ID" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" rows="3" placeholder="Type your reply..." required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Send Reply</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            const chatBox = $('#chat-box');

            function fetchMessages() {
                $.post('admin_chat.php', { action: 'get_messages' }, function (data) {
                    chatBox.empty();
                    data.forEach(msg => {
                        const senderClass = msg.sender === 'admin' ? 'text-danger' : 'text-primary';
                        chatBox.append(
                            `<div><strong class="${senderClass}">${msg.sender}</strong> [${msg.timestamp}]: ${msg.message} (UserID: ${msg.UserID})</div>`
                        );
                    });
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }, 'json');
            }

            $('#reply-form').submit(function (e) {
                e.preventDefault();
                const message = $('#message').val();
                const userID = $('#userID').val();

                if (message.trim() && userID) {
                    $.post('admin_chat.php', { action: 'send_message', message, userID }, function (response) {
                        if (response.success) {
                            $('#message').val('');
                            $('#userID').val('');
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
