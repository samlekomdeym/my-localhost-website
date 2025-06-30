<?php 
define('SECURE_ACCESS', true);
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php');
    exit();
}

$page_title = 'Kelola Dosen';
$current_page = 'dosen';

// Get filter parameters
$search = sanitize($_GET['search'] ?? '');
$jabatan_filter = sanitize($_GET['jabatan'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$pendidikan_filter = sanitize($_GET['pendidikan'] ?? '');
$page = (int)($_GET['page'] ?? 1);

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(nama LIKE ? OR nip LIKE ? OR email LIKE ? OR bidang_keahlian LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($jabatan_filter) {
    $where_conditions[] = "jabatan = ?";
    $params[] = $jabatan_filter;
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($pendidikan_filter) {
    $where_conditions[] = "pendidikan_terakhir = ?";
    $params[] = $pendidikan_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records
$count_query = "SELECT COUNT(*) FROM dosen $where_clause";
$total_records = fetchCount($count_query, $params);

// Pagination
$pagination = paginate($total_records, $page, 15);

// Get data
$query = "SELECT * FROM dosen $where_clause ORDER BY nama ASC 
          LIMIT {$pagination['records_per_page']} OFFSET {$pagination['offset']}";
$dosen_list = fetchAll($query, $params);

// Get filter options
$jabatan_options = fetchAll("SELECT DISTINCT jabatan FROM dosen WHERE jabatan IS NOT NULL ORDER BY jabatan");
$pendidikan_options = fetchAll("SELECT DISTINCT pendidikan_terakhir FROM dosen WHERE pendidikan_terakhir IS NOT NULL ORDER BY pendidikan_terakhir");

// Get statistics
$stats = [
    'total' => fetchCount("SELECT COUNT(*) FROM dosen"),
    'aktif' => fetchCount("SELECT COUNT(*) FROM dosen WHERE status = 'aktif'"),
    'cuti' => fetchCount("SELECT COUNT(*) FROM dosen WHERE status = 'cuti'"),
    'pensiun' => fetchCount("SELECT COUNT(*) FROM dosen WHERE status = 'pensiun'"),
    's3' => fetchCount("SELECT COUNT(*) FROM dosen WHERE pendidikan_terakhir = 'S3'"),
    's2' => fetchCount("SELECT COUNT(*) FROM dosen WHERE pendidikan_terakhir = 'S2'"),
    's1' => fetchCount("SELECT COUNT(*) FROM dosen WHERE pendidikan_terakhir = 'S1'")
];

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kelola Dosen</h1>
            <p class="mb-0 text-muted">Manajemen data dosen dan tenaga pengajar</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Dosen
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dosen</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dosen Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['aktif']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Bergelar S3</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['s3']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bergelar S2</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['s2']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nama, NIP, email, atau bidang keahlian..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jabatan</label>
                    <select name="jabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                        <?php foreach ($jabatan_options as $jabatan): ?>
                            <option value="<?php echo htmlspecialchars($jabatan['jabatan']); ?>" 
                                    <?php echo $jabatan_filter === $jabatan['jabatan'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jabatan['jabatan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?php echo $status_filter === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="cuti" <?php echo $status_filter === 'cuti' ? 'selected' : ''; ?>>Cuti</option>
                        <option value="pensiun" <?php echo $status_filter === 'pensiun' ? 'selected' : ''; ?>>Pensiun</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pendidikan</label>
                    <select name="pendidikan" class="form-select">
                        <option value="">Semua Pendidikan</option>
                        <?php foreach ($pendidikan_options as $pendidikan): ?>
                            <option value="<?php echo htmlspecialchars($pendidikan['pendidikan_terakhir']); ?>" 
                                    <?php echo $pendidikan_filter === $pendidikan['pendidikan_terakhir'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pendidikan['pendidikan_terakhir']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportData()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Dosen (<?php echo $total_records; ?> total)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($dosen_list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data dosen</h5>
                    <p class="text-muted">
                        <?php if ($search || $jabatan_filter || $status_filter || $pendidikan_filter): ?>
                            Tidak ditemukan dosen dengan kriteria yang dipilih
                        <?php else: ?>
                            Belum ada dosen yang terdaftar dalam sistem
                        <?php endif; ?>
                    </p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Dosen Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Foto</th>
                                <th width="15%">NIP</th>
                                <th width="20%">Nama</th>
                                <th width="15%">Email</th>
                                <th width="15%">Bidang Keahlian</th>
                                <th width="10%">Jabatan</th>
                                <th width="8%">Status</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dosen_list as $index => $dosen): ?>
                                <tr>
                                    <td><?php echo $pagination['start_number'] + $index; ?></td>
                                    <td class="text-center">
                                        <?php if ($dosen['foto']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/dosen/<?php echo $dosen['foto']; ?>" 
                                                 class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($dosen['nip']); ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($dosen['nama']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($dosen['pendidikan_terakhir']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($dosen['email']); ?></td>
                                    <td><?php echo htmlspecialchars($dosen['bidang_keahlian']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($dosen['jabatan']) {
                                                'Guru Besar' => 'danger',
                                                'Lektor Kepala' => 'warning',
                                                'Lektor' => 'info',
                                                'Asisten Ahli' => 'primary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($dosen['jabatan']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($dosen['status']) {
                                                'aktif' => 'success',
                                                'cuti' => 'warning',
                                                'pensiun' => 'secondary',
                                                default => 'light'
                                            };
                                        ?>">
                                            <?php echo ucfirst($dosen['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="viewDosen(<?php echo $dosen['id']; ?>)" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit.php?id=<?php echo $dosen['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $dosen['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Hapus"
                                               onclick="return confirm('Yakin ingin menghapus dosen <?php echo htmlspecialchars($dosen['nama']); ?>?')">
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
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&search=<?php echo urlencode($search); ?>&jabatan=<?php echo urlencode($jabatan_filter); ?>&status=<?php echo urlencode($status_filter); ?>&pendidikan=<?php echo urlencode($pendidikan_filter); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&jabatan=<?php echo urlencode($jabatan_filter); ?>&status=<?php echo urlencode($status_filter); ?>&pendidikan=<?php echo urlencode($pendidikan_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&search=<?php echo urlencode($search); ?>&jabatan=<?php echo urlencode($jabatan_filter); ?>&status=<?php echo urlencode($status_filter); ?>&pendidikan=<?php echo urlencode($pendidikan_filter); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="text-center text-muted">
                        Menampilkan <?php echo $pagination['start_number']; ?> - <?php echo min($pagination['start_number'] + $pagination['records_per_page'] - 1, $total_records); ?>
                        dari <?php echo $total_records; ?> data
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Dosen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    border: 0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<script>
function viewDosen(id) {
    // Show loading
    document.getElementById('modalContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="text-muted mt-2">Memuat data...</p>
        </div>
    `;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('viewModal')).show();
    
    // Fetch data
    fetch(`view.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('modalContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('modalContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memuat data dosen
                </div>
            `;
        });
}

function exportData() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = 'export.php?' + params.toString();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);

// Search on enter
document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// Highlight search terms
document.addEventListener('DOMContentLoaded', function() {
    const searchTerm = '<?php echo addslashes($search); ?>';
    if (searchTerm) {
        const cells = document.querySelectorAll('td');
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                cell.innerHTML = cell.innerHTML.replace(
                    new RegExp(searchTerm, 'gi'),
                    '<mark>$&</mark>'
                );
            }
        });
    }
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
