<?php
define('SECURE_ACCESS', true);
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getRole();
    header("Location: " . SITE_URL . "/$role/");
    exit();
}

$error = '';
$success = '';
$form_data = [
    'username' => '',
    'email' => '',
    'role' => 'mahasiswa',
    'nama_lengkap' => '',
    'nim_nidn' => '',
    'program_studi' => '',
    'jabatan_akademik' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    foreach ($form_data as $key => $value) {
        $form_data[$key] = sanitize($_POST[$key] ?? '');
    }
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($form_data['username'])) {
        $error = 'Username harus diisi';
    } elseif (strlen($form_data['username']) < 3) {
        $error = 'Username minimal 3 karakter';
    } elseif (!validateEmail($form_data['email'])) {
        $error = 'Format email tidak valid';
    } elseif (empty($password)) {
        $error = 'Password harus diisi';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif (empty($form_data['nama_lengkap'])) {
        $error = 'Nama lengkap harus diisi';
    } elseif (empty($form_data['nim_nidn'])) {
        $error = ($form_data['role'] === 'mahasiswa' ? 'NIM' : 'NIDN') . ' harus diisi';
    } elseif ($form_data['role'] === 'mahasiswa' && empty($form_data['program_studi'])) {
        $error = 'Program studi harus diisi';
    } elseif ($form_data['role'] === 'dosen' && empty($form_data['jabatan_akademik'])) {
        $error = 'Jabatan akademik harus diisi';
    } else {
        // Check rate limiting
        $client_ip = getClientIP();
        if (!checkRateLimit("register_$client_ip", 3, 300)) {
            $error = 'Terlalu banyak percobaan registrasi. Coba lagi dalam 5 menit.';
        } else {
            try {
                // Check if username exists
                if (usernameExists($form_data['username'])) {
                    $error = 'Username sudah digunakan';
                } elseif (emailExists($form_data['email'])) {
                    $error = 'Email sudah terdaftar';
                } else {
                    // Check if NIM/NIDN exists
                    $check_field = $form_data['role'] === 'mahasiswa' ? 'nim' : 'nidn';
                    $check_table = $form_data['role'];
                    $existing = fetchOne("SELECT id FROM $check_table WHERE $check_field = ?", [$form_data['nim_nidn']]);
                    
                    if ($existing) {
                        $error = ($form_data['role'] === 'mahasiswa' ? 'NIM' : 'NIDN') . ' sudah terdaftar';
                    } else {
                        // Begin transaction
                        beginTransaction();
                        
                        try {
                            // Insert user
                            $user_data = [
                                'username' => $form_data['username'],
                                'email' => $form_data['email'],
                                'password' => hashPassword($password),
                                'role' => $form_data['role'],
                                'status' => 'active',
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            
                            $user_query = "INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                            executeQuery($user_query, array_values($user_data));
                            $user_id = getLastInsertId();
                            
                            // Insert role-specific data
                            if ($form_data['role'] === 'mahasiswa') {
                                $mahasiswa_data = [
                                    'user_id' => $user_id,
                                    'nim' => $form_data['nim_nidn'],
                                    'nama_lengkap' => $form_data['nama_lengkap'],
                                    'program_studi' => $form_data['program_studi'],
                                    'status' => 'aktif',
                                    'created_at' => date('Y-m-d H:i:s')
                                ];
                                
                                $mahasiswa_query = "INSERT INTO mahasiswa (user_id, nim, nama_lengkap, program_studi, status, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                                executeQuery($mahasiswa_query, array_values($mahasiswa_data));
                            } else {
                                $dosen_data = [
                                    'user_id' => $user_id,
                                    'nidn' => $form_data['nim_nidn'],
                                    'nama_lengkap' => $form_data['nama_lengkap'],
                                    'jabatan_akademik' => $form_data['jabatan_akademik'],
                                    'status' => 'aktif',
                                    'created_at' => date('Y-m-d H:i:s')
                                ];
                                
                                $dosen_query = "INSERT INTO dosen (user_id, nidn, nama_lengkap, jabatan_akademik, status, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                                executeQuery($dosen_query, array_values($dosen_data));
                            }
                            
                            // Commit transaction
                            commitTransaction();
                            
                            // Log activity
                            logActivity($user_id, 'register', 'User registered from IP: ' . $client_ip);
                            
                            // Redirect to login with success message
                            header('Location: login.php?registered=1');
                            exit();
                            
                        } catch (Exception $e) {
                            rollbackTransaction();
                            throw $e;
                        }
                    }
                }
            } catch (Exception $e) {
                logMessage('ERROR', 'Registration error: ' . $e->getMessage());
                $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        }
    }
}

$page_title = 'Registrasi - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .register-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .register-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .role-specific {
            display: none;
        }
        .role-specific.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Registrasi Akun
                    </h3>
                    <p class="mb-0 mt-2 opacity-75">Buat akun baru untuk mengakses sistem</p>
                </div>
                
                <div class="register-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                                       placeholder="Masukkan username" required>
                                <div class="invalid-feedback">Username harus diisi (minimal 3 karakter)</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                                       placeholder="Masukkan email" required>
                                <div class="invalid-feedback">Email valid harus diisi</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                                <div class="invalid-feedback">Password minimal <?php echo PASSWORD_MIN_LENGTH; ?> karakter</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Ulangi password" required>
                                <div class="invalid-feedback">Konfirmasi password harus sama</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Daftar Sebagai</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="mahasiswa" <?php echo $form_data['role'] === 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                                <option value="dosen" <?php echo $form_data['role'] === 'dosen' ? 'selected' : ''; ?>>Dosen</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?php echo htmlspecialchars($form_data['nama_lengkap']); ?>" 
                                   placeholder="Masukkan nama lengkap" required>
                            <div class="invalid-feedback">Nama lengkap harus diisi</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nim_nidn" class="form-label fw-semibold">
                                    <span id="nim_nidn_label">NIM</span>
                                </label>
                                <input type="text" class="form-control" id="nim_nidn" name="nim_nidn" 
                                       value="<?php echo htmlspecialchars($form_data['nim_nidn']); ?>" 
                                       placeholder="Masukkan NIM" required>
                                <div class="invalid-feedback">
                                    <span id="nim_nidn_error">NIM harus diisi</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <!-- Mahasiswa fields -->
                                <div id="mahasiswa_fields" class="role-specific active">
                                    <label for="program_studi" class="form-label fw-semibold">Program Studi</label>
                                    <select class="form-control" id="program_studi" name="program_studi">
                                        <option value="">Pilih Program Studi</option>
                                        <option value="Teknik Informatika" <?php echo $form_data['program_studi'] === 'Teknik Informatika' ? 'selected' : ''; ?>>Teknik Informatika</option>
                                        <option value="Sistem Informasi" <?php echo $form_data['program_studi'] === 'Sistem Informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                                        <option value="Manajemen" <?php echo $form_data['program_studi'] === 'Manajemen' ? 'selected' : ''; ?>>Manajemen</option>
                                        <option value="Akuntansi" <?php echo $form_data['program_studi'] === 'Akuntansi' ? 'selected' : ''; ?>>Akuntansi</option>
                                        <option value="Psikologi" <?php echo $form_data['program_studi'] === 'Psikologi' ? 'selected' : ''; ?>>Psikologi</option>
                                    </select>
                                    <div class="invalid-feedback">Program studi harus dipilih</div>
                                </div>
                                
                                <!-- Dosen fields -->
                                <div id="dosen_fields" class="role-specific">
                                    <label for="jabatan_akademik" class="form-label fw-semibold">Jabatan Akademik</label>
                                    <select class="form-control" id="jabatan_akademik" name="jabatan_akademik">
                                        <option value="">Pilih Jabatan Akademik</option>
                                        <option value="Asisten Ahli" <?php echo $form_data['jabatan_akademik'] === 'Asisten Ahli' ? 'selected' : ''; ?>>Asisten Ahli</option>
                                        <option value="Lektor" <?php echo $form_data['jabatan_akademik'] === 'Lektor' ? 'selected' : ''; ?>>Lektor</option>
                                        <option value="Lektor Kepala" <?php echo $form_data['jabatan_akademik'] === 'Lektor Kepala' ? 'selected' : ''; ?>>Lektor Kepala</option>
                                        <option value="Profesor" <?php echo $form_data['jabatan_akademik'] === 'Profesor' ? 'selected' : ''; ?>>Profesor</option>
                                    </select>
                                    <div class="invalid-feedback">Jabatan akademik harus dipilih</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-register">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">Sudah punya akun?</p>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none opacity-75">
                    <i class="fas fa-home me-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role change handler
        document.getElementById('role').addEventListener('change', function() {
            const role = this.value;
            const mahasiswaFields = document.getElementById('mahasiswa_fields');
            const dosenFields = document.getElementById('dosen_fields');
            const nimNidnLabel = document.getElementById('nim_nidn_label');
            const nimNidnInput = document.getElementById('nim_nidn');
            const nimNidnError = document.getElementById('nim_nidn_error');
            
            if (role === 'mahasiswa') {
                mahasiswaFields.classList.add('active');
                dosenFields.classList.remove('active');
                nimNidnLabel.textContent = 'NIM';
                nimNidnInput.placeholder = 'Masukkan NIM';
                nimNidnError.textContent = 'NIM harus diisi';
                document.getElementById('program_studi').required = true;
                document.getElementById('jabatan_akademik').required = false;
            } else {
                mahasiswaFields.classList.remove('active');
                dosenFields.classList.add('active');
                nimNidnLabel.textContent = 'NIDN';
                nimNidnInput.placeholder = 'Masukkan NIDN';
                nimNidnError.textContent = 'NIDN harus diisi';
                document.getElementById('program_studi').required = false;
                document.getElementById('jabatan_akademik').required = true;
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });

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
    </script>
</body>
</html>
