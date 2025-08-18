<?php
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get active statistics ordered by display_order
$query = "SELECT * FROM statistics WHERE is_active = 1 ORDER BY display_order ASC LIMIT 4";
$stmt = $conn->prepare($query);
$stmt->execute();
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active stories ordered by date (newest first)
$query = "SELECT * FROM impact_stories WHERE status = 'active' ORDER BY story_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active partners grouped by category
$query = "SELECT * FROM partners WHERE status = 'active' ORDER BY category, sort_order, name";
$stmt = $conn->prepare($query);
$stmt->execute();
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group partners by category
$partnerCategories = [
    'sustained' => [],
    'individual' => [],
    'strengthened' => [],
    'other' => []
];

foreach ($partners as $partner) {
    $partnerCategories[$partner['category']][] = $partner;
}

// Category titles
$categoryTitles = [
    'sustained' => 'Sustained Partners',
    'individual' => 'Individual Partners',
    'strengthened' => 'Strengthened Partners',
    'other' => 'Other Private Partners'
];


// Get all news items for display
$query = "SELECT * FROM news_updates 
          ORDER BY is_featured DESC, sort_order, news_date DESC 
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);







?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SDO GenTri Partnerships and Linkages</title>
  <link rel="stylesheet" href="index.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous"> <!-- extension for bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- search field -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap Bundle with Popper (Required for dropdowns to work) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
                <a class="nav-link fw-bold" href="proj-isshed.html#adopt-a-school">Adopt-a-School</a>
                </li>
                <li class="nav-item">
                <a class="nav-link fw-bold" href="proj-isshed.html#brigada-eskwela">Brigada Eskwela</a>
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
          <img src="bg_images\sdologo.png" alt="SDO GenTri Logo" class="logo-img">
      </div>
  </section>

  <!-- top title section -->
  <div class="top-title">
    <div class="container-title">
      <h3 class="title"><b>Fostering Educational<br>Excellence Through<br>Strategic Partnerships</b> </h3>
      <p>The Schools Division Office of General Trias City enhances quality education<br> by building strong, sustainable partnerships with schools, businesses, and <br> the community to provide meaningful learning and support for student success.</p>
    </div>
  </div>

  <!-- status boxes -->
  <section class="stats-section">
    <?php foreach ($statistics as $stat): ?>
    <div class="stat-card">
        <div class="stat-number"><?= htmlspecialchars($stat['stat_number']) ?></div>
        <h3 class="stat-title"><?= htmlspecialchars($stat['stat_title']) ?></h3>
        <p class="stat-desc"><?= htmlspecialchars($stat['stat_description']) ?></p>
    </div>
    <?php endforeach; ?>
</section>

  <!-- partnership program section -->
  <section class="py-5 bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h1 class="h1 fw-bold text-dark mb-3" style="font-size: 4rem;">Our Partnership Programs</h1>
        <div class="mx-auto mb-3" style="width: 200px; height: 4px; background-color: #006400;"></div>
        <p class="text-muted mx-auto" style="max-width: 600px; font-size: 1.2rem;">
          Discover how strategic collaboration can transform education in General Trias City
        </p>
      </div>

      <!-- Center the cards -->
      <div class="row justify-content-center g-4">
        <div class="col-md-6 col-lg-4">
          <div class="card h-100">
            <img src="https://placehold.co/600x400?text=Project+ISSHED" class="card-img-top" alt="Students in a modern classroom with digital learning tools">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-success fw-bold">Project ISSHED</h5>
              <p class="card-text text-muted flex-grow-1">
                Infrastructure Support for School and Home Education Development
              </p>
              <a href="proj-isshed.html#about-project-isshed" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
                Learn More
                <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="card h-100">
            <img src="https://placehold.co/600x400?text=Adopt-a-School" class="card-img-top" alt="Volunteer painting school walls during community service day">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-success fw-bold">Adopt-a-School</h5>
              <p class="card-text text-muted flex-grow-1">
                Private sector partnership for infrastructure and capacity building
              </p>
              <a href="proj-isshed.html#adopt-a-school" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
                Learn More
                <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="card h-100">
            <img src="bg_images/Brigada Eskwela2024 _077.jpg" class="card-img-top" alt="Community volunteers cleaning school grounds before school opening">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-success fw-bold">Brigada Eskwela</h5>
              <p class="card-text text-muted flex-grow-1">
                Annual school maintenance program through community involvement
              </p>
              <a href="proj-isshed.html#brigada-eskwela" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
                Learn More
                <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                  <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Impact Stories -->
  <section class="py-5 bg-white">
    <div class="container">
        <!-- Heading -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-3" style="font-size: 4rem;">Impact Stories</h2>
            <div class="mx-auto mb-3" style="width: 150px; height: 4px; background-color: #006400;"></div>
            <p class="text-muted mx-auto" style="max-width: 600px; font-size: 1.2rem;">
                See how our partnerships are transforming education in General Trias City
            </p>
        </div>

        <!-- First Row (Visible by Default) -->
        <div class="row g-4 mb-4" id="visible-stories">
            <?php 
            $visibleStories = array_slice($stories, 0, 3);
            foreach ($visibleStories as $story): 
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($story['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($story['title']) ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-light me-2" style="color: #006400;"><?= htmlspecialchars($story['category']) ?></span>
                            <small class="story-date"><?= date('M j, Y', strtotime($story['story_date'])) ?></small>
                        </div>
                        <h5 class="card-title story-title"><?= htmlspecialchars($story['title']) ?></h5>
                        <p class="card-text story-text">
                            <?= htmlspecialchars($story['excerpt']) ?>
                        </p>
                       <a href="<?= htmlspecialchars($story['full_story']) ?>" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;" target="_blank">
                          Read Full Story
                          <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                          </svg>
                      </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Additional Hidden Stories -->
        <?php if (count($stories) > 3): ?>
        <div class="row g-4 mb-4 d-none" id="hidden-stories">
            <?php 
            $hiddenStories = array_slice($stories, 3);
            foreach ($hiddenStories as $story): 
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($story['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($story['title']) ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-light me-2" style="color: #006400;"><?= htmlspecialchars($story['category']) ?></span>
                            <small class="story-date"><?= date('M j, Y', strtotime($story['story_date'])) ?></small>
                        </div>
                        <h5 class="card-title story-title"><?= htmlspecialchars($story['title']) ?></h5>
                        <p class="card-text story-text">
                            <?= htmlspecialchars($story['excerpt']) ?>
                        </p>
                        <a href="story-details.php?id=<?= $story['id'] ?>" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
                            Read Full Story
                            <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Toggle Button -->
        <?php if (count($stories) > 3): ?>
        <div class="text-center mt-4">
            <button id="viewAllStoriesBtn" class="btn btn-outline-success">
                View All Stories <i class="fas fa-chevron-down ms-2"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

  <script src="impact.js"></script>

  <!-- Our Valued Partners -->
<section class="py-5 bg-light">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="partner-title" style="font-size: 4rem;">Our Valued Partners</h2>
            <div class="section-divider mx-auto mb-3" style="width: 150px; height: 4px; background-color: #006400;"></div>
            <p class="text-muted mx-auto" style="font-size: 1.5rem; max-width: 600px;">
                We appreciate the support of these organizations in advancing quality education in General Trias City
            </p>
        </div>
        
        <div class="mb-5">
            <?php 
            $showMoreButton = false;
            $visibleCategories = ['sustained', 'individual'];
            $hiddenCategories = ['strengthened', 'other'];
            
            // Display visible categories
            foreach ($visibleCategories as $category): 
                if (!empty($partnerCategories[$category])): ?>
                    <h3 class="partner-category mb-4"><?= $categoryTitles[$category] ?></h3>
                    <div class="row g-4 justify-content-center mb-4">
                        <?php foreach ($partnerCategories[$category] as $partner): ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                                <div class="partner-box text-center">
                                    <?php if (!empty($partner['website'])): ?>
                                        <a href="<?= htmlspecialchars($partner['website']) ?>" target="_blank" class="d-block">
                                            <img src="<?= htmlspecialchars($partner['logo_url']) ?>" 
                                                 alt="<?= htmlspecialchars($partner['name']) ?>" 
                                                 class="img-fluid partner-logo" style="max-height: 100px; width: auto;">
                                            <span class="visually-hidden"><?= htmlspecialchars($partner['name']) ?></span>
                                        </a>
                                    <?php else: ?>
                                        <div class="d-block">
                                            <img src="<?= htmlspecialchars($partner['logo_url']) ?>" 
                                                 alt="<?= htmlspecialchars($partner['name']) ?>" 
                                                 class="img-fluid partner-logo" style="max-height: 100px; width: auto;">
                                            <span class="visually-hidden"><?= htmlspecialchars($partner['name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
            endforeach; 
            
            // Check if we have hidden partners to show
            $hasHiddenPartners = false;
            foreach ($hiddenCategories as $category) {
                if (!empty($partnerCategories[$category])) {
                    $hasHiddenPartners = true;
                    break;
                }
            }
            
            if ($hasHiddenPartners): ?>
                <!-- Hidden Partners (Initially Hidden) -->
                <div class="additional-partners d-none" id="morePartners">
                    <?php foreach ($hiddenCategories as $category): 
                        if (!empty($partnerCategories[$category])): ?>
                            <h3 class="partner-category mb-4"><?= $categoryTitles[$category] ?></h3>
                            <div class="row g-4 justify-content-center mb-4">
                                <?php foreach ($partnerCategories[$category] as $partner): ?>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                                        <div class="partner-box text-center">
                                            <?php if (!empty($partner['website'])): ?>
                                                <a href="<?= htmlspecialchars($partner['website']) ?>" target="_blank" class="d-block">
                                                    <img src="<?= htmlspecialchars($partner['logo_url']) ?>" 
                                                         alt="<?= htmlspecialchars($partner['name']) ?>" 
                                                         class="img-fluid partner-logo" style="max-height: 100px; width: auto;">
                                                    <span class="visually-hidden"><?= htmlspecialchars($partner['name']) ?></span>
                                                </a>
                                            <?php else: ?>
                                                <div class="d-block">
                                                    <img src="<?= htmlspecialchars($partner['logo_url']) ?>" 
                                                         alt="<?= htmlspecialchars($partner['name']) ?>" 
                                                         class="img-fluid partner-logo" style="max-height: 100px; width: auto;">
                                                    <span class="visually-hidden"><?= htmlspecialchars($partner['name']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>

                <!-- View All / View Less Button -->
                <div class="text-center mt-4">
                    <button id="view-all-btn" class="btn btn-outline-success">
                        View All <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
  <script src="partners.js"></script>


<!-- News & Partnership Carousel -->
<section id="news-partnership-updates" class="news-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="mb-5 text-center">
                    <h2 class="section-title">News & Partnership Updates</h2>
                    <p class="lead text-muted">Latest developments and collaborations in our educational initiatives</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="carousel-container">
                    <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php 
                            // Include database connection
                            require_once '../config/database.php';
                            $db = new Database();
                            $conn = $db->getConnection();
                            
                            // Get featured news items for the carousel
                           $query = "SELECT * FROM news_updates 
                            ORDER BY is_featured DESC, sort_order, news_date DESC 
                            LIMIT 5";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Debug: Check if we have news items
                            // echo "<!-- Debug: Found " . count($newsItems) . " news items -->";
                            
                            if (count($newsItems) > 0) {
                                $firstItem = true;
                                foreach ($newsItems as $item): 
                                    $badgeClass = 'badge-' . $item['category'];
                                    $categoryName = ucfirst(str_replace('_', ' ', $item['category']));
                                    
                                    // Debug: Check image path
                                    // echo "<!-- Debug: Image URL: " . $item['image_url'] . " -->";
                            ?>
                            <!-- Slide -->
                            <div class="carousel-item <?= $firstItem ? 'active' : '' ?>">
                                <?php 
                                // Check if image exists and construct proper path
                                $imagePath = $item['image_url'];
                                
                                // If the path doesn't start with http or /, assume it's relative
                                if (!preg_match('/^(https?:\/\/|\/)/i', $imagePath)) {
                                    $imagePath = '../' . ltrim($imagePath, './');
                                }
                                
                                // Alternative: Use a default image if the file doesn't exist
                                $defaultImage = '../assets/images/default-news.jpg';
                                if (!file_exists(str_replace('../', '', $imagePath)) && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                    $imagePath = $defaultImage;
                                }
                                ?>
                                
                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                    class="d-block w-100 carousel-image" 
                                    alt="<?= htmlspecialchars($item['title']) ?>"
                                    onerror="this.src='<?= $defaultImage ?>'; console.log('Image failed to load: <?= htmlspecialchars($imagePath) ?>');">
                                
                                <div class="carousel-caption-container">
                                    <div class="carousel-overlay"></div>
                                    <div class="carousel-content">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="news-badge <?= $badgeClass ?>"><?= $categoryName ?></span>
                                            <span class="news-date">
                                                <i class="far fa-calendar me-1"></i> 
                                                <?= date('F j, Y', strtotime($item['news_date'])) ?>
                                            </span>
                                        </div>
                                        <h3 class="news-title"><?= htmlspecialchars($item['title']) ?></h3>
                                        <p class="news-excerpt">
                                            <?= htmlspecialchars(substr($item['excerpt'], 0, 150)) ?><?= strlen($item['excerpt']) > 150 ? '...' : '' ?>
                                        </p>
                                        <a href="news_details.php?id=<?= $item['id'] ?>" class="read-more-link">
                                            Read More <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                    $firstItem = false;
                                endforeach; 
                            } else {
                                // No news items found - show placeholder
                            ?>
                                <div class="carousel-item active">
                                    <div class="text-center p-5 bg-light rounded">
                                        <div class="py-5">
                                            <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                                            <h3 class="text-muted">No News Updates Available</h3>
                                            <p class="text-muted">Check back later for the latest news and updates.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        
                        <?php if (count($newsItems) > 1): ?>
                        <!-- Carousel Indicators -->
                        <div class="carousel-indicators">
                            <?php for ($i = 0; $i < count($newsItems); $i++): ?>
                            <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="<?= $i ?>" 
                                <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?> 
                                aria-label="Slide <?= $i + 1 ?>"></button>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Custom Navigation Buttons -->
                        <div class="carousel-nav">
                            <button class="carousel-btn carousel-btn-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                                <i class="fas fa-chevron-left"></i>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-btn carousel-btn-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                                <i class="fas fa-chevron-right"></i>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


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


</body>
</html>
