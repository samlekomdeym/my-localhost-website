<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/config.php'; 
require_once '../config/database.php'; // Tambahkan ini
require_once '../includes/functions.php'; // Tambahkan ini

// Get latest announcements from info_kampus
$announcements = array(); 
try {     
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM info_kampus WHERE status = 'aktif' AND kategori = 'pengumuman' ORDER BY created_at DESC LIMIT 3"); // Gunakan tabel info_kampus dan kategori
    $stmt->execute();     
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {     
    logMessage('ERROR', 'Failed to fetch announcements: ' . $e->getMessage());
    $announcements = array(); 
} 

// Get latest achievements
$achievements = array(); 
try {     
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM prestasi ORDER BY tahun DESC, created_at DESC LIMIT 3"); // Gunakan kolom tahun dan created_at
    $stmt->execute();     
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {     
    logMessage('ERROR', 'Failed to fetch achievements: ' . $e->getMessage());
    $achievements = array(); 
} 

$page_title = "Beranda - " . SITE_NAME;
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title><?php echo SITE_NAME; ?> - Beranda</title>     
    <meta name="description" content="<?php echo htmlspecialchars(SITE_DESCRIPTION); ?>">          

    <!-- CSS -->     
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">          

    <!-- Favicon -->     
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico"> 
</head> 
<body>     
    <?php include '../includes/navbar.php'; ?>     
    <!-- Hero Section -->     
    <section class="hero">         
        <div class="container">             
            <h1>Selamat Datang di <?php echo SITE_NAME; ?></h1>             
            <p>Membangun masa depan melalui teknologi informasi dan inovasi digital yang berkelanjutan</p>         
        </div>     
    </section>     
    <!-- Main Content -->     
    <main class="main-content">         
        <div class="container">             
            <!-- Program Studi Section -->             
            <section class="mb-5">                 
                <div class="text-center mb-4">                     
                    <h2 style="color: #2d3748; font-weight: 600; margin-bottom: 1rem;">Program Studi</h2>                     
                    <p style="color: #718096; max-width: 600px; margin: 0 auto;">                         
                        Pilih program studi yang sesuai dengan minat dan bakat Anda untuk masa depan yang cerah                     
                    </p>                 
                </div>                                  

                <div class="row">                     
                    <div class="col-md-4">                         
                        <div class="card program-card">                             
                            <div class="program-icon">                                 
                                <i class="fas fa-code"></i>                             
                            </div>                             
                            <h3>Teknik Informatika</h3>                             
                            <p>Program studi yang fokus pada pengembangan perangkat lunak, algoritma, dan sistem komputer.</p>                             
                            <ul class="program-features">                                 
                                <li>Pemrograman dan Rekayasa Perangkat Lunak</li>                                 
                                <li>Kecerdasan Buatan dan Machine Learning</li>                                 
                                <li>Keamanan Siber dan Jaringan</li>                                 
                                <li>Pengembangan Aplikasi Mobile</li>                             
                            </ul>                         
                        </div>                     
                    </div>                                          

                    <div class="col-md-4">                         
                        <div class="card program-card">                             
                            <div class="program-icon">                                 
                                <i class="fas fa-database"></i>                             
                            </div>                             
                            <h3>Sistem Informasi</h3>                             
                            <p>Program studi yang menggabungkan teknologi informasi dengan manajemen bisnis.</p>                             
                            <ul class="program-features">                                 
                                <li>Analisis dan Perancangan Sistem</li>                                 
                                <li>Manajemen Basis Data</li>                                 
                                <li>E-Business dan E-Commerce</li>                                 
                                <li>Audit Sistem Informasi</li>                             
                            </ul>                         
                        </div>                     
                    </div>                                          

                    <div class="col-md-4">                         
                        <div class="card program-card">                             
                            <div class="program-icon">                                 
                                <i class="fas fa-microchip"></i>                             
                            </div>                             
                            <h3>Teknik Komputer</h3>                             
                            <p>Program studi yang fokus pada perangkat keras komputer dan sistem embedded.</p>                             
                            <ul class="program-features">                                 
                                <li>Arsitektur Komputer</li>                                 
                                <li>Sistem Embedded dan IoT</li>                                 
                                <li>Robotika dan Otomasi</li>                                 
                                <li>Jaringan Komputer</li>                             
                            </ul>                         
                        </div>                     
                    </div>                 
                </div>             
            </section>             
            <!-- Content Section -->             
            <div class="row">                 
                <!-- Main Content -->                 
                <div class="col-md-8">                     
                    <!-- Latest Announcements -->                     
                    <div class="card">                         
                        <div class="card-header">                             
                            <h2 class="card-title">                                 
                                <i class="fas fa-bullhorn" style="color: #667eea; margin-right: 0.5rem;"></i>                                 
                                Pengumuman Terbaru                             
                            </h2>                             
                            <p class="card-subtitle">Informasi terkini dari Fakultas Ilmu Komputer</p>                         
                        </div>                                                  

                        <?php if (!empty($announcements)): ?>                             
                            <?php foreach ($announcements as $announcement): ?>                                 
                                <div class="news-item">                                     
                                    <div class="news-meta">                                         
                                        <i class="fas fa-calendar"></i>                                         
                                        <?php echo htmlspecialchars(formatDate($announcement['created_at'])); ?>                                     
                                    </div>                                     
                                    <h3 class="news-title"><?php echo htmlspecialchars($announcement['judul']); ?></h3>                                     
                                    <p class="news-excerpt">                                         
                                        <?php echo htmlspecialchars(truncateText($announcement['konten'], 150)); ?>                                     
                                    </p>                                 
                                </div>                             
                            <?php endforeach; ?>                         
                        <?php else: ?>                             
                            <div class="news-item">                                 
                                <div class="news-meta">                                     
                                    <i class="fas fa-calendar"></i>                                     
                                    <?php echo htmlspecialchars(date('d F Y')); ?>                                 
                                </div>                                 
                                <h3 class="news-title">Penerimaan Mahasiswa Baru 2025</h3>                                 
                                <p class="news-excerpt">                                     
                                    Fakultas Ilmu Komputer membuka pendaftaran mahasiswa baru untuk tahun akademik 2025/2026.                                      
                                    Pendaftaran dibuka mulai tanggal 1 Februari 2025.                                 
                                </p>                             
                            </div>                         
                        <?php endif; ?>                                                  

                        <div class="text-center mt-4">                             
                            <a href="<?php echo SITE_URL; ?>/pages/info.php" class="btn btn-primary">                                 
                                <i class="fas fa-eye"></i>                                 
                                Lihat Semua Pengumuman                             
                            </a>                         
                        </div>                     
                    </div>                 
                </div>                 
                <!-- Sidebar -->                 
                <div class="col-md-4">                     
                    <!-- Quick Links -->                     
                    <div class="sidebar">                         
                        <h3 class="sidebar-title">                             
                            <i class="fas fa-link"></i>                             
                            Quick Links                         
                        </h3>                         
                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="sidebar-link"> <!-- Menggunakan SITE_URL -->
                            <i class="fas fa-user-graduate"></i>                             
                            Portal Akademik                         
                        </a>                         
                        <a href="<?php echo SITE_URL; ?>/pages/info.php" class="sidebar-link">                             
                            <i class="fas fa-newspaper"></i>                             
                            Berita & Pengumuman                         
                        </a>                         
                        <a href="<?php echo SITE_URL; ?>/pages/prestasi.php" class="sidebar-link">                             
                            <i class="fas fa-trophy"></i>                             
                            Prestasi Mahasiswa                         
                        </a>                         
                        <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="sidebar-link">                             
                            <i class="fas fa-phone"></i>                             
                            Hubungi Kami                         
                        </a>                     
                    </div>                     
                    <!-- Latest Achievements -->                     
                    <div class="sidebar">                         
                        <h3 class="sidebar-title">                             
                            <i class="fas fa-trophy"></i>                             
                            Prestasi Terbaru                         
                        </h3>                                                  

                        <?php if (!empty($achievements)): ?>                             
                            <?php foreach ($achievements as $achievement): ?>                                 
                                <div class="news-item">                                     
                                    <div class="news-meta">                                         
                                        <span style="background: #48bb78; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">                                             
                                            <?php echo htmlspecialchars($achievement['kategori']); ?>                                         
                                        </span>                                         
                                        <span style="margin-left: 0.5rem;">                                             
                                            <?php echo htmlspecialchars($achievement['tahun']); ?>                                         
                                        </span>                                     
                                    </div>                                     
                                    <h4 class="news-title" style="font-size: 1rem;">                                         
                                        <?php echo htmlspecialchars($achievement['judul']); ?>                                     
                                    </h4>                                     
                                    <p class="news-excerpt" style="font-size: 0.9rem;">                                         
                                        <?php echo htmlspecialchars(truncateText($achievement['deskripsi'], 100)); ?>                                     
                                    </p>                                 
                                </div>                             
                            <?php endforeach; ?>                         
                        <?php else: ?>                             
                            <div class="news-item">                                 
                                <div class="news-meta">                                     
                                    <span style="background: #48bb78; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">                                         
                                        Akademik                                     
                                    </span>                                     
                                    <span style="margin-left: 0.5rem;">2024</span>                                 
                                </div>                                 
                                <h4 class="news-title" style="font-size: 1rem;">                                     
                                    Juara 2 Programming Contest Regional                                 
                                </h4>                                 
                                <p class="news-excerpt" style="font-size: 0.9rem;">                                     
                                    Tim programming berhasil meraih juara 2 dalam kontes programming tingkat regional...                                 
                                </p>                             
                            </div>                                                          

                            <div class="news-item">                                 
                                <div class="news-meta">                                     
                                    <span style="background: #ed8936; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">                                         
                                        Penelitian                                     
                                    </span>                                     
                                    <span style="margin-left: 0.5rem;">2024</span>                                 
                                </div>                                 
                                <h4 class="news-title" style="font-size: 1rem;">                                     
                                    Best Paper Award ICACSIS 2024                                 
                                </h4>                                 
                                <p class="news-excerpt" style="font-size: 0.9rem;">                                     
                                    Paper penelitian dosen berhasil meraih penghargaan best paper di konferensi internasional...                                 
                                </p>                             
                            </div>                         
                        <?php endif; ?>                     
                    </div>                 
                </div>             
            </div>         
        </div>     
    </main>     
    <?php include '../includes/footer.php'; ?>     
    <!-- JavaScript -->     
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> 
</body> 
</html>
