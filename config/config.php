<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Tetap 0 di produksi, 1 untuk debugging saat ini
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Site Configuration
// PASTIKAN INI SESUAI DENGAN LOKASI FOLDER PROYEK KAMU DI HTDOCS
// Contoh: Jika folder proyek adalah C:\xampp\htdocs\kampusprojek
define('SITE_URL', 'http://localhost/kampus'); 
define('SITE_NAME', 'MAGNOLIA UNIVERSITY');
define('SITE_EMAIL', 'info@magnolia-university.ac.id');
define('SITE_PHONE', '+62 21 1234 5678');
define('SITE_ADDRESS', 'Jl. Pendidikan No. 123, Jakarta Selatan 12345, Indonesia');

// Definisi SITE_DESCRIPTION dan SITE_KEYWORDS (untuk header.php)
define('SITE_DESCRIPTION', SITE_NAME . ' adalah universitas terkemuka yang menghasilkan lulusan berkualitas tinggi dalam bidang teknologi, bisnis, dan sains dengan pendekatan pembelajaran modern dan inovatif.');
define('SITE_KEYWORDS', 'magnolia university, universitas, pendidikan tinggi, teknologi, bisnis, sains, jakarta, kampus modern');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kampus_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security Configuration
define('HASH_ALGO', PASSWORD_DEFAULT); // Default akan menggunakan bcrypt di PHP 8
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 6);

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Pagination Configuration
define('ITEMS_PER_PAGE', 10);

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Application Version
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Cache Configuration
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600);

// Logging Configuration
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Include required files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
try {
    $db = getDB();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (APP_ENV === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("System temporarily unavailable. Please try again later.");
    }
}

// Start session
startSecureSession();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateRandomString(CSRF_TOKEN_LENGTH);
}

// Helper functions (Tetap di sini, bukan duplikasi dari functions.php)
function getAppVersion(): string {
    return APP_VERSION;
}

function isProduction(): bool {
    return APP_ENV === 'production';
}

function isDevelopment(): bool {
    return APP_ENV === 'development';
}

function getSiteUrl(string $path = ''): string {
    return SITE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function getUploadUrl(string $filename = ''): string {
    return UPLOAD_URL . ($filename ? ltrim($filename, '/') : '');
}

function logMessage(string $level, string $message, array $context = []): void {
    if (!LOG_ENABLED) return;
    
    $logLevels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3]; 
    $currentLevel = $logLevels[LOG_LEVEL] ?? 1; 
    $messageLevel = $logLevels[$level] ?? 1;
    
    if ($messageLevel >= $currentLevel) {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'app.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context) : ''; 
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Set error handler
set_error_handler(function(int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $errorTypes = [ 
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE'
    ];
    
    $type = $errorTypes[$severity] ?? 'UNKNOWN'; 
    logMessage('ERROR', "{$type}: {$message} in {$file} on line {$line}");
    
    if (isDevelopment()) {
        echo "<b>{$type}</b>: {$message} in <b>{$file}</b> on line <b>{$line}</b><br>";
    }
    
    return true;
});

// Set exception handler
set_exception_handler(function(Throwable $exception): void {
    logMessage('ERROR', 'Uncaught exception: ' . $exception->getMessage(), [ 
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (isDevelopment()) {
        echo "<h1>Uncaught Exception</h1>";
        echo "<p><b>Message:</b> " . $exception->getMessage() . "</p>";
        echo "<p><b>File:</b> " . $exception->getFile() . "</p>";
        echo "<p><b>Line:</b> " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>System Error</h1>";
        echo "<p>An error occurred. Please try again later.</p>";
    }
});

// Clean up old log files (keep last 30 days)
function cleanupLogs(): void {
    $logDir = __DIR__ . '/../logs/';
    if (is_dir($logDir)) {
        $files = glob($logDir . '*.log');
        foreach ($files as $file) {
            if (filemtime($file) < time() - (30 * 24 * 60 * 60)) {
                unlink($file);
            }
        }
    }
}

// Run cleanup occasionally (1% chance)
if (rand(1, 100) === 1) {
    cleanupLogs();
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Create logs directory if it doesn't exist
$logs_dir = __DIR__ . '/../logs/';
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}
?>
