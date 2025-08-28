<?php
session_start();
$error = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "sdo_gentri");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* --- Create users table if it doesn't exist --- */
$createTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 1
)";
$conn->query($createTable);

/* --- Insert default admin if not exists --- */
$defaultAdmin = "admin";
$defaultPassword = "12345";

// Hash password
$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $defaultAdmin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)");
    $insert->bind_param("ss", $defaultAdmin, $hashedPassword);
    $insert->execute();
}

/* --- Handle login --- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            // Always redirect to admin
            header("Location: admin-landing.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Username not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 300px; margin: 0 auto; margin-top: 100px; }
        input { margin: 5px 0; width: 100%; padding: 8px; }
        .error { color: red; }
        button { padding: 8px; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Default Admin: <b>admin</b> / <b>12345</b></p>
    </div>
</body>
</html>
