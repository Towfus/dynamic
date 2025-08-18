<?php
// File: admin/includes/manage_stats.php
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Update Website Statistics</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_stats">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="schools_supported" class="form-label">Schools Supported</label>
                            <input type="number" class="form-control" id="schools_supported" name="schools_supported" 
                                   value="<?php echo isset($stats[0]) ? $stats[0]['stat_number'] : '21'; ?>" required>
                            <div class="form-text">Number of schools currently supported</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_contributions" class="form-label">Total Contributions (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="total_contributions" name="total_contributions" 
                                   value="<?php echo isset($stats[1]) ? $stats[1]['stat_number'] : '12700000'; ?>" required>
                            <div class="form-text">Total monetary contributions this school year</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="ongoing_projects" class="form-label">Ongoing Projects</label>
                            <input type="number" class="form-control" id="ongoing_projects" name="ongoing_projects" 
                                   value="<?php echo isset($stats[2]) ? $stats[2]['stat_number'] : '24'; ?>" required>
                            <div class="form-text">Number of currently ongoing projects</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="active_partners" class="form-label">Active Partners</label>
                            <input type="number" class="form-control" id="active_partners" name="active_partners" 
                                   value="<?php echo isset($stats[3]) ? $stats[3]['stat_number'] : '24'; ?>" required>
                            <div class="form-text">Number of active partner organizations</div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Statistics
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Current Stats Preview -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-eye"></i> Current Statistics Preview</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <?php foreach ($stats as $stat): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-primary"><?php echo htmlspecialchars($stat['formatted_number']); ?></h3>
                                <h6 class="card-title"><?php echo htmlspecialchars($stat['stat_title']); ?></h6>
                                <p class="card-text small text-muted"><?php echo htmlspecialchars($stat['stat_desc']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-info-circle"></i> Instructions:</h6>
            <ul class="mb-0">
                <li>Enter numeric values for each statistic</li>
                <li>For contributions, enter the full amount (e.g., 12700000 for ₱12.7M)</li>
                <li>The system will automatically format large numbers on the website</li>
                <li>Changes will be reflected immediately on the public website</li>
            </ul>
        </div>
    </div>
</div>