<?php
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


include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMN Documents - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.smn-docs {
            --primary-green: #22c55e;
            --primary-green-dark: #16a34a;
            --primary-green-darker: #15803d;
            --secondary-green: #dcfce7;
            --accent-green: #bbf7d0;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --text-light: #9ca3af;
            --background-light: #f8fafc;
            --background-white: #ffffff;
            --border-light: #e5e7eb;
            --shadow-light: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-medium: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-large: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--background-light) 0%, #e0f2fe 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }


        .contact-info {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .contact-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 400;
        }

        .contact-info i {
            opacity: 0.9;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-links a {
            color: white;
            text-decoration: none;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
        }

        .social-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .header-main {
            padding: 16px 0;
        }

        .header-main-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: var(--text-dark);
        }

        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            box-shadow: var(--shadow-medium);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-main {
            font-weight: 700;
            font-size: 1.5rem;
            line-height: 1.2;
            color: var(--text-dark);
        }

        .logo-sub {
            font-size: 0.875rem;
            color: var(--text-gray);
            font-weight: 400;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 32px;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 10px 16px;
            border-radius: 8px;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            color: var(--primary-green);
            background: var(--secondary-green);
        }

        .nav-menu a.active {
            color: var(--primary-green);
            background: var(--secondary-green);
            font-weight: 600;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            background: var(--secondary-green);
            color: var(--primary-green);
        }

        /* Main Content */
        .main-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 48px;
            background: var(--background-white);
            padding: 48px 32px;
            border-radius: 20px;
            box-shadow: var(--shadow-large);
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            font-size: 1.125rem;
            color: var(--text-gray);
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
        }

        .documents-section {
            background: var(--background-white);
            border-radius: 24px;
            box-shadow: var(--shadow-large);
            overflow: hidden;
        }

        .documents-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            padding: 40px 32px;
            text-align: center;
            color: white;
        }

        .documents-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .documents-header p {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 400;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .search-section {
            padding: 32px;
            background: #f9fafb;
            border-bottom: 1px solid var(--border-light);
        }

        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-container input {
            width: 100%;
            padding: 16px 20px 16px 56px;
            border: 2px solid var(--border-light);
            border-radius: 16px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: var(--background-white);
            font-weight: 400;
        }

        .search-container input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .search-container i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.125rem;
        }

        .documents-grid {
            padding: 32px;
        }

        .document-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            margin-bottom: 20px;
            background: var(--background-white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .document-card:hover {
            border-color: var(--primary-green);
            box-shadow: var(--shadow-medium);
            transform: translateY(-2px);
            background: #fefffe;
        }

        .document-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 20px;
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
            box-shadow: var(--shadow-medium);
            flex-shrink: 0;
        }

        .document-details h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .document-meta {
            font-size: 0.875rem;
            color: var(--text-gray);
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .document-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .document-description {
            margin-top: 8px;
            font-style: italic;
            color: var(--text-light);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .document-actions {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
        }

        .action-btn {
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .view-btn {
            background: var(--primary-green);
            color: white;
        }

        .view-btn:hover {
            background: var(--primary-green-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }

        .download-btn {
            background: var(--text-dark);
            color: white;
        }

        .download-btn:hover {
            background: #374151;
            transform: translateY(-1px);
            box-shadow: var(--shadow-medium);
        }

        .empty-state {
            text-align: center;
            padding: 80px 32px;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 24px;
            color: var(--text-light);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-dark);
        }

        .empty-state p {
            font-size: 1rem;
            color: var(--text-gray);
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 48px 0 24px;
            margin-top: 80px;
        }

        .footer-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-green);
        }

        .footer-section p {
            line-height: 1.7;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 400;
        }

        .footer-links a:hover {
            color: var(--primary-green);
        }

        .footer-contact p {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            color: #d1d5db;
        }

        .footer-contact i {
            color: var(--primary-green);
            margin-top: 2px;
            flex-shrink: 0;
        }

        .footer-bottom {
            text-align: center;
            padding: 24px 0 0;
            margin-top: 40px;
            border-top: 1px solid #374151;
            color: #9ca3af;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-top {
                display: none;
            }
            
            .header-main-container {
                flex-direction: column;
                gap: 20px;
                padding: 16px 20px;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 16px;
                width: 100%;
                display: none;
                padding: 20px 0;
                background: var(--background-white);
                border-top: 1px solid var(--border-light);
                margin-top: 20px;
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .mobile-menu-btn {
                display: block;
                position: absolute;
                top: 16px;
                right: 20px;
            }
            
            .main-container {
                padding: 24px 16px;
            }
            
            .page-header {
                padding: 32px 24px;
                margin-bottom: 32px;
            }
            
            .page-header h1 {
                font-size: 2.25rem;
            }
            
            .documents-header {
                padding: 32px 24px;
            }
            
            .documents-header h2 {
                font-size: 1.75rem;
            }
            
            .search-section,
            .documents-grid {
                padding: 24px;
            }
            
            .document-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                padding: 20px;
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
            
            .footer-container {
                grid-template-columns: 1fr;
                gap: 32px;
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
        }
    </style>
</head>
<body class="smn-docs">
    <!-- Main Content -->
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
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search documents by title or description...">
                </div>
            </div>

            <div class="documents-grid">
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
                    <div class="document-card">
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
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-3">SDO General Trias</h5>
                    <p>Empowering education through strategic partnerships <br> with schools, businesses, and the community.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter fs-4"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram fs-4"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.html" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="proj-isshed.html" class="text-white text-decoration-none">Project ISSHED</a></li>
                        <li class="mb-2"><a href="proj-isshed.html#adopt-a-school" class="text-white text-decoration-none">Adopt-a-School</a></li>
                        <li class="mb-2"><a href="taxIncentives.html" class="text-white text-decoration-none">Brigada Eskwela</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>General Trias City, Cavite</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>sdo.gentri@deped.gov.ph</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>+63 46 123 4567</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="mb-3">Office Hours</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-clock me-2"></i>Monday-Friday: 8:00 AM - 5:00 PM</li>
                        <li class="mb-2"><i class="bi bi-clock me-2"></i>Saturday: Closed</li>
                        <li class="mb-2"><i class="bi bi-clock me-2"></i>Sunday: Closed</li>
                        <li class="mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Closed on Holidays</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">© 2025 SDO General Trias. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const documents = document.querySelectorAll('.document-card');
            
            documents.forEach(doc => {
                const title = doc.querySelector('.document-title').textContent.toLowerCase();
                const description = doc.querySelector('.document-description') ? 
                                  doc.querySelector('.document-description').textContent.toLowerCase() : '';
                
                if (title.includes(searchText) || description.includes(searchText)) {
                    doc.style.display = 'flex';
                } else {
                    doc.style.display = 'none';
                }
            });
        });

        // Document card click functionality
        document.querySelectorAll('.document-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('.document-actions')) {
                    const viewBtn = this.querySelector('.view-btn');
                    if (viewBtn) {
                        viewBtn.click();
                    }
                }
            });
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            
            if (navMenu.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Close mobile menu when clicking on links
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.className = 'fas fa-bars';
            });
        });
    </script>
</body>
</html>