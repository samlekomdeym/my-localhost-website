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

$page_title = "Laporan Dosen";
$additional_css = array('admin.css');

// Get filter parameters
$bidang_keahlian = isset($_GET['bidang_keahlian']) ? sanitize($_GET['bidang_keahlian']) : '';
$pendidikan = isset($_GET['pendidikan']) ? sanitize($_GET['pendidikan']) : '';
$jabatan = isset($_GET['jabatan']) ? sanitize($_GET['jabatan']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

try {
    $db = getDB();

    // Build query conditions
    $where_conditions = array();
    $params = array();

    if (!empty($bidang_keahlian)) {
        $where_conditions[] = "d.bidang_keahlian LIKE ?";
        $params[] = "%{$bidang_keahlian}%";
    }

    if (!empty($pendidikan)) {
        $where_conditions[] = "d.pendidikan_terakhir = ?";
        $params[] = $pendidikan;
    }

    if (!empty($jabatan)) {
        $where_conditions[] = "d.jabatan_akademik = ?";
        $params[] = $jabatan;
    }

    if (!empty($status)) {
        $where_conditions[] = "u.status = ?";
        $params[] = $status;
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get dosen data
    $query = "SELECT d.*, u.username, u.email, u.status, u.created_at as user_created               
                FROM dosen d               
                JOIN users u ON d.user_id = u.id               
                {$where_clause}               
                ORDER BY d.nama_lengkap";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $dosen_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stats = array();

    // Total dosen
    $stats['total'] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id {$where_clause}", $params);

    // By status
    $active_params = $params;
    $active_conditions = $where_conditions;
    $active_conditions[] = "u.status = 'active'";
    $active_params[] = 'active';
    $active_where = 'WHERE ' . implode(' AND ', $active_conditions);
    $stats['active'] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id {$active_where}", $active_params);
    $stats['inactive'] = $stats['total'] - $stats['active'];

    // By pendidikan
    $stats['s1'] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id " . 
                             (!empty($where_clause) ? $where_clause . " AND " : "WHERE ") . 
                             "d.pendidikan_terakhir = 'S1'", array_merge($params, array('S1')));
    $stats['s2'] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id " . 
                             (!empty($where_clause) ? $where_clause . " AND " : "WHERE ") . 
                             "d.pendidikan_terakhir = 'S2'", array_merge($params, array('S2')));
    $stats['s3'] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id " . 
                             (!empty($where_clause) ? $where_clause . " AND " : "WHERE ") . 
                             "d.pendidikan_terakhir = 'S3'", array_merge($params, array('S3')));

    // By jabatan
    $jabatan_stats = array();
    $jabatan_list = array('Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar');
    foreach ($jabatan_list as $jab) {
        $jabatan_stats[$jab] = fetchCount("SELECT COUNT(*) FROM dosen d JOIN users u ON d.user_id = u.id " . 
                                         (!empty($where_clause) ? $where_clause . " AND " : "WHERE ") . 
                                         "d.jabatan_akademik = ?", array_merge($params, array($jab)));
    }

    // Get unique values for filters
    $bidang_keahlian_list = fetchColumn("SELECT DISTINCT bidang_keahlian FROM dosen WHERE bidang_keahlian IS NOT NULL AND bidang_keahlian != '' ORDER BY bidang_keahlian");
    $pendidikan_list = fetchColumn("SELECT DISTINCT pendidikan_terakhir FROM dosen WHERE pendidikan_terakhir IS NOT NULL AND pendidikan_terakhir != '' ORDER BY pendidikan_terakhir");
    $jabatan_list_db = fetchColumn("SELECT DISTINCT jabatan_akademik FROM dosen WHERE jabatan_akademik IS NOT NULL AND jabatan_akademik != '' ORDER BY jabatan_akademik");

} catch (Exception $e) {
    error_log("Dosen report error: " . $e->getMessage());
    $dosen_list = array();
    $stats = array('total' => 0, 'active' => 0, 'inactive' => 0, 's1' => 0, 's2' => 0, 's3' => 0);
    $jabatan_stats = array();
    $bidang_keahlian_list = array();
    $pendidikan_list = array();
    $jabatan_list_db = array();
}

include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2"></i>Laporan Dosen</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan Dosen</li>
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
                                <p class="mb-0">Total Dosen</p>
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
                                <p class="mb-0">Dosen Aktif</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
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
                                <h4 class="mb-0"><?php echo number_format($stats['s3']); ?></h4>
                                <p class="mb-0">Bergelar S3</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-graduation-cap fa-2x"></i>
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
                                <h4 class="mb-0"><?php echo number_format($stats['s2']); ?></h4>
                                <p class="mb-0">Bergelar S2</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-graduate fa-2x"></i>
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
                        <label class="form-label">Bidang Keahlian</label>
                        <select class="form-control" name="bidang_keahlian">
                            <option value="">Semua Bidang</option>
                            <?php foreach ($bidang_keahlian_list as $bidang): ?>
                                <option value="<?php echo htmlspecialchars($bidang); ?>" 
                                        <?php echo ($bidang_keahlian == $bidang) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bidang); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pendidikan</label>
                        <select class="form-control" name="pendidikan">
                            <option value="">Semua Pendidikan</option>
                            <?php foreach ($pendidikan_list as $pend): ?>
                                <option value="<?php echo htmlspecialchars($pend); ?>" 
                                        <?php echo ($pendidikan == $pend) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pend); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jabatan</label>
                        <select class="form-control" name="jabatan">
                            <option value="">Semua Jabatan</option>
                            <?php foreach ($jabatan_list_db as $jab): ?>
                                <option value="<?php echo htmlspecialchars($jab); ?>" 
                                        <?php echo ($jabatan == $jab) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jab); ?>
                                </option>
                            <?php endforeach; ?>
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
                        <a href="<?php echo SITE_URL; ?>/admin/reports/dosen.php" class="btn btn-outline-secondary">
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
                        <h5 class="card-title mb-0">Distribusi Pendidikan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="pendidikanChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribusi Jabatan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="jabatanChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Data Dosen (<?php echo count($dosen_list); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="dosenReportTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIDN</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Bidang Keahlian</th>
                                <th>Pendidikan</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dosen_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada data dosen</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dosen_list as $index => $dosen): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($dosen['nidn']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['email']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['bidang_keahlian']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['pendidikan_terakhir']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['jabatan_akademik']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($dosen['status'] == 'active') ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($dosen['status']); ?>
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
// Pendidikan Chart
const pendidikanCtx = document.getElementById('pendidikanChart').getContext('2d');
const pendidikanChart = new Chart(pendidikanCtx, {
    type: 'doughnut',
    data: {
        labels: ['S1', 'S2', 'S3'],
        datasets: [{
            data: [<?php echo $stats['s1']; ?>, <?php echo $stats['s2']; ?>, <?php echo $stats['s3']; ?>],
            backgroundColor: ['#17a2b8', '#ffc107', '#28a745']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Jabatan Chart
const jabatanCtx = document.getElementById('jabatanChart').getContext('2d');
const jabatanChart = new Chart(jabatanCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($jabatan_stats)); ?>,
        datasets: [{
            label: 'Jumlah Dosen',
            data: <?php echo json_encode(array_values($jabatan_stats)); ?>,
            backgroundColor: '#007bff'
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
