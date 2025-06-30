<?php
define('SECURE_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Kontak Kami - ' . SITE_NAME;
$current_page = 'contact';
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = 'Nama wajib diisi';
    } elseif (empty($email)) {
        $error = 'Email wajib diisi';
    } elseif (!validateEmail($email)) {
        $error = 'Format email tidak valid';
    } elseif (empty($subject)) {
        $error = 'Subjek wajib diisi';
    } elseif (empty($message)) {
        $error = 'Pesan wajib diisi';
    } else {
        try {
            // Save contact message to database
            executeQuery("
                INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$name, $email, $phone, $subject, $message]);
            
            // In a real application, you would send email notification here
            logMessage('INFO', "New contact message from: $name ($email)");
            
            $success = 'Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.';
            
            // Clear form data
            $_POST = [];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Contact form error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.';
        }
    }
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Hubungi Kami</h1>
                <p class="lead mb-4">
                    Kami siap membantu Anda dengan informasi lengkap tentang program studi, 
                    pendaftaran, dan layanan akademik lainnya.
                </p>
                <div class="d-flex gap-3">
                    <a href="#contact-form" class="btn btn-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Kirim Pesan
                    </a>
                    <a href="#contact-info" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-map-marker-alt me-2"></i>Lokasi Kami
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="/placeholder.svg?height=400&width=500" alt="Contact Us" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section id="contact-info" class="contact-info-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="display-5 fw-bold mb-4">Informasi Kontak</h2>
                <p class="lead text-muted">
                    Berbagai cara untuk menghubungi kami
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Alamat -->
            <div class="col-lg-4 col-md-6">
                <div class="contact-card text-center p-4 h-100 bg-white rounded shadow-sm">
                    <div class="contact-icon mb-4">
                        <div class="icon-circle bg-primary-soft mx-auto">
                            <i class="fas fa-map-marker-alt text-primary fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="contact-title mb-3">Alamat Kampus</h5>
                    <p class="contact-description text-muted mb-3">
                        <?php echo SITE_ADDRESS; ?>
                    </p>
                    <a href="https://maps.google.com/?q=<?php echo urlencode(SITE_ADDRESS); ?>" 
                       target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-2"></i>Lihat di Maps
                    </a>
                </div>
            </div>
            
            <!-- Telepon -->
            <div class="col-lg-4 col-md-6">
                <div class="contact-card text-center p-4 h-100 bg-white rounded shadow-sm">
                    <div class="contact-icon mb-4">
                        <div class="icon-circle bg-success-soft mx-auto">
                            <i class="fas fa-phone text-success fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="contact-title mb-3">Telepon</h5>
                    <p class="contact-description text-muted mb-3">
                        <?php echo SITE_PHONE; ?>
                    </p>
                    <a href="tel:<?php echo SITE_PHONE; ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-phone me-2"></i>Hubungi Sekarang
                    </a>
                </div>
            </div>
            
            <!-- Email -->
            <div class="col-lg-4 col-md-6">
                <div class="contact-card text-center p-4 h-100 bg-white rounded shadow-sm">
                    <div class="contact-icon mb-4">
                        <div class="icon-circle bg-warning-soft mx-auto">
                            <i class="fas fa-envelope text-warning fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="contact-title mb-3">Email</h5>
                    <p class="contact-description text-muted mb-3">
                        <?php echo SITE_EMAIL; ?>
                    </p>
                    <a href="mailto:<?php echo SITE_EMAIL; ?>" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-envelope me-2"></i>Kirim Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section id="contact-form" class="contact-form-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Kirim Pesan</h2>
                    <p class="lead text-muted">
                        Sampaikan pertanyaan atau saran Anda kepada kami
                    </p>
                </div>
                
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nama Lengkap *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                           placeholder="Masukkan nama lengkap" required>
                                    <div class="invalid-feedback">
                                        Nama lengkap wajib diisi
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           placeholder="Masukkan email" required>
                                    <div class="invalid-feedback">
                                        Email valid wajib diisi
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Nomor Telepon
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           placeholder="Masukkan nomor telepon">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-tag me-2"></i>Subjek *
                                    </label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Pilih subjek</option>
                                        <option value="Informasi Pendaftaran" <?php echo ($_POST['subject'] ?? '') === 'Informasi Pendaftaran' ? 'selected' : ''; ?>>
                                            Informasi Pendaftaran
                                        </option>
                                        <option value="Program Studi" <?php echo ($_POST['subject'] ?? '') === 'Program Studi' ? 'selected' : ''; ?>>
                                            Program Studi
                                        </option>
                                        <option value="Beasiswa" <?php echo ($_POST['subject'] ?? '') === 'Beasiswa' ? 'selected' : ''; ?>>
                                            Beasiswa
                                        </option>
                                        <option value="Fasilitas" <?php echo ($_POST['subject'] ?? '') === 'Fasilitas' ? 'selected' : ''; ?>>
                                            Fasilitas
                                        </option>
                                        <option value="Kerjasama" <?php echo ($_POST['subject'] ?? '') === 'Kerjasama' ? 'selected' : ''; ?>>
                                            Kerjasama
                                        </option>
                                        <option value="Lainnya" <?php echo ($_POST['subject'] ?? '') === 'Lainnya' ? 'selected' : ''; ?>>
                                            Lainnya
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Subjek wajib dipilih
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment me-2"></i>Pesan *
                                    </label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              placeholder="Tulis pesan Anda di sini..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Pesan wajib diisi
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="privacy" required>
                                        <label class="form-check-label" for="privacy">
                                            Saya setuju dengan <a href="#" class="text-decoration-none">kebijakan privasi</a> 
                                            dan <a href="#" class="text-decoration-none">syarat & ketentuan</a> *
                                        </label>
                                        <div class="invalid-feedback">
                                            Anda harus menyetujui kebijakan privasi
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">Pertanyaan yang Sering Diajukan</h2>
                    <p class="lead text-muted">
                        Temukan jawaban untuk pertanyaan umum seputar <?php echo SITE_NAME; ?>
                    </p>
                </div>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                <i class="fas fa-question-circle me-3 text-primary"></i>
                                Bagaimana cara mendaftar sebagai mahasiswa baru?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Untuk mendaftar sebagai mahasiswa baru, Anda dapat mengikuti langkah-langkah berikut:</p>
                                <ol>
                                    <li>Kunjungi halaman <a href="<?php echo SITE_URL; ?>/auth/register.php">pendaftaran online</a></li>
                                    <li>Isi formulir pendaftaran dengan lengkap dan benar</li>
                                    <li>Upload dokumen yang diperlukan (ijazah, transkrip nilai, foto, dll.)</li>
                                    <li>Tunggu proses verifikasi dari tim admisi</li>
                                    <li>Setelah diterima, lakukan pembayaran biaya pendaftaran</li>
                                </ol>
                                <p>Untuk informasi lebih detail, silakan hubungi bagian admisi di <?php echo SITE_PHONE; ?>.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                <i class="fas fa-graduation-cap me-3 text-success"></i>
                                Apa saja program studi yang tersedia?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p><?php echo SITE_NAME; ?> menyediakan 25+ program studi unggulan yang terbagi dalam 4 fakultas:</p>
                                <ul>
                                    <li><strong>Fakultas Teknologi Informasi:</strong> Teknik Informatika, Sistem Informasi, Teknik Komputer, Cyber Security</li>
                                    <li><strong>Fakultas Bisnis & Manajemen:</strong> Manajemen, Akuntansi, Marketing, International Business</li>
                                    <li><strong>Fakultas Teknik:</strong> Teknik Mesin, Teknik Elektro, Teknik Sipil, Teknik Industri</li>
                                    <li><strong>Fakultas Sains:</strong> Matematika, Fisika, Kimia, Biologi, Statistika</li>
                                </ul>
                                <p>Semua program studi telah terakreditasi A dari BAN-PT.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                <i class="fas fa-money-bill-wave me-3 text-warning"></i>
                                Apakah tersedia program beasiswa?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Ya, kami menyediakan berbagai program beasiswa untuk mahasiswa berprestasi:</p>
                                <ul>
                                    <li><strong>Beasiswa Prestasi Akademik:</strong> Hingga 100% untuk lulusan terbaik SMA/SMK</li>
                                    <li><strong>Beasiswa Prestasi Non-Akademik:</strong> Untuk atlet, seniman, dan aktivis berprestasi</li>
                                    <li><strong>Beasiswa Ekonomi:</strong> Untuk mahasiswa dari keluarga kurang mampu</li>
                                    <li><strong>Beasiswa Kemitraan:</strong> Dari perusahaan partner universitas</li>
                                </ul>
                                <p>Informasi lengkap tentang syarat dan cara mendaftar beasiswa dapat diperoleh di bagian kemahasiswaan.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                <i class="fas fa-building me-3 text-info"></i>
                                Bagaimana fasilitas kampus yang tersedia?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p><?php echo SITE_NAME; ?> dilengkapi dengan fasilitas modern dan lengkap:</p>
                                <ul>
                                    <li>Laboratorium komputer dan multimedia canggih</li>
                                    <li>Perpustakaan digital dengan koleksi lengkap</li>
                                    <li>Ruang kuliah ber-AC dengan proyektor</li>
                                    <li>Auditorium dan ruang seminar</li>
                                    <li>Fasilitas olahraga (lapangan basket, futsal, gym)</li>
                                    <li>Kantin dan food court</li>
                                    <li>Area parkir yang luas</li>
                                    <li>Hotspot WiFi di seluruh area kampus</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                                <i class="fas fa-clock me-3 text-danger"></i>
                                Kapan jadwal pendaftaran mahasiswa baru?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Pendaftaran mahasiswa baru untuk tahun akademik 2025/2026 dibuka dalam 3 gelombang:</p>
                                <ul>
                                    <li><strong>Gelombang 1:</strong> 1 Januari - 31 Maret 2025</li>
                                    <li><strong>Gelombang 2:</strong> 1 April - 31 Mei 2025</li>
                                    <li><strong>Gelombang 3:</strong> 1 Juni - 31 Juli 2025</li>
                                </ul>
                                <p>Pendaftaran lebih awal memberikan kesempatan lebih besar untuk mendapatkan beasiswa dan pilihan program studi yang diinginkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="display-5 fw-bold mb-4">Lokasi Kampus</h2>
                <p class="lead text-muted">
                    Temukan kami di lokasi yang strategis dan mudah diakses
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="map-container rounded shadow">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322283!2d106.8195613!3d-6.2087634!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5390917b759%3A0x6b45e67356080477!2sJakarta%2C%20Indonesia!5e0!3m2!1sen!2sid!4v1635123456789!5m2!1sen!2sid" 
                        width="100%" 
                        height="400" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.contact-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft { background-color: rgba(37, 99, 235, 0.1); }
.bg-success-soft { background-color: rgba(5, 150, 105, 0.1); }
.bg-warning-soft { background-color: rgba(217, 119, 6, 0.1); }

.accordion-button {
    font-weight: 500;
    padding: 1.25rem 1.5rem;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(37, 99, 235, 0.1);
    border-color: rgba(37, 99, 235, 0.2);
}

.accordion-body {
    padding: 1.5rem;
}

.map-container {
    overflow: hidden;
    border-radius: 15px;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
    }
    
    .contact-icon i {
        font-size: 1.5rem !important;
    }
}
</style>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (alert.classList.contains('alert-success')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }
    });
}, 8000);
</script>

<?php include '../includes/footer.php'; ?>
