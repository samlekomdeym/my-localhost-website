<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}
?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
    
    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            const content = document.getElementById('adminContent');
            const toggleBtn = document.getElementById('sidebarToggle');
            const mobileToggleBtn = document.getElementById('mobileSidebarToggle');
            
            // Desktop sidebar toggle
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    content.classList.toggle('expanded');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }
            
            // Mobile sidebar toggle
            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Restore sidebar state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                content.classList.add('expanded');
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !mobileToggleBtn.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            
            // Load notifications
            loadNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });
        
        // Load notifications
        function loadNotifications() {
            fetch('<?php echo SITE_URL; ?>/api/notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationUI(data.notifications, data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }
        
        // Update notification UI
        function updateNotificationUI(notifications, unreadCount) {
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            
            // Update badge
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
            
            // Update notification list
            if (notifications.length === 0) {
                list.innerHTML = `
                    <li class="dropdown-item-text text-center text-muted py-3">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i><br>
                        Tidak ada notifikasi
                    </li>
                `;
            } else {
                list.innerHTML = notifications.map(notif => `
                    <li>
                        <a class="dropdown-item py-2 ${notif.is_read ? '' : 'bg-light'}" 
                           href="${notif.url || '#'}" 
                           onclick="markAsRead(${notif.id})">
                            <div class="d-flex">
                                <div class="me-2">
                                    <i class="${notif.icon || 'fas fa-bell'} text-${notif.priority || 'primary'}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${notif.title}</div>
                                    <div class="text-muted small">${notif.message}</div>
                                    <div class="text-muted small">${notif.time}</div>
                                </div>
                            </div>
                        </a>
                    </li>
                `).join('');
            }
        }
        
        // Mark notification as read
        function markAsRead(notificationId) {
            fetch('<?php echo SITE_URL; ?>/api/notifications.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
        
        // Mark all notifications as read
        function markAllAsRead() {
            // This would require a separate API endpoint
            fetch('<?php echo SITE_URL; ?>/api/notifications.php?action=mark_all_read', {
                method: 'PUT'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }
        
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 25,
                order: [[0, 'desc']]
            });
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                
                const btn = e.target.classList.contains('btn-delete') ? e.target : e.target.closest('.btn-delete');
                const url = btn.href || btn.getAttribute('data-url');
                const message = btn.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini?';
                
                if (confirm(message)) {
                    if (btn.tagName === 'A') {
                        window.location.href = url;
                    } else {
                        // Handle form submission or AJAX request
                        const form = btn.closest('form');
                        if (form) {
                            form.submit();
                        }
                    }
                }
            }
        });
        
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
        
        // Loading state for buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-loading')) {
                const btn = e.target;
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                btn.disabled = true;
                
                // Re-enable after 3 seconds (adjust as needed)
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 3000);
            }
        });
        
        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Popover initialization
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    </script>
    
    <?php if (isset($custom_js)): ?>
        <script><?php echo $custom_js; ?></script>
    <?php endif; ?>
</body>
</html>
