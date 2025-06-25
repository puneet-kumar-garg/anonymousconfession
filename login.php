<?php
session_start();
require_once 'backend/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Create database connection
            $pdo = new PDO(
                "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
                $db_user,
                $db_pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Check user credentials
            $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Anonymous Confessions</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Anonymous Confessions</h1>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="login.php" class="active">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="form-container">
            <h2>Login to Your Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <p class="form-footer">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>