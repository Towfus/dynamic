<?php
// File: admin/includes/manage_news.php
?>

<div class="row">
    <!-- Add New News Article Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle"></i> Add News Article</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_news">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Article Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Partnership">Partnership</option>
                            <option value="Brigada Eskwela">Brigada Eskwela</option>
                            <option value="Achievement">Achievement</option>
                            <option value="Event">Event</option>
                            <option value="Announcement">Announcement</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt/Summary</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="publish_date" class="form-label">Publish Date</label>
                        <input type="date" class="form-control" id="publish_date" name="publish_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" id="author" name="author" value="SDO GenTri Admin" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="news_link" class="form-label">News Link (optional)</label>
                        <input type="url" class="form-control" id="news_link" name="news_link" placeholder="https://example.com/full-article">
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Featured Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <div class="form-text">Max size: 5MB. Formats: JPG, PNG, GIF. Recommended size: 1200x600px</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                        <label class="form-check-label" for="is_featured">
                            Feature this article (show in carousel)
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Add Article
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Existing News Articles List -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-newspaper"></i> Current News Articles</h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php if (empty($news)): ?>
                    <p class="text-muted">No news articles found.</p>
                <?php else: ?>
                    <?php foreach ($news as $article): ?>
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="../<?php echo htmlspecialchars($article['image_path']); ?>" 
                                         alt="News Image" 
                                         class="img-fluid rounded"
                                         style="max-height: 60px; object-fit: cover;">
                                </div>
                                <div class="col-md-7">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($article['title']); ?></h6>
                                    <small class="text-muted">
                                        <span class="badge <?php echo formatNewsCategory($article['category']); ?>">
                                            <?php echo htmlspecialchars($article['category']); ?>
                                        </span>
                                        <?php echo formatNewsDate($article['publish_date']); ?>
                                        <?php if (isset($article['is_featured']) && $article['is_featured']): ?>
                                            <span class="badge bg-warning text-dark ms-1">Featured</span>
                                        <?php endif; ?>
                                    </small>
                                    <p class="mb-0 small"><?php echo truncateText($article['excerpt'], 80); ?></p>
                                    <?php if (!empty($article['author'])): ?>
                                        <small class="text-muted">By: <?php echo htmlspecialchars($article['author']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this article?')">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="news">
                                        <input type="hidden" name="item_id" value="<?php echo $article['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>