<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../config/config.php'; // Tambahkan ini
require_once '../includes/functions.php'; 

// Memastikan hanya user dengan role 'dosen' yang bisa mengakses
requireRole('dosen'); 

// Dapatkan profil dosen yang sedang login
// Pastikan getUserProfile() ada di functions.php atau buatkan fungsi baru jika belum ada
// Menggunakan getUserById() dari functions.php dan mendapatkan data dosen dari tabel 'dosen'
$user_info = getUserById(getUserId());
$user_profile = null;
if ($user_info && $user_info['role'] == 'dosen') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM dosen WHERE user_id = ?");
        $stmt->execute(array($user_info['id']));
        $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get dosen profile: ' . $e->getMessage());
        $user_profile = null;
    }
}

// Jika profil dosen tidak ditemukan, mungkin ada masalah data atau user belum lengkap
if (!$user_profile) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil dosen tidak lengkap atau tidak ditemukan.');
    exit();
}

$page_title = "Dashboard Dosen"; 
$additional_css = array('dosen.css'); // Menggunakan array() untuk PHP 5.6.3

// Get statistics
try {     
    $db = getDB();     
    $dosen_id = $user_profile['id']; // ID dari tabel dosen
         
    $stats = array( // Menggunakan array()
        'total_mata_kuliah' => fetchCount("SELECT COUNT(DISTINCT mata_kuliah_id) FROM jadwal WHERE dosen_id = ?", array($dosen_id)),
        'total_mahasiswa' => fetchCount("SELECT COUNT(DISTINCT k.mahasiswa_id) FROM krs k JOIN jadwal j ON k.jadwal_id = j.id WHERE j.dosen_id = ?", array($dosen_id)),
        'jadwal_hari_ini' => fetchCount("SELECT COUNT(*) FROM jadwal WHERE dosen_id = ? AND hari = ?", array($dosen_id, date('l'))),
        'nilai_belum_input' => fetchCount("SELECT COUNT(k.id) FROM krs k JOIN jadwal j ON k.jadwal_id = j.id LEFT JOIN nilai n ON k.id = n.krs_id WHERE j.dosen_id = ? AND n.id IS NULL", array($dosen_id))
    );
         
    // Get jadwal hari ini
    $jadwal_query = "SELECT j.*, mk.nama_mata_kuliah, mk.kode_mata_kuliah                       
                       FROM jadwal j                       
                       JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id                       
                       WHERE j.dosen_id = ? AND j.hari = ?                       
                       ORDER BY j.jam_mulai";     
    $jadwal_stmt = $db->prepare($jadwal_query);     
    $jadwal_stmt->execute(array($dosen_id, date('l'))); // Menggunakan array()
    $jadwal_hari_ini = $jadwal_stmt->fetchAll(PDO::FETCH_ASSOC);     
} catch (Exception $e) {     
    logMessage('ERROR', 'Dosen dashboard stats error: ' . $e->getMessage());
    $stats = array('total_mata_kuliah' => 0, 'total_mahasiswa' => 0, 'jadwal_hari_ini' => 0, 'nilai_belum_input' => 0); // Menggunakan array_fill_keys
    $jadwal_hari_ini = array(); // Menggunakan array()
} 

include '../includes/header.php'; 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Dashboard Dosen - <?php echo SITE_NAME; ?></title> 
    <!-- Menggunakan SITE_URL untuk jalur CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css"> 
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/dosen.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
</head> 
<body>     
    <div class="dashboard dosen-dashboard">         
        <?php include 'includes/sidebar.php'; ?>                  
        <div class="main-content">             
            <div class="dashboard-header">                 
                <h1>Dashboard Dosen</h1>                 
                <div class="user-info">                     
                    <span>Selamat datang, <?php echo htmlspecialchars($user_profile['nama_lengkap']); ?></span>                     
                    <span class="badge">NIDN: <?php echo htmlspecialchars($user_profile['nidn']); ?></span>                     
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-outline-secondary">Logout</a> <!-- Menggunakan SITE_URL dan btn-outline-secondary -->
                </div>             
            </div>                          

            <div class="dashboard-stats">                 
                <div class="stat-card primary">                     
                    <div class="icon">                         
                        <i class="fas fa-book"></i>                     
                    </div>                     
                    <div class="content">                         
                        <h3><?php echo number_format($stats['total_mata_kuliah']); ?></h3>                         
                        <p>Mata Kuliah Diampu</p>                     
                    </div>                 
                </div>                                  

                <div class="stat-card success">                     
                    <div class="icon">                         
                        <i class="fas fa-users"></i>                     
                    </div>                     
                    <div class="content">                         
                        <h3><?php echo number_format($stats['total_mahasiswa']); ?></h3>                         
                        <p>Total Mahasiswa</p>                     
                    </div>                 
                </div>                                  

                <div class="stat-card warning">                     
                    <div class="icon">                         
                        <i class="fas fa-calendar-day"></i>                     
                    </div>                     
                    <div class="content">                         
                        <h3><?php echo number_format($stats['jadwal_hari_ini']); ?></h3>                         
                        <p>Jadwal Hari Ini</p>                     
                    </div>                 
                </div>                                  

                <div class="stat-card danger">                     
                    <div class="icon">                         
                        <i class="fas fa-clipboard-list"></i>                     
                    </div>                     
                    <div class="content">                         
                        <h3><?php echo number_format($stats['nilai_belum_input']); ?></h3>                         
                        <p>Nilai Belum Input</p>                     
                    </div>                 
                </div>             
            </div>                          

            <div class="dashboard-content">                 
                <div class="content-grid">                     
                    <div class="card">                         
                        <div class="card-header">                             
                            <h3>Jadwal Mengajar Hari Ini</h3>                             
                            <span class="badge badge-secondary"><?php echo date('d F Y'); ?></span> <!-- Menambah class badge-secondary -->
                        </div>                         
                        <div class="card-body">                             
                            <?php if (empty($jadwal_hari_ini)): ?>                                 
                                <p>Tidak ada jadwal mengajar hari ini</p>                             
                            <?php else: ?>                                 
                                <div class="jadwal-grid">                                     
                                    <?php foreach ($jadwal_hari_ini as $jadwal): ?>                                         
                                        <div class="jadwal-card">                                             
                                            <h4><?php echo htmlspecialchars($jadwal['nama_mata_kuliah']); ?></h4>                                             
                                            <div class="jadwal-info">                                                 
                                                <span><i class="fas fa-code"></i> <?php echo htmlspecialchars($jadwal['kode_mata_kuliah']); ?></span>                                                 
                                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($jadwal['jam_selesai']))); ?></span>                                                 
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($jadwal['ruangan']); ?></span>                                             
                                            </div>                                         
                                        </div>                                     
                                    <?php endforeach; ?>                                 
                                </div>                             
                            <?php endif; ?>                         
                        </div>                     
                    </div>                                          

                    <div class="card">                         
                        <div class="card-header">                             
                            <h3>Quick Actions</h3>                         
                        </div>                         
                        <div class="card-body">                             
                            <div class="quick-actions">                                 
                                <a href="<?php echo SITE_URL; ?>/dosen/jadwal.php" class="action-btn"> <!-- Menggunakan SITE_URL -->
                                    <i class="fas fa-calendar"></i>                                     
                                    <span>Lihat Jadwal</span>                                 
                                </a>                                                                  
                                <a href="<?php echo SITE_URL; ?>/dosen/nilai.php" class="action-btn"> <!-- Menggunakan SITE_URL -->
                                    <i class="fas fa-clipboard-list"></i>                                     
                                    <span>Input Nilai</span>                                     
                                    <?php if ($stats['nilai_belum_input'] > 0): ?>                                         
                                        <span class="badge"><?php echo number_format($stats['nilai_belum_input']); ?></span>                                     
                                    <?php endif; ?>                                 
                                </a>                                                                  
                                <a href="<?php echo SITE_URL; ?>/dosen/mahasiswa.php" class="action-btn"> <!-- Menggunakan SITE_URL -->
                                    <i class="fas fa-users"></i>                                     
                                    <span>Data Mahasiswa</span>                                 
                                </a>                                                                  
                                <a href="<?php echo SITE_URL; ?>/dosen/profile.php" class="action-btn"> <!-- Menggunakan SITE_URL -->
                                    <i class="fas fa-user"></i>                                     
                                    <span>Profile</span>                                 
                                </a>                             
                            </div>                         
                        </div>                     
                    </div>                 
                </div>             
            </div>         
        </div>     
    </div>          
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> <!-- Menggunakan SITE_URL -->
</body> 
</html>
