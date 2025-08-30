<?php

session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sdo_gentri";

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the upload directory with web-accessible path
$upload_dir = 'shared/documents/';
$full_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $upload_dir;

// Check if the smn_documents table exists
$checkTableSql = "SHOW TABLES LIKE 'smn_documents'";
$tableExists = $conn->query($checkTableSql)->num_rows > 0;

// Create smn_documents table if it doesn't exist
if (!$tableExists) {
    $createTableSql = "CREATE TABLE smn_documents (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(255) NOT NULL,
        upload_date DATETIME NOT NULL
    )";
    
    if ($conn->query($createTableSql) === TRUE) {
        // Table created successfully
        $message = "SMN Documents table created successfully";
    } else {
        $error = "Error creating SMN Documents table: " . $conn->error;
    }
}

// Handle SMN Document operations
if (isset($_POST['action'])) {
    // Upload SMN Document
    if ($_POST['action'] == 'upload_smn_document') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        // Handle file upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
            // Create directory if it doesn't exist
            if (!file_exists($full_upload_dir)) {
                mkdir($full_upload_dir, 0777, true);
            }

            $original_filename = basename($_FILES["pdf_file"]["name"]);
            $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
            $new_filename = 'smn_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_extension;
            $target_file = $full_upload_dir . $new_filename;

            // Check if file is a PDF
            if (strtolower($file_extension) != "pdf") {
                $error = "Sorry, only PDF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                    // Store the web-accessible path in database
                    $relative_path = $upload_dir . $new_filename;
                    
                    // Insert into database
                    $sql = "INSERT INTO smn_documents (title, description, file_path, upload_date) VALUES (?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $title, $description, $relative_path);
                    
                    if ($stmt->execute()) {
                        $message = "SMN Document uploaded successfully";
                        // Log successful upload
                        error_log("SMN Document uploaded successfully: $title, Path: $target_file");
                    } else {
                        $error = "Error adding SMN Document to database: " . $conn->error;
                        error_log("Database error when uploading SMN Document: " . $conn->error);
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                    error_log("File upload failed for SMN Document: $title");
                }
            }
        } else {
            $error = "Please select a PDF file to upload";
            if (isset($_FILES['pdf_file'])) {
                error_log("File upload error code: " . $_FILES['pdf_file']['error']);
            }
        }
    }
    
    // Delete SMN Document
    elseif ($_POST['action'] == 'delete_smn_document') {
        $id = $_POST['id'];
        
        // Get file path before deleting record
        $sql = "SELECT file_path FROM smn_documents WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $file_path = $row['file_path'];
            $full_file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file_path;
            
            // Delete from database
            $sql = "DELETE FROM smn_documents WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete the file
                if (file_exists($full_file_path)) {
                    unlink($full_file_path);
                }
                $message = "SMN Document deleted successfully";
            } else {
                $error = "Error deleting SMN Document: " . $conn->error;
            }
        } else {
            $error = "SMN Document not found";
        }
    }
    
    // Bulk delete SMN Documents
    elseif ($_POST['action'] == 'bulk_delete_smn_documents') {
        if (isset($_POST['selected_documents']) && !empty($_POST['selected_documents'])) {
            $selected_documents = $_POST['selected_documents'];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($selected_documents as $doc_id) {
                // Get file path before deleting record
                $sql = "SELECT file_path FROM smn_documents WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $doc_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $file_path = $row['file_path'];
                    $full_file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file_path;
                    
                    // Delete from database
                    $sql = "DELETE FROM smn_documents WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $doc_id);
                    
                    if ($stmt->execute()) {
                        // Delete the file
                        if (file_exists($full_file_path)) {
                            unlink($full_file_path);
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            if ($successCount > 0 && $errorCount == 0) {
                $message = "$successCount SMN Documents deleted successfully";
            } elseif ($successCount > 0 && $errorCount > 0) {
                $message = "$successCount SMN Documents deleted successfully, but $errorCount failed";
            } else {
                $error = "Error deleting SMN Documents";
            }
        } else {
            $error = "No SMN Documents selected for deletion";
        }
    }
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

// Fetch all SMN Documents for display
$docsSql = "SELECT * FROM smn_documents ORDER BY upload_date DESC";
$docsResult = $conn->query($docsSql);

// Helper function to get the base URL of the application
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . '/';
}

$pageTitle = "SMN Documents Management"; // optional, will appear in <title>

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMN Documents Management - DepEd General Trias City</title>
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
        
        /* Document-specific styles */
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
        
        .document-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }
        
        .action-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 500px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .file-input-wrapper {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input-wrapper:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #3b82f6, #1e40af);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
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
            padding: 12px 24px;
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
            padding: 12px 24px;
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
            padding: 12px 16px;
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
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
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
                <a href="admin-document.php" class="active">
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
                <h1 class="text-xl font-semibold text-gray-800">SMN Documents Management</h1>
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
            <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="document-form">
                <h3 class="mb-4 text-2xl font-bold text-gray-800">
                    <i class="fas fa-upload me-2"></i>Upload New SMN Document
                </h3>
                
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="upload_smn_document">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-1"></i>Document Title
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="Enter document title" required>
                            <div class="invalid-feedback">Please provide a document title.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-pdf me-1"></i>PDF File
                            </label>
                            <div class="file-input-wrapper">
                                <label for="pdf_file" id="file-label" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-blue-500"></i>
                                    <div class="fw-medium">Choose a PDF file</div>
                                    <small class="text-muted">Click to browse or drag and drop</small>
                                </label>
                                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                                <div id="file-name" class="text-sm text-gray-500 mt-2">No file chosen</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter document description (optional)"></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Upload Document
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-xl font-semibold text-gray-800">
                        <i class="fas fa-file-pdf me-2"></i>SMN Documents
                        <?php 
                        $totalDocs = $docsResult ? $docsResult->num_rows : 0;
                        ?>
                        <span class="badge bg-primary"><?php echo $totalDocs; ?></span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if ($docsResult && $docsResult->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $docsResult->fetch_assoc()): ?>
                                <?php
                                // Get file information
                                $file_path = $row['file_path'];
                                $file_info = pathinfo($file_path);
                                $file_ext = isset($file_info['extension']) ? $file_info['extension'] : 'PDF';
                                
                                // Get file size if file exists
                                $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file_path;
                                $file_size = file_exists($full_path) ? formatSizeUnits(filesize($full_path)) : 'N/A';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php 
                                            $description = htmlspecialchars($row['description']);
                                            echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description; 
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo strtoupper($file_ext); ?></span>
                                    </td>
                                    <td><?php echo $file_size; ?></td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($row['upload_date'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/<?php echo $file_path; ?>" 
                                               target="_blank"  
                                               class="action-icon bg-primary"
                                               title="View Document">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/<?php echo $file_path; ?>" 
                                               download
                                               class="action-icon bg-success"
                                               title="Download Document">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')" 
                                                    class="action-icon bg-danger"
                                                    title="Delete Document">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-pdf display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No SMN Documents Found</h5>
                        <p class="text-muted">Upload your first SMN document using the form above.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Delete Document
                </h3>
                <button class="btn-close" onclick="closeModal()"></button>
            </div>
            
            <div class="text-center mb-4">
                <p class="mb-2">Are you sure you want to delete <strong id="deleteFileName"></strong>?</p>
                <p class="text-danger small">This action cannot be undone and the file will be permanently deleted.</p>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <button onclick="closeModal()" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_smn_document">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
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

        // File input display
        document.getElementById('pdf_file').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
            
            if (this.files[0]) {
                document.getElementById('file-label').innerHTML = `
                    <i class="fas fa-file-pdf fa-2x mb-2 text-success"></i>
                    <div class="fw-medium text-success">File Selected</div>
                    <small class="text-muted">Click to change file</small>
                `;
            }
        });

        // Delete confirmation
        function confirmDelete(id, title) {
            document.getElementById('deleteFileName').textContent = title;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('deleteModal')) {
                closeModal();
            }
        }

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

        // Drag and drop functionality for file upload
        const fileInputWrapper = document.querySelector('.file-input-wrapper');
        const fileInput = document.getElementById('pdf_file');

        fileInputWrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#3b82f6';
            this.style.backgroundColor = '#eff6ff';
        });

        fileInputWrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d1d5db';
            this.style.backgroundColor = 'transparent';
        });

        fileInputWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d1d5db';
            this.style.backgroundColor = 'transparent';
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            } else {
                alert('Please select a valid PDF file.');
            }
        });
    </script>
</body>
</html>