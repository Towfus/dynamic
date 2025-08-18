<?php
require_once 'config.php';

// Get all active highlights from database
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM project_highlights WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
$highlights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate visible and hidden items
$visibleCount = 2; // Number of items to show initially
$visibleHighlights = array_slice($highlights, 0, $visibleCount);
$hiddenHighlights = array_slice($highlights, $visibleCount);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project ISSHED - Highlights Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

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
    </style>
</head>
<body>
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
    <script>
        // Highlight data for modal display
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
    </script>
</body>
</html>