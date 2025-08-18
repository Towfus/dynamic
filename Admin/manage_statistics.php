<?php
// admin/manage-statistics.php - Manage Homepage Statistics
require_once '../shared/config.php';

startSecureSession();
requireLogin();

$currentAdmin = getCurrentAdmin();
$success = '';
$error = '';

// Get current statistics
$currentStats = getStatistics();

if ($_POST) {
    $schools_supported = intval($_POST['schools_supported'] ?? 0);
    $total_contributions = sanitizeInput($_POST['total_contributions'] ?? '');
    $ongoing_projects = intval($_POST['ongoing_projects'] ?? 0);
    $active_partners = intval($_POST['active_partners'] ?? 0);
    
    if ($schools_supported <= 0 || $ongoing_projects <= 0 || $active_partners <= 0 || empty($total_contributions)) {
        $error = 'Please fill in all fields with valid values.';
    } else {
        $db = Database::getInstance()->getConnection();
        
        // Store old values for logging
        $oldValues = $currentStats;
        
        try {
            // Check if statistics record exists
            if ($currentStats) {
                $stmt = $db->prepare("UPDATE statistics SET 
                    schools_supported = ?, 
                    total_contributions = ?, 
                    ongoing_projects = ?, 
                    active_partners = ?,
                    updated_at = NOW()
                    WHERE id = ?");
                $stmt->execute([$schools_supported, $total_contributions, $ongoing_projects, $active_partners, $currentStats['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO statistics (schools_supported, total_contributions, ongoing_projects, active_partners) 
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([$schools_supported, $total_contributions, $ongoing_projects, $active_partners]);
            }
            
            $newValues = [
                'schools_supported' => $schools_supported,
                'total_contributions' => $total_contributions,
                'ongoing_projects' => $ongoing_projects,
                'active_partners' => $active_partners
            ];
            
            // Log activity
            logActivity($currentAdmin['id'], 'Updated website statistics', 'statistics', $currentStats['id'] ?? null, $oldValues, $newValues);
            
            $success = 'Statistics updated successfully!';
            
            // Refresh current stats
            $currentStats = getStatistics();
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Statistics - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Website Statistics</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../user/index.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Preview Changes
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-bar-chart me-2"></i>
                                    Homepage Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">
                                    These statistics are displayed prominently on the homepage. They help showcase 
                                    the impact and reach of your partnership programs.
                                </p>

                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="schools_supported" class="form-label">
                                                <i class="bi bi-building me-1"></i>
                                                Schools Supported
                                            </label>
                                            <input type="number" 
                                                   class="form-control form-control-lg text-center" 
                                                   id="schools_supported" 
                                                   name="schools_supported" 
                                                   value="<?php echo htmlspecialchars($currentStats['schools_supported']); ?>"
                                                   min="0" 
                                                   required>
                                            <div class="form-text">Number of public schools currently supported</div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="total_contributions" class="form-label">
                                                <i class="bi bi-currency-dollar me-1"></i>
                                                Total Contributions
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-lg text-center" 
                                                   id="total_contributions" 
                                                   name="total_contributions" 
                                                   value="<?php echo htmlspecialchars($currentStats['total_contributions']); ?>"
                                                   placeholder="₱12.7M"
                                                   required>
                                            <div class="form-text">Total financial contributions (e.g., ₱12.7M)</div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="ongoing_projects" class="form-label">
                                                <i class="bi bi-clipboard-check me-1"></i>
                                                Ongoing Projects
                                            </label>
                                            <input type="number" 
                                                   class="form-control form-control-lg text-center" 
                                                   id="ongoing_projects" 
                                                   name="ongoing_projects" 
                                                   value="<?php echo htmlspecialchars($currentStats['ongoing_projects']); ?>"
                                                   min="0" 
                                                   required>
                                            <div class="form-text">Number of currently active projects</div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="active_partners" class="form-label">
                                                <i class="bi bi-people me-1"></i>
                                                Active Partners
                                            </label>
                                            <input type="number" 
                                                   class="form-control form-control-lg text-center" 
                                                   id="active_partners" 
                                                   name="active_partners" 
                                                   value="<?php echo htmlspecialchars($currentStats['active_partners']); ?>"
                                                   min="0" 
                                                   required>
                                            <div class="form-text">Number of active partner organizations</div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-check-lg me-2"></i>
                                            Update Statistics
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i>
                                    Preview
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">This is how the statistics will appear on the homepage:</p>
                                
                                <!-- Mini preview of stats -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded text-center">
                                            <div class="fw-bold text-primary"><?php echo htmlspecialchars($currentStats['schools_supported']); ?></div>
                                            <small>Schools</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-success bg-opacity-10 p-2 rounded text-center">
                                            <div class="fw-bold text-success"><?php echo htmlspecialchars($currentStats['total_contributions']); ?></div>
                                            <small>Contributions</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-info bg-opacity-10 p-2 rounded text-center">
                                            <div class="fw-bold text-info"><?php echo htmlspecialchars($currentStats['ongoing_projects']); ?></div>
                                            <small>Projects</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-warning bg-opacity-10 p-2 rounded text-center">
                                            <div class="fw-bold text-warning"><?php echo htmlspecialchars($currentStats['active_partners']); ?></div>
                                            <small>Partners</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-grid">
                                    <a href="../user/index.php" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-up-right-square me-1"></i>
                                        View Full Homepage
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Card -->
                        <div class="card shadow mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Tips
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Keep numbers accurate and up-to-date
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Use consistent formatting (e.g., ₱12.7M, not ₱12,700,000)
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Update regularly to maintain credibility
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Round large numbers for better readability
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/admin-script.js"></script>
</body>
</html>