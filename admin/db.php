<?php
// db.php - database + common config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Update with your DB credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotsheetapp');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Images directory settings
define('IMAGES_DIR', __DIR__ . '/images/');
define('IMAGES_URL', 'images/'); // relative URL used in img src

// PDO connection
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Migration: ensure offer_metadata table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS offer_metadata (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        start_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        expiry_time DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // migration for existing table
    try {
        $pdo->exec("ALTER TABLE offer_metadata ADD COLUMN start_time DATETIME DEFAULT CURRENT_TIMESTAMP AFTER filename");
    } catch (Exception $e) {
        // column likely exists
    }

} catch (Exception $e) {
    die("DB Connection failed: " . $e->getMessage());
}

require_once __DIR__ . '/cleanup.php';
// Perform cleanup on every admin/common load (or specific entry points)
// Note: We might want to trigger this only on specific pages to save resources.
if (stripos($_SERVER['PHP_SELF'], 'upload.php') === false) {
    // skip cleanup on upload.php to avoid race conditions if needed, 
    // but generally okay to run it.
    cleanup_expired_files($pdo);
}

// No caching headers for dynamic pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$host = 'localhost';
$db   = 'hotsheetapp';
$user = 'root';
$pass = 'root';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
function log_activity($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, action) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $action);
    $stmt->execute();
    $stmt->close();
}
?>
