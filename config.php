<?php
/**
 * Database Configuration File
 * Gender Desk App System
 * 
 * This file contains the database connection credentials.
 * It should be excluded from version control (.gitignore)
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gender_desk_db');

// Application constants
define('APP_NAME', 'Gender Desk App System');
define('APP_VERSION', '1.0.0');
define('BCRYPT_COST', 12);

// File upload settings
define('UPLOAD_DIR', dirname(__FILE__) . '/uploads/');
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'audio/mpeg', 'audio/wav']);
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Try to establish database connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
