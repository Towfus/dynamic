<?php
require_once '../shared/config.php';
requireLogin();

$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $category = $_POST['category'];
                $description = trim($_POST['description']);
                $website = trim($_POST['website']);
                
                // Handle logo upload
                $logoPath = '';
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                    $uploadDir = '../shared/uploads/partners/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['logo']['name']);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                        $logoPath = 'uploads/partners/' . $fileName;
                    }
                }
                
                if (!empty($name) && !empty($category)) {
                    $stmt = $pdo->prepare("INSERT INTO partners (name, category, logo_url, description, website) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$name, $category, $logoPath, $description, $website])) {
                        $message = 'Partner added successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error adding partner.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $category = $_POST['category'];
                $description = trim($_POST['description']);
                $website = trim($_POST['website']);
                
                // Handle logo upload
                $logoPath = $_POST['current_logo'];
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                    $uploadDir = '../shared/uploads/partners/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['logo']['name']);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                        // Delete old logo if exists
                        if (!empty($_POST['current_logo']) && file_exists('../shared/' . $_POST['current_logo'])) {
                            unlink('../shared/' . $_POST['current_logo']);
                        }
                        $logoPath = 'uploads/partners/' . $fileName;
                    }
                }
                
                if (!empty($name) && !empty($category)) {
                    $stmt = $pdo->prepare("UPDATE partners SET name = ?, category = ?, logo_url = ?, description = ?, website = ? WHERE id = ?");
                    if ($stmt->execute([$name, $category, $logoPath, $description, $website, $id])) {
                        $message = 'Partner updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error updating partner.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("SELECT logo_url FROM partners WHERE id = ?");
                $stmt->execute([$id]);
                $partner = $stmt->fetch();
                
                if ($partner && !empty($partner['logo_url']) && file_exists('../shared/' . $partner['logo_url'])) {
                    unlink('../shared/' . $partner['logo_url']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Partner deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting partner.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Fetch all partners
$stmt = $pdo->query("SELECT * FROM partners ORDER BY category, name");
$partners = $stmt->fetchAll();

// Group partners by category
$partnersByCategory = [];
foreach ($partners as $partner) {
    $partnersByCategory[$partner['category']][] = $partner;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Partners</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPartnerModal">
                    <i class="fas fa-plus"></i> Add Partner
                </button>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Partners by Category -->
            <?php foreach ($partnersByCategory as $category => $categoryPartners): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo ucfirst($category); ?> Partners (<?php echo count($categoryPartners); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($categoryPartners as $partner): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="partner-card">
                                <div class="partner-logo">
                                    <?php if ($partner['logo_url']): ?>
                                    <img src="../shared/<?php echo htmlspecialchars($partner['logo_url']); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?>">
                                    <?php else: ?>
                                    <div class="no-logo">No Logo</div>
                                    <?php endif; ?>
                                </div>
                                <div class="partner-info">
                                    <h6><?php echo htmlspecialchars($partner['name']); ?></h6>
                                    <p class="text-muted small"><?php echo htmlspecialchars($partner['description'] ?: 'No description'); ?></p>
                                    <?php if ($partner['website']): ?>
                                    <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank" class="text-primary small">
                                        <i class="fas fa-external-link-alt"></i> Website
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <div class="partner-actions">
                                    <button class="btn btn-sm btn-outline-primary edit-partner-btn" 
                                            data-partner='<?php echo json_encode($partner); ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this partner?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $partner['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($partners)): ?>
            <div class="text-center py-5">
                <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Partners Yet</h4>
                <p class="text-muted">Add your first partner to get started!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Partner Modal -->
<div class="modal fade" id="addPartnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Partner Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-control" name="category" required>
                            <option value="">Select Category</option>
                            <option value="sustained">Sustained Partners</option>
                            <option value="individual">Individual Partners</option>
                            <option value="strengthened">Strengthened Partners</option>
                            <option value="private">Other Private Partners</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <small class="form-text text-muted">Recommended size: 200x200px</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Website URL</label>
                        <input type="url" class="form-control" name="website" placeholder="https://example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Partner Modal -->
<div class="modal fade" id="editPartnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Partner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <input type="hidden" name="current_logo" id="edit-current-logo">
                    
                    <div class="mb-3">
                        <label class="form-label">Partner Name *</label>
                        <input type="text" class="form-control" name="name" id="edit-name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-control" name="category" id="edit-category" required>
                            <option value="">Select Category</option>
                            <option value="sustained">Sustained Partners</option>
                            <option value="individual">Individual Partners</option>
                            <option value="strengthened">Strengthened Partners</option>
                            <option value="private">Other Private Partners</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Logo</label>
                        <div id="current-logo-preview"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Logo (optional)</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep current logo</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit-description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Website URL</label>
                        <input type="url" class="form-control" name="website" id="edit-website" placeholder="https://example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit partner button clicks
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-partner-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const partner = JSON.parse(this.dataset.partner);
            
            document.getElementById('edit-id').value = partner.id;
            document.getElementById('edit-name').value = partner.name;
            document.getElementById('edit-category').value = partner.category;
            document.getElementById('edit-description').value = partner.description || '';
            document.getElementById('edit-website').value = partner.website || '';
            document.getElementById('edit-current-logo').value = partner.logo_url || '';
            
            // Show current logo preview
            const logoPreview = document.getElementById('current-logo-preview');
            if (partner.logo_url) {
                logoPreview.innerHTML = `<img src="../shared/${partner.logo_url}" alt="${partner.name}" style="max-width: 100px; max-height: 100px;">`;
            } else {
                logoPreview.innerHTML = '<span class="text-muted">No logo uploaded</span>';
            }
            
            const editModal = new bootstrap.Modal(document.getElementById('editPartnerModal'));
            editModal.show();
        });
    });
});
</script>

<style>
.partner-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.partner-logo {
    text-align: center;
    margin-bottom: 10px;
}

.partner-logo img {
    max-width: 80px;
    max-height: 80px;
    object-fit: contain;
}

.no-logo {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 12px;
    color: #6c757d;
}

.partner-info {
    flex: 1;
    text-align: center;
}

.partner-actions {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 10px;
}
</style>

<?php include 'includes/footer.php'; ?>