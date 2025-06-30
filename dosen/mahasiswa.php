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

// Get filter parameters 
// Menggunakan isset() dan ternary operator untuk PHP 5.6
$filter_jurusan = isset($_GET['jurusan']) ? sanitize($_GET['jurusan']) : ''; 
$filter_angkatan = isset($_GET['angkatan']) ? sanitize($_GET['angkatan']) : ''; 
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : ''; 
$search = isset($_GET['search']) ? sanitize($_GET['search']) : ''; 

// Build query with filters 
$where_conditions = array("m.status_akademik != 'dropout'"); // Menggunakan array() dan nama kolom yang benar
$params = array(); // Menggunakan array()

if ($filter_jurusan) {     
    $where_conditions[] = "m.program_studi = ?"; // Menggunakan nama kolom yang benar
    $params[] = $filter_jurusan; 
} 
if ($filter_angkatan) {     
    $where_conditions[] = "m.tahun_masuk = ?"; // Menggunakan nama kolom yang benar
    $params[] = $filter_angkatan; 
} 
if ($filter_status) {     
    $where_conditions[] = "m.status_akademik = ?"; // Menggunakan nama kolom yang benar
    $params[] = $filter_status; 
} 
if ($search) {     
    $where_conditions[] = "(m.nama_lengkap LIKE ? OR m.nim LIKE ?)"; // Menggunakan nama kolom yang benar
    $params[] = "%{$search}%";     
    $params[] = "%{$search}%"; 
} 

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions); 

// Get mahasiswa data with nilai information 
try {
    $db = getDB(); // Mendapatkan koneksi DB

    $query = "     
        SELECT          
            m.id, m.nim, m.nama_lengkap, m.program_studi, m.tahun_masuk, m.status_akademik,
            COALESCE(AVG(n.nilai_akhir), 0) as rata_nilai,
            COUNT(DISTINCT n.id) as total_nilai,
            MAX(n.updated_at) as last_nilai_update     
        FROM mahasiswa m     
        JOIN users u ON m.user_id = u.id
        LEFT JOIN krs k ON m.id = k.mahasiswa_id
        LEFT JOIN nilai n ON k.id = n.krs_id
        LEFT JOIN jadwal j ON k.jadwal_id = j.id AND j.dosen_id = ?
        {$where_clause}     
        GROUP BY m.id, m.nim, m.nama_lengkap, m.program_studi, m.tahun_masuk, m.status_akademik
        ORDER BY m.nama_lengkap ASC 
    "; 
    // Parameter dosen_id harus ditambahkan di awal params
    array_unshift($params, $dosen_id);
    
    $stmt = $db->prepare($query); 
    $stmt->execute($params); 
    $mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    // Get unique values for filters 
    $jurusan_list = fetchAll("SELECT DISTINCT program_studi FROM mahasiswa ORDER BY program_studi");
    $angkatan_list = fetchAll("SELECT DISTINCT tahun_masuk FROM mahasiswa ORDER BY tahun_masuk DESC");
    
    // Get dosen info 
    $dosen_info = fetchOne("SELECT * FROM dosen WHERE id = ?", array($dosen_id));

} catch (Exception $e) {     
    logMessage('ERROR', 'Dosen mahasiswa list error: ' . $e->getMessage());     
    $mahasiswa_list = array(); // Menggunakan array()
    $jurusan_list = array(); // Menggunakan array()
    $angkatan_list = array(); // Menggunakan array()
    $dosen_info = null;
} 

$page_title = "Data Mahasiswa";
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
                <h1><i class="fas fa-users"></i> Data Mahasiswa</h1>                 
                <div class="dosen-breadcrumb">                     
                    <a href="<?php echo SITE_URL; ?>/dosen/">Dashboard</a> > <span>Mahasiswa</span> <!-- Menggunakan SITE_URL -->
                </div>             
            </div>             
            <!-- Dosen Info -->             
            <div class="info-card">                 
                <div class="info-content">                     
                    <h3>Selamat datang, <?php echo htmlspecialchars(isset($dosen_info['nama_lengkap']) ? $dosen_info['nama_lengkap'] : ''); ?></h3>                     
                    <p>NIDN: <?php echo htmlspecialchars(isset($dosen_info['nidn']) ? $dosen_info['nidn'] : ''); ?> | Bidang Keahlian: <?php echo htmlspecialchars(isset($dosen_info['bidang_keahlian']) ? $dosen_info['bidang_keahlian'] : ''); ?></p>                 
                </div>             
            </div>             
            <!-- Search and Filters -->             
            <div class="dosen-card">                 
                <div class="card-header">                     
                    <h3><i class="fas fa-search"></i> Cari & Filter Mahasiswa</h3>                 
                </div>                 
                <div class="card-body">                     
                    <form method="GET" class="filter-form">                         
                        <div class="form-row">                             
                            <div class="form-group">                                 
                                <label for="search">Cari Mahasiswa</label>                                 
                                <input type="text" id="search" name="search" class="form-control"                                         
                                        value="<?php echo htmlspecialchars($search); ?>"                                        
                                        placeholder="Nama atau NIM mahasiswa...">                             
                            </div>                                                          
                            <div class="form-group">                                 
                                <label for="jurusan">Program Studi</label> <!-- Mengganti Jurusan menjadi Program Studi -->
                                <select id="jurusan" name="jurusan" class="form-control">                                     
                                    <option value="">Semua Program Studi</option>                                     
                                    <?php foreach ($jurusan_list as $jurusan): ?>                                         
                                        <option value="<?php echo htmlspecialchars($jurusan['program_studi']); ?>" <?php echo ($filter_jurusan == $jurusan['program_studi']) ? 'selected' : ''; ?>>                                             
                                            <?php echo htmlspecialchars($jurusan['program_studi']); ?>                                         
                                        </option>                                     
                                    <?php endforeach; ?>                                 
                                </select>                             
                            </div>                                                          
                            <div class="form-group">                                 
                                <label for="angkatan">Tahun Masuk</label> <!-- Mengganti Angkatan menjadi Tahun Masuk -->
                                <select id="angkatan" name="angkatan" class="form-control">                                     
                                    <option value="">Semua Tahun Masuk</option>                                     
                                    <?php foreach ($angkatan_list as $angkatan): ?>                                         
                                        <option value="<?php echo htmlspecialchars($angkatan['tahun_masuk']); ?>" <?php echo ($filter_angkatan == $angkatan['tahun_masuk']) ? 'selected' : ''; ?>>                                             
                                            <?php echo htmlspecialchars($angkatan['tahun_masuk']); ?>                                         
                                        </option>                                     
                                    <?php endforeach; ?>                                 
                                </select>                             
                            </div>                                                          
                            <div class="form-group">                                 
                                <label for="status">Status Akademik</label> <!-- Mengganti Status menjadi Status Akademik -->
                                <select id="status" name="status" class="form-control">                                     
                                    <option value="">Semua Status</option>                                     
                                    <option value="aktif" <?php echo ($filter_status == 'aktif') ? 'selected' : ''; ?>>Aktif</option>                                     
                                    <option value="cuti" <?php echo ($filter_status == 'cuti') ? 'selected' : ''; ?>>Cuti</option>                                     
                                    <option value="lulus" <?php echo ($filter_status == 'lulus') ? 'selected' : ''; ?>>Lulus</option>                                 
                                </select>                             
                            </div>                                                          
                            <div class="form-group">                                 
                                <label>&nbsp;</label>                                 
                                <button type="submit" class="btn btn-primary">                                     
                                    <i class="fas fa-search"></i> Cari                                 
                                </button>                                 
                                <a href="<?php echo SITE_URL; ?>/dosen/mahasiswa.php" class="btn btn-secondary">                                     
                                    <i class="fas fa-sync-alt"></i> Reset                                 
                                </a>                             
                            </div>                         
                        </div>                     
                    </form>                 
                </div>             
            </div>             
            <!-- Statistics -->             
            <div class="stats-grid">                 
                <div class="stat-card">                     
                    <div class="stat-icon">                         
                        <i class="fas fa-users"></i>                     
                    </div>                     
                    <div class="stat-content">                         
                        <h3><?php echo number_format(count($mahasiswa_list)); ?></h3>                         
                        <p>Total Mahasiswa</p>                     
                    </div>                 
                </div>                                  
                <div class="stat-card">                     
                    <div class="stat-icon active">                         
                        <i class="fas fa-user-graduate"></i>                     
                    </div>                     
                    <div class="stat-content">                         
                        <h3><?php echo number_format(count(array_filter($mahasiswa_list, function($m) { return isset($m['total_nilai']) && $m['total_nilai'] > 0; }))); ?></h3>                         
                        <p>Sudah Dinilai</p>                     
                    </div>                 
                </div>                                  
                <div class="stat-card">                     
                    <div class="stat-icon warning">                         
                        <i class="fas fa-user-clock"></i>                     
                    </div>                     
                    <div class="stat-content">                         
                        <h3><?php echo number_format(count(array_filter($mahasiswa_list, function($m) { return !isset($m['total_nilai']) || $m['total_nilai'] == 0; }))); ?></h3>                         
                        <p>Belum Dinilai</p>                     
                    </div>                 
                </div>                                  
                <div class="stat-card">                     
                    <div class="stat-icon success">                         
                        <i class="fas fa-chart-line"></i>                     
                    </div>                     
                    <div class="stat-content">                         
                        <h3><?php                              
                            $avg_nilai = 0;
                            if (!empty($mahasiswa_list)) {
                                $total_rata = 0;
                                $count_valid_nilai = 0;
                                foreach ($mahasiswa_list as $mhs_item) {
                                    if (isset($mhs_item['rata_nilai']) && $mhs_item['rata_nilai'] > 0) {
                                        $total_rata += $mhs_item['rata_nilai'];
                                        $count_valid_nilai++;
                                    }
                                }
                                if ($count_valid_nilai > 0) {
                                    $avg_nilai = $total_rata / $count_valid_nilai;
                                }
                            }
                            echo number_format($avg_nilai, 1);                          
                        ?></h3>                         
                        <p>Rata-rata Nilai Mahasiswa</p>                     
                    </div>                 
                </div>             
            </div>             
            <!-- Mahasiswa List -->             
            <div class="dosen-card">                 
                <div class="card-header">                     
                    <h3><i class="fas fa-list"></i> Daftar Mahasiswa (<?php echo number_format(count($mahasiswa_list)); ?>)</h3>                     
                    <div class="card-actions">                         
                        <a href="<?php echo SITE_URL; ?>/dosen/nilai.php" class="btn btn-primary">                             
                            <i class="fas fa-plus"></i> Input Nilai                         
                        </a>                     
                    </div>                 
                </div>                 
                <div class="card-body">                     
                    <?php if (empty($mahasiswa_list)): ?>                         
                        <div class="empty-state">                             
                            <i class="fas fa-users"></i>                             
                            <h4>Tidak Ada Mahasiswa</h4>                             
                            <p>Tidak ada mahasiswa yang sesuai dengan kriteria pencarian.</p>                         
                        </div>                     
                    <?php else: ?>                         
                        <div class="table-responsive">                             
                            <table class="table dosen-table">                                 
                                <thead>                                     
                                    <tr>                                         
                                        <th>No</th>                                         
                                        <th>NIM</th>                                         
                                        <th>Nama Mahasiswa</th>                                         
                                        <th>Program Studi</th>                                         
                                        <th>Tahun Masuk</th>                                         
                                        <th>Status Akademik</th>                                         
                                        <th>Rata-rata Nilai</th>                                         
                                        <th>Total Mata Kuliah Dinilai</th>                                         
                                        <th>Terakhir Update Nilai</th>                                         
                                        <th>Aksi</th>                                     
                                    </tr>                                 
                                </thead>                                 
                                <tbody>                                     
                                    <?php foreach ($mahasiswa_list as $index => $mhs): ?>                                         
                                        <tr>                                             
                                            <td><?php echo $index + 1; ?></td>                                             
                                            <td><strong><?php echo htmlspecialchars($mhs['nim']); ?></strong></td>                                             
                                            <td>                                                 
                                                <div class="student-info">                                                     
                                                    <strong><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></strong>                                                     
                                                    <small><?php echo htmlspecialchars($mhs['email']); ?></small>                                                 
                                                </div>                                             
                                            </td>                                             
                                            <td><?php echo htmlspecialchars($mhs['program_studi']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($mhs['tahun_masuk']); ?></td>                                             
                                            <td>                                                 
                                                <span class="badge badge-<?php echo htmlspecialchars($mhs['status_akademik']); ?>">                                                     
                                                    <?php echo ucfirst(htmlspecialchars($mhs['status_akademik'])); ?>                                                 
                                                </span>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <?php if (isset($mhs['rata_nilai']) && $mhs['rata_nilai'] > 0): ?>                                                     
                                                    <strong class="nilai-<?php echo ($mhs['rata_nilai'] >= 80) ? 'excellent' : (($mhs['rata_nilai'] >= 70) ? 'good' : 'average'); ?>">                                                         
                                                        <?php echo number_format($mhs['rata_nilai'], 1); ?>                                                     
                                                    </strong>                                                 
                                                <?php else: ?>                                                     
                                                    <span class="text-muted">Belum ada</span>                                                 
                                                <?php endif; ?>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <span class="badge badge-info">                                                     
                                                    <?php echo number_format(isset($mhs['total_nilai']) ? $mhs['total_nilai'] : 0); ?> mata kuliah                                                 
                                                </span>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <?php if (isset($mhs['last_nilai_update']) && $mhs['last_nilai_update']): ?>                                                     
                                                    <?php echo formatDateTime($mhs['last_nilai_update']); ?>                                                 
                                                <?php else: ?>                                                     
                                                    <span class="text-muted">-</span>                                                 
                                                <?php endif; ?>                                             
                                            </td>                                             
                                            <td>                                                 
                                                <div class="action-buttons">                                                     
                                                    <a href="<?php echo SITE_URL; ?>/dosen/nilai.php?mahasiswa_id=<?php echo htmlspecialchars($mhs['id']); ?>"                                                         
                                                        class="btn btn-sm btn-primary" title="Input/Edit Nilai">                                                         
                                                        <i class="fas fa-edit"></i>                                                     
                                                    </a>                                                     
                                                    <button onclick="viewStudentDetail(<?php echo htmlspecialchars($mhs['id']); ?>)"                                                              
                                                        class="btn btn-sm btn-info" title="Lihat Detail">                                                         
                                                        <i class="fas fa-eye"></i>                                                     
                                                    </button>                                                 
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
        </div>     
    </div>     
    <!-- Student Detail Modal -->     
    <div class="modal fade" id="studentModal" tabindex="-1" role="dialog"> <!-- Menambahkan role="dialog" untuk Bootstrap 5 -->
        <div class="modal-dialog modal-lg" role="document"> <!-- Menambahkan role="document" -->
            <div class="modal-content">             
                <div class="modal-header">                 
                    <h5 class="modal-title"><i class="fas fa-user"></i> Detail Mahasiswa</h5>                 
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> <!-- Menambahkan aria-label -->
                </div>             
                <div class="modal-body" id="studentModalBody">                 
                    <!-- Content will be loaded here -->             
                </div>         
            </div>     
        </div> 
    </div>     
    <script>         
        function viewStudentDetail(mahasiswaId) {             
            // Menggunakan window.bootstrap.Modal untuk inisialisasi modal
            const modalElement = document.getElementById('studentModal');
            const modal = new window.bootstrap.Modal(modalElement);
            const modalBody = document.getElementById('studentModalBody');          

            modalBody.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>'; // Spinner yang lebih baik
            modal.show();          

            // Load student data via AJAX
            // Asumsi ada file ajax/get_student_detail.php
            fetch(`<?php echo SITE_URL; ?>/dosen/ajax/get_student_detail.php?id=${mahasiswaId}`) // Menggunakan SITE_URL
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text(); // Karena mengembalikan HTML fragment
                })
                .then(data => {                     
                    modalBody.innerHTML = data;                 
                })                 
                .catch(error => {                     
                    modalBody.innerHTML = '<div class="alert alert-danger">Gagal memuat data mahasiswa: ' + error.message + '</div>';                 
                });         
        }
        // Bootstrap 5 sudah punya data-bs-dismiss="modal" jadi closeModal() manual mungkin tidak diperlukan
        // Namun, jika ada custom close logic, bisa dipertahankan.
        /*
        function closeModal() {
            const modalElement = document.getElementById('studentModal');
            const modal = window.bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        window.onclick = function(event) {
            const modal = document.getElementById('studentModal');
            if (event.target == modal) {
                const bsModal = window.bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        }
        */
    </script>     
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> <!-- Menggunakan SITE_URL -->
    <style>         
        .student-info {             
            display: flex;             
            flex-direction: column;         
        }                  
        .student-info small {             
            color: #666;             
            font-size: 0.8em;         
        }                  
        .nilai-excellent { color: #27ae60; }         
        .nilai-good { color: #f39c12; }         
        .nilai-average { color: #e74c3c; }                  
        /* Modal Override - perhatikan ini mungkin konflik dengan Bootstrap default modal style jika tidak diatur dengan benar */
        /* Pastikan style ini hanya untuk modal kustom jika Bootstrap tidak menangani penuh */
        /*
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        */
    </style> 
</body> 
</html>
