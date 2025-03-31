<?php
/**
 * Helper functions for the application
 */

require_once 'db.php';

/**
 * Sanitize user input
 *
 * @param string $data The input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 *
 * @param string $email Email to validate
 * @return bool True if valid email, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if a username already exists
 *
 * @param string $username Username to check
 * @return bool True if username exists, false otherwise
 */
function usernameExists($username) {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * Check if an email already exists
 *
 * @param string $email Email to check
 * @return bool True if email exists, false otherwise
 */
function emailExists($email) {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * Register a new user
 *
 * @param string $username Username
 * @param string $email Email
 * @param string $password Password
 * @return array Result of registration attempt
 */
function registerUser($username, $email, $password) {
    $db = getDbConnection();
    
    try {
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert user
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        $userId = $db->lastInsertId();
        
        // Create empty profile for user
        $stmt = $db->prepare("INSERT INTO profiles (user_id) VALUES (:user_id)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        // Commit transaction
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'Registration successful! You can now log in.'
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction
        $db->rollBack();
        
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Authenticate user login
 *
 * @param string $username Username
 * @param string $password Password
 * @return array Authentication result
 */
function loginUser($username, $password) {
    $db = getDbConnection();
    
    try {
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'message' => 'Login successful!',
                'user_id' => $user['id']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Check if user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID from session
 *
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get user information by ID
 *
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    $db = getDbConnection();
    
    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.email, u.created_at, 
                   p.full_name, p.bio, p.website
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Update user profile
 *
 * @param int $userId User ID
 * @param array $profileData Profile data to update
 * @return array Update result
 */
function updateProfile($userId, $profileData) {
    $db = getDbConnection();
    
    try {
        $stmt = $db->prepare("
            UPDATE profiles 
            SET full_name = :full_name, 
                bio = :bio, 
                website = :website,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
        ");
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':full_name', $profileData['full_name']);
        $stmt->bindParam(':bio', $profileData['bio']);
        $stmt->bindParam(':website', $profileData['website']);
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'Profile updated successfully!'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Profile update failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Change user password
 *
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Password change result
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = getDbConnection();
    
    try {
        // First verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.'
            ];
        }
        
        // Update with new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            UPDATE users 
            SET password = :password,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'Password changed successfully!'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Password change failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Set a cookie with the given name, value and expiration
 *
 * @param string $name Cookie name
 * @param string $value Cookie value
 * @param int $expiry Expiry time in seconds
 * @return bool True on success, false on failure
 */
function setUserCookie($name, $value, $expiry = COOKIE_LIFETIME) {
    return setcookie(
        $name,
        $value,
        time() + $expiry,
        COOKIE_PATH,
        COOKIE_DOMAIN,
        COOKIE_SECURE,
        COOKIE_HTTPONLY
    );
}

/**
 * Delete a cookie by setting its expiration to the past
 *
 * @param string $name Cookie name
 * @return bool True on success, false on failure
 */
function deleteUserCookie($name) {
    return setcookie(
        $name,
        '',
        time() - 3600,
        COOKIE_PATH,
        COOKIE_DOMAIN,
        COOKIE_SECURE,
        COOKIE_HTTPONLY
    );
}

/**
 * Remember user login with cookies
 *
 * @param int $userId User ID
 * @param string $username Username
 * @return bool Success status
 */
function rememberUser($userId, $username) {
    // Create a secure token (simplified version - in production use a more secure approach)
    $token = bin2hex(random_bytes(32));
    
    // Store token in cookie
    setUserCookie('remember_user', $userId);
    setUserCookie('remember_token', $token);
    
    return true;
}

/**
 * Check and restore user session from remember cookie
 *
 * @return bool True if session was restored, false otherwise
 */
function checkRememberCookie() {
    if (!isset($_COOKIE['remember_user']) || !isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $userId = $_COOKIE['remember_user'];
    
    // Get user from database
    $user = getUserById($userId);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        
        // Renew the cookies
        rememberUser($user['id'], $user['username']);
        
        return true;
    }
    
    // If user not found, clear cookies
    deleteUserCookie('remember_user');
    deleteUserCookie('remember_token');
    
    return false;
}

/**
 * Get all users (for admin purposes)
 *
 * @return array List of users
 */
function getAllUsers() {
    $db = getDbConnection();
    
    try {
        $stmt = $db->query("
            SELECT u.id, u.username, u.email, u.created_at, p.full_name
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            ORDER BY u.id DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Delete a user
 *
 * @param int $userId User ID
 * @return array Result of delete operation
 */
function deleteUser($userId) {
    $db = getDbConnection();
    
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Delete user (profiles will be deleted via foreign key constraint)
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        // Commit transaction
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'User deleted successfully!'
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction
        $db->rollBack();
        
        return [
            'success' => false,
            'message' => 'User deletion failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Validate password strength
 *
 * @param string $password Password to validate
 * @return bool True if password is strong enough
 */
function validatePassword($password) {
    // Password must be at least 8 characters
    return strlen($password) >= 8;
}

/**
 * Create a CSRF token and store it in the session
 *
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from form submission
 *
 * @param string $token Token from form
 * @return bool True if token is valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a specified path
 *
 * @param string $path Path to redirect to
 */
function redirect($path) {
    header("Location: " . $path);
    exit();
}
?>
