<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Log the logout activity
    logActivity($user_id, 'Logout', 'User logged out');
    
    // Update last activity in database
    try {
        executeQuery("UPDATE users SET last_activity = NOW() WHERE id = ?", [$user_id]);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to update last activity on logout: ' . $e->getMessage());
    }
}

// Destroy session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Remove remember token from database
    try {
        executeQuery("DELETE FROM remember_tokens WHERE token = ?", [$_COOKIE['remember_token']]);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to remove remember token: ' . $e->getMessage());
    }
}

// Set logout message
setFlashMessage('success', 'Anda telah berhasil logout');

// Redirect to login page
header('Location: ' . SITE_URL . '/auth/login.php');
exit();
?>
