<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'online_book_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/online-book-selling-system');

// Create connection
function getPDOConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Initialize database connection
$pdo = getPDOConnection();
?>