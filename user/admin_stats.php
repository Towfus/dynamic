<?php
// Connect to database
$db = new PDO('mysql:host=localhost;dbname=organization_portal', 'root', '');

// Initialize messages
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add'])) {
            $stmt = $db->prepare("INSERT INTO stats (stat_number, stat_title, stat_desc, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['stat_number'],
                $_POST['stat_title'],
                $_POST['stat_desc'],
                $_POST['display_order']
            ]);
            $message = "Statistic added successfully!";
        } elseif (isset($_POST['update'])) {
            $stmt = $db->prepare("UPDATE stats SET stat_number = ?, stat_title = ?, stat_desc = ?, display_order = ? WHERE id = ?");
            $stmt->execute([
                $_POST['stat_number'],
                $_POST['stat_title'],
                $_POST['stat_desc'],
                $_POST['display_order'],
                $_POST['id']
            ]);
            $message = "Statistic updated successfully!";
        } elseif (isset($_POST['delete'])) {
            $stmt = $db->prepare("DELETE FROM stats WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = "Statistic deleted successfully!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all stats
$stats = $db->query("SELECT * FROM stats ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_stats = count($stats);

include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #006400;
            --secondary-color: #28a745;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .admin-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .admin-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-overview {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            text-align: center;
        }

        .stats-overview .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stats-overview .stat-label {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .stats-table-card .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border: none;
        }

        .stats-table-card .card-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-table {
            margin: 0;
        }

        .stats-table th {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-weight: 600;
            padding: 1rem;
            border: none;
            font-size: 0.9rem;
        }

        .stats-table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .stats-table .form-control {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        .stats-table .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 100, 0, 0.25);
        }

        .stat-number-cell {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .stat-title-cell {
            font-weight: 500;
            color: var(--dark-color);
        }

        .stat-desc-cell {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .order-badge {
            background-color: var(--info-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-update {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-update:hover {
            background-color: #004d00;
            border-color: #004d00;
            color: white;
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
            border-color: #c82333;
            color: white;
            transform: translateY(-1px);
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--danger-color), #e74c3c);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .btn-close {
            filter: invert(1);
        }

        .number-input {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
        }

        .number-input:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 100, 0, 0.25);
        }

        @media (max-width: 768px) {
            .admin-title {
                font-size: 1.8rem;
            }
            
            .stats-table {
                font-size: 0.85rem;
            }
            
            .stats-table th,
            .stats-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .stats-table-wrapper {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title text-center">
                <i class="fas fa-chart-bar me-3"></i>Statistics Management
            </h1>
            <p class="admin-subtitle text-center">Manage your organization's key statistics and metrics</p>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Overview -->
        <div class="stats-overview">
            <div class="stat-number"><?php echo $total_stats; ?></div>
            <div class="stat-label">Total Statistics Configured</div>
        </div>

        <!-- Add New Statistic Form -->
        <div class="form-card">
            <h5><i class="fas fa-plus-circle"></i> Add New Statistic</h5>
            <form method="post">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="stat_number" class="form-label">Number/Value</label>
                            <input type="text" class="form-control number-input" id="stat_number" name="stat_number" required placeholder="e.g., 1000+">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="stat_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="stat_title" name="stat_title" required placeholder="e.g., Students Reached">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="stat_desc" class="form-label">Description</label>
                            <input type="text" class="form-control" id="stat_desc" name="stat_desc" required placeholder="Brief description">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" required min="1" value="<?php echo $total_stats + 1; ?>">
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" name="add" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i> Add Statistic
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Statistics Table -->
        <div class="stats-table-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Current Statistics</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($stats) > 0): ?>
                    <div class="stats-table-wrapper">
                        <table class="table stats-table mb-0">
                            <thead>
                                <tr>
                                    <th width="15%">Number</th>
                                    <th width="25%">Title</th>
                                    <th width="30%">Description</th>
                                    <th width="10%">Order</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $stat): ?>
                                <tr>
                                    <form method="post" class="stat-form">
                                        <input type="hidden" name="id" value="<?php echo $stat['id']; ?>">
                                        <td>
                                            <input type="text" name="stat_number" class="form-control stat-number-cell" value="<?php echo htmlspecialchars($stat['stat_number']); ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="stat_title" class="form-control stat-title-cell" value="<?php echo htmlspecialchars($stat['stat_title']); ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="stat_desc" class="form-control stat-desc-cell" value="<?php echo htmlspecialchars($stat['stat_desc']); ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="display_order" class="form-control text-center" value="<?php echo $stat['display_order']; ?>" min="1">
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="submit" name="update" class="btn btn-update btn-action" title="Update">
                                                    <i class="fas fa-save"></i> Update
                                                </button>
                                                <button type="button" class="btn btn-delete btn-action" onclick="openDeleteModal(<?php echo $stat['id']; ?>, '<?php echo addslashes($stat['stat_title']); ?>')" title="Delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <h4>No Statistics Yet</h4>
                        <p>Add your first statistic to showcase your organization's impact!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="delete_stat_id" name="id">
                        
                        <div class="text-center mb-3">
                            <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        
                        <p class="text-center mb-3">Are you sure you want to delete the statistic:</p>
                        <div class="alert alert-light text-center">
                            <strong id="deleteStatTitle" class="text-danger"></strong>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This action cannot be undone. The statistic will be permanently removed.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="submit" name="delete" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Delete Statistic
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open delete modal with statistic details
        function openDeleteModal(statId, statTitle) {
            document.getElementById('delete_stat_id').value = statId;
            document.getElementById('deleteStatTitle').textContent = statTitle;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-save indication for forms
        document.querySelectorAll('.stat-form').forEach(form => {
            const inputs = form.querySelectorAll('input[type="text"], input[type="number"]');
            inputs.forEach(input => {
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    this.style.borderColor = '#ffc107';
                    
                    timeout = setTimeout(() => {
                        this.style.borderColor = '#dee2e6';
                    }, 2000);
                });
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const numberInput = document.getElementById('stat_number');
            const titleInput = document.getElementById('stat_title');
            const descInput = document.getElementById('stat_desc');
            
            if (!numberInput.value.trim() || !titleInput.value.trim() || !descInput.value.trim()) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
        });

        // Highlight active row on focus
        document.querySelectorAll('.stats-table input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('tr').style.backgroundColor = '#f8f9fa';
            });
            
            input.addEventListener('blur', function() {
                this.closest('tr').style.backgroundColor = '';
            });
        });
    </script>
</body>
</html>