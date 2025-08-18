<?php
<<<<<<< HEAD:user/user_dashboard.php
// Dashboard for reading uploaded files from /shared/uploads
require_once('../shared/config.php');

$photos = getPhotos($conn);
$partners_by_category = getPartners($conn);
$news_articles = getNewsArticles($conn);
=======
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "organization_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Configuration for Impact Stories display
$max_visible_stories = 3; // Maximum stories to show initially (3 per row, 2 rows)

// Query to get photos for Impact Stories - UPDATED to include story_link
$query = "SELECT id, title, category, date_taken, description, story_link, file_path, upload_date 
          FROM photos 
          WHERE category IN ('Events', 'Activities', 'Awards', 'Students', 'Teachers') 
          ORDER BY upload_date DESC";

$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Convert result to array for easier handling
$photos = [];
while ($photo = mysqli_fetch_assoc($result)) {
    $photos[] = $photo;
}

// Query to get stats from database
$stats_query = "SELECT stat_number, stat_title, stat_desc FROM stats ORDER BY display_order ASC";
$stats_result = mysqli_query($conn, $stats_query);

// Convert stats result to array
$stats = [];
if ($stats_result) {
    while ($stat = mysqli_fetch_assoc($stats_result)) {
        $stats[] = $stat;
    }
}

// Query to get partners from database - NEW SECTION
$partners_query = "SELECT id, name, category, logo_path FROM partnership ORDER BY category ASC, name ASC";
$partners_result = mysqli_query($conn, $partners_query);

// Convert partners result to arrays organized by category
$partners_by_category = [
    'Sustained' => [],
    'Individual' => [],
    'Strengthened' => [],
    'Other-Private' => []
];

if ($partners_result) {
    while ($partner = mysqli_fetch_assoc($partners_result)) {
        $category = $partner['category'];
        if (isset($partners_by_category[$category])) {
            $partners_by_category[$category][] = $partner;
        }
    }
}

// Function to format category for display
function formatCategory($category) {
    $categoryColors = [
        'Events' => 'bg-success',
        'Activities' => 'bg-primary',
        'Awards' => 'bg-warning',
        'Students' => 'bg-info',
        'Teachers' => 'bg-secondary',
        'Facilities' => 'bg-dark'
    ];
    
    return isset($categoryColors[$category]) ? $categoryColors[$category] : 'bg-light';
}

// Function to truncate text
function truncateText($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

// Query to get news updates for carousel - NEW SECTION
$news_query = "SELECT id, title, category, excerpt, news_link, image_path, publish_date, author 
               FROM news_updates 
               WHERE is_published = 1 
               ORDER BY is_featured DESC, publish_date DESC 
               LIMIT 5";

$news_result = mysqli_query($conn, $news_query);

// Convert news result to array
$news_articles = [];
if ($news_result) {
    while ($article = mysqli_fetch_assoc($news_result)) {
        $news_articles[] = $article;
    }
}

// Function to format news category for display
function formatNewsCategory($category) {
    $categoryClasses = [
        'Partnership' => 'badge-partnership',
        'Brigada Eskwela' => 'badge-brigada',
        'Achievement' => 'badge-achievement', 
        'Event' => 'badge-event',
        'Announcement' => 'badge-announcement',
        'Other' => 'badge-other'
    ];
    
    return isset($categoryClasses[$category]) ? $categoryClasses[$category] : 'badge-other';
}

// Function to format date for news
function formatNewsDate($date) {
    return date('F j, Y', strtotime($date));
}

>>>>>>> 9bd911a09f594c16306a2d300474285568a36dc6:admin/user_dashboard.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD:user/user_dashboard.php
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SDO GenTri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/user.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container mt-5 pt-5">
        <h1 class="mb-4">Content Dashboard</h1>
        
        <!-- Photos from /shared/uploads -->
        <section class="mb-5">
            <h2>Latest Photos</h2>
            <div class="row">
                <?php foreach (array_slice($photos, 0, 6) as $photo): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="../<?php echo htmlspecialchars($photo['file_path']); ?>" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;"
                             alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($photo['title']); ?></h6>
                            <p class="card-text text-muted small">
                                <?php echo truncateText($photo['description'], 60); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Partners from /shared/uploads -->
        <section class="mb-5">
            <h2>Recent Partners</h2>
            <div class="row">
                <?php 
                $all_partners = array_merge(...array_values($partners_by_category));
                foreach (array_slice($all_partners, 0, 8) as $partner): 
                ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="text-center">
                        <img src="../<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                             class="img-fluid" 
                             style="height: 80px; object-fit: contain;"
                             alt="<?php echo htmlspecialchars($partner['name']); ?>">
                        <p class="mt-2 small"><?php echo htmlspecialchars($partner['name']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- News from /shared/uploads -->
        <section class="mb-5">
            <h2>Recent News</h2>
            <div class="row">
                <?php foreach (array_slice($news_articles, 0, 3) as $article): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="../<?php echo htmlspecialchars($article['image_path']); ?>" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;"
                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h6>
                            <p class="card-text text-muted small">
                                <?php echo truncateText($article['excerpt'], 80); ?>
                            </p>
                            <small class="text-muted">
                                <?php echo formatNewsDate($article['publish_date']); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
=======
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SDO GenTri Partnerships and Linkages</title>
  <link rel="stylesheet" href="../admin/dashboard.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <style>
    
  </style>
</head>
<body>
  <header>
    <!-- nav bar -->
    <nav class="navbar navbar-expand-lg fixed-top bg-light shadow-sm">
        <div class="container-fluid">
            <!-- page title -->
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
                <a class="nav-link fw-bold" href="user_dashboard.php">Home</a>
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
                    <li><a class="dropdown-item" href="index.php#news-partnership-updates">News & Partnership Updates</a></li>
                    <li><a class="dropdown-item" href="smn-forms.html">SMN Forms</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="admin_stories.php">Stories Admin</a></li>
                    <li><a class="dropdown-item" href="admin_partners.php">Partners Admin</a></li>
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
          <img src="..\admin\bg_images\sdologo.png" alt="SDO GenTri Logo" class="logo-img">
      </div>
  </section>

  <!-- top title section -->
  <div class="top-title">
    <div class="container-title">
      <h3 class="title"><b>Fostering Educational<br>Excellence Through<br>Strategic Partnerships</b> </h3>
      <p>The Schools Division Office of General Trias City enhances quality education<br> by building strong, sustainable partnerships with schools, businesses, and <br> the community to provide meaningful learning and support for student success.</p>
    </div>
  </div>

  <!-- Dynamic Status boxes from database -->
  <section class="stats-section">
    <?php if (count($stats) > 0): ?>
      <?php foreach ($stats as $stat): ?>
        <div class="stat-card">
          <div class="stat-number"><?php echo htmlspecialchars($stat['stat_number']); ?></div>
          <h3 class="stat-title"><?php echo htmlspecialchars($stat['stat_title']); ?></h3>
          <p class="stat-desc"><?php echo htmlspecialchars($stat['stat_desc']); ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Fallback default stats if none in database -->
      <div class="stat-card">
        <div class="stat-number">21</div>
        <h3 class="stat-title">Schools Supported</h3>
        <p class="stat-desc">Public schools in GenTri</p>
      </div>
      <div class="stat-card">
        <div class="stat-number">₱12.7M</div>
        <h3 class="stat-title">Total Contributions</h3>
        <p class="stat-desc">This school year</p>
      </div>
      <div class="stat-card">
        <div class="stat-number">24</div>
        <h3 class="stat-title">Ongoing Projects</h3>
        <p class="stat-desc">Benefiting 15,000+ students</p>
      </div>
      <div class="stat-card">
        <div class="stat-number">24</div>
        <h3 class="stat-title">Active Partners</h3>
        <p class="stat-desc">Private and Public Stakeholders</p>
      </div>
    <?php endif; ?>
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
            <img src="../admin/bg_images/ISSHED.png" class="card-img-top" alt="Students in a modern classroom with digital learning tools">
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
            <img src="../admin/bg_images/Brigada_Eskwela.jpg" class="card-img-top" alt="Community volunteers cleaning school grounds before school opening">
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





  
  
  <!-- Dynamic Our Valued Partners Section -->
  <section class="py-5 bg-light">
    <div class="container">
      <!-- Section Header -->
      <div class="text-center mb-5">
        <h2 class="fw-bold text-dark mb-3" style="font-size: 4rem;">Our Valued Partners</h2>
        <div class="mx-auto mb-3" style="width: 150px; height: 4px; background-color: #006400;"></div>
        <p class="text-muted mx-auto" style="font-size: 1.5rem; max-width: 600px;">
          We appreciate the support of these organizations in advancing quality education in General Trias City
        </p>
      </div>
      
      <div class="mb-5" id="partnersSection">
        <?php 
        $hasPartners = false;
        foreach ($partners_by_category as $category => $partners) {
          if (!empty($partners)) {
            $hasPartners = true;
            break;
          }
        }
        ?>
        <?php if ($hasPartners): ?>

          <!-- Sustained Partners -->
          <?php if (!empty($partners_by_category['Sustained'])): ?>
          <h3 class="partner-category">Sustained Partners</h3>
          <div class="row g-4 justify-content-center mb-4">
            <?php foreach ($partners_by_category['Sustained'] as $partner): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
              <div class="partner-box">
                <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                     class="img-fluid partner-logo"
                     onerror="this.src='https://placehold.co/200x150/f8f9fa/6c757d?text=<?php echo urlencode($partner['name']); ?>';">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Individual Partners -->
          <?php if (!empty($partners_by_category['Individual'])): ?>
          <h3 class="partner-category">Individual Partners</h3>
          <div class="row g-4 justify-content-center mb-4">
            <?php foreach ($partners_by_category['Individual'] as $partner): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
              <div class="partner-box">
                <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                     class="img-fluid partner-logo"
                     onerror="this.src='https://placehold.co/200x150/f8f9fa/6c757d?text=<?php echo urlencode($partner['name']); ?>';">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Strengthened Partners -->
          <?php if (!empty($partners_by_category['Strengthened'])): ?>
          <h3 class="partner-category">Strengthened Partners</h3>
          <div class="row g-4 justify-content-center mb-4">
            <?php foreach ($partners_by_category['Strengthened'] as $partner): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
              <div class="partner-box">
                <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                     class="img-fluid partner-logo"
                     onerror="this.src='https://placehold.co/200x150/f8f9fa/6c757d?text=<?php echo urlencode($partner['name']); ?>';">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Other Private Partners -->
          <?php if (!empty($partners_by_category['Other-Private'])): ?>
          <h3 class="partner-category">Other Private Partners</h3>
          <div class="row g-4 justify-content-center mb-4">
            <?php foreach ($partners_by_category['Other-Private'] as $partner): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
              <div class="partner-box">
                <img src="<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                     class="img-fluid partner-logo"
                     onerror="this.src='https://placehold.co/200x150/f8f9fa/6c757d?text=<?php echo urlencode($partner['name']); ?>';">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- No partners available -->
          <div class="text-center py-5">
            <div class="mb-4">
              <i class="fas fa-handshake text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted">No Partners Available</h4>
            <p class="text-muted">Partner information will appear here once they are added through the admin panel.</p>
            <a href="admin_partners.php" class="btn btn-success">
              <i class="fas fa-plus"></i> Add Partners
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Dynamic Impact Stories with automatic dropdown -->
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

      <?php if (count($photos) > 0): ?>
        <!-- Stories Container -->
        <div id="storiesContainer">
          <!-- Initial visible stories -->
          <div class="row g-4 mb-4" id="initialStories">
            <?php 
            $visible_count = min($max_visible_stories, count($photos));
            for ($i = 0; $i < $visible_count; $i++): 
              $photo = $photos[$i];
              $categoryClass = formatCategory($photo['category']);
            ?>
            <div class="col-md-4">
              <div class="card h-100 shadow-sm">
                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($photo['title']); ?>"
                     style="height: 250px; object-fit: cover;"
                     onerror="this.src='https://placehold.co/600x400?text=<?php echo urlencode($photo['category']); ?>';">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-2">
                    <span class="badge <?php echo $categoryClass; ?> me-2" style="color: white;">
                      <?php echo htmlspecialchars($photo['category']); ?>
                    </span>
                    <small class="story-date">
                      <?php echo date('M j, Y', strtotime($photo['date_taken'])); ?>
                    </small>
                  </div>
                  <h5 class="card-title story-title">
                    <?php echo htmlspecialchars($photo['title']); ?>
                  </h5>
                  <p class="card-text story-text">
                    <?php echo htmlspecialchars(truncateText($photo['description'], 120)); ?>
                  </p>
                  <?php if (!empty($photo['story_link'])): ?>
                    <a href="<?php echo htmlspecialchars($photo['story_link']); ?>" 
                       target="_blank" 
                       class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" 
                       style="text-decoration: underline; text-decoration-color: #006400;">
                      View Full Story
                      <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                        <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                      </svg>
                    </a>
                  <?php else: ?>
                    <a href="#" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" 
                       style="text-decoration: underline; text-decoration-color: #006400;" 
                       onclick="openStoryModal(<?php echo $photo['id']; ?>)">
                      Read More Details
                      <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                      </svg>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endfor; ?>
          </div>

          <!-- Additional Hidden Stories (only if more than max_visible_stories) -->
          <?php if (count($photos) > $max_visible_stories): ?>
          <div class="hidden-story d-none" id="additionalStories">
            <div class="row g-4">
              <?php for ($i = $max_visible_stories; $i < count($photos); $i++): 
                $photo = $photos[$i];
                $categoryClass = formatCategory($photo['category']);
              ?>
              <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                  <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                       class="card-img-top" 
                       alt="<?php echo htmlspecialchars($photo['title']); ?>"
                       style="height: 250px; object-fit: cover;"
                       onerror="this.src='https://placehold.co/600x400?text=<?php echo urlencode($photo['category']); ?>';">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                      <span class="badge <?php echo $categoryClass; ?> me-2" style="color: white;">
                        <?php echo htmlspecialchars($photo['category']); ?>
                      </span>
                      <small class="story-date">
                        <?php echo date('M j, Y', strtotime($photo['date_taken'])); ?>
                      </small>
                    </div>
                    <h5 class="card-title story-title">
                      <?php echo htmlspecialchars($photo['title']); ?>
                    </h5>
                    <p class="card-text story-text">
                      <?php echo htmlspecialchars(truncateText($photo['description'], 120)); ?>
                    </p>
                    <?php if (!empty($photo['story_link'])): ?>
                      <a href="<?php echo htmlspecialchars($photo['story_link']); ?>" 
                         target="_blank" 
                         class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" 
                         style="text-decoration: underline; text-decoration-color: #006400;">
                        View Full Story
                        <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                          <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                      </a>
                    <?php else: ?>
                      <a href="#" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" 
                         style="text-decoration: underline; text-decoration-color: #006400;" 
                         onclick="openStoryModal(<?php echo $photo['id']; ?>)">
                        Read More Details
                        <svg xmlns="http://www.w3.org/2000/svg" class="bi ms-2" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                        </svg>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Toggle Button (only shows if more stories than max_visible_stories) -->
        <?php if (count($photos) > $max_visible_stories): ?>
        <div class="text-center mt-4">
          <button id="viewAllStoriesBtn" class="view-all-btn">
            View All Stories <i class="fas fa-chevron-down ms-2"></i>
          </button>
        </div>
        <?php endif; ?>

      <?php else: ?>
        <!-- No stories available -->
        <div class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-images text-muted" style="font-size: 4rem;"></i>
          </div>
          <h4 class="text-muted">No Impact Stories Available</h4>
          <p class="text-muted">Stories will appear here once they are uploaded through the admin panel.</p>
          <a href="admin_stories.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Stories
          </a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Story Detail Modal -->
  <div class="modal fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="storyModalLabel">Story Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="storyContent">
            <!-- Content will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Store story data for JavaScript -->
  <script>
    const storyData = {};
    <?php foreach ($photos as $photo): ?>
    storyData[<?php echo $photo['id']; ?>] = {
      title: "<?php echo addslashes($photo['title']); ?>",
      category: "<?php echo addslashes($photo['category']); ?>",
      date_taken: "<?php echo $photo['date_taken']; ?>",
      description: "<?php echo addslashes($photo['description']); ?>",
      story_link: "<?php echo addslashes($photo['story_link']); ?>",
      file_path: "<?php echo addslashes($photo['file_path']); ?>",
      upload_date: "<?php echo $photo['upload_date']; ?>"
    };
    <?php endforeach; ?>

    // Function to open story modal with full details
    function openStoryModal(storyId) {
      const story = storyData[storyId];
      if (!story) return;

      const modalTitle = document.getElementById('storyModalLabel');
      const storyContent = document.getElementById('storyContent');
      
      modalTitle.textContent = story.title;
      
      const categoryClass = getCategoryClass(story.category);
      const formattedDate = new Date(story.date_taken).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });

      storyContent.innerHTML = `
        <div class="text-center mb-4">
          <img src="${story.file_path}" 
               alt="${story.title}" 
               class="img-fluid rounded"
               style="max-height: 400px; object-fit: cover;"
               onerror="this.src='https://placehold.co/600x400?text=${encodeURIComponent(story.category)}';">
        </div>
        <div class="mb-3">
          <span class="badge ${categoryClass} me-2">${story.category}</span>
          <small class="text-muted"><i class="fas fa-calendar me-1"></i>${formattedDate}</small>
        </div>
        <div class="story-description">
          <p class="lead">${story.description}</p>
        </div>
        ${story.story_link ? `
          <div class="alert alert-info">
            <i class="fas fa-external-link-alt me-2"></i>
            <strong>Full Story Available:</strong> 
            <a href="${story.story_link}" target="_blank" class="alert-link">View complete story</a>
          </div>
        ` : ''}
        <hr>
        <small class="text-muted">
          <i class="fas fa-clock me-1"></i>Published on ${new Date(story.upload_date).toLocaleDateString()}
        </small>
      `;
      
      const modal = new bootstrap.Modal(document.getElementById('storyModal'));
      modal.show();
    }

    function getCategoryClass(category) {
      const categoryClasses = {
        'Events': 'bg-success',
        'Activities': 'bg-primary',
        'Awards': 'bg-warning',
        'Students': 'bg-info',
        'Teachers': 'bg-secondary',
        'Facilities': 'bg-dark'
      };
      return categoryClasses[category] || 'bg-light';
    }

    // Toggle functionality for stories only
    document.addEventListener('DOMContentLoaded', function() {
      const viewAllBtn = document.getElementById('viewAllStoriesBtn');
      const additionalStories = document.getElementById('additionalStories');
      
      if (viewAllBtn && additionalStories) {
        let isExpanded = false;
        
        viewAllBtn.addEventListener('click', function() {
          if (!isExpanded) {
            // Show additional stories
            additionalStories.classList.remove('d-none');
            viewAllBtn.innerHTML = 'View Less <i class="fas fa-chevron-up ms-2"></i>';
            isExpanded = true;
            
            // Smooth scroll to reveal new content
            setTimeout(() => {
              additionalStories.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
              });
            }, 100);
          } else {
            // Hide additional stories
            additionalStories.classList.add('d-none');
            viewAllBtn.innerHTML = 'View All Stories <i class="fas fa-chevron-down ms-2"></i>';
            isExpanded = false;
            
            // Scroll back to the initial stories
            setTimeout(() => {
              document.getElementById('initialStories').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
              });
            }, 100);
          }
        });
      }
    });
  </script>

  <script src="script.js"></script>

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
                    <?php if (count($news_articles) > 0): ?>
                        <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($news_articles as $index => $article): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($article['image_path']); ?>" 
                                         class="d-block w-100 carousel-image" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                                         onerror="this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';">
                                    
                                    <div class="carousel-caption-container">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="news-badge <?php echo formatNewsCategory($article['category']); ?>">
                                                <?php echo htmlspecialchars($article['category']); ?>
                                            </span>
                                            <span class="news-date">
                                                <i class="far fa-calendar me-1"></i> 
                                                <?php echo formatNewsDate($article['publish_date']); ?>
                                            </span>
                                        </div>
                                        <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                                        <p class="news-excerpt">
                                            <?php 
                                            $excerpt = htmlspecialchars($article['excerpt']);
                                            // Limit excerpt length for carousel display
                                            echo strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt; 
                                            ?>
                                        </p>
                                        <?php if (!empty($article['news_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($article['news_link']); ?>" 
                                           target="_blank" class="read-more-link">
                                            Read More <i class="fas fa-external-link-alt ms-2"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="#" class="read-more-link" onclick="openNewsModal(<?php echo $article['id']; ?>)">
                                            Read More <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Carousel Indicators -->
                            <div class="carousel-indicators">
                                <?php foreach ($news_articles as $index => $article): ?>
                                <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                        <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                        aria-label="Slide <?php echo $index + 1; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Custom Navigation Buttons -->
                        <div class="carousel-nav">
                            <button class="carousel-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="carousel-btn" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- No news available -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-newspaper text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="text-muted">No News Updates Available</h4>
                            <p class="text-muted">News articles will appear here once they are published through the admin panel.</p>
                            <a href="submit_news.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add News Articles
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- News Detail Modal -->
<div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsModalLabel">News Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="newsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Store news data for JavaScript -->
<script>
    const newsData = {};
    <?php foreach ($news_articles as $article): ?>
    newsData[<?php echo $article['id']; ?>] = {
        title: "<?php echo addslashes($article['title']); ?>",
        category: "<?php echo addslashes($article['category']); ?>",
        excerpt: "<?php echo addslashes($article['excerpt']); ?>",
        news_link: "<?php echo addslashes($article['news_link']); ?>",
        image_path: "<?php echo addslashes($article['image_path']); ?>",
        publish_date: "<?php echo $article['publish_date']; ?>",
        author: "<?php echo addslashes($article['author']); ?>"
    };
    <?php endforeach; ?>

    // Function to open news modal with full details
    function openNewsModal(newsId) {
        const news = newsData[newsId];
        if (!news) return;

        const modalTitle = document.getElementById('newsModalLabel');
        const newsContent = document.getElementById('newsContent');
        
        modalTitle.textContent = news.title;
        
        const categoryClass = getNewsCategoryClass(news.category);
        const formattedDate = new Date(news.publish_date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        newsContent.innerHTML = `
            <div class="text-center mb-4">
                <img src="${news.image_path}" 
                     alt="${news.title}" 
                     class="img-fluid rounded"
                     style="max-height: 400px; object-fit: cover;"
                     onerror="this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';">
            </div>
            <div class="mb-3">
                <span class="badge ${categoryClass} me-2">${news.category}</span>
                <small class="text-muted"><i class="fas fa-calendar me-1"></i>${formattedDate}</small>
                ${news.author ? `<small class="text-muted"> • by ${news.author}</small>` : ''}
            </div>
            <div class="news-description">
                <p class="lead">${news.excerpt}</p>
            </div>
            ${news.news_link ? `
                <div class="alert alert-info">
                    <i class="fas fa-external-link-alt me-2"></i>
                    <strong>Full Article Available:</strong> 
                    <a href="${news.news_link}" target="_blank" class="alert-link">Read complete article</a>
                </div>
            ` : ''}
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('newsModal'));
        modal.show();
    }

    function getNewsCategoryClass(category) {
        const categoryClasses = {
            'Partnership': 'bg-success',
            'Brigada Eskwela': 'bg-info',
            'Achievement': 'bg-warning text-dark',
            'Event': 'bg-primary',
            'Announcement': 'bg-secondary',
            'Other': 'bg-dark'
        };
        return categoryClasses[category] || 'bg-secondary';
    }
</script>

<!-- Add these CSS styles to your existing dashboard.css or in a style tag -->
<style>
/* News section specific styles */
.news-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.section-title {
    font-size: 3.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1rem;
}

.carousel-container {
    position: relative;
    margin: 0 auto;
    max-width: 1200px;
}

.carousel-image {
    height: 500px;
    object-fit: cover;
    border-radius: 15px;
}

.carousel-caption-container {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    padding: 3rem 2rem 2rem;
    border-radius: 0 0 15px 15px;
    color: white;
}

.news-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-partnership { background-color: #28a745; color: white; }
.badge-brigada { background-color: #17a2b8; color: white; }
.badge-achievement { background-color: #ffc107; color: #212529; }
.badge-event { background-color: #007bff; color: white; }
.badge-announcement { background-color: #6f42c1; color: white; }
.badge-other { background-color: #6c757d; color: white; }

.news-date {
    margin-left: 1rem;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
}

.news-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.news-excerpt {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    color: rgba(255, 255, 255, 0.95);
}

.read-more-link {
    color: #ffc107;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.read-more-link:hover {
    color: #ffed4e;
    text-decoration: none;
    transform: translateX(5px);
}

.carousel-indicators {
    bottom: -50px;
}

.carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 6px;
    background-color: #006400;
    border: 2px solid transparent;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.carousel-indicators button.active {
    opacity: 1;
    transform: scale(1.2);
    background-color: #28a745;
}

.carousel-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 -60px;
    pointer-events: none;
    transform: translateY(-50%);
}

.carousel-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    pointer-events: all;
    color: #006400;
    font-size: 1.2rem;
}

.carousel-btn:hover {
    background: white;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transform: scale(1.1);
    color: #28a745;
}

.carousel-btn:first-child {
    margin-left: -80px;
}

.carousel-btn:last-child {
    margin-right: -80px;
}

/* Responsive design */
@media (max-width: 768px) {
    .section-title {
        font-size: 2.5rem;
    }
    
    .carousel-image {
        height: 300px;
    }
    
    .carousel-caption-container {
        padding: 2rem 1.5rem 1.5rem;
    }
    
    .news-title {
        font-size: 1.5rem;
    }
    
    .news-excerpt {
        font-size: 1rem;
    }
    
    .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .carousel-btn:first-child {
        margin-left: -60px;
    }
    
    .carousel-btn:last-child {
        margin-right: -60px;
    }
}

@media (max-width: 576px) {
    .carousel-btn:first-child {
        margin-left: -20px;
    }
    
    .carousel-btn:last-child {
        margin-right: -20px;
    }
}
</style>

  <!-- Contact Section -->
  <section class="contact-section py-5">
      <div class="container">
          <div class="row">
              <!-- Left Side (Original Contact Info) -->
              <div class="col-lg-6">
                  <h1 class="mb-4" style="font-size: 3rem;">Get in Touch</h1>
                  <p class="reach" style="font-size: 1.5rem">Reach out for partnerships, inquiries, or support from our team.</p>
                  
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
              
              <!-- Right Side (Google Map) -->
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
                      <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
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

<?php
// Close database connection
$conn->close();
?>
>>>>>>> 9bd911a09f594c16306a2d300474285568a36dc6:admin/user_dashboard.php
