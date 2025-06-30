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

$page_title = 'Edit Dosen';
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = sanitize($_POST['nip']);
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon']);
    $alamat = sanitize($_POST['alamat']);
    $tanggal_lahir = sanitize($_POST['tanggal_lahir']);
    $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
    $pendidikan_terakhir = sanitize($_POST['pendidikan_terakhir']);
    $bidang_keahlian = sanitize($_POST['bidang_keahlian']);
    $jabatan = sanitize($_POST['jabatan']);
    $status = sanitize($_POST['status']);
    $tanggal_bergabung = sanitize($_POST['tanggal_bergabung']);
    
    $errors = [];
    
    // Validation
    if (empty($nip)) $errors[] = 'NIP wajib diisi';
    if (empty($nama)) $errors[] = 'Nama wajib diisi';
    if (empty($email)) $errors[] = 'Email wajib diisi';
    if (!validateEmail($email)) $errors[] = 'Format email tidak valid';
    if (empty($telepon)) $errors[] = 'Telepon wajib diisi';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir wajib diisi';
    if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin wajib dipilih';
    if (empty($pendidikan_terakhir)) $errors[] = 'Pendidikan terakhir wajib diisi';
    if (empty($bidang_keahlian)) $errors[] = 'Bidang keahlian wajib diisi';
    if (empty($jabatan)) $errors[] = 'Jabatan wajib dipilih';
    if (empty($status)) $errors[] = 'Status wajib dipilih';
    if (empty($tanggal_bergabung)) $errors[] = 'Tanggal bergabung wajib diisi';
    
    // Check if NIP already exists (exclude current record)
    if (!empty($nip) && $nip !== $dosen['nip']) {
        $existing = fetchOne("SELECT id FROM dosen WHERE nip = ? AND id != ?", [$nip, $id]);
        if ($existing) {
            $errors[] = 'NIP sudah terdaftar';
        }
    }
    
    // Check if email already exists (exclude current record)
    if (!empty($email) && $email !== $dosen['email']) {
        $existing = fetchOne("SELECT id FROM dosen WHERE email = ? AND id != ?", [$email, $id]);
        if ($existing) {
            $errors[] = 'Email sudah terdaftar';
        }
    }
    
    // Handle photo upload
    $foto = $dosen['foto']; // Keep existing photo by default
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['foto'], 'dosen');
        if ($upload_result['success']) {
            // Delete old photo
            if ($foto && file_exists("../../assets/uploads/dosen/$foto")) {
                unlink("../../assets/uploads/dosen/$foto");
            }
            $foto = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    // Handle photo deletion
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] === '1') {
        if ($foto && file_exists("../../assets/uploads/dosen/$foto")) {
            unlink("../../assets/uploads/dosen/$foto");
        }
        $foto = '';
    }
    
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->beginTransaction();
            
            // Update dosen data
            $query = "UPDATE dosen SET 
                     nip = ?, nama = ?, email = ?, telepon = ?, alamat = ?, 
                     tanggal_lahir = ?, jenis_kelamin = ?, pendidikan_terakhir = ?, 
                     bidang_keahlian = ?, jabatan = ?, status = ?, tanggal_bergabung = ?, 
                     foto = ?, updated_at = NOW() 
                     WHERE id = ?";
            
            executeQuery($query, [
                $nip, $nama, $email, $telepon, $alamat, $tanggal_lahir, $jenis_kelamin,
                $pendidikan_terakhir, $bidang_keahlian, $jabatan, $status, $tanggal_bergabung, 
                $foto, $id
            ]);
            
            // Update user account if exists
            if ($dosen['user_id']) {
                executeQuery("UPDATE users SET email = ? WHERE id = ?", [$email, $dosen['user_id']]);
            }
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'Update Dosen', "Updated dosen: $nama (NIP: $nip)");
            setFlashMessage('success', 'Data dosen berhasil diperbarui');
            header('Location: index.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            logMessage('ERROR', 'Failed to update dosen: ' . $e->getMessage());
            $errors[] = 'Gagal memperbarui data dosen';
        }
    }
    
    if (!empty($errors)) {
        setFlashMessage('danger', implode('<br>', $errors));
    }
    
    // Update dosen array with submitted data for form repopulation
    $dosen = array_merge($dosen, $_POST);
}

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Dosen</h1>
            <p class="mb-0 text-muted">Perbarui data dosen: <?php echo htmlspecialchars($dosen['nama']); ?></p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Dosen</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Informasi Pribadi
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIP <span class="text-danger">*</span></label>
                                <input type="text" name="nip" class="form-control" 
                                       value="<?php echo htmlspecialchars($dosen['nip']); ?>" required>
                                <div class="invalid-feedback">NIP wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" 
                                       value="<?php echo htmlspecialchars($dosen['nama']); ?>" required>
                                <div class="invalid-feedback">Nama wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($dosen['email']); ?>" required>
                                <div class="invalid-feedback">Email valid wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="telepon" class="form-control" 
                                       value="<?php echo htmlspecialchars($dosen['telepon']); ?>" required>
                                <div class="invalid-feedback">Telepon wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" 
                                       value="<?php echo $dosen['tanggal_lahir']; ?>" required>
                                <div class="invalid-feedback">Tanggal lahir wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" <?php echo ($dosen['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?php echo ($dosen['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                                <div class="invalid-feedback">Jenis kelamin wajib dipilih</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($dosen['alamat']); ?></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- Academic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>Informasi Akademik
                                </h5>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select name="pendidikan_terakhir" class="form-select" required>
                                    <option value="">Pilih Pendidikan</option>
                                    <option value="S1" <?php echo ($dosen['pendidikan_terakhir'] == 'S1') ? 'selected' : ''; ?>>S1</option>
                                    <option value="S2" <?php echo ($dosen['pendidikan_terakhir'] == 'S2') ? 'selected' : ''; ?>>S2</option>
                                    <option value="S3" <?php echo ($dosen['pendidikan_terakhir'] == 'S3') ? 'selected' : ''; ?>>S3</option>
                                </select>
                                <div class="invalid-feedback">Pendidikan terakhir wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bidang Keahlian <span class="text-danger">*</span></label>
                                <input type="text" name="bidang_keahlian" class="form-control" 
                                       value="<?php echo htmlspecialchars($dosen['bidang_keahlian']); ?>" required>
                                <div class="invalid-feedback">Bidang keahlian wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan" class="form-select" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Asisten Ahli" <?php echo ($dosen['jabatan'] == 'Asisten Ahli') ? 'selected' : ''; ?>>Asisten Ahli</option>
                                    <option value="Lektor" <?php echo ($dosen['jabatan'] == 'Lektor') ? 'selected' : ''; ?>>Lektor</option>
                                    <option value="Lektor Kepala" <?php echo ($dosen['jabatan'] == 'Lektor Kepala') ? 'selected' : ''; ?>>Lektor Kepala</option>
                                    <option value="Guru Besar" <?php echo ($dosen['jabatan'] == 'Guru Besar') ? 'selected' : ''; ?>>Guru Besar</option>
                                </select>
                                <div class="invalid-feedback">Jabatan wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?php echo ($dosen['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="cuti" <?php echo ($dosen['status'] == 'cuti') ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="pensiun" <?php echo ($dosen['status'] == 'pensiun') ? 'selected' : ''; ?>>Pensiun</option>
                                </select>
                                <div class="invalid-feedback">Status wajib dipilih</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_bergabung" class="form-control" 
                                   value="<?php echo $dosen['tanggal_bergabung']; ?>" required>
                            <div class="invalid-feedback">Tanggal bergabung wajib diisi</div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Photo Panel -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Foto Profil</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($dosen['foto']): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/uploads/dosen/<?php echo $dosen['foto']; ?>" 
                                 class="img-fluid rounded" alt="Foto Dosen" style="max-height: 300px;">
                        <?php else: ?>
                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="mb-2">
                        <input type="file" name="foto" class="form-control mb-2" accept="image/*">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-upload me-1"></i>Upload Foto
                        </button>
                    </form>
                    
                    <?php if ($dosen['foto']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="delete_photo" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Hapus foto profil?')">
                                <i class="fas fa-trash me-1"></i>Hapus Foto
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <small class="text-muted d-block mt-2">
                        Format: JPG, PNG, GIF<br>
                        Maksimal: 2MB
                    </small>
                </div>
            </div>

            <!-- Account Info -->
            <?php if ($dosen['user_id']): ?>
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Informasi Akun</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $user = fetchOne("SELECT username, status, last_login FROM users WHERE id = ?", [$dosen['user_id']]);
                        if ($user):
                        ?>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Username:</strong></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'aktif' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Login Terakhir:</strong></td>
                                    <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Belum pernah'; ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Akun login belum dibuat</p>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="createUserAccount()">
                                <i class="fas fa-user-plus me-1"></i>Buat Akun
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.form-control:focus,
.form-select:focus {
    border-color: #36b9cc;
    box-shadow: 0 0 0 0.2rem rgba(54, 185, 204, 0.25);
}

.needs-validation .form-control:invalid,
.needs-validation .form-select:invalid {
    border-color: #e74a3b;
}

.needs-validation .form-control:valid,
.needs-validation .form-select:valid {
    border-color: #1cc88a;
}

.card {
    border: 0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// NIP validation
document.querySelector('input[name="nip"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});

// Phone number formatting
document.querySelector('input[name="telepon"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('0')) {
        e.target.value = value;
    } else if (value.startsWith('62')) {
        e.target.value = '0' + value.substring(2);
    }
});

// Create user account function
function createUserAccount() {
    if (confirm('Buat akun login untuk dosen ini?')) {
        // Implementation for creating user account
        alert('Fitur ini akan segera tersedia');
    }
}

// Photo preview
document.querySelector('input[name="foto"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add photo preview functionality here
            console.log('Photo selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
