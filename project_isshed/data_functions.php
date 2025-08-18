<?php
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
function getTimelineEvents() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM timeline_events WHERE is_active = 1 ORDER BY display_order ASC, event_date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return []; // Return empty array on error
    }
}

/**
 * Get project highlights for the gallery
 * @return array Array of project highlights with details
 */
function getProjectHighlights() {
    return [
        [
            'id' => 1,
            'title' => 'Student Health Screening',
            'description' => 'Comprehensive health screening program for students to ensure their well-being and readiness for learning.',
            'image' => 'bg_images/NEWS1.jpg',
            'alt' => 'Student Health Screening',
            'category' => 'health',
            'date' => '2023-09-15',
            'featured' => true,
            'tags' => ['health', 'students', 'screening']
        ],
        [
            'id' => 2,
            'title' => 'Teacher Training Workshop',
            'description' => 'Professional development workshop for educators to enhance teaching methodologies and student engagement.',
            'image' => 'bg_images/NEWS2.jpg',
            'alt' => 'Teacher Training',
            'category' => 'education',
            'date' => '2023-08-22',
            'featured' => true,
            'tags' => ['training', 'teachers', 'professional development']
        ],
        [
            'id' => 3,
            'title' => 'Interactive Student Workshop',
            'description' => 'Engaging hands-on workshop sessions designed to promote active learning and skill development among students.',
            'image' => 'bg_images/NEWS3.jpg',
            'alt' => 'Student Workshop',
            'category' => 'workshop',
            'date' => '2023-10-05',
            'featured' => true,
            'tags' => ['workshop', 'students', 'interactive learning']
        ],
        [
            'id' => 4,
            'title' => 'Community Volunteering Initiative',
            'description' => 'Community outreach program where students and teachers collaborate with local organizations for social impact.',
            'image' => 'bg_images/NEWS4.jpg',
            'alt' => 'Community Volunteering',
            'category' => 'community',
            'date' => '2023-07-18',
            'featured' => true,
            'tags' => ['community', 'volunteering', 'outreach']
        ],
        [
            'id' => 5,
            'title' => 'STEM Education Fair',
            'description' => 'Science, Technology, Engineering, and Mathematics fair showcasing innovative student projects and research.',
            'image' => 'bg_images/NEWS1.jpg',
            'alt' => 'STEM Education Fair',
            'category' => 'education',
            'date' => '2023-11-12',
            'featured' => false,
            'tags' => ['STEM', 'education', 'innovation']
        ],
        [
            'id' => 6,
            'title' => 'Parent-Teacher Conference',
            'description' => 'Collaborative meeting between parents and teachers to discuss student progress and educational goals.',
            'image' => 'bg_images/NEWS2.jpg',
            'alt' => 'Parent-Teacher Conference',
            'category' => 'collaboration',
            'date' => '2023-09-28',
            'featured' => false,
            'tags' => ['parents', 'teachers', 'collaboration']
        ],
        [
            'id' => 7,
            'title' => 'Digital Literacy Training',
            'description' => 'Technology training program to enhance digital skills among students and teachers in the modern era.',
            'image' => 'bg_images/NEWS3.jpg',
            'alt' => 'Digital Literacy Training',
            'category' => 'technology',
            'date' => '2023-10-20',
            'featured' => false,
            'tags' => ['technology', 'digital literacy', 'training']
        ],
        [
            'id' => 8,
            'title' => 'Environmental Awareness Campaign',
            'description' => 'School-wide campaign promoting environmental consciousness and sustainable practices among the community.',
            'image' => 'bg_images/NEWS4.jpg',
            'alt' => 'Environmental Awareness Campaign',
            'category' => 'environment',
            'date' => '2023-06-10',
            'featured' => false,
            'tags' => ['environment', 'sustainability', 'awareness']
        ],
        [
            'id' => 9,
            'title' => 'Sports and Recreation Day',
            'description' => 'Annual sports event promoting physical fitness, teamwork, and healthy competition among students.',
            'image' => 'bg_images/NEWS1.jpg',
            'alt' => 'Sports and Recreation Day',
            'category' => 'sports',
            'date' => '2023-05-15',
            'featured' => false,
            'tags' => ['sports', 'recreation', 'fitness']
        ],
        [
            'id' => 10,
            'title' => 'Arts and Culture Festival',
            'description' => 'Celebration of local arts and culture featuring student performances, exhibitions, and cultural activities.',
            'image' => 'bg_images/NEWS2.jpg',
            'alt' => 'Arts and Culture Festival',
            'category' => 'culture',
            'date' => '2023-04-22',
            'featured' => false,
            'tags' => ['arts', 'culture', 'festival']
        ]
    ];
}

/**
 * Get featured highlights (limited number for main display)
 * @param int $limit Number of featured highlights to return
 * @return array Array of featured project highlights
 */
function getFeaturedHighlights($limit = 4) {
    $all_highlights = getProjectHighlights();
    $featured = array_filter($all_highlights, function($highlight) {
        return $highlight['featured'] === true;
    });
    
    return array_slice($featured, 0, $limit);
}

/**
 * Get timeline events by status
 * @param string $status Status filter ('completed', 'in-progress', 'future')
 * @return array Filtered timeline events
 */
function getTimelineEventsByStatus($status) {
    $all_events = getTimelineEvents();
    return array_filter($all_events, function($event) use ($status) {
        return $event['status'] === $status;
    });
}

/**
 * Get project highlights by category
 * @param string $category Category filter
 * @return array Filtered project highlights
 */
function getHighlightsByCategory($category) {
    $all_highlights = getProjectHighlights();
    return array_filter($all_highlights, function($highlight) use ($category) {
        return $highlight['category'] === $category;
    });
}

/**
 * Search project highlights by tag
 * @param string $tag Tag to search for
 * @return array Filtered project highlights
 */
function searchHighlightsByTag($tag) {
    $all_highlights = getProjectHighlights();
    return array_filter($all_highlights, function($highlight) use ($tag) {
        return in_array(strtolower($tag), array_map('strtolower', $highlight['tags']));
    });
}

/**
 * Get recent highlights (sorted by date)
 * @param int $limit Number of recent highlights to return
 * @return array Recent project highlights
 */
function getRecentHighlights($limit = 5) {
    $all_highlights = getProjectHighlights();
    
    // Sort by date (newest first)
    usort($all_highlights, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return array_slice($all_highlights, 0, $limit);
}

/**
 * Get timeline statistics
 * @return array Statistics about timeline events
 */
function getTimelineStats() {
    $events = getTimelineEvents();
    $stats = [
        'total' => count($events),
        'completed' => 0,
        'in_progress' => 0,
        'future' => 0
    ];
    
    foreach ($events as $event) {
        $stats[$event['status']]++;
    }
    
    return $stats;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
?>