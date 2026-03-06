<?php
// db.php - database + common config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Los_Angeles');
// Update with your DB credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotsheetapp');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Images directory settings
define('IMAGES_DIR', __DIR__ . '/admin/images/');
define('IMAGES_URL', 'admin/images/'); // relative URL used in img src

// PDO connection
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Synchronize MySQL session timezone with PHP's timezone offset (e.g., -08:00 or -07:00 for DST)
    $pdo->exec("SET time_zone = '" . date('P') . "'");
} catch (Exception $e) {
    // Fail gracefully on public side
    error_log("DB Connection failed: " . $e->getMessage());
}

if (is_file(__DIR__ . '/admin/cleanup.php')) {
    require_once __DIR__ . '/admin/cleanup.php';
    if (isset($pdo)) {
        cleanup_expired_files($pdo, date('Y-m-d H:i:s'));
    }
}

// No caching headers for dynamic pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
