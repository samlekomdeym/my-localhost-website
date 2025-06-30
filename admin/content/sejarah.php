<?php 
define('SECURE_ACCESS', true);
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ' . SITE_URL . '/auth/unauthorized.php');
    exit();
}

$page_title = 'Kelola Sejarah & Visi Misi';
$current_page = 'content_sejarah';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $konten = $_POST['konten']; // Allow HTML content
        $visi = sanitize($_POST['visi']);
        $misi = sanitize($_POST['misi']);
        
        $errors = [];
        if (empty($konten)) $errors[] = 'Konten sejarah wajib diisi';
        if (empty($visi)) $errors[] = 'Visi wajib diisi';
        if (empty($misi)) $errors[] = 'Misi wajib diisi';
        
        if (empty($errors)) {
            try {
                // Check if record exists
                $existing = fetchOne("SELECT id FROM sejarah LIMIT 1");
                
                if ($existing) {
                    // Update existing record
                    $query = "UPDATE sejarah SET konten = ?, visi = ?, misi = ?, updated_at = NOW() WHERE id = ?";
                    executeQuery($query, [$konten, $visi, $misi, $existing['id']]);
                } else {
                    // Insert new record
                    $query = "INSERT INTO sejarah (konten, visi, misi, created_at) VALUES (?, ?, ?, NOW())";
                    executeQuery($query, [$konten, $visi, $misi]);
                }
                
                logActivity($_SESSION['user_id'], 'Update Sejarah', 'Updated sejarah, visi, and misi');
                setFlashMessage('success', 'Sejarah, visi, dan misi berhasil diperbarui');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                logMessage('ERROR', 'Failed to update sejarah: ' . $e->getMessage());
                $errors[] = 'Gagal memperbarui data';
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('danger', implode('<br>', $errors));
        }
    }
}

// Get current data
try {
    $sejarah = fetchOne("SELECT * FROM sejarah ORDER BY id DESC LIMIT 1");
    if (!$sejarah) {
        $sejarah = [
            'konten' => '',
            'visi' => '',
            'misi' => ''
        ];
    }
} catch (Exception $e) {
    logMessage('ERROR', 'Failed to fetch sejarah: ' . $e->getMessage());
    $sejarah = [
        'konten' => '',
        'visi' => '',
        'misi' => ''
    ];
}

include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kelola Sejarah & Visi Misi</h1>
            <p class="mb-0 text-muted">Kelola konten sejarah, visi, dan misi kampus</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Sejarah, Visi & Misi</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        
                        <!-- Sejarah Section -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-history me-2"></i>Sejarah Kampus
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Konten Sejarah <span class="text-danger">*</span></label>
                                <textarea name="konten" class="form-control" rows="10" required><?php echo htmlspecialchars($sejarah['konten']); ?></textarea>
                                <small class="text-muted">Ceritakan sejarah berdirinya kampus, perkembangan, dan pencapaian penting</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Visi Section -->
                        <div class="mb-4">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-eye me-2"></i>Visi
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Visi Kampus <span class="text-danger">*</span></label>
                                <textarea name="visi" class="form-control" rows="4" required><?php echo htmlspecialchars($sejarah['visi']); ?></textarea>
                                <small class="text-muted">Tuliskan visi kampus yang menggambarkan cita-cita dan tujuan jangka panjang</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Misi Section -->
                        <div class="mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-bullseye me-2"></i>Misi
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Misi Kampus <span class="text-danger">*</span></label>
                                <textarea name="misi" class="form-control" rows="6" required><?php echo htmlspecialchars($sejarah['misi']); ?></textarea>
                                <small class="text-muted">Tuliskan misi kampus (pisahkan setiap poin dengan baris baru)</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Sejarah Preview -->
                        <div class="col-12 mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-history me-2"></i>Sejarah
                            </h5>
                            <div class="p-3 bg-light rounded">
                                <?php if (!empty($sejarah['konten'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($sejarah['konten'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted fst-italic">Konten sejarah belum diisi</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Visi Preview -->
                        <div class="col-md-6 mb-4">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-eye me-2"></i>Visi
                            </h5>
                            <div class="p-3 bg-light rounded">
                                <?php if (!empty($sejarah['visi'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($sejarah['visi'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted fst-italic">Visi belum diisi</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Misi Preview -->
                        <div class="col-md-6 mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-bullseye me-2"></i>Misi
                            </h5>
                            <div class="p-3 bg-light rounded">
                                <?php if (!empty($sejarah['misi'])): ?>
                                    <?php 
                                    $misi_items = explode("\n", $sejarah['misi']);
                                    if (count($misi_items) > 1): ?>
                                        <ol>
                                            <?php foreach ($misi_items as $item): ?>
                                                <?php if (trim($item)): ?>
                                                    <li><?php echo htmlspecialchars(trim($item)); ?></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php else: ?>
                                        <p><?php echo nl2br(htmlspecialchars($sejarah['misi'])); ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted fst-italic">Misi belum diisi</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tips Penulisan</div>
                            <div class="text-gray-800">
                                <ul class="mb-0">
                                    <li><strong>Sejarah:</strong> Ceritakan kronologi berdirinya kampus, tokoh penting, dan milestone utama</li>
                                    <li><strong>Visi:</strong> Tuliskan dalam 1-2 kalimat yang menggambarkan cita-cita kampus di masa depan</li>
                                    <li><strong>Misi:</strong> Tuliskan dalam bentuk poin-poin konkret, pisahkan dengan baris baru</li>
                                    <li><strong>Format:</strong> Gunakan bahasa formal dan mudah dipahami oleh berbagai kalangan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lightbulb fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.form-control:focus {
    border-color: #36b9cc;
    box-shadow: 0 0 0 0.2rem rgba(54, 185, 204, 0.25);
}

.card {
    border: 0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.bg-light {
    background-color: #f8f9fc !important;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.preview-section {
    max-height: 300px;
    overflow-y: auto;
}
</style>

<script>
// Auto-save functionality (optional)
let autoSaveTimer;

function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        // You can implement auto-save functionality here
        console.log('Auto-save triggered');
    }, 30000); // Auto-save every 30 seconds
}

// Add event listeners to textareas
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', autoSave);
});

// Character counter for textareas
document.querySelectorAll('textarea').forEach(textarea => {
    const maxLength = textarea.getAttribute('maxlength');
    if (maxLength) {
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `${textarea.value.length}/${maxLength}`;
        
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            counter.textContent = `${this.value.length}/${maxLength}`;
            if (this.value.length > maxLength * 0.9) {
                counter.className = 'text-warning float-end';
            } else {
                counter.className = 'text-muted float-end';
            }
        });
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const konten = document.querySelector('textarea[name="konten"]').value.trim();
    const visi = document.querySelector('textarea[name="visi"]').value.trim();
    const misi = document.querySelector('textarea[name="misi"]').value.trim();
    
    if (!konten || !visi || !misi) {
        e.preventDefault();
        alert('Semua field wajib diisi!');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
    submitBtn.disabled = true;
    
    // Re-enable button after 3 seconds (in case of error)
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
