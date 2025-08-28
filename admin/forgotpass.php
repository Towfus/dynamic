<?php
session_start();

// Initialize CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$error_message = "";
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid form submission";
    } else {
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($email) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All fields are required";
        } elseif (!preg_match('/@deped\.gov\.ph$/', $email)) {
            $error_message = "Only @deped.gov.ph emails are allowed";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match";
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                $error_message = "Email not found in our system";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $hashed_password, $email);
                
                if ($update_stmt->execute()) {
                    $success_message = "Password updated successfully! You can now login with your new password.";
                    // Clear form
                    $_POST = array();
                } else {
                    $error_message = "Error updating password: " . $conn->error;
                }
                $update_stmt->close();
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DepEd General Trias City</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .password-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .password-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .logo-section {
            margin-bottom: 30px;
        }
        
        .logo-section div {
            font-weight: 600;
            color: var(--dark);
        }
        
        .logo-section div:first-child {
            font-size: 1.5rem;
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .logo-section div:last-child {
            font-size: 1rem;
            color: var(--gray);
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: white;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            background-color: #1a252f;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .options {
            margin-top: 25px;
            font-size: 0.9rem;
        }
        
        .options a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .options a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .error-message {
            background-color: #fdecea;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: var(--gray);
        }
        
        @media (max-width: 576px) {
            .password-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="logo-section">
            <div>SCHOOLS DIVISION OFFICE</div>
            <div>GENERAL TRIAS CITY</div>
        </div>
        
        <h1>Reset Your Password</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
            <div class="options">
                <a href="admin_login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="yourname@deped.gov.ph" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" 
                           placeholder="Enter new password" required>
                    <i class="fas fa-eye password-toggle" id="toggleNewPassword"></i>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Confirm your new password" required>
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
            
            <div class="options">
                <a href="admin_login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('new_password');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>