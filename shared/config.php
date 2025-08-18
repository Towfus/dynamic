<?php
// config.php - Merged configuration for SDO General Trias Partnership System

// Database configuration (using the first file's database name)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sdo_gentri');
define('DB_USER', 'root'); // Change this to your DB username
define('DB_PASS', '');     // Change this to your DB password

// Site configuration (merged values)
define('SITE_NAME', 'SDO General Trias - Partnership and Linkages');
define('SITE_URL', 'http://localhost/sdo-gentri'); // Primary URL
define('BASE_URL', SITE_URL); // Alias for compatibility
define('ADMIN_EMAIL', 'admin@sdo-gentri.gov.ph'); // Primary admin email
define('DIVISION_EMAIL', 'division.gentri@deped.gov.ph'); // Additional email

// Login page constant for redirects
define('LOGIN_PAGE', SITE_URL . '/login.php');

// File upload configuration (from first file with enhancements)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/shared/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    // Create subdirectories
    mkdir(UPLOAD_DIR . 'partners/', 0755, true);
    mkdir(UPLOAD_DIR . 'stories/', 0755, true);
    mkdir(UPLOAD_DIR . 'news/', 0755, true);
    mkdir(UPLOAD_DIR . 'cache/', 0755, true);
    mkdir(UPLOAD_DIR . 'logs/', 0755, true);
}

// Database connection class (singleton pattern from first file)
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Initialize database connection
try {
    $pdo = Database::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Session management (enhanced from both files)
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_only_cookies', 1);
        session_start();
    }
}

// Authentication functions (merged)
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']) || 
           (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . (defined('LOGIN_PAGE') ? LOGIN_PAGE : 'login.php'));
        exit();
    }
}

function login($username, $password) {
    // This should be replaced with proper database authentication
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

function getCurrentAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// Helper functions (merged and enhanced)
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function formatDate($date, $format = 'F j, Y') {
    return $date ? date($format, strtotime($date)) : '';
}

function truncateText($text, $length = 100) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

function getBadgeClass($category) {
    $classes = [
        'technology' => 'bg-primary',
        'scholarships' => 'bg-warning',
        'training' => 'bg-info',
        'facilities' => 'bg-secondary',
        'partnership' => 'badge-partnership bg-success',
        'brigada' => 'badge-brigada',
        'achievement' => 'badge-achievement bg-primary',
        'announcement' => 'bg-info',
        'event' => 'bg-warning text-dark',
        'infrastructure' => 'bg-secondary',
        'community' => 'bg-success',
        'education' => 'bg-primary'
    ];
    
    return $classes[strtolower($category)] ?? 'bg-light';
}

// File upload functions (enhanced)
function uploadFile($file, $subfolder = '', $allowedTypes = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
    
    if (!in_array($extension, $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . ($subfolder ? $subfolder . '/' : '') . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ($subfolder ? $subfolder . '/' : '') . $filename;
    }
    
    return false;
}

function deleteFile($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// Activity logging
function logActivity($adminId, $action, $tableAffected = null, $recordId = null, $oldValues = null, $newValues = null) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO activity_logs (admin_id, action, table_affected, record_id, old_values, new_values, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $adminId,
        $action,
        $tableAffected,
        $recordId,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Data access functions (merged)
function getStatistics() {
    $db = Database::getInstance()->getConnection();
    
    try {
        // Try the new approach first (assuming separate columns for each metric)
        $stmt = $db->query("
            SELECT 
                schools_supported,
                total_contributions,
                ongoing_projects,
                (SELECT COUNT(*) FROM partners WHERE status = 'active') as active_partners
            FROM statistics
            ORDER BY id DESC
            LIMIT 1
        ");
        
        $stats = $stmt->fetch();
        
        if ($stats) {
            return [
                'schools_supported' => $stats['schools_supported'] ?? 21,
                'total_contributions' => $stats['total_contributions'] ?? '₱12.7M',
                'ongoing_projects' => $stats['ongoing_projects'] ?? 24,
                'active_partners' => $stats['active_partners'] ?? 24
            ];
        }
    } catch (PDOException $e) {
        // If the query fails, try the old approach
        try {
            $activePartners = $db->query("SELECT COUNT(*) as count FROM partners WHERE status = 'active'")->fetch()['count'] ?? 24;
            
            return [
                'schools_supported' => 21,
                'total_contributions' => '₱12.7M',
                'ongoing_projects' => 24,
                'active_partners' => $activePartners
            ];
        } catch (PDOException $e) {
            // If all else fails, return default values
            return [
                'schools_supported' => 21,
                'total_contributions' => '₱12.7M',
                'ongoing_projects' => 24,
                'active_partners' => 24
            ];
        }
    }
}

function getImpactStories($limit = null, $featured = null) {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM impact_stories WHERE status = 'active'";
    $params = [];
    
    if ($featured !== null) {
        $sql .= " AND is_featured = ?";
        $params[] = $featured ? 1 : 0;
    }
    
    $sql .= " ORDER BY " . ($featured !== null ? "is_featured DESC, " : "") . "story_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getPartners($category = null) {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM partners WHERE status = 'active'";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY sort_order ASC, name ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getNewsUpdates($limit = null, $featured = null) {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM news_updates WHERE status = 'active'";
    $params = [];
    
    if ($featured !== null) {
        $sql .= " AND is_featured = ?";
        $params[] = $featured ? 1 : 0;
    }
    
    $sql .= " ORDER BY " . ($featured !== null ? "is_featured DESC, " : "") . "news_date DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Content management helpers
function getContentBySlug($table, $slug) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getContentById($table, $id) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Error handling
function logError($message, $context = []) {
    $logFile = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[{$timestamp}] {$message} {$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Cache helpers
function getCache($key, $expiry = 3600) {
    $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $expiry) {
        return unserialize(file_get_contents($cacheFile));
    }
    
    return null;
}

function setCache($key, $data) {
    $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
    file_put_contents($cacheFile, serialize($data), LOCK_EX);
}

function clearCache($key = null) {
    if ($key) {
        $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    } else {
        $files = glob(__DIR__ . '/cache/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Email helper
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "From: " . ADMIN_EMAIL . "\r\n";
        $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

// Security helpers
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function rateLimitCheck($identifier, $maxAttempts = 5, $timeWindow = 300) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    $key = md5($identifier);
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'first_attempt' => $now];
        return true;
    }
    
    $data = $_SESSION['rate_limit'][$key];
    
    if (($now - $data['first_attempt']) > $timeWindow) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'first_attempt' => $now];
        return true;
    }
    
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION['rate_limit'][$key]['count']++;
    return true;
}

// Start secure session
startSecureSession();
?>