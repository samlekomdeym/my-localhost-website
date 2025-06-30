<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
            <div class="brand-logo me-3">
                <i class="fas fa-graduation-cap text-primary fs-2"></i>
            </div>
            <div class="brand-text">
                <div class="brand-name fw-bold fs-4 text-gradient"><?php echo SITE_NAME; ?></div>
                <div class="brand-tagline small text-muted">Excellence in Education</div>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-info-circle me-1"></i>Tentang
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/sejarah.php">
                            <i class="fas fa-history me-2"></i>Sejarah
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/about.php">
                            <i class="fas fa-university me-2"></i>Profil Universitas
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/contact.php">
                            <i class="fas fa-phone me-2"></i>Kontak
                        </a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/info.php">
                        <i class="fas fa-newspaper me-1"></i>Informasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/prestasi.php">
                        <i class="fas fa-trophy me-1"></i>Prestasi
                    </a>
                </li>
            </ul>

            <!-- Right Side Menu -->
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" id="notification-count" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 300px;">
                            <li><h6 class="dropdown-header">Notifikasi</h6></li>
                            <div id="notification-list">
                                <li><span class="dropdown-item-text text-center text-muted py-3">Tidak ada notifikasi</span></li>
                            </div>
                        </ul>
                    </li>

                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user-circle fs-4 text-primary"></i>
                            </div>
                            <div class="user-info d-none d-md-block">
                                <div class="user-name small fw-semibold"><?php echo htmlspecialchars(getUsername()); ?></div>
                                <div class="user-role small text-muted"><?php echo ucfirst(getRole()); ?></div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars(getUsername()); ?>
                            </h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/<?php echo getRole(); ?>/">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/<?php echo getRole(); ?>/profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Guest Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-2" href="<?php echo SITE_URL; ?>/auth/register.php">
                            <i class="fas fa-user-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pencarian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="search-container">
                    <input type="text" id="globalSearch" class="form-control" placeholder="Cari informasi, prestasi, atau konten lainnya...">
                    <div id="searchResults" class="search-results mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
