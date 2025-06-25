<?php
header('Content-Type: application/json');

require_once 'config.php';

// Get section ID from query parameter
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;

if ($section_id <= 0) {
    echo json_encode(['error' => 'Invalid section ID']);
    exit;
}

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verify section exists
    $stmt = $pdo->prepare('SELECT id FROM sections WHERE id = ?');
    $stmt->execute([$section_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'Section not found']);
        exit;
    }

    // Fetch confessions for the specified section ordered by timestamp descending
    $stmt = $pdo->prepare(
        'SELECT c.id, c.content, c.timestamp 
         FROM confessions c 
         WHERE c.section_id = ? 
         ORDER BY c.timestamp DESC'
    );
    $stmt->execute([$section_id]);
    $confessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($confessions);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch confessions']);
}