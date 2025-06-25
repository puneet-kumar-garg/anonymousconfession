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

// Get sections
try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Fetch all sections
    $stmt = $pdo->query('SELECT id, section_code, section_name FROM sections ORDER BY section_code');
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $sections = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Anonymous Confessions</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Anonymous Confessions</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="my_confessions.php">My Confessions</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($username); ?>)</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="dashboard-container">
            <div class="section-selector">
                <h2>Select a Section</h2>
                <div class="section-buttons">
                    <?php foreach ($sections as $section): ?>
                        <button class="section-btn" data-section-id="<?php echo $section['id']; ?>">
                            <?php echo htmlspecialchars($section['section_code']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="confession-form-container" style="display: none;">
                <h2>Submit Confession to <span id="selected-section"></span></h2>
                <form id="confessionForm">
                    <input type="hidden" id="section_id" name="section_id" value="">
                    <div class="form-group">
                        <textarea name="confession" id="confession" placeholder="Share your confession..." required></textarea>
                    </div>
                    <button type="submit" class="btn">Submit Confession</button>
                </form>
                <div id="submission-result"></div>
            </div>
            
            <div class="section-confessions">
                <h2>Recent Confessions</h2>
                <div id="confessions-feed">
                    <p class="select-prompt">Select a section to view confessions</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Section selection
            $('.section-btn').click(function() {
                const sectionId = $(this).data('section-id');
                const sectionName = $(this).text();
                
                // Update UI
                $('.section-btn').removeClass('active');
                $(this).addClass('active');
                $('#selected-section').text(sectionName);
                $('#section_id').val(sectionId);
                $('.confession-form-container').show();
                
                // Load confessions for this section
                loadConfessions(sectionId);
            });
            
            // Confession submission
            $('#confessionForm').submit(function(e) {
                e.preventDefault();
                
                const formData = {
                    section_id: $('#section_id').val(),
                    confession: $('#confession').val()
                };
                
                $.ajax({
                    type: 'POST',
                    url: 'backend/submit_confession.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#submission-result').html('<div class="alert success">Confession submitted successfully!</div>');
                            $('#confession').val('');
                            
                            // Reload confessions
                            loadConfessions($('#section_id').val());
                        } else {
                            $('#submission-result').html('<div class="alert error">Error: ' + response.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#submission-result').html('<div class="alert error">Failed to submit confession. Please try again.</div>');
                    }
                });
            });
            
            // Function to load confessions for a section
            function loadConfessions(sectionId) {
                $.ajax({
                    type: 'GET',
                    url: 'backend/get_confessions.php',
                    data: { section_id: sectionId },
                    dataType: 'json',
                    success: function(confessions) {
                        displayConfessions(confessions);
                    },
                    error: function() {
                        $('#confessions-feed').html('<div class="alert error">Failed to load confessions. Please try again.</div>');
                    }
                });
            }
            
            // Function to display confessions
            function displayConfessions(confessions) {
                const feed = $('#confessions-feed');
                feed.empty();
                
                if (confessions.length === 0) {
                    feed.html('<p>No confessions in this section yet. Be the first to confess!</p>');
                    return;
                }
                
                confessions.forEach(function(confession) {
                    const timestamp = new Date(confession.timestamp).toLocaleString();
                    const card = $('<div class="confession-card"></div>');
                    
                    card.html(`
                        <p>${escapeHtml(confession.content)}</p>
                        <div class="confession-time">${timestamp}</div>
                    `);
                    
                    feed.append(card);
                });
            }
            
            // Helper function to escape HTML
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>