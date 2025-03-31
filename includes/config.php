<?php
/**
 * Configuration file for the PHP application
 * Contains database connection parameters and other settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define database configuration
define('DB_FILE', __DIR__ . '/../db/users.sqlite');
define('DB_DSN', 'sqlite:' . DB_FILE);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application constants
define('SITE_NAME', 'PHP User Management System');
define('BASE_URL', '/');

// Set timezone
date_default_timezone_set('UTC');

// Create db directory if it doesn't exist
if (!file_exists(__DIR__ . '/../db')) {
    mkdir(__DIR__ . '/../db', 0777, true);
}

// Cookie settings
define('COOKIE_LIFETIME', 86400 * 30); // 30 days
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false);
define('COOKIE_HTTPONLY', true);
?>
