<?php
// File: admin/includes/manage_partners.php
?>

<div class="row">
    <!-- Add New Partner Form -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle"></i> Add New Partner</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_partner">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Partner Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Partner Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Sustained">Sustained Partners</option>
                            <option value="Individual">Individual Partners</option>
                            <option value="Strengthened">Strengthened Partners</option>
                            <option value="Other-Private">Other Private Partners</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Partner Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                        <div class="form-text">Max size: 2MB. Formats: JPG, PNG, GIF. Recommended size: 200x100px</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Add Partner
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Existing Partners List -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-handshake"></i> Current Partners</h5>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($partners as $categoryName => $categoryPartners): ?>
                    <?php if (!empty($categoryPartners)): ?>
                    <h6 class="mt-3 mb-2"><?php echo ucwords(str_replace('-', ' ', $categoryName)); ?> Partners</h6>
                    <?php foreach ($categoryPartners as $partner): ?>
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="../<?php echo htmlspecialchars($partner['logo_path']); ?>" 
                                         alt="Partner Logo" 
                                         class="img-fluid rounded"
                                         style="max-height: 50px; object-fit: contain;">
                                </div>
                                <div class="col-md-7">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($partner['name']); ?></h6>
                                    <small class="text-muted">
                                        <span class="badge bg-info"><?php echo ucwords(str_replace('-', ' ', $partner['category'])); ?></span>
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this partner?')">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="partner">
                                        <input type="hidden" name="item_id" value="<?php echo $partner['id']; ?>">
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
                <?php endforeach; ?>
                
                <?php if (empty(array_filter($partners))): ?>
                    <p class="text-muted">No partners found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>