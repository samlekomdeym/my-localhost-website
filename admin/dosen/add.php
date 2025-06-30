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

$page_title = 'Tambah Dosen';
$current_page = 'dosen';

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
    
    // Check if NIP already exists
    if (!empty($nip)) {
        $existing = fetchOne("SELECT id FROM dosen WHERE nip = ?", [$nip]);
        if ($existing) {
            $errors[] = 'NIP sudah terdaftar';
        }
    }
    
    // Check if email already exists
    if (!empty($email)) {
        $existing = fetchOne("SELECT id FROM dosen WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email sudah terdaftar';
        }
    }
    
    // Handle photo upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['foto'], 'dosen');
        if ($upload_result['success']) {
            $foto = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->beginTransaction();
            
            // Insert dosen data
            $query = "INSERT INTO dosen (nip, nama, email, telepon, alamat, tanggal_lahir, jenis_kelamin, 
                     pendidikan_terakhir, bidang_keahlian, jabatan, status, tanggal_bergabung, foto, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            executeQuery($query, [
                $nip, $nama, $email, $telepon, $alamat, $tanggal_lahir, $jenis_kelamin,
                $pendidikan_terakhir, $bidang_keahlian, $jabatan, $status, $tanggal_bergabung, $foto
            ]);
            
            $dosen_id = $db->lastInsertId();
            
            // Create user account for dosen
            $username = strtolower(str_replace(' ', '', $nama)) . '_' . substr($nip, -4);
            $password = password_hash($nip, PASSWORD_DEFAULT); // Default password is NIP
            
            $user_query = "INSERT INTO users (username, email, password, role, status, created_at) 
                          VALUES (?, ?, ?, 'dosen', 'aktif', NOW())";
            executeQuery($user_query, [$username, $email, $password]);
            
            $user_id = $db->lastInsertId();
            
            // Update dosen with user_id
            executeQuery("UPDATE dosen SET user_id = ? WHERE id = ?", [$user_id, $dosen_id]);
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'Create Dosen', "Created dosen: $nama (NIP: $nip)");
            setFlashMessage('success', "Dosen berhasil ditambahkan. Username: $username, Password: $nip");
            header('Location: index.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            logMessage('ERROR', 'Failed to create dosen: ' . $e->getMessage());
            $errors[] = 'Gagal menambahkan dosen';
        }
    }
    
    if (!empty($errors)) {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tambah Dosen</h1>
            <p class="mb-0 text-muted">Tambah data dosen baru ke sistem</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Dosen</h6>
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
                                       value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">NIP wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" 
                                       value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Nama wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Email valid wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="telepon" class="form-control" 
                                       value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Telepon wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" 
                                       value="<?php echo isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Tanggal lahir wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                                <div class="invalid-feedback">Jenis kelamin wajib dipilih</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
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
                                    <option value="S1" <?php echo (isset($_POST['pendidikan_terakhir']) && $_POST['pendidikan_terakhir'] == 'S1') ? 'selected' : ''; ?>>S1</option>
                                    <option value="S2" <?php echo (isset($_POST['pendidikan_terakhir']) && $_POST['pendidikan_terakhir'] == 'S2') ? 'selected' : ''; ?>>S2</option>
                                    <option value="S3" <?php echo (isset($_POST['pendidikan_terakhir']) && $_POST['pendidikan_terakhir'] == 'S3') ? 'selected' : ''; ?>>S3</option>
                                </select>
                                <div class="invalid-feedback">Pendidikan terakhir wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bidang Keahlian <span class="text-danger">*</span></label>
                                <input type="text" name="bidang_keahlian" class="form-control" 
                                       value="<?php echo isset($_POST['bidang_keahlian']) ? htmlspecialchars($_POST['bidang_keahlian']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">Bidang keahlian wajib diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan" class="form-select" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Asisten Ahli" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Asisten Ahli') ? 'selected' : ''; ?>>Asisten Ahli</option>
                                    <option value="Lektor" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Lektor') ? 'selected' : ''; ?>>Lektor</option>
                                    <option value="Lektor Kepala" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Lektor Kepala') ? 'selected' : ''; ?>>Lektor Kepala</option>
                                    <option value="Guru Besar" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Guru Besar') ? 'selected' : ''; ?>>Guru Besar</option>
                                </select>
                                <div class="invalid-feedback">Jabatan wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="cuti" <?php echo (isset($_POST['status']) && $_POST['status'] == 'cuti') ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="pensiun" <?php echo (isset($_POST['status']) && $_POST['status'] == 'pensiun') ? 'selected' : ''; ?>>Pensiun</option>
                                </select>
                                <div class="invalid-feedback">Status wajib dipilih</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_bergabung" class="form-control" 
                                   value="<?php echo isset($_POST['tanggal_bergabung']) ? $_POST['tanggal_bergabung'] : date('Y-m-d'); ?>" 
                                   required>
                            <div class="invalid-feedback">Tanggal bergabung wajib diisi</div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Informasi</div>
                            <div class="text-gray-800">
                                <ul class="mb-0">
                                    <li>Setelah dosen ditambahkan, sistem akan otomatis membuat akun login</li>
                                    <li>Username akan dibuat otomatis berdasarkan nama dan NIP</li>
                                    <li>Password default adalah NIP dosen</li>
                                    <li>Dosen dapat mengubah password setelah login pertama kali</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

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

// Auto-generate username preview
function generateUsername() {
    const nama = document.querySelector('input[name="nama"]').value;
    const nip = document.querySelector('input[name="nip"]').value;
    
    if (nama && nip) {
        const username = nama.toLowerCase().replace(/\s+/g, '') + '_' + nip.slice(-4);
        console.log('Generated username:', username);
    }
}

document.querySelector('input[name="nama"]').addEventListener('input', generateUsername);
document.querySelector('input[name="nip"]').addEventListener('input', generateUsername);
</script>

<?php include '../../includes/admin_footer.php'; ?>
