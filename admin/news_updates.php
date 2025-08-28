<?php
session_start();
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
    <title>Manage News Updates - DepEd General Trias City</title>
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
                <a href="news_updates.php" class="active">
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
                <h1 class="text-xl font-semibold text-gray-800">News Updates Management</h1>
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
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Manage News & Partnership Updates</h2>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success p-4 mb-4 rounded-lg"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger p-4 mb-4 rounded-lg"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Add New News Form -->
                <div class="card mb-6 border border-gray-200 rounded-lg">
                    <div class="card-header bg-gray-50 p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Add New News Item</h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">
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
                            
                            <button type="submit" name="add_news" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                Add News Item
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- News Items List -->
                <div class="card border border-gray-200 rounded-lg">
                    <div class="card-header bg-gray-50 p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Current News Items</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
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
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 text-white rounded-full <?= $badgeClass ?>"><?= $categoryName ?></span>
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
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                <button type="submit" name="delete_news" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded-md" 
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
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="editModal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-800">Edit News Item</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="news_id" id="edit_news_id">
                    <input type="hidden" name="existing_image" id="edit_existing_image">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" class="w-full p-2 border border-gray-300 rounded-md" id="edit_title" name="title" required>
                        </div>
                        <div>
                            <label for="edit_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select class="w-full p-2 border border-gray-300 rounded-md" id="edit_category" name="category" required>
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
                            <input type="date" class="w-full p-2 border border-gray-300 rounded-md" id="edit_news_date" name="news_date" required>
                        </div>
                        <div>
                            <label for="edit_sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" class="w-full p-2 border border-gray-300 rounded-md" id="edit_sort_order" name="sort_order">
                        </div>
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select class="w-full p-2 border border-gray-300 rounded-md" id="edit_status" name="status">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea class="w-full p-2 border border-gray-300 rounded-md" id="edit_excerpt" name="excerpt" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_full_content" class="block text-sm font-medium text-gray-700 mb-1">Full Content</label>
                        <textarea class="w-full p-2 border border-gray-300 rounded-md" id="edit_full_content" name="full_content" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-center">
                            <input class="mr-2" type="checkbox" id="edit_is_featured" name="is_featured">
                            <label for="edit_is_featured" class="text-sm font-medium text-gray-700">Featured Item</label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                        <input type="file" class="w-full p-2 border border-gray-300 rounded-md" id="edit_image" name="image" accept="image/*">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current image</p>
                        <div class="mt-2">
                            <img id="edit_current_image" src="" alt="Current Image" class="news-preview rounded-md">
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t mt-4">
                        <button type="button" class="close-modal bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md mr-2">
                            Close
                        </button>
                        <button type="submit" name="update_news" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle sidebar
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Modal functionality
        const modal = document.getElementById('editModal');
        const closeModalButtons = document.querySelectorAll('.close-modal');
        
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
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
                
                // Show modal
                modal.classList.remove('hidden');
            });
        });
        
        // Close modal
        closeModalButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        });
        
        // Close modal if clicked outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>