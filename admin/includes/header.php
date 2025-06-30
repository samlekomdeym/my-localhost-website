<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel - ' . SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
    
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo SITE_URL; ?>/assets/css/<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --sidebar-width: 280px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            font-size: 14px;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar.collapsed {
            width: 70px;
        }
        
        .admin-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }
        
        .admin-content.expanded {
            margin-left: 70px;
        }
        
        .admin-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .admin-main {
            padding: 1.5rem;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .nav-text {
            transition: opacity 0.3s ease;
        }
        
        .admin-sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .admin-sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }
        
        .admin-sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        
        .sidebar-toggle {
            position: absolute;
            top: 1rem;
            right: -15px;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: #1d4ed8;
        }
        
        .user-dropdown .dropdown-toggle {
            border: none;
            background: none;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .user-dropdown .dropdown-toggle:after {
            margin-left: 0.5rem;
        }
        
        .notification-dropdown .dropdown-toggle {
            position: relative;
            border: none;
            background: none;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
            color: white;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stats-card .card-body {
            position: relative;
            z-index: 1;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .table {
            font-size: 0.9rem;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #e2e8f0;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar will be included here -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content" id="adminContent">
            <!-- Header -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link d-md-none me-2" id="mobileSidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="<?php echo SITE_URL; ?>/admin/" class="text-decoration-none">
                                        <i class="fas fa-home"></i> Dashboard
                                    </a>
                                </li>
                                <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                                        <?php if (isset($breadcrumb['url'])): ?>
                                            <li class="breadcrumb-item">
                                                <a href="<?php echo $breadcrumb['url']; ?>" class="text-decoration-none">
                                                    <?php echo $breadcrumb['title']; ?>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="breadcrumb-item active" aria-current="page">
                                                <?php echo $breadcrumb['title']; ?>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ol>
                        </nav>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="notification-dropdown dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationBadge">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifikasi</span>
                                    <button class="btn btn-sm btn-link p-0" onclick="markAllAsRead()">
                                        <small>Tandai semua dibaca</small>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationList" style="max-height: 300px; overflow-y: auto;">
                                    <li class="dropdown-item-text text-center text-muted py-3">
                                        <i class="fas fa-bell-slash fa-2x mb-2"></i><br>
                                        Tidak ada notifikasi
                                    </li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center" href="<?php echo SITE_URL; ?>/admin/notifications.php">
                                        Lihat semua notifikasi
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown user-dropdown">
                            <button class="dropdown-toggle d-flex align-items-center" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="me-2 text-end">
                                    <div class="fw-semibold"><?php echo htmlspecialchars(getUsername()); ?></div>
                                    <small class="text-muted"><?php echo ucfirst(getRole()); ?></small>
                                </div>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr(getUsername(), 0, 1)); ?>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php">
                                        <i class="fas fa-user me-2"></i>Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings.php">
                                        <i class="fas fa-cog me-2"></i>Pengaturan
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="admin-main">
                <!-- Flash Messages -->
                <?php if (hasFlashMessage()): ?>
                    <?php $flash = getFlashMessage(); ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
