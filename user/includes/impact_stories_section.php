
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
          $visible_count = min(MAX_VISIBLE_STORIES, count($photos));
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

        <!-- Additional Hidden Stories -->
        <?php if (count($photos) > MAX_VISIBLE_STORIES): ?>
        <div class="hidden-story d-none" id="additionalStories">
          <div class="row g-4">
            <?php for ($i = MAX_VISIBLE_STORIES; $i < count($photos); $i++): 
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

      <!-- Toggle Button -->
      <?php if (count($photos) > MAX_VISIBLE_STORIES): ?>
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
        <a href="../admin/submit.php?type=stories" class="btn btn-success">
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