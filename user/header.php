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

// Set page title if not already set
if (!isset($pageTitle)) {
    $pageTitle = "SMN Documents";
}

if (!isset($additionalCss)) {
    $additionalCss = [];
}
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
                <li class="nav-item d-none d-lg-flex align-items-center px-2">
                    <div style="height: 24px; border-left: 1px solid #ccc;"></div>
                </li>

                <li class="nav-item">
                    <a class="nav-link fw-bold" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj.php">Project ISSHED</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj.php#adopt-a-school">Adopt-a-School</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj.php#brigada-eskwela">Brigada Eskwela</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="taxIncentives.php">Tax Incentives</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="proj.php#be-our-partner">Be Our Partner</a></li>
                        <li><a class="dropdown-item" href="index.php#news-partnership-updates">News & Partnership Updates</a></li>
                         <li><a class="dropdown-item" href="smn_document.php">Smn documents</a></li>
                    </ul>
                </li>

              
            </ul>
        </div>
    </div>
</nav>