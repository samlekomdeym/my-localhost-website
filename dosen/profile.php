<?php 
// File ini diakses langsung, jadi definisikan SECURE_ACCESS
define('SECURE_ACCESS', true);
require_once '../config/session.php'; 
require_once '../config/database.php'; 
require_once '../config/config.php'; // Tambahkan ini
require_once '../includes/functions.php'; 

// Memastikan hanya user dengan role 'dosen' yang bisa mengakses
requireRole('dosen'); 

// Dapatkan info user yang login
$user_info = getUserById(getUserId()); // Menggunakan getUserById

// Get dosen data
$dosen = null;
if ($user_info && $user_info['role'] == 'dosen') {
    try {
        $db = getDB();
        $query = "SELECT d.*, u.username, u.email FROM dosen d            
                   JOIN users u ON d.user_id = u.id            
                   WHERE d.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute(array($user_info['id']));
        $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logMessage('ERROR', 'Failed to get dosen profile data: ' . $e->getMessage());
        $dosen = null;
    }
}

if (!$dosen) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil dosen tidak ditemukan.');
    exit();
}

// Handle form submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {     
    if ($_POST['action'] == 'update_profile') {         
        // Menggunakan isset() dan ternary operator untuk PHP 5.6
        $nama_lengkap = sanitize(isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : '');         
        $tempat_lahir = sanitize(isset($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : '');         
        $tanggal_lahir = isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : '';         
        $jenis_kelamin = sanitize(isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '');         
        $alamat = sanitize(isset($_POST['alamat']) ? $_POST['alamat'] : '');         
        $no_telepon = sanitize(isset($_POST['no_telepon']) ? $_POST['no_telepon'] : '');         
        $bidang_keahlian = sanitize(isset($_POST['bidang_keahlian']) ? $_POST['bidang_keahlian'] : ''); // Mengganti bidang_keahlian
        $pendidikan_terakhir = sanitize(isset($_POST['pendidikan_terakhir']) ? $_POST['pendidikan_terakhir'] : '');         
        $jabatan_akademik = sanitize(isset($_POST['jabatan_akademik']) ? $_POST['jabatan_akademik'] : ''); // Mengganti jabatan_akademik
                 
        // Handle file upload         
        $foto = isset($dosen['foto']) ? $dosen['foto'] : null; // Keep existing photo         
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            try {
                // Gunakan fungsi uploadFile dari functions.php
                // Tipe upload 'dosen' untuk path khusus
                $uploaded_file_name = uploadFile($_FILES['foto'], getAllowedImageTypes(), MAX_FILE_SIZE, UPLOAD_PATH . 'dosen/'); 
                if ($uploaded_file_name) {
                    // Delete old photo if exists
                    if ($foto && file_exists(UPLOAD_PATH . 'dosen/' . $foto)) {
                        unlink(UPLOAD_PATH . 'dosen/' . $foto);
                    }
                    $foto = $uploaded_file_name;
                }
            } catch (Exception $e) {
                setFlashMessage('error', 'Gagal mengupload foto: ' . $e->getMessage());
                logMessage('ERROR', 'Dosen profile photo upload error: ' . $e->getMessage());
            }
        }
                 
        // Update dosen data         
        try {
            $db = getDB();
            $update_query = "UPDATE dosen SET nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?,                          
                             jenis_kelamin = ?, alamat = ?, no_telepon = ?, bidang_keahlian = ?,                          
                             pendidikan_terakhir = ?, jabatan_akademik = ?, foto = ? WHERE user_id = ?"; // Sesuaikan kolom
            $update_stmt = $db->prepare($update_query);
            $update_stmt->execute(array($nama_lengkap, $tempat_lahir, $tanggal_lahir,                                  
                                     $jenis_kelamin, $alamat, $no_telepon, $bidang_keahlian,                                  
                                     $pendidikan_terakhir, $jabatan_akademik, $foto, $user_info['id'])); // Pastikan urutan parameter sesuai
            
            if ($update_stmt->rowCount() > 0) { // Cek apakah ada baris yang terpengaruh
                // Update session
                $_SESSION['nama_lengkap'] = $nama_lengkap;                          
                setFlashMessage('success', 'Profile berhasil diupdate!');
                logActivity($user_info['id'], 'Update Profile', 'Update profile dosen');
            } else {
                setFlashMessage('info', 'Tidak ada perubahan pada profile.'); // Jika tidak ada perubahan
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal mengupdate profile: ' . $e->getMessage());
            logMessage('ERROR', 'Dosen profile update error: ' . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/profile.php");
        exit();     

    } elseif ($_POST['action'] == 'change_password') {         
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';         
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';         
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';                  

        try {
            $db = getDB();
            // Get current password hash         
            $pass_query = "SELECT password FROM users WHERE id = ?";         
            $pass_stmt = $db->prepare($pass_query);         
            $pass_stmt->execute(array($user_info['id']));         
            $user_data = $pass_stmt->fetch(PDO::FETCH_ASSOC);                  
            
            if ($user_data && verifyPassword($current_password, $user_data['password'])) {             
                if ($new_password === $confirm_password) {                 
                    if (strlen($new_password) >= PASSWORD_MIN_LENGTH) { // Menggunakan PASSWORD_MIN_LENGTH dari config
                        $new_hash = hashPassword($new_password);                                          
                        $update_pass_query = "UPDATE users SET password = ? WHERE id = ?";                     
                        $update_pass_stmt = $db->prepare($update_pass_query);                     
                        $update_pass_stmt->execute(array($new_hash, $user_info['id']));                                          
                        
                        if ($update_pass_stmt->rowCount() > 0) {
                            setFlashMessage('success', 'Password berhasil diubah!');                         
                            logActivity($user_info['id'], 'Change Password', 'Password changed');                     
                        } else {
                            setFlashMessage('info', 'Password tidak berubah!');
                        }
                    } else {                     
                        setFlashMessage('error', 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter!');                 
                    }             
                } else {                 
                    setFlashMessage('error', 'Konfirmasi password tidak cocok!');             
                }         
            } else {                 
                setFlashMessage('error', 'Password lama tidak benar!');             
            }         
        } catch (Exception $e) {
            logMessage('ERROR', 'Change password error: ' . $e->getMessage());
            setFlashMessage('error', 'Terjadi kesalahan saat mengubah password: ' . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/dosen/profile.php");
        exit();     
    } 
} 

$page_title = "Profile";
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
                            <h1>Profile Dosen</h1>                         
                            <p class="text-muted">Kelola informasi profile Anda</p>                     
                        </div>                                          
                        <?php                      
                        $flash = getFlashMessages(); // Menggunakan getFlashMessages()
                        if (!empty($flash)):                      
                        ?>                     
                        <div class="alert alert-<?php echo htmlspecialchars($flash[0]['type']); ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?php echo ($flash[0]['type'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($flash[0]['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>                                          
                        <div class="row">                         
                            <!-- Profile Info -->                         
                            <div class="col-md-8">                             
                                <div class="card">                                 
                                    <div class="card-header">                                     
                                        <h5>Informasi Profile</h5>                                 
                                    </div>                                 
                                    <div class="card-body">                                     
                                        <form method="POST" enctype="multipart/form-data">                                         
                                            <input type="hidden" name="action" value="update_profile">                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">NIDN:</label>                                                     
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($dosen['nidn']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Username:</label>                                                     
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($dosen['username']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Nama Lengkap:</label>                                                     
                                                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($dosen['nama_lengkap']); ?>" required>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Email:</label>                                                     
                                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($dosen['email']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Tempat Lahir:</label>                                                     
                                                        <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($dosen['tempat_lahir']); ?>">                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Tanggal Lahir:</label>                                                     
                                                        <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($dosen['tanggal_lahir']); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Jenis Kelamin:</label>                                                     
                                                        <select name="jenis_kelamin" class="form-select">                                                         
                                                            <option value="L" <?php echo (isset($dosen['jenis_kelamin']) && $dosen['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>                                                         
                                                            <option value="P" <?php echo (isset($dosen['jenis_kelamin']) && $dosen['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>                                                     
                                                        </select>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">No. Telepon:</label>                                                     
                                                        <input type="text" name="no_telepon" class="form-control" value="<?php echo htmlspecialchars($dosen['no_telepon']); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Alamat:</label>                                             
                                                <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars(isset($dosen['alamat']) ? $dosen['alamat'] : ''); ?></textarea>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Bidang Keahlian:</label>                                                     
                                                        <input type="text" name="bidang_keahlian" class="form-control" value="<?php echo htmlspecialchars($dosen['bidang_keahlian']); ?>">                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Pendidikan Terakhir:</label>                                                     
                                                        <input type="text" name="pendidikan_terakhir" class="form-control" value="<?php echo htmlspecialchars($dosen['pendidikan_terakhir']); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Jabatan Akademik:</label> <!-- Mengganti 'Jabatan' -->
                                                <input type="text" name="jabatan_akademik" class="form-control" value="<?php echo htmlspecialchars($dosen['jabatan_akademik']); ?>"> <!-- Mengganti 'jabatan' -->
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Foto Profile:</label>                                             
                                                <input type="file" name="foto" class="form-control" accept="image/*">                                             
                                                <?php if (isset($dosen['foto']) && $dosen['foto']): ?>                                             
                                                <small class="text-muted">File saat ini: <?php echo htmlspecialchars($dosen['foto']); ?></small>                                             
                                                <?php endif; ?>                                         
                                            </div>                                                                                  
                                            <button type="submit" class="btn btn-primary">Update Profile</button>                                     
                                        </form>                                 
                                    </div>                             
                                </div>                         
                            </div>                                                  

                            <!-- Photo & Password -->                         
                            <div class="col-md-4">                             
                                <!-- Current Photo -->                             
                                <div class="card mb-4">                                 
                                    <div class="card-header">                                     
                                        <h5>Foto Profile</h5>                                 
                                    </div>                                 
                                    <div class="card-body text-center">                                     
                                        <?php if (isset($dosen['foto']) && $dosen['foto'] && file_exists(UPLOAD_PATH . 'dosen/' . $dosen['foto'])): // Menggunakan UPLOAD_PATH ?>                                     
                                        <img src="<?php echo UPLOAD_URL; ?>dosen/<?php echo htmlspecialchars($dosen['foto']); ?>" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">                                     
                                        <?php else: ?>                                     
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">                                         
                                            <i class="fas fa-user fa-4x text-white"></i>                                     
                                        </div>                                     
                                        <?php endif; ?>                                 
                                    </div>                             
                                </div>                                                          

                                <!-- Change Password -->                             
                                <div class="card">                                 
                                    <div class="card-header">                                     
                                        <h5>Ubah Password</h5>                                 
                                    </div>                                 
                                    <div class="card-body">                                     
                                        <form method="POST">                                         
                                            <input type="hidden" name="action" value="change_password">                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Password Lama:</label>                                             
                                                <input type="password" name="current_password" class="form-control" required>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Password Baru:</label>                                             
                                                <input type="password" name="new_password" class="form-control" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Konfirmasi Password Baru:</label>                                             
                                                <input type="password" name="confirm_password" class="form-control" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" required>                                         
                                            </div>                                                                                  
                                            <button type="submit" class="btn btn-warning w-100">Ubah Password</button>                                     
                                        </form>                                 
                                    </div>                             
                                </div>                         
                            </div>                     
                        </div>                 
                    </div>             
                </div>         
            </div>     
        </div>          
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> 
</body> 
</html>
