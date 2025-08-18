<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "organization_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

function getCorrectImagePath($stored_path) {
    if (strpos($stored_path, 'http') === 0) {
        return $stored_path; // URL
    }
    if (strpos($stored_path, 'uploads/') === 0) {
        return '../admin/' . $stored_path; // Fix relative path for images in admin/uploads
    }
    return $stored_path;
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create uploads directory if it doesn't exist
$upload_dir = "../admin/uploads/news/"; // Changed path since we're now in user folder
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $news_id = mysqli_real_escape_string($conn, $_POST['news_id']);
        
        // Handle delete action
        if ($_POST['action'] === 'delete') {
            // First get the file path to delete the file
            $get_file_query = "SELECT image_path FROM news_updates WHERE id = '$news_id'";
            $file_result = mysqli_query($conn, $get_file_query);
            
            if ($file_row = mysqli_fetch_assoc($file_result)) {
                $file_path = $file_row['image_path'];
                // Delete the file if it exists and is local
                if (file_exists($file_path) && strpos($file_path, 'uploads/') !== false) {
                    unlink($file_path);
                } elseif (file_exists("../admin/" . $file_path) && strpos($file_path, 'uploads/') === 0) {
                    unlink("../admin/" . $file_path);
                }
            }
            
            $delete_query = "DELETE FROM news_updates WHERE id = '$news_id'";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "News article deleted successfully";
            } else {
                $error = "Error deleting news article: " . mysqli_error($conn);
            }
        }
        
        // Handle update action
        if ($_POST['action'] === 'update') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
            $content = mysqli_real_escape_string($conn, $_POST['content']);
            $news_link = mysqli_real_escape_string($conn, $_POST['news_link']);
            $publish_date = mysqli_real_escape_string($conn, $_POST['publish_date']);
            $author = mysqli_real_escape_string($conn, $_POST['author']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            
            $update_query = "UPDATE news_updates SET 
                            title = '$title',
                            category = '$category', 
                            excerpt = '$excerpt',
                            content = '$content',
                            news_link = '$news_link',
                            publish_date = '$publish_date',
                            author = '$author',
                            is_featured = '$is_featured',
                            is_published = '$is_published'
                            WHERE id = '$news_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "News article updated successfully";
            } else {
                $error = "Error updating news article: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle news upload
    if (isset($_POST['submit_news'])) {
        $title = mysqli_real_escape_string($conn, $_POST['news_title']);
        $category = mysqli_real_escape_string($conn, $_POST['news_category']);
        $excerpt = mysqli_real_escape_string($conn, $_POST['news_excerpt']);
        $content = mysqli_real_escape_string($conn, $_POST['news_content']);
        $news_link = mysqli_real_escape_string($conn, $_POST['news_link']);
        $publish_date = mysqli_real_escape_string($conn, $_POST['news_publish_date']);
        $author = mysqli_real_escape_string($conn, $_POST['news_author']);
        $is_featured = isset($_POST['news_is_featured']) ? 1 : 0;
        $is_published = isset($_POST['news_is_published']) ? 1 : 0;
        
        // Handle image upload or URL
        $image_path = '';
        $use_url = isset($_POST['use_image_url']) && $_POST['use_image_url'] == '1';
        
        if ($use_url && !empty($_POST['image_url'])) {
            // Use provided URL
            $image_path = mysqli_real_escape_string($conn, $_POST['image_url']);
        } elseif (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === 0) {
            // Handle file upload
            $file = $_FILES['news_image'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];
            
            // Get file extension
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            
            if (in_array($file_ext, $allowed_extensions)) {
                if ($file_size < 10000000) { // 10MB limit
                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $file_ext;
                    $file_destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $image_path = "uploads/news/" . $new_filename; // Store relative path
                    } else {
                        $error = "Error uploading image file";
                    }
                } else {
                    $error = "Image file size too large (max 10MB)";
                }
            } else {
                $error = "Invalid image file type. Only JPG, JPEG, PNG, GIF, and WebP are allowed";
            }
        } else {
            $error = "Please provide either an image file or image URL";
        }
        
        // Insert into database if no errors
        if (!isset($error) && !empty($image_path)) {
            $upload_date = date('Y-m-d H:i:s');
            
            $insert_query = "INSERT INTO news_updates (title, category, excerpt, content, news_link, image_path, publish_date, upload_date, author, is_featured, is_published) 
                           VALUES ('$title', '$category', '$excerpt', '$content', '$news_link', '$image_path', '$publish_date', '$upload_date', '$author', '$is_featured', '$is_published')";
            
            if (mysqli_query($conn, $insert_query)) {
                $message = "News article submitted successfully! It will now appear in the News & Partnership Updates section.";
            } else {
                $error = "Error saving news article to database: " . mysqli_error($conn);
            }
        }
    }
}

// Query to get all news articles
$query = "SELECT id, title, category, excerpt, content, news_link, image_path, publish_date, upload_date, author, is_featured, is_published
          FROM news_updates 
          ORDER BY is_featured DESC, publish_date DESC";

$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Get count by category for dashboard
$category_stats = [];
$stats_query = "SELECT category, COUNT(*) as count FROM news_updates GROUP BY category";
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
    <title>News & Updates Management - SDO General Trias</title>
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
            padding-top: 80px; /* Account for navbar */
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

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .news-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .news-card.featured {
            border: 3px solid var(--warning-color);
        }

        .news-card.featured::before {
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

        .news-card.unpublished {
            opacity: 0.7;
            border: 2px dashed #6c757d;
        }

        .news-card.unpublished::after {
            content: 'DRAFT';
            position: absolute;
            top: 10px;
            left: 10px;
            background: #6c757d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            z-index: 2;
        }

        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .news-info {
            padding: 1.25rem;
        }

        .news-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
            line-height: 1.4;
        }

        .news-category {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .category-partnership { background-color: var(--success-color); color: white; }
        .category-brigada-eskwela { background-color: var(--info-color); color: white; }
        .category-achievement { background-color: var(--warning-color); color: var(--dark-color); }
        .category-event { background-color: var(--secondary-color); color: white; }
        .category-announcement { background-color: #6f42c1; color: white; }
        .category-other { background-color: #6c757d; color: white; }

        .news-date {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        .news-excerpt {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .news-link-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .news-link-info a {
            color: var(--primary-color);
            text-decoration: none;
            word-break: break-all;
        }

        .news-link-info a:hover {
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

        .image-option-toggle {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
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

        .publish-checkbox {
            background-color: #d1edff;
            border: 2px solid var(--info-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        /* Navigation bar styles */
        .navbar {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand span:first-child {
            color: #006400 !important;
        }

        .navbar-nav .nav-link {
            color: #006400 !important;
        }

        .navbar-nav .nav-link:hover {
            color: #28a745 !important;
        }

        .custom-green {
            color: #006400 !important;
        }

        @media (max-width: 768px) {
            .news-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-title {
                font-size: 1.8rem;
            }
            
            .stats-row .col-md-3 {
                margin-bottom: 1rem;
            }
            
            body {
                padding-top: 120px; /* More padding for mobile navbar */
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
                    <h1 class="admin-title">News & Updates Dashboard</h1>
                    <p class="admin-subtitle">Manage news articles that appear in the News & Partnership Updates section</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column align-items-end">
                        <div class="text-light mb-2">
                            <i class="fas fa-newspaper me-2"></i>
                            <?php echo mysqli_num_rows($result); ?> Total Articles
                        </div>
                        <a href="#uploadSection" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Add New Article
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
                        <div class="stat-label">Total Articles</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Partnership']) ? $category_stats['Partnership'] : 0; ?></div>
                        <div class="stat-label">Partnerships</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Achievement']) ? $category_stats['Achievement'] : 0; ?></div>
                        <div class="stat-label">Achievements</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo isset($category_stats['Brigada Eskwela']) ? $category_stats['Brigada Eskwela'] : 0; ?></div>
                        <div class="stat-label">Brigada Eskwela</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-form" id="uploadSection">
            <h3 class="mb-4">
                <i class="fas fa-newspaper me-2 text-success"></i>
                Submit New News Article
            </h3>
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="news_title" name="news_title" placeholder="Enter article title..." required>
                            <label for="news_title">Article Title</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="news_category" name="news_category" required>
                                <option value="">Select Category</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Brigada Eskwela">Brigada Eskwela</option>
                                <option value="Achievement">Achievement</option>
                                <option value="Event">Event</option>
                                <option value="Announcement">Announcement</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="news_category">Category</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="news_publish_date" name="news_publish_date" required>
                            <label for="news_publish_date">Publish Date</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="news_author" name="news_author" placeholder="Author Name">
                            <label for="news_author">Author</label>
                        </div>
                    </div>
                </div>

                <!-- Image Upload Section -->
                <div class="image-option-toggle">
                    <h5 class="mb-3">
                        <i class="fas fa-image me-2"></i>Article Image
                    </h5>
                    <div class="btn-group mb-3" role="group" aria-label="Image source options">
                        <input type="radio" class="btn-check" name="image_source" id="upload_option" onclick="toggleImageSource('upload')" checked>
                        <label class="btn btn-outline-primary" for="upload_option">
                            <i class="fas fa-upload me-2"></i>Upload Image
                        </label>
                        
                        <input type="radio" class="btn-check" name="image_source" id="url_option" onclick="toggleImageSource('url')">
                        <label class="btn btn-outline-primary" for="url_option">
                            <i class="fas fa-link me-2"></i>Use Image URL
                        </label>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div id="file_upload_section">
                        <div class="file-input-wrapper">
                            <label for="news_image" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt mb-2" style="font-size: 2rem; color: #6c757d;"></i>
                                <div><strong>Click to upload image</strong></div>
                                <div class="text-muted">or drag and drop</div>
                                <div class="text-muted small">JPG, JPEG, PNG, GIF, WebP (Max 10MB)</div>
                            </label>
                            <input type="file" id="news_image" name="news_image" class="file-input" accept="image/*" onchange="previewImage(event)" required>
                        </div>
                        <img id="imagePreview" class="preview-image d-none" alt="Image preview">
                    </div>
                    
                    <!-- URL Input Section -->
                    <div id="url_input_section" class="d-none">
                        <div class="form-floating mb-3">
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                            <label for="image_url">Image URL</label>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Make sure the image URL is publicly accessible and points directly to an image file.
                        </div>
                    </div>
                    
                    <input type="hidden" id="use_image_url" name="use_image_url" value="0">
                </div>

                <div class="form-floating mb-3">
                    <textarea class="form-control" id="news_excerpt" name="news_excerpt" style="height: 100px" placeholder="Brief excerpt for carousel display..." required></textarea>
                    <label for="news_excerpt">Excerpt (Brief summary for carousel)</label>
                </div>

                <div class="form-floating mb-3">
                    <textarea class="form-control" id="news_content" name="news_content" style="height: 200px" placeholder="Full article content..."></textarea>
                    <label for="news_content">Full Content (Optional)</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="url" class="form-control" id="news_link" name="news_link" placeholder="https://example.com/full-article">
                    <label for="news_link">External News Link (Optional)</label>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>News Link:</strong> Add a URL where visitors can read the full article. This creates a "Read More" button that redirects to your link. Leave empty if no external link is needed.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="featured-checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="news_is_featured" name="news_is_featured">
                                <label class="form-check-label fw-bold" for="news_is_featured">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    Mark as Featured Article
                                </label>
                                <div class="form-text">Featured articles appear prominently in the news carousel</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="publish-checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="news_is_published" name="news_is_published" checked>
                                <label class="form-check-label fw-bold" for="news_is_published">
                                    <i class="fas fa-eye text-info me-2"></i>
                                    Publish Article
                                </label>
                                <div class="form-text">Uncheck to save as draft</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" name="submit_news" class="btn btn-success btn-lg">
                        <i class="fas fa-paper-plane me-2"></i> Submit Article
                    </button>
                </div>
            </form>
        </div>

        <!-- News Display -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">
                        <i class="fas fa-newspaper me-2 text-primary"></i>
                        News Articles Gallery
                    </h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="categoryFilter" onchange="filterByCategory()">
                            <option value="">All Categories</option>
                            <option value="Partnership">Partnership</option>
                            <option value="Brigada Eskwela">Brigada Eskwela</option>
                            <option value="Achievement">Achievement</option>
                            <option value="Event">Event</option>
                            <option value="Announcement">Announcement</option>
                            <option value="Other">Other</option>
                        </select>
                        <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="featured">Featured</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="news-grid">
                        <?php while ($news = mysqli_fetch_assoc($result)) : ?>
                            <div class="news-card <?php echo $news['is_featured'] ? 'featured' : ''; ?> <?php echo !$news['is_published'] ? 'unpublished' : ''; ?>" 
                                 data-category="<?php echo $news['category']; ?>" 
                                 data-status="<?php echo !$news['is_published'] ? 'draft' : ($news['is_featured'] ? 'featured' : 'published'); ?>">
                                <img src="<?php echo htmlspecialchars(getCorrectImagePath($news['image_path'])); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                                     onerror="this.src='https://placehold.co/400x200/f8f9fa/6c757d?text=Image+Not+Found';">
                                
                                <div class="news-info">
                                    <div class="news-title"><?php echo htmlspecialchars($news['title']); ?></div>
                                    <span class="news-category category-<?php echo strtolower(str_replace(' ', '-', $news['category'])); ?>">
                                        <?php echo htmlspecialchars($news['category']); ?>
                                    </span>
                                    <div class="news-date">
                                        <i class="fas fa-calendar me-1"></i> 
                                        <?php echo date('M j, Y', strtotime($news['publish_date'])); ?>
                                        <?php if (!empty($news['author'])): ?>
                                        <span class="text-muted"> • by <?php echo htmlspecialchars($news['author']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="news-excerpt">
                                        <?php 
                                        $excerpt = htmlspecialchars($news['excerpt']);
                                        echo strlen($excerpt) > 120 ? substr($excerpt, 0, 120) . '...' : $excerpt; 
                                        ?>
                                    </div>
                                    
                                    <?php if (!empty($news['news_link'])): ?>
                                    <div class="news-link-info">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        <strong>Full Article:</strong> 
                                        <a href="<?php echo htmlspecialchars($news['news_link']); ?>" target="_blank">
                                            <?php echo strlen($news['news_link']) > 35 ? substr($news['news_link'], 0, 35) . '...' : $news['news_link']; ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="action-buttons">
                                        <?php if (!empty($news['news_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($news['news_link']); ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-external-link-alt"></i> View
                                        </a>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-primary btn-sm" onclick="openEditModal(<?php echo $news['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="openDeleteModal(<?php echo $news['id']; ?>, '<?php echo addslashes($news['title']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">No News Articles Yet</h4>
                        <p class="text-muted">Submit your first article to get started!</p>
                        <a href="#uploadSection" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Add First Article
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit News Article
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit_news_id" name="news_id">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                    <label for="edit_title">Article Title</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Partnership">Partnership</option>
                                        <option value="Brigada Eskwela">Brigada Eskwela</option>
                                        <option value="Achievement">Achievement</option>
                                        <option value="Event">Event</option>
                                        <option value="Announcement">Announcement</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label for="edit_category">Category</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="edit_publish_date" name="publish_date" required>
                                    <label for="edit_publish_date">Publish Date</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_author" name="author" placeholder="Author Name">
                                    <label for="edit_author">Author</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="edit_excerpt" name="excerpt" style="height: 100px" required></textarea>
                            <label for="edit_excerpt">Excerpt</label>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="edit_content" name="content" style="height: 150px"></textarea>
                            <label for="edit_content">Full Content</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="url" class="form-control" id="edit_news_link" name="news_link" placeholder="https://example.com/full-article">
                            <label for="edit_news_link">External News Link</label>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="featured-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                        <label class="form-check-label fw-bold" for="edit_is_featured">
                                            <i class="fas fa-star text-warning me-2"></i>
                                            Mark as Featured Article
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="publish-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_published" name="is_published">
                                        <label class="form-check-label fw-bold" for="edit_is_published">
                                            <i class="fas fa-eye text-info me-2"></i>
                                            Publish Article
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Article
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
                        <input type="hidden" id="delete_news_id" name="news_id">
                        <input type="hidden" name="action" value="delete">
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center mb-3">Are you sure you want to delete "<strong id="deleteNewsTitle"></strong>"?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone and will permanently delete the article and its image file.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Article
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // News data for editing
        const newsData = {};
        
        <?php
        // Reset mysqli data pointer to beginning
        mysqli_data_seek($result, 0);
        
        // Store all news data for JavaScript
        while ($news = mysqli_fetch_assoc($result)) {
            echo "newsData[" . $news['id'] . "] = {
                title: '" . addslashes($news['title']) . "',
                category: '" . addslashes($news['category']) . "',
                excerpt: '" . addslashes($news['excerpt']) . "',
                content: '" . addslashes($news['content']) . "',
                news_link: '" . addslashes($news['news_link']) . "',
                publish_date: '" . $news['publish_date'] . "',
                author: '" . addslashes($news['author']) . "',
                is_featured: " . ($news['is_featured'] ? 'true' : 'false') . ",
                is_published: " . ($news['is_published'] ? 'true' : 'false') . "
            };\n";
        }
        ?>

        // Set today's date as default
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('news_publish_date').value = today;
        });
        
        // Toggle between file upload and URL input
        function toggleImageSource(source) {
            const fileSection = document.getElementById('file_upload_section');
            const urlSection = document.getElementById('url_input_section');
            const useImageUrl = document.getElementById('use_image_url');
            
            if (source === 'url') {
                fileSection.classList.add('d-none');
                urlSection.classList.remove('d-none');
                useImageUrl.value = '1';
                document.getElementById('news_image').removeAttribute('required');
            } else {
                fileSection.classList.remove('d-none');
                urlSection.classList.add('d-none');
                useImageUrl.value = '0';
                document.getElementById('news_image').setAttribute('required', 'required');
            }
        }
        
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
        function openEditModal(newsId) {
            const news = newsData[newsId];
            
            document.getElementById('edit_news_id').value = newsId;
            document.getElementById('edit_title').value = news.title;
            document.getElementById('edit_category').value = news.category;
            document.getElementById('edit_excerpt').value = news.excerpt;
            document.getElementById('edit_content').value = news.content;
            document.getElementById('edit_news_link').value = news.news_link;
            document.getElementById('edit_publish_date').value = news.publish_date;
            document.getElementById('edit_author').value = news.author;
            document.getElementById('edit_is_featured').checked = news.is_featured;
            document.getElementById('edit_is_published').checked = news.is_published;
            
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
        
        // Open delete modal
        function openDeleteModal(newsId, newsTitle) {
            document.getElementById('delete_news_id').value = newsId;
            document.getElementById('deleteNewsTitle').textContent = newsTitle;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Filter by category
        function filterByCategory() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            filterCards();
        }

        // Filter by status
        function filterByStatus() {
            const selectedStatus = document.getElementById('statusFilter').value;
            filterCards();
        }

        function filterCards() {
            const selectedCategory = document.getElementById('categoryFilter').value;
            const selectedStatus = document.getElementById('statusFilter').value;
            const newsCards = document.querySelectorAll('.news-card');
            
            newsCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const cardStatus = card.getAttribute('data-status');
                
                const categoryMatch = selectedCategory === '' || cardCategory === selectedCategory;
                const statusMatch = selectedStatus === '' || cardStatus === selectedStatus;
                
                if (categoryMatch && statusMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-warning):not(.alert-info)');
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
?>