<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getRole();
    header("Location: " . SITE_URL . "/$role/");
    exit();
}

$error = '';
$username = '';
$remember_me = false;

// Check for logout message
$logout_message = '';
if (isset($_GET['logout'])) {
    $logout_message = 'Anda telah berhasil logout.';
}

// Check for registration success
$register_message = '';
if (isset($_GET['registered'])) {
    $register_message = 'Registrasi berhasil! Silakan login dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        // Check rate limiting
        $client_ip = getClientIP();
        if (!checkRateLimit("login_$client_ip", MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . (LOGIN_LOCKOUT_TIME / 60) . ' menit.';
        } else {
            try {
                // Get user by username or email
                $user = fetchOne("
                    SELECT u.*, 
                           CASE 
                               WHEN u.role = 'dosen' THEN d.nama_lengkap
                               WHEN u.role = 'mahasiswa' THEN m.nama_lengkap
                               ELSE u.username
                           END as nama
                    FROM users u
                    LEFT JOIN dosen d ON u.id = d.user_id
                    LEFT JOIN mahasiswa m ON u.id = m.user_id
                    WHERE (u.username = ? OR u.email = ?) AND u.status = 'active'
                ", [$username, $username]);
                
                if ($user && verifyPassword($password, $user['password'])) {
                    // Login successful
                    setUserSession($user);
                    
                    // Handle remember me
                    if ($remember_me) {
                        $token = generateRandomString(32);
                        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
                        
                        executeQuery("UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?", 
                                   [$token, $expires, $user['id']]);
                        
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    }
                    
                    // Log activity
                    logActivity($user['id'], 'login', 'User logged in from IP: ' . $client_ip);
                    
                    // Redirect based on role
                    $redirect_url = SITE_URL . '/' . $user['role'] . '/';
                    header("Location: $redirect_url");
                    exit();
                } else {
                    $error = 'Username/email atau password salah';
                    logMessage('WARNING', "Failed login attempt for username: $username from IP: $client_ip");
                }
            } catch (Exception $e) {
                logMessage('ERROR', 'Login error: ' . $e->getMessage());
                $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        }
    }
}

$page_title = 'Login - ' . SITE_NAME;
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
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 12px 0 0 12px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        .demo-accounts {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .demo-account {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h3 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <?php echo SITE_NAME; ?>
                    </h3>
                    <p class="mb-0 mt-2 opacity-75">Sistem Informasi Kampus</p>
                </div>
                
                <div class="login-body">
                    <?php if ($logout_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($logout_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($register_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($register_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username atau Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username); ?>" 
                                       placeholder="Masukkan username atau email" required autofocus>
                                <div class="invalid-feedback">Username atau email harus diisi</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password harus diisi</div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" 
                                   <?php echo $remember_me ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="remember_me">
                                Ingat saya
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mb-3">
                        <a href="forgot-password.php" class="text-decoration-none">
                            Lupa password?
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">Belum punya akun?</p>
                        <a href="register.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                    </div>
                    
                    <!-- Demo Accounts -->
                    <div class="demo-accounts">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-info-circle me-1"></i>Akun Demo:
                        </h6>
                        <div class="demo-account">
                            <strong>Admin:</strong> admin / admin123
                        </div>
                        <div class="demo-account">
                            <strong>Dosen:</strong> dosen1 / dosen123
                        </div>
                        <div class="demo-account">
                            <strong>Mahasiswa:</strong> mahasiswa1 / mahasiswa123
                        </div>
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
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
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

        // Demo account quick fill
        document.querySelectorAll('.demo-account').forEach(function(element) {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function() {
                const text = this.textContent;
                const parts = text.split(' / ');
                if (parts.length === 2) {
                    const username = parts[0].split(': ')[1];
                    const password = parts[1];
                    document.getElementById('username').value = username;
                    document.getElementById('password').value = password;
                }
            });
        });
    </script>
</body>
</html>
