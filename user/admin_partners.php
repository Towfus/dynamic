<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "organization_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create uploads directory if it doesn't exist
$upload_dir = "uploads/partners/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $partner_id = mysqli_real_escape_string($conn, $_POST['partner_id']);
        
        // Handle delete action
        if ($_POST['action'] === 'delete') {
            // First get the file path to delete the file
            $get_file_query = "SELECT logo_path FROM partnership WHERE id = '$partner_id'";
            $file_result = mysqli_query($conn, $get_file_query);
            
            if ($file_row = mysqli_fetch_assoc($file_result)) {
                $file_path = $file_row['logo_path'];
                // Delete the file if it exists
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            $delete_query = "DELETE FROM partnership WHERE id = '$partner_id'";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "Partner deleted successfully";
            } else {
                $error = "Error deleting partner: " . mysqli_error($conn);
            }
        }
        
        // Handle update action
        if ($_POST['action'] === 'update') {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            
            $update_query = "UPDATE partnership SET 
                    name = '$name',
                    category = '$category'
                    WHERE id = '$partner_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Partner information updated successfully";
            } else {
                $error = "Error updating partner: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle partner upload
    if (isset($_POST['upload']) && isset($_FILES['logo'])) {
        $name = mysqli_real_escape_string($conn, $_POST['upload_name']);
        $category = mysqli_real_escape_string($conn, $_POST['upload_category']);
        
        $file = $_FILES['logo'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
        
        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_error === 0) {
                if ($file_size < 5000000) { // 5MB limit
                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $file_ext;
                    $file_destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $insert_query = "INSERT INTO partnership (name, category, logo_path) 
                             VALUES ('$name', '$category', '$file_destination')";
                        
                        if (mysqli_query($conn, $insert_query)) {
                            $message = "Partner added successfully!";
                        } else {
                            $error = "Error saving partner to database: " . mysqli_error($conn);
                        }
                    } else {
                        $error = "Error uploading file";
                    }
                } else {
                    $error = "File size too large (max 5MB)";
                }
            } else {
                $error = "Error uploading file";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, WebP, and SVG are allowed";
        }
    }
}

// Query to get all partners
$partners_query = "SELECT id, name, category, logo_path FROM partnership ORDER BY category ASC, name ASC";
$partners_result = mysqli_query($conn, $partners_query);

// Check if query was successful
if (!$partners_result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Get count by category for dashboard
$category_stats = array();
$stats_query = "SELECT category, COUNT(*) as count FROM partnership GROUP BY category";
$stats_result = mysqli_query($conn, $stats_query);
while ($stat = mysqli_fetch_assoc($stats_result)) {
    $category_stats[$stat['category']] = $stat['count'];
}
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
          :root {
            --primary-color: #006400;
            --secondary-color: #28a745;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .admin-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .admin-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .partner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .partner-card {
            background: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .partner-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .partner-card.featured {
            border: 3px solid var(--warning-color);
        }

        .partner-card.featured::before {
            content: '★ FEATURED';
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--warning-color);
            color: var(--dark-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            z-index: 2;
        }

        .partner-logo-container {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9e9e9ff;
            padding: 1px;
            overflow: hidden;
        }

        .partner-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.3s ease;
        }

        .partner-logo-container:hover .partner-logo {
            transform: scale(1.05);
        }

        .partner-info {
            padding: 1.25rem;
        }

        .partner-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }

        .partner-category {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .category-sustained { background-color: var(--success-color); color: white; }
        .category-individual { background-color: var(--info-color); color: white; }
        .category-strengthened { background-color: var(--secondary-color); color: white; }
        .category-other-private { background-color: var(--warning-color); color: var(--dark-color); }

        .partner-description {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .website-link-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .website-link-info a {
            color: var(--primary-color);
            text-decoration: none;
            word-break: break-all;
        }

        .website-link-info a:hover {
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.85rem;
        }

        .upload-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .file-input-label {
            display: block;
            padding: 3rem 2rem;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            background: #f0fff0;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .btn-close {
            filter: invert(1);
        }

        .navigation-bar {
            background: white;
            padding: 1rem 0;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            color: var(--primary-color) !important;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .featured-checkbox {
            background-color: #fff3cd;
            border: 2px solid var(--warning-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .partner-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-title {
                font-size: 1.8rem;
            }
            
            .stats-row .col-md-3 {
                margin-bottom: 1rem;
            }
            
            .partner-logo-container {
                height: 150px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="text-center">Partner Management</h1>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Add New Partner</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_name" class="form-label">Partner Name</label>
                                <input type="text" class="form-control" id="upload_name" name="upload_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="upload_category" class="form-label">Category</label>
                                <select class="form-select" id="upload_category" name="upload_category" required>
                                    <option value="">Select Category</option>
                                    <option value="Sustained">Sustained Partners</option>
                                    <option value="Individual">Individual Partners</option>
                                    <option value="Strengthened">Strengthened Partners</option>
                                    <option value="Other">Other Partners</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Partner Logo</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="logo" name="logo" class="file-input" accept="image/*" required onchange="previewImage(event)">
                            <label for="logo" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt fs-4 text-muted mb-2"></i>
                                <div>Click to select logo</div>
                                <small class="text-muted">JPG, PNG, GIF, WebP, SVG (Max 5MB)</small>
                            </label>
                            <img id="imagePreview" class="preview-image d-none">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" name="upload" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add Partner
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Partners Display -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Partner Gallery</h5>
                    <div>
                        <select class="form-select form-select-sm" id="categoryFilter" onchange="filterByCategory()">
                            <option value="">All Categories</option>
                            <option value="Sustained">Sustained Partners</option>
                            <option value="Individual">Individual Partners</option>
                            <option value="Strengthened">Strengthened Partners</option>
                            <option value="Other">Other Partners</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($partners_result) > 0): ?>
                    <div class="partner-grid">
                        <?php while ($partner = mysqli_fetch_assoc($partners_result)) : ?>
                            <div class="partner-card" data-category="<?php echo $partner['category']; ?>">
                                <div class="partner-logo-container">
                                    <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($partner['name']); ?> Logo"
                                         class="partner-logo"
                                         onerror="this.src='https://placehold.co/400x200/f8f9fa/6c757d?text=Partner+Logo';">
                                </div>
                                
                                <div class="partner-info">
                                    <div class="partner-name"><?php echo htmlspecialchars($partner['name']); ?></div>
                                    <span class="partner-category">
                                        <?php echo htmlspecialchars($partner['category']); ?>
                                    </span>
                                    
                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(<?php echo $partner['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal(<?php echo $partner['id']; ?>, '<?php echo addslashes($partner['name']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-handshake text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">No Partners Yet</h4>
                        <p class="text-muted">Add your first partner to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Partner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit_partner_id" name="partner_id">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Partner Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-select" id="edit_category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Sustained">Sustained Partners</option>
                                <option value="Individual">Individual Partners</option>
                                <option value="Strengthened">Strengthened Partners</option>
                                <option value="Other">Other Partners</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="delete_partner_id" name="partner_id">
                        <input type="hidden" name="action" value="delete">
                        
                        <p class="text-center">Are you sure you want to delete "<strong id="deletePartnerName"></strong>"?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Partner data for editing
        const partnerData = {};
        
        <?php
        // Reset mysqli data pointer to beginning for JavaScript data
        mysqli_data_seek($partners_result, 0);
        
        // Store all partner data for JavaScript
        while ($partner = mysqli_fetch_assoc($partners_result)) {
            echo "partnerData[" . $partner['id'] . "] = {
                name: '" . addslashes($partner['name']) . "',
                category: '" . addslashes($partner['category']) . "'
            };\n";
        }
        ?>
        
        // Preview uploaded image
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const label = document.querySelector('.file-input-label');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    label.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Open edit modal
        function openEditModal(partnerId) {
            const partner = partnerData[partnerId];
            
            document.getElementById('edit_partner_id').value = partnerId;
            document.getElementById('edit_name').value = partner.name;
            document.getElementById('edit_category').value = partner.category;
            
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
        
        // Open delete modal
        function openDeleteModal(partnerId, partnerName) {
            document.getElementById('delete_partner_id').value = partnerId;
            document.getElementById('deletePartnerName').textContent = partnerName;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Filter by category
        function filterByCategory() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            const partnerCards = document.querySelectorAll('.partner-card');
            
            partnerCards.forEach(card => {
                if (selectedCategory === '' || card.getAttribute('data-category') === selectedCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>