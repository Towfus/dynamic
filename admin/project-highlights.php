<?php
// Authentication check would go here
session_start();


// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../helpers/file_upload.php';
$db = new Database();
$conn = $db->getConnection();


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_highlight'])) {
        // Handle image upload
        $uploadResult = uploadImage($_FILES['highlight_image']);

        if ($uploadResult['success']) {
            // Read the image file data
            $imageData = file_get_contents($uploadResult['file_path']);
            $imageType = $_FILES['highlight_image']['type'];
            
            // Set default display order if not provided
            $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
            
            // Add new highlight
            $query = "INSERT INTO project_highlights 
                     (title, description, image_data, image_type, category, event_date, display_order, is_featured, is_active) 
                     VALUES 
                     (:title, :description, :image_data, :image_type, :category, :event_date, :display_order, :is_featured, :is_active)";

            $stmt = $conn->prepare($query);
            
            // Set max allowed packet size (adjust as needed)
            $conn->exec("SET GLOBAL max_allowed_packet=16777216;"); // 16MB
            
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':image_data' => $imageData,
                ':image_type' => $imageType,
                ':category' => $_POST['category'],
                ':event_date' => $_POST['event_date'],
                ':display_order' => $displayOrder,
                ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);

            // Delete the temporary uploaded file
            unlink($uploadResult['file_path']);
            
            $_SESSION['message'] = "Highlight added successfully!";
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } elseif (isset($_POST['update_highlight'])) {
        // Initialize variables for image data
        $imageData = null;
        $imageType = null;
        $imageUpdate = '';
        
        // Handle image update if new image is uploaded
        if (!empty($_FILES['highlight_image']['name'])) {
            $uploadResult = uploadImage($_FILES['highlight_image']);
            if ($uploadResult['success']) {
                $imageData = file_get_contents($uploadResult['file_path']);
                $imageType = $_FILES['highlight_image']['type'];
                $imageUpdate = ", image_data = :image_data, image_type = :image_type";
                // Delete temporary file
                unlink($uploadResult['file_path']);
            } else {
                $_SESSION['error'] = $uploadResult['error'];
            }
        }

        // Set default display order if not provided
        $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
        
        // Update highlight
        $query = "UPDATE project_highlights SET 
                 title = :title,
                 description = :description,
                 category = :category,
                 event_date = :event_date,
                 display_order = :display_order,
                 is_featured = :is_featured,
                 is_active = :is_active
                 $imageUpdate
                 WHERE id = :id";

        $stmt = $conn->prepare($query);
        
        // Set max allowed packet size (adjust as needed)
        $conn->exec("SET GLOBAL max_allowed_packet=16777216;"); // 16MB
        
        // Bind parameters
        $params = [
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':category' => $_POST['category'],
            ':event_date' => $_POST['event_date'],
            ':display_order' => $displayOrder,
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['highlight_id']
        ];
        
        // Add image parameters if updating image
        if (!empty($imageData)) {
            $params[':image_data'] = $imageData;
            $params[':image_type'] = $imageType;
        }
        
        $stmt->execute($params);

        $_SESSION['message'] = "Highlight updated successfully!";
    } elseif (isset($_POST['delete_highlight'])) {
        // Delete the highlight
        $query = "DELETE FROM project_highlights WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['highlight_id']]);

        $_SESSION['message'] = "Highlight deleted successfully!";
    }

    header("Location: project-highlights.php");
    exit();
}

// Get all highlights for display
$query = "SELECT id, title, description, category, event_date, display_order, is_featured, is_active 
          FROM project_highlights 
          ORDER BY display_order, event_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to display image from blob data
function getImageSrc($id) {
    global $conn;
    $query = "SELECT image_data, image_type FROM project_highlights WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image && $image['image_data']) {
        return 'data:' . $image['image_type'] . ';base64,' . base64_encode($image['image_data']);
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Highlights Management - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
        
        /* Project Highlights specific styles */
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
        
        .img-thumbnail {
            max-width: 100px;
            height: auto;
            border-radius: 8px;
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
        
        .btn-success {
            background: linear-gradient(45deg, #10b981, #059669);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6b7280, #4b5563);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #f59e0b, #d97706);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
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
        
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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

                   <a href="impact-stories.php">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>

                
                 <a href="partners.php">
                    <i class="fas fa-handshake"></i>
                    <span>Partners</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Proj ISSHED</div>

                <a href="timeline-management.php" >
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timeline Management</span>
                </a>

                <a href="project-highlights.php" class="active">
                    <i class="fas fa-star"></i>
                    <span>Project Highlights</span>
                </a>
                <a href="admin-document.php">
                    <i class="fas fa-file-pdf"></i>
                    <span>SMN Documents</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">System</div>
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
                <h1 class="text-xl font-semibold text-gray-800">Project Highlights Management</h1>
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
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Add New Highlight Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="bi bi-plus-circle me-2"></i>Add New Highlight
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="1" min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                    <label class="form-check-label" for="is_featured">Featured Highlight</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="highlight_image" class="form-label">Highlight Image</label>
                            <input type="file" class="form-control" id="highlight_image" name="highlight_image" accept="image/*" required>
                            <small class="text-muted">Recommended size: 800x600 pixels (Max 8MB)</small>
                        </div>

                        <button type="submit" name="add_highlight" class="btn btn-primary">Add Highlight</button>
                    </form>
                </div>
            </div>

            <!-- Highlights List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="bi bi-list-ul me-2"></i>Current Highlights 
                        <span class="badge bg-primary"><?php echo count($highlights); ?></span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($highlights)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-star display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Project Highlights Found</h5>
                        <p class="text-muted">Add your first highlight using the form above.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Order</th>
                                    <th>Featured</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($highlights as $highlight): ?>
                                    <tr>
                                        <td>
                                            <?php $imgSrc = getImageSrc($highlight['id']); ?>
                                            <?php if ($imgSrc): ?>
                                                <img src="<?= $imgSrc ?>" alt="Highlight Image" class="img-thumbnail">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($highlight['title']) ?></td>
                                        <td><?= htmlspecialchars($highlight['category']) ?></td>
                                        <td><?= date('M d, Y', strtotime($highlight['event_date'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= $highlight['display_order'] ?></span></td>
                                        <td><?= $highlight['is_featured'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                        <td><?= $highlight['is_active'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-warning edit-btn"
                                                    data-id="<?= $highlight['id'] ?>"
                                                    data-title="<?= htmlspecialchars($highlight['title']) ?>"
                                                    data-description="<?= htmlspecialchars($highlight['description']) ?>"
                                                    data-category="<?= htmlspecialchars($highlight['category']) ?>"
                                                    data-event-date="<?= $highlight['event_date'] ?>"
                                                    data-display-order="<?= $highlight['display_order'] ?>"
                                                    data-is-featured="<?= $highlight['is_featured'] ?>"
                                                    data-is-active="<?= $highlight['is_active'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="highlight_id" value="<?= $highlight['id'] ?>">
                                                    <button type="submit" name="delete_highlight" class="btn btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this highlight?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
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
                        <h5 class="modal-title">Edit Highlight</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="highlight_id" id="edit_highlight_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="edit_category" name="category" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="edit_event_date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="edit_display_order" name="display_order" required min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label" for="edit_is_featured">Featured Highlight</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                    <label class="form-check-label" for="edit_is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_highlight_image" class="form-label">Highlight Image</label>
                            <input type="file" class="form-control" id="edit_highlight_image" name="highlight_image" accept="image/*">
                            <small class="text-muted">Leave blank to keep current image (Max 8MB)</small>
                            <div class="mt-2">
                                <img id="edit_current_image" src="" alt="Current Image" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_highlight" class="btn btn-primary">Save changes</button>
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
                const highlightId = this.getAttribute('data-id');

                // Set all the form values
                document.getElementById('edit_highlight_id').value = highlightId;
                document.getElementById('edit_title').value = this.getAttribute('data-title');
                document.getElementById('edit_description').value = this.getAttribute('data-description');
                document.getElementById('edit_category').value = this.getAttribute('data-category');
                document.getElementById('edit_event_date').value = this.getAttribute('data-event-date');
                document.getElementById('edit_display_order').value = this.getAttribute('data-display-order');
                document.getElementById('edit_is_featured').checked = this.getAttribute('data-is-featured') === '1';
                document.getElementById('edit_is_active').checked = this.getAttribute('data-is-active') === '1';

                // Handle image
                const imgElement = this.closest('tr').querySelector('img');
                if (imgElement) {
                    document.getElementById('edit_current_image').src = imgElement.src;
                }

                modal.show();
            });
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            });
        }, 5000);
    </script>
</body>

</html>