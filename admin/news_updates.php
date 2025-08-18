<?php
require_once '../config/database.php';
require_once '../helpers/file_upload.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_news'])) {
        $uploadResult = uploadImage($_FILES['image'], 'news');
        
        if ($uploadResult['success']) {
            $query = "INSERT INTO news_updates 
                     (title, category, news_date, excerpt, image_url, full_content, is_featured, status, sort_order) 
                     VALUES 
                     (:title, :category, :news_date, :excerpt, :image_url, :full_content, :is_featured, :status, :sort_order)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':category' => $_POST['category'],
                ':news_date' => $_POST['news_date'],
                ':excerpt' => $_POST['excerpt'],
                ':image_url' => $uploadResult['file_path'],
                ':full_content' => $_POST['full_content'],
                ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                ':status' => $_POST['status'],
                ':sort_order' => $_POST['sort_order']
            ]);
            
            $_SESSION['message'] = "News item added successfully!";
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } elseif (isset($_POST['update_news'])) {
        $image_url = $_POST['existing_image'];
        
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = uploadImage($_FILES['image'], 'news');
            if ($uploadResult['success']) {
                $image_url = $uploadResult['file_path'];
                if (file_exists($_POST['existing_image'])) {
                    unlink($_POST['existing_image']);
                }
            } else {
                $_SESSION['error'] = $uploadResult['error'];
            }
        }
        
        $query = "UPDATE news_updates SET 
                 title = :title,
                 category = :category,
                 news_date = :news_date,
                 excerpt = :excerpt,
                 image_url = :image_url,
                 full_content = :full_content,
                 is_featured = :is_featured,
                 status = :status,
                 sort_order = :sort_order
                 WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':title' => $_POST['title'],
            ':category' => $_POST['category'],
            ':news_date' => $_POST['news_date'],
            ':excerpt' => $_POST['excerpt'],
            ':image_url' => $image_url,
            ':full_content' => $_POST['full_content'],
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':status' => $_POST['status'],
            ':sort_order' => $_POST['sort_order'],
            ':id' => $_POST['news_id']
        ]);
        
        $_SESSION['message'] = "News item updated successfully!";
    } elseif (isset($_POST['delete_news'])) {
        $query = "SELECT image_url FROM news_updates WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['news_id']]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($news && file_exists($news['image_url'])) {
            unlink($news['image_url']);
        }
        
        $query = "DELETE FROM news_updates WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['news_id']]);
        
        $_SESSION['message'] = "News item deleted successfully!";
    }
    
    header("Location: news_updates.php");
    exit();
}

// Get all news items for display
$query = "SELECT * FROM news_updates ORDER BY is_featured DESC, sort_order, news_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News & Updates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .news-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
        }
        .badge-partnership { background-color: #4e73df; }
        .badge-brigada { background-color: #1cc88a; }
        .badge-achievement { background-color: #f6c23e; }
        .badge-event { background-color: #e74a3b; }
        .badge-announcement { background-color: #36b9cc; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Manage News & Partnership Updates</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Add New News Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Add New News Item</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="partnership">Partnership</option>
                                    <option value="brigada">Brigada Eskwela</option>
                                    <option value="achievement">Achievement</option>
                                    <option value="event">Event</option>
                                    <option value="announcement">Announcement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="news_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="news_date" name="news_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required></textarea>
                        <small class="text-muted">Short summary displayed in the carousel</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_content" class="form-label">Full Content</label>
                        <textarea class="form-control" id="full_content" name="full_content" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                            <label class="form-check-label" for="is_featured">Featured Item</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Featured Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <small class="text-muted">Recommended size: 1200x800 pixels</small>
                    </div>
                    
                    <button type="submit" name="add_news" class="btn btn-primary">Add News Item</button>
                </form>
            </div>
        </div>
        
        <!-- News Items List -->
        <div class="card">
            <div class="card-header">
                <h2>Current News Items</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newsItems as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>" 
                                         class="news-preview">
                                </td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = 'badge-' . $item['category'];
                                    $categoryName = ucfirst(str_replace('_', ' ', $item['category']));
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $categoryName ?></span>
                                </td>
                                <td><?= date('M j, Y', strtotime($item['news_date'])) ?></td>
                                <td>
                                    <?php if ($item['status'] == 'published'): ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php elseif ($item['status'] == 'draft'): ?>
                                        <span class="badge bg-secondary">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['is_featured']): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="<?= $item['id'] ?>"
                                            data-title="<?= htmlspecialchars($item['title']) ?>"
                                            data-category="<?= htmlspecialchars($item['category']) ?>"
                                            data-news-date="<?= $item['news_date'] ?>"
                                            data-excerpt="<?= htmlspecialchars($item['excerpt']) ?>"
                                            data-full-content="<?= htmlspecialchars($item['full_content']) ?>"
                                            data-is-featured="<?= $item['is_featured'] ?>"
                                            data-status="<?= $item['status'] ?>"
                                            data-sort-order="<?= $item['sort_order'] ?>"
                                            data-image-url="<?= htmlspecialchars($item['image_url']) ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="delete_news" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this news item?')">
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
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit News Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="news_id" id="edit_news_id">
                        <input type="hidden" name="existing_image" id="edit_existing_image">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="edit_title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="partnership">Partnership</option>
                                        <option value="brigada">Brigada Eskwela</option>
                                        <option value="achievement">Achievement</option>
                                        <option value="event">Event</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_news_date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="edit_news_date" name="news_date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="edit_sort_order" name="sort_order">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status">
                                        <option value="published">Published</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="edit_excerpt" name="excerpt" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_full_content" class="form-label">Full Content</label>
                            <textarea class="form-control" id="edit_full_content" name="full_content" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                <label class="form-check-label" for="edit_is_featured">Featured Item</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <small class="text-muted">Leave blank to keep current image</small>
                            <div class="mt-2">
                                <img id="edit_current_image" src="" alt="Current Image" class="news-preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_news" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                const newsId = this.getAttribute('data-id');
                
                // Set all the form values
                document.getElementById('edit_news_id').value = newsId;
                document.getElementById('edit_title').value = this.getAttribute('data-title');
                document.getElementById('edit_category').value = this.getAttribute('data-category');
                document.getElementById('edit_news_date').value = this.getAttribute('data-news-date');
                document.getElementById('edit_excerpt').value = this.getAttribute('data-excerpt');
                document.getElementById('edit_full_content').value = this.getAttribute('data-full-content');
                document.getElementById('edit_sort_order').value = this.getAttribute('data-sort-order');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
                
                // Handle featured checkbox
                document.getElementById('edit_is_featured').checked = this.getAttribute('data-is-featured') === '1';
                
                // Handle image
                const imageUrl = this.getAttribute('data-image-url');
                document.getElementById('edit_existing_image').value = imageUrl;
                document.getElementById('edit_current_image').src = imageUrl;
                
                modal.show();
            });
        });
    </script>
</body>
</html>