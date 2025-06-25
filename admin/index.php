<?php
session_start();
require_once '../backend/config.php';

// Admin credentials (change these and store securely in production)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Use strong password in production

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Handle login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }

    // Show login form
    include 'login_form.php';
    exit;
}

// Handle confession deletion
if (isset($_POST['delete']) && isset($_POST['confession_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare('DELETE FROM confessions WHERE id = ?');
        $stmt->execute([$_POST['confession_id']]);

        $success_message = 'Confession deleted successfully';
    } catch (PDOException $e) {
        $error_message = 'Failed to delete confession';
    }
}

// Fetch all confessions
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query('SELECT * FROM confessions ORDER BY timestamp DESC');
    $confessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Failed to fetch confessions';
    $confessions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anonymous Confessions</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Dashboard</h1>
            <div class="admin-controls">
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <?php if (isset($success_message)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <section class="confessions-admin">
            <?php foreach ($confessions as $confession): ?>
                <div class="confession-card admin">
                    <p><?php echo htmlspecialchars($confession['content']); ?></p>
                    <div class="confession-meta">
                        <span class="confession-time">
                            <?php echo date('M d, Y H:i:s', strtotime($confession['timestamp'])); ?>
                        </span>
                        <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this confession?');">
                            <input type="hidden" name="confession_id" value="<?php echo $confession['id']; ?>">
                            <button type="submit" name="delete" class="delete-btn">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($confessions)): ?>
                <p class="no-confessions">No confessions found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>