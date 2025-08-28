<?php
session_start();
$error = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "deped_schools");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check user credentials
    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Login success - set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Redirect based on role
            header("Location: " . ($user['is_admin'] ? "admin_dashboard.php" : "user_dashboard.php"));
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 300px; margin: 0 auto; }
        input { margin: 5px 0; width: 100%; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="admin_register.php">Register here</a></p>
    </div>
</body>
</html>