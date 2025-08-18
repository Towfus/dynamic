<?php
// Connect to database
$db = new PDO('mysql:host=localhost;dbname=your_db_name', 'username', 'password');

// Fetch stats from database
$query = $db->query("SELECT * FROM stats ORDER BY display_order ASC");
$stats = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- status boxes -->
<section class="stats-section">
    <?php foreach ($stats as $stat): ?>
    <div class="stat-card">
        <div class="stat-number"><?php echo htmlspecialchars($stat['stat_number']); ?></div>
        <h3 class="stat-title"><?php echo htmlspecialchars($stat['stat_title']); ?></h3>
        <p class="stat-desc"><?php echo htmlspecialchars($stat['stat_desc']); ?></p>
        
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
        <div class="stat-actions">
            <a href="edit_stat.php?id=<?php echo $stat['id']; ?>" class="edit-btn">Edit</a>
            <a href="delete_stat.php?id=<?php echo $stat['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    
    <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
    <div class="stat-card add-new">
        <a href="add_stat.php" class="add-btn">+ Add New Stat</a>
    </div>
    <?php endif; ?>
</section>