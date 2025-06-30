<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/session.php'; 
require_once '../config/database.php'; 
require_once '../config/config.php'; // Tambahkan ini
require_once '../includes/functions.php'; 

// Memastikan hanya user dengan role 'dosen' yang bisa mengakses
requireRole('dosen'); 

// Dapatkan ID dosen dari user_id yang login
$user_info = getUserById(getUserId());
$dosen_id = null;
if ($user_info && $user_info['role'] == 'dosen') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM dosen WHERE user_id = ?");
        $stmt->execute(array($user_info['id']));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $dosen_id = $result['id'];
        }
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get dosen ID: ' . $e->getMessage());
        $dosen_id = null;
    }
}

if (!$dosen_id) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Data dosen tidak ditemukan.');
    exit();
}

// Handle form submission for jadwal 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {     
    // Menggunakan isset() dan ternary operator untuk PHP 5.6
    $action = isset($_POST['action']) ? $_POST['action'] : ''; 

    if ($action === 'add') {         
        $mata_kuliah_id = isset($_POST['mata_kuliah_id']) ? (int)$_POST['mata_kuliah_id'] : 0; // Menggunakan ID Mata Kuliah
        $hari = sanitize(isset($_POST['hari']) ? $_POST['hari'] : '');         
        $jam_mulai = isset($_POST['jam_mulai']) ? $_POST['jam_mulai'] : '';         
        $jam_selesai = isset($_POST['jam_selesai']) ? $_POST['jam_selesai'] : '';         
        $ruangan = sanitize(isset($_POST['ruangan']) ? $_POST['ruangan'] : '');         
        $tahun_akademik = sanitize(isset($_POST['tahun_akademik']) ? $_POST['tahun_akademik'] : ''); // Menggunakan tahun_akademik
        $semester_input = sanitize(isset($_POST['semester_input']) ? $_POST['semester_input'] : ''); // Menggunakan semester_input
        
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO jadwal (dosen_id, mata_kuliah_id, hari, jam_mulai, jam_selesai, ruangan, tahun_akademik, semester, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute(array($dosen_id, $mata_kuliah_id, $hari, $jam_mulai, $jam_selesai, $ruangan, $tahun_akademik, $semester_input))) {
                setFlashMessage('success', "Jadwal berhasil ditambahkan!"); // Menggunakan setFlashMessage
            } else {
                setFlashMessage('error', "Gagal menambahkan jadwal!"); // Menggunakan setFlashMessage
            }
        } catch (Exception $e) {
            logMessage('ERROR', 'Add jadwal failed: ' . $e->getMessage());
            setFlashMessage('error', "Error: " . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/jadwal.php?semester=" . urlencode($tahun_akademik . $semester_input));
        exit();

    } elseif ($action === 'edit') {         
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;         
        $mata_kuliah_id = isset($_POST['mata_kuliah_id']) ? (int)$_POST['mata_kuliah_id'] : 0; // Menggunakan ID Mata Kuliah
        $hari = sanitize(isset($_POST['hari']) ? $_POST['hari'] : '');         
        $jam_mulai = isset($_POST['jam_mulai']) ? $_POST['jam_mulai'] : '';         
        $jam_selesai = isset($_POST['jam_selesai']) ? $_POST['jam_selesai'] : '';         
        $ruangan = sanitize(isset($_POST['ruangan']) ? $_POST['ruangan'] : '');         
        $tahun_akademik = sanitize(isset($_POST['tahun_akademik']) ? $_POST['tahun_akademik'] : ''); // Menggunakan tahun_akademik
        $semester_input = sanitize(isset($_POST['semester_input']) ? $_POST['semester_input'] : ''); // Menggunakan semester_input
        
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE jadwal SET mata_kuliah_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, ruangan = ?, tahun_akademik = ?, semester = ?, updated_at = NOW() WHERE id = ? AND dosen_id = ?");
            if ($stmt->execute(array($mata_kuliah_id, $hari, $jam_mulai, $jam_selesai, $ruangan, $tahun_akademik, $semester_input, $id, $dosen_id))) {
                setFlashMessage('success', "Jadwal berhasil diupdate!");
            } else {
                setFlashMessage('error', "Gagal mengupdate jadwal!");
            }
        } catch (Exception $e) {
            logMessage('ERROR', 'Edit jadwal failed: ' . $e->getMessage());
            setFlashMessage('error', "Error: " . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/jadwal.php?semester=" . urlencode($tahun_akademik . $semester_input));
        exit();
        
    } elseif ($action === 'delete') {         
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;         
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM jadwal WHERE id = ? AND dosen_id = ?");
            if ($stmt->execute(array($id, $dosen_id))) {
                setFlashMessage('success', "Jadwal berhasil dihapus!");
            } else {
                setFlashMessage('error', "Gagal menghapus jadwal!");
            }
        } catch (Exception $e) {
            logMessage('ERROR', 'Delete jadwal failed: ' . $e->getMessage());
            setFlashMessage('error', "Error: " . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/jadwal.php?semester=" . urlencode($current_semester_param)); // Redirect ke semester saat ini
        exit();
    } 
} 

// Get current semester parameter from URL, default ke semester aktif
$current_semester_param = isset($_GET['semester']) ? sanitize($_GET['semester']) : '';

// Get daftar tahun akademik dan semester dari database
$tahun_akademik_options = array(); // Untuk dropdown tahun akademik
$semester_options = array(); // Untuk dropdown semester Ganjil/Genap

try {
    $db = getDB();
    // Ambil tahun akademik aktif (atau terbaru jika tidak ada yang aktif)
    $stmt_ta = $db->query("SELECT id, tahun, semester FROM tahun_akademik WHERE status = 'aktif' ORDER BY tahun DESC, semester DESC LIMIT 1");
    $active_ta = $stmt_ta->fetch(PDO::FETCH_ASSOC);

    if ($active_ta) {
        $default_tahun_akademik = $active_ta['tahun'];
        $default_semester_nama = $active_ta['semester'];
        $default_semester_id = $active_ta['id'];
    } else {
        // Fallback jika tidak ada tahun akademik aktif, ambil yang terbaru
        $stmt_latest_ta = $db->query("SELECT id, tahun, semester FROM tahun_akademik ORDER BY tahun DESC, semester DESC LIMIT 1");
        $latest_ta = $stmt_latest_ta->fetch(PDO::FETCH_ASSOC);
        if ($latest_ta) {
            $default_tahun_akademik = $latest_ta['tahun'];
            $default_semester_nama = $latest_ta['semester'];
            $default_semester_id = $latest_ta['id'];
        } else {
            // Jika benar-benar kosong, set default manual
            $default_tahun_akademik = date('Y') . '/' . (date('Y') + 1);
            $default_semester_nama = (date('n') >= 9 || date('n') <= 2) ? 'Ganjil' : 'Genap'; // September-Feb: Ganjil, Mar-Agu: Genap
            $default_semester_id = 0; // Tidak ada ID
            logMessage('WARNING', 'No active or latest academic year found in database. Using default values.');
        }
    }

    // Jika parameter semester dari URL tidak ada, gunakan yang default
    if (empty($current_semester_param)) {
        $current_semester_param = $default_tahun_akademik . $default_semester_nama;
    } else {
        // Jika ada parameter, coba parse tahun dan semester
        $param_tahun = substr($current_semester_param, 0, 9); // e.g., 2024/2025
        $param_semester_nama = substr($current_semester_param, 9); // e.g., Ganjil
        
        $stmt_check_ta = $db->prepare("SELECT id FROM tahun_akademik WHERE tahun = ? AND semester = ?");
        $stmt_check_ta->execute(array($param_tahun, $param_semester_nama));
        $checked_ta = $stmt_check_ta->fetch(PDO::FETCH_ASSOC);

        if ($checked_ta) {
            $default_tahun_akademik = $param_tahun;
            $default_semester_nama = $param_semester_nama;
            $default_semester_id = $checked_ta['id'];
        } else {
            // Jika parameter tidak valid, kembali ke default
            $current_semester_param = $default_tahun_akademik . $default_semester_nama;
        }
    }

    // Ambil semua tahun akademik dan semester untuk dropdown filter
    $stmt_all_ta = $db->query("SELECT DISTINCT tahun, semester FROM tahun_akademik ORDER BY tahun DESC, semester DESC");
    while ($row = $stmt_all_ta->fetch(PDO::FETCH_ASSOC)) {
        $tahun_akademik_options[$row['tahun']] = $row['tahun'];
        if (!in_array($row['semester'], $semester_options)) {
            $semester_options[] = $row['semester'];
        }
    }
    sort($semester_options); // Urutkan semester
    
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch academic year/semester data: ' . $e->getMessage());
    $default_tahun_akademik = date('Y') . '/' . (date('Y') + 1);
    $default_semester_nama = (date('n') >= 9 || date('n') <= 2) ? 'Ganjil' : 'Genap';
    $default_semester_id = 0;
    $tahun_akademik_options = array($default_tahun_akademik => $default_tahun_akademik);
    $semester_options = array('Ganjil', 'Genap');
    $current_semester_param = $default_tahun_akademik . $default_semester_nama;
}

// Get jadwal for current dosen based on selected academic year and semester
$jadwal_list = array();
if ($default_semester_id > 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT j.*, mk.nama_mata_kuliah, mk.kode_mata_kuliah 
                              FROM jadwal j 
                              JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
                              WHERE j.dosen_id = ? AND j.tahun_akademik = ? AND j.semester = ?
                              ORDER BY 
                                CASE hari 
                                    WHEN 'Monday' THEN 1 
                                    WHEN 'Tuesday' THEN 2 
                                    WHEN 'Wednesday' THEN 3 
                                    WHEN 'Thursday' THEN 4 
                                    WHEN 'Friday' THEN 5 
                                    WHEN 'Saturday' THEN 6 
                                    WHEN 'Sunday' THEN 7 
                                END, jam_mulai");
        $stmt->execute(array($dosen_id, $default_tahun_akademik, $default_semester_nama));
        $jadwal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to fetch jadwal list: ' . $e->getMessage());
        $jadwal_list = array();
    }
}

// Get jadwal for editing
$edit_jadwal = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT j.*, mk.nama_mata_kuliah, mk.kode_mata_kuliah 
                              FROM jadwal j JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id 
                              WHERE j.id = ? AND j.dosen_id = ?");
        $stmt->execute(array($edit_id, $dosen_id));
        $edit_jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to fetch jadwal for edit: ' . $e->getMessage());
        $edit_jadwal = null;
    }
}

// Get all mata kuliah for dropdown
$mata_kuliah_options = array();
try {
    $db = getDB();
    $stmt_mk = $db->query("SELECT id, kode_mata_kuliah, nama_mata_kuliah FROM mata_kuliah ORDER BY nama_mata_kuliah ASC");
    $mata_kuliah_options = $stmt_mk->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch mata kuliah options: ' . $e->getMessage());
    $mata_kuliah_options = array();
}

$page_title = "Jadwal Mengajar";
include '../includes/header.php'; 
?>

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $page_title; ?> - Dashboard Dosen</title> 
    <!-- Menggunakan SITE_URL untuk jalur CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css"> 
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/dosen.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
</head> 
<body>     
    <div class="dosen-container">         
        <?php include 'includes/sidebar.php'; ?>                  
        <div class="dosen-content">             
            <div class="dosen-header">                 
                <h1><i class="fas fa-calendar-alt"></i> Jadwal Mengajar</h1>                 
                <div class="dosen-breadcrumb">                     
                    <a href="<?php echo SITE_URL; ?>/dosen/">Dashboard</a> > <span>Jadwal</span> <!-- Menggunakan SITE_URL -->
                </div>             
            </div>             
            <?php 
            $flash = getFlashMessages(); // Menggunakan getFlashMessages()
            if (!empty($flash)): 
                foreach ($flash as $msg):
            ?>
            <div class="alert alert-<?php echo htmlspecialchars($msg['type']); ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo ($msg['type'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($msg['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
                endforeach;
            endif; 
            ?>
            <!-- Semester Selector -->             
            <div class="dosen-card">                 
                <div class="card-header">                     
                    <h3><i class="fas fa-calendar"></i> Pilih Semester</h3>                 
                </div>                 
                <div class="card-body">                     
                    <form method="GET" class="semester-form">                         
                        <div class="form-row">                             
                            <div class="form-group">                                 
                                <label for="semester_select">Tahun Akademik & Semester</label>                                 
                                <select id="semester_select" name="semester" class="form-control" onchange="this.form.submit()">                                     
                                    <?php 
                                    // Mendapatkan semua kombinasi tahun akademik dan semester
                                    $all_ta_sem_options = array();
                                    try {
                                        $db_temp = getDB();
                                        $stmt_all = $db_temp->query("SELECT tahun, semester FROM tahun_akademik ORDER BY tahun DESC, semester DESC");
                                        while($row_all = $stmt_all->fetch(PDO::FETCH_ASSOC)) {
                                            $all_ta_sem_options[$row_all['tahun'] . $row_all['semester']] = $row_all;
                                        }
                                    } catch (Exception $e) {
                                        logMessage('ERROR', 'Failed to fetch all academic year/semester options: ' . $e->getMessage());
                                    }

                                    foreach ($all_ta_sem_options as $key => $sem_data): ?>
                                        <option value="<?php echo htmlspecialchars($sem_data['tahun'] . $sem_data['semester']); ?>" 
                                                <?php echo ($current_semester_param == ($sem_data['tahun'] . $sem_data['semester'])) ? 'selected' : ''; ?>>
                                            Semester <?php echo htmlspecialchars($sem_data['semester']); ?> - <?php echo htmlspecialchars($sem_data['tahun']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>                             
                            </div>                         
                        </div>                     
                    </form>                 
                </div>             
            </div>             
            <!-- Add/Edit Jadwal Form -->             
            <div class="dosen-card">                 
                <div class="card-header">                     
                    <h3><?php echo $edit_jadwal ? 'Edit Jadwal' : 'Tambah Jadwal Baru'; ?></h3>                 
                </div>                 
                <div class="card-body">                     
                    <form method="POST" class="dosen-form">                         
                        <input type="hidden" name="action" value="<?php echo $edit_jadwal ? 'edit' : 'add'; ?>">                         
                        <?php if ($edit_jadwal): ?>                             
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_jadwal['id']); ?>">                         
                        <?php endif; ?>                                                  
                        <div class="form-row">                             
                            <div class="form-group">                                 
                                <label for="mata_kuliah_id">Mata Kuliah *</label>                                 
                                <select id="mata_kuliah_id" name="mata_kuliah_id" class="form-control" required>
                                    <option value="">Pilih Mata Kuliah</option>
                                    <?php foreach ($mata_kuliah_options as $mk_opt): ?>
                                        <option value="<?php echo htmlspecialchars($mk_opt['id']); ?>"
                                                <?php echo (isset($edit_jadwal['mata_kuliah_id']) && $edit_jadwal['mata_kuliah_id'] == $mk_opt['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mk_opt['kode_mata_kuliah'] . ' - ' . $mk_opt['nama_mata_kuliah']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>                             
                            </div>                             
                            <div class="form-group">                                 
                                <label for="hari">Hari *</label>                                 
                                <select id="hari" name="hari" class="form-control" required>                                     
                                    <option value="">Pilih Hari</option>                                     
                                    <option value="Monday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Monday') ? 'selected' : ''; ?>>Senin</option>                                     
                                    <option value="Tuesday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Tuesday') ? 'selected' : ''; ?>>Selasa</option>                                     
                                    <option value="Wednesday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Wednesday') ? 'selected' : ''; ?>>Rabu</option>                                     
                                    <option value="Thursday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Thursday') ? 'selected' : ''; ?>>Kamis</option>                                     
                                    <option value="Friday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Friday') ? 'selected' : ''; ?>>Jumat</option>                                     
                                    <option value="Saturday" <?php echo (isset($edit_jadwal['hari']) && $edit_jadwal['hari'] == 'Saturday') ? 'selected' : ''; ?>>Sabtu</option>                                 
                                </select>                             
                            </div>                         
                        </div>                                                  
                        <div class="form-row">                             
                            <div class="form-group">                                 
                                <label for="jam_mulai">Jam Mulai *</label>                                 
                                <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required                                         
                                        value="<?php echo isset($edit_jadwal['jam_mulai']) ? htmlspecialchars($edit_jadwal['jam_mulai']) : ''; ?>">                             
                            </div>                             
                            <div class="form-group">                                 
                                <label for="jam_selesai">Jam Selesai *</label>                                 
                                <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required                                         
                                        value="<?php echo isset($edit_jadwal['jam_selesai']) ? htmlspecialchars($edit_jadwal['jam_selesai']) : ''; ?>">                             
                            </div>                         
                        </div>                                                  
                        <div class="form-row">                             
                            <div class="form-group">                                 
                                <label for="ruangan">Ruangan *</label>                                 
                                <input type="text" id="ruangan" name="ruangan" class="form-control" required                                         
                                        value="<?php echo isset($edit_jadwal['ruangan']) ? htmlspecialchars($edit_jadwal['ruangan']) : ''; ?>"                                        
                                        placeholder="Contoh: Lab Komputer 1">                             
                            </div>                             
                            <div class="form-group">                                 
                                <label for="tahun_akademik_input">Tahun Akademik</label>                                 
                                <select id="tahun_akademik_input" name="tahun_akademik" class="form-control" required>
                                    <?php foreach ($tahun_akademik_options as $tahun_opt): ?>
                                        <option value="<?php echo htmlspecialchars($tahun_opt); ?>"
                                                <?php echo ($default_tahun_akademik == $tahun_opt) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tahun_opt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>                         
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="semester_input">Semester</label>
                                <select id="semester_input" name="semester_input" class="form-control" required>
                                    <?php foreach ($semester_options as $sem_opt): ?>
                                        <option value="<?php echo htmlspecialchars($sem_opt); ?>"
                                                <?php echo ($default_semester_nama == $sem_opt) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sem_opt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <!-- Kosongkan, untuk alignment form-row -->
                            </div>
                        </div>                                                  
                        <div class="form-actions">                             
                            <button type="submit" class="btn btn-primary">                                 
                                <i class="fas fa-save"></i> <?php echo $edit_jadwal ? 'Update Jadwal' : 'Tambah Jadwal'; ?>                             
                            </button>                             
                            <?php if ($edit_jadwal): ?>                                 
                                <a href="<?php echo SITE_URL; ?>/dosen/jadwal.php?semester=<?php echo urlencode($current_semester_param); ?>" class="btn btn-secondary"> <!-- Menggunakan SITE_URL -->
                                    <i class="fas fa-times"></i> Batal                                 
                                </a>                             
                            <?php endif; ?>                         
                        </div>                     
                    </form>                 
                </div>             
            </div>             
            <!-- Jadwal Table -->             
            <div class="dosen-card mt-4"> <!-- Menambah margin-top -->
                <div class="card-header">                     
                    <h3><i class="fas fa-list"></i> Jadwal Mengajar (<?php echo count($jadwal_list); ?>)</h3>                 
                </div>                 
                <div class="card-body">                     
                    <?php if (empty($jadwal_list)): ?>                         
                        <div class="empty-state">                             
                            <i class="fas fa-calendar-alt"></i>                             
                            <h4>Belum Ada Jadwal</h4>                             
                            <p>Tambahkan jadwal mengajar untuk semester ini.</p>                         
                        </div>                     
                    <?php else: ?>                         
                        <div class="table-responsive">                             
                            <table class="table dosen-table"> <!-- Menambah class table -->
                                <thead>                                     
                                    <tr>                                         
                                        <th>No</th>                                         
                                        <th>Mata Kuliah</th>                                         
                                        <th>Hari</th>                                         
                                        <th>Waktu</th>                                         
                                        <th>Ruangan</th>                                         
                                        <th>Aksi</th>                                     
                                    </tr>                                 
                                </thead>                                 
                                <tbody>                                     
                                    <?php foreach ($jadwal_list as $index => $jadwal): ?>                                         
                                        <tr>                                             
                                            <td><?php echo $index + 1; ?></td>                                             
                                            <td>                                                 
                                                <strong><?php echo htmlspecialchars($jadwal['nama_mata_kuliah']); ?> (<?php echo htmlspecialchars($jadwal['kode_mata_kuliah']); ?>)</strong>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <span class="badge badge-day">                                                     
                                                    <?php echo htmlspecialchars($jadwal['hari']); ?>                                                 
                                                </span>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <div class="time-range">                                                     
                                                    <i class="fas fa-clock"></i>                                                     
                                                    <?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($jadwal['jam_selesai']))); ?>                                                 
                                                </div>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <i class="fas fa-door-open"></i>                                                 
                                                <?php echo htmlspecialchars($jadwal['ruangan']); ?>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <div class="action-buttons">                                                     
                                                    <a href="<?php echo SITE_URL; ?>/dosen/jadwal.php?edit=<?php echo htmlspecialchars($jadwal['id']); ?>&semester=<?php echo urlencode($current_semester_param); ?>"                                                         
                                                        class="btn btn-sm btn-warning">                                                         
                                                        <i class="fas fa-edit"></i>                                                     
                                                    </a>                                                     
                                                    <form method="POST" style="display: inline;"                                                            
                                                        onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">                                                         
                                                        <input type="hidden" name="action" value="delete">                                                         
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($jadwal['id']); ?>">                                                         
                                                        <button type="submit" class="btn btn-sm btn-danger">                                                             
                                                            <i class="fas fa-trash"></i>                                                         
                                                        </button>                                                     
                                                    </form>                                                 
                                                </div>                                             
                                            </td>                                         
                                        </tr>                                     
                                    <?php endforeach; ?>                                 
                                </tbody>                             
                            </table>                         
                        </div>                     
                    <?php endif; ?>                 
                </div>             
            </div>             
            <!-- Weekly Schedule View -->             
            <?php if (!empty($jadwal_list)): ?>                 
                <div class="dosen-card mt-4"> <!-- Menambah margin-top -->
                    <div class="card-header">                     
                        <h3><i class="fas fa-calendar-week"></i> Jadwal Mingguan</h3>                 
                    </div>                 
                    <div class="card-body">                     
                        <div class="weekly-schedule">                             
                            <?php                             
                            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'); // Menggunakan array()
                            $day_names = array('Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu');
                            $schedule_by_day = array(); // Menggunakan array()
                            foreach ($jadwal_list as $jadwal) {                                 
                                $schedule_by_day[$jadwal['hari']][] = $jadwal;                             
                            }                             
                            ?>                                                          
                            <?php foreach ($days as $day): ?>                                 
                                <div class="day-schedule">                                     
                                    <h4><?php echo htmlspecialchars($day_names[$day]); ?></h4>                                     
                                    <?php if (isset($schedule_by_day[$day])): ?>                                         
                                        <?php foreach ($schedule_by_day[$day] as $jadwal): ?>                                             
                                            <div class="schedule-item">                                                 
                                                <div class="schedule-time">                                                     
                                                    <?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($jadwal['jam_selesai']))); ?>                                                 
                                                </div>                                                 
                                                <div class="schedule-subject">                                                     
                                                    <?php echo htmlspecialchars($jadwal['nama_mata_kuliah']); ?>                                                 
                                                </div>                                                 
                                                <div class="schedule-room">                                                     
                                                    <i class="fas fa-door-open"></i>                                                     
                                                    <?php echo htmlspecialchars($jadwal['ruangan']); ?>                                                 
                                                </div>                                             
                                            </div>                                         
                                        <?php endforeach; ?>                                     
                                    <?php else: ?>                                         
                                        <div class="no-schedule">                                             
                                            <i class="fas fa-calendar-times"></i>                                             
                                            Tidak ada jadwal                                         
                                        </div>                                     
                                    <?php endif; ?>                                 
                                </div>                             
                            <?php endforeach; ?>                         
                        </div>                     
                    </div>                 
                </div>             
            <?php endif; ?>         
        </div>     
    </div>     
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> <!-- Menggunakan SITE_URL -->
    <style>         
        .weekly-schedule {             
            display: grid;             
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));             
            gap: 1rem;             
            margin-top: 1rem;         
        }                  
        .day-schedule {             
            background: #f8f9fa;             
            border-radius: 8px;             
            padding: 1rem;             
            border: 1px solid #dee2e6;         
        }                  
        .day-schedule h4 {             
            margin: 0 0 1rem 0;             
            color: #2c3e50;             
            text-align: center;             
            padding-bottom: 0.5rem;             
            border-bottom: 2px solid #3498db;         
        }                  
        .schedule-item {             
            background: white;             
            padding: 0.75rem;             
            border-radius: 6px;             
            margin-bottom: 0.5rem;             
            border-left: 4px solid #3498db;             
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);         
        }                  
        .schedule-item:last-child {             
            border-bottom: none;         
        }                  
        .schedule-time {             
            background: #17a2b8;             
            color: white;             
            padding: 0.5rem 1rem;             
            border-radius: 5px;             
            font-weight: 500;             
            margin-right: 1rem;             
            min-width: 120px;             
            text-align: center;         
        }                  
        .schedule-details h4 {             
            margin-bottom: 0.5rem;             
            color: #333;         
        }                  
        .schedule-details p {             
            margin-bottom: 0.25rem;             
            color: #666;             
            font-size: 0.9rem;         
        }                  
        .mk-code {             
            background: #e9ecef;             
            padding: 0.25rem 0.5rem;             
            border-radius: 3px;             
            font-weight: 500;         
        }                  
        .summary-stats {             
            display: grid;             
            grid-template-columns: repeat(3, 1fr);             
            gap: 2rem;             
            text-align: center;         
        }                  
        .summary-item {             
            padding: 1rem;         
        }                  
        .summary-number {             
            font-size: 2rem;             
            font-weight: bold;             
            color: #17a2b8;             
            margin-bottom: 0.5rem;         
        }                  
        .summary-label {             
            color: #666;             
            font-size: 0.9rem;         
        }                  
        @media (max-width: 768px) {             
            .jadwal-table {                 
                display: none;             
            }                          
            .jadwal-cards {                 
                display: block !important;             
            }                          
            .summary-stats {                 
                grid-template-columns: 1fr;                 
                gap: 1rem;             
            }         
        }     
    </style>          
</body> 
</html>
