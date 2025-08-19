<?php
// get_highlight_image.php - Helper file to fetch highlight images
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration - Update with your actual database credentials
$host = 'localhost';
$dbname = 'sdo_gentri';
$username = 'root';
$password = '';

// Get the highlight ID from request
$highlightId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($highlightId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid highlight ID'
    ]);
    exit;
}

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get image data
    $query = "SELECT image_data, image_type FROM project_highlights WHERE id = :id AND is_active = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $highlightId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image && $image['image_data']) {
        $imageSrc = 'data:' . $image['image_type'] . ';base64,' . base64_encode($image['image_data']);
        echo json_encode([
            'success' => true,
            'image_src' => $imageSrc
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Image not found',
            'id_searched' => $highlightId
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in get_highlight_image.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error'
    ]);
}
?>