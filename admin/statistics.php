<?php
// Authentication check would go here
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_stat'])) {
        // Add new statistic
        $query = "INSERT INTO statistics (stat_number, stat_title, stat_description, display_order, is_active) 
                  VALUES (:number, :title, :description, :order, :active)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':number' => $_POST['stat_number'],
            ':title' => $_POST['stat_title'],
            ':description' => $_POST['stat_description'],
            ':order' => $_POST['display_order'],
            ':active' => isset($_POST['is_active']) ? 1 : 0
        ]);
    } elseif (isset($_POST['update_stat'])) {
        // Update existing statistic
        $query = "UPDATE statistics SET 
                  stat_number = :number,
                  stat_title = :title,
                  stat_description = :description,
                  display_order = :order,
                  is_active = :active
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':number' => $_POST['stat_number'],
            ':title' => $_POST['stat_title'],
            ':description' => $_POST['stat_description'],
            ':order' => $_POST['display_order'],
            ':active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $_POST['stat_id']
        ]);
    } elseif (isset($_POST['delete_stat'])) {
        // Delete statistic
        $query = "DELETE FROM statistics WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $_POST['stat_id']]);
    }
}

// Get all statistics for display
$query = "SELECT * FROM statistics ORDER BY display_order ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Manage Statistics</h1>
        
        <!-- Add New Statistic Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Add New Statistic</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stat_number" class="form-label">Statistic Number</label>
                                <input type="text" class="form-control" id="stat_number" name="stat_number" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stat_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="stat_title" name="stat_title" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stat_description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="stat_description" name="stat_description" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" required>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-check mt-4 pt-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_stat" class="btn btn-primary">Add Statistic</button>
                </form>
            </div>
        </div>
        
        <!-- Statistics List -->
        <div class="card">
            <div class="card-header">
                <h2>Current Statistics</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Order</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statistics as $stat): ?>
                            <tr>
                                <td><?= htmlspecialchars($stat['stat_number']) ?></td>
                                <td><?= htmlspecialchars($stat['stat_title']) ?></td>
                                <td><?= htmlspecialchars($stat['stat_description']) ?></td>
                                <td><?= htmlspecialchars($stat['display_order']) ?></td>
                                <td><?= $stat['is_active'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="<?= $stat['id'] ?>"
                                            data-number="<?= htmlspecialchars($stat['stat_number']) ?>"
                                            data-title="<?= htmlspecialchars($stat['stat_title']) ?>"
                                            data-desc="<?= htmlspecialchars($stat['stat_description']) ?>"
                                            data-order="<?= $stat['display_order'] ?>"
                                            data-active="<?= $stat['is_active'] ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="stat_id" value="<?= $stat['id'] ?>">
                                        <button type="submit" name="delete_stat" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure?')">
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Statistic</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="stat_id" id="edit_stat_id">
                        <div class="mb-3">
                            <label for="edit_stat_number" class="form-label">Statistic Number</label>
                            <input type="text" class="form-control" id="edit_stat_number" name="stat_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_stat_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_stat_title" name="stat_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_stat_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="edit_stat_description" name="stat_description" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_display_order" class="form-label">Order</label>
                            <input type="number" class="form-control" id="edit_display_order" name="display_order" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_stat" class="btn btn-primary">Save changes</button>
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
                const statId = this.getAttribute('data-id');
                const statNumber = this.getAttribute('data-number');
                const statTitle = this.getAttribute('data-title');
                const statDesc = this.getAttribute('data-desc');
                const statOrder = this.getAttribute('data-order');
                const statActive = this.getAttribute('data-active');
                
                document.getElementById('edit_stat_id').value = statId;
                document.getElementById('edit_stat_number').value = statNumber;
                document.getElementById('edit_stat_title').value = statTitle;
                document.getElementById('edit_stat_description').value = statDesc;
                document.getElementById('edit_display_order').value = statOrder;
                document.getElementById('edit_is_active').checked = statActive === '1';
                
                modal.show();
            });
        });
    </script>
</body>
</html>