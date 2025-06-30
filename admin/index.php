<?php 
require_once '../config/config.php'; 
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../includes/functions.php'; 

// Require admin role
requireRole('admin'); 

$page_title = "Dashboard Admin"; 
// Menggunakan array() untuk PHP 5.6.3
$additional_css = array('admin.css'); 

// Get statistics
try {     
    $db = getDB();          

    // Count total users
    $total_mahasiswa = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE u.status = 'active'");     
    $total_dosen = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id WHERE u.status = 'active'");     
    $total_pending = fetchCount("SELECT COUNT(*) FROM users WHERE status = 'pending'");          

    // Get recent activities     
    $recent_activities = fetchAll("         
        SELECT la.*, u.username          
        FROM log_aktivitas la          
        JOIN users u ON la.user_id = u.id          
        ORDER BY la.created_at DESC          
        LIMIT 10     
    ");          

    // Get recent registrations     
    $recent_registrations = fetchAll("         
        SELECT u.*,                 
        CASE                     
            WHEN u.role = 'mahasiswa' THEN m.nama_lengkap                    
            WHEN u.role = 'dosen' THEN d.nama_lengkap                    
            ELSE u.username                
        END as nama_lengkap         
        FROM users u         
        LEFT JOIN mahasiswa m ON u.id = m.user_id AND u.role = 'mahasiswa'         
        LEFT JOIN dosen d ON u.id = d.user_id AND u.role = 'dosen'         
        WHERE u.status = 'pending'         
        ORDER BY u.created_at DESC         
        LIMIT 5     
    ");          

    // Get system info     
    $db_version = getDatabaseInfo();     
    $total_info = fetchCount("SELECT COUNT(*) FROM info_kampus WHERE status = 'aktif'");      
} catch (Exception $e) {     
    error_log("Dashboard error: " . $e->getMessage());     
    $total_mahasiswa = $total_dosen = $total_pending = $total_info = 0;     
    // Menggunakan array() untuk PHP 5.6.3
    $recent_activities = array();     
    $recent_registrations = array();     
    $db_version = 'Unknown'; 
} 

include '../includes/header.php'; 
?> 

<div class="d-flex">     
    <?php include 'includes/sidebar.php'; ?>          
    <div class="flex-grow-1 p-4">         
        <!-- Header -->         
        <div class="d-flex justify-content-between align-items-center mb-4">             
            <div>                 
                <h1 class="h3 mb-0">Dashboard Admin</h1>                 
                <p class="text-muted">Selamat datang, <?php echo htmlspecialchars(getUsername()); ?>!</p>             
            </div>             
            <div class="text-end">                 
                <small class="text-muted">                     
                    <?php echo formatDateTime(date('Y-m-d H:i:s')); ?>                 
                </small>             
            </div>         
        </div>                  

        <!-- Statistics Cards -->         
        <div class="row mb-4">             
            <div class="col-md-3 mb-3">                 
                <div class="card bg-primary text-white">                     
                    <div class="card-body">                         
                        <div class="d-flex justify-content-between align-items-center">                             
                            <div>                                 
                                <h5 class="card-title">Total Mahasiswa</h5>                                 
                                <h2 class="mb-0"><?php echo number_format($total_mahasiswa); ?></h2>                             
                            </div>                             
                            <div>                                 
                                <i class="fas fa-user-graduate fa-2x opacity-75"></i>                             
                            </div>                         
                        </div>                         
                        <small class="opacity-75">Mahasiswa aktif</small>                     
                    </div>                     
                    <div class="card-footer bg-primary border-0">                         
                        <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/" class="text-white text-decoration-none">                             
                            Lihat Detail <i class="fas fa-arrow-right ms-1"></i>                         
                        </a>                     
                    </div>                 
                </div>             
            </div>                          

            <div class="col-md-3 mb-3">                 
                <div class="card bg-success text-white">                     
                    <div class="card-body">                         
                        <div class="d-flex justify-content-between align-items-center">                             
                            <div>                                 
                                <h5 class="card-title">Total Dosen</h5>                                 
                                <h2 class="mb-0"><?php echo number_format($total_dosen); ?></h2>                             
                            </div>                             
                            <div>                                 
                                <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>                             
                            </div>                         
                        </div>                         
                        <small class="opacity-75">Dosen aktif</small>                     
                    </div>                     
                    <div class="card-footer bg-success border-0">                         
                        <a href="<?php echo SITE_URL; ?>/admin/dosen/" class="text-white text-decoration-none">                             
                            Lihat Detail <i class="fas fa-arrow-right ms-1"></i>                         
                        </a>                     
                    </div>                 
                </div>             
            </div>                          

            <div class="col-md-3 mb-3">                 
                <div class="card bg-warning text-white">                     
                    <div class="card-body">                         
                        <div class="d-flex justify-content-between align-items-center">                             
                            <div>                                 
                                <h5 class="card-title">Pending Approval</h5>                                 
                                <h2 class="mb-0"><?php echo number_format($total_pending); ?></h2>                             
                            </div>                             
                            <div>                                 
                                <i class="fas fa-clock fa-2x opacity-75"></i>                             
                            </div>                         
                        </div>                         
                        <small class="opacity-75">Menunggu verifikasi</small>                     
                    </div>                     
                    <div class="card-footer bg-warning border-0">                         
                        <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/validate.php" class="text-white text-decoration-none">                             
                            Verifikasi <i class="fas fa-arrow-right ms-1"></i>                         
                        </a>                     
                    </div>                 
                </div>             
            </div>                          

            <div class="col-md-3 mb-3">                 
                <div class="card bg-info text-white">                     
                    <div class="card-body">                         
                        <div class="d-flex justify-content-between align-items-center">                             
                            <div>                                 
                                <h5 class="card-title">Info Kampus</h5>                                 
                                <h2 class="mb-0"><?php echo number_format($total_info); ?></h2>                             
                            </div>                             
                            <div>                                 
                                <i class="fas fa-info-circle fa-2x opacity-75"></i>                             
                            </div>                         
                        </div>                         
                        <small class="opacity-75">Info aktif</small>                     
                    </div>                     
                    <div class="card-footer bg-info border-0">                         
                        <a href="<?php echo SITE_URL; ?>/admin/content/info.php" class="text-white text-decoration-none">                             
                            Kelola Info <i class="fas fa-arrow-right ms-1"></i>                         
                        </a>                     
                    </div>                 
                </div>             
            </div>         
        </div>                  

        <div class="row">             
            <!-- Recent Activities -->             
            <div class="col-md-8 mb-4">                 
                <div class="card">                     
                    <div class="card-header">                         
                        <h5 class="card-title mb-0">                             
                            <i class="fas fa-history me-2"></i>                             
                            Aktivitas Terbaru                         
                        </h5>                     
                    </div>                     
                    <div class="card-body">                         
                        <?php if (empty($recent_activities)): ?>                             
                            <div class="text-center text-muted py-4">                                 
                                <i class="fas fa-history fa-3x mb-3 opacity-50"></i>                                 
                                <p>Belum ada aktivitas</p>                             
                            </div>                         
                        <?php else: ?>                             
                            <div class="table-responsive">                                 
                                <table class="table table-hover">                                     
                                    <thead>                                         
                                        <tr>                                             
                                            <th>User</th>                                             
                                            <th>Aktivitas</th>                                             
                                            <th>Deskripsi</th>                                             
                                            <th>Waktu</th>                                         
                                        </tr>                                     
                                    </thead>                                     
                                    <tbody>                                         
                                        <?php foreach ($recent_activities as $activity): ?>                                             
                                            <tr>                                                 
                                                <td>                                                     
                                                    <div class="d-flex align-items-center">                                                         
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"                                                              
                                                            style="width: 32px; height: 32px; font-size: 12px;">                                                             
                                                            <?php echo strtoupper(substr($activity['username'], 0, 1)); ?>                                                         
                                                        </div>                                                         
                                                        <?php echo htmlspecialchars($activity['username']); ?>                                                     
                                                    </div>                                                 
                                                </td>                                                 
                                                <td>                                                     
                                                    <span class="badge bg-primary">                                                         
                                                        <?php echo htmlspecialchars($activity['aktivitas']); ?>                                                     
                                                    </span>                                                 
                                                </td>                                                 
                                                <td><?php echo htmlspecialchars($activity['deskripsi']); ?></td>                                                 
                                                <td>                                                     
                                                    <small class="text-muted">                                                         
                                                        <?php echo formatDateTime($activity['created_at']); ?>                                                     
                                                    </small>                                                 
                                                </td>                                             
                                            </tr>                                         
                                        <?php endforeach; ?>                                     
                                    </tbody>                                 
                                </table>                             
                            </div>                         
                        <?php endif; ?>                     
                    </div>                 
                </div>             
            </div>                          

            <!-- Pending Registrations -->             
            <div class="col-md-4 mb-4">                 
                <div class="card">                     
                    <div class="card-header">                         
                        <h5 class="card-title mb-0">                             
                            <i class="fas fa-user-clock me-2"></i>                             
                            Pendaftaran Baru                         
                        </h5>                     
                    </div>                     
                    <div class="card-body">                         
                        <?php if (empty($recent_registrations)): ?>                             
                            <div class="text-center text-muted py-4">                                 
                                <i class="fas fa-user-plus fa-2x mb-3 opacity-50"></i>                                 
                                <p>Tidak ada pendaftaran baru</p>                             
                            </div>                         
                        <?php else: ?>                             
                            <?php foreach ($recent_registrations as $registration): ?>                                 
                                <div class="d-flex align-items-center mb-3 p-2 border rounded">                                     
                                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-3"                                           
                                        style="width: 40px; height: 40px;">                                         
                                        <i class="fas fa-<?php echo ($registration['role'] == 'mahasiswa') ? 'user-graduate' : 'chalkboard-teacher'; ?>"></i>                                     
                                    </div>                                     
                                    <div class="flex-grow-1">                                         
                                        <div class="fw-bold"><?php echo htmlspecialchars($registration['nama_lengkap']); ?></div>                                         
                                        <small class="text-muted">                                             
                                            <?php echo ucfirst($registration['role']); ?> •                                              
                                            <?php echo formatDateTime($registration['created_at']); ?>                                         
                                        </small>                                     
                                    </div>                                 
                                </div>                             
                            <?php endforeach; ?>                                                          
                            <div class="text-center mt-3">                                 
                                <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/validate.php" class="btn btn-sm btn-primary">                                     
                                    Lihat Semua                                 
                                </a>                             
                            </div>                         
                        <?php endif; ?>                     
                    </div>                 
                </div>             
            </div>         
        </div>                  

        <!-- System Information -->         
        <div class="row">             
            <div class="col-md-12">                 
                <div class="card">                     
                    <div class="card-header">                         
                        <h5 class="card-title mb-0">                             
                            <i class="fas fa-server me-2"></i>                             
                            Informasi Sistem                         
                        </h5>                     
                    </div>                     
                    <div class="card-body">                         
                        <div class="row">                             
                            <div class="col-md-3">                                 
                                <strong>Versi Aplikasi:</strong><br>                                 
                                <span class="text-muted"><?php echo getAppVersion(); ?></span>                             
                            </div>                             
                            <div class="col-md-3">                                 
                                <strong>Database:</strong><br>                                 
                                <span class="text-muted"><?php echo htmlspecialchars($db_version); ?></span>                             
                            </div>                             
                            <div class="col-md-3">                                 
                                <strong>PHP Version:</strong><br>                                 
                                <span class="text-muted"><?php echo PHP_VERSION; ?></span>                             
                            </div>                             
                            <div class="col-md-3">                                 
                                <strong>Server Time:</strong><br>                                 
                                <span class="text-muted"><?php echo date('Y-m-d H:i:s'); ?></span>                             
                            </div>                         
                        </div>                     
                    </div>                 
                </div>             
            </div>         
        </div>     
    </div> 
</div> 
<?php include '../includes/footer.php'; ?>
