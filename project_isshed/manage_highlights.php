<?php
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // Handle file upload
                    $imagePath = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = handleFileUpload($_FILES['image'], 'uploads/highlights/');
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO project_highlights (title, description, image_path, category, event_date, display_order, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'] ?? '',
                        $imagePath,
                        $_POST['category'] ?? '',
                        $_POST['event_date'] ?? null,
                        $_POST['display_order'] ?? 0,
                        isset($_POST['is_featured']) ? 1 : 0
                    ]);
                    $message = "Highlight added successfully!";
                    break;
                    
                case 'edit':
                    $imagePath = $_POST['existing_image'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = handleFileUpload($_FILES['image'], 'uploads/highlights/');
                        // Delete old image if exists
                        if (!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])) {
                            unlink($_POST['existing_image']);
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE project_highlights SET title=?, description=?, image_path=?, category=?, event_date=?, display_order=?, is_featured=? WHERE id=?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'] ?? '',
                        $imagePath,
                        $_POST['category'] ?? '',
                        $_POST['event_date'] ?? null,
                        $_POST['display_order'] ?? 0,
                        isset($_POST['is_featured']) ? 1 : 0,
                        $_POST['id']
                    ]);
                    $message = "Highlight updated successfully!";
                    break;
                    
                case 'delete':
                    // Get image path before deleting
                    $stmt = $pdo->prepare("SELECT image_path FROM project_highlights WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $highlight = $stmt->fetch();
                    
                    // Delete the highlight
                    $stmt = $pdo->prepare("DELETE FROM project_highlights WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    // Delete associated image
                    if ($highlight && !empty($highlight['image_path']) && file_exists($highlight['image_path'])) {
                        unlink($highlight['image_path']);
                    }
                    
                    $message = "Highlight deleted successfully!";
                    break;
                    
                case 'toggle_status':
                    $stmt = $pdo->prepare("UPDATE project_highlights SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Highlight status updated!";
                    break;
                    
                case 'toggle_featured':
                    $stmt = $pdo->prepare("UPDATE project_highlights SET is_featured = NOT is_featured WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Featured status updated!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all highlights
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM project_highlights ORDER BY display_order ASC, created_at DESC");
$highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single highlight for editing
$edit_highlight = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM project_highlights WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_highlight = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get distinct categories for dropdown
$stmt = $pdo->query("SELECT DISTINCT category FROM project_highlights WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Highlights Manager - Project ISSHED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .highlight-preview img {
            max-width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .admin-header {
            background: linear-gradient(135deg, #800000, #a52a2a);
            color: white;
            padding: 2rem 0;
        }
        .gallery-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .gallery-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .gallery-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .gallery-item-content {
            padding: 0.75rem;
        }
        .featured-star {
            color: gold;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-images"></i> Project Highlights Manager</h1>
            <p class="mb-0">Manage Project ISSHED Gallery & Highlights</p>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-plus-circle"></i> <?php echo $edit_highlight ? 'Edit' : 'Add'; ?> Highlight</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_highlight ? 'edit' : 'add'; ?>">
                            <?php if ($edit_highlight): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_highlight['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo $edit_highlight['image_path']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?php echo $edit_highlight ? htmlspecialchars($edit_highlight['title']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo $edit_highlight ? htmlspecialchars($edit_highlight['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input list="categories" name="category" class="form-control" 
                                       value="<?php echo $edit_highlight ? htmlspecialchars($edit_highlight['category']) : ''; ?>">
                                <datalist id="categories">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php endforeach; ?>
                                    <option value="Health Programs">
                                    <option value="Education">
                                    <option value="Community Outreach">
                                    <option value="Student Development">
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" 
                                       value="<?php echo $edit_highlight ? $edit_highlight['event_date'] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?php echo $edit_highlight ? $edit_highlight['display_order'] : '0'; ?>">
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                           <?php echo ($edit_highlight && $edit_highlight['is_featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <i class="bi bi-star-fill text-warning"></i> Featured Highlight
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image *</label>
                                <input type="file" name="image" class="form-control" accept="image/*" 
                                       <?php echo $edit_highlight ? '' : 'required'; ?>>
                                <?php if ($edit_highlight && $edit_highlight['image_path']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo $edit_highlight['image_path']; ?>" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="d-block text-muted">Current: <?php echo basename($edit_highlight['image_path']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> <?php echo $edit_highlight ? 'Update' : 'Add'; ?> Highlight
                                </button>
                                <?php if ($edit_highlight): ?>
                                    <a href="manage_highlights.php" class="btn btn-secondary">
                                        <i class="bi bi-x-lg"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Highlights List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-grid"></i> Project Highlights</h5>
                        <div>
                            <span class="badge bg-primary"><?php echo count($highlights); ?> total</span>
                            <span class="badge bg-warning"><?php echo count(array_filter($highlights, fn($h) => $h['is_featured'])); ?> featured</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($highlights)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-image fs-1 text-muted"></i>
                                <p class="text-muted">No highlights found. Add your first highlight!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Image</th>
                                            <th>Featured</th>
                                            <th>Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($highlights as $highlight): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo $highlight['display_order']; ?></span></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($highlight['title']); ?></strong>
                                                    <?php if ($highlight['description']): ?>
                                                        <br><small class="text-muted"><?php echo substr(htmlspecialchars($highlight['description']), 0, 40) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($highlight['category']): ?>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($highlight['category']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($highlight['event_date']): ?>
                                                        <?php echo formatDate($highlight['event_date'], 'M j, Y'); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="highlight-preview">
                                                    <?php if ($highlight['image_path'] && file_exists($highlight['image_path'])): ?>
                                                        <img src="<?php echo $highlight['image_path']; ?>" alt="Preview">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_featured">
                                                        <input type="hidden" name="id" value="<?php echo $highlight['id']; ?>">
                                                        <button type="submit" class="btn btn-sm <?php echo $highlight['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                                            <i class="bi bi-star<?php echo $highlight['is_featured'] ? '-fill' : ''; ?>"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?php echo $highlight['id']; ?>">
                                                        <button type="submit" class="btn btn-sm <?php echo $highlight['is_active'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                                            <i class="bi bi-<?php echo $highlight['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?edit=<?php echo $highlight['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this highlight?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $highlight['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger">
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

                            <!-- Gallery Preview -->
                            <div class="mt-4">
                                <h6><i class="bi bi-eye"></i> Gallery Preview</h6>
                                <div class="gallery-preview">
                                    <?php foreach (array_slice($highlights, 0, 8) as $highlight): ?>
                                        <?php if ($highlight['is_active'] && $highlight['image_path'] && file_exists($highlight['image_path'])): ?>
                                            <div class="gallery-item">
                                                <img src="<?php echo $highlight['image_path']; ?>" alt="<?php echo htmlspecialchars($highlight['title']); ?>">
                                                <div class="gallery-item-content">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="fw-bold"><?php echo htmlspecialchars($highlight['title']); ?></small>
                                                        <?php if ($highlight['is_featured']): ?>
                                                            <i class="bi bi-star-fill featured-star"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($highlight['category']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($highlight['category']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="manage_timeline.php" class="btn btn-outline-primary">
                        <i class="bi bi-clock-history"></i> Manage Timeline
                    </a>
                    <a href="proj-isshed.php" class="btn btn-outline-success">
                        <i class="bi bi-eye"></i> View Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>