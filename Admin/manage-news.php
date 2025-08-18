<?php
require_once '../shared/config.php';
requireLogin();

$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $excerpt = trim($_POST['excerpt']);
                $content = trim($_POST['content']);
                $category = $_POST['category'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                $news_date = $_POST['news_date'];
                
                // Handle image upload
                $imagePath = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $uploadDir = '../shared/uploads/news/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $imagePath = 'uploads/news/' . $fileName;
                    }
                }
                
                if (!empty($title) && !empty($content)) {
                    $stmt = $pdo->prepare("INSERT INTO news_updates (title, excerpt, content, image_url, category, featured, news_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    if ($stmt->execute([$title, $excerpt, $content, $imagePath, $category, $featured, $news_date])) {
                        $message = 'News article added successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error adding news article.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = trim($_POST['title']);
                $excerpt = trim($_POST['excerpt']);
                $content = trim($_POST['content']);
                $category = $_POST['category'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                $news_date = $_POST['news_date'];
                
                // Handle image upload
                $imagePath = $_POST['current_image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $uploadDir = '../shared/uploads/news/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        // Delete old image if exists
                        if (!empty($_POST['current_image']) && file_exists('../shared/' . $_POST['current_image'])) {
                            unlink('../shared/' . $_POST['current_image']);
                        }
                        $imagePath = 'uploads/news/' . $fileName;
                    }
                }
                
                if (!empty($title) && !empty($content)) {
                    $stmt = $pdo->prepare("UPDATE news_updates SET title = ?, excerpt = ?, content = ?, image_url = ?, category = ?, featured = ?, news_date = ?, updated_at = NOW() WHERE id = ?");
                    if ($stmt->execute([$title, $excerpt, $content, $imagePath, $category, $featured, $news_date, $id])) {
                        $message = 'News article updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error updating news article.';
                        $messageType = 'danger';
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("SELECT image_url FROM news_updates WHERE id = ?");
                $stmt->execute([$id]);
                $news = $stmt->fetch();
                
                if ($news && !empty($news['image_url']) && file_exists('../shared/' . $news['image_url'])) {
                    unlink('../shared/' . $news['image_url']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM news_updates WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'News article deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting news article.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Fetch all news with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT * FROM news_updates ORDER BY featured DESC, news_date DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$newsArticles = $stmt->fetchAll();

// Get total count for pagination
$countStmt = $pdo->query("SELECT COUNT(*) FROM news_updates");
$totalNews = $countStmt->fetchColumn();
$totalPages = ceil($totalNews / $limit);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage News & Updates</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                    <i class="fas fa-plus"></i> Add News Article
                </button>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- News Articles Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($newsArticles)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($newsArticles as $news): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($news['image_url']): ?>
                                            <img src="../shared/<?php echo htmlspecialchars($news['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                                 class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($news['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars(truncateText($news['excerpt'], 80)); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo getBadgeClass($news['category']); ?>">
                                            <?php echo ucfirst($news['category']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($news['news_date']); ?></td>
                                    <td>
                                        <?php if ($news['featured']): ?>
                                            <span class="badge bg-warning">Featured</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-news-btn" 
                                                data-news='<?php echo json_encode($news); ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this news article?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="News pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No News Articles Yet</h4>
                        <p class="text-muted">Add your first news article to get started!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add News Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="partnership">Partnership</option>
                                <option value="announcement">Announcement</option>
                                <option value="event">Event</option>
                                <option value="achievement">Achievement</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" name="news_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Featured Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="form-text text-muted">Recommended size: 1200x600px</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea class="form-control" name="excerpt" rows="3" placeholder="Brief summary of the article..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="content" rows="8" required placeholder="Full article content..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="featured" id="featured">
                            <label class="form-check-label" for="featured">
                                Featured Article (will appear in carousel)
                            </label>
                        </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit News Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit News Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <input type="hidden" name="current_image" id="edit-current-image">
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" id="edit-title" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" id="edit-category" required>
                                <option value="">Select Category</option>
                                <option value="partnership">Partnership</option>
                                <option value="announcement">Announcement</option>
                                <option value="event">Event</option>
                                <option value="achievement">Achievement</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" name="news_date" id="edit-news-date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div id="current-image-preview"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep current image</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea class="form-control" name="excerpt" id="edit-excerpt" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="content" id="edit-content" rows="8" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="featured" id="edit-featured">
                            <label class="form-check-label" for="edit-featured">
                                Featured Article (will appear in carousel)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit news button clicks
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-news-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const news = JSON.parse(this.dataset.news);
            
            document.getElementById('edit-id').value = news.id;
            document.getElementById('edit-title').value = news.title;
            document.getElementById('edit-category').value = news.category;
            document.getElementById('edit-news-date').value = news.news_date;
            document.getElementById('edit-excerpt').value = news.excerpt || '';
            document.getElementById('edit-content').value = news.content || '';
            document.getElementById('edit-current-image').value = news.image_url || '';
            document.getElementById('edit-featured').checked = news.featured == 1;
            
            // Show current image preview
            const imagePreview = document.getElementById('current-image-preview');
            if (news.image_url) {
                imagePreview.innerHTML = `<img src="../shared/${news.image_url}" alt="${news.title}" style="max-width: 200px; max-height: 150px; object-fit: cover;">`;
            } else {
                imagePreview.innerHTML = '<span class="text-muted">No image uploaded</span>';
            }
            
            const editModal = new bootstrap.Modal(document.getElementById('editNewsModal'));
            editModal.show();
        });
    });
});
</script>

<style>
.badge-partnership { background-color: #28a745; }
.badge-announcement { background-color: #17a2b8; }
.badge-event { background-color: #ffc107; color: #212529; }
.badge-achievement { background-color: #6f42c1; }
</style>

<?php include 'includes/footer.php'; ?>