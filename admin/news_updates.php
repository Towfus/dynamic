<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../helpers/file_upload.php';

$db = new Database();
$conn = $db->getConnection();
// Make PDO throw exceptions for easier debugging and transactional safety
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/**
 * Helper that attempts to map a stored image URL/path to a filesystem path for unlink()
 * It will return a path that exists if it can detect one; otherwise returns the original input.
 */
function serverPathFromStored($storedPath) {
    if (empty($storedPath)) return '';
    // If absolute path (unix or windows)
    if (strpos($storedPath, '/') === 0 || preg_match('/^[A-Za-z]:\\\\/', $storedPath)) {
        return $storedPath;
    }
    // Try relative to this file's directory
    $try1 = __DIR__ . '/' . ltrim($storedPath, '/');
    if (file_exists($try1)) return $try1;
    // Try one level up
    $try2 = __DIR__ . '/../' . ltrim($storedPath, '/');
    if (file_exists($try2)) return $try2;
    // fallback: return original path (may be correct already)
    return $storedPath;
}

// POST handling using a reliable hidden action field
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_news') {
            // Add new news item
            $uploadResult = uploadImage($_FILES['image'] ?? null, 'news');

            if ($uploadResult['success']) {
                $query = "INSERT INTO news_updates 
                         (title, category, news_date, excerpt, image_url, full_content, is_featured, status, sort_order) 
                         VALUES 
                         (:title, :category, :news_date, :excerpt, :image_url, :full_content, :is_featured, :status, :sort_order)";

                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':title' => trim($_POST['title']),
                    ':category' => trim($_POST['category']),
                    ':news_date' => trim($_POST['news_date']),
                    ':excerpt' => trim($_POST['excerpt']),
                    ':image_url' => $uploadResult['file_path'],
                    ':full_content' => trim($_POST['full_content']),
                    ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    ':status' => trim($_POST['status']),
                    ':sort_order' => (int)($_POST['sort_order'] ?? 0)
                ]);

                $_SESSION['message'] = "News item added successfully!";
            } else {
                $_SESSION['error'] = $uploadResult['error'] ?? 'Image upload failed.';
            }
        } elseif ($action === 'update_news') {
            // Update existing news item
            $newsId = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
            if ($newsId <= 0) {
                throw new Exception('Invalid news ID.');
            }

            // Start transaction
            $conn->beginTransaction();

            // Get current record to check if it exists and get current image
            $checkQuery = "SELECT image_url FROM news_updates WHERE id = :id";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([':id' => $newsId]);
            $currentRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$currentRecord) {
                $conn->rollBack();
                throw new Exception('News item not found.');
            }

            $existingImage = $currentRecord['image_url'];
            $image_url = $existingImage; // Default: keep existing image

            // Handle new image upload if provided
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
                $uploadResult = uploadImage($_FILES['image'], 'news');
                if ($uploadResult['success']) {
                    $image_url = $uploadResult['file_path'];
                } else {
                    $conn->rollBack();
                    throw new Exception('Image upload failed: ' . ($uploadResult['error'] ?? 'Unknown error'));
                }
            }

            // Update the record
            $updateQuery = "UPDATE news_updates SET 
                           title = :title,
                           category = :category,
                           news_date = :news_date,
                           excerpt = :excerpt,
                           image_url = :image_url,
                           full_content = :full_content,
                           is_featured = :is_featured,
                           status = :status,
                           sort_order = :sort_order,
                           updated_at = NOW()
                           WHERE id = :id";

            $updateStmt = $conn->prepare($updateQuery);
            $result = $updateStmt->execute([
                ':title' => trim($_POST['title']),
                ':category' => trim($_POST['category']),
                ':news_date' => trim($_POST['news_date']),
                ':excerpt' => trim($_POST['excerpt']),
                ':image_url' => $image_url,
                ':full_content' => trim($_POST['full_content']),
                ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                ':status' => trim($_POST['status']),
                ':sort_order' => (int)($_POST['sort_order'] ?? 0),
                ':id' => $newsId
            ]);

            // Check if update was successful
            if ($updateStmt->rowCount() === 0) {
                $conn->rollBack();
                throw new Exception('No changes were made or record not found.');
            }

            // If we uploaded a new image and update was successful, delete the old file
            if (isset($uploadResult) && $uploadResult['success'] && !empty($existingImage) && $existingImage !== $image_url) {
                $oldFile = serverPathFromStored($existingImage);
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $conn->commit();
            $_SESSION['message'] = "News item updated successfully!";

        } elseif ($action === 'delete_news') {
            $newsId = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
            if ($newsId <= 0) throw new Exception('Invalid news ID.');

            // Get image URL so we can delete file
            $query = "SELECT image_url FROM news_updates WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $newsId]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($news && !empty($news['image_url'])) {
                $imagePath = serverPathFromStored($news['image_url']);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            $query = "DELETE FROM news_updates WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $newsId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Record not found or already deleted.');
            }

            $_SESSION['message'] = "News item deleted successfully!";
        }
    } catch (Exception $e) {
        // If we started a transaction but an exception occurred, ensure rollback
        if ($conn->inTransaction()) $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: news_updates.php");
    exit();
}

// Get all news items for display - with explicit ordering
$query = "SELECT * FROM news_updates ORDER BY is_featured DESC, sort_order ASC, news_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News Updates - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* your styles kept as-is (copied from your original file) */
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
        
        /* Enhanced Modal Styles */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-backdrop.show {
            opacity: 1;
        }
        .modal-dialog {
            transform: translateY(-50px);
            transition: transform 0.3s ease;
        }
        .modal-dialog.show {
            transform: translateY(0);
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

                   <a href="impact-stories.php">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>

                <a href="news_updates.php" class="active" >
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
                <h1 class="text-xl font-semibold text-gray-800">News Updates Management</h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="notification-badge cursor-pointer">
                    <i class="fas fa-bell text-gray-600 text-lg hover:text-blue-600 transition-colors"></i>
                </div>
                <div class="user-profile">
                    <div class="user-avatar">A</div>
                    <div>
                        <div class="font-semibold text-gray-800">Admin User</div>
                        <div class="text-xs text-gray-500">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4 p-4">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Manage News & Partnership Updates</h2>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success p-4 mb-4 rounded-lg"><?= htmlspecialchars($_SESSION['message']) ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger p-4 mb-4 rounded-lg"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Add New News Form -->
                <div class="card mb-6 border border-gray-200 rounded-lg">
                    <div class="card-header bg-gray-50 p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Add New News Item</h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Hidden action to reliably detect the add operation server-side -->
                            <input type="hidden" name="action" value="add_news">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" class="w-full p-2 border border-gray-300 rounded-md" id="title" name="title" required>
                                </div>
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select class="w-full p-2 border border-gray-300 rounded-md" id="category" name="category" required>
                                        <option value="partnership">Partnership</option>
                                        <option value="brigada">Brigada Eskwela</option>
                                        <option value="achievement">Achievement</option>
                                        <option value="event">Event</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="news_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" class="w-full p-2 border border-gray-300 rounded-md" id="news_date" name="news_date" required>
                                </div>
                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                    <input type="number" class="w-full p-2 border border-gray-300 rounded-md" id="sort_order" name="sort_order" value="0">
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select class="w-full p-2 border border-gray-300 rounded-md" id="status" name="status">
                                        <option value="published">Published</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                                <textarea class="w-full p-2 border border-gray-300 rounded-md" id="excerpt" name="excerpt" rows="3" required></textarea>
                                <p class="text-xs text-gray-500 mt-1">Short summary displayed in the carousel</p>
                            </div>

                            <div class="mb-4">
                                <label for="full_content" class="block text-sm font-medium text-gray-700 mb-1">Full Content</label>
                                <textarea class="w-full p-2 border border-gray-300 rounded-md" id="full_content" name="full_content" rows="5" required></textarea>
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input class="mr-2" type="checkbox" id="is_featured" name="is_featured">
                                    <label for="is_featured" class="text-sm font-medium text-gray-700">Featured Item</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                                <input type="file" class="w-full p-2 border border-gray-300 rounded-md" id="image" name="image" accept="image/*" required>
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 1200x800 pixels</p>
                            </div>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                Add News Item
                            </button>
                        </form>
                    </div>
                </div>

                <!-- News Items List -->
                <div class="card border border-gray-200 rounded-lg">
                    <div class="card-header bg-gray-50 p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Current News Items (<?= count($newsItems) ?> total)</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($newsItems as $item): ?>
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            #<?= (int)$item['id'] ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                 alt="<?= htmlspecialchars($item['title']) ?>" 
                                                 class="news-preview rounded-md">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <?php 
                                            $badgeClass = 'badge-' . $item['category'];
                                            $categoryName = ucfirst(str_replace('_', ' ', $item['category']));
                                            ?>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-white rounded-full <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($categoryName) ?></span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($item['news_date'])) ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <?php if ($item['status'] == 'published'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Published</span>
                                            <?php elseif ($item['status'] == 'draft'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-gray-800 bg-gray-100 rounded-full">Draft</span>
                                            <?php else: ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full">Archived</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($item['is_featured']): ?>
                                                <i class="fas fa-star text-yellow-400"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md mr-2 edit-btn" 
                                                    type="button"
                                                    data-id="<?= (int)$item['id'] ?>"
                                                    data-title="<?= htmlspecialchars($item['title']) ?>"
                                                    data-category="<?= htmlspecialchars($item['category']) ?>"
                                                    data-date="<?= htmlspecialchars($item['news_date']) ?>"
                                                    data-excerpt="<?= htmlspecialchars($item['excerpt']) ?>"
                                                    data-content="<?= htmlspecialchars($item['full_content']) ?>"
                                                    data-sort="<?= (int)$item['sort_order'] ?>"
                                                    data-status="<?= htmlspecialchars($item['status']) ?>"
                                                    data-featured="<?= $item['is_featured'] ? '1' : '0' ?>"
                                                    data-image="<?= htmlspecialchars($item['image_url']) ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this news item?')">
                                                <input type="hidden" name="action" value="delete_news">
                                                <input type="hidden" name="news_id" value="<?= (int)$item['id'] ?>">
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded-md">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($newsItems)): ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                            No news items found. Add your first news item above.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" id="editModalBackdrop">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white" id="editModal">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800">Edit News Item (ID: <span id="modal-news-id">-</span>)</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mt-4" id="editForm" onsubmit="return validateEditForm()">
                    <input type="hidden" name="action" value="update_news">
                    <input type="hidden" name="news_id" id="edit_news_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_title" name="title" required>
                        </div>
                        <div>
                            <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_category" name="category" required>
                                <option value="partnership">Partnership</option>
                                <option value="brigada">Brigada Eskwela</option>
                                <option value="achievement">Achievement</option>
                                <option value="event">Event</option>
                                <option value="announcement">Announcement</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="edit_news_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_news_date" name="news_date" required>
                        </div>
                        <div>
                            <label for="edit_sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_sort_order" name="sort_order">
                        </div>
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_status" name="status">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_excerpt" name="excerpt" rows="3" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="edit_full_content" class="block text-sm font-medium text-gray-700 mb-1">Full Content</label>
                        <textarea class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_full_content" name="full_content" rows="5" required></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center">
                            <input class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" id="edit_is_featured" name="is_featured">
                            <label for="edit_is_featured" class="text-sm font-medium text-gray-700">Featured Item</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                        <input type="file" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="edit_image" name="image" accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current image</p>
                        <div class="mt-2" id="current-image-container">
                            <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                            <img id="edit_current_image" src="" alt="Current Image" class="news-preview rounded-md border">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t mt-4">
                        <button type="button" class="close-modal bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md mr-2 transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                            <i class="fas fa-save mr-2"></i>Update News Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Debug flag
        const DEBUG = true;

        function debugLog(message, data = null) {
            if (DEBUG) {
                console.log('[NEWS_UPDATE_DEBUG]', message, data);
            }
        }

        // Toggle sidebar
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Modal functionality
        const modalBackdrop = document.getElementById('editModalBackdrop');
        const closeModalButtons = document.querySelectorAll('.close-modal');

        // Function to show modal
        function showModal() {
            debugLog('Showing modal');
            modalBackdrop.classList.remove('hidden');
        }

        // Function to hide modal
        function hideModal() {
            debugLog('Hiding modal');
            modalBackdrop.classList.add('hidden');
        }

        // Validate edit form before submission
        function validateEditForm() {
            const newsId = document.getElementById('edit_news_id').value;
            const title = document.getElementById('edit_title').value.trim();
            const category = document.getElementById('edit_category').value;
            const newsDate = document.getElementById('edit_news_date').value;
            const excerpt = document.getElementById('edit_excerpt').value.trim();
            const fullContent = document.getElementById('edit_full_content').value.trim();
            const status = document.getElementById('edit_status').value;

            debugLog('Form validation started', {
                newsId: newsId,
                title: title,
                category: category,
                newsDate: newsDate,
                excerpt: excerpt ? 'has content' : 'empty',
                fullContent: fullContent ? 'has content' : 'empty',
                status: status
            });

            if (!newsId) {
                alert('Error: News ID is missing. Please try again.');
                debugLog('Validation failed: Missing news ID');
                return false;
            }

            if (!title) {
                alert('Please enter a title.');
                document.getElementById('edit_title').focus();
                debugLog('Validation failed: Missing title');
                return false;
            }

            if (!category) {
                alert('Please select a category.');
                document.getElementById('edit_category').focus();
                debugLog('Validation failed: Missing category');
                return false;
            }

            if (!newsDate) {
                alert('Please select a date.');
                document.getElementById('edit_news_date').focus();
                debugLog('Validation failed: Missing date');
                return false;
            }

            if (!excerpt) {
                alert('Please enter an excerpt.');
                document.getElementById('edit_excerpt').focus();
                debugLog('Validation failed: Missing excerpt');
                return false;
            }

            if (!fullContent) {
                alert('Please enter full content.');
                document.getElementById('edit_full_content').focus();
                debugLog('Validation failed: Missing full content');
                return false;
            }

            if (!status) {
                alert('Please select a status.');
                document.getElementById('edit_status').focus();
                debugLog('Validation failed: Missing status');
                return false;
            }

            debugLog('Form validation passed - submitting form');
            return true;
        }

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                debugLog('Edit button clicked');
                
                // Get data from button attributes
                const data = {
                    id: this.getAttribute('data-id'),
                    title: this.getAttribute('data-title'),
                    category: this.getAttribute('data-category'),
                    date: this.getAttribute('data-date'),
                    excerpt: this.getAttribute('data-excerpt'),
                    content: this.getAttribute('data-content'),
                    sort: this.getAttribute('data-sort'),
                    status: this.getAttribute('data-status'),
                    featured: this.getAttribute('data-featured'),
                    image: this.getAttribute('data-image')
                };

                debugLog('Data retrieved from button:', data);

                // Populate form fields
                document.getElementById('edit_news_id').value = data.id || '';
                document.getElementById('modal-news-id').textContent = data.id || 'Unknown';
                document.getElementById('edit_title').value = data.title || '';
                document.getElementById('edit_category').value = data.category || '';
                document.getElementById('edit_news_date').value = data.date || '';
                document.getElementById('edit_excerpt').value = data.excerpt || '';
                document.getElementById('edit_full_content').value = data.content || '';
                document.getElementById('edit_sort_order').value = data.sort || '0';
                document.getElementById('edit_status').value = data.status || '';
                document.getElementById('edit_is_featured').checked = (data.featured === '1');
                
                // Set current image
                const currentImg = document.getElementById('edit_current_image');
                if (data.image) {
                    currentImg.src = data.image;
                    currentImg.style.display = 'block';
                } else {
                    currentImg.style.display = 'none';
                }

                debugLog('Form populated successfully');

                // Show modal
                showModal();
            });
        });

        // Close modal event listeners
        closeModalButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                hideModal();
            });
        });

        // Close modal if clicked on backdrop (but not the modal content)
        modalBackdrop.addEventListener('click', function(event) {
            if (event.target === modalBackdrop) {
                hideModal();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modalBackdrop.classList.contains('hidden')) {
                hideModal();
            }
        });

        // Form submission debugging
        document.getElementById('editForm').addEventListener('submit', function(e) {
            debugLog('Form submit event triggered');
            
            // Get form data for debugging
            const formData = new FormData(this);
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            
            debugLog('Form data being submitted:', formDataObj);
        });

        // Auto-set today's date for new news items
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('news_date').value = today;
            debugLog('Page loaded, today date set to:', today);
        });
    </script>
</body>
</html>