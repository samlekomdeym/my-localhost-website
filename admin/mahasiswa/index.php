<?php 
define('SECURE_ACCESS', true);
require_once '../../config/config.php'; 
require_once '../../config/database.php'; 
require_once '../../config/session.php'; 
require_once '../../includes/functions.php'; 

// Check if user is logged in and is admin
if (!isLoggedIn() || getRole() !== 'admin') {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php');
    exit();
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$program_studi = isset($_GET['program_studi']) ? sanitize($_GET['program_studi']) : '';
$tahun_masuk = isset($_GET['tahun_masuk']) ? sanitize($_GET['tahun_masuk']) : '';

$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = array();
$params = array();

if (!empty($search)) {
    $where_conditions[] = "(m.nama_lengkap LIKE ? OR m.nim LIKE ? OR u.email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($program_studi)) {
    $where_conditions[] = "m.program_studi = ?";
    $params[] = $program_studi;
}

if (!empty($tahun_masuk)) {
    $where_conditions[] = "m.tahun_masuk = ?";
    $params[] = $tahun_masuk;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    $db = getDB();

    // Get total count
    $count_query = "SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$where_clause}";
    $total_records = fetchCount($count_query, $params);

    // Get pagination info
    $pagination = paginate($total_records, $page);

    // Get mahasiswa data
    $query = "SELECT m.*, u.username, u.email, u.status               
                FROM mahasiswa m               
                JOIN users u ON m.user_id = u.id               
                {$where_clause}               
                ORDER BY m.created_at DESC               
                LIMIT ? OFFSET ?";
    $current_params = $params;
    $current_params[] = $pagination['records_per_page'];
    $current_params[] = $pagination['offset'];

    $stmt = $db->prepare($query);
    $stmt->execute($current_params);
    $mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get program studi list for filter
    $stmt = $db->query("SELECT DISTINCT program_studi FROM mahasiswa ORDER BY program_studi");
    $program_studi_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get tahun masuk list for filter
    $stmt = $db->query("SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC");
    $tahun_masuk_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    error_log("Get mahasiswa list error: " . $e->getMessage());
    $mahasiswa_list = array();
    $program_studi_list = array();
    $tahun_masuk_list = array();
    $pagination = paginate(0);
}

$page_title = "Kelola Mahasiswa";
$additional_css = array('admin.css');
include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Kelola Mahasiswa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mahasiswa</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Mahasiswa
                </a>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search"
                                    placeholder="Cari nama, NIM, atau email..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="program_studi">
                            <option value="">Semua Program Studi</option>
                            <?php foreach ($program_studi_list as $prodi): ?>
                                <option value="<?php echo htmlspecialchars($prodi); ?>" 
                                        <?php echo ($program_studi == $prodi) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prodi); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="tahun_masuk">
                            <option value="">Semua Tahun Masuk</option>
                            <?php foreach ($tahun_masuk_list as $tahun): ?>
                                <option value="<?php echo htmlspecialchars($tahun); ?>" 
                                        <?php echo ($tahun_masuk == $tahun) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tahun); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Mahasiswa (<?php echo number_format($total_records); ?>)</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.CampusApp.exportToCSV('mahasiswaTable', 'data-mahasiswa.csv')">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.CampusApp.printPage()">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($mahasiswa_list)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data mahasiswa</h5>
                        <p class="text-muted">
                            <?php if (!empty($search) || !empty($program_studi) || !empty($tahun_masuk)): ?>
                                Tidak ditemukan mahasiswa dengan kriteria yang dipilih
                            <?php else: ?>
                                Belum ada mahasiswa yang terdaftar
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Mahasiswa Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="mahasiswaTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Program Studi</th>
                                    <th>Tahun Masuk</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa_list as $index => $mahasiswa): ?>
                                    <tr>
                                        <td><?php echo $pagination['offset'] + $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mahasiswa['nim']); ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 32px; height: 32px; font-size: 12px;">
                                                    <?php echo strtoupper(substr(htmlspecialchars($mahasiswa['nama_lengkap']), 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></div>
                                                    <small class="text-muted">@<?php echo htmlspecialchars($mahasiswa['username']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['program_studi']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['tahun_masuk']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch ($mahasiswa['status']) {
                                                case 'active':
                                                    $status_class = 'bg-success';
                                                    $status_text = 'Aktif';
                                                    break;
                                                case 'pending':
                                                    $status_class = 'bg-warning';
                                                    $status_text = 'Pending';
                                                    break;
                                                case 'inactive':
                                                    $status_class = 'bg-danger';
                                                    $status_text = 'Nonaktif';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                                    $status_text = 'Unknown';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/edit.php?id=<?php echo htmlspecialchars($mahasiswa['user_id']); ?>"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/delete.php?id=<?php echo htmlspecialchars($mahasiswa['id']); ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin ingin menghapus mahasiswa <?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>?')"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['has_prev']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&search=<?php echo urlencode($search); ?>&program_studi=<?php echo urlencode($program_studi); ?>&tahun_masuk=<?php echo urlencode($tahun_masuk); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $pagination['current_page'] - 2);
                                $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&program_studi=<?php echo urlencode($program_studi); ?>&tahun_masuk=<?php echo urlencode($tahun_masuk); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&search=<?php echo urlencode($search); ?>&program_studi=<?php echo urlencode($program_studi); ?>&tahun_masuk=<?php echo urlencode($tahun_masuk); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center text-muted">
                            Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $total_records); ?>
                            dari <?php echo number_format($total_records); ?> data
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
