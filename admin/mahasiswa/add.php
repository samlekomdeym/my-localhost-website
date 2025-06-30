<?php 
define('SECURE_ACCESS', true);
require_once '../../config/config.php'; 
require_once '../../config/database.php'; 
require_once '../../config/session.php'; 
require_once '../../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php');
    exit();
}

$page_title = "Tambah Mahasiswa";
$current_page = 'mahasiswa';
$additional_css = array('admin.css');
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = sanitize($_POST['nim']);
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon']);
    $alamat = sanitize($_POST['alamat']);
    $tanggal_lahir = sanitize($_POST['tanggal_lahir']);
    $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
    $program_studi = sanitize($_POST['program_studi']);
    $angkatan = sanitize($_POST['angkatan']);
    $status = sanitize($_POST['status']);
    $tanggal_masuk = sanitize($_POST['tanggal_masuk']);
    
    $errors = [];
    
    // Validation
    if (empty($nim)) $errors[] = 'NIM wajib diisi';
    if (empty($nama)) $errors[] = 'Nama wajib diisi';
    if (empty($email)) $errors[] = 'Email wajib diisi';
    if (!validateEmail($email)) $errors[] = 'Format email tidak valid';
    if (empty($telepon)) $errors[] = 'Telepon wajib diisi';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir wajib diisi';
    if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin wajib dipilih';
    if (empty($program_studi)) $errors[] = 'Program studi wajib dipilih';
    if (empty($angkatan)) $errors[] = 'Angkatan wajib diisi';
    if (empty($status)) $errors[] = 'Status wajib dipilih';
    if (empty($tanggal_masuk)) $errors[] = 'Tanggal masuk wajib diisi';
    
    // Check if NIM already exists
    if (!empty($nim)) {
        $existing = fetchOne("SELECT id FROM mahasiswa WHERE nim = ?", [$nim]);
        if ($existing) {
            $errors[] = 'NIM sudah terdaftar';
        }
    }
    
    // Check if email already exists
    if (!empty($email)) {
        $existing = fetchOne("SELECT id FROM mahasiswa WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email sudah terdaftar';
        }
    }
    
    // Handle photo upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['foto'], 'mahasiswa');
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
            
            // Insert mahasiswa data
            $query = "INSERT INTO mahasiswa (nim, nama, email, telepon, alamat, tanggal_lahir, jenis_kelamin, 
                     program_studi, angkatan, status, tanggal_masuk, foto, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            executeQuery($query, [
                $nim, $nama, $email, $telepon, $alamat, $tanggal_lahir, $jenis_kelamin,
                $program_studi, $angkatan, $status, $tanggal_masuk, $foto
            ]);
            
            $mahasiswa_id = $db->lastInsertId();
            
            // Create user account for mahasiswa
            $username = strtolower(str_replace(' ', '', $nama)) . '_' . substr($nim, -4);
            $password = password_hash($nim, PASSWORD_DEFAULT); // Default password is NIM
            
            $user_query = "INSERT INTO users (username, email, password, role, status, created_at) 
                          VALUES (?, ?, ?, 'mahasiswa', 'aktif', NOW())";
            executeQuery($user_query, [$username, $email, $password]);
            
            $user_id = $db->lastInsertId();
            
            // Update mahasiswa with user_id
            executeQuery("UPDATE mahasiswa SET user_id = ? WHERE id = ?", [$user_id, $mahasiswa_id]);
            
            $db->commit();
            
            logActivity($_SESSION['user_id'], 'Create Mahasiswa', "Created mahasiswa: $nama (NIM: $nim)");
            setFlashMessage('success', "Mahasiswa berhasil ditambahkan. Username: $username, Password: $nim");
            header('Location: index.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            logMessage('ERROR', 'Failed to create mahasiswa: ' . $e->getMessage());
            $errors[] = 'Gagal menambahkan mahasiswa';
        }
    }
    
    if (!empty($errors)) {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

// Get program studi options
$program_studi_options = [
    'Teknik Informatika',
    'Sistem Informasi',
    'Teknik Komputer',
    'Manajemen Informatika',
    'Teknologi Informasi'
];

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tambah Mahasiswa</h1>
            <p class="mb-0 text-muted">Tambah data mahasiswa baru ke sistem</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Mahasiswa</h6>
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
                                <label class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" name="nim" class="form-control" 
                                       value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>" 
                                       required>
                                <div class="invalid-feedback">NIM wajib diisi</div>
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
                                <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select name="program_studi" class="form-select" required>
                                    <option value="">Pilih Program Studi</option>
                                    <?php foreach ($program_studi_options as $prodi): ?>
                                        <option value="<?php echo $prodi; ?>" 
                                                <?php echo (isset($_POST['program_studi']) && $_POST['program_studi'] == $prodi) ? 'selected' : ''; ?>>
                                            <?php echo $prodi; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Program studi wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                                <select name="angkatan" class="form-select" required>
                                    <option value="">Pilih Angkatan</option>
                                    <?php 
                                    $current_year = date('Y');
                                    for ($year = $current_year; $year >= $current_year - 10; $year--): 
                                    ?>
                                        <option value="<?php echo $year; ?>" 
                                                <?php echo (isset($_POST['angkatan']) && $_POST['angkatan'] == $year) ? 'selected' : ''; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <div class="invalid-feedback">Angkatan wajib dipilih</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="cuti" <?php echo (isset($_POST['status']) && $_POST['status'] == 'cuti') ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="lulus" <?php echo (isset($_POST['status']) && $_POST['status'] == 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                                    <option value="dropout" <?php echo (isset($_POST['status']) && $_POST['status'] == 'dropout') ? 'selected' : ''; ?>>Dropout</option>
                                </select>
                                <div class="invalid-feedback">Status wajib dipilih</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_masuk" class="form-control" 
                                       value="<?php echo isset($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d'); ?>" 
                                       required>
                                <div class="invalid-feedback">Tanggal masuk wajib diisi</div>
                            </div>
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
                                    <li>Setelah mahasiswa ditambahkan, sistem akan otomatis membuat akun login</li>
                                    <li>Username akan dibuat otomatis berdasarkan nama dan NIM</li>
                                    <li>Password default adalah NIM mahasiswa</li>
                                    <li>Mahasiswa dapat mengubah password setelah login pertama kali</li>
                                    <li>Pastikan NIM dan email belum terdaftar dalam sistem</li>
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

// NIM validation
document.querySelector('input[name="nim"]').addEventListener('input', function(e) {
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
    const nim = document.querySelector('input[name="nim"]').value;
    
    if (nama && nim) {
        const username = nama.toLowerCase().replace(/\s+/g, '') + '_' + nim.slice(-4);
        console.log('Generated username:', username);
    }
}

document.querySelector('input[name="nama"]').addEventListener('input', generateUsername);
document.querySelector('input[name="nim"]').addEventListener('input', generateUsername);

// Auto-set angkatan based on NIM
document.querySelector('input[name="nim"]').addEventListener('input', function(e) {
    const nim = e.target.value;
    if (nim.length >= 4) {
        const year = '20' + nim.substring(0, 2);
        const angkatanSelect = document.querySelector('select[name="angkatan"]');
        if (angkatanSelect.querySelector(`option[value="${year}"]`)) {
            angkatanSelect.value = year;
        }
    }
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
