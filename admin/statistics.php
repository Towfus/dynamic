<?php
// Authentication check would go here
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_stat'])) {
        // Add new statistic
        $query = "INSERT INTO statistics (stat_number, stat_title, stat_description, display_order, is_active) 
                  VALUES (:number, :title, :description, :order, :active)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':number' => $_POST['stat_number'],
            ':title' => $_POST['stat_title'],
            ':description' => $_POST['stat_description'],
            ':order' => $_POST['display_order'],
            ':active' => isset($_POST['is_active']) ? 1 : 0
        ]);
        
        $_SESSION['message'] = "Statistic added successfully!";
        $_SESSION['messageType'] = "success";
    } elseif (isset($_POST['update_stat'])) {
        // Update existing statistic
        $query = "UPDATE statistics SET 
                  stat_number = :number,
                  stat_title = :title,
                  stat_description = :description,
                  display_order = :order,
                  is_active = :active
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':number' => $_POST['stat_number'],
            ':title' => $_POST['stat_title'],
            ':description' => $_POST['stat_description'],
            ':order' => $_POST['display_order'],
            ':active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['stat_id']
        ]);
        
        $_SESSION['message'] = "Statistic updated successfully!";
        $_SESSION['messageType'] = "success";
    } elseif (isset($_POST['delete_stat'])) {
        // Delete statistic
        $query = "DELETE FROM statistics WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['stat_id']]);
        
        $_SESSION['message'] = "Statistic deleted successfully!";
        $_SESSION['messageType'] = "success";
    }
    
    header("Location: statistics.php");
    exit();
}

// Get session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Get all statistics for display
$query = "SELECT * FROM statistics ORDER BY display_order ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Management - DepEd General Trias City</title>
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
        
        /* Statistics specific styles */
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
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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

                 <a href="statistics.php" class="active">
                    <i class="fas fa-chart-bar" ></i>
                    <span>Statistics Management</span>
                </a> 

                   <a href="impact-stories.php">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>

                <a href="news_updates.php">
                    <i class="fas fa-newspaper"></i>
                    <span>News Updates</span>
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

                <a href="project-highlights.php">
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
                <h1 class="text-xl font-semibold text-gray-800">Statistics Management</h1>
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
            <!-- Alert Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Add New Statistic Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="bi bi-plus-circle me-2"></i>Add New Statistic
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="stat_number" name="stat_number" 
                                               placeholder="Statistic Number" required>
                                        <label for="stat_number"><i class="bi bi-123 me-1"></i>Statistic Number</label>
                                        <div class="invalid-feedback">Please provide a statistic number.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="stat_title" name="stat_title" 
                                               placeholder="Title" required>
                                        <label for="stat_title"><i class="bi bi-card-text me-1"></i>Title</label>
                                        <div class="invalid-feedback">Please provide a title.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="stat_description" name="stat_description" 
                                               placeholder="Description" required>
                                        <label for="stat_description"><i class="bi bi-textarea-resize me-1"></i>Description</label>
                                        <div class="invalid-feedback">Please provide a description.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="display_order" name="display_order" 
                                               min="1" placeholder="Order" required>
                                        <label for="display_order"><i class="bi bi-sort-numeric-up me-1"></i>Order</label>
                                        <div class="invalid-feedback">Please provide a display order.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-check mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        <i class="bi bi-eye me-1"></i>Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_stat" class="btn btn-success">
                            <i class="bi bi-plus-lg me-1"></i>Add Statistic
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Statistics List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="bi bi-bar-chart-line me-2"></i>Current Statistics 
                        <span class="badge bg-primary"><?php echo count($statistics); ?></span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($statistics)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-graph-up display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Statistics Found</h5>
                        <p class="text-muted">Add your first statistic using the form above.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Number</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics as $stat): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($stat['display_order']) ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($stat['stat_number']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($stat['stat_title']) ?></td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($stat['stat_description']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $stat['is_active'] ? 'bg-success' : 'bg-secondary'; ?> status-badge">
                                            <?= $stat['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-warning edit-btn btn-action" 
                                                    data-id="<?= $stat['id'] ?>"
                                                    data-number="<?= htmlspecialchars($stat['stat_number']) ?>"
                                                    data-title="<?= htmlspecialchars($stat['stat_title']) ?>"
                                                    data-desc="<?= htmlspecialchars($stat['stat_description']) ?>"
                                                    data-order="<?= $stat['display_order'] ?>"
                                                    data-active="<?= $stat['is_active'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" id="delete-form-<?= $stat['id'] ?>" style="display:inline;">
                                                <input type="hidden" name="stat_id" value="<?= $stat['id'] ?>">
                                                <button type="submit" name="delete_stat" class="btn btn-danger btn-action" 
                                                        onclick="return confirmDelete(<?= $stat['id'] ?>)">
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil me-2"></i>Edit Statistic
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="stat_id" id="edit_stat_id">
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_stat_number" name="stat_number" 
                                       placeholder="Statistic Number" required>
                                <label for="edit_stat_number"><i class="bi bi-123 me-1"></i>Statistic Number</label>
                                <div class="invalid-feedback">Please provide a statistic number.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_stat_title" name="stat_title" 
                                       placeholder="Title" required>
                                <label for="edit_stat_title"><i class="bi bi-card-text me-1"></i>Title</label>
                                <div class="invalid-feedback">Please provide a title.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_stat_description" name="stat_description" 
                                       placeholder="Description" required>
                                <label for="edit_stat_description"><i class="bi bi-textarea-resize me-1"></i>Description</label>
                                <div class="invalid-feedback">Please provide a description.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="edit_display_order" name="display_order" 
                                       min="1" placeholder="Order" required>
                                <label for="edit_display_order"><i class="bi bi-sort-numeric-up me-1"></i>Order</label>
                                <div class="invalid-feedback">Please provide a display order.</div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">
                                <i class="bi bi-eye me-1"></i>Active
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Close
                        </button>
                        <button type="submit" name="update_stat" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save changes
                        </button>
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

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                const statId = this.getAttribute('data-id');
                const statNumber = this.getAttribute('data-number');
                const statTitle = this.getAttribute('data-title');
                const statDesc = this.getAttribute('data-desc');
                const statOrder = this.getAttribute('data-order');
                const statActive = this.getAttribute('data-active');
                
                document.getElementById('edit_stat_id').value = statId;
                document.getElementById('edit_stat_number').value = statNumber;
                document.getElementById('edit_stat_title').value = statTitle;
                document.getElementById('edit_stat_description').value = statDesc;
                document.getElementById('edit_display_order').value = statOrder;
                document.getElementById('edit_is_active').checked = statActive === '1';
                
                modal.show();
            });
        });

        // Delete confirmation function
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this statistic?')) {
                document.getElementById('delete-form-' + id).submit();
            }
            return false;
        }

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