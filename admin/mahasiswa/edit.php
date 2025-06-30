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
$error = '';
$success = '';
if (!$id) {
    header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?error=ID tidak valid');
    exit();
}

try {
    $db = getDB();

    // Get mahasiswa data
    $stmt = $db->prepare("
        SELECT m.*, u.username, u.email, u.status         
        FROM mahasiswa m         
        JOIN users u ON m.user_id = u.id         
        WHERE m.user_id = ?    
    ");
    $stmt->execute(array($id));
    $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mahasiswa) {
        header('Location: ' . SITE_URL . '/admin/mahasiswa/index.php?error=Data mahasiswa tidak ditemukan');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_lengkap = sanitize(isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : '');
        $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
        $tempat_lahir = sanitize(isset($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : '');
        $tanggal_lahir = isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : '';
        $jenis_kelamin = isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '';
        $alamat = sanitize(isset($_POST['alamat']) ? $_POST['alamat'] : '');
        $no_telepon = sanitize(isset($_POST['no_telepon']) ? $_POST['no_telepon'] : '');
        $program_studi = sanitize(isset($_POST['program_studi']) ? $_POST['program_studi'] : '');
        $tahun_masuk = isset($_POST['tahun_masuk']) ? $_POST['tahun_masuk'] : '';
        $status = sanitize(isset($_POST['status']) ? $_POST['status'] : '');

        // Validation
        if (empty($nama_lengkap) || empty($email) || empty($program_studi)) {
            $error = 'Field yang wajib harus diisi';
        } elseif (!isValidEmail($email)) {
            $error = 'Format email tidak valid';
        } else {
            // Check if email is already used by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute(array($email, $mahasiswa['user_id']));

            if ($stmt->fetch()) {
                $error = 'Email sudah digunakan oleh user lain';
            } else {
                try {
                    $db->beginTransaction();

                    // Update user data
                    $stmt = $db->prepare("UPDATE users SET email = ?, status = ? WHERE id = ?");
                    $stmt->execute(array($email, $status, $mahasiswa['user_id']));

                    // Update mahasiswa data
                    $stmt = $db->prepare("
                        UPDATE mahasiswa SET                         
                        nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?,                         
                        jenis_kelamin = ?, alamat = ?, no_telepon = ?,                         
                        program_studi = ?, tahun_masuk = ?                         
                        WHERE user_id = ?                    
                    ");
                    $stmt->execute(array(
                        $nama_lengkap, $tempat_lahir, $tanggal_lahir,
                        $jenis_kelamin, $alamat, $no_telepon,
                        $program_studi, $tahun_masuk, $mahasiswa['user_id']
                    ));

                    $db->commit();

                    // Log activity
                    logActivity(getUserId(), 'edit_mahasiswa', "Updated mahasiswa: " . $nama_lengkap . " (NIM: " . $mahasiswa['nim'] . ")");

                    $success = 'Data mahasiswa berhasil diupdate';

                    // Refresh data
                    $stmt = $db->prepare("
                        SELECT m.*, u.username, u.email, u.status                         
                        FROM mahasiswa m                         
                        JOIN users u ON m.user_id = u.id                         
                        WHERE m.user_id = ?                    
                    ");
                    $stmt->execute(array($id));
                    $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

                } catch (Exception $e) {
                    $db->rollBack();
                    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
                    error_log("Edit mahasiswa error: " . $e->getMessage());
                }
            }
        }
    }

} catch (Exception $e) {
    error_log("Edit mahasiswa error: " . $e->getMessage());
    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
}

$page_title = "Edit Mahasiswa";
$additional_css = array('admin.css');
include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="fas fa-user-edit me-2"></i>Edit Mahasiswa</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/admin/mahasiswa/">Mahasiswa</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Data Mahasiswa</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="editMahasiswaForm">
                    <!-- Account Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user-cog me-2"></i>Informasi Akun
                            </h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" readonly
                                    value="<?php echo htmlspecialchars($mahasiswa['username']); ?>">
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="nim" name="nim" readonly
                                    value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>">
                            <small class="text-muted">NIM tidak dapat diubah</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                    value="<?php echo htmlspecialchars($mahasiswa['email']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status Akun</label>
                            <select class="form-control" id="status" name="status">
                                <option value="active" <?php echo ($mahasiswa['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo ($mahasiswa['status'] == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                <option value="pending" <?php echo ($mahasiswa['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2"></i>Informasi Pribadi
                            </h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required
                                    value="<?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir"
                                    value="<?php echo htmlspecialchars($mahasiswa['tempat_lahir']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                    value="<?php echo htmlspecialchars($mahasiswa['tanggal_lahir']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-control" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" <?php echo ($mahasiswa['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo ($mahasiswa['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_telepon" class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon" name="no_telepon"
                                    value="<?php echo htmlspecialchars($mahasiswa['no_telepon']); ?>"
                                    placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($mahasiswa['alamat']); ?></textarea>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-graduation-cap me-2"></i>Informasi Akademik
                            </h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="program_studi" class="form-label">Program Studi *</label>
                            <select class="form-control" id="program_studi" name="program_studi" required>
                                <option value="">Pilih Program Studi</option>
                                <option value="Teknik Informatika" <?php echo ($mahasiswa['program_studi'] == 'Teknik Informatika') ? 'selected' : ''; ?>>Teknik Informatika</option>
                                <option value="Sistem Informasi" <?php echo ($mahasiswa['program_studi'] == 'Sistem Informasi') ? 'selected' : ''; ?>>Sistem Informasi</option>
                                <option value="Teknik Komputer" <?php echo ($mahasiswa['program_studi'] == 'Teknik Komputer') ? 'selected' : ''; ?>>Teknik Komputer</option>
                                <option value="Manajemen Informatika" <?php echo ($mahasiswa['program_studi'] == 'Manajemen Informatika') ? 'selected' : ''; ?>>Manajemen Informatika</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                            <select class="form-control" id="tahun_masuk" name="tahun_masuk">
                                <option value="">Pilih Tahun Masuk</option>
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                                    $selected = ($mahasiswa['tahun_masuk'] == $year) ? 'selected' : '';
                                    echo "<option value=\"{$year}\" {$selected}>{$year}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo SITE_URL; ?>/admin/mahasiswa/" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('editMahasiswaForm').addEventListener('submit', function(e) {
    const phoneField = document.getElementById('no_telepon');
    const phone = phoneField.value;
    
    // Validate phone number if provided
    if (phone && !phone.match(/^08[0-9]{8,11}$/)) {
        e.preventDefault();
        if (typeof window.CampusApp !== 'undefined' && typeof window.CampusApp.showAlert === 'function') {
            window.CampusApp.showAlert('Format nomor telepon tidak valid! Gunakan format 08xxxxxxxxxx', 'danger');
        } else {
            alert('Format nomor telepon tidak valid! Gunakan format 08xxxxxxxxxx');
        }
        return false;
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
