<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

// Redirect based on user role
$role = getRole();
switch ($role) {
    case 'admin':
        header('Location: ' . SITE_URL . '/admin/');
        break;
    case 'dosen':
        header('Location: ' . SITE_URL . '/dosen/');
        break;
    case 'mahasiswa':
        header('Location: ' . SITE_URL . '/mahasiswa/');
        break;
    default:
        header('Location: ' . SITE_URL . '/auth/unauthorized.php');
        break;
}
exit();
?>
