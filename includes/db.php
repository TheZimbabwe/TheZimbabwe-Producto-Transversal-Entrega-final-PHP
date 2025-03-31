<?php
/**
 * Database connection using PDO
 * Handles database initialization and connection
 */

require_once 'config.php';

/**
 * Gets a PDO database connection instance
 *
 * @return PDO The database connection
 */
function getDbConnection() {
    static $db = null;
    
    if ($db === null) {
        try {
            // Create a new PDO instance
            $db = new PDO(DB_DSN);
            
            // Set PDO error mode to exception
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign keys
            $db->exec('PRAGMA foreign_keys = ON;');
            
            // Create tables if they don't exist
            createTables($db);
            
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $db;
}

/**
 * Creates necessary database tables if they don't exist
 *
 * @param PDO $db Database connection
 */
function createTables($db) {
    // Create users table
    $db->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create profiles table with foreign key relationship to users
    $db->exec('
        CREATE TABLE IF NOT EXISTS profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            full_name TEXT,
            bio TEXT,
            website TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ');
}
?>
