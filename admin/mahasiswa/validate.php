<?php 
define('SECURE_ACCESS', true);
require_once '../../config/config.php'; 
require_once '../../config/database.php'; 
require_once '../../config/session.php'; 
require_once '../../includes/functions.php'; 

// Check if user is logged in and is admin
if (!isLoggedIn() || getRole() !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Unauthorized'));
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('error' => 'Method not allowed'));
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'check_username':
            $username = sanitize(isset($_POST['username']) ? $_POST['username'] : '');
            $exclude_id = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
            
            if (empty($username)) {
                echo json_encode(array('valid' => false, 'message' => 'Username tidak boleh kosong'));
                exit();
            }
            
            $query = "SELECT id FROM users WHERE username = ?";
            $params = array($username);
            
            if ($exclude_id > 0) {
                $query .= " AND id != ?";
                $params[] = $exclude_id;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $exists = $stmt->fetch();
            
            if ($exists) {
                echo json_encode(array('valid' => false, 'message' => 'Username sudah digunakan'));
            } else {
                echo json_encode(array('valid' => true, 'message' => 'Username tersedia'));
            }
            break;
            
        case 'check_email':
            $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
            $exclude_id = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
            
            if (empty($email)) {
                echo json_encode(array('valid' => false, 'message' => 'Email tidak boleh kosong'));
                exit();
            }
            
            if (!isValidEmail($email)) {
                echo json_encode(array('valid' => false, 'message' => 'Format email tidak valid'));
                exit();
            }
            
            $query = "SELECT id FROM users WHERE email = ?";
            $params = array($email);
            
            if ($exclude_id > 0) {
                $query .= " AND id != ?";
                $params[] = $exclude_id;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $exists = $stmt->fetch();
            
            if ($exists) {
                echo json_encode(array('valid' => false, 'message' => 'Email sudah digunakan'));
            } else {
                echo json_encode(array('valid' => true, 'message' => 'Email tersedia'));
            }
            break;
            
        case 'check_nim':
            $nim = sanitize(isset($_POST['nim']) ? $_POST['nim'] : '');
            $exclude_id = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
            
            if (empty($nim)) {
                echo json_encode(array('valid' => false, 'message' => 'NIM tidak boleh kosong'));
                exit();
            }
            
            $query = "SELECT id FROM mahasiswa WHERE nim = ?";
            $params = array($nim);
            
            if ($exclude_id > 0) {
                $query .= " AND id != ?";
                $params[] = $exclude_id;
            }
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $exists = $stmt->fetch();
            
            if ($exists) {
                echo json_encode(array('valid' => false, 'message' => 'NIM sudah digunakan'));
            } else {
                echo json_encode(array('valid' => true, 'message' => 'NIM tersedia'));
            }
            break;
            
        case 'validate_phone':
            $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
            
            if (empty($phone)) {
                echo json_encode(array('valid' => true, 'message' => 'Nomor telepon opsional'));
                exit();
            }
            
            // Indonesian phone number validation
            if (!preg_match('/^08[0-9]{8,11}$/', $phone)) {
                echo json_encode(array('valid' => false, 'message' => 'Format nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx'));
            } else {
                echo json_encode(array('valid' => true, 'message' => 'Format nomor telepon valid'));
            }
            break;
            
        case 'validate_password':
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if (empty($password)) {
                echo json_encode(array('valid' => false, 'message' => 'Password tidak boleh kosong'));
                exit();
            }
            
            $errors = array();
            
            if (strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter';
            }
            
            if (!preg_match('/[A-Za-z]/', $password)) {
                $errors[] = 'Password harus mengandung huruf';
            }
            
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password harus mengandung angka';
            }
            
            if (!empty($errors)) {
                echo json_encode(array('valid' => false, 'message' => implode(', ', $errors)));
            } else {
                echo json_encode(array('valid' => true, 'message' => 'Password valid'));
            }
            break;
            
        case 'get_mahasiswa_stats':
            $program_studi = isset($_POST['program_studi']) ? sanitize($_POST['program_studi']) : '';
            $tahun_masuk = isset($_POST['tahun_masuk']) ? sanitize($_POST['tahun_masuk']) : '';
            
            $where_conditions = array();
            $params = array();
            
            if (!empty($program_studi)) {
                $where_conditions[] = "m.program_studi = ?";
                $params[] = $program_studi;
            }
            
            if (!empty($tahun_masuk)) {
                $where_conditions[] = "m.tahun_masuk = ?";
                $params[] = $tahun_masuk;
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Total mahasiswa
            $total_query = "SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$where_clause}";
            $total = fetchCount($total_query, $params);
            
            // Active mahasiswa
            $active_conditions = $where_conditions;
            $active_conditions[] = "u.status = 'active'";
            $active_params = $params;
            $active_params[] = 'active';
            $active_where = 'WHERE ' . implode(' AND ', $active_conditions);
            
            $active_query = "SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$active_where}";
            $active = fetchCount($active_query, $active_params);
            
            // By gender
            $male_conditions = $where_conditions;
            $male_conditions[] = "m.jenis_kelamin = 'L'";
            $male_params = $params;
            $male_params[] = 'L';
            $male_where = !empty($male_conditions) ? 'WHERE ' . implode(' AND ', $male_conditions) : '';
            
            $male_query = "SELECT COUNT(*) FROM mahasiswa m JOIN users u ON m.user_id = u.id {$male_where}";
            $male = fetchCount($male_query, $male_params);
            
            $female = $total - $male;
            
            echo json_encode(array(
                'total' => $total,
                'active' => $active,
                'inactive' => $total - $active,
                'male' => $male,
                'female' => $female
            ));
            break;
            
        default:
            echo json_encode(array('error' => 'Invalid action'));
            break;
    }
    
} catch (Exception $e) {
    error_log("Mahasiswa validation error: " . $e->getMessage());
    echo json_encode(array('error' => 'Terjadi kesalahan sistem'));
}
?>
