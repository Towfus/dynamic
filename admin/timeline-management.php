<?php
// Database configuration
require_once '../config/database.php';
require_once '../helpers/file_upload.php';

session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle image upload
        $uploadResult = uploadImage($_FILES['event_image'], 'timeline');
        
        if ($uploadResult['success']) {
            // Add new event
            $query = "INSERT INTO timeline_events 
                      (title, description, image_path, status, event_date, 
                      display_order, position, is_active) 
                      VALUES 
                      (:title, :description, :image_path, :status, :event_date, 
                      :display_order, :position, :is_active)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':image_path' => $uploadResult['file_path'],
                ':status' => $_POST['status'],
                ':event_date' => $_POST['event_date'],
                ':display_order' => $_POST['display_order'],
                ':position' => $_POST['position'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $_SESSION['message'] = "Timeline event added successfully!";
            $_SESSION['messageType'] = "success";
        } else {
            $_SESSION['message'] = $uploadResult['error'];
            $_SESSION['messageType'] = "danger";
        }
    } elseif (isset($_POST['update_event'])) {
        // Handle image update if new image is uploaded
        $image_path = $_POST['existing_image'];
        
        if (!empty($_FILES['event_image']['name'])) {
            $uploadResult = uploadImage($_FILES['event_image'], 'timeline');
            if ($uploadResult['success']) {
                $image_path = $uploadResult['file_path'];
                // Delete old image if needed
                if (file_exists($_POST['existing_image'])) {
                    unlink($_POST['existing_image']);
                }
            } else {
                $_SESSION['message'] = $uploadResult['error'];
                $_SESSION['messageType'] = "danger";
            }
        }
        
        // Update event
        $query = "UPDATE timeline_events SET 
                 title = :title,
                 description = :description,
                 image_path = :image_path,
                 status = :status,
                 event_date = :event_date,
                 display_order = :display_order,
                 position = :position,
                 is_active = :is_active,
                 updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':image_path' => $image_path,
            ':status' => $_POST['status'],
            ':event_date' => $_POST['event_date'],
            ':display_order' => $_POST['display_order'],
            ':position' => $_POST['position'],
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['event_id']
        ]);
        
        $_SESSION['message'] = "Timeline event updated successfully!";
        $_SESSION['messageType'] = "success";
    } elseif (isset($_POST['delete_event'])) {
        // First get image path to delete it
        $query = "SELECT image_path FROM timeline_events WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the image file
        if ($event && file_exists($event['image_path'])) {
            unlink($event['image_path']);
        }
        
        // Delete the event
        $query = "DELETE FROM timeline_events WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        
        $_SESSION['message'] = "Timeline event deleted successfully!";
        $_SESSION['messageType'] = "success";
    } elseif (isset($_POST['toggle_status'])) {
        $query = "UPDATE timeline_events SET is_active = NOT is_active WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        
        $_SESSION['message'] = "Event status updated successfully!";
        $_SESSION['messageType'] = "success";
    }
    
    header("Location: timeline-management.php");
    exit();
}

// Get session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Get all events for display
$query = "SELECT * FROM timeline_events ORDER BY display_order ASC, event_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get event for editing if ID is provided
$editEvent = null;
if (isset($_GET['edit'])) {
    $query = "SELECT * FROM timeline_events WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $_GET['edit']]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Management - DepEd General Trias City</title>
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
        
        /* Timeline-specific styles */
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
        
        .timeline-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
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
                <a href="partners.php">
                    <i class="fas fa-handshake"></i>
                    <span>Partners</span>
                </a>

                <a href="project-highlights.php">
                    <i class="fas fa-star"></i>
                    <span>Project Highlights</span>
                </a>

                <a href="timeline-management.php" class="active">
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
                <h1 class="text-xl font-semibold text-gray-800">Timeline Management</h1>
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

            <!-- Form Section -->
            <div class="timeline-form">
                <h3 class="mb-4 text-2xl font-bold text-gray-800">
                    <i class="bi bi-<?php echo $editEvent ? 'pencil' : 'plus-circle'; ?> me-2"></i>
                    <?php echo $editEvent ? 'Edit Timeline Event' : 'Add New Timeline Event'; ?>
                </h3>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php if ($editEvent): ?>
                    <input type="hidden" name="event_id" value="<?php echo $editEvent['id']; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo $editEvent['image_path']; ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Title -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo $editEvent ? htmlspecialchars($editEvent['title']) : ''; ?>" 
                                       placeholder="Event Title" required>
                                <label for="title"><i class="bi bi-card-text me-1"></i>Event Title</label>
                                <div class="invalid-feedback">Please provide a valid title.</div>
                            </div>
                        </div>

                        <!-- Event Date -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                       value="<?php echo $editEvent ? $editEvent['event_date'] : ''; ?>" 
                                       placeholder="Event Date" required>
                                <label for="event_date"><i class="bi bi-calendar me-1"></i>Event Date</label>
                                <div class="invalid-feedback">Please provide a valid date.</div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="description" name="description" 
                                          style="height: 120px" placeholder="Event Description" required><?php echo $editEvent ? htmlspecialchars($editEvent['description']) : ''; ?></textarea>
                                <label for="description"><i class="bi bi-textarea-resize me-1"></i>Event Description</label>
                                <div class="invalid-feedback">Please provide a description.</div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="planned" <?php echo ($editEvent && $editEvent['status'] === 'planned') ? 'selected' : ''; ?>>Planned</option>
                                    <option value="in-progress" <?php echo ($editEvent && $editEvent['status'] === 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo ($editEvent && $editEvent['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <label for="status"><i class="bi bi-flag me-1"></i>Status</label>
                                <div class="invalid-feedback">Please select a status.</div>
                            </div>
                        </div>

                        <!-- Position -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="position" name="position" required>
                                    <option value="">Select Position</option>
                                    <option value="left" <?php echo ($editEvent && $editEvent['position'] === 'left') ? 'selected' : ''; ?>>Left</option>
                                    <option value="right" <?php echo ($editEvent && $editEvent['position'] === 'right') ? 'selected' : ''; ?>>Right</option>
                                </select>
                                <label for="position"><i class="bi bi-align-center me-1"></i>Position</label>
                                <div class="invalid-feedback">Please select a position.</div>
                            </div>
                        </div>

                        <!-- Display Order -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="display_order" name="display_order" 
                                       value="<?php echo $editEvent ? $editEvent['display_order'] : '1'; ?>" 
                                       min="1" placeholder="Display Order" required>
                                <label for="display_order"><i class="bi bi-sort-numeric-up me-1"></i>Display Order</label>
                                <div class="invalid-feedback">Please provide a valid display order.</div>
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="col-md-8">
                            <label for="event_image" class="form-label">
                                <i class="bi bi-image me-1"></i>Event Image
                            </label>
                            <input type="file" class="form-control" id="event_image" name="event_image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif" <?php echo !$editEvent ? 'required' : ''; ?>>
                            <div class="form-text">
                                Accepted formats: JPG, JPEG, PNG, GIF. Max size: 5MB
                            </div>
                        </div>

                        <!-- Current Image Preview -->
                        <?php if ($editEvent && $editEvent['image_path']): ?>
                        <div class="col-md-4">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="<?php echo htmlspecialchars($editEvent['image_path']); ?>" 
                                     alt="Current Image" class="image-preview">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Active Status -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo ($editEvent && $editEvent['is_active']) || !$editEvent ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    <i class="bi bi-eye me-1"></i>Active (Visible on timeline)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="<?php echo $editEvent ? 'update_event' : 'add_event'; ?>" class="btn btn-success me-2">
                            <i class="bi bi-<?php echo $editEvent ? 'check-lg' : 'plus-lg'; ?> me-1"></i>
                            <?php echo $editEvent ? 'Update Event' : 'Add Event'; ?>
                        </button>
                        
                        <?php if ($editEvent): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel Edit
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Events List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="bi bi-list-ul me-2"></i>Timeline Events 
                        <span class="badge bg-primary"><?php echo count($events); ?></span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($events)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Timeline Events Found</h5>
                        <p class="text-muted">Add your first timeline event using the form above.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Position</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $event['display_order']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($event['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($event['image_path']); ?>" 
                                                 alt="Event Image" class="image-preview me-2">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                <small class="d-block text-muted">
                                                    <?php echo substr(htmlspecialchars($event['description']), 0, 60) . '...'; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y', strtotime($event['event_date'])); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'completed' => 'bg-success',
                                            'in-progress' => 'bg-warning text-dark',
                                            'planned' => 'bg-primary'
                                        ];
                                        $statusClass = $statusClasses[$event['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> status-badge">
                                            <?php echo ucwords(str_replace('-', ' ', $event['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info status-badge">
                                            <?php echo ucfirst($event['position']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $event['is_active'] ? 'btn-success' : 'btn-outline-secondary'; ?> btn-action">
                                                <i class="bi bi-<?php echo $event['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-action">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <button type="submit" name="delete_event" class="btn btn-outline-danger btn-action" 
                                                        onclick="return confirm('Are you sure you want to delete this event?')">
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