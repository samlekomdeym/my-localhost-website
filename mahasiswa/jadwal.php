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
if ($user_info && $user_info['role'] == 'mahasiswa') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
        $stmt->execute(array($user_info['id']));
        $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get mahasiswa profile: ' . $e->getMessage());
        $user_profile = null;
    }
}

if (!$user_profile) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil mahasiswa tidak ditemukan.');
    exit();
}

try {     
    $db = getDB();     
    $mahasiswa_id = $user_profile['id'];          

    // Get current semester (tahun_akademik)
    // Asumsi tabel tahun_akademik memiliki kolom id, tahun, semester, status
    $current_semester = null;
    $query_current_semester = "SELECT id, tahun, semester FROM tahun_akademik WHERE status = 'aktif' LIMIT 1";
    $stmt_current_semester = $db->query($query_current_semester);
    $current_semester = $stmt_current_semester->fetch(PDO::FETCH_ASSOC);

    $jadwal_list = array(); // Menggunakan array()
    $jadwal_by_day = array(); // Menggunakan array()

    if ($current_semester) {         
        // Get jadwal kuliah         
        $jadwal_query = "SELECT mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, j.hari, j.jam_mulai, j.jam_selesai, j.ruangan, d.nama_lengkap as dosen                          
                          FROM krs k                          
                          JOIN jadwal j ON k.jadwal_id = j.id                          
                          JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id                          
                          JOIN dosen d ON j.dosen_id = d.id                          
                          WHERE k.mahasiswa_id = ? AND j.tahun_akademik = ? AND j.semester = ? AND k.status = 'diambil'                          
                          ORDER BY                               
                            CASE j.hari                                   
                                WHEN 'Monday' THEN 1                                  
                                WHEN 'Tuesday' THEN 2                                  
                                WHEN 'Wednesday' THEN 3                                  
                                WHEN 'Thursday' THEN 4                                  
                                WHEN 'Friday' THEN 5                                  
                                WHEN 'Saturday' THEN 6                                  
                                WHEN 'Sunday' THEN 7                              
                            END, j.jam_mulai";                  
        $jadwal_stmt = $db->prepare($jadwal_query);         
        $jadwal_stmt->execute(array($mahasiswa_id, $current_semester['tahun'], $current_semester['semester'])); // Menggunakan array()
        $jadwal_list = $jadwal_stmt->fetchAll(PDO::FETCH_ASSOC);                  

        // Group by day         
        foreach ($jadwal_list as $jadwal) {             
            $jadwal_by_day[$jadwal['hari']][] = $jadwal;         
        }     
    } 
} catch (Exception $e) {     
    logMessage('ERROR', 'Failed to fetch jadwal kuliah: ' . $e->getMessage());
    $jadwal_by_day = array(); // Menggunakan array()
    $current_semester = null; 
} 

$hari_list = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'); // Menggunakan array()
$day_names_map = array( // Untuk tampilan
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
);

$page_title = "Jadwal Kuliah";
include '../includes/header.php'; 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title><?php echo $page_title; ?> - Mahasiswa</title>     
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
                <h1><i class="fas fa-calendar"></i> Jadwal Kuliah</h1>                 
                <?php if ($current_semester): ?>                     
                    <div class="semester-info">                         
                        <span class="badge badge-primary">                             
                            <?php echo htmlspecialchars($current_semester['tahun']); ?> -                              
                            <?php echo ucfirst(htmlspecialchars($current_semester['semester'])); ?>                         
                        </span>                     
                    </div>                 
                <?php endif; ?>             
            </div>                          

            <?php if (!$current_semester): ?>                 
                <div class="alert alert-warning">                     
                    <i class="fas fa-exclamation-triangle"></i>                     
                    Tidak ada semester aktif saat ini.                 
                </div>             
            <?php elseif (empty($jadwal_list)): ?>                 
                <div class="alert alert-info">                     
                    <i class="fas fa-info-circle"></i>                     
                    Anda belum mengambil mata kuliah untuk semester ini. Silakan lakukan KRS terlebih dahulu.                 
                </div>             
            <?php else: ?>                 
                <!-- Jadwal Table View -->                 
                <div class="jadwal-table">                     
                    <table class="table">                         
                        <thead>                             
                            <tr>                                 
                                <th>Hari</th>                                 
                                <th>Waktu</th>                                 
                                <th>Kode MK</th>                                 
                                <th>Mata Kuliah</th>                                 
                                <th>SKS</th>                                 
                                <th>Ruangan</th>                                 
                                <th>Dosen</th>                             
                            </tr>                         
                        </thead>                         
                        <tbody>                             
                            <?php foreach ($hari_list as $hari_key): // Menggunakan $hari_key untuk loop ?>                                 
                                <?php if (isset($jadwal_by_day[$hari_key])): ?>                                     
                                    <?php foreach ($jadwal_by_day[$hari_key] as $index => $jadwal): ?>                                         
                                        <tr class="hari-<?php echo strtolower($hari_key); ?>">                                             
                                            <?php if ($index == 0): ?>                                                 
                                                <td rowspan="<?php echo count($jadwal_by_day[$hari_key]); ?>" class="hari-cell">                                                     
                                                    <strong><?php echo htmlspecialchars($day_names_map[$hari_key]); ?></strong>                                                 
                                                </td>                                             
                                            <?php endif; ?>                                             
                                            <td>                                                 
                                                <?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($jadwal['jam_selesai']))); ?>                                             
                                            </td>                                             
                                            <td><strong><?php echo htmlspecialchars($jadwal['kode_mata_kuliah']); ?></strong></td>                                             
                                            <td><?php echo htmlspecialchars($jadwal['nama_mata_kuliah']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($jadwal['sks']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($jadwal['dosen']); ?></td>                                         
                                        </tr>                                     
                                    <?php endforeach; ?>                                 
                                <?php endif; ?>                             
                            <?php endforeach; ?>                         
                        </tbody>                     
                    </table>                 
                </div>                                  

                <!-- Jadwal Card View for Mobile -->                 
                <div class="jadwal-cards" style="display: none;">                     
                    <?php foreach ($hari_list as $hari_key): ?>                         
                        <?php if (isset($jadwal_by_day[$hari_key])): ?>                             
                            <div class="day-card">                                 
                                <h3 class="day-header hari-<?php echo strtolower($hari_key); ?>"><?php echo htmlspecialchars($day_names_map[$hari_key]); ?></h3>                                 
                                <div class="day-schedule">                                     
                                    <?php foreach ($jadwal_by_day[$hari_key] as $jadwal): ?>                                         
                                        <div class="schedule-item">                                             
                                            <div class="schedule-time">                                                 
                                                <i class="fas fa-clock"></i>                                                 
                                                <?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($jadwal['jam_selesai']))); ?>                                             
                                            </div>                                             
                                            <div class="schedule-details">                                                 
                                                <h4><?php echo htmlspecialchars($jadwal['nama_mata_kuliah']); ?></h4>                                                 
                                                <p>                                                     
                                                    <span class="mk-code"><?php echo htmlspecialchars($jadwal['kode_mata_kuliah']); ?></span> |                                                     
                                                    <span class="mk-sks"><?php echo htmlspecialchars($jadwal['sks']); ?> SKS</span>                                                 
                                                </p>                                                 
                                                <p>                                                     
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($jadwal['ruangan']); ?> |                                                     
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($jadwal['dosen']); ?>                                                 
                                                </p>                                             
                                            </div>                                         
                                        </div>                                     
                                    <?php endforeach; ?>                                 
                                </div>                             
                            </div>                         
                        <?php endif; ?>                     
                    <?php endforeach; ?>                 
                </div>                                  

                <!-- Summary -->                 
                <div class="jadwal-summary" style="margin-top: 2rem;">                     
                    <div class="card">                         
                        <div class="card-header">                             
                            <h3><i class="fas fa-chart-pie"></i> Ringkasan Jadwal</h3>                         
                        </div>                         
                        <div class="card-body">                             
                            <div class="summary-stats">                                 
                                <div class="summary-item">                                     
                                    <div class="summary-number"><?php echo number_format(count($jadwal_list)); ?></div>                                     
                                    <div class="summary-label">Total Mata Kuliah</div>                                 
                                </div>                                 
                                <div class="summary-item">                                     
                                    <div class="summary-number"><?php echo number_format(array_sum(array_column($jadwal_list, 'sks'))); ?></div>                                     
                                    <div class="summary-label">Total SKS</div>                                 
                                </div>                                 
                                <div class="summary-item">                                     
                                    <div class="summary-number"><?php echo number_format(count($jadwal_by_day)); ?></div>                                     
                                    <div class="summary-label">Hari Kuliah</div>                                 
                                </div>                             
                            </div>                         
                        </div>                     
                    </div>                 
                </div>             
            <?php endif; ?>         
        </div>     
    </div>          
    <style>         
        .hari-cell {             
            background: #f8f9fa;             
            font-weight: bold;             
            text-align: center;             
            vertical-align: middle;         
        }                  
        .day-card {             
            background: white;             
            border-radius: 10px;             
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);             
            margin-bottom: 1.5rem;             
            overflow: hidden;         
        }                  
        .day-header {             
            padding: 1rem;             
            margin: 0;             
            color: white;             
            font-weight: bold;         
        }                  
        .day-schedule {             
            padding: 1rem;         
        }                  
        .schedule-item {             
            display: flex;             
            align-items: center;             
            padding: 1rem 0;             
            border-bottom: 1px solid #e9ecef;         
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
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script> 
</body> 
</html>
