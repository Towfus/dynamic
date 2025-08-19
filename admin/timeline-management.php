<?php
// Database configuration
require_once '../config/database.php';
require_once '../helpers/file_upload.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle image upload
        $uploadResult = uploadImage($_FILES['event_image'], 'timeline');
        
        if ($uploadResult['success']) {
            // Add new event
            $query = "INSERT INTO timeline_events 
                      (title, description, image_path, status, event_date, 
                      display_order, position, is_active) 
                      VALUES 
                      (:title, :description, :image_path, :status, :event_date, 
                      :display_order, :position, :is_active)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':image_path' => $uploadResult['file_path'],
                ':status' => $_POST['status'],
                ':event_date' => $_POST['event_date'],
                ':display_order' => $_POST['display_order'],
                ':position' => $_POST['position'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $message = "Timeline event added successfully!";
            $messageType = "success";
        } else {
            $message = $uploadResult['error'];
            $messageType = "danger";
        }
    } elseif (isset($_POST['update_event'])) {
        // Handle image update if new image is uploaded
        $image_path = $_POST['existing_image'];
        
        if (!empty($_FILES['event_image']['name'])) {
            $uploadResult = uploadImage($_FILES['event_image'], 'timeline');
            if ($uploadResult['success']) {
                $image_path = $uploadResult['file_path'];
                // Delete old image if needed
                if (file_exists($_POST['existing_image'])) {
                    unlink($_POST['existing_image']);
                }
            } else {
                $message = $uploadResult['error'];
                $messageType = "danger";
            }
        }
        
        // Update event
        $query = "UPDATE timeline_events SET 
                 title = :title,
                 description = :description,
                 image_path = :image_path,
                 status = :status,
                 event_date = :event_date,
                 display_order = :display_order,
                 position = :position,
                 is_active = :is_active,
                 updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':image_path' => $image_path,
            ':status' => $_POST['status'],
            ':event_date' => $_POST['event_date'],
            ':display_order' => $_POST['display_order'],
            ':position' => $_POST['position'],
            ':is_active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['event_id']
        ]);
        
        $message = "Timeline event updated successfully!";
        $messageType = "success";
    } elseif (isset($_POST['delete_event'])) {
        // First get image path to delete it
        $query = "SELECT image_path FROM timeline_events WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the image file
        if ($event && file_exists($event['image_path'])) {
            unlink($event['image_path']);
        }
        
        // Delete the event
        $query = "DELETE FROM timeline_events WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        
        $message = "Timeline event deleted successfully!";
        $messageType = "success";
    } elseif (isset($_POST['toggle_status'])) {
        $query = "UPDATE timeline_events SET is_active = NOT is_active WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['event_id']]);
        
        $message = "Event status updated successfully!";
        $messageType = "success";
    }
}

// Get all events for display
$query = "SELECT * FROM timeline_events ORDER BY display_order ASC, event_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get event for editing if ID is provided
$editEvent = null;
if (isset($_GET['edit'])) {
    $query = "SELECT * FROM timeline_events WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $_GET['edit']]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project ISSHED Timeline Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #800000, #a52a2a);
            color: white;
            padding: 2rem 0;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .timeline-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .events-table {
            background: white;
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Timeline Management</h1>
                    <p class="mb-0 opacity-75">Manage Project ISSHED Timeline Events</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="proj-isshed.php" class="btn btn-light">
                        <i class="bi bi-eye me-1"></i>View Timeline
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Form Section -->
        <div class="timeline-form">
            <h3 class="mb-4">
                <i class="bi bi-<?php echo $editEvent ? 'pencil' : 'plus-circle'; ?> me-2"></i>
                <?php echo $editEvent ? 'Edit Timeline Event' : 'Add New Timeline Event'; ?>
            </h3>

            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php if ($editEvent): ?>
                <input type="hidden" name="event_id" value="<?php echo $editEvent['id']; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $editEvent['image_path']; ?>">
                <?php endif; ?>

                <div class="row g-3">
                    <!-- Title -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $editEvent ? htmlspecialchars($editEvent['title']) : ''; ?>" 
                                   placeholder="Event Title" required>
                            <label for="title"><i class="bi bi-card-text me-1"></i>Event Title</label>
                            <div class="invalid-feedback">Please provide a valid title.</div>
                        </div>
                    </div>

                    <!-- Event Date -->
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo $editEvent ? $editEvent['event_date'] : ''; ?>" 
                                   placeholder="Event Date" required>
                            <label for="event_date"><i class="bi bi-calendar me-1"></i>Event Date</label>
                            <div class="invalid-feedback">Please provide a valid date.</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control" id="description" name="description" 
                                      style="height: 120px" placeholder="Event Description" required><?php echo $editEvent ? htmlspecialchars($editEvent['description']) : ''; ?></textarea>
                            <label for="description"><i class="bi bi-textarea-resize me-1"></i>Event Description</label>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <div class="form-floating">
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="planned" <?php echo ($editEvent && $editEvent['status'] === 'planned') ? 'selected' : ''; ?>>Planned</option>
                                <option value="in-progress" <?php echo ($editEvent && $editEvent['status'] === 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo ($editEvent && $editEvent['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <label for="status"><i class="bi bi-flag me-1"></i>Status</label>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="col-md-4">
                        <div class="form-floating">
                            <select class="form-select" id="position" name="position" required>
                                <option value="">Select Position</option>
                                <option value="left" <?php echo ($editEvent && $editEvent['position'] === 'left') ? 'selected' : ''; ?>>Left</option>
                                <option value="right" <?php echo ($editEvent && $editEvent['position'] === 'right') ? 'selected' : ''; ?>>Right</option>
                            </select>
                            <label for="position"><i class="bi bi-align-center me-1"></i>Position</label>
                            <div class="invalid-feedback">Please select a position.</div>
                        </div>
                    </div>

                    <!-- Display Order -->
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="number" class="form-control" id="display_order" name="display_order" 
                                   value="<?php echo $editEvent ? $editEvent['display_order'] : '1'; ?>" 
                                   min="1" placeholder="Display Order" required>
                            <label for="display_order"><i class="bi bi-sort-numeric-up me-1"></i>Display Order</label>
                            <div class="invalid-feedback">Please provide a valid display order.</div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="col-md-8">
                        <label for="event_image" class="form-label">
                            <i class="bi bi-image me-1"></i>Event Image
                        </label>
                        <input type="file" class="form-control" id="event_image" name="event_image" 
                               accept="image/jpeg,image/jpg,image/png,image/gif" <?php echo !$editEvent ? 'required' : ''; ?>>
                        <div class="form-text">
                            Accepted formats: JPG, JPEG, PNG, GIF. Max size: 5MB
                        </div>
                    </div>

                    <!-- Current Image Preview -->
                    <?php if ($editEvent && $editEvent['image_path']): ?>
                    <div class="col-md-4">
                        <label class="form-label">Current Image</label>
                        <div>
                            <img src="<?php echo htmlspecialchars($editEvent['image_path']); ?>" 
                                 alt="Current Image" class="image-preview">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Active Status -->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo ($editEvent && $editEvent['is_active']) || !$editEvent ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-eye me-1"></i>Active (Visible on timeline)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" name="<?php echo $editEvent ? 'update_event' : 'add_event'; ?>" class="btn btn-success me-2">
                        <i class="bi bi-<?php echo $editEvent ? 'check-lg' : 'plus-lg'; ?> me-1"></i>
                        <?php echo $editEvent ? 'Update Event' : 'Add Event'; ?>
                    </button>
                    
                    <?php if ($editEvent): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i>Cancel Edit
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Events List -->
        <div class="events-table">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Timeline Events 
                        <span class="badge bg-primary"><?php echo count($events); ?></span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($events)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Timeline Events Found</h5>
                        <p class="text-muted">Add your first timeline event using the form above.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Position</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $event['display_order']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($event['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($event['image_path']); ?>" 
                                                 alt="Event Image" class="image-preview me-2">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                <small class="d-block text-muted">
                                                    <?php echo substr(htmlspecialchars($event['description']), 0, 60) . '...'; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y', strtotime($event['event_date'])); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'completed' => 'bg-success',
                                            'in-progress' => 'bg-warning text-dark',
                                            'planned' => 'bg-primary'
                                        ];
                                        $statusClass = $statusClasses[$event['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> status-badge">
                                            <?php echo ucwords(str_replace('-', ' ', $event['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info status-badge">
                                            <?php echo ucfirst($event['position']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $event['is_active'] ? 'btn-success' : 'btn-outline-secondary'; ?> btn-action">
                                                <i class="bi bi-<?php echo $event['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-action">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <button type="submit" name="delete_event" class="btn btn-outline-danger btn-action" 
                                                        onclick="return confirm('Are you sure you want to delete this event?')">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            });
        }, 5000);
    </script>
</body>
</html>