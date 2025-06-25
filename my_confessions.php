<?php
session_start();
require_once 'backend/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's confessions
try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Fetch user's confessions with section information
    $stmt = $pdo->prepare(
        'SELECT c.id, c.content, c.timestamp, s.section_code, s.section_name 
         FROM confessions c 
         JOIN sections s ON c.section_id = s.id 
         WHERE c.user_id = ? 
         ORDER BY c.timestamp DESC'
    );
    $stmt->execute([$user_id]);
    $confessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $confessions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Confessions - Anonymous Confessions</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Anonymous Confessions</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my_confessions.php" class="active">My Confessions</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($username); ?>)</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="my-confessions-container">
            <h2>My Confessions</h2>
            
            <?php if (empty($confessions)): ?>
                <p class="no-confessions">You haven't submitted any confessions yet.</p>
            <?php else: ?>
                <div class="confessions-list">
                    <?php foreach ($confessions as $confession): ?>
                        <div class="confession-card">
                            <div class="confession-section">
                                Section: <?php echo htmlspecialchars($confession['section_code']); ?> - 
                                <?php echo htmlspecialchars($confession['section_name']); ?>
                            </div>
                            <p><?php echo htmlspecialchars($confession['content']); ?></p>
                            <div class="confession-time">
                                <?php echo date('F j, Y, g:i a', strtotime($confession['timestamp'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>