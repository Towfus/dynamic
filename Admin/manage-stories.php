<?php
// admin/manage-stories.php - Manage Impact Stories
require_once '../shared/config.php';

startSecureSession();
requireLogin();

$currentAdmin = getCurrentAdmin();
$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_POST) {
    if ($action == 'add' || $action == 'edit') {
        $title = sanitizeInput($_POST['title'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $story_date = $_POST['story_date'] ?? '';
        $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
        $full_story = $_POST['full_story'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';
        
        $image_url = '';
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploaded = uploadFile($_FILES['image'], 'stories');
            if ($uploaded) {
                $image_url = UPLOAD_URL . 'stories/' . $uploaded;
            } else {
                $error = 'Failed to upload image. Please check file format and size.';
            }
        } elseif ($action == 'edit' && !empty($_POST['current_image'])) {
            $image_url = $_POST['current_image'];
        }
        
        if (empty($title) || empty($category) || empty($story_date) || empty($excerpt)) {
            $error = 'Please fill in all required fields.';
        } elseif (empty($image_url)) {
            $error = 'Please upload an image for the story.';
        } else {
            try {
                if ($action == 'add') {
                    $stmt = $db->prepare("INSERT INTO impact_stories (title, category, story_date, excerpt, image_url, full_story, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $category, $story_date, $excerpt, $image_url, $full_story, $is_featured, $status]);
                    
                    logActivity($currentAdmin['id'], 'Added new impact story', 'impact_stories', $db->lastInsertId());
                    $success = 'Impact story added successfully!';
                    
                } elseif ($action == 'edit' && $editId) {
                    // Get old values for logging
                    $oldStmt = $db->prepare("SELECT * FROM impact_stories WHERE id = ?");
                    $oldStmt->execute([$editId]);
                    $oldValues = $oldStmt->fetch();
                    
                    $stmt = $db->prepare("UPDATE impact_stories SET title = ?, category = ?, story_date = ?, excerpt = ?, image_url = ?, full_story = ?, is_featured = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $category, $story_date, $excerpt, $image_url, $full_story, $is_featured, $status, $editId]);
                    
                    $newValues = compact('title', 'category', 'story_date', 'excerpt', 'image_url', 'full_story', 'is_featured', 'status');
                    logActivity($currentAdmin['id'], 'Updated impact story', 'impact_stories', $editId, $oldValues, $newValues);
                    $success = 'Impact story updated successfully!';
                }
                
                // Redirect to list after successful add/edit
                if (!$error) {
                    header('Location: manage-stories.php?success=' . urlencode($success));
                    exit;
                }
                
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle delete action
if ($action == 'delete' && $editId) {
    try {
        // Get story details for logging
        $storyStmt = $db->prepare("SELECT * FROM impact_stories WHERE id = ?");
        $storyStmt->execute([$editId]);
        $story = $storyStmt->fetch();
        
        if ($story) {
            $deleteStmt = $db->prepare("DELETE FROM impact_stories WHERE id = ?");
            $deleteStmt->execute([$editId]);
            
            // Delete associated image file
            if ($story['image_url'] && strpos($story['image_url'], UPLOAD_URL) === 0) {
                $filename = str_replace(UPLOAD_URL, '', $story['image_url']);
                deleteFile($filename);
            }
            
            logActivity($currentAdmin['id'], 'Deleted impact story: ' . $story['title'], 'impact_stories', $editId, $story);
            $success = 'Impact story deleted successfully!';
        }
        
        header('Location: manage-stories.php?success=' . urlencode($success));
        exit;
        
    } catch (PDOException $e) {
        $error = 'Error deleting story: ' . $e->getMessage();
    }
}

// Get story for editing
$editStory = null;
if ($action == 'edit' && $editId) {
    $editStmt = $db->prepare("SELECT * FROM impact_stories WHERE id = ?");
    $editStmt->execute([$editId]);
    $editStory = $editStmt->fetch();
    
    if (!$editStory) {
        $error = 'Story not found.';
        $action = 'list';
    }
}

// Get all stories for listing
if ($action == 'list') {
    $storiesStmt = $db->query("SELECT * FROM impact_stories ORDER BY created_at DESC");
    $stories = $storiesStmt->fetchAll();
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Impact Stories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php 
                        switch($action) {
                            case 'add': echo 'Add New Impact Story'; break;
                            case 'edit': echo 'Edit Impact Story'; break;
                            default: echo 'Manage Impact Stories'; break;
                        }
                        ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($action == 'list'): ?>
                        <a href="manage-stories.php?action=add" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-circle"></i> Add New Story
                        </a>
                        <?php else: ?>
                        <a href="manage-stories.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($action == 'list'): ?>
                    <!-- Stories List -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">All Impact Stories</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($stories)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-newspaper display-1 text-muted"></i>
                                    <h4 class="mt-3">No Stories Yet</h4>
                                    <p class="text-muted">Get started by adding your first impact story.</p>
                                    <a href="manage-stories.php?action=add" class="btn btn-success">
                                        <i class="bi bi-plus-circle"></i> Add First Story
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Date</th>
                                                <th>Featured</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stories as $story): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($story['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($story['title']); ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($story['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(truncateText($story['excerpt'], 80)); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo getBadgeClass($story['category']); ?>">
                                                        <?php echo htmlspecialchars($story['category']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($story['story_date']); ?></td>
                                                <td>
                                                    <?php if ($story['is_featured']): ?>
                                                        <span class="badge bg-warning">Featured</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $story['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($story['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="manage-stories.php?action=edit&id=<?php echo $story['id']; ?>" 
                                                           class="btn btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="manage-stories.php?action=delete&id=<?php echo $story['id']; ?>" 
                                                           class="btn btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this story?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
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

                <?php else: ?>
                    <!-- Add/Edit Form -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <?php echo $action == 'add' ? 'Add New' : 'Edit'; ?> Impact Story
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Story Title *</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="title" 
                                                   name="title" 
                                                   value="<?php echo htmlspecialchars($editStory['title'] ?? ''); ?>"
                                                   required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="category" class="form-label">Category *</label>
                                                <select class="form-select" id="category" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="Technology" <?php echo ($editStory['category'] ?? '') == 'Technology' ? 'selected' : ''; ?>>Technology</option>
                                                    <option value="Scholarships" <?php echo ($editStory['category'] ?? '') == 'Scholarships' ? 'selected' : ''; ?>>Scholarships</option>
                                                    <option value="Training" <?php echo ($editStory['category'] ?? '') == 'Training' ? 'selected' : ''; ?>>Training</option>
                                                    <option value="Facilities" <?php echo ($editStory['category'] ?? '') == 'Facilities' ? 'selected' : ''; ?>>Facilities</option>
                                                    <option value="Infrastructure" <?php echo ($editStory['category'] ?? '') == 'Infrastructure' ? 'selected' : ''; ?>>Infrastructure</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="story_date" class="form-label">Story Date *</label>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="story_date" 
                                                       name="story_date" 
                                                       value="<?php echo $editStory['story_date'] ?? date('Y-m-d'); ?>"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="excerpt" class="form-label">Story Excerpt *</label>
                                            <textarea class="form-control" 
                                                      id="excerpt" 
                                                      name="excerpt" 
                                                      rows="3" 
                                                      required 
                                                      placeholder="Brief description that will appear on the homepage..."><?php echo htmlspecialchars($editStory['excerpt'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="full_story" class="form-label">Full Story (Optional)</label>
                                            <textarea class="form-control" 
                                                      id="full_story" 
                                                      name="full_story" 
                                                      rows="6"
                                                      placeholder="Complete story content..."><?php echo htmlspecialchars($editStory['full_story'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Story Image *</label>
                                            <?php if ($editStory && $editStory['image_url']): ?>
                                                <div class="mb-3">
                                                    <img src="<?php echo htmlspecialchars($editStory['image_url']); ?>" 
                                                         alt="Current image" 
                                                         class="img-fluid rounded">
                                                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editStory['image_url']); ?>">
                                                    <small class="text-muted d-block mt-1">Current image - upload new to replace</small>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" 
                                                   class="form-control" 
                                                   id="image" 
                                                   name="image" 
                                                   accept="image/*"
                                                   <?php echo $editStory ? '' : 'required'; ?>>
                                            <div class="form-text">Upload JPG, PNG, or GIF (max 5MB)</div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_featured" 
                                                       name="is_featured"
                                                       <?php echo ($editStory['is_featured'] ?? false) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_featured">
                                                    <strong>Featured Story</strong>
                                                    <small class="text-muted d-block">Show on homepage</small>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($editStory['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($editStory['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="manage-stories.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg"></i> 
                                        <?php echo $action == 'add' ? 'Add Story' : 'Update Story'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/admin-script.js"></script>
</body>
</html>