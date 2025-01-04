<?php
// community_forum.php

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

// Initialize variables
$error = null;
$success = null;

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $user_id = $_SESSION['UserID'] ?? 0;

    if (empty($title) || empty($content)) {
        $error = 'Please fill out all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO forum_posts (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('ssi', $title, $content, $user_id);

        if ($stmt->execute()) {
            $success = 'Your post has been added.';
        } else {
            $error = 'Failed to add your post. Please try again.';
        }

        $stmt->close();
    }
}

// Fetch all posts
$posts = [];
$result = $conn->query("SELECT forum_posts.id, forum_posts.title, forum_posts.content, forum_posts.created_at, users.Username FROM forum_posts JOIN users ON forum_posts.user_id = users.UserID ORDER BY forum_posts.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ComParts</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Community Forum</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Community Forum</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"> <?php echo htmlspecialchars($success); ?> </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">Create a New Post</div>
            <div class="card-body">
                <form method="POST" action="community_forum.php">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="new_post" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <h3>Recent Posts</h3>
        <?php if (empty($posts)): ?>
            <p>No posts available. Be the first to create one!</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                        <span class="text-muted">by <?php echo htmlspecialchars($post['Username']); ?> on <?php echo htmlspecialchars($post['created_at']); ?></span>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
