
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
          <a href="../admin/submit.php?type=partners" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Partners
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>