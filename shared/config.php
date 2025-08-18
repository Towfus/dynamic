<?php
// Database connection configuration
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

// Configuration constants
define('MAX_VISIBLE_STORIES', 3);
define('UPLOADS_PATH', 'shared/uploads/'); // Changed from '../shared/uploads/' for public site
define('PHOTOS_PATH', UPLOADS_PATH . 'photos/');
define('NEWS_PATH', UPLOADS_PATH . 'news/');
define('PARTNERS_PATH', UPLOADS_PATH . 'partners/');
define('BG_IMAGES_PATH', UPLOADS_PATH . 'bg_images/');

// Site configuration
define('SITE_NAME', 'SDO General Trias');
define('ADMIN_EMAIL', 'division.gentri@deped.gov.ph');

// Shared functions
function formatCategory($category) {
    $categoryColors = [
        'Events' => 'bg-success',
        'Activities' => 'bg-primary',
        'Awards' => 'bg-warning',
        'Students' => 'bg-info',
        'Teachers' => 'bg-secondary',
        'Facilities' => 'bg-dark',
        'Technology' => 'bg-info',
        'Scholarships' => 'bg-success',
        'Training' => 'bg-warning'
    ];
    
    return isset($categoryColors[$category]) ? $categoryColors[$category] : 'bg-light';
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
    
    return isset($categoryClasses[$category]) ? $categoryClasses[$category] : 'badge-other';
}

function formatNewsDate($date) {
    return date('F j, Y', strtotime($date));
}

// Enhanced function to format currency (for stats)
function formatCurrency($amount) {
    if ($amount >= 1000000) {
        return '₱' . number_format($amount / 1000000, 1) . 'M';
    } elseif ($amount >= 1000) {
        return '₱' . number_format($amount / 1000, 1) . 'K';
    } else {
        return '₱' . number_format($amount, 2);
    }
}

// Function to get photos for Impact Stories
function getPhotos($conn) {
    $query = "SELECT id, title, category, date_taken, description, story_link, file_path, upload_date 
              FROM photos 
              WHERE category IN ('Events', 'Activities', 'Awards', 'Students', 'Teachers', 'Technology', 'Scholarships', 'Training', 'Facilities') 
              ORDER BY upload_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Database query failed: " . mysqli_error($conn));
    }
    
    $photos = [];
    while ($photo = mysqli_fetch_assoc($result)) {
        $photos[] = $photo;
    }
    
    return $photos;
}

// Function to get stats with enhanced formatting
function getStats($conn) {
    $stats_query = "SELECT stat_number, stat_title, stat_desc FROM stats ORDER BY display_order ASC";
    $stats_result = mysqli_query($conn, $stats_query);
    
    $stats = [];
    if ($stats_result) {
        while ($stat = mysqli_fetch_assoc($stats_result)) {
            // Format currency if stat_title contains "Contributions" or "Budget"
            if (strpos(strtolower($stat['stat_title']), 'contribution') !== false || 
                strpos(strtolower($stat['stat_title']), 'budget') !== false) {
                $stat['formatted_number'] = formatCurrency($stat['stat_number']);
            } else {
                $stat['formatted_number'] = $stat['stat_number'];
            }
            $stats[] = $stat;
        }
    }
    
    // If no stats in database, return default stats
    if (empty($stats)) {
        $stats = [
            [
                'stat_number' => 21,
                'formatted_number' => '21',
                'stat_title' => 'Schools Supported',
                'stat_desc' => 'Public schools in GenTri'
            ],
            [
                'stat_number' => 12700000,
                'formatted_number' => '₱12.7M',
                'stat_title' => 'Total Contributions',
                'stat_desc' => 'This school year'
            ],
            [
                'stat_number' => 24,
                'formatted_number' => '24',
                'stat_title' => 'Ongoing Projects',
                'stat_desc' => 'Benefiting 15,000+ students'
            ],
            [
                'stat_number' => 24,
                'formatted_number' => '24',
                'stat_title' => 'Active Partners',
                'stat_desc' => 'Private and Public Stakeholders'
            ]
        ];
    }
    
    return $stats;
}

// Function to get partners with better categorization
function getPartners($conn) {
    $partners_query = "SELECT id, name, category, logo_path FROM partnership ORDER BY category ASC, name ASC";
    $partners_result = mysqli_query($conn, $partners_query);
    
    $partners_by_category = [
        'Sustained' => [],
        'Individual' => [],
        'Strengthened' => [],
        'Other-Private' => []
    ];
    
    if ($partners_result) {
        while ($partner = mysqli_fetch_assoc($partners_result)) {
            $category = $partner['category'];
            if (isset($partners_by_category[$category])) {
                $partners_by_category[$category][] = $partner;
            } else {
                // If category doesn't exist, put in Other-Private
                $partners_by_category['Other-Private'][] = $partner;
            }
        }
    }
    
    return $partners_by_category;
}

// Enhanced function to get news articles with better sorting
function getNewsArticles($conn) {
    $news_query = "SELECT id, title, category, excerpt, news_link, image_path, publish_date, author 
                   FROM news_updates 
                   WHERE is_published = 1 
                   ORDER BY is_featured DESC, publish_date DESC";
    
    $news_result = mysqli_query($conn, $news_query);
    
    $news_articles = [];
    if ($news_result) {
        while ($article = mysqli_fetch_assoc($news_result)) {
            $news_articles[] = $article;
        }
    }
    
    return $news_articles;
}

// New function to get featured content
function getFeaturedStories($conn, $limit = 3) {
    $query = "SELECT id, title, category, date_taken, description, story_link, file_path, upload_date 
              FROM photos 
              WHERE category IN ('Technology', 'Scholarships', 'Training', 'Facilities', 'Awards') 
              ORDER BY upload_date DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $stories = [];
    
    if ($result) {
        while ($story = mysqli_fetch_assoc($result)) {
            $stories[] = $story;
        }
    }
    
    return $stories;
}

// Function to check if file exists, with fallback to placeholder
function getImagePath($imagePath, $type = 'general') {
    if (empty($imagePath) || !file_exists($imagePath)) {
        // Return placeholder based on type
        switch($type) {
            case 'partner':
                return 'https://placehold.co/200x100?text=Partner+Logo';
            case 'news':
                return 'https://placehold.co/600x400?text=News+Image';
            case 'story':
                return 'https://placehold.co/600x400?text=Story+Image';
            default:
                return 'https://placehold.co/600x400?text=Image';
        }
    }
    return $imagePath;
}

// Enhanced error handling
function handleDatabaseError($error_message) {
    error_log("Database Error: " . $error_message);
    // In production, don't show detailed errors to users
    if (defined('DEBUG') ) {
        die("Database Error: " . $error_message);
    } else {
        die("A database error occurred. Please try again later.");
    }
}
?>