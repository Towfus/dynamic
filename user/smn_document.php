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
            /* Updated Color Palette based on the image */
            --primary-green: #14740D;
            --primary-green-light: #2E8B27;
            --primary-green-dark: #0D5C08;
            --light-green: #E8F5E6;
            --pale-green: #F5FBF4;
            --accent-green: #4CAF50;
            
            /* Neutral Colors */
            --text-dark: #333333;
            --text-medium: #555555;
            --text-light: #777777;
            --text-muted: #999999;
            
            --white: #ffffff;
            --gray-50: #f9f9f9;
            --gray-100: #f3f3f3;
            --gray-200: #eaeaea;
            --gray-300: #dddddd;
            
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
            background: var(--pale-green);
            min-height: 100vh;
            color: var(--text-dark);
            padding-top: 80px;
            line-height: 1.6;
        }

        /* Header Styles from original design */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--primary-green);
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
            color: var(--primary-green);
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

        /* Main Content - Adjusted for no sidebar */
        .main-content {
            padding: 2rem;
            min-height: calc(100vh - 80px);
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            background: var(--white);
            padding: 3rem 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .page-header p {
            font-size: 1.125rem;
            color: var(--text-medium);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Documents Section */
        .documents-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .documents-header {
            background: var(--primary-green);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .documents-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
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
            padding: 1.5rem;
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
            padding: 0.875rem 1.25rem 0.875rem 3.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: var(--white);
            font-weight: 400;
            font-family: 'Inter', sans-serif;
        }

        .search-container input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(20, 116, 13, 0.1);
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
            padding: 1.5rem;
        }

        .document-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem;
            margin-bottom: 1rem;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
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
            background: var(--light-green);
        }

        .document-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .document-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        .document-details h3 {
            font-size: 1rem;
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
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
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
            box-shadow: var(--shadow-sm);
            color: white;
        }

        .download-btn {
            background: var(--text-medium);
            color: white;
        }

        .download-btn:hover {
            background: var(--text-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--text-muted);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .empty-state p {
            font-size: 1rem;
            color: var(--text-light);
        }

        /* Footer */
        footer {
            background: var(--text-dark) !important;
            margin-top: 3rem;
        }

        footer a {
            color: #d1d5db !important;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: var(--light-green) !important;
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

            .main-content {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .documents-header {
                padding: 1.5rem;
            }
            
            .documents-header h2 {
                font-size: 1.5rem;
            }
            
            .search-section,
            .documents-grid {
                padding: 1rem;
            }
            
            .document-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
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
                font-size: 1.75rem;
            }
            
            .document-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }

            .documents-grid {
                padding: 0.75rem;
            }

            .document-card {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>

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
                </li>

                <li class="nav-item d-none d-lg-flex align-items-center px-2">
                    <div style="height: 24px; border-left: 1px solid #ccc;"></div>
                </li>

                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo isActive('index.php'); ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo isActive('proj-isshed.php'); ?>" href="proj-isshed.php">Project ISSHED</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj-isshed.php#adopt-a-school">Adopt-a-School</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="proj-isshed.php#brigada-eskwela">Brigada Eskwela</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo isActive('taxIncentives.php'); ?>" href="taxIncentives.php">Tax Incentives</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="proj-isshed.php#be-our-partner">Be Our Partner</a></li>
                        <li><a class="dropdown-item <?php echo isActive('smn_document.php'); ?>" href="smn_document.php">SMN Documents</a></li>
                        <li><a class="dropdown-item" href="index.php#news-partnership-updates">News & Partnership Updates</a></li>
                    </ul>
                </li>

            </ul>
        </div>
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