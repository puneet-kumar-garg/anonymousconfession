<?php
header('Content-Type: application/json');

require_once 'config.php';
session_start();

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a confession']);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get and validate confession content and section
$confession = trim($_POST['confession'] ?? '');
$section_id = intval($_POST['section_id'] ?? 0);

if (empty($confession)) {
    echo json_encode(['success' => false, 'message' => 'Confession cannot be empty']);
    exit;
}

if (strlen($confession) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Confession is too long (max 1000 characters)']);
    exit;
}

if ($section_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid section']);
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
        echo json_encode(['success' => false, 'message' => 'Selected section does not exist']);
        exit;
    }

    // Prepare and execute the insert statement
    $stmt = $pdo->prepare('INSERT INTO confessions (user_id, section_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $section_id, $confession]);

    echo json_encode(['success' => true, 'message' => 'Confession submitted successfully']);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save confession']);
}