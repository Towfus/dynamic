<?php
// Function to check if a page is active
function isActive($pageName) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage == $pageName) ? 'active-nav' : '';
}

// Function to check if a page belongs to a specific group (for dropdowns, if needed in future)
function isInGroup($pageNames) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return in_array($currentPage, $pageNames) ? 'active-nav' : '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        :root {
            --primary-green: #22c55e;
            --light-green: #86efac;
            --pale-green: #f0fdf4;
            --dark-green: #16a34a;
            --accent-green: #4ade80;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--pale-green) 0%, var(--gray-50) 100%);
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--white) 0%, var(--gray-50) 100%);
            border-bottom: 3px solid var(--primary-green);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        #checkbox {
            display: none;
        }

        label[for="checkbox"] {
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: var(--pale-green);
            border: 1px solid var(--gray-200);
        }

        label[for="checkbox"]:hover {
            background: var(--light-green);
            border-color: var(--primary-green);
        }

        #navbtn {
            font-size: 1.25rem;
            color: var(--primary-green);
        }

        .deped-header-text {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logo-republic,
        .logo-deped {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(var(--shadow-sm));
        }

        .header-title {
            text-align: center;
            color: var(--text-dark);
            font-weight: 600;
        }

        .header-title div:first-child {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 0.25rem;
        }

        .header-title div:not(:first-child) {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        /* Body and Navigation */
        .body {
            display: flex;
            min-height: calc(100vh - 100px);
        }

        .side-bar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            box-shadow: var(--shadow-lg);
            transition: transform 0.3s ease;
            position: fixed;
            top: 100px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 999;
        }

        #checkbox:checked ~ .body .side-bar {
            transform: translateX(-100%);
        }

        .user-p {
            padding: 2rem 0;
        }

        .user-p ul {
            list-style: none;
        }

        .user-p li {
            margin-bottom: 0.5rem;
            padding: 0 1.5rem;
        }

        .user-p a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 500;
            transition: all 0.3s ease, border-left-color 0.3s ease;
            position: relative;
            overflow: hidden;

        }

        .user-p a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .user-p a:hover::before {
            left: 100%;
        }

        .user-p a:hover {
            background: var(--pale-green);
            color: var(--primary-green);
            transform: translateX(8px);
            box-shadow: var(--shadow-md);
        }

        .user-p a i {
            font-size: 1.125rem;
            width: 24px;
            text-align: center;
            color: var(--primary-green);
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .user-p a:hover {
            background: var(--pale-green);
            color: var(--primary-green);
            /* Remove transform and replace with padding-left */
            padding-left: 1.5rem;
            box-shadow: var(--shadow-md);
        }

       .active-nav a {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green)) !important;
            color: var(--white) !important;
            /* Remove transform and replace with padding-left */
            padding-left: 1.5rem;
            box-shadow: var(--shadow-lg);
            /* Keep other properties */
            border-left-color: var(--white) !important;
        }

        .active-nav a i {
            color: var(--white) !important;
            opacity: 1;
            /* Remove transform scaling */
        }

        /* Navigation Icons */
        .nav-icon {
            position: relative;
        }

        .nav-icon::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: var(--accent-green);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-p a:hover .nav-icon::after,
        .active-nav .nav-icon::after {
            opacity: 1;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 100px);
        }

        #checkbox:checked ~ .body .main-content {
            margin-left: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .logo-container {
                gap: 1rem;
            }

            .logo-republic,
            .logo-deped {
                width: 45px;
                height: 45px;
            }

            .header-title div:first-child {
                font-size: 1rem;
            }

            .header-title div:not(:first-child) {
                font-size: 0.75rem;
            }

            .side-bar {
                width: 100%;
                transform: translateX(-100%);
            }

            #checkbox:checked ~ .body .side-bar {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            #checkbox:checked ~ .body .main-content {
                margin-left: 0;
            }
        }

        /* Custom Scrollbar */
        .side-bar::-webkit-scrollbar {
            width: 6px;
        }

        .side-bar::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .side-bar::-webkit-scrollbar-thumb {
            background: var(--light-green);
            border-radius: 3px;
        }

        .side-bar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-green);
        }

        /* Enhanced hover effects */
        .user-p a {
            border-left: 3px solid transparent;
            transition: all 0.3s ease, border-left-color 0.3s ease;
        }

        .user-p a:hover {
            border-left-color: var(--primary-green);
        }

        .active-nav a {
            border-left-color: var(--white) !important;
        }

        /* Menu categories */
        .menu-category {
            padding: 1rem 1.5rem 0.5rem;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-light);
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 0.5rem;
        }

        .menu-category:first-child {
            margin-top: 0;
        }
        
    </style>
</head>
<body>

<input type="checkbox" id="checkbox">
<header class="header">
    <div class="header-left">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
    </div>
    <div class="deped-header-text">
        <div class="logo-container">
            <img src="images/deped_republic.png" alt="Republic of the Philippines" class="logo-republic">
            <div class="header-title">
                <div>Project ISSHED</div>
                <div>Department of Education</div>
                <div>Division of General Trias City</div>
            </div>
            <img src="images/deped_logo.png" alt="DepEd Logo" class="logo-deped">
        </div>
    </div>
</header>

<div class="body">
    <nav class="side-bar">
        <div class="user-p">
            <ul>
                <div class="menu-category">Dashboard</div>
                <li class="<?php echo isActive('admin_stats.php'); ?>">
                    <a href="admin_stats.php">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Stats</span>
                    </a>
                </li>
                
                <div class="menu-category">Information</div>
                <li class="<?php echo isActive('submit_impact_stories.php'); ?>">
                    <a href="submit_impact_stories.php">
                        <i class="fas fa-map-marked-alt nav-icon"></i>
                        <span>Stories</span>
                    </a>
                </li>
                
                <div class="menu-category">Documents & Resources</div>
                <li class="<?php echo isActive('admin_partners.php'); ?>">
                    <a href="admin_partners.php">
                        <i class="fas fa-bullhorn nav-icon"></i>
                        <span>Partners</span>
                    </a>
                </li>
                <li class="<?php echo isActive('submit_news.php'); ?>">
                    <a href="submit_news.php">
                        <i class="fas fa-file-text nav-icon"></i>
                        <span>News and Partnership Updates</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content" id="mainContent">
        <!-- Page content starts here -->