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
                            <a href="../admin/submit.php?type=news" class="btn btn-success">
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

<!-- Populate JavaScript data -->
<script>
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
</script>