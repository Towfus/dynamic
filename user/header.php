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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
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
            padding-top: 80px; /* Account for fixed navbar */
        }

        /* Custom green utility class */
        .custom-green {
            color: var(--primary-green) !important;
        }

        /* Header Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 3px solid var(--primary-green);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .navbar-brand span:first-child {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        .navbar-brand span:last-child {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .navbar-toggler {
            border: 1px solid var(--primary-green);
            padding: 0.5rem 0.75rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%2322c55e' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle {
            background: var(--pale-green);
            border: 1px solid var(--gray-200);
            color: var(--primary-green);
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: var(--light-green);
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        #checkbox {
            display: none;
        }

        /* Body and Navigation */
        .body {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .side-bar {
            width: 280px;
            background: var(--white);
            border-left: 1px solid var(--gray-200);
            box-shadow: var(--shadow-lg);
            transition: transform 0.3s ease;
            position: fixed;
            top: 80px;
            right: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 999;
        }

        #checkbox:checked ~ .body .side-bar {
            transform: translateX(100%);
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
            border-left: 3px solid transparent;
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
            padding-left: 1.5rem;
            box-shadow: var(--shadow-md);
            border-left-color: var(--primary-green);
        }

        .user-p a i {
            font-size: 1.125rem;
            width: 24px;
            text-align: center;
            color: var(--primary-green);
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .active-nav a {
            background: linear-gradient(135deg, var(--primary-green), var(--dark-green)) !important;
            color: var(--white) !important;
            padding-left: 1.5rem;
            box-shadow: var(--shadow-lg);
            border-left-color: var(--white) !important;
        }

        .active-nav a i {
            color: var(--white) !important;
            opacity: 1;
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
            margin-right: 280px;
            padding: 2rem;
            transition: margin-right 0.3s ease;
            min-height: calc(100vh - 80px);
        }

        #checkbox:checked ~ .body .main-content {
            margin-right: 0;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-top: 76px;
            }

            .side-bar {
                width: 100%;
                transform: translateX(100%);
                top: 76px;
            }

            #checkbox:checked ~ .body .side-bar {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
                padding: 1rem;
            }

            #checkbox:checked ~ .body .main-content {
                margin-right: 0;
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

        /* Logo images */
        .logo-republic,
        .logo-deped {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: drop-shadow(var(--shadow-sm));
        }

        /* Vertical divider */
        .navbar-divider {
            height: 24px; 
            border-left: 1px solid var(--gray-300);
            margin: 0 1rem;
        }
    </style>
</head>
<body>

<input type="checkbox" id="checkbox">

<!-- Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button (visible on all screen sizes) -->
        <div class="d-flex align-items-center order-lg-last">
            <label for="checkbox" class="sidebar-toggle d-flex align-items-center">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </label>
        </div>

        <!-- Brand/Logo Section -->
        <div class="navbar-brand d-flex align-items-center">
            <div class="d-flex flex-column align-items-start">
                <span class="custom-green fw-bold">SDO General Trias</span>
                <span class="text-muted fs-6">Partnership and Linkages</span>
            </div>
        </div>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Content -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
            <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                <!-- Vertical Divider (desktop only) -->
                <li class="nav-item d-none d-lg-flex align-items-center px-2">
                    <div class="navbar-divider"></div>
                </li>

                <!-- Additional nav items can be added here if needed -->
                <li class="nav-item">
                    <span class="nav-link text-muted">Welcome to Private School Portal</span>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="body">
    <nav class="side-bar">
        <div class="user-p">
            <ul>
                <div class="menu-category">SDO General Trias</div>
                <li class="<?php echo isActive('index.php'); ?>">
                    <a href="index.php">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Home</span>
                    </a>
                </li>

                <li class="<?php echo isActive('proj-isshed.php'); ?>">
                    <a href="proj-isshed.php">
                        <i class="fas fa-map-marked-alt nav-icon"></i>
                        <span>Project ISSHED</span>
                    </a>
                </li>

                <li class="<?php echo isActive('adopt_a_school.php'); ?>">
                    <a href="proj-isshed.html#adopt-a-school">
                        <i class="fas fa-bullhorn nav-icon"></i>
                        <span>Adopt A School</span>
                    </a>
                </li>

                <li class="">
                    <a href="#brigada-eskwela.php">
                        <i class="fas fa-file-text nav-icon"></i>
                        <span>Brigada Eskwela</span>
                    </a>
                </li>

                <li class="<?php echo isActive('tax_incentives.php'); ?>">
                    <a href="tax_incentives.php">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <span>Tax Incentives</span>
                    </a>
                </li>

                <div class="menu-category">Partnership and Linkages</div>
                <li class="<?php echo isActive('Be_our_partner.php'); ?>">
                    <a href="proj-isshed.html#be-our-partner">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Be Our Partner</span>
                    </a>
                </li>

                <li class="<?php echo isActive('Be_our_partner.php'); ?>">
                    <a href="proj-isshed.html#be-our-partner">
                        <i class="fas fa-home nav-icon"></i>
                        <span>Home</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </nav>

    <div class="main-content" id="mainContent">
        <!-- Page content starts here -->

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>