<?php 
// Ini adalah file yang di-include. Tidak perlu define SECURE_ACCESS di sini,
// karena file induk yang meng-include ini harus sudah mendefinisikannya.
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

<div class="sidebar dosen-sidebar">     
    <div class="logo">         
        <h3><?php echo SITE_NAME; ?></h3>         
        <p>Portal Dosen</p>     
    </div>          

    <ul class="nav-menu">         
        <li><a href="<?php echo SITE_URL; ?>/dosen/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-tachometer-alt"></i> Dashboard         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/dosen/jadwal.php" class="<?php echo ($current_page == 'jadwal.php') ? 'active' : ''; ?>"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-calendar"></i> Jadwal Mengajar         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/dosen/nilai.php" class="<?php echo ($current_page == 'nilai.php') ? 'active' : ''; ?>"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-clipboard-list"></i> Input Nilai         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/dosen/mahasiswa.php" class="<?php echo ($current_page == 'mahasiswa.php') ? 'active' : ''; ?>"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-users"></i> Data Mahasiswa         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/dosen/profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-user"></i> Profile         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/index.php"> <!-- Menggunakan SITE_URL -->
            <i class="fas fa-home"></i> Beranda         
        </a></li>                  

        <li><a href="<?php echo SITE_URL; ?>/auth/logout.php"> <!-- Menggunakan SITE_URL -->
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
