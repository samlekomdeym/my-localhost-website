<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Tentang Kami - ' . SITE_NAME;
$current_page = 'about';

// Get sejarah data
try {
    $sejarah = fetchOne("SELECT * FROM sejarah ORDER BY id DESC LIMIT 1");
} catch (Exception $e) {
    logMessage('ERROR', 'Error fetching sejarah: ' . $e->getMessage());
    $sejarah = null;
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Tentang <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4">
                    Universitas terdepan yang berkomitmen menghasilkan lulusan berkualitas tinggi, 
                    berkarakter, dan siap menghadapi tantangan global.
                </p>
                <div class="d-flex gap-3">
                    <a href="#sejarah" class="btn btn-light btn-lg">
                        <i class="fas fa-history me-2"></i>Sejarah Kami
                    </a>
                    <a href="#visi-misi" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-eye me-2"></i>Visi & Misi
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="/placeholder.svg?height=400&width=500" alt="About Us" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stats-icon text-primary mb-3">
                        <i class="fas fa-graduation-cap fa-3x"></i>
                    </div>
                    <h3 class="stats-number text-primary fw-bold">15,000+</h3>
                    <p class="stats-label text-muted mb-0">Alumni Sukses</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stats-icon text-success mb-3">
                        <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    </div>
                    <h3 class="stats-number text-success fw-bold">500+</h3>
                    <p class="stats-label text-muted mb-0">Dosen Berkualitas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stats-icon text-warning mb-3">
                        <i class="fas fa-book fa-3x"></i>
                    </div>
                    <h3 class="stats-number text-warning fw-bold">25+</h3>
                    <p class="stats-label text-muted mb-0">Program Studi</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card text-center p-4 bg-white rounded shadow-sm h-100">
                    <div class="stats-icon text-info mb-3">
                        <i class="fas fa-trophy fa-3x"></i>
                    </div>
                    <h3 class="stats-number text-info fw-bold">100+</h3>
                    <p class="stats-label text-muted mb-0">Prestasi</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sejarah Section -->
<section id="sejarah" class="sejarah-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Sejarah Kami</h2>
                    <p class="lead text-muted">
                        Perjalanan panjang dalam membangun institusi pendidikan berkualitas
                    </p>
                </div>
                
                <?php if ($sejarah && !empty($sejarah['konten'])): ?>
                    <div class="sejarah-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5">
                                <?php echo $sejarah['konten']; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h5>Informasi Sejarah</h5>
                        <p class="mb-0">Informasi sejarah sedang dalam proses pembaruan. Silakan kunjungi kembali nanti.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Visi Misi Section -->
<section id="visi-misi" class="visi-misi-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Visi & Misi</h2>
                    <p class="lead text-muted">
                        Landasan dan arah pengembangan institusi
                    </p>
                </div>
                
                <div class="row g-4">
                    <!-- Visi -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white text-center py-4">
                                <i class="fas fa-eye fa-3x mb-3"></i>
                                <h4 class="mb-0">VISI</h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($sejarah && !empty($sejarah['visi'])): ?>
                                    <p class="lead text-center"><?php echo nl2br(htmlspecialchars($sejarah['visi'])); ?></p>
                                <?php else: ?>
                                    <p class="lead text-center">
                                        Menjadi universitas terdepan yang menghasilkan lulusan berkualitas tinggi, 
                                        berkarakter, dan mampu bersaing di tingkat nasional dan internasional 
                                        dalam era digital dan globalisasi.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Misi -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white text-center py-4">
                                <i class="fas fa-bullseye fa-3x mb-3"></i>
                                <h4 class="mb-0">MISI</h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($sejarah && !empty($sejarah['misi'])): ?>
                                    <?php 
                                    $misi_items = explode("\n", $sejarah['misi']);
                                    if (count($misi_items) > 1): ?>
                                        <ol class="list-group list-group-numbered list-group-flush">
                                            <?php foreach ($misi_items as $item): ?>
                                                <?php if (trim($item)): ?>
                                                    <li class="list-group-item border-0 px-0">
                                                        <?php echo htmlspecialchars(trim($item)); ?>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php else: ?>
                                        <p><?php echo nl2br(htmlspecialchars($sejarah['misi'])); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <ol class="list-group list-group-numbered list-group-flush">
                                        <li class="list-group-item border-0 px-0">
                                            Menyelenggarakan pendidikan tinggi yang berkualitas dan relevan dengan kebutuhan industri 4.0
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Mengembangkan penelitian dan inovasi yang memberikan dampak positif bagi masyarakat
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Melaksanakan pengabdian kepada masyarakat yang berkelanjutan dan bermakna
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Membangun kemitraan strategis dengan industri dan institusi pendidikan internasional
                                        </li>
                                        <li class="list-group-item border-0 px-0">
                                            Menciptakan lingkungan akademik yang kondusif untuk pengembangan karakter dan kepemimpinan
                                        </li>
                                    </ol>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Keunggulan Section -->
<section class="keunggulan-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="display-5 fw-bold mb-4">Keunggulan Kami</h2>
                <p class="lead text-muted">
                    Mengapa memilih <?php echo SITE_NAME; ?> sebagai tempat menuntut ilmu
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-primary-soft mx-auto">
                            <i class="fas fa-medal text-primary fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Akreditasi A</h5>
                    <p class="keunggulan-description text-muted">
                        Semua program studi telah terakreditasi A dari BAN-PT, 
                        menjamin kualitas pendidikan yang terbaik.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-success-soft mx-auto">
                            <i class="fas fa-users text-success fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Dosen Berkualitas</h5>
                    <p class="keunggulan-description text-muted">
                        500+ dosen dengan kualifikasi S2 dan S3 dari universitas 
                        terkemuka dalam dan luar negeri.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-warning-soft mx-auto">
                            <i class="fas fa-microscope text-warning fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Fasilitas Modern</h5>
                    <p class="keunggulan-description text-muted">
                        Laboratorium canggih, perpustakaan digital, dan fasilitas 
                        pembelajaran yang mendukung praktik langsung.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-info-soft mx-auto">
                            <i class="fas fa-handshake text-info fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Kemitraan Industri</h5>
                    <p class="keunggulan-description text-muted">
                        Kerjasama dengan 200+ perusahaan multinasional untuk 
                        program magang dan penempatan kerja.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-danger-soft mx-auto">
                            <i class="fas fa-globe text-danger fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Program Internasional</h5>
                    <p class="keunggulan-description text-muted">
                        Student exchange dan dual degree program dengan 
                        universitas partner di Asia, Eropa, dan Amerika.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="keunggulan-card text-center p-4 h-100">
                    <div class="keunggulan-icon mb-4">
                        <div class="icon-circle bg-purple-soft mx-auto">
                            <i class="fas fa-trophy text-purple fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="keunggulan-title mb-3">Prestasi Gemilang</h5>
                    <p class="keunggulan-description text-muted">
                        Ratusan prestasi di tingkat nasional dan internasional 
                        dalam bidang akademik, penelitian, dan kompetisi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Bergabunglah dengan Kami</h2>
                <p class="lead mb-0">
                    Wujudkan impian akademik Anda bersama <?php echo SITE_NAME; ?>. 
                    Dapatkan pendidikan berkualitas tinggi dengan fasilitas modern dan dosen berpengalaman.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-3">
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.min-vh-50 {
    min-height: 50vh;
}

.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.keunggulan-card {
    transition: transform 0.3s ease;
}

.keunggulan-card:hover {
    transform: translateY(-5px);
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft { background-color: rgba(37, 99, 235, 0.1); }
.bg-success-soft { background-color: rgba(5, 150, 105, 0.1); }
.bg-warning-soft { background-color: rgba(217, 119, 6, 0.1); }
.bg-info-soft { background-color: rgba(8, 145, 178, 0.1); }
.bg-danger-soft { background-color: rgba(220, 38, 38, 0.1); }
.bg-purple-soft { background-color: rgba(147, 51, 234, 0.1); }

.text-purple { color: #9333ea; }

.sejarah-content .card {
    border-left: 4px solid var(--bs-primary);
}

.list-group-numbered .list-group-item {
    padding-left: 0;
    padding-right: 0;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
    
    .stats-number {
        font-size: 2rem;
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
    }
    
    .keunggulan-icon i {
        font-size: 1.5rem !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
