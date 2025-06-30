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
        logMessage('ERROR', 'Failed to get mahasiswa profile data: ' . $e->getMessage());
        $mahasiswa = null;
    }
}

if (!$mahasiswa) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php?msg=Profil mahasiswa tidak ditemukan.');
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
        $nama_orang_tua = sanitize(isset($_POST['nama_orang_tua']) ? $_POST['nama_orang_tua'] : '');         
        $pekerjaan_orang_tua = sanitize(isset($_POST['pekerjaan_orang_tua']) ? $_POST['pekerjaan_orang_tua'] : '');         
        $asal_sekolah = sanitize(isset($_POST['asal_sekolah']) ? $_POST['asal_sekolah'] : '');                  
        
        // Handle file upload         
        $foto = isset($mahasiswa['foto']) ? $mahasiswa['foto'] : null; // Keep existing photo         
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            try {
                // Gunakan fungsi uploadFile dari functions.php
                // Tipe upload 'mahasiswa' untuk path khusus
                $uploaded_file_name = uploadFile($_FILES['foto'], getAllowedImageTypes(), MAX_FILE_SIZE, UPLOAD_PATH . 'mahasiswa/'); 
                if ($uploaded_file_name) {
                    // Delete old photo if exists
                    if ($foto && file_exists(UPLOAD_PATH . 'mahasiswa/' . $foto)) {
                        unlink(UPLOAD_PATH . 'mahasiswa/' . $foto);
                    }
                    $foto = $uploaded_file_name;
                }
            } catch (Exception $e) {
                setFlashMessage('error', 'Gagal mengupload foto: ' . $e->getMessage());
                logMessage('ERROR', 'Mahasiswa profile photo upload error: ' . $e->getMessage());
            }
        }
                 
        // Update mahasiswa data         
        try {
            $db = getDB();
            $update_query = "UPDATE mahasiswa SET nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?,                          
                             jenis_kelamin = ?, alamat = ?, no_telepon = ?, nama_orang_tua = ?,                          
                             pekerjaan_orang_tua = ?, asal_sekolah = ?, foto = ? WHERE user_id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->execute(array($nama_lengkap, $tempat_lahir, $tanggal_lahir,                                  
                                     $jenis_kelamin, $alamat, $no_telepon, $nama_orang_tua,                                  
                                     $pekerjaan_orang_tua, $asal_sekolah, $foto, $user_info['id']));
            
            if ($update_stmt->rowCount() > 0) { // Cek apakah ada baris yang terpengaruh
                // Update session
                $_SESSION['username'] = $nama_lengkap; // Update username di sesi dengan nama_lengkap jika itu yang ditampilkan
                                        
                setFlashMessage('success', 'Profile berhasil diupdate!');
                logActivity($user_info['id'], 'Update Profile', 'Update profile mahasiswa');
            } else {
                setFlashMessage('info', 'Tidak ada perubahan pada profile.');
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal mengupdate profile: ' . $e->getMessage());
            logMessage('ERROR', 'Mahasiswa profile update error: ' . $e->getMessage());
        }
        header("Location: " . SITE_URL . "/mahasiswa/profile.php");
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
        header("Location: " . SITE_URL . "/mahasiswa/profile.php");
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
    <title><?php echo $page_title; ?> - Portal Mahasiswa</title>     
    <!-- Menggunakan SITE_URL untuk jalur CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">     
    <link href="<?php echo SITE_URL; ?>/assets/css/mahasiswa.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head> 
<body>     
    <div class="dosen-container"> <!-- Perhatikan class ini, harusnya mahasiswa-layout atau konsisten -->
        <?php include 'includes/sidebar.php'; ?>          
        <div class="main-content">         
            <div class="container-fluid">             
                <div class="row">                 
                    <div class="col-12">                     
                        <div class="page-header">                         
                            <h1>Profile Mahasiswa</h1>                         
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
                                                        <label class="form-label">NIM:</label>                                                     
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Username:</label>                                                     
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['username']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Nama Lengkap:</label>                                                     
                                                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>" required>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Email:</label>                                                     
                                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Tempat Lahir:</label>                                                     
                                                        <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['tempat_lahir']) ? $mahasiswa['tempat_lahir'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Tanggal Lahir:</label>                                                     
                                                        <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['tanggal_lahir']) ? $mahasiswa['tanggal_lahir'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Jenis Kelamin:</label>                                                     
                                                        <select name="jenis_kelamin" class="form-select">                                                         
                                                            <option value="L" <?php echo (isset($mahasiswa['jenis_kelamin']) && $mahasiswa['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>                                                         
                                                            <option value="P" <?php echo (isset($mahasiswa['jenis_kelamin']) && $mahasiswa['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>                                                     
                                                        </select>                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">No. Telepon:</label>                                                     
                                                        <input type="text" name="no_telepon" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['no_telepon']) ? $mahasiswa['no_telepon'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Alamat:</label>                                             
                                                <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars(isset($mahasiswa['alamat']) ? $mahasiswa['alamat'] : ''); ?></textarea>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Nama Orang Tua:</label>                                                     
                                                        <input type="text" name="nama_orang_tua" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['nama_orang_tua']) ? $mahasiswa['nama_orang_tua'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Pekerjaan Orang Tua:</label>                                                     
                                                        <input type="text" name="pekerjaan_orang_tua" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['pekerjaan_orang_tua']) ? $mahasiswa['pekerjaan_orang_tua'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="row">                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Asal Sekolah:</label>                                                     
                                                        <input type="text" name="asal_sekolah" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['asal_sekolah']) ? $mahasiswa['asal_sekolah'] : ''); ?>">                                                 
                                                    </div>                                             
                                                </div>                                             
                                                <div class="col-md-6">                                                 
                                                    <div class="mb-3">                                                     
                                                        <label class="form-label">Tahun Masuk:</label>                                                     
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars(isset($mahasiswa['tahun_masuk']) ? $mahasiswa['tahun_masuk'] : ''); ?>" readonly>                                                 
                                                    </div>                                             
                                                </div>                                         
                                            </div>                                                                                  
                                            <div class="mb-3">                                             
                                                <label class="form-label">Foto Profile:</label>                                             
                                                <input type="file" name="foto" class="form-control" accept="image/*">                                             
                                                <?php if (isset($mahasiswa['foto']) && $mahasiswa['foto']): ?>                                             
                                                <small class="text-muted">File saat ini: <?php echo htmlspecialchars($mahasiswa['foto']); ?></small>                                             
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
                                        <?php if (isset($mahasiswa['foto']) && $mahasiswa['foto'] && file_exists(UPLOAD_PATH . 'mahasiswa/' . $mahasiswa['foto'])): // Menggunakan UPLOAD_PATH ?>                                     
                                        <img src="<?php echo UPLOAD_URL; ?>mahasiswa/<?php echo htmlspecialchars($mahasiswa['foto']); ?>" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">                                     
                                        <?php else: ?>                                     
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">                                         
                                            <i class="fas fa-user fa-4x text-white"></i>                                     
                                        </div>                                     
                                        <?php endif; ?>                                 
                                    </div>                             
                                </div>                                                          

                                <!-- Academic Info -->                             
                                <div class="card mb-4">                                 
                                    <div class="card-header">                                     
                                        <h5>Informasi Akademik</h5>                                 
                                    </div>                                 
                                    <div class="card-body">                                     
                                        <div class="row">                                         
                                            <div class="col-6">                                             
                                                <strong>IPK:</strong>                                         
                                            </div>                                         
                                            <div class="col-6">                                             
                                                <?php echo number_format(isset($mahasiswa['ipk']) ? $mahasiswa['ipk'] : 0, 2); ?>                                         
                                            </div>                                         
                                        </div>                                         
                                        <div class="row">                                         
                                            <div class="col-6">                                             
                                                <strong>Total SKS:</strong>                                         
                                            </div>                                         
                                            <div class="col-6">                                             
                                                <?php echo number_format(isset($mahasiswa['total_sks']) ? $mahasiswa['total_sks'] : 0); ?>                                         
                                            </div>                                         
                                        </div>                                 
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
