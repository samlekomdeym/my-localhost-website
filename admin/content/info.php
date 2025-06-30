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

$page_title = 'Kelola Informasi Kampus';
$current_page = 'content_info';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $judul = sanitize($_POST['judul']);
                $konten = $_POST['konten']; // Allow HTML content
                $kategori = sanitize($_POST['kategori']);
                $status = sanitize($_POST['status']);
                
                $errors = [];
                if (empty($judul)) $errors[] = 'Judul wajib diisi';
                if (empty($konten)) $errors[] = 'Konten wajib diisi';
                if (empty($kategori)) $errors[] = 'Kategori wajib dipilih';
                
                // Handle image upload
                $gambar = '';
                if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFile($_FILES['gambar'], 'info');
                    if ($upload_result['success']) {
                        $gambar = $upload_result['filename'];
                    } else {
                        $errors[] = $upload_result['message'];
                    }
                }
                
                if (empty($errors)) {
                    try {
                        $query = "INSERT INTO info_kampus (judul, konten, kategori, gambar, status, created_by, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, NOW())";
                        $stmt = executeQuery($query, [$judul, $konten, $kategori, $gambar, $status, $_SESSION['user_id']]);
                        
                        logActivity($_SESSION['user_id'], 'Create Info', "Created info: $judul");
                        setFlashMessage('success', 'Informasi berhasil ditambahkan');
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    } catch (Exception $e) {
                        logMessage('ERROR', 'Failed to create info: ' . $e->getMessage());
                        $errors[] = 'Gagal menambahkan informasi';
                    }
                }
                
                if (!empty($errors)) {
                    setFlashMessage('danger', implode('<br>', $errors));
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $judul = sanitize($_POST['judul']);
                $konten = $_POST['konten'];
                $kategori = sanitize($_POST['kategori']);
                $status = sanitize($_POST['status']);
                
                $errors = [];
                if (empty($judul)) $errors[] = 'Judul wajib diisi';
                if (empty($konten)) $errors[] = 'Konten wajib diisi';
                if (empty($kategori)) $errors[] = 'Kategori wajib dipilih';
                
                // Get current data
                $current = fetchOne("SELECT * FROM info_kampus WHERE id = ?", [$id]);
                if (!$current) {
                    $errors[] = 'Data tidak ditemukan';
                }
                
                $gambar = $current['gambar'];
                if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFile($_FILES['gambar'], 'info');
                    if ($upload_result['success']) {
                        // Delete old image
                        if ($gambar && file_exists("../../assets/uploads/$gambar")) {
                            unlink("../../assets/uploads/$gambar");
                        }
                        $gambar = $upload_result['filename'];
                    } else {
                        $errors[] = $upload_result['message'];
                    }
                }
                
                if (empty($errors)) {
                    try {
                        $query = "UPDATE info_kampus SET judul = ?, konten = ?, kategori = ?, gambar = ?, 
                                 status = ?, updated_at = NOW() WHERE id = ?";
                        executeQuery($query, [$judul, $konten, $kategori, $gambar, $status, $id]);
                        
                        logActivity($_SESSION['user_id'], 'Update Info', "Updated info: $judul");
                        setFlashMessage('success', 'Informasi berhasil diperbarui');
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    } catch (Exception $e) {
                        logMessage('ERROR', 'Failed to update info: ' . $e->getMessage());
                        $errors[] = 'Gagal memperbarui informasi';
                    }
                }
                
                if (!empty($errors)) {
                    setFlashMessage('danger', implode('<br>', $errors));
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                try {
                    $info = fetchOne("SELECT * FROM info_kampus WHERE id = ?", [$id]);
                    if ($info) {
                        // Delete image file
                        if ($info['gambar'] && file_exists("../../assets/uploads/{$info['gambar']}")) {
                            unlink("../../assets/uploads/{$info['gambar']}");
                        }
                        
                        executeQuery("DELETE FROM info_kampus WHERE id = ?", [$id]);
                        logActivity($_SESSION['user_id'], 'Delete Info', "Deleted info: {$info['judul']}");
                        setFlashMessage('success', 'Informasi berhasil dihapus');
                    }
                } catch (Exception $e) {
                    logMessage('ERROR', 'Failed to delete info: ' . $e->getMessage());
                    setFlashMessage('danger', 'Gagal menghapus informasi');
                }
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
                break;
        }
    }
}

// Get filter parameters
$kategori_filter = sanitize($_GET['kategori'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);

// Build query
$where_conditions = [];
$params = [];

if ($kategori_filter) {
    $where_conditions[] = "kategori = ?";
    $params[] = $kategori_filter;
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(judul LIKE ? OR konten LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records
$count_query = "SELECT COUNT(*) FROM info_kampus $where_clause";
$total_records = fetchCount($count_query, $params);

// Pagination
$pagination = paginate($total_records, $page, 10);

// Get data
$query = "SELECT ik.*, u.username as author 
          FROM info_kampus ik 
          LEFT JOIN users u ON ik.created_by = u.id 
          $where_clause 
          ORDER BY ik.created_at DESC 
          LIMIT {$pagination['records_per_page']} OFFSET {$pagination['offset']}";
$info_list = fetchAll($query, $params);

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kelola Informasi Kampus</h1>
            <p class="mb-0 text-muted">Kelola berita, pengumuman, dan informasi kampus</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-2"></i>Tambah Informasi
        </button>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="pengumuman" <?php echo $kategori_filter === 'pengumuman' ? 'selected' : ''; ?>>Pengumuman</option>
                        <option value="berita" <?php echo $kategori_filter === 'berita' ? 'selected' : ''; ?>>Berita</option>
                        <option value="event" <?php echo $kategori_filter === 'event' ? 'selected' : ''; ?>>Event</option>
                        <option value="akademik" <?php echo $kategori_filter === 'akademik' ? 'selected' : ''; ?>>Akademik</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?php echo $status_filter === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="arsip" <?php echo $status_filter === 'arsip' ? 'selected' : ''; ?>>Arsip</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul atau konten..." value="<?php echo htmlspecialchars($search); ?>">
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
                Daftar Informasi (<?php echo $total_records; ?> total)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($info_list)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada data informasi</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Judul</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Status</th>
                                <th width="15%">Penulis</th>
                                <th width="15%">Tanggal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($info_list as $index => $info): ?>
                                <tr>
                                    <td><?php echo $pagination['start_number'] + $index; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($info['gambar']): ?>
                                                <img src="<?php echo SITE_URL; ?>/assets/uploads/<?php echo $info['gambar']; ?>" 
                                                     class="rounded me-2" width="40" height="40" style="object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($info['judul']); ?></strong>
                                                <br><small class="text-muted"><?php echo truncateText(strip_tags($info['konten']), 50); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($info['kategori']) {
                                                'pengumuman' => 'warning',
                                                'berita' => 'info',
                                                'event' => 'success',
                                                'akademik' => 'primary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($info['kategori']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($info['status']) {
                                                'aktif' => 'success',
                                                'draft' => 'warning',
                                                'arsip' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($info['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($info['author'] ?? 'Unknown'); ?></td>
                                    <td><?php echo formatDate($info['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editInfo(<?php echo htmlspecialchars(json_encode($info)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteInfo(<?php echo $info['id']; ?>, '<?php echo htmlspecialchars($info['judul']); ?>')">
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
                                    <a class="page-link" href="?page=<?php echo $pagination['prev_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&kategori=<?php echo urlencode($kategori_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">
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
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="pengumuman">Pengumuman</option>
                                <option value="berita">Berita</option>
                                <option value="event">Event</option>
                                <option value="akademik">Akademik</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konten <span class="text-danger">*</span></label>
                        <textarea name="konten" class="form-control" rows="8" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="gambar" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF. Max: 2MB</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="draft">Draft</option>
                                <option value="arsip">Arsip</option>
                            </select>
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
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="edit_judul" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="edit_kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="pengumuman">Pengumuman</option>
                                <option value="berita">Berita</option>
                                <option value="event">Event</option>
                                <option value="akademik">Akademik</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konten <span class="text-danger">*</span></label>
                        <textarea name="konten" id="edit_konten" class="form-control" rows="8" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="gambar" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                            <div id="current_image" class="mt-2"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="draft">Draft</option>
                                <option value="arsip">Arsip</option>
                            </select>
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
                    <p>Apakah Anda yakin ingin menghapus informasi "<span id="delete_title"></span>"?</p>
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
function editInfo(info) {
    document.getElementById('edit_id').value = info.id;
    document.getElementById('edit_judul').value = info.judul;
    document.getElementById('edit_kategori').value = info.kategori;
    document.getElementById('edit_konten').value = info.konten;
    document.getElementById('edit_status').value = info.status;
    
    const currentImageDiv = document.getElementById('current_image');
    if (info.gambar) {
        currentImageDiv.innerHTML = `
            <small class="text-muted">Gambar saat ini:</small><br>
            <img src="<?php echo SITE_URL; ?>/assets/uploads/${info.gambar}" class="img-thumbnail" width="100">
        `;
    } else {
        currentImageDiv.innerHTML = '<small class="text-muted">Tidak ada gambar</small>';
    }
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteInfo(id, title) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_title').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../../includes/admin_footer.php'; ?>
