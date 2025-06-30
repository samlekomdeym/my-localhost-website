<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';

$page_title = 'Sejarah - ' . SITE_NAME;
$current_page = 'sejarah';

// Get sejarah data
try {
    $sejarah = fetchOne("SELECT * FROM sejarah ORDER BY id DESC LIMIT 1");
    if (!$sejarah) {
        $sejarah = [
            'konten' => '<p>Konten sejarah belum tersedia.</p>',
            'visi' => 'Visi belum tersedia.',
            'misi' => 'Misi belum tersedia.'
        ];
    }
} catch (Exception $e) {
    $sejarah = [
        'konten' => '<p>Terjadi kesalahan saat memuat data sejarah.</p>',
        'visi' => 'Visi belum tersedia.',
        'misi' => 'Misi belum tersedia.'
    ];
    logMessage('ERROR', 'Failed to fetch sejarah: ' . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sejarah</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold text-primary mb-4">Sejarah MAGNOLIA UNIVERSITY</h1>
            <p class="lead text-muted">
                Perjalanan panjang dalam membangun institusi pendidikan tinggi yang berkualitas dan terpercaya
            </p>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-primary">1985 - Pendirian</h5>
                                <p class="card-text">
                                    MAGNOLIA UNIVERSITY didirikan sebagai institut teknologi kecil dengan visi menjadi 
                                    pusat pendidikan tinggi yang unggul di Jakarta Selatan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-success">1990 - Ekspansi Fakultas</h5>
                                <p class="card-text">
                                    Pembukaan Fakultas Bisnis dan Manajemen sebagai respons terhadap kebutuhan 
                                    industri akan tenaga kerja profesional di bidang bisnis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-warning"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-warning">2000 - Akreditasi Nasional</h5>
                                <p class="card-text">
                                    Meraih akreditasi A dari BAN-PT untuk semua program studi, menandai pengakuan 
                                    kualitas pendidikan yang telah dicapai.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-info">2010 - Era Digital</h5>
                                <p class="card-text">
                                    Transformasi digital dengan implementasi sistem pembelajaran online dan 
                                    pembangunan laboratorium teknologi informasi yang canggih.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-purple"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-purple">2020 - Kemitraan Internasional</h5>
                                <p class="card-text">
                                    Menjalin kemitraan strategis dengan universitas-universitas terkemuka di Asia, 
                                    Eropa, dan Amerika untuk program pertukaran mahasiswa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-marker bg-danger"></div>
                    <div class="timeline-content">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title text-danger">2024 - Inovasi Berkelanjutan</h5>
                                <p class="card-text">
                                    Meluncurkan program AI dan Machine Learning, serta pusat riset dan inovasi 
                                    untuk mendukung industri 4.0.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Content -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="card-title text-primary mb-4">Perjalanan Kami</h3>
                    <div class="history-content">
                        <?php echo $sejarah['konten']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vision & Mission -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-primary-soft me-3">
                            <i class="fas fa-eye text-primary fs-3"></i>
                        </div>
                        <h3 class="card-title mb-0 text-primary">Visi</h3>
                    </div>
                    <p class="card-text lead">
                        <?php echo nl2br(htmlspecialchars($sejarah['visi'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-success-soft me-3">
                            <i class="fas fa-bullseye text-success fs-3"></i>
                        </div>
                        <h3 class="card-title mb-0 text-success">Misi</h3>
                    </div>
                    <div class="mission-list">
                        <?php 
                        $misi_items = explode("\n", $sejarah['misi']);
                        foreach ($misi_items as $item):
                            if (trim($item)):
                        ?>
                        <div class="mission-item d-flex align-items-start mb-3">
                            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                            <span><?php echo htmlspecialchars(trim($item)); ?></span>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-5">
                    <div class="row text-center">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="stat-item">
                                <h2 class="stat-number display-4 fw-bold mb-2">39</h2>
                                <p class="stat-label mb-0">Tahun Pengalaman</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="stat-item">
                                <h2 class="stat-number display-4 fw-bold mb-2">15K+</h2>
                                <p class="stat-label mb-0">Alumni Sukses</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="stat-item">
                                <h2 class="stat-number display-4 fw-bold mb-2">500+</h2>
                                <p class="stat-label mb-0">Dosen Berkualitas</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="stat-item">
                                <h2 class="stat-number display-4 fw-bold mb-2">25+</h2>
                                <p class="stat-label mb-0">Program Studi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #28a745, #ffc107, #17a2b8, #6f42c1, #dc3545);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -1.5rem;
    top: 1rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-content {
    margin-left: 2rem;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
.bg-purple-soft { background-color: rgba(111, 66, 193, 0.1); }

.text-purple { color: #6f42c1 !important; }

.history-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.history-content p {
    margin-bottom: 1.5rem;
}

.mission-item {
    font-size: 1rem;
    line-height: 1.6;
}

.stat-number {
    background: linear-gradient(45deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<?php include '../includes/footer.php'; ?>
