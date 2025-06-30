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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?error=ID tidak valid');
    exit();
}

try {
    $db = getDB();

    // Get mahasiswa data first
    $stmt = $db->prepare("
        SELECT m.*, u.username, u.email         
        FROM mahasiswa m         
        JOIN users u ON m.user_id = u.id         
        WHERE m.id = ?    
    ");
    $stmt->execute(array($id));
    $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mahasiswa) {
        header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?error=Data mahasiswa tidak ditemukan');
        exit();
    }

    // Check if mahasiswa has related data
    $hasKRS = fetchCount("SELECT COUNT(*) FROM krs WHERE mahasiswa_id = ?", array($id));
    $hasNilai = fetchCount("SELECT COUNT(n.id) FROM nilai n JOIN krs k ON n.krs_id = k.id WHERE k.mahasiswa_id = ?", array($id));
    $hasAbsensi = fetchCount("SELECT COUNT(a.id) FROM absensi a JOIN krs k ON a.krs_id = k.id WHERE k.mahasiswa_id = ?", array($id));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
        $db->beginTransaction();
        
        try {
            // Delete related data first (order matters for foreign keys)
            if ($hasAbsensi > 0) {
                 $db->prepare("DELETE a FROM absensi a JOIN krs k ON a.krs_id = k.id WHERE k.mahasiswa_id = ?")->execute(array($id));
            }
            if ($hasNilai > 0) {
                $db->prepare("DELETE n FROM nilai n JOIN krs k ON n.krs_id = k.id WHERE k.mahasiswa_id = ?")->execute(array($id));
            }

            // Hapus KRS dari mahasiswa ini
            if ($hasKRS > 0) {
                $db->prepare("DELETE FROM krs WHERE mahasiswa_id = ?")->execute(array($id));
            }

            // Delete mahasiswa record
            $db->prepare("DELETE FROM mahasiswa WHERE id = ?")->execute(array($id));

            // Delete user account
            $db->prepare("DELETE FROM users WHERE id = ?")->execute(array($mahasiswa['user_id']));

            $db->commit();

            // Log activity
            logActivity(getUserId(), 'delete_mahasiswa', "Deleted mahasiswa: " . $mahasiswa['nama_lengkap'] . " (NIM: " . $mahasiswa['nim'] . ")");

            header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?success=Data mahasiswa berhasil dihapus');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    error_log("Delete mahasiswa error: " . $e->getMessage());
    header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?error=Terjadi kesalahan sistem: ' . $e->getMessage());
    exit();
}

$page_title = "Hapus Mahasiswa";
$additional_css = array('admin.css');
include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-user-times me-2"></i>Hapus Mahasiswa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/mahasiswa/">Mahasiswa</a></li>
                        <li class="breadcrumb-item active">Hapus</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Penghapusan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan. Semua data terkait mahasiswa akan dihapus secara permanen.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Data Mahasiswa yang akan dihapus:</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama</strong></td>
                                <td>: <?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM</strong></td>
                                <td>: <?php echo htmlspecialchars($mahasiswa['nim']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: <?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?php echo htmlspecialchars($mahasiswa['program_studi']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tahun Masuk</strong></td>
                                <td>: <?php echo htmlspecialchars($mahasiswa['tahun_masuk']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6>Data terkait yang akan dihapus:</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                KRS (Kartu Rencana Studi)
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($hasKRS); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Nilai
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($hasNilai); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Absensi
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($hasAbsensi); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Akun User
                                <span class="badge bg-danger rounded-pill">1</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <hr>

                <form method="POST" class="text-center">
                    <div class="form-check d-inline-block mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                        <label class="form-check-label" for="confirmCheck">
                            Saya memahami bahwa tindakan ini tidak dapat dibatalkan
                        </label>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="submit" name="confirm_delete" class="btn btn-danger" id="deleteBtn" disabled>
                            <i class="fas fa-trash me-2"></i>Ya, Hapus Data
                        </button>
                        <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirmCheck').addEventListener('change', function() {
    document.getElementById('deleteBtn').disabled = !this.checked;
});
</script>

<?php include '../../includes/footer.php'; ?>
