<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'organization_portal');

// Create connection


function getDBConnection() {
    $host = 'localhost';        // Usually 'localhost' for XAMPP
    $db   = 'organization_portal';   // Your database name
    $user = 'root';             // Default XAMPP username is 'root'
    $pass = '';                 // Default XAMPP password is empty
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

// Helper function to handle file uploads
function handleFileUpload($file, $uploadDir = 'uploads/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file extension
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        throw new Exception("Invalid file type. Allowed types: " . implode(', ', ALLOWED_EXTENSIONS));
    }
    
    // Validate file size
    if ($fileSize > MAX_FILE_SIZE) {
        throw new Exception("File too large. Maximum size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB");
    }
    
    // Create unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        return $uploadPath;
    }
    
    return false;
}

// File upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Helper function to format date
function formatDate($date, $format = 'F Y') {
    return date($format, strtotime($date));
}


?>