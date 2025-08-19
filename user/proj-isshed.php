<?php
// Database configuration - Update with your actual database credentials
$host = 'localhost';
$dbname = 'sdo_gentri';
$username = 'root';
$password = '';


try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all active highlights from database
    $stmt = $pdo->query("SELECT * FROM project_highlights WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
    $highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Now you can use $highlights array containing your data
    
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}

// Separate visible and hidden items
$visibleCount = 2; // Number of items to show initially
$visibleHighlights = array_slice($highlights, 0, $visibleCount);
$hiddenHighlights = array_slice($highlights, $visibleCount);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to fetch timeline data from database
function getTimelineData($pdo) {
    try {
        // Updated query to match the management interface table structure
        $stmt = $pdo->prepare("
            SELECT id, title, description, image_path, status as category, event_date, 
                   display_order, position, is_active, created_at, updated_at,
                   CASE 
                       WHEN status = 'completed' THEN 1 
                       ELSE 0 
                   END as is_featured
            FROM timeline_events 
            WHERE is_active = 1 
            ORDER BY display_order ASC, event_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Return empty array if database query fails
        error_log("Timeline query failed: " . $e->getMessage());
        return [];
    }
}

// Function to get status color class
function getStatusColorClass($status) {
    switch($status) {
        case 'completed':
            return 'border-green-600';
        case 'in-progress':
            return 'border-yellow-500';
        case 'planned':
            return 'border-blue-500';
        default:
            return 'border-gray-400';
    }
}

// Function to get status badge color
function getStatusBadgeColor($status) {
    switch($status) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'in-progress':
            return 'bg-yellow-100 text-yellow-800';
        case 'planned':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Get timeline data
$timelineData = getTimelineData($pdo);

// Handle AJAX request for showing all items
$showAll = isset($_GET['show_all']) && $_GET['show_all'] === 'true';
$displayedItems = $showAll ? $timelineData : array_slice($timelineData, 0, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Project ISSHED</title>
  <link rel="stylesheet" href="projisshed.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous"> <!-- extension for bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- search field -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- nav bar -->
<script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'spin': 'spin 1s linear infinite',
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-in-out'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
 <style>

        .section-header {
            margin-bottom: 3rem;
            position: relative;
        }

        .section-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #800000, #a52a2a);
            border-radius: 2px;
        }

        .lead {
            font-size: 1.2rem;
            color: #6c757d;
            font-weight: 300;
        }

        #gallery {
            padding: 4rem 0;
            background: transparent;
        }

        .container {
            max-width: 1200px;
        }

        .gallery-container {
            position: relative;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .gallery-item-container {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gallery-collapsed .gallery-hidden .gallery-item-container {
            opacity: 0;
            transform: translateY(30px);
            pointer-events: none;
            position: absolute;
            top: 0;
            left: 0;
        }

        .gallery-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            position: relative;
            height: auto;
            min-height: 350px;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .gallery-img-container {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .gallery-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover .gallery-img-container img {
            transform: scale(1.1);
        }

        .gallery-img-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(128, 0, 0, 0.2), rgba(165, 42, 42, 0.2));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .gallery-item:hover .gallery-img-container::before {
            opacity: 1;
        }

        .gallery-caption-container {
            padding: 1.5rem;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .gallery-caption {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
        }

        .gallery-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .gallery-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto;
        }

        .gallery-category {
            background: linear-gradient(135deg, #800000, #a52a2a);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .gallery-date {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 0.4rem 0.8rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(255, 215, 0, 0.4);
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .gallery-hidden {
            display: none;
        }

        .gallery-expanded .gallery-hidden {
            display: contents;
        }

        .gallery-expanded .gallery-hidden .gallery-item-container {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            position: static;
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .gallery-btn-container {
            text-align: center;
            margin-top: 2rem;
        }

        .view-more-gallery {
            background: linear-gradient(135deg, #800000, #a52a2a);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin: 0 auto;
        }

        .view-more-gallery:hover {
            background: linear-gradient(135deg, #a52a2a, #800000);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(128, 0, 0, 0.4);
        }

        .view-more-gallery i {
            transition: transform 0.3s ease;
            font-size: 1.1rem;
        }

        .gallery-expanded .view-more-gallery i {
            transform: rotate(180deg);
        }

        .no-highlights {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .no-highlights i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }

            .gallery-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .view-more-gallery {
                padding: 0.8rem 2rem;
                font-size: 0.9rem;
            }
        }





















        .timeline-dot {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 1.2rem;
            height: 1.2rem;
            background-color: #16a34a;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .timeline-dot.completed {
            background-color: #16a34a;
        }
        
        .timeline-dot.in-progress {
            background-color: #eab308;
        }
        
        .timeline-dot.planned {
            background-color: #3b82f6;
        }
        
        .timeline-line {
            position: absolute;
            left: 50%;
            transform: translateX(-1px);
            height: 100%;
            width: 3px;
            background: linear-gradient(180deg, #16a34a 0%, #dc2626 50%, #3b82f6 100%);
        }
        
        @media (max-width: 768px) {
            .timeline-item {
                flex-direction: column !important;
            }
            .timeline-content {
                width: 100% !important;
                text-align: left !important;
                padding: 0 !important;
                margin-top: 1rem;
                margin-left: 2rem;
            }
            .timeline-line {
                left: 1rem;
            }
            .timeline-dot {
                left: 1rem;
            }
        }

        .timeline-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .timeline-item.animate {
            opacity: 1;
            transform: translateY(0);
        }
 </style>

</head>

<body>
  <header>
      <!-- nav bar -->
      <nav class="navbar navbar-expand-lg fixed-top bg-light shadow-sm">
          <div class="container-fluid">

              <!-- pae title -->
              <div class="navbar-brand d-flex flex-column align-items-start">
              <span class="custom-green fw-bold">SDO General Trias</span>
              <span class="text-muted fs-6">Partnership and Linkages</span>
              </div>

              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGenTri" aria-controls="navbarGenTri" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
              </button>

              <!-- search field -->
              <div class="collapse navbar-collapse justify-content-end" id="navbarGenTri">
              <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">

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
                      <li><a class="dropdown-item" href="proj-isshed.html#be-our-partner">Be Our Partner</a></li>
                      <li><a class="dropdown-item" href="index.html#news-partnership-updates">News & Partnership Updates</a></li>
                      <li><a class="dropdown-item" href="smn-forms.html">SMN Forms</a></li>
                  </ul>
                  </li>
              </ul>
              </div>
          </div>
      </nav>
  </header>


    <!-- Logo Section Below Navbar -->
    <section class="logo-section">
        <div class="container text-center">
            <img src="bg_images\div-tri.png" alt="SDO GenTri Logo" class="logo-img">
        </div>
    </section>

    <!-- Hero Section -->
    <main class="overall-content">
        <section class="hero">
            <div class="container">
                <h1>Project ISSHED</h1>
                <p class="hero-subtitle">Implementing Social Services and Health Education for Sustainable Development</p>
            </div>
            </section>

            <section class="mission-section">
                <div class="container">
                    <div class="mission-header">
                    <span class="mission-tag">ALIGNED WITH DEPED MATATAG AGENDA</span>
                    <h1 class="mission-title">
                        Strengthening Philippine <br> Education Through <br>
                        <span class="text-maroon">Four Instructional Principles (4Is)</span>
                    </h1>
                    <p class="mission-subtitle">
                        Project ISSHEd fully supports DepEd’s transformative MATATAG agenda by aligning its initiatives with efforts to enhance curriculum relevance, improve school facilities, promote learner inclusivity, and uplift teacher welfare for quality basic education. Through these priorities, Project ISSHED creates synergies between health education and sustainable development, supporting DepEd's vision for transformative basic education.
                    </p>
                    </div>
                </div>
            </section>
        </section>

        <!-- Priority Cards -->
        <section class="matatag-section py-5">
            <div class="container">
                <!-- MATATAG Cards - Using your existing structure -->
                <div class="row g-4 justify-content-center">
                    <!-- MA -->
                    <div class="col-md-3">
                        <div class="card border-1 shadow-sm hover-grow h-100">
                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                <div class="card-content">
                                    <div class="icon-maroon-bg mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                        MA
                                    </div>
                                    <h5 class="text-matatag">Make the curriculum relevant to produce competent, job-ready, active, and responsible citizens</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TA -->
                    <div class="col-md-3">
                        <div class="card border-1 shadow-sm hover-grow h-100">
                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                <div class="card-content">
                                    <div class="icon-maroon-bg mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                        TA
                                    </div>
                                    <h5 class="text-matatag">Take steps to accelerate the delivery of basic education facilities and services;</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TA -->
                    <div class="col-md-3">
                        <div class="card border-1 shadow-sm hover-grow h-100">
                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                <div class="card-content">
                                    <div class="icon-maroon-bg mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                        TA
                                    </div>
                                    <h5 class="text-matatag">Take good care of learners by promoting learner well-being, inclusive education, and a positive learning environment</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- G -->
                    <div class="col-md-3">
                        <div class="card border-1 shadow-sm hover-grow h-100">
                            <div class="card-body text-center p-4 d-flex flex-column justify-content-between">
                                <div class="card-content">
                                    <div class="icon-maroon-bg mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                        G
                                    </div>
                                    <h5 class="text-matatag">Give support to teachers to teach better</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about-project-isshed" class="mb-5">
            <div class="container">
                <h2 class="project-header mb-4">About the Project</h2>
                <p class="lead text-green text-justify">
                    The Project ISSHED (Innovating and Strengthening Support through Holistic Engagement of DepEd Partners) is the enabling mechanism of the division to strengthen active collaboration with key stakeholders using the multi-sectoral partners to establish the support to the different PPAs of the division. Aside from the integration to the different PPAs of the division, ISSHED has different sub-projects that can be aligned with the implementation of the different activities to achieve the objective of this project. These sub-projects are the creation of PMET (Partnership Monitoring Tool), Brigada Eskwela, the platform for adopting and helping the division and schools, The SEEdS (Sustaining Engagement for Educators and Stakeholders Day) which is through the PMET Tool for easy-to-identify the level of support and involvement of the stakeholder for the betterment of the learners through the various PPAs.
                </p>

                <!-- Logo with description -->
                <figure class="isshed-logo my-5 text-center position-relative">
                    <img src="bg_images/PROJECT ISSHED_1.png" alt="Project ISSHED Logo" class="custom-logo mb-2 with-tooltip" />
                    <figcaption class="lead text-green text-center">Project ISSHED Logo</figcaption>
                </figure>

                <p class="lead text-green text-justify mt-4">
                    Project ISSHED (Innovating and Strengthening Support through Holistic Engagement of DepEd Partners) will be a platform for the division to develop monitoring, evaluation, and communication mechanisms in the division/school and develop localized guidelines and manual for partnership in collaboration and implementation in the division/schools through the SEPS-SMN, EPS II-SMN, Education Facilities, PSDS, EPS, ITO, Schools Heads, ASP/BE Coordinators, Stakeholders, and other PPAs proponent. This project will evolve in addressing the needs of the division/schools through the eight (8) forms of support through partnerships. Strengthening these projects will have the division sustainable partners and a partnership support tracking system.
                </p>

                <!-- Project Objectives Container -->
                <div class="projectobj-container my-5">
                    <h2 class="text-left mb-4">Project ISSHED Objectives</h2>
                    <div class="objective-item">
                        <h3>Objective 1</h3>
                        <p>Sustained support on different PPAs of the division and on the specific resource in support of the division/school.</p>
                    </div>
                    <div class="objective-item">
                        <h3>Objective 2</h3>
                        <p>Collaborative Strategies with Division/Schools in Implementing PPAs</p>
                    </div>
                    <div class="objective-item">
                        <h3>Objective 3</h3>
                        <p>Strengthened the support of different stakeholders in learning environment and services delivery.</p>
                    </div>
                </div>

                <!-- ISSHED's Project Design -->
                <div class="project-design-wrapper py-5">
                    <div class="container text-center">
                        <h2 class="mb-4 text-maroon fw-bold">Project Design</h2>
                        <p class="text-muted mb-4">A visual representation of the project implementation and key components.</p>
                        <img src="bg_images\ProjectISSHED-Design.png" alt="Project Design" class="img-fluid project-design-img shadow" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Partnership Programs -->
        <section id="partnership-program" class="py-5 bg-white">
            <div class="partnership-header text-center my-5 px-3">
                <h1 class="fw-bold text-black mb-3 display-5" style="font-size: 80px; color: #000;">Our Partnership Programs</h1>
                <div class="header-underline mx-auto mb-3"></div>
                <p class="text-muted mx-auto" style="max-width: 900px;">
                    Discover how strategic collaboration empowers schools, stakeholders, and communities to transform education in General Trias City.
                </p>
            </div>

            <!-- Adopt-A-School Program Section -->
            <section id="adopt-a-school" class="program-section py-5 bg-white">
                <div class="container">
                    <h1 class="program-title">Adopt-A-School Program</h1>
                    <div class="divider-line mb-4"></div>

                    <h2 class="program-heading">What is the Adopt-A-School Program?</h2>
                    <p class="program-text">
                    The Adopt-A-School Program, which started in 1998 through Republic Act 8525, was created to help generate investments and support for education beyond mainstream funding. It enables the private sector to collaborate with the government by providing assistance in areas like infrastructure, health and nutrition, teacher training, equipment, and learning materials.
                    </p>

                    <h2 class="program-heading">Who Can Be An ASP Partner?</h2>
                    <ul class="program-list list-group list-group-flush shadow-sm mb-5">
                    <li class="list-group-item bg-white">Any private individual, group, or organization with credible track record can be an ASP partner.</li>
                    <li class="list-group-item bg-white">Must be registered with Securities and Exchange Commission (SEC), with the Cooperative Development Authority (CDA), or Department of Trade and Industry (DTI).</li>
                    <li class="list-group-item bg-white">Must not have been prosecuted or found guilty of engaging in any illegal activities or of being involved in the tobacco industry.</li>
                    <li class="list-group-item bg-white">Must share the trust and values promoted by DepEd.</li>
                    <li class="list-group-item bg-white">Any individual with great intention of helping the school community to deliver basic education through its support and donations.</li>
                    </ul>

                    <h2 class="program-heading">Benefits of Being an ASP Partner</h2>
                    <p class="program-text mb-4">
                    Partnership, through ASP, provides mutual benefits between the DepEd and the adopting private entity.
                    The DepEd schools improved through the support outsourced from the adopting entity. In return,
                    the adopting entity may avail of the following benefits​.
                    </p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <ul class="program-list list-group list-group-flush shadow-sm">
                            <li class="list-group-item bg-white">150% tax deduction from gross income.</li>
                            <li class="list-group-item bg-white">Exemption from Donors’ Tax.</li>
                            <li class="list-group-item bg-white">Duty/tax-free importation for foreign donations.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="program-list list-group list-group-flush shadow-sm">
                            <li class="list-group-item bg-white">Recognition in adopted schools.</li>
                            <li class="list-group-item bg-white">Enhanced corporate image and goodwill within the school community.</li>
                            <li class="list-group-item bg-white">Recognition as partners during the Education Summit.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Brigada Eskwela Section -->
            <section id="brigada-eskwela" class="program-section py-5 bg-white">
                <div class="container">
                    <h1 class="program-title">Brigada Eskwela</h1>
                    <div class="divider-line mb-4"></div>

                    <h2 class="program-heading">What is Brigada Eskwela?</h2>
                    <p class="program-text">
                    Brigada Eskwela is a nationwide program in the Philippines that mobilizes communities to prepare public schools for the opening of classes. Since 2003, it has encouraged volunteerism and support through facility repairs, donations, and improving school environments.
                    </p>

                    <h2 class="program-heading">What Can We Contribute to Brigada Eskwela?</h2>
                    <ul class="program-list list-group list-group-flush shadow-sm mb-5">
                    <li class="list-group-item bg-white">Support the schools by addressing school needs and gaps.</li>
                    <li class="list-group-item bg-white">Clean school grounds and facilities like canteens and clinics.</li>
                    <li class="list-group-item bg-white">Repair and repaint classrooms, wash areas, chairs, and windows.</li>
                    <li class="list-group-item bg-white">Donate school supplies, hygiene kits, first aid kits, and materials.</li>
                    </ul>

                    <h2 class="program-heading">Benefits of Being a Brigada Eskwela Partner</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <ul class="program-list list-group list-group-flush shadow-sm">
                            <li class="list-group-item bg-white">150% tax deduction from gross income.</li>
                            <li class="list-group-item bg-white">Exemption from Donors’ Tax.</li>
                            <li class="list-group-item bg-white">Duty/tax-free importation for foreign donations.</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="program-list list-group list-group-flush shadow-sm">
                            <li class="list-group-item bg-white">Recognition in adopted schools.</li>
                            <li class="list-group-item bg-white">Enhanced corporate image and goodwill within the school community.</li>
                            <li class="list-group-item bg-white">Recognition as partners during the Education Summit.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </section>

<div class="min-h-screen py-12 px-4 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <header class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                Project ISSHED Timeline
            </h1>
            <div class="w-32 h-1 bg-gradient-to-r from-green-600 to-blue-600 mx-auto mb-6"></div>
            <p class="text-gray-600 text-lg md:text-xl max-w-2xl mx-auto">
                Documenting our journey from inception to achievement - every milestone, every breakthrough, every step forward
            </p>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mt-8 max-w-2xl mx-auto">
                <?php 
                $statusCounts = [
                    'completed' => count(array_filter($timelineData, fn($item) => $item['category'] === 'completed')),
                    'in-progress' => count(array_filter($timelineData, fn($item) => $item['category'] === 'in-progress')),
                    'planned' => count(array_filter($timelineData, fn($item) => $item['category'] === 'planned'))
                ];
                
                $statusCards = [
                    [
                        'count' => $statusCounts['completed'],
                        'label' => 'Completed',
                        'color' => 'green',
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                    ],
                    [
                        'count' => $statusCounts['in-progress'],
                        'label' => 'In Progress',
                        'color' => 'yellow',
                        'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                    ],
                    [
                        'count' => $statusCounts['planned'],
                        'label' => 'Planned',
                        'color' => 'blue',
                        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
                    ]
                ];
                
                foreach ($statusCards as $card): 
                ?>
                <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-full bg-<?php echo $card['color']; ?>-100">
                            <svg class="w-6 h-6 text-<?php echo $card['color']; ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $card['icon']; ?>"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-<?php echo $card['color']; ?>-600"><?php echo $card['count']; ?></div>
                            <div class="text-sm text-gray-600"><?php echo $card['label']; ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </header>

        <?php if (empty($timelineData)): ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                <div class="text-6xl text-gray-300 mb-4" aria-hidden="true">📅</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Timeline Events Yet</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">Timeline events will appear here once they are added through the management interface.</p>
                <a href="manage_timeline.php" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add First Event
                </a>
            </div>
        <?php else: ?>
            <!-- Timeline Container -->
            <div class="relative">
                <!-- Timeline Line -->
                <div class="absolute left-1/2 w-1 h-full bg-gradient-to-b from-green-500 to-blue-500 transform -translate-x-1/2" aria-hidden="true"></div>

                <!-- Timeline Items -->
                <div class="space-y-16">
                    <?php foreach ($displayedItems as $index => $item): 
                        $position = isset($item['position']) ? $item['position'] : ($index % 2 === 0 ? 'right' : 'left');
                        $isRight = $position === 'right';
                    ?>
                    <div class="relative timeline-item group" data-index="<?php echo $index; ?>">
                        <!-- Timeline Dot -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full border-4 border-white bg-<?php echo getStatusColor($item['category'], false); ?>-500 shadow-md z-10" aria-hidden="true"></div>

                        <!-- Content Container -->
                        <div class="timeline-item-container flex flex-col md:flex-row items-center <?php echo $isRight ? 'md:flex-row-reverse' : ''; ?>">
                            <!-- Content Card -->
                            <div class="w-full md:w-5/12 <?php echo $isRight ? 'md:pr-8 md:text-right' : 'md:pl-8 md:text-left'; ?> mt-8 md:mt-0">
                                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl p-6 border-l-4 border-<?php echo getStatusColor($item['category'], false); ?>-500 transition-all duration-300 hover:-translate-y-1">
                                    <!-- Date and Featured Badge -->
                                    <div class="flex items-center gap-3 mb-4 <?php echo $isRight ? 'justify-end' : 'justify-start'; ?>">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-green-600 font-semibold text-sm">
                                            <?php echo formatDate($item['event_date']); ?>
                                        </span>
                                        <?php if ($item['is_featured']): ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full font-medium">
                                            ⭐ Featured
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-tight">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>

                                    <!-- Image -->
                                    <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                                    <div class="mb-4 overflow-hidden rounded-lg">
                                        <img 
                                            src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                            alt="<?php echo htmlspecialchars($item['title']); ?>"
                                            class="w-full h-48 md:h-56 object-cover hover:scale-105 transition-transform duration-500"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='assets/images/timeline-placeholder.jpg'"
                                        />
                                    </div>
                                    <?php endif; ?>

                                    <!-- Description -->
                                    <div class="text-gray-600 mb-4 leading-relaxed text-base prose max-w-none">
                                        <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="flex <?php echo $isRight ? 'justify-end' : 'justify-start'; ?>">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-<?php echo getStatusColor($item['category'], false); ?>-100 text-<?php echo getStatusColor($item['category'], false); ?>-800">
                                            <?php 
                                            $statusIcons = [
                                                'completed' => '✅',
                                                'in-progress' => '🔄',
                                                'planned' => '📋'
                                            ];
                                            echo ($statusIcons[$item['category']] ?? '') . ' ' . ucfirst(str_replace('-', ' ', $item['category']));
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Spacer for desktop -->
                            <div class="hidden md:block w-2/12"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- View All Button -->
            <?php if (count($timelineData) > 2): ?>
            <div class="text-center mt-16">
                <button 
                    onclick="toggleTimeline()"
                    id="toggleButton"
                    aria-expanded="<?php echo $showAll ? 'true' : 'false'; ?>"
                    aria-controls="timelineItems"
                    class="inline-flex items-center gap-3 bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-6 py-3 md:px-8 md:py-4 rounded-lg md:rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <?php if ($showAll): ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        Show Less
                    <?php else: ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        View All Timeline (<?php echo count($timelineData); ?> events)
                    <?php endif; ?>
                </button>
            </div>
             <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
// Helper functions would typically be in a separate file
function getStatusColor($status, $includePrefix = true) {
    $prefix = $includePrefix ? 'text-' : '';
    return match($status) {
        'completed' => $prefix . 'green',
        'in-progress' => $prefix . 'yellow',
        'planned' => $prefix . 'blue',
        default => $prefix . 'gray'
    };
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M j, Y');
}
?>

<script>
function toggleTimeline() {
    const container = document.querySelector('.relative'); // Your timeline container
    const button = document.getElementById('toggleButton');
    const allItems = document.querySelectorAll('.timeline-item');
    
    if (container.classList.contains('expanded')) {
        // Collapse - hide items after first 2
        container.classList.remove('expanded');
        button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> View All Timeline (' + allItems.length + ' events)';
        
        allItems.forEach((item, index) => {
            if (index >= 2) {
                item.classList.add('hidden');
            }
        });
    } else {
        // Expand - show all items
        container.classList.add('expanded');
        button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> Show Less';
        
        allItems.forEach(item => {
            item.classList.remove('hidden');
        });
        
        // Animate newly shown items
        animateTimelineItems();
    }
}

function animateTimelineItems() {
    const hiddenItems = document.querySelectorAll('.timeline-item.hidden');
    hiddenItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.remove('hidden');
            item.classList.add('animate');
        }, index * 150);
    });
}
</script>     

<!-- Project Highlights Section -->
    <section id="gallery">
        <div class="section-header text-center">
            <h2>Project Highlights</h2>
            <p class="lead">Moments that inspire us to keep going and achieve greatness.</p>
        </div>

        <div class="container">
            <?php if (empty($highlights)): ?>
                <div class="no-highlights">
                    <i class="bi bi-images"></i>
                    <h4>No Highlights Available</h4>
                    <p>Check back later for exciting updates from Project ISSHED.</p>
                </div>
            <?php else: ?>
                <div class="gallery-container gallery-collapsed" id="galleryContainer">
                    <div class="gallery-grid" id="galleryGrid">
                        
                        <!-- Visible Items -->
                        <?php foreach ($visibleHighlights as $highlight): ?>
                            <div class="gallery-item-container">
                                <div class="gallery-item" onclick="showHighlightModal(<?php echo $highlight['id']; ?>)">
                                    <?php if ($highlight['is_featured']): ?>
                                        <div class="featured-badge">
                                            <i class="bi bi-star-fill"></i>
                                            Featured
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="gallery-img-container">
                                        <img src="<?php echo htmlspecialchars($highlight['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($highlight['title']); ?>"
                                             loading="lazy"
                                             onerror="this.src='https://via.placeholder.com/400x220?text=Image+Not+Found'">
                                    </div>
                                    
                                    <div class="gallery-caption-container">
                                        <h3 class="gallery-caption"><?php echo htmlspecialchars($highlight['title']); ?></h3>
                                        
                                        <?php if (!empty($highlight['description'])): ?>
                                            <p class="gallery-description"><?php echo htmlspecialchars($highlight['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="gallery-meta">
                                            <?php if (!empty($highlight['category'])): ?>
                                                <span class="gallery-category"><?php echo htmlspecialchars($highlight['category']); ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($highlight['event_date'])): ?>
                                                <span class="gallery-date">
                                                    <i class="bi bi-calendar-event"></i>
                                                    <?php echo date('M j, Y', strtotime($highlight['event_date'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Hidden Items -->
                        <?php if (!empty($hiddenHighlights)): ?>
                            <div class="gallery-hidden">
                                <?php foreach ($hiddenHighlights as $highlight): ?>
                                    <div class="gallery-item-container">
                                        <div class="gallery-item" onclick="showHighlightModal(<?php echo $highlight['id']; ?>)">
                                            <?php if ($highlight['is_featured']): ?>
                                                <div class="featured-badge">
                                                    <i class="bi bi-star-fill"></i>
                                                    Featured
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="gallery-img-container">
                                                <img src="<?php echo htmlspecialchars($highlight['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($highlight['title']); ?>"
                                                     loading="lazy"
                                                     onerror="this.src='https://via.placeholder.com/400x220?text=Image+Not+Found'">
                                            </div>
                                            
                                            <div class="gallery-caption-container">
                                                <h3 class="gallery-caption"><?php echo htmlspecialchars($highlight['title']); ?></h3>
                                                
                                                <?php if (!empty($highlight['description'])): ?>
                                                    <p class="gallery-description"><?php echo htmlspecialchars($highlight['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="gallery-meta">
                                                    <?php if (!empty($highlight['category'])): ?>
                                                        <span class="gallery-category"><?php echo htmlspecialchars($highlight['category']); ?></span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($highlight['event_date'])): ?>
                                                        <span class="gallery-date">
                                                            <i class="bi bi-calendar-event"></i>
                                                            <?php echo date('M j, Y', strtotime($highlight['event_date'])); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($highlights) > $visibleCount): ?>
                    <div class="gallery-btn-container">
                        <button class="view-more-gallery" onclick="toggleGallery()">
                            <i class="bi bi-chevron-down"></i>
                            <span id="viewMoreText">View More Highlights</span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal for Highlight Details -->
    <div class="modal fade" id="highlightModal" tabindex="-1" aria-labelledby="highlightModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="highlightModalLabel">Highlight Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Get in touch with us -->
        <section id="be-our-partner" class="py-5" style="background-color: #f5f5f5;">
            <div class="container">
                <!-- Section Header -->
                <div class="text-center mb-5">
                    <h2 class="display-4 fw-bold" style="color: #000000;">Our Partnership Team</h2>
                    <p class="lead mx-auto" style="max-width: 700px; font-size: 1.4rem; color: #000000;">
                        Direct contacts for Education Collaboration Initiatives, <br> Partnerships and even Tax Incentives!
                    </p>
                </div>

                <!-- Staff Cards -->
                <div class="row g-4 justify-content-center">
                    <!-- Staff Card 1 -->
                    <div class="col-xl-10">
                        <div class="card border-0 shadow-lg overflow-hidden" style="border-left: 5px solid #006400;">
                            <div class="row g-0">
                                <!-- Image Column -->
                                <div class="col-md-5">
                                    <img src="bg_images/pic-leah-anne-misenas.png" class="img-fluid h-100 object-fit-cover" alt="Leah Anne A. Misenas" style="min-height: 350px;">
                                </div>
                                <!-- Content Column -->
                                <div class="col-md-7 p-4 p-lg-5 bg-white">
                                    <div class="card-body h-100 d-flex flex-column">
                                        <div>
                                            <h3 class="fw-bold mb-2" style="color: #000;">Leah Anne A. Misenas, EdD.</h3>
                                            <span class="badge mb-3" style="background-color: #006400; color: white; font-size: 1rem;">Senior Education Program Specialist</span>
                                            
                                            <!-- Contact Information Block -->
                                            <div class="mb-4 p-3 rounded" style="background-color: #f8f8f8; border-left: 3px solid #006400;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-envelope-fill me-2" style="color: #006400;"></i>
                                                    <strong style="color: #000;">Email:</strong>
                                                    <span class="ms-2" style="color: #555;">leahannemisenas@gmail.com</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-telephone-fill me-2" style="color: #006400;"></i>
                                                    <strong style="color: #000;">Contact:</strong>
                                                    <span class="ms-2" style="color: #555;">09675746670</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock-fill me-2" style="color: #006400;"></i>
                                                    <strong style="color: #000;">Response Time:</strong>
                                                    <span class="ms-2" style="color: #555;">Within 24 hours</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text mb-4 flex-grow-1" style="color: #333; font-size: 1.1rem;">
                                            Provides technical support in strengthening and sustaining relationships and collaboration of education partners and stakeholders, and mobilizing resources; and providing technical assistance to support special programs and projects towards increasing access to and enhancing the delivery of quality basic education 
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Card 2 -->
                    <div class="col-xl-10 mt-4">
                        <div class="card border-0 shadow-lg overflow-hidden" style="border-left: 5px solid #800000;">
                            <div class="row g-0">
                                <!-- Image Column -->
                                <div class="col-md-5">
                                    <img src="bg_images/pic-rencie-majillo.png" class="img-fluid h-100 object-fit-cover" alt="Rencie O. Majillo" style="min-height: 350px;">
                                </div>
                                <!-- Content Column -->
                                <div class="col-md-7 p-4 p-lg-5 bg-white">
                                    <div class="card-body h-100 d-flex flex-column">
                                        <div>
                                            <h3 class="fw-bold mb-2" style="color: #000;">Rencie O. Majillo</h3>
                                            <span class="badge mb-3" style="background-color: #800000; color: white; font-size: 1rem;">Education Program Specialist II</span>
                                            
                                            <!-- Contact Information Block -->
                                            <div class="mb-4 p-3 rounded" style="background-color: #f8f8f8; border-left: 3px solid #800000;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-envelope-fill me-2" style="color: #800000;"></i>
                                                    <strong style="color: #000;">Email</strong>
                                                    <span class="ms-2" style="color: #555;">renciemajillo@gmail.com</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-telephone-fill me-2" style="color: #800000;"></i>
                                                    <strong style="color: #000;">Phone</strong>
                                                    <span class="ms-2" style="color: #555;"> 09360544084</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock-fill me-2" style="color: #800000;"></i>
                                                    <strong style="color: #000;">Best Reach Hours:</strong>
                                                    <span class="ms-2" style="color: #555;">9:00 AM - 3:00 PM Weekdays</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text mb-4 flex-grow-1" style="color: #333; font-size: 1.1rem;">
                                            Assists in providing technical support to strengthening partnerships with both internal and external educational stakeholders and respond to the needs of the schools and learning centers for the resources and capacity to implement sustainable programs and projects to enhance the delivery of quality basic education. 
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section py-5">
            <div class="container">
                <div class="row">
                    <!-- Left Side (Original Contact Info - UNCHANGED) -->
                    <div class="col-lg-6">
                        <h1 class="mb-4" styles="font-size: 3rem;">Get in Touch</h1>
                        <p class="reach" styles="font-size: 1.5rem">Reach out for partnerships, inquiries, or support from our team.</p>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-geo-alt-fill me-3 fs-4 text-primary"></i>
                            <div>
                                <h5>Address</h5>
                                <p>General Trias City, Cavite</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-envelope-fill me-3 fs-4 text-primary"></i>
                            <div>
                                <h5>Email</h5>
                                <p>division.gentri@deped.gov.ph</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-telephone-fill me-3 fs-4 text-primary"></i>
                            <div>
                                <h5>Phone</h5>
                                <p>(046) 509 1167<br>(046) 431 4275</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side (Replaced with Google Map) -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-0">
                                <!-- Embedded Google Map - DepEd General Trias Location -->
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3864.328623763576!2d120.88078531532637!3d14.383985789948633!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33962c9650e12a91%3A0x5d5c8988d126f0a5!2sDepEd%20Schools%20Division%20Office%20-%20General%20Trias%20City!5e0!3m2!1sen!2sph!4v1621234567890!5m2!1sen!2sph" 
                                    width="100%" 
                                    height="400" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                                </iframe>
                                
                                <!-- Map Footer -->
                                <div class="p-3 bg-light">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-fill text-danger fs-5 me-2"></i>
                                        <div>
                                            <small class="fw-bold">DepEd General Trias Location</small><br>
                                            <small class="text-muted">City Hall Compound, Governor's Drive, General Trias, Cavite</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="footer-container">
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

        const highlights = <?php echo json_encode($highlights); ?>;

        function toggleGallery() {
            const container = document.getElementById('galleryContainer');
            const viewMoreText = document.getElementById('viewMoreText');
            const chevron = document.querySelector('.view-more-gallery i');
            
            if (container.classList.contains('gallery-collapsed')) {
                container.classList.remove('gallery-collapsed');
                container.classList.add('gallery-expanded');
                viewMoreText.textContent = 'View Less Highlights';
            } else {
                container.classList.remove('gallery-expanded');
                container.classList.add('gallery-collapsed');
                viewMoreText.textContent = 'View More Highlights';
                
                // Scroll back to gallery section
                document.getElementById('gallery').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function showHighlightModal(id) {
            const highlight = highlights.find(h => h.id == id);
            if (!highlight) return;

            const modalContent = document.getElementById('modalContent');
            const modalLabel = document.getElementById('highlightModalLabel');
            
            modalLabel.textContent = highlight.title;
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="${highlight.image_path}" 
                             alt="${highlight.title}" 
                             class="img-fluid rounded"
                             onerror="this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'">
                    </div>
                    <div class="col-md-6">
                        <div class="highlight-details">
                            <h5 class="mb-3">${highlight.title}</h5>
                            
                            ${highlight.is_featured ? '<div class="mb-2"><span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured Highlight</span></div>' : ''}
                            
                            ${highlight.category ? `<div class="mb-2"><strong>Category:</strong> <span class="badge bg-primary">${highlight.category}</span></div>` : ''}
                            
                            ${highlight.event_date ? `<div class="mb-2"><strong>Date:</strong> <i class="bi bi-calendar-event"></i> ${new Date(highlight.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>` : ''}
                            
                            ${highlight.description ? `<div class="mt-3"><strong>Description:</strong><p class="mt-2 text-muted">${highlight.description}</p></div>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('highlightModal'));
            modal.show();
        }

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Animate gallery items on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all gallery items
            document.querySelectorAll('.gallery-item-container').forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(item);
            });
        });























        function toggleTimeline() {
            const currentUrl = new URL(window.location);
            const showAll = currentUrl.searchParams.get('show_all') === 'true';
            
            if (showAll) {
                currentUrl.searchParams.delete('show_all');
            } else {
                currentUrl.searchParams.set('show_all', 'true');
            }
            
            // Show loading state
            const button = document.getElementById('toggleButton');
            const originalContent = button.innerHTML;
            button.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Loading...';
            button.disabled = true;
            
            // Redirect to new URL
            window.location.href = currentUrl.toString();
        }

        // Enhanced scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate timeline items on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('animate');
                        }, index * 150); // Staggered animation
                    }
                });
            }, observerOptions);

            // Apply animation to timeline items
            document.querySelectorAll('.timeline-item').forEach(item => {
                observer.observe(item);
            });

            // Add hover effects to cards
            document.querySelectorAll('.timeline-content .bg-white').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-4px)';
                });
            });
        });

        // Add some interactive feedback
        document.addEventListener('click', function(e) {
            if (e.target.matches('img')) {
                // Simple lightbox effect for images
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4';
                overlay.onclick = () => overlay.remove();
                
                const img = document.createElement('img');
                img.src = e.target.src;
                img.className = 'max-w-full max-h-full rounded-lg shadow-2xl';
                
                overlay.appendChild(img);
                document.body.appendChild(overlay);
            }
        });
    </script>


</body>
</html>
