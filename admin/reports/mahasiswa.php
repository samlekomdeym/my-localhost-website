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

$page_title = "Laporan Mahasiswa";
$additional_css = array('admin.css');

// Get filter parameters
$program_studi = isset($_GET['program_studi']) ? sanitize($_GET['program_studi']) : '';
$tahun_masuk = isset($_GET['tahun_masuk']) ? sanitize($_GET['tahun_masuk']) : '';
$jenis_kelamin = isset($_GET['jenis_kelamin']) ? sanitize($_GET['jenis_kelamin']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

try {
    $db = getDB();

    // Build query conditions
    $where_conditions = array();
    $params = array();

    if (!empty($program_studi)) {
        $where_conditions[] = "m.program_studi = ?";
        $params[] = $program_studi;
    }

    if (!empty($tahun_masuk)) {
        $where_conditions[] = "m.tahun_masuk = ?";
        $params[] = $tahun_masuk;
    }

    if (!empty($jenis_kelamin)) {
        $where_conditions[] = "m.jenis_kelamin = ?";
        $params[] = $jenis_kelamin;
    }

    if (!empty($status)) {
        $where_conditions[] = "u.status = ?";
        $params[] = $status;
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get mahasiswa data
    $query = "SELECT m.*, u.username, u.email, u.status, u.created_at as user_created               
                FROM mahasiswa m               
                JOIN users u ON m.user_id = u.id               
                {$where_clause}               
                ORDER BY m.nama_lengkap";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stats = array();

    // Total mahasiswa
    $stats['total'] = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$where_clause}", $params);

    // By status
    $active_params = $params;
    $active_conditions = $where_conditions;
    $active_conditions[] = "u.status = 'active'";
    $active_params[] = 'active';
    $active_where = 'WHERE ' . implode(' AND ', $active_conditions);
    $stats['active'] = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$active_where}", $active_params);
    $stats['inactive'] = $stats['total'] - $stats['active'];

    // By gender
    $male_params = $params;
    $male_conditions = $where_conditions;
    $male_conditions[] = "m.jenis_kelamin = 'L'";
    $male_params[] = 'L';
    $male_where = 'WHERE ' . implode(' AND ', $male_conditions);
    $stats['male'] = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$male_where}", $male_params);
    $stats['female'] = $stats['total'] - $stats['male'];

    // By program studi
    $prodi_stats = array();
    $prodi_list = fetchColumn("SELECT DISTINCT program_studi FROM mahasiswa ORDER BY program_studi");
    foreach ($prodi_list as $prodi) {
        $prodi_params = $params;
        $prodi_conditions = $where_conditions;
        if ($program_studi != $prodi) { // Only add condition if not already filtered by this prodi
            $prodi_conditions[] = "m.program_studi = ?";
            $prodi_params[] = $prodi;
        }
        $prodi_where = !empty($prodi_conditions) ? 'WHERE ' . implode(' AND ', $prodi_conditions) : '';
        $prodi_stats[$prodi] = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$prodi_where}", $prodi_params);
    }

    // By tahun masuk
    $tahun_stats = array();
    $tahun_list = fetchColumn("SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC");
    foreach ($tahun_list as $tahun) {
        $tahun_params = $params;
        $tahun_conditions = $where_conditions;
        if ($tahun_masuk != $tahun) { // Only add condition if not already filtered by this year
            $tahun_conditions[] = "m.tahun_masuk = ?";
            $tahun_params[] = $tahun;
        }
        $tahun_where = !empty($tahun_conditions) ? 'WHERE ' . implode(' AND ', $tahun_conditions) : '';
        $tahun_stats[$tahun] = fetchCount("SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$tahun_where}", $tahun_params);
    }

    // Get unique values for filters
    $program_studi_list = fetchColumn("SELECT DISTINCT program_studi FROM mahasiswa ORDER BY program_studi");
    $tahun_masuk_list = fetchColumn("SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC");

} catch (Exception $e) {
    error_log("Mahasiswa report error: " . $e->getMessage());
    $mahasiswa_list = array();
    $stats = array('total' => 0, 'active' => 0, 'inactive' => 0, 'male' => 0, 'female' => 0);
    $prodi_stats = array();
    $tahun_stats = array();
    $program_studi_list = array();
    $tahun_masuk_list = array();
}

include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Laporan Mahasiswa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan Mahasiswa</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </button>
                <button class="btn btn-danger" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </button>
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo number_format($stats['total']); ?></h4>
                                <p class="mb-0">Total Mahasiswa</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo number_format($stats['active']); ?></h4>
                                <p class="mb-0">Mahasiswa Aktif</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo number_format($stats['male']); ?></h4>
                                <p class="mb-0">Laki-laki</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-male fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo number_format($stats['female']); ?></h4>
                                <p class="mb-0">Perempuan</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-female fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Program Studi</label>
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
                        <label class="form-label">Tahun Masuk</label>
                        <select class="form-control" name="tahun_masuk">
                            <option value="">Semua Tahun</option>
                            <?php foreach ($tahun_masuk_list as $tahun): ?>
                                <option value="<?php echo htmlspecialchars($tahun); ?>" 
                                        <?php echo ($tahun_masuk == $tahun) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tahun); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin">
                            <option value="">Semua</option>
                            <option value="L" <?php echo ($jenis_kelamin == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo ($jenis_kelamin == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Terapkan Filter
                        </button>
                        <a href="<?php echo SITE_URL; ?>/admin/reports/mahasiswa.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribusi Program Studi</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="prodiChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribusi Tahun Masuk</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tahunChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Data Mahasiswa (<?php echo count($mahasiswa_list); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="mahasiswaReportTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Program Studi</th>
                                <th>Tahun Masuk</th>
                                <th>Jenis Kelamin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data mahasiswa</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mahasiswa_list as $index => $mahasiswa): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['nim']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['program_studi']); ?></td>
                                        <td><?php echo htmlspecialchars($mahasiswa['tahun_masuk']); ?></td>
                                        <td><?php echo ($mahasiswa['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($mahasiswa['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($mahasiswa['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Program Studi Chart
const prodiCtx = document.getElementById('prodiChart').getContext('2d');
const prodiChart = new Chart(prodiCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($prodi_stats)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($prodi_stats)); ?>,
            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Tahun Masuk Chart
const tahunCtx = document.getElementById('tahunChart').getContext('2d');
const tahunChart = new Chart(tahunCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($tahun_stats)); ?>,
        datasets: [{
            label: 'Jumlah Mahasiswa',
            data: <?php echo json_encode(array_values($tahun_stats)); ?>,
            backgroundColor: '#28a745'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function exportToExcel() {
    // Implementation for Excel export
    alert('Fitur export Excel akan segera tersedia');
}

function exportToPDF() {
    // Implementation for PDF export
    alert('Fitur export PDF akan segera tersedia');
}
</script>

<?php include '../../includes/footer.php'; ?>
