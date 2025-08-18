<?php
header('Content-Type: application/json');

// Database connection (same as upload_photo.php)
$host = 'localhost';
$dbname = 'your_database';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM photos ORDER BY created_at DESC");
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($photos);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}