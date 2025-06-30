<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}
?>
    </main>
    
    <!-- Footer -->
    <footer class="footer bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <?php echo SITE_NAME; ?>
                    </h5>
                    <p class="text-muted">
                        Universitas terdepan yang berkomitmen menghasilkan lulusan berkualitas tinggi, 
                        berkarakter, dan siap menghadapi tantangan global dengan pendidikan yang relevan 
                        dan fasilitas modern.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link me-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link me-2">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link me-2">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link me-2">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Menu Utama</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/about.php">Tentang Kami</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/info.php">Informasi</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/prestasi.php">Prestasi</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Kontak</a></li>
                    </ul>
                </div>
                
                <!-- Programs -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Program Studi</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/pages/programs.php?faculty=ti">Teknologi Informasi</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/programs.php?faculty=bisnis">Bisnis & Manajemen</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/programs.php?faculty=teknik">Teknik</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/programs.php?faculty=sains">Sains</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Kontak Kami</h6>
                    <div class="contact-info">
                        <div class="contact-item d-flex align-items-start mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-3 mt-1"></i>
                            <div>
                                <p class="mb-0 text-muted"><?php echo SITE_ADDRESS; ?></p>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-center mb-3">
                            <i class="fas fa-phone text-primary me-3"></i>
                            <div>
                                <p class="mb-0 text-muted"><?php echo SITE_PHONE; ?></p>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-center mb-3">
                            <i class="fas fa-envelope text-primary me-3"></i>
                            <div>
                                <p class="mb-0 text-muted"><?php echo SITE_EMAIL; ?></p>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-3"></i>
                            <div>
                                <p class="mb-0 text-muted">Senin - Jumat: 08:00 - 17:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4 border-secondary">
            
            <!-- Bottom Footer -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links d-inline">
                        <a href="#" class="text-muted me-3">Privacy Policy</a>
                        <a href="#" class="text-muted me-3">Terms of Service</a>
                        <a href="#" class="text-muted">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="btn btn-primary btn-floating" id="backToTop" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Notification dropdown auto-close
        document.addEventListener('click', function(e) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown && !notificationDropdown.contains(e.target)) {
                const dropdown = bootstrap.Dropdown.getInstance(notificationDropdown);
                if (dropdown) {
                    dropdown.hide();
                }
            }
        });
    </script>
</body>
</html>
