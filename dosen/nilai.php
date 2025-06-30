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
$user_info = getUserById(getUserId()); // Menggunakan getUserById
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

// Handle form submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {     
    if ($_POST['action'] == 'input_nilai') {         
        // Menggunakan isset() dan ternary operator untuk PHP 5.6
        $krs_id = isset($_POST['krs_id']) ? (int)$_POST['krs_id'] : 0;         
        $tugas = isset($_POST['tugas']) ? (float)$_POST['tugas'] : 0.0;         
        $uts = isset($_POST['uts']) ? (float)$_POST['uts'] : 0.0;         
        $uas = isset($_POST['uas']) ? (float)$_POST['uas'] : 0.0;         
        $praktikum = isset($_POST['praktikum']) ? (float)$_POST['praktikum'] : 0.0;         
        $kehadiran = isset($_POST['kehadiran']) ? (float)$_POST['kehadiran'] : 0.0;                  

        // Hitung nilai akhir (sesuai bobot yang Anda tetapkan)
        $nilai_akhir = ($tugas * 0.2) + ($uts * 0.3) + ($uas * 0.4) + ($praktikum * 0.05) + ($kehadiran * 0.05);         
        $grade = getGradeLetter($nilai_akhir); // Menggunakan fungsi getGradeLetter dari functions.php
        // status di tabel nilai tidak ada, tapi nilai_huruf dan nilai_angka ada
        // $status = ($grade != 'E') ? 'lulus' : 'tidak_lulus'; // Status ini mungkin tidak perlu jika hanya ada nilai_huruf

        try {
            $db = getDB(); // Mendapatkan koneksi DB
            // Check if nilai already exists
            $check_query = "SELECT id FROM nilai WHERE krs_id = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute(array($krs_id));
            $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC); // Menggunakan PDO::FETCH_ASSOC
            
            // Konversi nilai huruf ke angka mutu
            $nilai_angka_mutu = 0.0;
            // Gunakan logika yang sama dengan calculateGPA() untuk nilai_angka
            $gradePoints = array('A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0);
            if (isset($gradePoints[$grade])) {
                $nilai_angka_mutu = $gradePoints[$grade];
            }
            
            if ($check_result) { // Jika sudah ada, lakukan UPDATE
                $update_query = "UPDATE nilai SET tugas = ?, uts = ?, uas = ?, praktikum = ?, kehadiran = ?, nilai_akhir = ?, nilai_huruf = ?, nilai_angka = ?, updated_at = NOW() WHERE krs_id = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute(array($tugas, $uts, $uas, $praktikum, $kehadiran, $nilai_akhir, $grade, $nilai_angka_mutu, $krs_id));
                
                if ($update_stmt->rowCount() > 0) { // Cek apakah ada baris yang terpengaruh
                    setFlashMessage('success', 'Nilai berhasil diupdate!');
                } else {
                    setFlashMessage('error', 'Gagal mengupdate nilai atau tidak ada perubahan!');
                }
            } else { // Jika belum ada, lakukan INSERT
                $insert_query = "INSERT INTO nilai (krs_id, tugas, uts, uas, praktikum, kehadiran, nilai_akhir, nilai_huruf, nilai_angka, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->execute(array($krs_id, $tugas, $uts, $uas, $praktikum, $kehadiran, $nilai_akhir, $grade, $nilai_angka_mutu));
                
                if ($insert_stmt->rowCount() > 0) {
                    setFlashMessage('success', 'Nilai berhasil disimpan!');
                } else {
                    setFlashMessage('error', 'Gagal menyimpan nilai!');
                }
            }
            logActivity(getUserId(), 'Input Nilai', "Input nilai untuk KRS ID: {$krs_id}");
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Input nilai failed: ' . $e->getMessage());
            setFlashMessage('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/nilai.php?jadwal_id=" . urlencode(isset($_POST['current_jadwal_id']) ? $_POST['current_jadwal_id'] : '')); // Redirect kembali ke jadwal yang sama
        exit();
    } 
} 

// Get mata kuliah yang diampu dosen 
$mata_kuliah_options = array();
try {
    $db = getDB();
    $mata_kuliah_query = "SELECT DISTINCT mk.id, mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, j.id as jadwal_id                       
                       FROM mata_kuliah mk                        
                       JOIN jadwal j ON mk.id = j.mata_kuliah_id                        
                       WHERE j.dosen_id = ? ORDER BY mk.nama_mata_kuliah ASC";
    $mata_kuliah_stmt = $db->prepare($mata_kuliah_query);
    $mata_kuliah_stmt->execute(array($dosen_id));
    $mata_kuliah_options = $mata_kuliah_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch dosen mata kuliah: ' . $e->getMessage());
    $mata_kuliah_options = array();
}

$selected_jadwal_id = isset($_GET['jadwal_id']) ? (int)$_GET['jadwal_id'] : (isset($mata_kuliah_options[0]['jadwal_id']) ? $mata_kuliah_options[0]['jadwal_id'] : null);

$page_title = "Input Nilai";
include '../includes/header.php'; 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $page_title; ?> - Portal Dosen</title> 
    <!-- Menggunakan SITE_URL untuk jalur CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="<?php echo SITE_URL; ?>/assets/css/dosen.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
</head> 
<body>     
    <div class="dosen-container">         
        <?php include 'includes/sidebar.php'; ?>          
        <div class="main-content">         
            <div class="container-fluid">             
                <div class="row">                 
                    <div class="col-12">                     
                        <div class="page-header">                         
                            <h1>Input Nilai Mahasiswa</h1>                         
                            <p class="text-muted">Kelola nilai mahasiswa untuk mata kuliah yang Anda ampu</p>                     
                        </div>                                          
                        <?php                      
                        $flash = getFlashMessages(); // Menggunakan getFlashMessages()
                        if (!empty($flash)):                      
                        ?>                     
                        <div class="alert alert-<?php echo htmlspecialchars($flash[0]['type']); ?> alert-dismissible fade show" role="alert"> <!-- Mengambil pesan pertama -->
                            <i class="fas fa-<?php echo ($flash[0]['type'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($flash[0]['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        <!-- Filter Mata Kuliah -->                     
                        <div class="card mb-4">                         
                            <div class="card-body">                             
                                <form method="GET" class="row g-3">                                 
                                    <div class="col-md-6">                                     
                                        <label class="form-label">Pilih Mata Kuliah:</label>                                     
                                        <select name="jadwal_id" class="form-select" onchange="this.form.submit()">                                         
                                            <option value="">-- Pilih Mata Kuliah --</option>                                         
                                            <?php foreach ($mata_kuliah_options as $mk): ?>                                         
                                            <option value="<?php echo htmlspecialchars($mk['jadwal_id']); ?>" <?php echo ($selected_jadwal_id == $mk['jadwal_id']) ? 'selected' : ''; ?>>                                             
                                                <?php echo htmlspecialchars($mk['kode_mata_kuliah'] . ' - ' . $mk['nama_mata_kuliah'] . ' (' . $mk['sks'] . ' SKS)'); ?>                                         
                                            </option>                                         
                                            <?php endforeach; ?>                                     
                                        </select>                                 
                                    </div>                             
                                </form>                         
                            </div>                     
                        </div>                                          
                        <?php if ($selected_jadwal_id): ?>                     
                        <!-- Daftar Mahasiswa -->                     
                        <div class="card">                         
                            <div class="card-header">                             
                                <h5>Daftar Mahasiswa</h5>                         
                            </div>                         
                            <div class="card-body">                             
                                <?php                             
                                // Get mahasiswa yang mengambil mata kuliah ini                             
                                $mahasiswa_query = "SELECT k.id as krs_id, m.nim, m.nama_lengkap,                                                 
                                                    n.tugas, n.uts, n.uas, n.praktikum, n.kehadiran, n.nilai_akhir, n.nilai_huruf                                                
                                                    FROM krs k                                                 
                                                    JOIN mahasiswa m ON k.mahasiswa_id = m.id                                                 
                                                    LEFT JOIN nilai n ON k.id = n.krs_id                                                
                                                    WHERE k.jadwal_id = ? AND k.status = 'diambil'                                                
                                                    ORDER BY m.nim";                             
                                $mahasiswa_stmt = $db->prepare($mahasiswa_query);                             
                                $mahasiswa_stmt->execute(array($selected_jadwal_id));                             
                                $mahasiswa_result = $mahasiswa_stmt->fetchAll(PDO::FETCH_ASSOC); // Menggunakan fetchAll(PDO::FETCH_ASSOC)
                                ?>                                                          
                                <div class="table-responsive">                                 
                                    <table class="table table-striped">                                     
                                        <thead>                                         
                                            <tr>                                             
                                                <th>NIM</th>                                             
                                                <th>Nama Mahasiswa</th>                                             
                                                <th>Tugas</th>                                             
                                                <th>UTS</th>                                             
                                                <th>UAS</th>                                             
                                                <th>Praktikum</th>                                             
                                                <th>Kehadiran</th>                                             
                                                <th>Nilai Akhir</th>                                             
                                                <th>Grade</th>                                             
                                                <th>Aksi</th>                                         
                                            </tr>                                     
                                        </thead>                                     
                                        <tbody>                                         
                                            <?php if (empty($mahasiswa_result)): ?>
                                                <tr><td colspan="10" class="text-center">Tidak ada mahasiswa di jadwal ini.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($mahasiswa_result as $mhs): ?>                                         
                                                <tr>                                             
                                                    <td><?php echo htmlspecialchars($mhs['nim']); ?></td>                                             
                                                    <td><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></td>                                             
                                                    <td><?php echo (isset($mhs['tugas']) && $mhs['tugas'] !== null) ? htmlspecialchars(number_format($mhs['tugas'], 2)) : '-'; ?></td>                                             
                                                    <td><?php echo (isset($mhs['uts']) && $mhs['uts'] !== null) ? htmlspecialchars(number_format($mhs['uts'], 2)) : '-'; ?></td>                                             
                                                    <td><?php echo (isset($mhs['uas']) && $mhs['uas'] !== null) ? htmlspecialchars(number_format($mhs['uas'], 2)) : '-'; ?></td>                                             
                                                    <td><?php echo (isset($mhs['praktikum']) && $mhs['praktikum'] !== null) ? htmlspecialchars(number_format($mhs['praktikum'], 2)) : '-'; ?></td>                                             
                                                    <td><?php echo (isset($mhs['kehadiran']) && $mhs['kehadiran'] !== null) ? htmlspecialchars(number_format($mhs['kehadiran'], 2)) : '-'; ?></td>                                             
                                                    <td><?php echo (isset($mhs['nilai_akhir']) && $mhs['nilai_akhir'] !== null) ? htmlspecialchars(number_format($mhs['nilai_akhir'], 2)) : '-'; ?></td>                                             
                                                    <td>                                                 
                                                        <?php if (isset($mhs['nilai_huruf']) && $mhs['nilai_huruf']): ?>                                                 
                                                        <span class="badge bg-<?php echo htmlspecialchars($mhs['nilai_huruf'] == 'A' ? 'success' : (($mhs['nilai_huruf'] == 'E' || $mhs['nilai_huruf'] == 'D') ? 'danger' : 'primary')); ?>">                                                     
                                                            <?php echo htmlspecialchars($mhs['nilai_huruf']); ?>                                                 
                                                        </span>                                                 
                                                        <?php else: ?>                                                 
                                                        -                                                 
                                                        <?php endif; ?>                                             
                                                    </td>                                             
                                                    <td>                                                 
                                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inputNilaiModal"                                                          
                                                                onclick="setNilaiData(<?php echo htmlspecialchars($mhs['krs_id']); ?>, '<?php echo htmlspecialchars($mhs['nim']); ?>', '<?php echo htmlspecialchars($mhs['nama_lengkap']); ?>',                                                          
                                                                <?php echo (isset($mhs['tugas']) && $mhs['tugas'] !== null) ? htmlspecialchars($mhs['tugas']) : 0; ?>,                                                              
                                                                <?php echo (isset($mhs['uts']) && $mhs['uts'] !== null) ? htmlspecialchars($mhs['uts']) : 0; ?>,                                                              
                                                                <?php echo (isset($mhs['uas']) && $mhs['uas'] !== null) ? htmlspecialchars($mhs['uas']) : 0; ?>,                                                              
                                                                <?php echo (isset($mhs['praktikum']) && $mhs['praktikum'] !== null) ? htmlspecialchars($mhs['praktikum']) : 0; ?>,                                                              
                                                                <?php echo (isset($mhs['kehadiran']) && $mhs['kehadiran'] !== null) ? htmlspecialchars($mhs['kehadiran']) : 0; ?>)">                                                     
                                                            <?php echo (isset($mhs['nilai_huruf']) && $mhs['nilai_huruf']) ? 'Edit' : 'Input'; ?> Nilai                                                 
                                                        </button>                                             
                                                    </td>                                         
                                                </tr>                                         
                                                <?php endforeach; ?>
                                            <?php endif; ?>                                     
                                        </tbody>                                 
                                    </table>                             
                                </div>                         
                            </div>                     
                        </div>                     
                        <?php endif; ?>                 
                    </div>             
                </div>         
            </div>     
        </div>          

    <!-- Modal Input Nilai -->     
    <div class="modal fade" id="inputNilaiModal" tabindex="-1" role="dialog"> <!-- Menambahkan role="dialog" -->
        <div class="modal-dialog" role="document"> <!-- Menambahkan role="document" -->
            <div class="modal-content">                 
                <form method="POST">                     
                    <div class="modal-header">                         
                        <h5 class="modal-title">Input Nilai Mahasiswa</h5>                         
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>                     
                    </div>                     
                    <div class="modal-body">                         
                        <input type="hidden" name="action" value="input_nilai">                         
                        <input type="hidden" name="krs_id" id="modal_krs_id">                                                  
                        <input type="hidden" name="current_jadwal_id" value="<?php echo htmlspecialchars($selected_jadwal_id); ?>">

                        <div class="mb-3">                             
                            <label class="form-label">NIM:</label>                             
                            <input type="text" class="form-control" id="modal_nim" readonly>                         
                        </div>                                                  
                        <div class="mb-3">                             
                            <label class="form-label">Nama Mahasiswa:</label>                             
                            <input type="text" class="form-control" id="modal_nama" readonly>                         
                        </div>                                                  
                        <div class="row">                             
                            <div class="col-md-6">                                 
                                <div class="mb-3">                                     
                                    <label class="form-label">Nilai Tugas (0-100):</label>                                     
                                    <input type="number" name="tugas" id="modal_tugas" class="form-control" min="0" max="100" step="0.01" required>                                 
                                </div>                             
                            </div>                             
                            <div class="col-md-6">                                 
                                <div class="mb-3">                                     
                                    <label class="form-label">Nilai UTS (0-100):</label>                                     
                                    <input type="number" name="uts" id="modal_uts" class="form-control" min="0" max="100" step="0.01" required>                                 
                                </div>                             
                            </div>                         
                        </div>                                                  
                        <div class="row">                             
                            <div class="col-md-6">                                 
                                <div class="mb-3">                                     
                                    <label class="form-label">Nilai UAS (0-100):</label>                                     
                                    <input type="number" name="uas" id="modal_uas" class="form-control" min="0" max="100" step="0.01" required>                                 
                                </div>                             
                            </div>                             
                            <div class="col-md-6">                                 
                                <div class="mb-3">                                     
                                    <label class="form-label">Nilai Praktikum (0-100):</label>                                     
                                    <input type="number" name="praktikum" id="modal_praktikum" class="form-control" min="0" max="100" step="0.01" required>                                 
                                </div>                             
                            </div>                         
                        </div>                                                  
                        <div class="mb-3">                             
                            <label class="form-label">Nilai Kehadiran (0-100):</label>                             
                            <input type="number" name="kehadiran" id="modal_kehadiran" class="form-control" min="0" max="100" step="0.01" required>                         
                        </div>                                                  
                        <div class="alert alert-info">                             
                            <small>                                 
                                <strong>Bobot Penilaian:</strong><br>                                 
                                Tugas: 20%, UTS: 30%, UAS: 40%, Praktikum: 5%, Kehadiran: 5%                             
                            </small>                         
                        </div>                     
                    </div>                     
                    <div class="modal-footer">                         
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>                         
                        <button type="submit" class="btn btn-primary">Simpan Nilai</button>                     
                    </div>                 
                </form>             
            </div>         
        </div>     
    </div>          
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>     
    <script>         
        function setNilaiData(krs_id, nim, nama, tugas, uts, uas, praktikum, kehadiran) {             
            document.getElementById('modal_krs_id').value = krs_id;             
            document.getElementById('modal_nim').value = nim;             
            document.getElementById('modal_nama').value = nama;             
            document.getElementById('modal_tugas').value = tugas;             
            document.getElementById('modal_uts').value = uts;             
            document.getElementById('modal_uas').value = uas;             
            document.getElementById('modal_praktikum').value = praktikum;             
            document.getElementById('modal_kehadiran').value = kehadiran;         
        }     
    </script> 
</body> 
</html>
