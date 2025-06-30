<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../config/config.php'; 
require_once '../includes/functions.php'; 

// Menggunakan isset() dan ternary operator untuk PHP 5.6
$kategori_filter = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : ''; 
$tingkat_filter = isset($_GET['tingkat']) ? sanitize($_GET['tingkat']) : ''; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 

try {     
    $db = getDB();          
    // Build query     
    $where_conditions = array(); // Menggunakan array()
    $params = array(); // Menggunakan array()

    if ($kategori_filter) {         
        $where_conditions[] = "kategori = ?";         
        $params[] = $kategori_filter;     
    }          

    if ($tingkat_filter) {         
        $where_conditions[] = "tingkat = ?";         
        $params[] = $tingkat_filter;     
    }          

    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';          

    // Count total records     
    $count_query = "SELECT COUNT(*) FROM prestasi {$where_clause}";     
    $total_records = fetchCount($count_query, $params);          

    // Pagination     
    $pagination = paginate($total_records, $page, 9);          

    // Get prestasi data     
    $query = "SELECT * FROM prestasi {$where_clause} ORDER BY tahun DESC, id DESC LIMIT {$pagination['records_per_page']} OFFSET {$pagination['offset']}";     
    $prestasi_list = fetchAll($query, $params);      
} catch (Exception $e) {     
    logMessage('ERROR', 'Failed to fetch prestasi data for public page: ' . $e->getMessage());
    $prestasi_list = array(); // Menggunakan array()
    $pagination = paginate(0); 
} 

$page_title = "Prestasi - " . SITE_NAME;
include '../includes/header.php'; // Include header di sini
?> 

<!-- Hero Section -->
<section class="hero-page">
    <div class="hero-background">
        <div class="hero-overlay"></div>
    </div>
    <div class="container">
        <div class="hero-content text-center">
            <div class="hero-badge">
                <i class="fas fa-trophy me-2"></i>
                Pencapaian Terbaik
            </div>
            <h1 class="hero-title text-white">Prestasi MAGNOLIA UNIVERSITY</h1>
            <p class="hero-description text-white">Berbagai pencapaian membanggakan dari civitas akademika yang menginspirasi dan memotivasi generasi masa depan</p>
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
                    <div class="col-md-4">
                        <label for="kategori" class="form-label fw-semibold">Kategori Prestasi</label>
                        <select name="kategori" id="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="Akademik" <?php echo ($kategori_filter == 'Akademik') ? 'selected' : ''; ?>>Akademik</option>
                            <option value="Olahraga" <?php echo ($kategori_filter == 'Olahraga') ? 'selected' : ''; ?>>Olahraga</option>
                            <option value="Seni" <?php echo ($kategori_filter == 'Seni') ? 'selected' : ''; ?>>Seni</option>
                            <option value="Teknologi" <?php echo ($kategori_filter == 'Teknologi') ? 'selected' : ''; ?>>Teknologi</option>
                            <option value="Penelitian" <?php echo ($kategori_filter == 'Penelitian') ? 'selected' : ''; ?>>Penelitian</option>
                            <option value="Lainnya" <?php echo ($kategori_filter == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tingkat" class="form-label fw-semibold">Tingkat Kompetisi</label>
                        <select name="tingkat" id="tingkat" class="form-select">
                            <option value="">Semua Tingkat</option>
                            <option value="Lokal" <?php echo ($tingkat_filter == 'Lokal') ? 'selected' : ''; ?>>Lokal</option>
                            <option value="Regional" <?php echo ($tingkat_filter == 'Regional') ? 'selected' : ''; ?>>Regional</option>
                            <option value="Nasional" <?php echo ($tingkat_filter == 'Nasional') ? 'selected' : ''; ?>>Nasional</option>
                            <option value="Internasional" <?php echo ($tingkat_filter == 'Internasional') ? 'selected' : ''; ?>>Internasional</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="<?php echo SITE_URL; ?>/pages/prestasi.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Prestasi Content -->
<section class="prestasi-content py-5">
    <div class="container">
        <?php if (empty($prestasi_list)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-trophy text-muted" style="font-size: 4rem;"></i>
                </div>
                <h3 class="text-muted mb-3">Tidak Ada Prestasi Ditemukan</h3>
                <p class="text-muted">Belum ada prestasi yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo SITE_URL; ?>/pages/prestasi.php" class="btn btn-primary">
                    <i class="fas fa-refresh me-2"></i>Lihat Semua Prestasi
                </a>
            </div>
        <?php else: ?>
            <!-- Stats -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="stats-card bg-primary text-white rounded-3 p-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-number"><?php echo count($prestasi_list); ?></div>
                                <div class="stat-label">Prestasi Ditampilkan</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number"><?php echo $total_records; ?></div>
                                <div class="stat-label">Total Prestasi</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number">
                                    <?php 
                                    $categories = array_unique(array_column($prestasi_list, 'kategori'));
                                    echo count($categories);
                                    ?>
                                </div>
                                <div class="stat-label">Kategori</div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-number">
                                    <?php 
                                    $years = array_unique(array_column($prestasi_list, 'tahun'));
                                    echo count($years);
                                    ?>
                                </div>
                                <div class="stat-label">Tahun</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prestasi Grid -->
            <div class="row g-4">
                <?php foreach ($prestasi_list as $index => $prestasi): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="prestasi-card h-100" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="prestasi-header">
                                <div class="prestasi-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="prestasi-year"><?php echo htmlspecialchars($prestasi['tahun']); ?></div>
                            </div>
                            
                            <div class="prestasi-badges mb-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($prestasi['kategori']); ?></span>
                                <span class="badge bg-success"><?php echo htmlspecialchars($prestasi['tingkat']); ?></span>
                            </div>
                            
                            <h5 class="prestasi-title"><?php echo htmlspecialchars($prestasi['judul']); ?></h5>
                            
                            <p class="prestasi-description">
                                <?php echo htmlspecialchars(truncateText($prestasi['deskripsi'], 120)); ?>
                            </p>
                            
                            <div class="prestasi-footer">
                                <button class="btn btn-outline-primary btn-sm" onclick="showPrestasiDetail(<?php echo htmlspecialchars(json_encode($prestasi)); ?>)">
                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Prestasi pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>">
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

<!-- Modal Detail Prestasi -->
<div class="modal fade" id="prestasiModal" tabindex="-1" aria-labelledby="prestasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="prestasiModalLabel">
                    <i class="fas fa-trophy me-2"></i>Detail Prestasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prestasiModalBody">
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

/* Prestasi Cards */
.prestasi-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #E8E2DD;
    position: relative;
    overflow: hidden;
}

.prestasi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%);
}

.prestasi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.prestasi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.prestasi-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #F59E0B 0%, #FCD34D 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.prestasi-year {
    background: #6B46C1;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.prestasi-badges .badge {
    margin-right: 0.5rem;
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}

.prestasi-title {
    color: #1F2937;
    font-weight: 600;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.prestasi-description {
    color: #4B5563;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.prestasi-footer {
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
}
</style>

<script>
function showPrestasiDetail(prestasi) {
    const modalBody = document.getElementById('prestasiModalBody');
    const modalLabel = document.getElementById('prestasiModalLabel');
    
    modalLabel.innerHTML = `<i class="fas fa-trophy me-2"></i>${prestasi.judul}`;
    
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-primary fs-6">${prestasi.kategori}</span>
                    <span class="badge bg-success fs-6">${prestasi.tingkat}</span>
                    <span class="badge bg-warning text-dark fs-6">${prestasi.tahun}</span>
                </div>
                
                <h5 class="mb-3 text-primary">${prestasi.judul}</h5>
                
                <div class="prestasi-detail-content">
                    <p class="lead">${prestasi.deskripsi}</p>
                </div>
                
                <div class="prestasi-meta mt-4 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold text-primary">${prestasi.tahun}</div>
                            <small class="text-muted">Tahun</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success">${prestasi.tingkat}</div>
                            <small class="text-muted">Tingkat</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning">${prestasi.kategori}</div>
                            <small class="text-muted">Kategori</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('prestasiModal'));
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
<!-- main.js sudah dimuat via footer.php, tidak perlu di sini lagi -->
<!-- <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> -->
</body> 
</html>
