<?php
// admin/index.php - Admin Dashboard
require_once '../shared/config.php';

// Get dashboard statistics
$db = Database::getInstance()->getConnection();

// Count records
$statsStmt = $db->query("SELECT 
    (SELECT COUNT(*) FROM impact_stories WHERE status = 'active') as active_stories,
    (SELECT COUNT(*) FROM partners WHERE status = 'active') as active_partners,
    (SELECT COUNT(*) FROM news_updates WHERE status = 'active') as active_news,
    (SELECT COUNT(*) FROM admin_users WHERE status = 'active') as active_admins
");
$counts = $statsStmt->fetch();

// Recent activity
$recentStmt = $db->prepare("SELECT al.*, au.username 
    FROM activity_logs al 
    LEFT JOIN admin_users au ON al.admin_id = au.id 
    ORDER BY al.created_at DESC 
    LIMIT 10");
$recentStmt->execute();
$recentActivities = $recentStmt->fetchAll();

// Get current statistics
$currentStats = getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Impact Stories
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $counts['active_stories']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-newspaper fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Active Partners
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $counts['active_partners']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            News Updates
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $counts['active_news']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-broadcast fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Admin Users
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $counts['active_admins']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-gear fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Website Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <a href="manage-stories.php?action=add" class="btn btn-outline-primary btn-block w-100">
                                            <i class="bi bi-plus-circle"></i><br>
                                            <small>Add Story</small>
                                        </a>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <a href="manage-partners.php?action=add" class="btn btn-outline-success btn-block w-100">
                                            <i class="bi bi-building-add"></i><br>
                                            <small>Add Partner</small>
                                        </a>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <a href="manage-news.php?action=add" class="btn btn-outline-info btn-block w-100">
                                            <i class="bi bi-newspaper"></i><br>
                                            <small>Add News</small>
                                        </a>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <a href="../user/index.php" target="_blank" class="btn btn-outline-secondary btn-block w-100">
                                            <i class="bi bi-eye"></i><br>
                                            <small>View Site</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-muted">No recent activity recorded.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Table</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['username'] ?: 'System'); ?></td>
                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                            <td>
                                                <?php if ($activity['table_affected']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($activity['table_affected']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo formatDate($activity['created_at'], 'M j, Y g:i A'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/admin-script.js"></script>
</body>
</html>