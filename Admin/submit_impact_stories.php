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
$upload_dir = "uploads/photos/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $photo_id = mysqli_real_escape_string($conn, $_POST['photo_id']);
        
        // Handle delete action
        if ($_POST['action'] === 'delete') {
            // First get the file path to delete the file
            $get_file_query = "SELECT file_path FROM photos WHERE id = '$photo_id'";
            $file_result = mysqli_query($conn, $get_file_query);
            
            if ($file_row = mysqli_fetch_assoc($file_result)) {
                $file_path = $file_row['file_path'];
                // Delete the file if it exists
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            $delete_query = "DELETE FROM photos WHERE id = '$photo_id'";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "Photo deleted successfully";
            } else {
                $error = "Error deleting photo: " . mysqli_error($conn);
            }
        }
        
        // Handle update action
        if ($_POST['action'] === 'update') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            $date_taken = mysqli_real_escape_string($conn, $_POST['date_taken']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $story_link = mysqli_real_escape_string($conn, $_POST['story_link']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            $update_query = "UPDATE photos SET 
                            title = '$title',
                            category = '$category', 
                            date_taken = '$date_taken', 
                            description = '$description',
                            story_link = '$story_link',
                            is_featured = '$is_featured'
                            WHERE id = '$photo_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Photo information updated successfully";
            } else {
                $error = "Error updating photo: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle photo upload
    if (isset($_POST['upload']) && isset($_FILES['photo'])) {
        $title = mysqli_real_escape_string($conn, $_POST['upload_title']);
        $category = mysqli_real_escape_string($conn, $_POST['upload_category']);
        $date_taken = mysqli_real_escape_string($conn, $_POST['upload_date_taken']);
        $description = mysqli_real_escape_string($conn, $_POST['upload_description']);
        $story_link = mysqli_real_escape_string($conn, $_POST['upload_story_link']);
        $is_featured = isset($_POST['upload_is_featured']) ? 1 : 0;
        
        $file = $_FILES['photo'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_error === 0) {
                if ($file_size < 10000000) { // 10MB limit
                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $file_ext;
                    $file_destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $upload_date = date('Y-m-d H:i:s');
                        
                        $insert_query = "INSERT INTO photos (title, category, date_taken, description, story_link, file_path, upload_date, is_featured) 
                                       VALUES ('$title', '$category', '$date_taken', '$description', '$story_link', '$file_destination', '$upload_date', '$is_featured')";
                        
                        if (mysqli_query($conn, $insert_query)) {
                            $message = "Photo uploaded successfully! It will now appear in the Impact Stories section.";
                        } else {
                            $error = "Error saving photo to database: " . mysqli_error($conn);
                        }
                    } else {
                        $error = "Error uploading file";
                    }
                } else {
                    $error = "File size too large (max 10MB)";
                }
            } else {
                $error = "Error uploading file";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WebP are allowed";
        }
    }
}

// Query to get all photos (including story_link)
$query = "SELECT id, title, category, date_taken, description, story_link, file_path, upload_date, is_featured
          FROM photos 
          ORDER BY is_featured DESC, upload_date DESC";

$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Get count by category for dashboard
$category_stats = [];
$stats_query = "SELECT category, COUNT(*) as count FROM photos GROUP BY category";
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
    <title>Impact Stories Management - SDO General Trias</title>
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

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .photo-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .photo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .photo-card.featured {
            border: 3px solid var(--warning-color);
        }

        .photo-card.featured::before {
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

        .photo-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .photo-info {
            padding: 1.25rem;
        }

        .photo-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }

        .photo-category {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .category-events { background-color: var(--success-color); color: white; }
        .category-activities { background-color: var(--info-color); color: white; }
        .category-facilities { background-color: var(--secondary-color); color: white; }
        .category-students { background-color: var(--warning-color); color: var(--dark-color); }
        .category-teachers { background-color: #6f42c1; color: white; }
        .category-awards { background-color: #fd7e14; color: white; }
        .category-other { background-color: #6c757d; color: white; }

        .photo-date {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        .photo-description {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .story-link-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .story-link-info a {
            color: var(--primary-color);
            text-decoration: none;
            word-break: break-all;
        }

        .story-link-info a:hover {
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

        .link-field-info {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #0c5460;
        }

        @media (max-width: 768px) {
            .photo-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-title {
                font-size: 1.8rem;
            }
            
            .stats-row .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="admin-title">Impact Stories Dashboard</h1>
                    <p class="admin-subtitle">Manage photos that appear in the Impact Stories section of your homepage</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column align-items-end">
                        <div class="text-light mb-2">
                            <i class="fas fa-images me-2"></i>
                            <?php echo mysqli_num_rows($result); ?> Total Photos
                        </div>
                        <a href="#uploadSection" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Add New Story
                        </a>
                    </div>
                </div>
            </div>
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

        <!-- Statistics Row -->
        <div class="stats-row">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo mysqli_num_rows($result); ?></div>
                        <div class="stat-label">Total Stories</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Events']) ? $category_stats['Events'] : 0; ?></div>
                        <div class="stat-label">Events</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Activities']) ? $category_stats['Activities'] : 0; ?></div>
                        <div class="stat-label">Activities</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Awards']) ? $category_stats['Awards'] : 0; ?></div>
                        <div class="stat-label">Awards</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-form" id="uploadSection">
            <h3 class="mb-4">
                <i class="fas fa-cloud-upload-alt me-2 text-success"></i>
                Upload New Impact Story
            </h3>
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="upload_title" name="upload_title" placeholder="Story Title" required>
                            <label for="upload_title">Story Title</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="upload_category" name="upload_category" required>
                                <option value="">Select Category</option>
                                <option value="Events">Events</option>
                                <option value="Activities">Activities</option>
                                <option value="Facilities">Facilities</option>
                                <option value="Students">Students</option>
                                <option value="Teachers">Teachers</option>
                                <option value="Awards">Awards</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="upload_category">Category</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="upload_date_taken" name="upload_date_taken" required>
                            <label for="upload_date_taken">Date Taken</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="file-input-wrapper">
                            <input type="file" id="photo" name="photo" class="file-input" accept="image/*" required onchange="previewImage(event)">
                            <label for="photo" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt fs-2 text-muted mb-3"></i>
                                <div class="h5">Click to select photo or drag and drop</div>
                                <small class="text-muted">JPG, JPEG, PNG, GIF, WebP (Max 10MB)</small>
                            </label>
                            <img id="imagePreview" class="preview-image d-none">
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <textarea class="form-control" id="upload_description" name="upload_description" style="height: 120px" placeholder="Story description..."></textarea>
                    <label for="upload_description">Story Description</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="url" class="form-control" id="upload_story_link" name="upload_story_link" placeholder="https://example.com/full-story">
                    <label for="upload_story_link">Story Link (Optional)</label>
                </div>
                
                <div class="link-field-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Story Link:</strong> Add a URL where visitors can read the full story or article. This creates a "View Full Story" button that redirects to your link. Leave empty if no external link is needed.
                </div>

                <div class="featured-checkbox">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upload_is_featured" name="upload_is_featured">
                        <label class="form-check-label fw-bold" for="upload_is_featured">
                            <i class="fas fa-star text-warning me-2"></i>
                            Mark as Featured Story
                        </label>
                        <div class="form-text">Featured stories appear prominently in the Impact Stories section</div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" name="upload" class="btn btn-success btn-lg">
                        <i class="fas fa-upload me-2"></i> Upload Story
                    </button>
                </div>
            </form>
        </div>

        <!-- Photos Display -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">
                        <i class="fas fa-images me-2 text-primary"></i>
                        Impact Stories Gallery
                    </h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="categoryFilter" onchange="filterByCategory()">
                            <option value="">All Categories</option>
                            <option value="Events">Events</option>
                            <option value="Activities">Activities</option>
                            <option value="Facilities">Facilities</option>
                            <option value="Students">Students</option>
                            <option value="Teachers">Teachers</option>
                            <option value="Awards">Awards</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="photo-grid">
                        <?php while ($photo = mysqli_fetch_assoc($result)) : ?>
                            <div class="photo-card <?php echo $photo['is_featured'] ? 'featured' : ''; ?>" data-category="<?php echo $photo['category']; ?>">
                                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($photo['title']); ?>"
                                     onerror="this.src='https://placehold.co/400x200/f8f9fa/6c757d?text=Image+Not+Found';">
                                
                                <div class="photo-info">
                                    <div class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></div>
                                    <span class="photo-category category-<?php echo strtolower($photo['category']); ?>">
                                        <?php echo htmlspecialchars($photo['category']); ?>
                                    </span>
                                    <div class="photo-date">
                                        <i class="fas fa-calendar me-1"></i> 
                                        <?php echo date('M j, Y', strtotime($photo['date_taken'])); ?>
                                    </div>
                                    <div class="photo-description">
                                        <?php 
                                        $description = htmlspecialchars($photo['description']);
                                        echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description; 
                                        ?>
                                    </div>
                                    
                                    <?php if (!empty($photo['story_link'])): ?>
                                    <div class="story-link-info">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        <strong>Full Story:</strong> 
                                        <a href="<?php echo htmlspecialchars($photo['story_link']); ?>" target="_blank">
                                            <?php echo strlen($photo['story_link']) > 30 ? substr($photo['story_link'], 0, 30) . '...' : $photo['story_link']; ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="action-buttons">
                                        <?php if (!empty($photo['story_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($photo['story_link']); ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-external-link-alt"></i> View Story
                                        </a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-primary btn-sm" onclick="openEditModal(<?php echo $photo['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="openDeleteModal(<?php echo $photo['id']; ?>, '<?php echo addslashes($photo['title']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-images text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">No Impact Stories Yet</h4>
                        <p class="text-muted">Upload your first story to get started!</p>
                        <a href="#uploadSection" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Add First Story
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Story Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit_photo_id" name="photo_id">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                    <label for="edit_title">Story Title</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Events">Events</option>
                                        <option value="Activities">Activities</option>
                                        <option value="Facilities">Facilities</option>
                                        <option value="Students">Students</option>
                                        <option value="Teachers">Teachers</option>
                                        <option value="Awards">Awards</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label for="edit_category">Category</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="edit_date_taken" name="date_taken" required>
                            <label for="edit_date_taken">Date Taken</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="edit_description" name="description" style="height: 120px"></textarea>
                            <label for="edit_description">Story Description</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="url" class="form-control" id="edit_story_link" name="story_link" placeholder="https://example.com/full-story">
                            <label for="edit_story_link">Story Link (Optional)</label>
                        </div>

                        <div class="featured-checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                <label class="form-check-label fw-bold" for="edit_is_featured">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    Mark as Featured Story
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Story
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="delete_photo_id" name="photo_id">
                        <input type="hidden" name="action" value="delete">
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center mb-3">Are you sure you want to delete "<strong id="deletePhotoTitle"></strong>"?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone and will permanently delete the photo file.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Story
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Photo data for editing
        const photoData = {};
        
        <?php
        // Reset mysqli data pointer to beginning
        mysqli_data_seek($result, 0);
        
        // Store all photo data for JavaScript
        while ($photo = mysqli_fetch_assoc($result)) {
            echo "photoData[" . $photo['id'] . "] = {
                title: '" . addslashes($photo['title']) . "',
                category: '" . addslashes($photo['category']) . "',
                date_taken: '" . $photo['date_taken'] . "',
                description: '" . addslashes($photo['description']) . "',
                story_link: '" . addslashes($photo['story_link']) . "',
                is_featured: " . ($photo['is_featured'] ? 'true' : 'false') . "
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
            } else {
                preview.classList.add('d-none');
                label.style.display = 'block';
            }
        }
        
        // Open edit modal
        function openEditModal(photoId) {
            const photo = photoData[photoId];
            
            document.getElementById('edit_photo_id').value = photoId;
            document.getElementById('edit_title').value = photo.title;
            document.getElementById('edit_category').value = photo.category;
            document.getElementById('edit_date_taken').value = photo.date_taken;
            document.getElementById('edit_description').value = photo.description;
            document.getElementById('edit_story_link').value = photo.story_link;
            document.getElementById('edit_is_featured').checked = photo.is_featured;
            
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
        
        // Open delete modal
        function openDeleteModal(photoId, photoTitle) {
            document.getElementById('delete_photo_id').value = photoId;
            document.getElementById('deletePhotoTitle').textContent = photoTitle;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Filter by category
        function filterByCategory() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            const photoCards = document.querySelectorAll('.photo-card');
            
            photoCards.forEach(card => {
                if (selectedCategory === '' || card.getAttribute('data-category') === selectedCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-warning)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();