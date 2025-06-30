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

$page_title = 'Hapus Dosen';
$current_page = 'dosen';

// Get dosen ID
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    setFlashMessage('danger', 'ID dosen tidak valid');
    header('Location: index.php');
    exit();
}

// Get dosen data
try {
    $dosen = fetchOne("SELECT * FROM dosen WHERE id = ?", [$id]);
    if (!$dosen) {
        setFlashMessage('danger', 'Data dosen tidak ditemukan');
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch dosen: ' . $e->getMessage());
    setFlashMessage('danger', 'Terjadi kesalahan sistem');
    header('Location: index.php');
    exit();
}

// Check related data
$related_data = [];
try {
    // Check jadwal mengajar
    $jadwal_count = fetchCount("SELECT COUNT(*) FROM jadwal WHERE dosen_id = ?", [$id]);
    if ($jadwal_count > 0) {
        $related_data[] = "Jadwal mengajar ($jadwal_count)";
    }
    
    // Check mata kuliah yang diampu
    $matkul_count = fetchCount("SELECT COUNT(*) FROM mata_kuliah WHERE dosen_pengampu = ?", [$id]);
    if ($matkul_count > 0) {
        $related_data[] = "Mata kuliah yang diampu ($matkul_count)";
    }
    
    // Check nilai yang diberikan
    $nilai_count = fetchCount("SELECT COUNT(*) FROM nilai n 
                              JOIN jadwal j ON n.jadwal_id = j.id 
                              WHERE j.dosen_id = ?", [$id]);
    if ($nilai_count > 0) {
        $related_data[] = "Nilai mahasiswa ($nilai_count)";
    }
    
    // Check absensi
    $absensi_count = fetchCount("SELECT COUNT(*) FROM absensi a 
                                JOIN jadwal j ON a.jadwal_id = j.id 
                                WHERE j.dosen_id = ?", [$id]);
    if ($absensi_count > 0) {
        $related_data[] = "Data absensi ($absensi_count)";
    }
    
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to check related data: ' . $e->getMessage());
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db = getDB();
        $db->beginTransaction();
        
        // Delete related data first
        if ($nilai_count > 0) {
            executeQuery("DELETE n FROM nilai n 
                         JOIN jadwal j ON n.jadwal_id = j.id 
                         WHERE j.dosen_id = ?", [$id]);
        }
        
        if ($absensi_count > 0) {
            executeQuery("DELETE a FROM absensi a 
                         JOIN jadwal j ON a.jadwal_id = j.id 
                         WHERE j.dosen_id = ?", [$id]);
        }
        
        if ($jadwal_count > 0) {
            executeQuery("DELETE FROM jadwal WHERE dosen_id = ?", [$id]);
        }
        
        if ($matkul_count > 0) {
            executeQuery("UPDATE mata_kuliah SET dosen_pengampu = NULL WHERE dosen_pengampu = ?", [$id]);
        }
        
        // Delete user account if exists
        if ($dosen['user_id']) {
            executeQuery("DELETE FROM users WHERE id = ?", [$dosen['user_id']]);
        }
        
        // Delete photo if exists
        if ($dosen['foto'] && file_exists("../../assets/uploads/dosen/{$dosen['foto']}")) {
            unlink("../../assets/uploads/dosen/{$dosen['foto']}");
        }
        
        // Delete dosen record
        executeQuery("DELETE FROM dosen WHERE id = ?", [$id]);
        
        $db->commit();
        
        logActivity($_SESSION['user_id'], 'Delete Dosen', "Deleted dosen: {$dosen['nama']} (NIP: {$dosen['nip']})");
        setFlashMessage('success', 'Data dosen berhasil dihapus');
        header('Location: index.php');
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        logMessage('ERROR', 'Failed to delete dosen: ' . $e->getMessage());
        setFlashMessage('danger', 'Gagal menghapus data dosen');
    }
}

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Hapus Dosen</h1>
            <p class="mb-0 text-muted">Konfirmasi penghapusan data dosen</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow border-left-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Peringatan Penghapusan Data
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>Perhatian!</strong> Tindakan ini akan menghapus data dosen secara permanen dan tidak dapat dibatalkan.
                    </div>

                    <!-- Dosen Information -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if ($dosen['foto']): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/dosen/<?php echo $dosen['foto']; ?>" 
                                     class="img-fluid rounded" alt="Foto Dosen">
                            <?php else: ?>
                                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-user fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5 class="text-danger mb-3">Data yang akan dihapus:</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>NIP</strong></td>
                                    <td>: <?php echo htmlspecialchars($dosen['nip']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>: <?php echo htmlspecialchars($dosen['nama']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>: <?php echo htmlspecialchars($dosen['email']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td>: <?php echo htmlspecialchars($dosen['jabatan']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Bidang Keahlian</strong></td>
                                    <td>: <?php echo htmlspecialchars($dosen['bidang_keahlian']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>: 
                                        <span class="badge bg-<?php echo $dosen['status'] === 'aktif' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($dosen['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Related Data Warning -->
                    <?php if (!empty($related_data)): ?>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Data Terkait yang Akan Dihapus:</h6>
                            <ul class="mb-0">
                                <?php foreach ($related_data as $data): ?>
                                    <li><?php echo $data; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <small class="text-muted mt-2 d-block">
                                Semua data terkait akan dihapus secara otomatis untuk menjaga konsistensi database.
                            </small>
                        </div>
                    <?php endif; ?>

                    <!-- Confirmation Form -->
                    <form method="POST" class="mt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_checkbox" required>
                            <label class="form-check-label" for="confirm_checkbox">
                                Saya memahami bahwa tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait
                            </label>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Ketik "<strong>HAPUS</strong>" untuk konfirmasi:</label>
                            <input type="text" class="form-control" id="confirm_text" placeholder="Ketik HAPUS" required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" name="confirm_delete" class="btn btn-danger" id="delete_btn" disabled>
                                <i class="fas fa-trash me-2"></i>Hapus Dosen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow border-left-info">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Informasi Penghapusan
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-info">Yang akan dihapus:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-danger me-2"></i>Data pribadi dosen</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Akun login (jika ada)</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Foto profil</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Jadwal mengajar</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Data nilai yang diberikan</li>
                        <li><i class="fas fa-check text-danger me-2"></i>Data absensi</li>
                    </ul>

                    <h6 class="text-warning mt-3">Yang tidak akan dihapus:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info text-warning me-2"></i>Mata kuliah (hanya dosen pengampu dihapus)</li>
                        <li><i class="fas fa-info text-warning me-2"></i>Log aktivitas sistem</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>Tips:</strong> Jika dosen hanya tidak aktif sementara, 
                            pertimbangkan untuk mengubah status menjadi "cuti" daripada menghapus data.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.card {
    border: 0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.form-check-input:checked {
    background-color: #e74a3b;
    border-color: #e74a3b;
}

.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheckbox = document.getElementById('confirm_checkbox');
    const confirmText = document.getElementById('confirm_text');
    const deleteBtn = document.getElementById('delete_btn');

    function checkFormValidity() {
        const isChecked = confirmCheckbox.checked;
        const isTextCorrect = confirmText.value.toUpperCase() === 'HAPUS';
        
        deleteBtn.disabled = !(isChecked && isTextCorrect);
        
        if (isChecked && isTextCorrect) {
            deleteBtn.classList.remove('btn-outline-danger');
            deleteBtn.classList.add('btn-danger');
        } else {
            deleteBtn.classList.remove('btn-danger');
            deleteBtn.classList.add('btn-outline-danger');
        }
    }

    confirmCheckbox.addEventListener('change', checkFormValidity);
    confirmText.addEventListener('input', checkFormValidity);

    // Form submission confirmation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda benar-benar yakin ingin menghapus data dosen ini? Tindakan ini tidak dapat dibatalkan!')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
        deleteBtn.disabled = true;
    });
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
