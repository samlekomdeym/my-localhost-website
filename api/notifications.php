<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = getRole();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get notifications for user
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            
            $where_clause = "user_id = ?";
            $params = [$user_id];
            
            if ($unread_only) {
                $where_clause .= " AND is_read = 0";
            }
            
            // Get notifications
            $notifications = fetchAll("
                SELECT id, title, message, type, is_read, created_at,
                       DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as formatted_date
                FROM notifications 
                WHERE $where_clause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ", array_merge($params, [$limit, $offset]));
            
            // Get unread count
            $unread_count = fetchOne("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ", [$user_id])['count'];
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unread_count,
                'has_more' => count($notifications) === $limit
            ]);
            break;
            
        case 'POST':
            // Create new notification (admin only)
            if ($role !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit();
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $title = sanitize($input['title'] ?? '');
            $message = sanitize($input['message'] ?? '');
            $type = sanitize($input['type'] ?? 'info');
            $target_user_id = (int)($input['user_id'] ?? 0);
            $target_role = sanitize($input['role'] ?? '');
            
            if (empty($title) || empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and message are required']);
                exit();
            }
            
            // Send to specific user or all users with specific role
            if ($target_user_id > 0) {
                // Send to specific user
                executeQuery("
                    INSERT INTO notifications (user_id, title, message, type, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ", [$target_user_id, $title, $message, $type]);
            } elseif (!empty($target_role)) {
                // Send to all users with specific role
                $users = fetchAll("SELECT id FROM users WHERE role = ? AND status = 'active'", [$target_role]);
                
                foreach ($users as $user) {
                    executeQuery("
                        INSERT INTO notifications (user_id, title, message, type, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ", [$user['id'], $title, $message, $type]);
                }
            } else {
                // Send to all active users
                $users = fetchAll("SELECT id FROM users WHERE status = 'active'");
                
                foreach ($users as $user) {
                    executeQuery("
                        INSERT INTO notifications (user_id, title, message, type, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ", [$user['id'], $title, $message, $type]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
            break;
            
        case 'PUT':
            // Mark notification as read
            $input = json_decode(file_get_contents('php://input'), true);
            $notification_id = (int)($input['id'] ?? 0);
            
            if ($notification_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid notification ID']);
                exit();
            }
            
            // Check if notification belongs to user
            $notification = fetchOne("
                SELECT id FROM notifications 
                WHERE id = ? AND user_id = ?
            ", [$notification_id, $user_id]);
            
            if (!$notification) {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found']);
                exit();
            }
            
            executeQuery("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ?
            ", [$notification_id]);
            
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            break;
            
        case 'DELETE':
            // Delete notification
            $notification_id = (int)($_GET['id'] ?? 0);
            
            if ($notification_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid notification ID']);
                exit();
            }
            
            // Check if notification belongs to user
            $notification = fetchOne("
                SELECT id FROM notifications 
                WHERE id = ? AND user_id = ?
            ", [$notification_id, $user_id]);
            
            if (!$notification) {
                http_response_code(404);
                echo json_encode(['error' => 'Notification not found']);
                exit();
            }
            
            executeQuery("DELETE FROM notifications WHERE id = ?", [$notification_id]);
            
            echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            break;
            
        default:
            // Get notifications based on user role
            $notifications = [];
            
            switch ($role) {
                case 'admin':
                    // Admin gets all system notifications
                    $notifications = fetchAll("
                        SELECT 
                            'system' as type,
                            'Sistem' as title,
                            CONCAT('Ada ', COUNT(*), ' aktivitas baru hari ini') as message,
                            MAX(created_at) as created_at,
                            'info' as priority
                        FROM activity_logs 
                        WHERE DATE(created_at) = CURDATE()
                        UNION ALL
                        SELECT 
                            'users' as type,
                            'Pengguna Baru' as title,
                            CONCAT('Ada ', COUNT(*), ' pengguna baru mendaftar') as message,
                            MAX(created_at) as created_at,
                            'success' as priority
                        FROM users 
                        WHERE DATE(created_at) = CURDATE()
                        UNION ALL
                        SELECT 
                            'maintenance' as type,
                            'Pemeliharaan Sistem' as title,
                            'Sistem akan maintenance pada 02:00 WIB' as message,
                            NOW() as created_at,
                            'warning' as priority
                        ORDER BY created_at DESC
                        LIMIT 10
                    ");
                    break;
                    
                case 'dosen':
                    // Dosen gets academic notifications
                    $dosen = fetchOne("SELECT id FROM dosen WHERE user_id = ?", [$user_id]);
                    if ($dosen) {
                        $notifications = fetchAll("
                            SELECT 
                                'jadwal' as type,
                                'Jadwal Mengajar' as title,
                                CONCAT('Anda memiliki ', COUNT(*), ' jadwal mengajar hari ini') as message,
                                NOW() as created_at,
                                'info' as priority
                            FROM jadwal 
                            WHERE dosen_id = ? AND DATE(tanggal) = CURDATE()
                            UNION ALL
                            SELECT 
                                'nilai' as type,
                                'Penilaian' as title,
                                'Ada tugas penilaian yang perlu diselesaikan' as message,
                                NOW() as created_at,
                                'warning' as priority
                            ORDER BY created_at DESC
                            LIMIT 5
                        ", [$dosen['id']]);
                    }
                    break;
                    
                case 'mahasiswa':
                    // Mahasiswa gets academic notifications
                    $mahasiswa = fetchOne("SELECT id FROM mahasiswa WHERE user_id = ?", [$user_id]);
                    if ($mahasiswa) {
                        $notifications = fetchAll("
                            SELECT 
                                'jadwal' as type,
                                'Jadwal Kuliah' as title,
                                CONCAT('Anda memiliki ', COUNT(*), ' jadwal kuliah hari ini') as message,
                                NOW() as created_at,
                                'info' as priority
                            FROM jadwal j
                            JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
                            WHERE DATE(j.tanggal) = CURDATE()
                            UNION ALL
                            SELECT 
                                'tugas' as type,
                                'Tugas & Ujian' as title,
                                'Ada tugas yang mendekati deadline' as message,
                                NOW() as created_at,
                                'warning' as priority
                            ORDER BY created_at DESC
                            LIMIT 5
                        ");
                    }
                    break;
            }
            
            // Add general notifications for all users
            $general_notifications = fetchAll("
                SELECT 
                    'info' as type,
                    'Informasi Kampus' as title,
                    judul as message,
                    created_at,
                    'info' as priority
                FROM info_kampus 
                WHERE status = 'aktif' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC
                LIMIT 3
            ");
            
            $notifications = array_merge($notifications, $general_notifications);
            
            // Format notifications
            $formatted_notifications = [];
            foreach ($notifications as $notif) {
                $formatted_notifications[] = [
                    'id' => uniqid(),
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'time' => timeAgo($notif['created_at']),
                    'priority' => $notif['priority'],
                    'icon' => getNotificationIcon($notif['type']),
                    'url' => getNotificationUrl($notif['type'], $role)
                ];
            }
            
            echo json_encode([
                'success' => true,
                'notifications' => $formatted_notifications,
                'count' => count($formatted_notifications),
                'unread_count' => count($formatted_notifications) // In real app, track read status
            ]);
            
            break;
    }
    
} catch (Exception $e) {
    logMessage('ERROR', 'Notifications API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function getNotificationIcon($type) {
    $icons = [
        'system' => 'fas fa-cog',
        'users' => 'fas fa-users',
        'maintenance' => 'fas fa-tools',
        'jadwal' => 'fas fa-calendar',
        'nilai' => 'fas fa-star',
        'tugas' => 'fas fa-tasks',
        'info' => 'fas fa-info-circle',
        'default' => 'fas fa-bell'
    ];
    
    return $icons[$type] ?? $icons['default'];
}

function getNotificationUrl($type, $role) {
    $urls = [
        'system' => SITE_URL . '/admin/',
        'users' => SITE_URL . '/admin/users/',
        'jadwal' => SITE_URL . '/' . $role . '/jadwal.php',
        'nilai' => SITE_URL . '/' . $role . '/nilai.php',
        'tugas' => SITE_URL . '/' . $role . '/tugas.php',
        'info' => SITE_URL . '/pages/info.php'
    ];
    
    return $urls[$type] ?? '#';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Baru saja';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan lalu';
    
    return floor($time/31536000) . ' tahun lalu';
}
?>
