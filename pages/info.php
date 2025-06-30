<?php 
define('SECURE_ACCESS', true);
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../config/config.php'; 
require_once '../includes/functions.php'; 

$tipe_filter = sanitize($_GET['tipe'] ?? ''); 
$page = (int)($_GET['page'] ?? 1); 

try {     
    $db = getDB();          
    $where_conditions = ["ik.status = 'aktif'"];
    $params = []; 

    if ($tipe_filter) {         
        $where_conditions[] = "ik.kategori = ?"; 
        $params[] = $tipe_filter;     
    }          

    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);          
    $count_query = "SELECT COUNT(*) FROM info_kampus ik JOIN users u ON ik.created_by = u.id {$where_clause}";
    $total_records = fetchCount($count_query, $params);          
    $pagination = paginate($total_records, $page, 6);          
    $query = "SELECT ik.id, ik.judul, ik.konten, ik.kategori, ik.gambar, ik.created_at, u.username as author                
                FROM info_kampus ik                
                LEFT JOIN users u ON ik.created_by = u.id                
                {$where_clause}                
                ORDER BY ik.created_at DESC        
                LIMIT {$pagination['records_per_page']} OFFSET {$pagination['offset']}";          

    $info_list = fetchAll($query, $params); 
} catch (Exception $e) {     
    logMessage('ERROR', 'Failed to fetch info list for public page: ' . $e->getMessage());
    $info_list = []; 
    $pagination = paginate(0); 
} 

$page_title = "Informasi Terkini - " . SITE_NAME; 
include '../includes/header.php'; 
?> 

<!-- Hero Section -->
<section class="hero-page">
    <div class="hero-background">
        <div class="hero-overlay"></div>
    </div>
    <div class="container">
        <div class="hero-content text-center">
            <div class="hero-badge">
                <i class="fas fa-newspaper me-2"></i>
                Informasi Terkini
            </div>
            <h1 class="hero-title text-white">Berita & Pengumuman</h1>
            <p class="hero-description text-white">Dapatkan informasi terbaru seputar kegiatan akademik, pengumuman penting, dan berita terkini dari MAGNOLIA UNIVERSITY</p>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
        </svg>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section py-4 bg-light">
    <div class="container">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="tipe" class="form-label fw-semibold">Kategori Informasi</label>
                        <select name="tipe" id="tipe" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="pengumuman" <?php echo ($tipe_filter === 'pengumuman') ? 'selected' : ''; ?>>📢 Pengumuman</option>
                            <option value="berita" <?php echo ($tipe_filter === 'berita') ? 'selected' : ''; ?>>📰 Berita</option>
                            <option value="event" <?php echo ($tipe_filter === 'event') ? 'selected' : ''; ?>>🎉 Event</option>
                            <option value="akademik" <?php echo ($tipe_filter === 'akademik') ? 'selected' : ''; ?>>🎓 Akademik</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="<?php echo SITE_URL; ?>/pages/info.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Info Content -->
<section class="info-content py-5">
    <div class="container">
        <?php if (empty($info_list)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-newspaper text-muted" style="font-size: 4rem;"></i>
                </div>
                <h3 class="text-muted mb-3">Tidak Ada Informasi</h3>
                <p class="text-muted">Belum ada informasi yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo SITE_URL; ?>/pages/info.php" class="btn btn-primary">
                    <i class="fas fa-refresh me-2"></i>Lihat Semua Informasi
                </a>
            </div>
        <?php else: ?>
            <!-- Info Stats -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="stats-card bg-primary text-white rounded-3 p-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-number"><?php echo count($info_list); ?></div>
                                <div class="stat-label">Informasi Ditampilkan</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number"><?php echo $total_records; ?></div>
                                <div class="stat-label">Total Informasi</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number">
                                    <?php 
                                    $categories = array_unique(array_column($info_list, 'kategori'));
                                    echo count($categories);
                                    ?>
                                </div>
                                <div class="stat-label">Kategori</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number"><?php echo date('Y'); ?></div>
                                <div class="stat-label">Tahun Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="row g-4">
                <?php foreach ($info_list as $index => $info): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="info-card h-100" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="info-image">
                                <?php if (!empty($info['gambar'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($info['gambar']); ?>"
                                         alt="<?php echo htmlspecialchars($info['judul']); ?>"
                                         class="img-fluid info-image-main">
                                <?php else: ?>
                                    <div class="info-image-placeholder">
                                        <i class="fas fa-<?php 
                                            $icons = [
                                                'pengumuman' => 'bullhorn',
                                                'berita' => 'newspaper', 
                                                'event' => 'calendar-alt',
                                                'akademik' => 'graduation-cap'
                                            ];
                                            echo $icons[$info['kategori']] ?? 'info-circle';
                                        ?>"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="info-category-badge">
                                    <span class="badge bg-<?php 
                                        $colors = [
                                            'pengumuman' => 'warning',
                                            'berita' => 'info', 
                                            'event' => 'success',
                                            'akademik' => 'primary'
                                        ];
                                        echo $colors[$info['kategori']] ?? 'secondary';
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($info['kategori'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="info-content">
                                <div class="info-meta mb-3">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-calendar me-2"></i>
                                        <span><?php echo formatDate($info['created_at']); ?></span>
                                        <?php if (!empty($info['author'])): ?>
                                            <span class="ms-3">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($info['author']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <h5 class="info-title mb-3">
                                    <?php echo htmlspecialchars($info['judul']); ?>
                                </h5>
                                
                                <p class="info-excerpt">
                                    <?php echo htmlspecialchars(truncateText(strip_tags($info['konten'] ?? ''), 150)); ?>
                                </p>
                                
                                <div class="info-footer">
                                    <button class="btn btn-outline-primary btn-sm" onclick="showInfoDetail(<?php echo htmlspecialchars(json_encode($info)); ?>)">
                                        <i class="fas fa-eye me-2"></i>Baca Selengkapnya
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Info pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&tipe=<?php echo urlencode($tipe_filter); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&tipe=<?php echo urlencode($tipe_filter); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&tipe=<?php echo urlencode($tipe_filter); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Detail Info -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="fas fa-newspaper me-2"></i>Detail Informasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="infoModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Hero Page Styles */
.hero-page {
    position: relative;
    padding: 120px 0 80px;
    background: linear-gradient(135deg, #6B46C1 0%, #553C9A 50%, #D97706 100%);
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
    color: white;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    margin-bottom: 1rem;
    line-height: 1.1;
}

.hero-description {
    font-size: 1.125rem;
    line-height: 1.7;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.hero-wave svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 60px;
    fill: #FEFEFE;
}

/* Stats Card */
.stats-card {
    background: linear-gradient(135deg, #6B46C1 0%, #8B5CF6 100%) !important;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

/* Info Cards */
.info-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #E8E2DD;
    position: relative;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.info-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.info-image-main {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.info-card:hover .info-image-main {
    transform: scale(1.05);
}

.info-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.info-category-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
}

.info-category-badge .badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 2rem;
}

.info-content {
    padding: 1.5rem;
}

.info-meta {
    font-size: 0.875rem;
}

.info-title {
    color: #1F2937;
    font-weight: 600;
    line-height: 1.3;
}

.info-excerpt {
    color: #4B5563;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.info-footer {
    margin-top: auto;
}

/* Empty State */
.empty-state {
    padding: 4rem 2rem;
}

.empty-icon {
    opacity: 0.5;
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
}

.modal-header {
    border-bottom: none;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    border-top: 1px solid #E8E2DD;
    padding: 1rem 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-page {
        padding: 80px 0 60px;
    }
    
    .stats-card .row > div {
        margin-bottom: 1rem;
    }
    
    .stats-card .row > div:last-child {
        margin-bottom: 0;
    }
    
    .info-image {
        height: 150px;
    }
}
</style>

<script>
function showInfoDetail(info) {
    const modalBody = document.getElementById('infoModalBody');
    const modalLabel = document.getElementById('infoModalLabel');
    
    modalLabel.innerHTML = `<i class="fas fa-newspaper me-2"></i>${info.judul}`;
    
    const categoryColors = {
        'pengumuman': 'warning',
        'berita': 'info',
        'event': 'success',
        'akademik': 'primary'
    };
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-${categoryColors[info.kategori] || 'secondary'} fs-6">
                        ${info.kategori.charAt(0).toUpperCase() + info.kategori.slice(1)}
                    </span>
                    <span class="badge bg-light text-dark fs-6">
                        <i class="fas fa-calendar me-1"></i>
                        ${window.CampusApp.formatDate(info.created_at)}
                    </span>
                    ${info.author ? `<span class="badge bg-light text-dark fs-6">
                        <i class="fas fa-user me-1"></i>
                        ${info.author}
                    </span>` : ''}
                </div>
                
                ${info.gambar ? `
                    <div class="mb-4">
                        <img src="<?php echo SITE_URL; ?>/assets/uploads/${info.gambar}" 
                             alt="${info.judul}" 
                             class="img-fluid rounded shadow-sm w-100" 
                             style="max-height: 300px; object-fit: cover;">
                    </div>
                ` : ''}
                
                <div class="info-detail-content">
                    <div class="content-body" style="line-height: 1.8;">
                        ${info.konten.replace(/\n/g, '<br>')}
                    </div>
                </div>
                
                <div class="info-meta mt-4 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="fw-bold text-primary">${info.kategori.charAt(0).toUpperCase() + info.kategori.slice(1)}</div>
                            <small class="text-muted">Kategori</small>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-bold text-success">${window.CampusApp.formatDate(info.created_at)}</div>
                            <small class="text-muted">Tanggal</small>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-bold text-warning">${info.author || 'Admin'}</div>
                            <small class="text-muted">Penulis</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('infoModal'));
    modal.show();
}

// Initialize AOS (Animate On Scroll) if available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            easing: 'ease-out-cubic',
            once: true
        });
    }
});
</script>

<!-- AOS Library for animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<?php include '../includes/footer.php'; ?>
