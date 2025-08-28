<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | DepEd Schools</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a90e2;
            --primary-dark: #357abd;
            --secondary: #f8f9fa;
            --text: #333;
            --error: #e74c3c;
            --success: #2ecc71;
            --border-radius: 12px;
            --box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            max-width: 900px;
        }
        
        .registration-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            width: 100%;
            box-shadow: var(--box-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .registration-header {
            margin-bottom: 1.5rem;
        }
        
        .registration-header h2 {
            color: var(--text);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .registration-header p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.2rem;
            text-align: left;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .input-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .btn-register:hover {
            background: var(--primary-dark);
        }
        
        .error-message {
            background: #ffeded;
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: #effff0;
            color: var(--success);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .registration-footer {
            margin-top: 1.5rem;
            font-size: 14px;
            color: #666;
        }
        
        .registration-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .registration-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .database-setup {
            background: #fff;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .database-setup h3 {
            margin-bottom: 15px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .database-setup code {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            display: block;
            overflow-x: auto;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.5;
            border-left: 4px solid var(--primary);
        }
        
        .steps {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .steps li {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .registration-container, .database-setup {
                width: 100%;
            }
        }
        
        .btn-loading {
            position: relative;
            color: transparent;
        }
        
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: #e9ecef;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: var(--primary);
            color: white;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('register')">Admin Registration</div>
            <div class="tab" onclick="switchTab('database')">Database Setup</div>
        </div>
        
        <div id="register-tab" class="tab-content active">
            <div class="registration-container">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                
                <div class="registration-header">
                    <h2>One-Time Admin Registration</h2>
                    <p>Create your administrator account with the preset credentials</p>
                </div>
                
                <div class="success-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Default credentials: Username - <strong>12345</strong>, Password - <strong>12345</strong></span>
                </div>
                
                <form method="POST" id="registrationForm">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Enter username (12345)" required value="12345" readonly>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Enter password (12345)" required value="12345" readonly>
                        <span class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-signature"></i>
                        <input type="text" name="fullname" placeholder="Enter your full name" required>
                    </div>
                    
                    <button type="submit" class="btn-register" id="registerButton">
                        <i class="fas fa-user-plus"></i>
                        <span>Register Admin Account</span>
                    </button>
                </form>
                
                <div class="registration-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
        
        <div id="database-tab" class="tab-content">
            <div class="database-setup">
                <h3><i class="fas fa-database"></i> Database Table Setup</h3>
                <p>Run this SQL code in your database to create the necessary table for the admin registration:</p>
                
                <code>
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
                </code>
                
                <p><strong>Instructions:</strong></p>
                <ol class="steps">
                    <li>Open your PHPMyAdmin or database management tool</li>
                    <li>Select your database (deped_schools)</li>
                    <li>Go to the SQL tab</li>
                    <li>Copy and paste the above SQL code</li>
                    <li>Click "Go" to execute the query</li>
                    <li>The admin_users table will be created in your database</li>
                </ol>
                
                <div class="success-message">
                    <i class="fas fa-lightbulb"></i>
                    <span>After creating the table, you can use the registration form to create your admin account.</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activate selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Activate clicked tab button
            event.currentTarget.classList.add('active');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Form submission handling
            const registrationForm = document.getElementById('registrationForm');
            const registerButton = document.getElementById('registerButton');
            
            registrationForm.addEventListener('submit', function() {
                // Show loading state
                registerButton.classList.add('btn-loading');
                registerButton.disabled = true;
                
                // In a real application, this would be handled by PHP
                // For demonstration, we'll simulate a successful registration
                setTimeout(function() {
                    alert('Admin account created successfully! You can now login with username: 12345 and password: 12345');
                    registerButton.classList.remove('btn-loading');
                    registerButton.disabled = false;
                }, 2000);
                
                // Prevent actual form submission for this demo
                event.preventDefault();
            });
        });
    </script>
</body>
</html>