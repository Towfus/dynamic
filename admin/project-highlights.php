<?php
// Authentication check would go here
session_start();
require_once '../config/database.php';
require_once '../helpers/file_upload.php';
$db = new Database();
$conn = $db->getConnection();

include 'admin-header.php';


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_highlight'])) {
        // Handle image upload
        $uploadResult = uploadImage($_FILES['highlight_image']);

        if ($uploadResult['success']) {
            // Read the image file data
            $imageData = file_get_contents($uploadResult['file_path']);
            $imageType = $_FILES['highlight_image']['type'];
            
            // Set default display order if not provided
            $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
            
            // Add new highlight
            $query = "INSERT INTO project_highlights 
                     (title, description, image_data, image_type, category, event_date, display_order, is_featured, is_active) 
                     VALUES 
                     (:title, :description, :image_data, :image_type, :category, :event_date, :display_order, :is_featured, :is_active)";

            $stmt = $conn->prepare($query);
            
            // Set max allowed packet size (adjust as needed)
            $conn->exec("SET GLOBAL max_allowed_packet=16777216;"); // 16MB
            
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':image_data' => $imageData,
                ':image_type' => $imageType,
                ':category' => $_POST['category'],
                ':event_date' => $_POST['event_date'],
                ':display_order' => $displayOrder,
                ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);

            // Delete the temporary uploaded file
            unlink($uploadResult['file_path']);
            
            $_SESSION['message'] = "Highlight added successfully!";
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } elseif (isset($_POST['update_highlight'])) {
        // Initialize variables for image data
        $imageData = null;
        $imageType = null;
        $imageUpdate = '';
        
        // Handle image update if new image is uploaded
        if (!empty($_FILES['highlight_image']['name'])) {
            $uploadResult = uploadImage($_FILES['highlight_image']);
            if ($uploadResult['success']) {
                $imageData = file_get_contents($uploadResult['file_path']);
                $imageType = $_FILES['highlight_image']['type'];
                $imageUpdate = ", image_data = :image_data, image_type = :image_type";
                // Delete temporary file
                unlink($uploadResult['file_path']);
            } else {
                $_SESSION['error'] = $uploadResult['error'];
            }
        }

        // Set default display order if not provided
        $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 1;
        
        // Update highlight
        $query = "UPDATE project_highlights SET 
                 title = :title,
                 description = :description,
                 category = :category,
                 event_date = :event_date,
                 display_order = :display_order,
                 is_featured = :is_featured,
                 is_active = :is_active
                 $imageUpdate
                 WHERE id = :id";

        $stmt = $conn->prepare($query);
        
        // Set max allowed packet size (adjust as needed)
        $conn->exec("SET GLOBAL max_allowed_packet=16777216;"); // 16MB
        
        // Bind parameters
        $params = [
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':category' => $_POST['category'],
            ':event_date' => $_POST['event_date'],
            ':display_order' => $displayOrder,
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['highlight_id']
        ];
        
        // Add image parameters if updating image
        if (!empty($imageData)) {
            $params[':image_data'] = $imageData;
            $params[':image_type'] = $imageType;
        }
        
        $stmt->execute($params);

        $_SESSION['message'] = "Highlight updated successfully!";
    } elseif (isset($_POST['delete_highlight'])) {
        // Delete the highlight
        $query = "DELETE FROM project_highlights WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['highlight_id']]);

        $_SESSION['message'] = "Highlight deleted successfully!";
    }

    header("Location: project-highlights.php");
    exit();
}

// Get all highlights for display
$query = "SELECT id, title, description, category, event_date, display_order, is_featured, is_active 
          FROM project_highlights 
          ORDER BY display_order, event_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to display image from blob data
function getImageSrc($id) {
    global $conn;
    $query = "SELECT image_data, image_type FROM project_highlights WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image && $image['image_data']) {
        return 'data:' . $image['image_type'] . ';base64,' . base64_encode($image['image_data']);
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Project Highlights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .img-thumbnail {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Manage Project Highlights</h1>

        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Add New Highlight Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Add New Highlight</h2>
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
                                <label for="event_date" class="form-label">Event Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3 mt-4 pt-3">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                <label class="form-check-label" for="is_featured">Featured Highlight</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3 mt-4 pt-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="highlight_image" class="form-label">Highlight Image</label>
                        <input type="file" class="form-control" id="highlight_image" name="highlight_image" accept="image/*" required>
                        <small class="text-muted">Recommended size: 800x600 pixels (Max 8MB)</small>
                    </div>

                    <button type="submit" name="add_highlight" class="btn btn-primary">Add Highlight</button>
                </form>
            </div>
        </div>

        <!-- Highlights List -->
        <div class="card">
            <div class="card-header">
                <h2>Current Highlights</h2>
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
                                <th>Order</th>
                                <th>Featured</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($highlights as $highlight): ?>
                                <tr>
                                    <td>
                                        <?php $imgSrc = getImageSrc($highlight['id']); ?>
                                        <?php if ($imgSrc): ?>
                                            <img src="<?= $imgSrc ?>" alt="Highlight Image" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($highlight['title']) ?></td>
                                    <td><?= htmlspecialchars($highlight['category']) ?></td>
                                    <td><?= date('M d, Y', strtotime($highlight['event_date'])) ?></td>
                                    <td><?= $highlight['display_order'] ?></td>
                                    <td><?= $highlight['is_featured'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                    <td><?= $highlight['is_active'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-btn"
                                            data-id="<?= $highlight['id'] ?>"
                                            data-title="<?= htmlspecialchars($highlight['title']) ?>"
                                            data-description="<?= htmlspecialchars($highlight['description']) ?>"
                                            data-category="<?= htmlspecialchars($highlight['category']) ?>"
                                            data-event-date="<?= $highlight['event_date'] ?>"
                                            data-display-order="<?= $highlight['display_order'] ?>"
                                            data-is-featured="<?= $highlight['is_featured'] ?>"
                                            data-is-active="<?= $highlight['is_active'] ?>">
                                            Edit
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="highlight_id" value="<?= $highlight['id'] ?>">
                                            <button type="submit" name="delete_highlight" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this highlight?')">
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
                        <h5 class="modal-title">Edit Highlight</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="highlight_id" id="edit_highlight_id">

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
                                    <label for="edit_event_date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="edit_display_order" name="display_order" required min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label" for="edit_is_featured">Featured Highlight</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3 mt-4 pt-3">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                    <label class="form-check-label" for="edit_is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_highlight_image" class="form-label">Highlight Image</label>
                            <input type="file" class="form-control" id="edit_highlight_image" name="highlight_image" accept="image/*">
                            <small class="text-muted">Leave blank to keep current image (Max 8MB)</small>
                            <div class="mt-2">
                                <img id="edit_current_image" src="" alt="Current Image" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_highlight" class="btn btn-primary">Save changes</button>
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
                const highlightId = this.getAttribute('data-id');

                // Set all the form values
                document.getElementById('edit_highlight_id').value = highlightId;
                document.getElementById('edit_title').value = this.getAttribute('data-title');
                document.getElementById('edit_description').value = this.getAttribute('data-description');
                document.getElementById('edit_category').value = this.getAttribute('data-category');
                document.getElementById('edit_event_date').value = this.getAttribute('data-event-date');
                document.getElementById('edit_display_order').value = this.getAttribute('data-display-order');
                document.getElementById('edit_is_featured').checked = this.getAttribute('data-is-featured') === '1';
                document.getElementById('edit_is_active').checked = this.getAttribute('data-is-active') === '1';

                // Handle image
                const imgElement = this.closest('tr').querySelector('img');
                if (imgElement) {
                    document.getElementById('edit_current_image').src = imgElement.src;
                }

                modal.show();
            });
        });
    </script>
</body>

</html>