<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/database.php'; 
require_once '../config/session.php'; 
require_once '../config/config.php'; 
require_once '../includes/functions.php'; 

// Memastikan hanya user dengan role 'mahasiswa' yang bisa mengakses
requireRole('mahasiswa'); 

// Dapatkan profil mahasiswa yang sedang login
$user_info = getUserById(getUserId());
$user_profile = null;
$mahasiswa_id = null;
if ($user_info && $user_info['role'] == 'mahasiswa') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nim, ipk FROM mahasiswa WHERE user_id = ?");
        $stmt->execute(array($user_info['id']));
        $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user_profile) {
            $mahasiswa_id = $user_profile['id'];
        }
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get mahasiswa profile for nilai: ' . $e->getMessage());
        $user_profile = null;
    }
}

if (!$mahasiswa_id) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil mahasiswa tidak ditemukan atau tidak lengkap.');
    exit();
}

try {     
    $db = getDB();          

    // Get nilai per semester
    $query = "SELECT                  
                n.id as nilai_id, n.krs_id, n.tugas, n.uts, n.uas, n.praktikum, n.kehadiran, n.nilai_akhir, n.nilai_huruf, n.nilai_angka,                  
                mk.nama_mata_kuliah,                  
                mk.kode_mata_kuliah,                  
                mk.sks,                 
                j.tahun_akademik,
                j.semester               
               FROM nilai n               
               JOIN krs k ON n.krs_id = k.id               
               JOIN jadwal j ON k.jadwal_id = j.id               
               JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id               
               WHERE k.mahasiswa_id = ?               
               ORDER BY j.tahun_akademik DESC, j.semester DESC, mk.nama_mata_kuliah";          

    $stmt = $db->prepare($query);     
    $stmt->execute(array($mahasiswa_id));     
    $nilai_list = $stmt->fetchAll(PDO::FETCH_ASSOC);          

    // Group by semester     
    $nilai_per_semester = array(); // Menggunakan array()
    foreach ($nilai_list as $nilai) {         
        $key = $nilai['tahun_akademik'] . '_' . $nilai['semester'];         
        if (!isset($nilai_per_semester[$key])) {             
            $nilai_per_semester[$key] = array( // Menggunakan array()                 
                'tahun_akademik' => $nilai['tahun_akademik'],                 
                'semester' => $nilai['semester'],                 
                'mata_kuliah' => array() // Menggunakan array()
            );         
        }         
        $nilai_per_semester[$key]['mata_kuliah'][] = $nilai;     
    }      
} catch (Exception $e) {     
    $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();     
    logMessage('ERROR', 'Get nilai error: ' . $e->getMessage());     
    $nilai_per_semester = array(); // Menggunakan array()
} 

$page_title = "Nilai Mata Kuliah"; // Sesuaikan judul halaman
include '../includes/header.php'; 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>     
    <!-- Menggunakan SITE_URL untuk jalur CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">     
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/mahasiswa.css">     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
</head> 
<body>     
    <div class="dashboard mahasiswa-dashboard">         
        <?php include 'includes/sidebar.php'; ?>                  
        <div class="main-content">             
            <div class="dashboard-header">                 
                <h1>Nilai Mata Kuliah</h1>                 
                <div class="user-info">                     
                    <span>NIM: <?php echo htmlspecialchars(isset($user_profile['nim']) ? $user_profile['nim'] : '-'); ?></span>                     
                    <span>IPK: <?php echo number_format(isset($user_profile['ipk']) ? $user_profile['ipk'] : 0, 2); ?></span>                 
                </div>             
            </div>                          

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($nilai_per_semester)): ?>                 
                <div class="card">                     
                    <div class="card-body text-center">                         
                        <i class="fas fa-clipboard-list" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>                         
                        <h3>Belum Ada Nilai</h3>                         
                        <p>Nilai akan muncul setelah dosen menginput nilai mata kuliah yang Anda ambil.</p>                     
                    </div>                 
                </div>             
            <?php else: ?>                 
                <?php foreach ($nilai_per_semester as $semester_key => $semester_data): // Menggunakan $semester_key dan $semester_data ?>                     
                    <div class="transkrip-semester card mb-4"> <!-- Tambah class card mb-4 -->
                        <div class="card-header semester-header">                             
                            <h3>Semester <?php echo htmlspecialchars($semester_data['semester']); ?> - <?php echo htmlspecialchars($semester_data['tahun_akademik']); ?></h3>                             
                            <?php                             
                            // Calculate semester stats                             
                            $total_sks_semester = 0;                             
                            $total_points_semester = 0;                             
                            foreach ($semester_data['mata_kuliah'] as $mk) {                                 
                                $total_sks_semester += $mk['sks'];                                 
                                $total_points_semester += getGradePoints($mk['nilai_huruf']) * $mk['sks']; // Menggunakan nilai_huruf dan getGradePoints()
                            }                             
                            $ips = ($total_sks_semester > 0) ? round((float)$total_points_semester / $total_sks_semester, 2) : 0; // Cast to float
                            ?>                             
                            <div class="semester-stats">                                 
                                <div class="stat-item">                                     
                                    <div class="stat-value"><?php echo htmlspecialchars($total_sks_semester); ?></div>                                     
                                    <div class="stat-label">SKS</div>                                 
                                </div>                                 
                                <div class="stat-item">                                     
                                    <div class="stat-value"><?php echo number_format($ips, 2); ?></div>                                     
                                    <div class="stat-label">IPS</div>                                 
                                </div>                                 
                                <div class="stat-item">                                     
                                    <div class="stat-value"><?php echo htmlspecialchars(count($semester_data['mata_kuliah'])); ?></div>                                     
                                    <div class="stat-label">Mata Kuliah</div>                                 
                                </div>                             
                            </div>                         
                        </div>                                                  
                        <div class="card-body table-container"> <!-- Tambah class card-body -->
                            <table class="table grades-table">                                 
                                <thead>                                     
                                    <tr>                                         
                                        <th>Kode</th>                                         
                                        <th>Mata Kuliah</th>                                         
                                        <th>SKS</th>                                         
                                        <th>Tugas</th>                                         
                                        <th>UTS</th>                                         
                                        <th>UAS</th>                                         
                                        <th>Praktikum</th>                                         
                                        <th>Kehadiran</th>                                         
                                        <th>Nilai Akhir</th>                                         
                                        <th>Grade</th>                                     
                                    </tr>                                 
                                </thead>                                 
                                <tbody>                                     
                                    <?php foreach ($semester_data['mata_kuliah'] as $mk): ?>                                         
                                        <tr>                                             
                                            <td><?php echo htmlspecialchars($mk['kode_mata_kuliah']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($mk['nama_mata_kuliah']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($mk['sks']); ?></td>                                             
                                            <td><?php echo (isset($mk['tugas']) && $mk['tugas'] !== null) ? htmlspecialchars(number_format($mk['tugas'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['uts']) && $mk['uts'] !== null) ? htmlspecialchars(number_format($mk['uts'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['uas']) && $mk['uas'] !== null) ? htmlspecialchars(number_format($mk['uas'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['praktikum']) && $mk['praktikum'] !== null) ? htmlspecialchars(number_format($mk['praktikum'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['kehadiran']) && $mk['kehadiran'] !== null) ? htmlspecialchars(number_format($mk['kehadiran'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['nilai_akhir']) && $mk['nilai_akhir'] !== null) ? htmlspecialchars(number_format($mk['nilai_akhir'], 2)) : '-'; ?></td>                                             
                                            <td>                                                 
                                                <span class="grade-badge grade-<?php echo htmlspecialchars($mk['nilai_huruf']); ?>"><?php echo htmlspecialchars($mk['nilai_huruf']); ?></span>                                             
                                            </td>                                         
                                        </tr>                                     
                                    <?php endforeach; ?>                                 
                                </tbody>                             
                            </table>                         
                        </div>                     
                    </div>                 
                <?php endforeach; ?>             
            <?php endif; ?>         
        </div>     
    </div>          
    <!-- Menggunakan SITE_URL untuk jalur JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> 
</body> 
</html>
