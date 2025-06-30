<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Start secure session
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
    }
}

// Session timeout check
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && checkSessionTimeout();
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getRole() {
    return $_SESSION['role'] ?? null;
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'nama' => $_SESSION['nama'] ?? ''
    ];
}

// Set user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'] ?? $user['username'];
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
}

// Destroy user session
function destroyUserSession() {
    session_unset();
    session_destroy();
}

// Check role permission
function hasRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = getRole();
    
    // Admin has access to everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Check specific role
    return $user_role === $required_role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit();
    }
}

// Require specific role
function requireRole($required_role) {
    requireLogin();
    
    if (!hasRole($required_role)) {
        header('Location: ' . SITE_URL . '/auth/unauthorized.php');
        exit();
    }
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function hasFlashMessages() {
    return !empty($_SESSION['flash_messages']);
}

// Remember user preference
function setUserPreference($key, $value) {
    if (!isset($_SESSION['user_preferences'])) {
        $_SESSION['user_preferences'] = [];
    }
    $_SESSION['user_preferences'][$key] = $value;
}

function getUserPreference($key, $default = null) {
    return $_SESSION['user_preferences'][$key] ?? $default;
}

// Session security
function regenerateSessionId() {
    session_regenerate_id(true);
}

// Check for session hijacking
function validateSession() {
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        destroyUserSession();
        return false;
    }
    
    return true;
}

// Initialize session security
if (isLoggedIn()) {
    validateSession();
}
?>
