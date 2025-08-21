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

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sdo_gentri";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the upload directory with web-accessible path
$upload_dir = 'shared/documents/';

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

// Fetch all SMN Documents for display
$docsSql = "SELECT * FROM smn_documents ORDER BY upload_date DESC";
$docsResult = $conn->query($docsSql);

// Set page title
$pageTitle = "SMN Documents";
$additionalCss = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        :root {
            /* Main Color Palette */
            --primary-green: #006400;
            --primary-green-light: #22c55e;
            --primary-green-dark: #16a34a;
            --primary-green-darker: #15803d;
            --light-green: #86efac;
            --pale-green: #f0fdf4;
            --secondary-green: #dcfce7;
            --accent-green: #4ade80;
            
            /* Text Colors */
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --text-muted: #9ca3af;
            
            /* Background Colors */
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --background-light: #f8fafc;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--pale-green) 0%, var(--gray-50) 100%);
            min-height: 100vh;
            color: var(--text-dark);
            padding-top: 80px;
            line-height: 1.6;
        }

        /* Header Styles from original design */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid #218838;
            backdrop-filter: blur(15px);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .navbar-brand span:first-child {
            font-size: 2rem;
            padding-left: 20px;
            font-weight: bold;
            display: block;
        }

        .navbar-brand span:last-child {
            font-size: 1rem;
            padding-left: 20px; 
            color: #000000;
            display: block;
            margin-top: -2px;
        }

        .custom-green {
            color: #14740D;
        }

        .navbar-nav .nav-link {
            font-weight: 600;
            font-size: 15px;
            color: #000000 !important;
            padding: 10px 15px;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
        }

        .navbar-nav .nav-item {
            margin-right: 20px; 
        }

        .navbar-nav .nav-link:hover {
            background-color: #e6e8e675;
            color: black !important;
            border-radius: 5px;
            transform: scale(1.05);
        }

        .dropdown-menu {
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .dropdown-menu .dropdown-item {
            font-size: 0.95rem;
            font-weight: 500;
            color: #000000;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #e6e8e675;
            color: #000000;
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle {
            background: var(--pale-green);
            border: 1px solid var(--gray-200);
            color: var(--primary-green);
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
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
            border-right: 1px solid var(--gray-200);
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
            transition: all 0.3s ease;
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
            background: linear-gradient(90deg, transparent, rgba(0, 100, 0, 0.1), transparent);
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
            background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark)) !important;
            color: var(--white) !important;
            padding-left: 1.5rem;
            box-shadow: var(--shadow-lg);
            border-left-color: var(--white) !important;
        }

        .active-nav a i {
            color: var(--white) !important;
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

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            background: var(--white);
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .page-header p {
            font-size: 1.125rem;
            color: var(--text-light);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Documents Section */
        .documents-section {
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .documents-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }

        .documents-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .documents-header p {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 400;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Search Section */
        .search-section {
            padding: 2rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-container input {
            width: 100%;
            padding: 1rem 1.25rem 1rem 3.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: var(--white);
            font-weight: 400;
            font-family: 'Inter', sans-serif;
        }

        .search-container input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(0, 100, 0, 0.1);
        }

        .search-container i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.125rem;
        }

        /* Documents Grid */
        .documents-grid {
            padding: 2rem;
        }

        .document-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .document-card:last-child {
            margin-bottom: 0;
        }

        .document-card:hover {
            border-color: var(--primary-green);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            background: #fafffe;
        }

        .document-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .document-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: var(--shadow-md);
            flex-shrink: 0;
        }

        .document-details h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .document-meta {
            font-size: 0.875rem;
            color: var(--text-light);
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .document-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .document-description {
            margin-top: 0.5rem;
            font-style: italic;
            color: var(--text-muted);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .document-actions {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .action-btn {
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }

        .view-btn {
            background: var(--primary-green);
            color: white;
        }

        .view-btn:hover {
            background: var(--primary-green-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .download-btn {
            background: var(--text-dark);
            color: white;
        }

        .download-btn:hover {
            background: #374151;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--text-muted);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }

        .empty-state p {
            font-size: 1rem;
            color: var(--text-light);
        }

        /* Footer */
        footer {
            background: var(--text-dark) !important;
        }

        footer a {
            color: #d1d5db !important;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--light-green) !important;
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

        /* Responsive Design */
        @media (max-width: 991px) {
            .navbar-brand {
                align-items: flex-start;
            }

            .navbar-collapse {
                background-color: #ffffff;
                padding: 10px 20px;
                border-top: 1px solid #ddd;
            }
        }

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

            .page-header {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }
            
            .page-header h1 {
                font-size: 2.25rem;
            }
            
            .documents-header {
                padding: 2rem 1.5rem;
            }
            
            .documents-header h2 {
                font-size: 1.75rem;
            }
            
            .search-section,
            .documents-grid {
                padding: 1.5rem;
            }
            
            .document-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.25rem;
                padding: 1.25rem;
            }
            
            .document-info {
                width: 100%;
            }
            
            .document-actions {
                width: 100%;
                justify-content: stretch;
            }
            
            .action-btn {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .document-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }

            .documents-grid {
                padding: 1rem;
            }

            .document-card {
                padding: 1rem;
            }
        }

        /* Animation for search results */
        .document-card {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading state for search */
        .search-container input:focus + .search-icon {
            color: var(--primary-green);
        }
    </style>
</head>
<body>

<input type="checkbox" id="checkbox">

<!-- Bootstrap Navbar from original design -->
<nav class="navbar navbar-expand-lg fixed-top bg-light shadow-sm">
    <div class="container-fluid">
        <!-- Brand/Logo Section -->
        <div class="navbar-brand d-flex flex-column align-items-start">
            <span class="custom-green fw-bold">SDO General Trias</span>
            <span class="text-muted fs-6">Partnership and Linkages</span>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGenTri" aria-controls="navbarGenTri" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Search field and navigation -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarGenTri">
            <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                <li class="nav-item me-3">
                    <form class="d-flex" role="search">
                        <div class="input-group">
                            <input class="form-control" type="search" placeholder="Search..." aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </li>

                <li class="nav-item d-none d-lg-flex align-items-center px-2">
                    <div style="height: 24px; border-left: 1px solid #ccc;"></div>
                </li>

                <li class="nav-item">
                    <a class="nav-link fw-bold" href="index.html">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj-isshed.html">Project ISSHED</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="#adopt-a-school">Adopt-a-School</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="#brigada-eskwela">Brigada Eskwela</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="taxIncentives.html">Tax Incentives</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#be-our-partner">Be Our Partner</a></li>
                        <li><a class="dropdown-item" href="#news-partnership-updates">News & Partnership Updates</a></li>
                    </ul>
                </li>

                <!-- Sidebar Toggle Button -->
                <li class="nav-item">
                    <label for="checkbox" class="sidebar-toggle d-flex align-items-center ms-3">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </label>
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
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>

                <li class="<?php echo isActive('proj-isshed.php'); ?>">
                    <a href="proj-isshed.php">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Project ISSHED</span>
                    </a>
                </li>

                <li class="<?php echo isActive('proj-isshed.php'); ?>">
                    <a href="proj-isshed.php#adopt-a-school">
                        <i class="fas fa-bullhorn"></i>
                        <span>Adopt A School</span>
                    </a>
                </li>

                <li class="<?php echo isActive('proj-isshed.php'); ?>">
                    <a href="proj-isshed.php#brigada-eskwela">
                        <i class="fas fa-hammer"></i>
                        <span>Brigada Eskwela</span>
                    </a>
                </li>

                <li class="<?php echo isActive('taxIncentives.php'); ?>">
                    <a href="taxIncentives.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Tax Incentives</span>
                    </a>
                </li>

                <div class="menu-category">MORE</div>
                <li class="<?php echo isActive('Be_our_partner.php'); ?>">
                    <a href="proj-isshed.php#be-our-partner">
                        <i class="fas fa-handshake"></i>
                        <span>Be Our Partner</span>
                    </a>
                </li>

                <li class="<?php echo isActive('smn_document.php'); ?>">
                    <a href="smn_document.php">
                        <i class="fas fa-file-alt"></i>
                        <span>SMN Documents</span>
                    </a>
                </li>

                 <li class="<?php echo isActive('index.php'); ?>">
                    <a href="index.php#news-partnership-updates">
                        <i class="fas fa-newspaper"></i>
                        <span>News And Partnerships</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content" id="mainContent">
        <!-- Page content starts here -->
        <main class="main-container">
            <div class="page-header">
                <h1>SMN Documents Repository</h1>
                <p>Access, view, and download all official SMN documents from our comprehensive repository</p>
            </div>

            <div class="documents-section">
                <div class="documents-header">
                    <h2>Official SMN Documents</h2>
                    <p>Find and download the SMN forms and documents you need. Our repository contains the most up-to-date versions of all official forms. If you need assistance or can't find what you're looking for, please don't hesitate to contact us.</p>
                </div>

                <div class="search-section">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search documents by title or description...">
                    </div>
                </div>

                <div class="documents-grid" id="documentsGrid">
                    <?php if ($docsResult && $docsResult->num_rows > 0): ?>
                        <?php while ($row = $docsResult->fetch_assoc()): ?>
                        <?php
                        // Get file information
                        $file_path = $row['file_path'];
                        $file_info = pathinfo($file_path);
                        $file_ext = isset($file_info['extension']) ? strtoupper($file_info['extension']) : 'PDF';
                        
                        // Get file size if file exists
                        $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file_path;
                        $file_size = file_exists($full_path) ? formatSizeUnits(filesize($full_path)) : 'N/A';
                        
                        // Format date
                        $upload_date = date('M d, Y', strtotime($row['upload_date']));
                        ?>
                        <div class="document-card" data-title="<?php echo strtolower(htmlspecialchars($row['title'])); ?>" data-description="<?php echo strtolower(htmlspecialchars($row['description'] ?? '')); ?>">
                            <div class="document-info">
                                <div class="document-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="document-details">
                                    <h3 class="document-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <div class="document-meta">
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo $upload_date; ?></span>
                                        <span><i class="fas fa-hdd"></i> <?php echo $file_size; ?></span>
                                        <span><i class="fas fa-file"></i> <?php echo $file_ext; ?></span>
                                    </div>
                                    <?php if (!empty($row['description'])): ?>
                                        <div class="document-description"><?php echo htmlspecialchars($row['description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="document-actions">
                                <a href="/<?php echo $file_path; ?>" target="_blank" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i>
                                    View
                                </a>
                                <a href="/<?php echo $file_path; ?>" download class="action-btn download-btn">
                                    <i class="fas fa-download"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h3>No Documents Available</h3>
                            <p>There are currently no SMN documents in the repository. Please check back later or contact us for assistance.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-dark text-white py-5 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h5 class="mb-3">SDO General Trias</h5>
                        <p>Empowering education through strategic partnerships <br> with schools, businesses, and the community.</p>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white"><i class="fab fa-facebook fs-4"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter fs-4"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-instagram fs-4"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                        <h5 class="mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                            <li class="mb-2"><a href="proj-isshed.php" class="text-white text-decoration-none">Project ISSHED</a></li>
                            <li class="mb-2"><a href="proj-isshed.php#adopt-a-school" class="text-white text-decoration-none">Adopt-a-School</a></li>
                            <li class="mb-2"><a href="taxIncentives.php" class="text-white text-decoration-none">Tax Incentives</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                        <h5 class="mb-3">Contact</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>General Trias City, Cavite</li>
                            <li class="mb-2"><i class="fas fa-envelope me-2"></i>sdo.gentri@deped.gov.ph</li>
                            <li class="mb-2"><i class="fas fa-phone me-2"></i>+63 46 123 4567</li>
                        </ul>
                    </div>
                    <div class="col-lg-3">
                        <h5 class="mb-3">Office Hours</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-clock me-2"></i>Monday-Friday: 8:00 AM - 5:00 PM</li>
                            <li class="mb-2"><i class="fas fa-clock me-2"></i>Saturday: Closed</li>
                            <li class="mb-2"><i class="fas fa-clock me-2"></i>Sunday: Closed</li>
                            <li class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Closed on Holidays</li>
                        </ul>
                    </div>
                </div>
                <hr class="my-4">
                <div class="text-center">
                    <p class="mb-0">© 2025 SDO General Trias. All Rights Reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Enhanced search functionality with debouncing
    let searchTimeout;
    
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchText = this.value.toLowerCase().trim();
        
        // Debounce search for better performance
        searchTimeout = setTimeout(() => {
            performSearch(searchText);
        }, 300);
    });

    function performSearch(searchText) {
        const documents = document.querySelectorAll('.document-card');
        let visibleCount = 0;
        
        documents.forEach(doc => {
            const title = doc.getAttribute('data-title') || '';
            const description = doc.getAttribute('data-description') || '';
            
            if (searchText === '' || title.includes(searchText) || description.includes(searchText)) {
                doc.style.display = 'flex';
                doc.style.animation = 'fadeIn 0.3s ease-in-out';
                visibleCount++;
            } else {
                doc.style.display = 'none';
            }
        });

        // Show/hide empty state based on search results
        updateEmptyState(visibleCount, searchText);
    }

    function updateEmptyState(visibleCount, searchText) {
        const documentsGrid = document.getElementById('documentsGrid');
        let existingEmptyState = documentsGrid.querySelector('.search-empty-state');
        
        if (existingEmptyState) {
            existingEmptyState.remove();
        }

        if (visibleCount === 0 && searchText !== '') {
            const emptyStateHTML = `
                <div class="empty-state search-empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No Results Found</h3>
                    <p>No documents match your search for "<strong>${escapeHtml(searchText)}</strong>". Try different keywords or check the spelling.</p>
                </div>
            `;
            documentsGrid.insertAdjacentHTML('beforeend', emptyStateHTML);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Document card click functionality with improved UX
    document.querySelectorAll('.document-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (!e.target.closest('.document-actions')) {
                const viewBtn = this.querySelector('.view-btn');
                if (viewBtn) {
                    // Add visual feedback
                    this.style.transform = 'translateY(-4px)';
                    setTimeout(() => {
                        this.style.transform = '';
                        viewBtn.click();
                    }, 150);
                }
            }
        });

        // Add hover effects for better interactivity
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Enhanced sidebar toggle functionality
    const checkbox = document.getElementById('checkbox');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    // Add keyboard support for accessibility
    sidebarToggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            checkbox.checked = !checkbox.checked;
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.side-bar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (checkbox.checked && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target)) {
                checkbox.checked = false;
            }
        }
    });

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lazy loading for better performance (if needed for images in future)
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Add loading state for document actions
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            const isDownload = this.classList.contains('download-btn');
            
            if (isDownload) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                this.style.pointerEvents = 'none';
                
                // Reset after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.pointerEvents = '';
                }, 2000);
            }
        });
    });

    // Add print functionality (if needed)
    function printDocumentList() {
        const printWindow = window.open('', '_blank');
        const documentsHTML = document.querySelector('.documents-grid').innerHTML;
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>SMN Documents - Print View</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .document-card { margin-bottom: 20px; border: 1px solid #ccc; padding: 15px; }
                    .document-actions { display: none; }
                    @media print {
                        .document-actions { display: none !important; }
                    }
                </style>
            </head>
            <body>
                <h1>SMN Documents Repository</h1>
                <div class="documents-grid">${documentsHTML}</div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
</body>
</html>