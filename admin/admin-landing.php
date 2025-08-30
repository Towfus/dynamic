<?php
// Start session and check if user is logged in
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #60a5fa, #3b82f6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            margin-bottom: 25px;
        }
        
        .menu-label {
            padding: 0 20px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            letter-spacing: 0.5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 2px 12px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            transition: width 0.3s ease;
        }
        
        .sidebar-menu a:hover::before,
        .sidebar-menu a.active::before {
            width: 100%;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .sidebar-menu a i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .topbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .welcome-section {
            text-align: center;
            padding: 60px 30px;
            color: #1f2937;
        }
        
        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-subtitle {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 50px;
        }
        
        .buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .nav-button {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-decoration: none;
            color: #1f2937;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }
        
        .nav-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--button-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        .nav-button:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            color: white;
            border-color: var(--button-color);
        }
        
        .nav-button:hover::before {
            opacity: 1;
        }
        
        .nav-button.blue {
            --button-color: #3b82f6;
            --button-gradient: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .nav-button.green {
            --button-color: #10b981;
            --button-gradient: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }
        
        .nav-button.purple {
            --button-color: #8b5cf6;
            --button-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        }
        
        .nav-button.orange {
            --button-color: #f59e0b;
            --button-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .nav-button.red {
            --button-color: #ef4444;
            --button-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .nav-button.indigo {
            --button-color: #6366f1;
            --button-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .nav-button.teal {
            --button-color: #14b8a6;
            --button-gradient: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        }

        .nav-button.pink {
            --button-color: #ec4899;
            --button-gradient: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }
        
        .button-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--button-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .nav-button:hover .button-icon {
            transform: scale(1.1);
        }
        
        .button-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .button-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .nav-button:hover .button-description {
            color: rgba(255,255,255,0.9);
        }
        
        .hamburger {
            display: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        
        .hamburger:hover {
            background: rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .hamburger {
                display: block;
            }
            .topbar {
                padding: 15px 20px;
            }
            .welcome-title {
                font-size: 2rem;
            }
            .buttons-grid {
                grid-template-columns: 1fr;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">ISSHED</h2>
                    <p class="text-sm text-blue-200">Project ISSHED</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-label">Main</div>
                <a href="admin-landing.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Home</span>
                </a>

                 <a href="statistics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics Management</span>
                </a> 

                   <a href="impact-stories.php">
                    <i class="fas fa-users"></i>
                    <span>Impact Stories Management</span>
                </a>

                
                 <a href="partners.php">
                    <i class="fas fa-handshake"></i>
                    <span>Partners</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Proj ISSHED</div>

                <a href="timeline-management.php" >
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timeline Management</span>
                </a>

                <a href="project-highlights.php">
                    <i class="fas fa-star"></i>
                    <span>Project Highlights</span>
                </a>
                <a href="admin-document.php">
                    <i class="fas fa-file-pdf"></i>
                    <span>SMN Documents</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">System</div>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content" id="main-content">
        <div class="topbar">
            <div class="flex items-center">
                <div class="hamburger mr-4" id="hamburger">
                    <i class="fas fa-bars text-xl"></i>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="notification-badge cursor-pointer">
                    <i class="fas fa-bell text-gray-600 text-lg hover:text-blue-600 transition-colors"></i>
                </div>
                <div class="user-profile">
                    <div class="user-avatar">
                        A
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">Admin User</div>
                        <div class="text-xs text-gray-500">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="welcome-section">
            <h1 class="welcome-title">Welcome Back, Admin!</h1>
            <p class="welcome-subtitle">Choose an option below to manage your DepEd General Trias system</p>
        </div>

        <div class="buttons-grid">
            <a href="statistics.php" class="nav-button blue">
                <div class="button-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="button-title">Statistics Management</div>
                <div class="button-description">View and manage system statistics, analytics, and performance metrics</div>
            </a>

            <a href="impact-stories.php" class="nav-button green">
                <div class="button-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="button-title">Impact Stories Management</div>
                <div class="button-description">Create, edit, and manage impact stories and success narratives</div>
            </a>

            <a href="admin-document.php" class="nav-button purple">
                <div class="button-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="button-title">SMN Documents</div>
                <div class="button-description">Upload, organize, and manage SMN educational documents and materials</div>
            </a>

            <a href="news_updates.php" class="nav-button orange">
                <div class="button-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="button-title">News Updates Management</div>
                <div class="button-description">Create and manage news updates, announcements, and notifications</div>
            </a>

            <a href="partners.php" class="nav-button indigo">
                <div class="button-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="button-title">Partners</div>
                <div class="button-description">Manage partner organizations and collaboration relationships</div>
            </a>

            <a href="project-highlights.php" class="nav-button red">
                <div class="button-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="button-title">Project Highlights</div>
                <div class="button-description">Showcase and manage key project achievements and milestones</div>
            </a>

            <a href="timeline-management.php" class="nav-button teal">
                <div class="button-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="button-title">Timeline Management</div>
                <div class="button-description">Create and manage project timelines, schedules, and important dates</div>
            </a>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburger');
            
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Add loading animation for buttons
        window.addEventListener('load', function() {
            const buttons = document.querySelectorAll('.nav-button');
            buttons.forEach((button, index) => {
                button.style.opacity = '0';
                button.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    button.style.transition = 'all 0.6s ease';
                    button.style.opacity = '1';
                    button.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>
</body>
</html>