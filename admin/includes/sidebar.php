<?php 
// Ini adalah file yang di-include. Tidak perlu define SECURE_ACCESS di sini,
// karena file induk yang meng-include ini harus sudah mendefinisikannya.
// if (!defined('SECURE_ACCESS')) { 
//     die('Direct access not permitted'); 
// } 

$current_page = basename($_SERVER['PHP_SELF']); 
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); 
?> 

<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh;">     
    <div class="sidebar-header p-3 border-bottom border-secondary">         
        <h4 class="mb-0">             
            <i class="fas fa-tachometer-alt me-2"></i>             
            Admin Panel         
        </h4>         
        <small class="text-muted">Sistem Manajemen Kampus</small>     
    </div>          

    <nav class="sidebar-nav p-3">         
        <ul class="nav flex-column">             
            <!-- Dashboard -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link <?php echo ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : ''; ?>"                     
                    href="<?php echo SITE_URL; ?>/admin/"> <!-- Menggunakan SITE_URL -->
                    <i class="fas fa-home"></i>                     
                    Dashboard                 
                </a>             
            </li>                          

            <!-- Manajemen User -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#userManagement" role="button">                     
                    <i class="fas fa-users"></i>                     
                    Manajemen User                     
                    <i class="fas fa-chevron-down ms-auto"></i>                 
                </a>                 
                <div class="collapse <?php echo (in_array($current_dir, array('mahasiswa', 'dosen'))) ? 'show' : ''; ?>" id="userManagement"> <!-- Menggunakan array() -->
                    <ul class="nav flex-column ms-3">                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_dir == 'mahasiswa') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/mahasiswa/"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-user-graduate"></i>                                 
                                Mahasiswa                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_dir == 'dosen') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/dosen/"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-chalkboard-teacher"></i>                                 
                                Dosen                             
                            </a>                         
                        </li>                     
                    </ul>                 
                </div>             
            </li>                          

            <!-- Manajemen Konten -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#contentManagement" role="button">                     
                    <i class="fas fa-edit"></i>                     
                    Manajemen Konten                     
                    <i class="fas fa-chevron-down ms-auto"></i>                 
                </a>                 
                <div class="collapse <?php echo ($current_dir == 'content') ? 'show' : ''; ?>" id="contentManagement">                     
                    <ul class="nav flex-column ms-3">                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_page == 'info.php') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/content/info.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-info-circle"></i>                                 
                                Info Kampus                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_page == 'sejarah.php') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/content/sejarah.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-history"></i>                                 
                                Sejarah                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_page == 'prestasi.php') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/content/prestasi.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-trophy"></i>                                 
                                Prestasi                             
                            </a>                         
                        </li>                     
                    </ul>                 
                </div>             
            </li>                          

            <!-- Akademik -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#academicManagement" role="button">                     
                    <i class="fas fa-graduation-cap"></i>                     
                    Akademik                     
                    <i class="fas fa-chevron-down ms-auto"></i>                 
                </a>                 
                <div class="collapse" id="academicManagement">                     
                    <ul class="nav flex-column ms-3">                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/academic/mata-kuliah.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-book"></i>                                 
                                Mata Kuliah                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/academic/jadwal.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-calendar"></i>                                 
                                Jadwal                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/academic/nilai.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-chart-line"></i>                                 
                                Nilai                             
                            </a>                         
                        </li>                     
                    </ul>                 
                </div>             
            </li>                          

            <!-- Laporan -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#reportManagement" role="button">                     
                    <i class="fas fa-chart-bar"></i>                     
                    Laporan                     
                    <i class="fas fa-chevron-down ms-auto"></i>                 
                </a>                 
                <div class="collapse <?php echo ($current_dir == 'reports') ? 'show' : ''; ?>" id="reportManagement">                     
                    <ul class="nav flex-column ms-3">                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_page == 'mahasiswa.php' && $current_dir == 'reports') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/reports/mahasiswa.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-user-graduate"></i>                                 
                                Laporan Mahasiswa                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link <?php echo ($current_page == 'dosen.php' && $current_dir == 'reports') ? 'active' : ''; ?>"                                 
                                href="<?php echo SITE_URL; ?>/admin/reports/dosen.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-chalkboard-teacher"></i>                                 
                                Laporan Dosen                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/reports/akademik.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-graduation-cap"></i>                                 
                                Laporan Akademik                             
                            </a>                         
                        </li>                     
                    </ul>                 
                </div>             
            </li>                          

            <!-- Pengaturan -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#settingsManagement" role="button">                     
                    <i class="fas fa-cog"></i>                     
                    Pengaturan                     
                    <i class="fas fa-chevron-down ms-auto"></i>                 
                </a>                 
                <div class="collapse" id="settingsManagement">                     
                    <ul class="nav flex-column ms-3">                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/settings/general.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-sliders-h"></i>                                 
                                Umum                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/settings/users.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-users-cog"></i>                                 
                                User Management                             
                            </a>                         
                        </li>                         
                        <li class="nav-item">                             
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/settings/backup.php"> <!-- Menggunakan SITE_URL -->
                                <i class="fas fa-database"></i>                                 
                                Backup & Restore                             
                            </a>                         
                        </li>                     
                    </ul>                 
                </div>             
            </li>                          

            <hr class="border-secondary">                          

            <!-- Profile -->             
            <li class="nav-item mb-2">                 
                <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/profile.php"> <!-- Menggunakan SITE_URL -->
                    <i class="fas fa-user"></i>                     
                    Profil Saya                 
                </a>             
            </li>                          

            <!-- Logout -->             
            <li class="nav-item">                 
                <a class="nav-link text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"                     
                    onclick="return confirm('Yakin ingin logout?')">                     
                    <i class="fas fa-sign-out-alt"></i>                     
                    Logout                 
                </a>             
            </li>         
        </ul>     
    </nav>          

    <!-- User Info -->     
    <div class="sidebar-footer p-3 border-top border-secondary mt-auto">         
        <div class="d-flex align-items-center">             
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"                   
                style="width: 40px; height: 40px;">                 
                <?php echo strtoupper(substr(getUsername(), 0, 1)); ?>             
            </div>             
            <div class="flex-grow-1">                 
                <div class="fw-bold small"><?php echo htmlspecialchars(getUsername()); ?></div>                 
                <div class="text-muted small">Administrator</div>             
            </div>         
        </div>     
    </div> 
</div> 
<style> 
.sidebar .nav-link {     
    color: rgba(255,255,255,0.8);     
    padding: 12px 15px;     
    margin: 2px 0;     
    border-radius: 8px;     
    transition: all 0.3s ease;     
    text-decoration: none; 
} 
.sidebar .nav-link:hover, .sidebar .nav-link.active {     
    color: white;     
    background: rgba(255,255,255,0.1);     
    transform: translateX(5px); 
} 
.sidebar .nav-link i {     
    width: 20px;     
    margin-right: 10px;     
    text-align: center; 
} 
.sidebar .collapse .nav-link {     
    padding: 8px 15px;     
    font-size: 0.9rem; 
} 
.sidebar .collapse .nav-link:hover {     
    color: #fff;     
    background: rgba(255,255,255,0.05); 
} 
/* Sidebar Toggle */ 
.sidebar-toggle {     
    position: fixed;     
    top: 20px;     
    left: 300px;     
    z-index: 1001;     
    background: #fff;     
    border: none;     
    border-radius: 50%;     
    width: 40px;     
    height: 40px;     
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);     
    transition: all 0.3s ease; 
} 
.sidebar-toggle.collapsed {     
    left: 90px; 
} 
</style>
