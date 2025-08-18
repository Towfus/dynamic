<?php
require_once '../shared/config.php';

// Returns the current full URL
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $requestUri;
}

// Get story ID from URL
$storyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$storyId) {
    header('Location: index.php');
    exit();
}

// Fetch the impact story
$stmt = $pdo->prepare("SELECT * FROM impact_stories WHERE id = ?");
$stmt->execute([$storyId]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: index.php');
    exit();
}

// Fetch related stories (same category, excluding current)
$relatedStmt = $pdo->prepare("SELECT * FROM impact_stories WHERE category = ? AND id != ? ORDER BY story_date DESC LIMIT 3");
$relatedStmt->execute([$story['category'], $storyId]);
$relatedStories = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($story['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($story['excerpt']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($story['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($story['excerpt']); ?>">
    <meta property="og:image" content="<?php echo $story['image_url'] ? BASE_URL . '/shared/' . $story['image_url'] : ''; ?>">
    <meta property="og:url" content="<?php echo getCurrentUrl(); ?>">
    <meta property="og:type" content="article">
    
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg fixed-top bg-light shadow-sm">
            <div class="container-fluid">
                <div class="navbar-brand d-flex flex-column align-items-start">
                    <span class="custom-green fw-bold">SDO General Trias</span>
                    <span class="text-muted fs-6">Partnership and Linkages</span>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGenTri">
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
                            <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                More
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="proj-isshed.php#be-our-partner">Be Our Partner</a></li>
                                <li><a class="dropdown-item" href="index.php#news-partnership-updates">News & Partnership Updates</a></li>
                                <li><a class="dropdown-item" href="smn-forms.php">SMN Forms</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Logo Section -->
    <section class="logo-section">
        <div class="container text-center">
            <img src="../assets/images/sdologo.png" alt="SDO GenTri Logo" class="logo-img">
        </div>
    </section>

    <!-- Breadcrumb -->
    <section class="py-3 bg-light">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php#impact-stories" class="text-success">Impact Stories</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Story</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Main Story Content -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article class="impact-story">
                        <!-- Story Header -->
                        <header class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-light me-3" style="color: #006400;">
                                    <?php echo htmlspecialchars($story['category']); ?>
                                </span>
                                <span class="text-muted">
                                    <i class="far fa-calendar me-1"></i>
                                    <?php echo formatDate($story['story_date']); ?>
                                </span>
                                <?php if ($story['featured']): ?>
                                <span class="badge bg-warning text-dark ms-2">Featured</span>
                                <?php endif; ?>
                            </div>
                            
                            <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($story['title']); ?></h1>
                            
                            <?php if ($story['excerpt']): ?>
                            <p class="lead text-muted"><?php echo htmlspecialchars($story['excerpt']); ?></p>
                            <?php endif; ?>
                        </header>

                        <!-- Featured Image -->
                        <?php if ($story['image_url']): ?>
                        <div class="mb-4">
                            <img src="../shared/<?php echo htmlspecialchars($story['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($story['title']); ?>" 
                                 class="img-fluid rounded shadow-sm">
                        </div>
                        <?php endif; ?>

                        <!-- Story Content -->
                        <div class="story-content">
                            <?php echo nl2br(htmlspecialchars($story['content'])); ?>
                        </div>

                        <!-- Impact Metrics (if available) -->
                        <?php if (!empty($story['impact_metrics'])): ?>
                        <div class="impact-metrics mt-5 p-4 bg-light rounded">
                            <h4 class="mb-3">Impact Metrics</h4>
                            <div class="row">
                                <?php 
                                $metrics = json_decode($story['impact_metrics'], true);
                                if ($metrics && is_array($metrics)):
                                    foreach ($metrics as $metric => $value): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chart-line text-success me-3"></i>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($value); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($metric); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Story Footer -->
                        <footer class="mt-5 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        Published on <?php echo formatDate($story['story_date']); ?>
                                        <?php if ($story['updated_at'] && $story['updated_at'] != $story['created_at']): ?>
                                        | Updated <?php echo formatDate($story['updated_at']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div>
                                    <!-- Social Share Buttons -->
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>" 
                                       target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                        <i class="fab fa-facebook-f"></i> Share
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode($story['title']); ?>" 
                                       target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="fab fa-twitter"></i> Tweet
                                    </a>
                                </div>
                            </div>
                        </footer>
                    </article>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <aside class="sticky-top" style="top: 100px;">
                        <!-- Back to Stories Button -->
                        <div class="mb-4">
                            <a href="index.php#impact-stories" class="btn btn-outline-success">
                                <i class="fas fa-arrow-left me-2"></i>Back to Stories
                            </a>
                        </div>

                        <!-- Related Stories -->
                        <?php if (!empty($relatedStories)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Related Stories</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($relatedStories as $related): ?>
                                <div class="p-3 border-bottom">
                                    <?php if ($related['image_url']): ?>
                                    <img src="../shared/<?php echo htmlspecialchars($related['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                         class="img-fluid rounded mb-2" style="height: 120px; object-fit: cover; width: 100%;">
                                    <?php endif; ?>
                                    <h6 class="mb-1">
                                        <a href="story.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($related['title']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo formatDate($related['story_date']); ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Partnership Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Get Involved</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Interested in creating similar impact stories? Join our partnership programs!</p>
                                <a href="proj-isshed.php#be-our-partner" class="btn btn-success btn-sm d-block mb-2">
                                    <i class="fas fa-handshake me-2"></i>Become a Partner
                                </a>
                                <a href="proj-isshed.php" class="btn btn-outline-success btn-sm d-block">
                                    <i class="fas fa-info-circle me-2"></i>Learn More
                                </a>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Contact Us</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    <small><?php echo ADMIN_EMAIL; ?></small>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-phone me-2 text-primary"></i>
                                    <small>(046) 509 1167</small>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                    <small>General Trias City, Cavite</small>
                                </p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-3">SDO General Trias</h5>
                    <p>Empowering education through strategic partnerships with schools, businesses, and the community.</p>
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
                        <li class="mb-2"><a href="taxIncentives.php" class="text-white text-decoration-none">Tax Incentives</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>General Trias City, Cavite</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i><?php echo ADMIN_EMAIL; ?></li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>(046) 509 1167</li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/script.js"></script>
</body>
</html>