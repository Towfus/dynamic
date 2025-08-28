<?php
// Authentication check would go here
require_once '../config/database.php';
require_once '../helpers/file_upload.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_story'])) {
        // Handle image upload
        $uploadResult = uploadImage($_FILES['story_image']);

        if ($uploadResult['success']) {
            // Add new story
            $query = "INSERT INTO impact_stories 
                     (title, category, story_date, excerpt, image_url, full_story, is_featured, status) 
                     VALUES 
                     (:title, :category, :story_date, :excerpt, :image_url, :full_story, :is_featured, :status)";

            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':category' => $_POST['category'],
                ':story_date' => $_POST['story_date'],
                ':excerpt' => $_POST['excerpt'],
                ':image_url' => $uploadResult['file_path'],
                ':full_story' => $_POST['full_story'],
                ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                ':status' => $_POST['status']
            ]);

            $_SESSION['message'] = "Story added successfully!";
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } elseif (isset($_POST['update_story'])) {
        // Handle image update if new image is uploaded
        $image_url = $_POST['existing_image'];

        if (!empty($_FILES['story_image']['name'])) {
            $uploadResult = uploadImage($_FILES['story_image']);
            if ($uploadResult['success']) {
                $image_url = $uploadResult['file_path'];
                // Delete old image if needed
                if (file_exists($_POST['existing_image'])) {
                    unlink($_POST['existing_image']);
                }
            } else {
                $_SESSION['error'] = $uploadResult['error'];
            }
        }

        // Update story
        $query = "UPDATE impact_stories SET 
                 title = :title,
                 category = :category,
                 story_date = :story_date,
                 excerpt = :excerpt,
                 image_url = :image_url,
                 full_story = :full_story,
                 is_featured = :is_featured,
                 status = :status
                 WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':title' => $_POST['title'],
            ':category' => $_POST['category'],
            ':story_date' => $_POST['story_date'],
            ':excerpt' => $_POST['excerpt'],
            ':image_url' => $image_url,
            ':full_story' => $_POST['full_story'],
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':status' => $_POST['status'],
            ':id' => $_POST['story_id']
        ]);

        $_SESSION['message'] = "Story updated successfully!";
    } elseif (isset($_POST['delete_story'])) {
        // First get image path to delete it
        $query = "SELECT image_url FROM impact_stories WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['story_id']]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete the image file
        if ($story && file_exists($story['image_url'])) {
            unlink($story['image_url']);
        }

        // Delete the story
        $query = "DELETE FROM impact_stories WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['story_id']]);

        $_SESSION['message'] = "Story deleted successfully!";
    }
    
    // Redirect to prevent form resubmission and show the full page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get all stories for display
$query = "SELECT * FROM impact_stories ORDER BY story_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Stories Management - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="impact-stories.php" class="active">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>
                <a href="admin-document.php">
                    <i class="fas fa-file-pdf"></i>
                    <span>SMN Documents</span>
                </a>
                <a href="news_updates.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>News Updates Management</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">System</div>
                <a href="partners.php">
                    <i class="fas fa-cog"></i>
                    <span>Partners</span>
                </a>

                <a href="project-highlights.php">
                    <i class="fas fa-cog"></i>
                    <span>Project Highlights</span>
                </a>

                <a href="timeline-management.php">
                    <i class="fas fa-cog"></i>
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
                <h1 class="text-xl font-semibold text-gray-800">Impact Stories Management</h1>
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

        <div class="container mt-4">
            <h1>Manage Impact Stories</h1>

            <!-- Display messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Add New Story Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2>Add New Story</h2>
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
                                    <label for="story_date" class="form-label">Story Date</label>
                                    <input type="date" class="form-control" id="story_date" name="story_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt (Short Description)</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="full_story" class="form-label">Full Story URL</label>
                            <input type="url" class="form-control" id="full_story" name="full_story" required placeholder="https://example.com/story-details">
                        </div>

                        <div class="mb-3">
                            <label for="story_image" class="form-label">Story Image</label>
                            <input type="file" class="form-control" id="story_image" name="story_image" accept="image/*" required>
                            <small class="text-muted">Recommended size: 600x400 pixels</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                    <label class="form-check-label" for="is_featured">Featured Story</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="add_story" class="btn btn-primary">Add Story</button>
                    </form>
                </div>
            </div>

            <!-- Stories List -->
            <div class="card">
                <div class="card-header">
                    <h2>Current Stories</h2>
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
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Full Story URL</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stories as $story): ?>
                                    <tr>
                                        <td><img src="<?= htmlspecialchars($story['image_url']) ?>" alt="Story Image" style="width: 80px; height: auto;"></td>
                                        <td><?= htmlspecialchars($story['title']) ?></td>
                                        <td><?= htmlspecialchars($story['category']) ?></td>
                                        <td><?= date('M d, Y', strtotime($story['story_date'])) ?></td>
                                        <td><?= $story['is_featured'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                        <td><?= ucfirst($story['status']) ?></td>
                                        <td><a href="<?= htmlspecialchars($story['full_story']) ?>" target="_blank">View Link</a></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-btn"
                                                data-id="<?= $story['id'] ?>"
                                                data-title="<?= htmlspecialchars($story['title']) ?>"
                                                data-category="<?= htmlspecialchars($story['category']) ?>"
                                                data-story-date="<?= $story['story_date'] ?>"
                                                data-excerpt="<?= htmlspecialchars($story['excerpt']) ?>"
                                                data-image-url="<?= htmlspecialchars($story['image_url']) ?>"
                                                data-full-story="<?= htmlspecialchars($story['full_story']) ?>"
                                                data-is-featured="<?= $story['is_featured'] ?>"
                                                data-status="<?= $story['status'] ?>">
                                                Edit
                                            </button>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                                <button type="submit" name="delete_story" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this story?')">
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
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Story</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="story_id" id="edit_story_id">
                        <input type="hidden" name="existing_image" id="edit_existing_image">

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
                                    <label for="edit_story_date" class="form-label">Story Date</label>
                                    <input type="date" class="form-control" id="edit_story_date" name="story_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_excerpt" class="form-label">Excerpt (Short Description)</label>
                            <textarea class="form-control" id="edit_excerpt" name="excerpt" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_full_story" class="form-label">Full Story URL</label>
                            <input type="url" class="form-control" id="edit_full_story" name="full_story" required placeholder="https://example.com/story-details">
                        </div>

                        <div class="mb-3">
                            <label for="edit_story_image" class="form-label">Story Image</label>
                            <input type="file" class="form-control" id="edit_story_image" name="story_image" accept="image/*">
                            <small class="text-muted">Leave blank to keep current image</small>
                            <div class="mt-2">
                                <img id="edit_current_image" src="" alt="Current Image" style="max-width: 200px; height: auto;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label" for="edit_is_featured">Featured Story</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_story" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburger');
            
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                const storyId = this.getAttribute('data-id');

                // Set all the form values
                document.getElementById('edit_story_id').value = storyId;
                document.getElementById('edit_title').value = this.getAttribute('data-title');
                document.getElementById('edit_category').value = this.getAttribute('data-category');
                document.getElementById('edit_story_date').value = this.getAttribute('data-story-date');
                document.getElementById('edit_excerpt').value = this.getAttribute('data-excerpt');
                document.getElementById('edit_full_story').value = this.getAttribute('data-full-story');
                document.getElementById('edit_is_featured').checked = this.getAttribute('data-is-featured') === '1';
                document.getElementById('edit_status').value = this.getAttribute('data-status');

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