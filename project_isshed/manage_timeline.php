<?php
require_once 'config.php';

function getTimelineEvents() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT 
            id,
            title,
            description,
            event_date as date,
            image_path as image,
            position,
            status,
            display_order,
            is_active,
            'bi-calendar-event' as icon
        FROM timeline_events 
        WHERE is_active = 1 
        ORDER BY display_order ASC, event_date ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Alternative function that maps the data properly
function getTimelineEventsFormatted() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT * FROM timeline_events 
        WHERE is_active = 1 
        ORDER BY display_order ASC, event_date ASC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data to match what the template expects
    $formatted_events = [];
    foreach ($events as $event) {
        $formatted_events[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'date' => $event['event_date'], // Map event_date to date
            'image' => $event['image_path'], // Map image_path to image
            'icon' => 'bi-calendar-event', // Default icon since it's not in database
            'position' => $event['position'],
            'status' => $event['status'],
            'display_order' => $event['display_order'],
            'is_active' => $event['is_active']
        ];
    }
    
    return $formatted_events;
}




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
                        $imagePath = handleFileUpload($_FILES['image'], 'uploads/timeline/');
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO timeline_events (title, description, event_date, status, image_path, position, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['event_date'],
                        $_POST['status'],
                        $imagePath,
                        $_POST['position'],
                        $_POST['display_order'] ?? 0
                    ]);
                    $message = "Timeline event added successfully!";
                    break;
                    
                case 'edit':
                    $imagePath = $_POST['existing_image'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imagePath = handleFileUpload($_FILES['image'], 'uploads/timeline/');
                        // Delete old image if exists
                        if (!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])) {
                            unlink($_POST['existing_image']);
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE timeline_events SET title=?, description=?, event_date=?, status=?, image_path=?, position=?, display_order=? WHERE id=?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['event_date'],
                        $_POST['status'],
                        $imagePath,
                        $_POST['position'],
                        $_POST['display_order'],
                        $_POST['id']
                    ]);
                    $message = "Timeline event updated successfully!";
                    break;
                    
                case 'delete':
                    // Get image path before deleting
                    $stmt = $pdo->prepare("SELECT image_path FROM timeline_events WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $event = $stmt->fetch();
                    
                    // Delete the event
                    $stmt = $pdo->prepare("DELETE FROM timeline_events WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    // Delete associated image
                    if ($event && !empty($event['image_path']) && file_exists($event['image_path'])) {
                        unlink($event['image_path']);
                    }
                    
                    $message = "Timeline event deleted successfully!";
                    break;
                    
                case 'toggle_status':
                    $stmt = $pdo->prepare("UPDATE timeline_events SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Timeline event status updated!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all timeline events
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM timeline_events ORDER BY display_order ASC, event_date ASC");
$timeline_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single event for editing
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM timeline_events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Manager - Project ISSHED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .timeline-preview img {
            max-width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .admin-header {
            background: linear-gradient(135deg, #1e7e34, #28a745);
            color: white;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-clock-history"></i> Timeline Manager</h1>
            <p class="mb-0">Manage Project ISSHED Timeline Events</p>
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
                        <h5><i class="bi bi-plus-circle"></i> <?php echo $edit_event ? 'Edit' : 'Add'; ?> Timeline Event</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit' : 'add'; ?>">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_event['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo $edit_event['image_path']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Date *</label>
                                <input type="date" name="event_date" class="form-control" required 
                                       value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="completed" <?php echo ($edit_event && $edit_event['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="in-progress" <?php echo ($edit_event && $edit_event['status'] === 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="planned" <?php echo ($edit_event && $edit_event['status'] === 'planned') ? 'selected' : ''; ?>>Planned</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-control">
                                    <option value="left" <?php echo ($edit_event && $edit_event['position'] === 'left') ? 'selected' : ''; ?>>Left</option>
                                    <option value="right" <?php echo ($edit_event && $edit_event['position'] === 'right') ? 'selected' : ''; ?>>Right</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?php echo $edit_event ? $edit_event['display_order'] : '0'; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if ($edit_event && $edit_event['image_path']): ?>
                                    <small class="text-muted">Current: <?php echo basename($edit_event['image_path']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> <?php echo $edit_event ? 'Update' : 'Add'; ?> Event
                                </button>
                                <?php if ($edit_event): ?>
                                    <a href="manage_timeline.php" class="btn btn-secondary">
                                        <i class="bi bi-x-lg"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Timeline Events List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-list"></i> Timeline Events</h5>
                        <span class="badge bg-primary"><?php echo count($timeline_events); ?> events</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($timeline_events)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                <p class="text-muted">No timeline events found. Add your first event!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Position</th>
                                            <th>Image</th>
                                            <th>Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($timeline_events as $event): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo $event['display_order']; ?></span></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                    <br><small class="text-muted"><?php echo substr(htmlspecialchars($event['description']), 0, 50) . '...'; ?></small>
                                                </td>
                                                <td><?php echo formatDate($event['event_date']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($event['status']) {
                                                        'completed' => 'bg-success',
                                                        'in-progress' => 'bg-warning',
                                                        'planned' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?> status-badge">
                                                        <?php echo ucfirst(str_replace('-', ' ', $event['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-outline-dark">
                                                        <?php echo ucfirst($event['position']); ?>
                                                    </span>
                                                </td>
                                                <td class="timeline-preview">
                                                    <?php if ($event['image_path'] && file_exists($event['image_path'])): ?>
                                                        <img src="<?php echo $event['image_path']; ?>" alt="Preview">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                                        <button type="submit" class="btn btn-sm <?php echo $event['is_active'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                                            <i class="bi bi-<?php echo $event['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="manage_highlights.php" class="btn btn-outline-primary">
                        <i class="bi bi-images"></i> Manage Highlights
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