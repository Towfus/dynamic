<?php
// File: admin/includes/manage_stories.php
?>

<div class="row">
    <!-- Add New Story Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle"></i> Add New Impact Story</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_story">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Story Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Technology">Technology</option>
                            <option value="Scholarships">Scholarships</option>
                            <option value="Training">Training</option>
                            <option value="Facilities">Facilities</option>
                            <option value="Awards">Awards</option>
                            <option value="Events">Events</option>
                            <option value="Activities">Activities</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_taken" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date_taken" name="date_taken" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="story_link" class="form-label">Story Link (optional)</label>
                        <input type="url" class="form-control" id="story_link" name="story_link" placeholder="https://example.com/full-story">
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                        <div class="form-text">Max size: 5MB. Formats: JPG, PNG, GIF</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Add Story
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Existing Stories List -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Current Impact Stories</h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php if (empty($stories)): ?>
                    <p class="text-muted">No stories found.</p>
                <?php else: ?>
                    <?php foreach ($stories as $story): ?>
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="../<?php echo htmlspecialchars($story['file_path']); ?>" 
                                         alt="Story Image" 
                                         class="img-fluid rounded"
                                         style="max-height: 60px; object-fit: cover;">
                                </div>
                                <div class="col-md-7">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($story['title']); ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($story['category']); ?></span>
                                        <?php echo formatNewsDate($story['date_taken']); ?>
                                    </small>
                                    <p class="mb-0 small"><?php echo truncateText($story['description'], 80); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this story?')">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="story">
                                        <input type="hidden" name="item_id" value="<?php echo $story['id']; ?>">
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