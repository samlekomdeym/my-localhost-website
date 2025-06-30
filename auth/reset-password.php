<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$page_title = 'Reset Password - ' . SITE_NAME;
$error = '';
$success = '';
$token = sanitize($_GET['token'] ?? '');

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/' . getRole());
    exit();
}

// Validate token
$reset_data = null;
if ($token) {
    try {
        $reset_data = fetchOne("SELECT pr.*, u.username, u.email 
                               FROM password_resets pr 
                               JOIN users u ON pr.user_id = u.id 
                               WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL", 
                              [$token]);
        
        if (!$reset_data) {
            $error = 'Token reset password tidak valid atau sudah kedaluwarsa';
        }
    } catch (Exception $e) {
        logMessage('ERROR', 'Token validation error: ' . $e->getMessage());
        $error = 'Terjadi kesalahan sistem';
    }
} else {
    $error = 'Token reset password tidak ditemukan';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset_data) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password baru wajib diisi';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        try {
            $db = getDB();
            $db->beginTransaction();
            
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            executeQuery("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?", 
                        [$hashed_password, $reset_data['user_id']]);
            
            // Mark token as used
            executeQuery("UPDATE password_resets SET used_at = NOW() WHERE id = ?", 
                        [$reset_data['id']]);
            
            $db->commit();
            
            logActivity($reset_data['user_id'], 'Password Reset', 'Password successfully reset');
            
            $success = 'Password berhasil direset. Silakan login dengan password baru Anda.';
            
        } catch (Exception $e) {
            $db->rollBack();
            logMessage('ERROR', 'Password reset error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan saat mereset password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #764ba2;
            text-decoration: none;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="auth-card">
                    <div class="auth-header">
                        <i class="fas fa-lock fa-3x mb-3"></i>
                        <h4 class="mb-0">Reset Password</h4>
                        <?php if ($reset_data): ?>
                            <p class="mb-0 opacity-75">Untuk: <?php echo htmlspecialchars($reset_data['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="auth-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                            
                            <div class="text-center">
                                <a href="forgot-password.php" class="back-link">
                                    <i class="fas fa-arrow-left me-2"></i>Minta Reset Ulang
                                </a>
                            </div>
                            
                        <?php elseif ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                            
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                                </a>
                            </div>
                            
                        <?php elseif ($reset_data): ?>
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-key me-2"></i>Password Baru
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Masukkan password baru" required minlength="6">
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                                onclick="togglePassword('password')" style="z-index: 10;">
                                            <i class="fas fa-eye" id="password-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="password-strength"></div>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                    <div class="invalid-feedback">
                                        Password minimal 6 karakter
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-check-double me-2"></i>Konfirmasi Password
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru" required>
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                                onclick="togglePassword('confirm_password')" style="z-index: 10;">
                                            <i class="fas fa-eye" id="confirm_password-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="confirm-feedback">
                                        Konfirmasi password wajib diisi
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Reset Password
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <a href="login.php" class="back-link">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            return strength;
        }
        
        // Update password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength');
            const strength = checkPasswordStrength(password);
            
            strengthBar.style.width = (strength * 20) + '%';
            
            if (strength <= 2) {
                strengthBar.className = 'password-strength strength-weak';
            } else if (strength <= 3) {
                strengthBar.className = 'password-strength strength-medium';
            } else {
                strengthBar.className = 'password-strength strength-strong';
            }
        });
        
        // Password confirmation validation
        function validatePasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmField = document.getElementById('confirm_password');
            const feedback = document.getElementById('confirm-feedback');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmField.setCustomValidity('Password tidak cocok');
                feedback.textContent = 'Password tidak cocok';
            } else {
                confirmField.setCustomValidity('');
                feedback.textContent = 'Konfirmasi password wajib diisi';
            }
        }
        
        document.getElementById('password').addEventListener('input', validatePasswordMatch);
        document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        validatePasswordMatch();
                        
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
