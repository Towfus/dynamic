<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'your_database';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $uploadDir = 'uploads/photos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $response = [];
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $date_taken = $_POST['date_taken'] ?? date('Y-m-d');
    $description = $_POST['description'] ?? '';

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = $_FILES['images']['name'][$key];
        $fileSize = $_FILES['images']['size'][$key];
        $fileType = $_FILES['images']['type'][$key];
        $fileError = $_FILES['images']['error'][$key];

        // Validate file
        if ($fileError !== UPLOAD_ERR_OK) {
            $response[] = ['success' => false, 'message' => "Error uploading $fileName"];
            continue;
        }

        // Generate unique filename
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('photo_', true) . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        // Move uploaded file
        if (move_uploaded_file($tmpName, $uploadPath)) {
            // Save to database
            try {
                $stmt = $pdo->prepare("INSERT INTO photos (title, category, date_taken, description, image_path) 
                                      VALUES (:title, :category, :date_taken, :description, :image_path)");
                $stmt->execute([
                    ':title' => $title,
                    ':category' => $category,
                    ':date_taken' => $date_taken,
                    ':description' => $description,
                    ':image_path' => $uploadPath
                ]);

                $response[] = [
                    'success' => true,
                    'message' => "$fileName uploaded successfully",
                    'photo' => [
                        'id' => $pdo->lastInsertId(),
                        'title' => $title,
                        'category' => $category,
                        'date_taken' => $date_taken,
                        'description' => $description,
                        'image_path' => $uploadPath
                    ]
                ];
            } catch (PDOException $e) {
                $response[] = ['success' => false, 'message' => "Database error for $fileName"];
            }
        } else {
            $response[] = ['success' => false, 'message' => "Failed to move $fileName"];
        }
    }

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}