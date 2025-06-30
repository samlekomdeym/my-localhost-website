<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number (Indonesian format)
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(08|62)[0-9]{8,13}$/', $phone);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Format date
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'd M Y H:i') {
    if (empty($datetime)) return '-';
    return date($format, strtotime($datetime));
}

// Time ago function
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time/60) . ' menit yang lalu';
    if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan yang lalu';
    return floor($time/31536000) . ' tahun yang lalu';
}

// Truncate text
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Upload file
function uploadFile($file, $allowed_types = null, $max_size = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    $allowed_types = $allowed_types ?? ALLOWED_EXTENSIONS;
    $max_size = $max_size ?? MAX_FILE_SIZE;
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File terlalu besar. Maksimal ' . formatFileSize($max_size)];
    }
    
    // Check file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $filepath = UPLOAD_PATH . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan file'];
}

// Delete file
function deleteFile($filename) {
    $filepath = UPLOAD_PATH . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// Log activity
function logActivity($user_id, $action, $description = '', $ip_address = null) {
    $ip_address = $ip_address ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    try {
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
        executeQuery($query, [$user_id, $action, $description, $ip_address]);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to log activity: ' . $e->getMessage());
    }
}

// Send notification
function sendNotification($user_id, $title, $message, $type = 'info') {
    try {
        $query = "INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())";
        return executeQuery($query, [$user_id, $title, $message, $type]);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to send notification: ' . $e->getMessage());
        return false;
    }
}

// Get user notifications
function getUserNotifications($user_id, $limit = 10, $unread_only = false) {
    try {
        $where = "user_id = ?";
        $params = [$user_id];
        
        if ($unread_only) {
            $where .= " AND is_read = 0";
        }
        
        $query = "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT $limit";
        return fetchAll($query, $params);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get notifications: ' . $e->getMessage());
        return [];
    }
}

// Mark notification as read
function markNotificationAsRead($notification_id, $user_id) {
    try {
        $query = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?";
        return executeQuery($query, [$notification_id, $user_id]);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to mark notification as read: ' . $e->getMessage());
        return false;
    }
}

// Get user by ID
function getUserById($user_id) {
    return fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
}

// Get users by role
function getUsersByRole($role) {
    return fetchAll("SELECT * FROM users WHERE role = ?", [$role]);
}

// Check if username exists
function usernameExists($username, $exclude_id = null) {
    $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    $params = [$username];
    
    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $result = fetchOne($query, $params);
    return $result['count'] > 0;
}

// Check if email exists
function emailExists($email, $exclude_id = null) {
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $params = [$email];
    
    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $result = fetchOne($query, $params);
    return $result['count'] > 0;
}

// Generate pagination
function generatePagination($current_page, $total_pages, $base_url, $params = []) {
    if ($total_pages <= 1) return '';
    
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_params = array_merge($params, ['page' => $current_page - 1]);
        $prev_url = $base_url . '?' . http_build_query($prev_params);
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $prev_url . '">Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $page_params = array_merge($params, ['page' => $i]);
        $page_url = $base_url . '?' . http_build_query($page_params);
        $active = ($i == $current_page) ? ' active' : '';
        $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $page_url . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_params = array_merge($params, ['page' => $current_page + 1]);
        $next_url = $base_url . '?' . http_build_query($next_params);
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $next_url . '">Next</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}

// Redirect with message
function redirectWithMessage($url, $type, $message) {
    setFlashMessage($type, $message);
    header("Location: $url");
    exit();
}

// Get client IP address
function getClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Check rate limiting
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 300) {
    $cache_file = __DIR__ . '/../logs/rate_limit_' . md5($identifier) . '.json';
    
    $data = [];
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true) ?: [];
    }
    
    $current_time = time();
    $data = array_filter($data, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    if (count($data) >= $max_attempts) {
        return false;
    }
    
    $data[] = $current_time;
    file_put_contents($cache_file, json_encode($data));
    
    return true;
}

// Clean old files
function cleanOldFiles($directory, $max_age_days = 30) {
    $max_age = time() - ($max_age_days * 24 * 60 * 60);
    $files = glob($directory . '/*');
    
    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $max_age) {
            unlink($file);
        }
    }
}

// Generate breadcrumb
function generateBreadcrumb($items) {
    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);
        
        if ($is_last) {
            $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $breadcrumb .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $breadcrumb .= '</ol></nav>';
    return $breadcrumb;
}
?>
