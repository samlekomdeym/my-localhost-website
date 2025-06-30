<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';

$page_title = 'Akses Ditolak - ' . SITE_NAME;
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }
        
        .error-icon {
            font-size: 8rem;
            margin-bottom: 2rem;
            opacity: 0.8;
            animation: pulse 2s infinite;
        }
        
        .error-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .error-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .error-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        .btn-home {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .btn-home:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
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
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 10%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 30%;
            right: 30%;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .error-code {
            font-size: 1rem;
            opacity: 0.6;
            margin-top: 2rem;
            font-family: 'Courier New', monospace;
        }
        
        .back-options {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        
        .back-option {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .back-option:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1 class="error-title">403</h1>
        <h2 class="error-subtitle">Akses Ditolak</h2>
        
        <p class="error-description">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. 
            Silakan login dengan akun yang memiliki hak akses yang sesuai 
            atau hubungi administrator sistem.
        </p>
        
        <div class="back-options">
            <a href="<?php echo SITE_URL; ?>/auth/login.php" class="back-option">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
            <a href="<?php echo SITE_URL; ?>" class="back-option">
                <i class="fas fa-home me-2"></i>Beranda
            </a>
            <a href="javascript:history.back()" class="back-option">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
        
        <div class="error-code">
            Error Code: UNAUTHORIZED_ACCESS<br>
            Time: <?php echo date('Y-m-d H:i:s'); ?><br>
            IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto redirect after 30 seconds
        let countdown = 30;
        const countdownElement = document.createElement('div');
        countdownElement.className = 'mt-3 opacity-75';
        countdownElement.innerHTML = `<small>Akan diarahkan ke halaman login dalam <span id="countdown">${countdown}</span> detik</small>`;
        document.querySelector('.error-container').appendChild(countdownElement);
        
        const timer = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?php echo SITE_URL; ?>/auth/login.php';
            }
        }, 1000);
        
        // Stop countdown if user interacts with page
        document.addEventListener('click', () => {
            clearInterval(timer);
            countdownElement.remove();
        });
        
        document.addEventListener('keydown', () => {
            clearInterval(timer);
            countdownElement.remove();
        });
    </script>
</body>
</html>
