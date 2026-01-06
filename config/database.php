<?php
/**
 * Database Configuration
 * Using PDO for secure database connection
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'edulearn');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default Laragon password is empty

// Database connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Koneksi database gagal. Silakan hubungi administrator.");
}

/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}
