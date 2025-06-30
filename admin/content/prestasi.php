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

$page_title = 'Kelola Prestasi';
$current_page = 'content_prestasi';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $judul = sanitize($_POST['judul']);
                $deskripsi = sanitize($_POST['deskripsi']);
                $kategori = sanitize($_POST['kategori']);
                $tingkat = sanitize($_POST['tingkat']);
                $tahun = (int)$_POST['tahun'];
                $penerima = sanitize($_POST['penerima']);
                $pemberi = sanitize($_POST['pemberi']);
                
                $errors = [];
                if (empty($judul)) $errors[] = 'Judul prestasi wajib diisi';
                if (empty($deskripsi)) $errors[] = 'Deskripsi wajib diisi';
                if (empty($kategori)) $errors[] = 'Kategori wajib dipilih';
                if (empty($tingkat)) $errors[] = 'Tingkat wajib dipilih';
                if ($tahun < 1900 || $tahun > date('Y')) $errors[] = 'Tahun tidak valid';
                
                if (empty($errors)) {
                    try {
                        $query = "INSERT INTO prestasi (judul, deskripsi, kategori, tingkat, tahun, penerima, pemberi, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                        executeQuery($query, [$judul, $deskripsi, $kategori, $tingkat, $tahun, $penerima, $pemberi]);
                        
                        logActivity($_SESSION['user_id'], 'Create Prestasi', "Created prestasi: $judul");
                        setFlashMessage('success', 'Prestasi berhasil ditambahkan');
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    } catch (Exception $e) {
                        logMessage('ERROR', 'Failed to create prestasi: ' . $e->getMessage());
                        $errors[] = 'Gagal menambahkan prestasi';
                    }
                }
                
                if (!empty($errors)) {
                    setFlashMessage('danger', implode('<br>', $errors));
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $judul = sanitize($_POST['judul']);
                $deskripsi = sanitize($_POST['deskripsi']);
                $kategori = sanitize($_POST['kategori']);
                $tingkat = sanitize($_POST['tingkat']);
                $tahun = (int)$_POST['tahun'];
                $penerima = sanitize($_POST['penerima']);
                $pemberi = sanitize($_POST['pemberi']);
                
                $errors = [];
                if (empty($judul)) $errors[] = 'Judul prestasi wajib diisi';
                if (empty($deskripsi)) $errors[] = 'Deskripsi wajib diisi';
                if (empty($kategori)) $errors[] = 'Kategori wajib dipilih';
                if (empty($tingkat)) $errors[] = 'Tingkat wajib dipilih';
                if ($tahun < 1900 || $tahun > date('Y')) $errors[] = 'Tahun tidak valid';
                
                if (empty($errors)) {
                    try {
                        $query = "UPDATE prestasi SET judul = ?, deskripsi = ?, kategori = ?, tingkat = ?, 
                                 tahun = ?, penerima = ?, pemberi = ?, updated_at = NOW() WHERE id = ?";
                        executeQuery($query, [$judul, $deskripsi, $kategori, $tingkat, $tahun, $penerima, $pemberi, $id]);
                        
                        logActivity($_SESSION['user_id'], 'Update Prestasi', "Updated prestasi: $judul");
                        setFlashMessage('success', 'Prestasi berhasil diperbarui');
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    } catch (Exception $e) {
                        logMessage('ERROR', 'Failed to update prestasi: ' . $e->getMessage());
                        $errors[] = 'Gagal memperbarui prestasi';
                    }
                }
                
                if (!empty($errors)) {
                    setFlashMessage('danger', implode('<br>', $errors));
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                try {
                    $prestasi = fetchOne("SELECT judul FROM prestasi WHERE id = ?", [$id]);
                    if ($prestasi) {
                        executeQuery("DELETE FROM prestasi WHERE id = ?", [$id]);
                        logActivity($_SESSION['user_id'], 'Delete Prestasi', "Deleted prestasi: {$prestasi['judul']}");
                        setFlashMessage('success', 'Prestasi berhasil dihapus');
                    }
                } catch (Exception $e) {
                    logMessage('ERROR', 'Failed to delete prestasi: ' . $e->getMessage());
                    setFlashMessage('danger', 'Gagal menghapus prestasi');
                }
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
                break;
        }
    }
}

// Get filter parameters
$kategori_filter = sanitize($_GET['kategori'] ?? '');
$tingkat_filter = sanitize($_GET['tingkat'] ?? '');
$tahun_filter = sanitize($_GET['tahun'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);

// Build query
$where_conditions = [];
$params = [];

if ($kategori_filter) {
    $where_conditions[] = "kategori = ?";
    $params[] = $kategori_filter;
}

if ($tingkat_filter) {
    $where_conditions[] = "tingkat = ?";
    $params[] = $tingkat_filter;
}

if ($tahun_filter) {
    $where_conditions[] = "tahun = ?";
    $params[] = $tahun_filter;
}

if ($search) {
    $where_conditions[] = "(judul LIKE ? OR deskripsi LIKE ? OR penerima LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records
$count_query = "SELECT COUNT(*) FROM prestasi $where_clause";
$total_records = fetchCount($count_query, $params);

// Pagination
$pagination = paginate($total_records, $page, 10);

// Get data
$query = "SELECT * FROM prestasi $where_clause ORDER BY tahun DESC, created_at DESC 
          LIMIT {$pagination['records_per_page']} OFFSET {$pagination['offset']}";
$prestasi_list = fetchAll($query, $params);

// Get years for filter
$years = fetchAll("SELECT DISTINCT tahun FROM prestasi ORDER BY tahun DESC");

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kelola Prestasi</h1>
            <p class="mb-0 text-muted">Kelola data prestasi dan pencapaian kampus</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-trophy me-2"></i>Tambah Prestasi
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Prestasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo fetchCount("SELECT COUNT(*) FROM prestasi"); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Prestasi Tahun Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo fetchCount("SELECT COUNT(*) FROM prestasi WHERE tahun = ?", [date('Y')]); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Prestasi Internasional</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo fetchCount("SELECT COUNT(*) FROM prestasi WHERE tingkat = 'Internasional'"); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Prestasi Nasional</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo fetchCount("SELECT COUNT(*) FROM prestasi WHERE tingkat = 'Nasional'"); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
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
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua</option>
                        <option value="Akademik" <?php echo $kategori_filter === 'Akademik' ? 'selected' : ''; ?>>Akademik</option>
                        <option value="Olahraga" <?php echo $kategori_filter === 'Olahraga' ? 'selected' : ''; ?>>Olahraga</option>
                        <option value="Seni" <?php echo $kategori_filter === 'Seni' ? 'selected' : ''; ?>>Seni</option>
                        <option value="Teknologi" <?php echo $kategori_filter === 'Teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="Penelitian" <?php echo $kategori_filter === 'Penelitian' ? 'selected' : ''; ?>>Penelitian</option>
                        <option value="Lainnya" <?php echo $kategori_filter === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tingkat</label>
                    <select name="tingkat" class="form-select">
                        <option value="">Semua</option>
                        <option value="Lokal" <?php echo $tingkat_filter === 'Lokal' ? 'selected' : ''; ?>>Lokal</option>
                        <option value="Regional" <?php echo $tingkat_filter === 'Regional' ? 'selected' : ''; ?>>Regional</option>
                        <option value="Nasional" <?php echo $tingkat_filter === 'Nasional' ? 'selected' : ''; ?>>Nasional</option>
                        <option value="Internasional" <?php echo $tingkat_filter === 'Internasional' ? 'selected' : ''; ?>>Internasional</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year['tahun']; ?>" <?php echo $tahun_filter == $year['tahun'] ? 'selected' : ''; ?>>
                                <?php echo $year['tahun']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, deskripsi, atau penerima..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> Filter
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
                Daftar Prestasi (<?php echo $total_records; ?> total)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($prestasi_list)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada data prestasi</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Judul</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tingkat</th>
                                <th width="8%">Tahun</th>
                                <th width="15%">Penerima</th>
                                <th width="12%">Pemberi</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prestasi_list as $index => $prestasi): ?>
                                <tr>
                                    <td><?php echo $pagination['start_number'] + $index; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prestasi['judul']); ?></strong>
                                        <br><small class="text-muted"><?php echo truncateText($prestasi['deskripsi'], 60); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($prestasi['kategori']) {
                                                'Akademik' => 'primary',
                                                'Olahraga' => 'success',
                                                'Seni' => 'warning',
                                                'Teknologi' => 'info',
                                                'Penelitian' => 'secondary',
                                                default => 'light'
                                            };
                                        ?>">
                                            <?php echo $prestasi['kategori']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($prestasi['tingkat']) {
                                                'Internasional' => 'danger',
                                                'Nasional' => 'warning',
                                                'Regional' => 'info',
                                                'Lokal' => 'secondary',
                                                default => 'light'
                                            };
                                        ?>">
                                            <?php echo $prestasi['tingkat']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $prestasi['tahun']; ?></td>
                                    <td><?php echo htmlspecialchars($prestasi['penerima']); ?></td>
                                    <td><?php echo htmlspecialchars($prestasi['pemberi']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editPrestasi(<?php echo htmlspecialchars(json_encode($prestasi)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePrestasi(<?php echo $prestasi['id']; ?>, '<?php echo htmlspecialchars($prestasi['judul']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                                    <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>&tahun=<?php echo urlencode($tahun_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>&tahun=<?php echo urlencode($tahun_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&tingkat=<?php echo urlencode($tingkat_filter); ?>&tahun=<?php echo urlencode($tahun_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Prestasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul Prestasi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" class="form-control" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Olahraga">Olahraga</option>
                                <option value="Seni">Seni</option>
                                <option value="Teknologi">Teknologi</option>
                                <option value="Penelitian">Penelitian</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select name="tingkat" class="form-select" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="Lokal">Lokal</option>
                                <option value="Regional">Regional</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Internasional">Internasional</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerima</label>
                            <input type="text" name="penerima" class="form-control" placeholder="Nama penerima prestasi">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pemberi</label>
                            <input type="text" name="pemberi" class="form-control" placeholder="Institusi/organisasi pemberi">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Prestasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul Prestasi <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="edit_judul" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="edit_tahun" class="form-control" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="edit_kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Olahraga">Olahraga</option>
                                <option value="Seni">Seni</option>
                                <option value="Teknologi">Teknologi</option>
                                <option value="Penelitian">Penelitian</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select name="tingkat" id="edit_tingkat" class="form-select" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="Lokal">Lokal</option>
                                <option value="Regional">Regional</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Internasional">Internasional</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerima</label>
                            <input type="text" name="penerima" id="edit_penerima" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pemberi</label>
                            <input type="text" name="pemberi" id="edit_pemberi" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus prestasi "<span id="delete_title"></span>"?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPrestasi(prestasi) {
    document.getElementById('edit_id').value = prestasi.id;
    document.getElementById('edit_judul').value = prestasi.judul;
    document.getElementById('edit_deskripsi').value = prestasi.deskripsi;
    document.getElementById('edit_kategori').value = prestasi.kategori;
    document.getElementById('edit_tingkat').value = prestasi.tingkat;
    document.getElementById('edit_tahun').value = prestasi.tahun;
    document.getElementById('edit_penerima').value = prestasi.penerima;
    document.getElementById('edit_pemberi').value = prestasi.pemberi;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deletePrestasi(id, title) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_title').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../../includes/admin_footer.php'; ?>
