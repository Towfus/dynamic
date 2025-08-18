<?php
// admin/includes/sidebar.php - Admin Sidebar Navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-house-door"></i>
                    Dashboard
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Content Management</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage-statistics.php' ? 'active' : ''; ?>" href="manage-statistics.php">
                    <i class="bi bi-bar-chart"></i>
                    Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage-stories.php' ? 'active' : ''; ?>" href="manage-stories.php">
                    <i class="bi bi-newspaper"></i>
                    Impact Stories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage-partners.php' ? 'active' : ''; ?>" href="manage-partners.php">
                    <i class="bi bi-building"></i>
                    Partners
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage-news.php' ? 'active' : ''; ?>" href="manage-news.php">
                    <i class="bi bi-broadcast"></i>
                    News & Updates
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>System</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>" href="manage-users.php">
                    <i class="bi bi-people"></i>
                    Admin Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'activity-logs.php' ? 'active' : ''; ?>" href="activity-logs.php">
                    <i class="bi bi-clock-history"></i>
                    Activity Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'file-manager.php' ? 'active' : ''; ?>" href="file-manager.php">
                    <i class="bi bi-folder"></i>
                    File Manager
                </a>
            </li>
        </ul>
    </div>
</nav>