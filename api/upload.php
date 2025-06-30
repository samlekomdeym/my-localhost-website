<?php 
// Pastikan SECURE_ACCESS didefinisikan agar file config tidak die
define('SECURE_ACCESS', true); 

require_once '../config/config.php'; 
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../includes/functions.php'; 

header('Content-Type: application/json'); 

if (!isLoggedIn()) {     
    http_response_code(401);     
    echo json_encode(array('error' => 'Unauthorized')); // Menggunakan array()     
    exit; 
} 

// Menggunakan isset() dan ternary operator untuk PHP 5.6
$query = isset($_GET['q']) ? sanitize($_GET['q']) : ''; 
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; 
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10; 

if (empty($query) || strlen($query) < 2) {     
    echo json_encode(array('success' => true, 'data' => array())); // Menggunakan array()     
    exit; 
} 

$results = array(); // Menggunakan array()

try {     
    $db = getDB(); // Mendapatkan koneksi DB
    $search_term = "%{$query}%";          

    // Search mahasiswa     
    if ($type === 'all' || $type === 'mahasiswa') {         
        if (hasRole(array('admin', 'dosen'))) { // Menggunakan array()
            $mahasiswa = fetchAll("                 
                SELECT m.id, m.nim, m.nama_lengkap, m.program_studi, 'mahasiswa' as type                 
                FROM mahasiswa m                  
                JOIN users u ON m.user_id = u.id                  
                WHERE (m.nama_lengkap LIKE ? OR m.nim LIKE ?) AND u.status = 'active'                 
                LIMIT ?             
            ", array($search_term, $search_term, $limit)); // Menggunakan array()
                         
            $results = array_merge($results, $mahasiswa);         
        }     
    }          

    // Search dosen     
    if ($type === 'all' || $type === 'dosen') {         
        if (hasRole(array('admin'))) { // Menggunakan array()
            $dosen = fetchAll("                 
                SELECT d.id, d.nidn, d.nama_lengkap, d.jabatan_akademik as jabatan, 'dosen' as type                 
                FROM dosen d                  
                JOIN users u ON d.user_id = u.id                  
                WHERE (d.nama_lengkap LIKE ? OR d.nidn LIKE ?) AND u.status = 'active'                 
                LIMIT ?             
            ", array($search_term, $search_term, $limit)); // Menggunakan array()
                         
            $results = array_merge($results, $dosen);         
        }     
    }          

    // Search mata kuliah     
    if ($type === 'all' || $type === 'mata_kuliah') {         
        $mata_kuliah = fetchAll("             
            SELECT id, kode_mata_kuliah as kode_mk, nama_mata_kuliah as nama_mk, sks, 'mata_kuliah' as type             
            FROM mata_kuliah              
            WHERE nama_mata_kuliah LIKE ? OR kode_mata_kuliah LIKE ?             
            LIMIT ?         
        ", array($search_term, $search_term, $limit)); // Menggunakan array()
                 
        $results = array_merge($results, $mata_kuliah);     
    }          

    // Search info kampus     
    if ($type === 'all' || $type === 'info') {         
        $info = fetchAll("             
            SELECT id, judul, kategori as tipe, created_at, 'info' as type             
            FROM info_kampus              
            WHERE judul LIKE ? AND status = 'aktif'             
            LIMIT ?         
        ", array($search_term, $limit)); // Menggunakan array()
                 
        $results = array_merge($results, $info);     
    }          

    // Limit total results     
    $results = array_slice($results, 0, $limit);          

    echo json_encode(array( // Menggunakan array()         
        'success' => true,         
        'data' => $results,         
        'query' => $query,         
        'count' => count($results)     
    ));      
} catch (Exception $e) {     
    http_response_code(500);     
    echo json_encode(array('error' => $e->getMessage())); // Menggunakan array()     
    error_log("Search API error: " . $e->getMessage()); 
} 
?>
