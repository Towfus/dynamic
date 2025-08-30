<?php
// Authentication check would go here
require_once '../config/database.php';
require_once '../helpers/file_upload.php';
$db = new Database();
$conn = $db->getConnection();

session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

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
    <title>Manage Partners - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #60a5fa, #3b82f6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            margin-bottom: 25px;
        }
        
        .menu-label {
            padding: 0 20px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            letter-spacing: 0.5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 2px 12px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            transition: width 0.3s ease;
        }
        
        .sidebar-menu a:hover::before,
        .sidebar-menu a.active::before {
            width: 100%;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .sidebar-menu a i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .topbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .hamburger {
            display: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        
        .hamburger:hover {
            background: rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .hamburger {
                display: block;
            }
            .topbar {
                padding: 15px 20px;
            }
        }
        
        /* Partner-specific styles */
        .logo-preview {
            max-width: 150px;
            max-height: 100px;
            object-fit: contain;
        }
        .category-badge {
            font-size: 0.8rem;
        }
        
        /* Card styling to match the modern design */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #3b82f6, #1e40af);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 1rem;
        }
        
        .table tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">ISSHED</h2>
                    <p class="text-sm text-blue-200">Project ISSHED</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-label">Main</div>
                <a href="admin-landing.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Home</span>
                </a>
                <a href="statistics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics Management</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Management</div>
                <a href="impact-stories.php">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>
                <a href="admin-document.php">
                    <i class="fas fa-file-pdf"></i>
                    <span>SMN Documents</span>
                </a>
                <a href="news_updates.php">
                    <i class="fas fa-newspaper"></i>
                    <span>News Updates Management</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">System</div>
                <a href="partners.php" class="active">
                    <i class="fas fa-handshake"></i>
                    <span>Partners</span>
                </a>

                <a href="project-highlights.php">
                    <i class="fas fa-star"></i>
                    <span>Project Highlights</span>
                </a>

                <a href="timeline-management.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timeline Management</span>
                </a>

                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content" id="main-content">
        <div class="topbar">
            <div class="flex items-center">
                <div class="hamburger mr-4" id="hamburger">
                    <i class="fas fa-bars text-xl"></i>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Partners Management</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="notification-badge cursor-pointer">
                    <i class="fas fa-bell text-gray-600 text-lg hover:text-blue-600 transition-colors"></i>
                </div>
                <div class="user-profile">
                    <div class="user-avatar">
                        A
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Admin User</div>
                        <div class="text-xs text-gray-500">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4 p-4">
            <!-- Display messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success p-4 mb-4 rounded-lg"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger p-4 mb-4 rounded-lg"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Add New Partner Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-gray-800 mb-0">Add New Partner</h2>
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
                    <h2 class="text-xl font-semibold text-gray-800 mb-0">Current Partners</h2>
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
        // Toggle sidebar
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

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
</html></button>