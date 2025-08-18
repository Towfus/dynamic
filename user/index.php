<?php
// user/index.php - Dynamic user-facing landing page
require_once '../shared/config.php';

// Fetch dynamic data
$statistics = getStatistics();
$impactStories = getImpactStories();
$featuredStories = getImpactStories(3, true);
$additionalStories = getImpactStories(null, false);
$sustainedPartners = getPartners('sustained');
$individualPartners = getPartners('individual');
$strengthenedPartners = getPartners('strengthened');
$privatePartners = getPartners('private');
$allPartners = getPartners();
$newsUpdates = getNewsUpdates(3, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header>
        <!-- nav bar -->
        <nav class="navbar navbar-expand-lg fixed-top bg-light shadow-sm">
            <div class="container-fluid">
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
                            <a class="nav-link fw-bold" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="proj-isshed.php">Project ISSHED</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="proj-isshed.php#adopt-a-school">Adopt-a-School</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="proj-isshed.php#brigada-eskwela">Brigada Eskwela</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="taxIncentives.php">Tax Incentives</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                More
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="proj-isshed.php#be-our-partner">Be Our Partner</a></li>
                                <li><a class="dropdown-item" href="#news-partnership-updates">News & Partnership Updates</a></li>
                                <li><a class="dropdown-item" href="smn-forms.php">SMN Forms</a></li>
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
            <img src="../assets/images/sdologo.png" alt="SDO GenTri Logo" class="logo-img">
        </div>
    </section>

    <!-- top title section -->
    <div class="top-title">
        <div class="container-title">
            <h3 class="title"><b>Fostering Educational<br>Excellence Through<br>Strategic Partnerships</b></h3>
            <p>The Schools Division Office of General Trias City enhances quality education<br> by building strong, sustainable partnerships with schools, businesses, and <br> the community to provide meaningful learning and support for student success.</p>
        </div>
    </div>

    <!-- Dynamic Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-number"><?php echo htmlspecialchars($statistics['schools_supported']); ?></div>
            <h3 class="stat-title">Schools Supported</h3>
            <p class="stat-desc">Public schools in GenTri</p>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo htmlspecialchars($statistics['total_contributions']); ?></div>
            <h3 class="stat-title">Total Contributions</h3>
            <p class="stat-desc">This school year</p>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo htmlspecialchars($statistics['ongoing_projects']); ?></div>
            <h3 class="stat-title">Ongoing Projects</h3>
            <p class="stat-desc">Benefiting 15,000+ students</p>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo htmlspecialchars($statistics['active_partners']); ?></div>
            <h3 class="stat-title">Active Partners</h3>
            <p class="stat-desc">Private and Public Stakeholders</p>
        </div>
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

            <div class="row justify-content-center g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <img src="https://placehold.co/600x400?text=Project+ISSHED" class="card-img-top" alt="Students in a modern classroom with digital learning tools">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-success fw-bold">Project ISSHED</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Infrastructure Support for School and Home Education Development
                            </p>
                            <a href="proj-isshed.php#about-project-isshed" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
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

    <!-- Dynamic Impact Stories -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark mb-3" style="font-size: 4rem;">Impact Stories</h2>
                <div class="mx-auto mb-3" style="width: 150px; height: 4px; background-color: #006400;"></div>
                <p class="text-muted mx-auto" style="max-width: 600px; font-size: 1.2rem;">
                    See how our partnerships are transforming education in General Trias City
                </p>
            </div>

            <!-- Featured Stories (First Row) -->
            <div class="row g-4 mb-4">
                <?php foreach ($featuredStories as $story): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo htmlspecialchars($story['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($story['title']); ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-light me-2" style="color: #006400;"><?php echo htmlspecialchars($story['category']); ?></span>
                                <small class="story-date"><?php echo formatDate($story['story_date']); ?></small>
                            </div>
                            <h5 class="card-title story-title"><?php echo htmlspecialchars($story['title']); ?></h5>
                            <p class="card-text story-text">
                                <?php echo htmlspecialchars(truncateText($story['excerpt'], 150)); ?>
                            </p>
                            <a href="story.php?id=<?php echo $story['id']; ?>" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
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
            <?php if (!empty($additionalStories)): ?>
            <div class="row g-4 hidden-story d-none">
                <?php foreach ($additionalStories as $story): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo htmlspecialchars($story['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($story['title']); ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-light me-2" style="color: #006400;"><?php echo htmlspecialchars($story['category']); ?></span>
                                <small class="story-date"><?php echo formatDate($story['story_date']); ?></small>
                            </div>
                            <h5 class="card-title story-title"><?php echo htmlspecialchars($story['title']); ?></h5>
                            <p class="card-text story-text">
                                <?php echo htmlspecialchars(truncateText($story['excerpt'], 150)); ?>
                            </p>
                            <a href="story.php?id=<?php echo $story['id']; ?>" class="text-success fw-medium d-inline-flex align-items-center mt-2 icon-link" style="text-decoration: underline; text-decoration-color: #006400;">
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
            <?php if (!empty($additionalStories)): ?>
            <div class="text-center mt-4">
                <button id="viewAllStoriesBtn" class="view-all-btn">
                    View All Stories <i class="fas fa-chevron-down ms-2"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Dynamic Our Valued Partners -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="partner-title" style="font-size: 4rem;">Our Valued Partners</h2>
                <div class="section-divider"></div>
                <p class="text-muted mx-auto" style="font-size: 1.5rem; max-width: 600px;">
                    We appreciate the support of these organizations in advancing quality education in General Trias City
                </p>
            </div>
            
            <div class="mb-5">
                <!-- Sustained Partners -->
                <?php if (!empty($sustainedPartners)): ?>
                <h3 class="partner-category">Sustained Partners</h3>
                <div class="row g-4 justify-content-center mb-4">
                    <?php foreach ($sustainedPartners as $partner): ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                        <div class="partner-box">
                            <img src="../assets/<?php echo htmlspecialchars($partner['logo_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                 class="img-fluid partner-logo"
                                 title="<?php echo htmlspecialchars($partner['name']); ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Hidden Partners (Initially Hidden) -->
                <div class="additional-partners d-none" id="morePartners">
                    <!-- Individual Partners -->
                    <?php if (!empty($individualPartners)): ?>
                    <h3 class="partner-category">Individual Partners</h3>
                    <div class="row g-4 justify-content-center mb-4">
                        <?php foreach ($individualPartners as $partner): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                            <div class="partner-box">
                                <img src="../assets/<?php echo htmlspecialchars($partner['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                     class="img-fluid partner-logo"
                                     title="<?php echo htmlspecialchars($partner['name']); ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Strengthened Partners -->
                    <?php if (!empty($strengthenedPartners)): ?>
                    <h3 class="partner-category">Strengthened Partners</h3>
                    <div class="row g-4 justify-content-center mb-4">
                        <?php foreach ($strengthenedPartners as $partner): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                            <div class="partner-box">
                                <img src="../assets/<?php echo htmlspecialchars($partner['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                     class="img-fluid partner-logo"
                                     title="<?php echo htmlspecialchars($partner['name']); ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Other Private Partners -->
                    <?php if (!empty($privatePartners)): ?>
                    <h3 class="partner-category">Other Private Partners</h3>
                    <div class="row g-4 justify-content-center mb-4">
                        <?php foreach ($privatePartners as $partner): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center">
                            <div class="partner-box">
                                <img src="../assets/<?php echo htmlspecialchars($partner['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($partner['name']); ?>" 
                                     class="img-fluid partner-logo"
                                     title="<?php echo htmlspecialchars($partner['name']); ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- View All / View Less Button -->
                <?php if (!empty($individualPartners) || !empty($strengthenedPartners) || !empty($privatePartners)): ?>
                <div class="text-center mt-4">
                    <button id="view-all-btn" class="view-all-btn">
                        View All <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Dynamic News & Partnership Carousel -->
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
                                <?php foreach ($newsUpdates as $index => $news): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($news['image_url']); ?>" 
                                         class="d-block w-100 carousel-image" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>">
                                    
                                    <div class="carousel-caption-container">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="news-badge <?php echo getBadgeClass($news['category']); ?>"><?php echo ucfirst($news['category']); ?></span>
                                            <span class="news-date"><i class="far fa-calendar me-1"></i> <?php echo formatDate($news['news_date']); ?></span>
                                        </div>
                                        <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                                        <p class="news-excerpt">
                                            <?php echo htmlspecialchars($news['excerpt']); ?>
                                        </p>
                                        <a href="news.php?id=<?php echo $news['id']; ?>" class="read-more-link">
                                            Read More <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Carousel Indicators -->
                            <div class="carousel-indicators">
                                <?php foreach ($newsUpdates as $index => $news): ?>
                                <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                        class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                        aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section py-5">
        <div class="container">
            <div class="row">
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
                
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-0">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3864.328623763576!2d120.88078531532637!3d14.383985789948633!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33962c9650e12a91%3A0x5d5c8988d126f0a5!2sDepEd%20Schools%20Division%20Office%20-%20General%20Trias%20City!5e0!3m2!1sen!2sph!4v1621234567890!5m2!1sen!2sph" 
                                width="100%" 
                                height="400" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                            
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
                        <li class="mb-2"><a href="proj-isshed.php" class="text-white text-decoration-none">Project ISSHED</a></li>
                        <li class="mb-2"><a href="proj-isshed.php#adopt-a-school" class="text-white text-decoration-none">Adopt-a-School</a></li>
                        <li class="mb-2"><a href="taxIncentives.php" class="text-white text-decoration-none">Brigada Eskwela</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>General Trias City, Cavite</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i><?php echo ADMIN_EMAIL; ?></li>
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
                <p class="mb-0">© <?php echo date('Y'); ?> SDO General Trias. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>>