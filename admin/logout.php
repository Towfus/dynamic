<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Determine where to redirect based on request origin
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$is_admin_request = strpos($referer, '/admin/') !== false;

// Redirect to appropriate login page
if ($is_admin_request) {
    header("Location: /GITHUB/dynamic/admin/login.php");
} else {
    header("Location:/GITHUB/dynamic/admin/login.php"); // Adjust if your user login has different path
}
exit();
?>