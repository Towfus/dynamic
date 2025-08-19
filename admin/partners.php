<?php
// Authentication check would go here
require_once '../config/database.php';
require_once '../helpers/file_upload.php';
$db = new Database();
$conn = $db->getConnection();

include 'admin/impact-stories.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_partner'])) {
        // Handle logo upload
        $uploadResult = uploadImage($_FILES['logo'], 'partners');
        
        if ($uploadResult['success']) {
            // Add new partner
            $query = "INSERT INTO partners 
                     (name, logo_url, category, description, website, status, sort_order) 
                     VALUES 
                     (:name, :logo_url, :category, :description, :website, :status, :sort_order)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':logo_url' => $uploadResult['file_path'],
                ':category' => $_POST['category'],
                ':description' => $_POST['description'],
                ':website' => $_POST['website'],
                ':status' => $_POST['status'],
                ':sort_order' => $_POST['sort_order']
            ]);
            
            $_SESSION['message'] = "Partner added successfully!";
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } elseif (isset($_POST['update_partner'])) {
        // Handle logo update if new logo is uploaded
        $logo_url = $_POST['existing_logo'];
        
        if (!empty($_FILES['logo']['name'])) {
            $uploadResult = uploadImage($_FILES['logo'], 'partners');
            if ($uploadResult['success']) {
                $logo_url = $uploadResult['file_path'];
                // Delete old logo if needed
                if (file_exists($_POST['existing_logo'])) {
                    unlink($_POST['existing_logo']);
                }
            } else {
                $_SESSION['error'] = $uploadResult['error'];
            }
        }
        
        // Update partner
        $query = "UPDATE partners SET 
                 name = :name,
                 logo_url = :logo_url,
                 category = :category,
                 description = :description,
                 website = :website,
                 status = :status,
                 sort_order = :sort_order
                 WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':name' => $_POST['name'],
            ':logo_url' => $logo_url,
            ':category' => $_POST['category'],
            ':description' => $_POST['description'],
            ':website' => $_POST['website'],
            ':status' => $_POST['status'],
            ':sort_order' => $_POST['sort_order'],
            ':id' => $_POST['partner_id']
        ]);
        
        $_SESSION['message'] = "Partner updated successfully!";
    } elseif (isset($_POST['delete_partner'])) {
        // First get logo path to delete it
        $query = "SELECT logo_url FROM partners WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['partner_id']]);
        $partner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the logo file
        if ($partner && file_exists($partner['logo_url'])) {
            unlink($partner['logo_url']);
        }
        
        // Delete the partner
        $query = "DELETE FROM partners WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['partner_id']]);
        
        $_SESSION['message'] = "Partner deleted successfully!";
    }
    
    header("Location: partners.php");
    exit();
}

// Get all partners for display
$query = "SELECT * FROM partners ORDER BY category, sort_order, name";
$stmt = $conn->prepare($query);
$stmt->execute();
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Partners</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo-preview {
            max-width: 150px;
            max-height: 100px;
            object-fit: contain;
        }
        .category-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Manage Partners</h1>
        
        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Add New Partner Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Add New Partner</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Partner Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="sustained">Sustained Partners</option>
                                    <option value="individual">Individual Partners</option>
                                    <option value="strengthened">Strengthened Partners</option>
                                    <option value="other">Other Private Partners</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="website" class="form-label">Website URL</label>
                                <input type="url" class="form-control" id="website" name="website" placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Partner Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                        <small class="text-muted">Recommended size: 300x200 pixels (transparent PNG preferred)</small>
                    </div>
                    
                    <button type="submit" name="add_partner" class="btn btn-primary">Add Partner</button>
                </form>
            </div>
        </div>
        
        <!-- Partners List -->
        <div class="card">
            <div class="card-header">
                <h2>Current Partners</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Website</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partners as $partner): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($partner['logo_url']) ?>" 
                                         alt="<?= htmlspecialchars($partner['name']) ?>" 
                                         class="logo-preview">
                                </td>
                                <td><?= htmlspecialchars($partner['name']) ?></td>
                                <td>
                                    <span class="badge bg-secondary category-badge">
                                        <?= ucfirst($partner['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($partner['website']): ?>
                                        <a href="<?= htmlspecialchars($partner['website']) ?>" target="_blank">Visit</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= $partner['sort_order'] ?></td>
                                <td>
                                    <?php if ($partner['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="<?= $partner['id'] ?>"
                                            data-name="<?= htmlspecialchars($partner['name']) ?>"
                                            data-category="<?= htmlspecialchars($partner['category']) ?>"
                                            data-description="<?= htmlspecialchars($partner['description']) ?>"
                                            data-website="<?= htmlspecialchars($partner['website']) ?>"
                                            data-status="<?= $partner['status'] ?>"
                                            data-sort-order="<?= $partner['sort_order'] ?>"
                                            data-logo-url="<?= htmlspecialchars($partner['logo_url']) ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="partner_id" value="<?= $partner['id'] ?>">
                                        <button type="submit" name="delete_partner" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this partner?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Partner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="partner_id" id="edit_partner_id">
                        <input type="hidden" name="existing_logo" id="edit_existing_logo">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Partner Name</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="sustained">Sustained Partners</option>
                                        <option value="individual">Individual Partners</option>
                                        <option value="strengthened">Strengthened Partners</option>
                                        <option value="other">Other Private Partners</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_website" class="form-label">Website URL</label>
                                    <input type="url" class="form-control" id="edit_website" name="website" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="edit_sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="edit_sort_order" name="sort_order">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_logo" class="form-label">Partner Logo</label>
                            <input type="file" class="form-control" id="edit_logo" name="logo" accept="image/*">
                            <small class="text-muted">Leave blank to keep current logo</small>
                            <div class="mt-2">
                                <img id="edit_current_logo" src="" alt="Current Logo" class="logo-preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_partner" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                const partnerId = this.getAttribute('data-id');
                
                // Set all the form values
                document.getElementById('edit_partner_id').value = partnerId;
                document.getElementById('edit_name').value = this.getAttribute('data-name');
                document.getElementById('edit_category').value = this.getAttribute('data-category');
                document.getElementById('edit_description').value = this.getAttribute('data-description');
                document.getElementById('edit_website').value = this.getAttribute('data-website');
                document.getElementById('edit_sort_order').value = this.getAttribute('data-sort-order');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
                
                // Handle logo
                const logoUrl = this.getAttribute('data-logo-url');
                document.getElementById('edit_existing_logo').value = logoUrl;
                document.getElementById('edit_current_logo').src = logoUrl;
                
                modal.show();
            });
        });
    </script>
</body>
</html>