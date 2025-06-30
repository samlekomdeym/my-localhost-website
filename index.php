<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

$page_title = 'Beranda - ' . SITE_NAME;
$current_page = 'home';
$additional_css = ['style.css'];

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="hero-particles"></div>
    </div>
    
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="hero-badge mb-4">
                        <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-star me-2"></i>Akreditasi A - Semua Program Studi
                        </span>
                    </div>
                    
                    <h1 class="hero-title display-3 fw-bold mb-4">
                        Wujudkan Masa Depan Cemerlang di 
                        <span class="text-gradient"><?php echo SITE_NAME; ?></span>
                    </h1>
                    
                    <p class="hero-description lead mb-5">
                        Bergabunglah dengan universitas terdepan yang telah menghasilkan ribuan lulusan berkualitas. 
                        Dengan fasilitas modern, dosen berpengalaman, dan kurikulum yang relevan dengan industri 4.0.
                    </p>
                    
                    <div class="hero-actions">
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary btn-lg me-3 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/about.php" class="btn btn-outline-primary btn-lg mb-3">
                            <i class="fas fa-info-circle me-2"></i>Pelajari Lebih Lanjut
                        </a>
                    </div>
                    
                    <div class="hero-stats mt-5">
                        <div class="row">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number text-primary fw-bold">15K+</h3>
                                    <p class="stat-label text-muted">Alumni</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number text-primary fw-bold">500+</h3>
                                    <p class="stat-label text-muted">Dosen</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number text-primary fw-bold">25+</h3>
                                    <p class="stat-label text-muted">Program Studi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="hero-image-container">
                        <img src="/placeholder.svg?height=600&width=500" alt="MAGNOLIA UNIVERSITY Campus" class="img-fluid hero-main-image">
                        <div class="hero-floating-card card-1">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-success-soft me-3">
                                            <i class="fas fa-graduation-cap text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">95% Tingkat Kelulusan</h6>
                                            <small class="text-muted">Dalam 4 tahun</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hero-floating-card card-2">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-warning-soft me-3">
                                            <i class="fas fa-briefcase text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">90% Terserap Kerja</h6>
                                            <small class="text-muted">Dalam 6 bulan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="section-title display-5 fw-bold mb-4">Mengapa Memilih MAGNOLIA UNIVERSITY?</h2>
                <p class="section-description lead text-muted">
                    Kami berkomitmen memberikan pendidikan terbaik dengan standar internasional
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-primary-soft mx-auto">
                                <i class="fas fa-chalkboard-teacher text-primary fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Dosen Berkualitas</h5>
                        <p class="feature-description text-muted">
                            500+ dosen berpengalaman dengan kualifikasi S2 dan S3 dari universitas terkemuka dalam dan luar negeri
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-success-soft mx-auto">
                                <i class="fas fa-microscope text-success fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Fasilitas Modern</h5>
                        <p class="feature-description text-muted">
                            Laboratorium canggih, perpustakaan digital, dan fasilitas pembelajaran yang mendukung praktik langsung
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-warning-soft mx-auto">
                                <i class="fas fa-handshake text-warning fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Kemitraan Industri</h5>
                        <p class="feature-description text-muted">
                            Kerjasama dengan 200+ perusahaan multinasional untuk program magang dan penempatan kerja
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-info-soft mx-auto">
                                <i class="fas fa-globe text-info fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Program Internasional</h5>
                        <p class="feature-description text-muted">
                            Student exchange dan dual degree program dengan universitas partner di Asia, Eropa, dan Amerika
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-danger-soft mx-auto">
                                <i class="fas fa-trophy text-danger fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Prestasi Gemilang</h5>
                        <p class="feature-description text-muted">
                            Ratusan prestasi di tingkat nasional dan internasional dalam bidang akademik, penelitian, dan kompetisi
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="feature-icon mb-4">
                            <div class="icon-circle bg-purple-soft mx-auto">
                                <i class="fas fa-rocket text-purple fs-2"></i>
                            </div>
                        </div>
                        <h5 class="feature-title mb-3">Inovasi & Riset</h5>
                        <p class="feature-description text-muted">
                            Pusat penelitian dan inkubator bisnis yang mendorong inovasi dan entrepreneurship mahasiswa
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section class="programs-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="section-title display-5 fw-bold mb-4">Program Studi Unggulan</h2>
                <p class="section-description lead text-muted">
                    Pilih dari 25+ program studi yang telah terakreditasi A dan sesuai dengan kebutuhan industri
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="program-card card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="program-icon me-4">
                                <div class="icon-circle bg-primary-soft">
                                    <i class="fas fa-laptop-code text-primary fs-3"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="program-title mb-2">Fakultas Teknologi Informasi</h5>
                                <p class="program-description text-muted mb-3">
                                    Teknik Informatika, Sistem Informasi, Teknik Komputer, Cyber Security
                                </p>
                                <div class="program-features">
                                    <span class="badge bg-primary-soft text-primary me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Lab AI & Machine Learning
                                    </span>
                                    <span class="badge bg-primary-soft text-primary me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Sertifikasi Internasional
                                    </span>
                                    <span class="badge bg-primary-soft text-primary me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Magang di Tech Company
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="program-card card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="program-icon me-4">
                                <div class="icon-circle bg-success-soft">
                                    <i class="fas fa-chart-line text-success fs-3"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="program-title mb-2">Fakultas Bisnis & Manajemen</h5>
                                <p class="program-description text-muted mb-3">
                                    Manajemen, Akuntansi, Marketing, International Business
                                </p>
                                <div class="program-features">
                                    <span class="badge bg-success-soft text-success me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Business Incubator
                                    </span>
                                    <span class="badge bg-success-soft text-success me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Case Study Real
                                    </span>
                                    <span class="badge bg-success-soft text-success me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Networking Alumni
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="program-card card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="program-icon me-4">
                                <div class="icon-circle bg-warning-soft">
                                    <i class="fas fa-cogs text-warning fs-3"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="program-title mb-2">Fakultas Teknik</h5>
                                <p class="program-description text-muted mb-3">
                                    Teknik Mesin, Teknik Elektro, Teknik Sipil, Teknik Industri
                                </p>
                                <div class="program-features">
                                    <span class="badge bg-warning-soft text-warning me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Workshop Modern
                                    </span>
                                    <span class="badge bg-warning-soft text-warning me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Proyek Industri
                                    </span>
                                    <span class="badge bg-warning-soft text-warning me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Sertifikasi Profesi
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="program-card card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="program-icon me-4">
                                <div class="icon-circle bg-info-soft">
                                    <i class="fas fa-atom text-info fs-3"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="program-title mb-2">Fakultas Sains</h5>
                                <p class="program-description text-muted mb-3">
                                    Matematika, Fisika, Kimia, Biologi, Statistika
                                </p>
                                <div class="program-features">
                                    <span class="badge bg-info-soft text-info me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Lab Penelitian
                                    </span>
                                    <span class="badge bg-info-soft text-info me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Publikasi Jurnal
                                    </span>
                                    <span class="badge bg-info-soft text-info me-2 mb-2">
                                        <i class="fas fa-check me-1"></i>Riset Kolaboratif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo SITE_URL; ?>/pages/about.php" class="btn btn-primary btn-lg">
                <i class="fas fa-eye me-2"></i>Lihat Semua Program Studi
            </a>
        </div>
    </div>
</section>

<!-- News Section -->
<section class="news-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="section-title display-5 fw-bold mb-4">Berita & Informasi Terkini</h2>
                <p class="section-description lead text-muted">
                    Ikuti perkembangan terbaru dari MAGNOLIA UNIVERSITY
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            try {
                $news = fetchAll("SELECT * FROM info_kampus WHERE status = 'aktif' ORDER BY created_at DESC LIMIT 3");
                foreach ($news as $item):
            ?>
            <div class="col-lg-4">
                <article class="news-card card border-0 shadow-sm h-100">
                    <?php if ($item['gambar']): ?>
                    <div class="news-image">
                        <img src="<?php echo SITE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($item['gambar']); ?>" 
                             alt="<?php echo htmlspecialchars($item['judul']); ?>" class="card-img-top">
                    </div>
                    <?php else: ?>
                    <div class="news-image-placeholder">
                        <div class="placeholder-content">
                            <i class="fas fa-newspaper fs-1 text-muted"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <div class="news-meta mb-3">
                            <span class="badge bg-<?php echo $item['kategori'] == 'pengumuman' ? 'primary' : ($item['kategori'] == 'berita' ? 'success' : 'warning'); ?>-soft text-<?php echo $item['kategori'] == 'pengumuman' ? 'primary' : ($item['kategori'] == 'berita' ? 'success' : 'warning'); ?>">
                                <?php echo ucfirst($item['kategori']); ?>
                            </span>
                            <small class="text-muted ms-2">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($item['created_at']); ?>
                            </small>
                        </div>
                        
                        <h5 class="news-title mb-3">
                            <a href="<?php echo SITE_URL; ?>/pages/info.php?id=<?php echo $item['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($item['judul']); ?>
                            </a>
                        </h5>
                        
                        <p class="news-excerpt text-muted mb-3">
                            <?php echo truncateText(strip_tags($item['konten']), 120); ?>
                        </p>
                        
                        <a href="<?php echo SITE_URL; ?>/pages/info.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>Baca Selengkapnya
                        </a>
                    </div>
                </article>
            </div>
            <?php 
                endforeach;
            } catch (Exception $e) {
                echo '<div class="col-12"><div class="alert alert-warning">Tidak dapat memuat berita saat ini.</div></div>';
            }
            ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo SITE_URL; ?>/pages/info.php" class="btn btn-primary btn-lg">
                <i class="fas fa-newspaper me-2"></i>Lihat Semua Berita
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="cta-content">
                    <h2 class="cta-title display-5 fw-bold mb-3">Siap Memulai Perjalanan Akademik Anda?</h2>
                    <p class="cta-description lead mb-0">
                        Bergabunglah dengan ribuan mahasiswa yang telah memilih MAGNOLIA UNIVERSITY sebagai tempat meraih impian mereka.
                    </p>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="cta-actions">
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-light btn-lg me-3 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-outline-light btn-lg mb-3">
                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
