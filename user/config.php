<?php
// config.php in admin folder
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "organization_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Common functions
function formatCategory($category) {
    $categoryColors = [
        'Events' => 'bg-success',
        'Activities' => 'bg-primary',
        'Awards' => 'bg-warning',
        'Students' => 'bg-info',
        'Teachers' => 'bg-secondary',
        'Facilities' => 'bg-dark'
    ];
    return $categoryColors[$category] ?? 'bg-light';
}

function truncateText($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function formatNewsCategory($category) {
    $categoryClasses = [
        'Partnership' => 'badge-partnership',
        'Brigada Eskwela' => 'badge-brigada',
        'Achievement' => 'badge-achievement', 
        'Event' => 'badge-event',
        'Announcement' => 'badge-announcement',
        'Other' => 'badge-other'
    ];
    return $categoryClasses[$category] ?? 'badge-other';
}

function formatNewsDate($date) {
    return date('F j, Y', strtotime($date));
}
?>