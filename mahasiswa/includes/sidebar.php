<?php 
// Ini adalah file yang di-include. Tidak perlu define SECURE_ACCESS di sini.
// if (!defined('SECURE_ACCESS')) {     
//     die('Direct access not permitted'); 
// } 

// Dapatkan informasi user untuk menampilkan di sidebar
// Pastikan getUserById() ada di functions.php
$current_user_id = getUserId();
$current_user_profile = getUserById($current_user_id);
$current_username = isset($current_user_profile['username']) ? $current_user_profile['username'] : 'Guest';
$current_role_display = isset($current_user_profile['role']) ? ucfirst($current_user_profile['role']) : '';

$current_page = basename($_SERVER['PHP_SELF']); 
?> 

<div class="sidebar mahasiswa-sidebar">     
    <div class="logo">         
        <h3><?php echo SITE_NAME; ?></h3>         
        <p>Portal Mahasiswa</p>     
    </div>          

    <ul class="nav-menu">         
        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">             
            <i class="fas fa-tachometer-alt"></i> Dashboard         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">             
            <i class="fas fa-user"></i> Profile         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/jadwal.php" class="<?php echo ($current_page == 'jadwal.php') ? 'active' : ''; ?>">             
            <i class="fas fa-calendar"></i> Jadwal Kuliah         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/krs.php" class="<?php echo ($current_page == 'krs.php') ? 'active' : ''; ?>">             
            <i class="fas fa-list-alt"></i> KRS         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/nilai.php" class="<?php echo ($current_page == 'nilai.php') ? 'active' : ''; ?>">             
            <i class="fas fa-chart-line"></i> Nilai         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/transkrip.php" class="<?php echo ($current_page == 'transkrip.php') ? 'active' : ''; ?>">             
            <i class="fas fa-file-alt"></i> Transkrip         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/mahasiswa/absensi.php" class="<?php echo ($current_page == 'absensi.php') ? 'active' : ''; ?>">             
            <i class="fas fa-check-square"></i> Absensi         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/index.php">             
            <i class="fas fa-home"></i> Beranda         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/auth/logout.php">             
            <i class="fas fa-sign-out-alt"></i> Logout         
        </a></li>     
    </ul> 
</div>
<!-- User Info (Tambahan di footer sidebar jika ada) -->
<div class="sidebar-footer p-3 border-top border-secondary mt-auto">         
    <div class="d-flex align-items-center">             
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"                   
            style="width: 40px; height: 40px;">                 
            <?php echo strtoupper(substr($current_username, 0, 1)); ?>             
        </div>             
        <div class="flex-grow-1">                 
            <div class="fw-bold small"><?php echo htmlspecialchars($current_username); ?></div>                 
            <div class="text-muted small"><?php echo htmlspecialchars($current_role_display); ?></div>             
        </div>         
    </div>     
</div>
