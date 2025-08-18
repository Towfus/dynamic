<?php
require_once('../shared/config.php');

// Determine upload type
$upload_type = $_GET['type'] ?? 'stories';

// Handle file uploads - all files go to /shared/uploads/
function handleFileUpload($file, $destination_folder) {
    $upload_dir = '../shared/uploads/' . $destination_folder . '/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = time() . '_' . basename($file['name']);
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return 'shared/uploads/' . $destination_folder . '/' . $filename;
    }
    
    return false;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($upload_type) {
        case 'stories':
            if (isset($_FILES['photo'])) {
                $file_path = handleFileUpload($_FILES['photo'], 'photos');
                if ($file_path) {
                    $stmt = $conn->prepare("INSERT INTO photos (title, category, date_taken, description, story_link, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $_POST['title'], $_POST['category'], $_POST['date_taken'], $_POST['description'], $_POST['story_link'], $file_path);
                    $stmt->execute();
                }
            }
            break;
            
        case 'partners':
            if (isset($_FILES['logo'])) {
                $file_path = handleFileUpload($_FILES['logo'], 'partners');
                if ($file_path) {
                    $stmt = $conn->prepare("INSERT INTO partnership (name, category, logo_path) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $_POST['name'], $_POST['category'], $file_path);
                    $stmt->execute();
                }
            }
            break;
            
        case 'news':
            if (isset($_FILES['image'])) {
                $file_path = handleFileUpload($_FILES['image'], 'news');
                if ($file_path) {
                    $stmt = $conn->prepare("INSERT INTO news_updates (title, category, excerpt, news_link, image_path, author, is_published) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("ssssss", $_POST['title'], $_POST['category'], $_POST['excerpt'], $_POST['news_link'], $file_path, $_POST['author']);
                    $stmt->execute();
                }
            }
            break;
    }
    
    header("Location: submit.php?type=" . $upload_type . "&success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload <?php echo ucfirst($upload_type); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Upload <?php echo ucfirst($upload_type); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Upload successful!</div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($upload_type === 'stories'): ?>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="Events">Events</option>
                                        <option value="Activities">Activities</option>
                                        <option value="Awards">Awards</option>
                                        <option value="Students">Students</option>
                                        <option value="Teachers">Teachers</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="date_taken" class="form-label">Date Taken</label>
                                    <input type="date" class="form-control" name="date_taken" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="story_link" class="form-label">Story Link (Optional)</label>
                                    <input type="url" class="form-control" name="story_link">
                                </div>
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Photo</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" required>
                                </div>
                                
                            <?php elseif ($upload_type === 'partners'): ?>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Partner Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="Sustained">Sustained</option>
                                        <option value="Individual">Individual</option>
                                        <option value="Strengthened">Strengthened</option>
                                        <option value="Other-Private">Other-Private</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*" required>
                                </div>
                                
                            <?php elseif ($upload_type === 'news'): ?>
                                <div class="mb-3">
                                    <label for="title" class="form-label">News Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="Partnership">Partnership</option>
                                        <option value="Brigada Eskwela">Brigada Eskwela</option>
                                        <option value="Achievement">Achievement</option>
                                        <option value="Event">Event</option>
                                        <option value="Announcement">Announcement</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="excerpt" class="form-label">Excerpt</label>
                                    <textarea class="form-control" name="excerpt" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="news_link" class="form-label">News Link (Optional)</label>
                                    <input type="url" class="form-control" name="news_link">
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control" name="author">
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">News Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" required>
                                </div>
                                
                            <?php elseif ($upload_type === 'stats'): ?>
                                <div class="mb-3">
                                    <label for="stat_number" class="form-label">Statistic Number</label>
                                    <input type="text" class="form-control" name="stat_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stat_title" class="form-label">Statistic Title</label>
                                    <input type="text" class="form-control" name="stat_title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stat_desc" class="form-label">Description</label>
                                    <input type="text" class="form-control" name="stat_desc" required>
                                </div>
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="display_order" value="1" required>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-success">Upload</button>
                            <a href="index.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>