<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/session.php'; 
require_once '../config/database.php'; 
require_once '../config/config.php'; 
require_once '../includes/functions.php'; 

// Memastikan hanya user dengan role 'mahasiswa' yang bisa mengakses
requireRole('mahasiswa'); 

// Dapatkan info user yang login
$user_info = getUserById(getUserId()); // Menggunakan getUserById

// Get mahasiswa data
$mahasiswa = null;
if ($user_info && $user_info['role'] == 'mahasiswa') {
    try {
        $db = getDB();
        $query = "SELECT m.*, u.username, u.email FROM mahasiswa m            
                   JOIN users u ON m.user_id = u.id            
                   WHERE m.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute(array($user_info['id']));
        $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get mahasiswa profile for transkrip: ' . $e->getMessage());
        $mahasiswa = null;
    }
}

if (!$mahasiswa) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil mahasiswa tidak ditemukan.');
    exit();
}

// Get nilai mahasiswa dengan detail mata kuliah
$nilai_list = array();
try {
    $db = getDB();
    $nilai_query = "SELECT mk.kode_mata_kuliah, mk.nama_mata_kuliah, mk.sks, j.semester as semester_nama,
                           n.tugas, n.uts, n.uas, n.praktikum, n.kehadiran, 
                           n.nilai_akhir, n.nilai_huruf, n.nilai_angka,
                           ta.tahun, ta.semester as semester_ta_db
                    FROM nilai n
                    JOIN krs k ON n.krs_id = k.id
                    JOIN jadwal j ON k.jadwal_id = j.id
                    JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
                    JOIN tahun_akademik ta ON j.tahun_akademik = ta.tahun AND j.semester = ta.semester -- Join berdasarkan tahun dan semester string
                    WHERE k.mahasiswa_id = ? AND n.nilai_huruf IS NOT NULL -- Hanya nilai yang sudah diisi
                    ORDER BY ta.tahun ASC, FIELD(j.semester, 'Ganjil', 'Genap') ASC, mk.kode_mata_kuliah ASC"; // Urutkan sesuai tahun dan semester

    $stmt = $db->prepare($nilai_query);
    $stmt->execute(array($mahasiswa['id']));
    $nilai_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch student grades for transcript: ' . $e->getMessage());
    $nilai_list = array();
}


// Hitung statistik dan kelompokkan per semester
$total_sks_kumulatif = 0;
$total_mutu_kumulatif = 0;
$semester_data = array(); // Menggunakan array()

// Grade points map
$gradePointsMap = array('A' => 4.0, 'B+' => 3.5, 'B' => 3.0, 'C+' => 2.5, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0);

foreach ($nilai_list as $row) {
    $semester_key = $row['tahun'] . ' - ' . $row['semester_nama']; // Gunakan tahun dan semester dari jadwal
    if (!isset($semester_data[$semester_key])) {
        $semester_data[$semester_key] = array(
            'tahun_akademik' => $row['tahun'],
            'semester' => $row['semester_nama'],
            'mata_kuliah' => array(),
            'total_sks_semester' => 0,
            'total_mutu_semester' => 0
        );
    }
    $semester_data[$semester_key]['mata_kuliah'][] = $row;
    
    // Hitung untuk IPS dan IPK
    $mutu_mk = isset($gradePointsMap[$row['nilai_huruf']]) ? $gradePointsMap[$row['nilai_huruf']] * $row['sks'] : 0.0;
    
    $semester_data[$semester_key]['total_sks_semester'] += $row['sks'];
    $semester_data[$semester_key]['total_mutu_semester'] += $mutu_mk;

    $total_sks_kumulatif += $row['sks'];
    $total_mutu_kumulatif += $mutu_mk;
}

$ipk = ($total_sks_kumulatif > 0) ? round((float)$total_mutu_kumulatif / $total_sks_kumulatif, 2) : 0.00;

// Update IPK di database mahasiswa
try {
    $db = getDB();
    $update_ipk_query = "UPDATE mahasiswa SET ipk = ?, total_sks = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_ipk_query);
    $update_stmt->execute(array($ipk, $total_sks_kumulatif, $mahasiswa['id']));
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to update student IPK/SKS: ' . $e->getMessage());
}

// Mode cetak
$print_mode = isset($_GET['print']) && $_GET['print'] == '1';

// Fungsi formatTanggalIndonesia (jika belum ada di functions.php)
// Asumsi sudah ada di functions.php. Jika tidak, tambahkan ini di functions.php
/*
function formatTanggalIndonesia($tanggal) {
    $bulan = array (
        1 =>   'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
*/
$page_title = "Transkrip Nilai";
include '../includes/header.php'; 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title><?php echo $page_title; ?> - Portal Mahasiswa</title>     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">     
    <?php if (!$print_mode): ?>     
    <link href="<?php echo SITE_URL; ?>/assets/css/mahasiswa.css" rel="stylesheet">     
    <?php endif; ?>     
    <style>         
        @media print {             
            .no-print { display: none !important; }             
            .print-only { display: block !important; }             
            body { font-size: 12px; }             
            .card { border: none; box-shadow: none; }         
        }         
        .print-only { display: none; }         
        .transkrip-header {             
            text-align: center;             
            margin-bottom: 30px;             
            border-bottom: 2px solid #000;             
            padding-bottom: 20px;         
        }         
        .mahasiswa-info {             
            margin-bottom: 20px;         
        }         
        .nilai-table th, .nilai-table td {             
            border: 1px solid #000;             
            padding: 8px;             
            text-align: center;         
        }         
        .nilai-table th {             
            background-color: #f8f9fa;             
            font-weight: bold;         
        }         
        .semester-header {             
            background-color: #e9ecef;             
            font-weight: bold;         
        }     
    </style> 
</head> 
<body>     
    <?php if (!$print_mode): ?>     
    <?php include 'includes/sidebar.php'; ?>          
    <div class="main-content">         
        <div class="container-fluid">             
            <div class="row">                 
                <div class="col-12">                     
                    <div class="page-header no-print">                         
                        <h1>Transkrip Nilai</h1>                         
                        <p class="text-muted">Lihat dan cetak transkrip nilai Anda</p>                         
                        <div class="d-flex gap-2">                             
                            <a href="<?php echo SITE_URL; ?>/mahasiswa/transkrip.php?print=1" target="_blank" class="btn btn-primary">                                 
                                <i class="fas fa-print"></i> Cetak Transkrip                             
                            </a>                             
                            <button onclick="window.print()" class="btn btn-secondary">                                 
                                <i class="fas fa-download"></i> Print/Save PDF                             
                            </button>                         
                        </div>                     
                    </div>     
    <?php endif; ?>                                          
                    <div class="card">                         
                        <div class="card-body">                             
                            <!-- Header Transkrip -->                             
                            <div class="transkrip-header">                                 
                                <h2>TRANSKRIP AKADEMIK</h2>                                 
                                <h3>FAKULTAS ILMU KOMPUTER</h3>                                 
                                <h4>UNIVERSITAS TEKNOLOGI INDONESIA</h4>                                 
                                <p>Jl. Pendidikan No. 123, Jakarta 12345</p>                             
                            </div>                                                          

                            <!-- Info Mahasiswa -->                             
                            <div class="mahasiswa-info">                                 
                                <div class="row">                                     
                                    <div class="col-md-6">                                         
                                        <table class="table table-borderless">                                             
                                            <tr>                                                 
                                                <td width="150"><strong>Nama</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></td>                                             
                                            </tr>                                             
                                            <tr>                                                 
                                                <td><strong>NIM</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars($mahasiswa['nim']); ?></td>                                             
                                            </tr>                                             
                                            <tr>                                                 
                                                <td><strong>Tempat, Tgl Lahir</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars(isset($mahasiswa['tempat_lahir']) ? $mahasiswa['tempat_lahir'] : '') . ', ' . htmlspecialchars(formatDate(isset($mahasiswa['tanggal_lahir']) ? $mahasiswa['tanggal_lahir'] : '')); ?></td>                                             
                                            </tr>                                         
                                        </table>                                     
                                    </div>                                     
                                    <div class="col-md-6">                                         
                                        <table class="table table-borderless">                                             
                                            <tr>                                                 
                                                <td width="150"><strong>Program Studi</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars($mahasiswa['program_studi']); ?></td> <!-- Menampilkan program_studi dari DB -->
                                            </tr>                                             
                                            <tr>                                                 
                                                <td><strong>Tahun Masuk</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars($mahasiswa['tahun_masuk']); ?></td>                                             
                                            </tr>                                             
                                            <tr>                                                 
                                                <td><strong>Status</strong></td>                                                 
                                                <td>: <?php echo htmlspecialchars(ucfirst($mahasiswa['status_akademik'])); ?></td> <!-- Menampilkan status_akademik dari DB -->
                                            </tr>                                         
                                        </table>                                     
                                    </div>                                 
                                </div>                             
                            </div>                                                          

                            <!-- Tabel Nilai -->                             
                            <div class="table-responsive">                                 
                                <table class="table nilai-table">                                     
                                    <thead>                                         
                                        <tr>                                             
                                            <th rowspan="2">No</th>                                             
                                            <th rowspan="2">Kode MK</th>                                             
                                            <th rowspan="2">Mata Kuliah</th>                                             
                                            <th rowspan="2">SKS</th>                                             
                                            <th colspan="5">Komponen Nilai</th>                                             
                                            <th rowspan="2">Nilai Akhir</th>                                             
                                            <th rowspan="2">Grade</th>                                             
                                            <th rowspan="2">Mutu</th>                                         
                                        </tr>                                         
                                        <tr>                                             
                                            <th>Tugas</th>                                             
                                            <th>UTS</th>                                             
                                            <th>UAS</th>                                             
                                            <th>Praktikum</th>                                             
                                            <th>Kehadiran</th>                                         
                                        </tr>                                     
                                    </thead>                                     
                                    <tbody>                                         
                                        <?php                                          
                                        $no = 1;                                         
                                        // $nilai_grade sudah ada di functions.php (getGradePoints)                                                                                  
                                        foreach ($semester_data as $semester_key => $data_mk_per_semester): // Loop melalui semester yang sudah dikelompokkan
                                        ?>                                         
                                        <tr class="semester-header">                                             
                                            <td colspan="12">SEMESTER <?php echo htmlspecialchars($data_mk_per_semester['semester']); ?> - <?php echo htmlspecialchars($data_mk_per_semester['tahun_akademik']); ?></td>                                         
                                        </tr>                                         
                                        <?php                                          
                                        foreach ($data_mk_per_semester['mata_kuliah'] as $mk):                                              
                                            $mutu_mk = getGradePoints($mk['nilai_huruf']) * $mk['sks']; // Menggunakan fungsi
                                        ?>                                         
                                        <tr>                                             
                                            <td><?php echo htmlspecialchars($no++); ?></td>                                             
                                            <td><?php echo htmlspecialchars($mk['kode_mata_kuliah']); ?></td>                                             
                                            <td style="text-align: left;"><?php echo htmlspecialchars($mk['nama_mata_kuliah']); ?></td>                                             
                                            <td><?php echo htmlspecialchars($mk['sks']); ?></td>                                             
                                            <td><?php echo (isset($mk['tugas']) && $mk['tugas'] !== null) ? htmlspecialchars(number_format($mk['tugas'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['uts']) && $mk['uts'] !== null) ? htmlspecialchars(number_format($mk['uts'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['uas']) && $mk['uas'] !== null) ? htmlspecialchars(number_format($mk['uas'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['praktikum']) && $mk['praktikum'] !== null) ? htmlspecialchars(number_format($mk['praktikum'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['kehadiran']) && $mk['kehadiran'] !== null) ? htmlspecialchars(number_format($mk['kehadiran'], 0)) : '-'; ?></td>                                             
                                            <td><?php echo (isset($mk['nilai_akhir']) && $mk['nilai_akhir'] !== null) ? htmlspecialchars(number_format($mk['nilai_akhir'], 2)) : '-'; ?></td>                                             
                                            <td><strong><?php echo htmlspecialchars($mk['nilai_huruf']); ?></strong></td>                                             
                                            <td><?php echo htmlspecialchars(number_format($mutu_mk, 2)); ?></td>                                         
                                        </tr>                                         
                                        <?php endforeach; ?>                                         
                                        <tr style="background-color: #f8f9fa;">                                             
                                            <td colspan="3"><strong>Jumlah Semester <?php echo htmlspecialchars($data_mk_per_semester['semester']); ?></strong></td>                                             
                                            <td><strong><?php echo htmlspecialchars($data_mk_per_semester['total_sks_semester']); ?></strong></td>                                             
                                            <td colspan="7"></td>                                             
                                            <td><strong><?php echo htmlspecialchars(number_format($data_mk_per_semester['total_mutu_semester'], 2)); ?></strong></td>                                         
                                        </tr>                                         
                                        <?php endforeach; ?>                                                                                  

                                        <!-- Total Keseluruhan -->                                         
                                        <tr style="background-color: #e9ecef; font-weight: bold;">                                             
                                            <td colspan="3"><strong>TOTAL KESELURUHAN</strong></td>                                             
                                            <td><strong><?php echo htmlspecialchars($total_sks_kumulatif); ?></strong></td>                                             
                                            <td colspan="7"></td>                                             
                                            <td><strong><?php echo htmlspecialchars(number_format($total_mutu_kumulatif, 2)); ?></strong></td>                                         
                                        </tr>                                     
                                    </tbody>                                 
                                </table>                             
                            </div>                                                          

                            <!-- Ringkasan -->                             
                            <div class="row mt-4">                                 
                                <div class="col-md-6">                                     
                                    <table class="table table-bordered">                                         
                                        <tr>                                             
                                            <td><strong>Total SKS Lulus</strong></td>                                             
                                            <td><strong><?php echo htmlspecialchars($total_sks_kumulatif); ?></strong></td>                                         
                                        </tr>                                         
                                        <tr>                                             
                                            <td><strong>Total Mutu</strong></td>                                             
                                            <td><strong><?php echo htmlspecialchars(number_format($total_mutu_kumulatif, 2)); ?></strong></td>                                         
                                        </tr>                                         
                                        <tr style="background-color: #e9ecef;">                                             
                                            <td><strong>IPK (Indeks Prestasi Kumulatif)</strong></td>                                             
                                            <td><strong><?php echo htmlspecialchars(number_format($ipk, 2)); ?></strong></td>                                         
                                        </tr>                                     
                                    </table>                                 
                                </div>                                 
                                <div class="col-md-6">                                     
                                    <div class="text-center">                                         
                                        <p>Jakarta, <?php echo htmlspecialchars(formatDate(date('Y-m-d'))); ?></p>                                         
                                        <br><br><br>                                         
                                        <p><strong>Kepala Bagian Akademik</strong></p>                                         
                                        <br>                                         
                                        <p><strong>Dr. Ahmad Wijaya, M.Kom</strong></p>                                         
                                        <p>NIP. 198505152010011001</p>                                     
                                    </div>                                 
                                </div>                             
                            </div>                                                          

                            <!-- Keterangan Grade -->                             
                            <div class="mt-4">                                 
                                <h6><strong>Keterangan Grade:</strong></h6>                                 
                                <div class="row">                                     
                                    <div class="col-md-6">                                         
                                        <ul class="list-unstyled">                                             
                                            <li>A = 85-100 (Sangat Baik) = 4.0</li>                                             
                                            <li>B+ = 80-84 (Baik Sekali) = 3.5</li>                                             
                                            <li>B = 75-79 (Baik) = 3.0</li>                                             
                                            <li>C+ = 70-74 (Cukup Baik) = 2.5</li>                                         
                                        </ul>                                     
                                    </div>                                     
                                    <div class="col-md-6">                                         
                                        <ul class="list-unstyled">                                             
                                            <li>C = 65-69 (Cukup) = 2.0</li>                                             
                                            <li>D = 60-64 (Kurang) = 1.0</li>                                             
                                            <li>E = 0-59 (Gagal) = 0.0</li>                                         
                                        </ul>                                     
                                    </div>                                 
                                </div>                             
                            </div>                         
                        </div>                     
                    </div>                          
    <?php if (!$print_mode): ?>                 
                </div>             
            </div>         
        </div>     
    </div>     
    <?php endif; ?>          
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>     
    <?php if ($print_mode): ?>     
    <script>         
        window.onload = function() {             
            window.print();         
        }     
    </script>     
    <?php endif; ?> 
</body> 
</html>
