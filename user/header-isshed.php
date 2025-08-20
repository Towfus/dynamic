<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO General Trias - Partnership and Linkages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --sdo-primary: #800000;
            --sdo-secondary: #a52a2a;
            --sdo-accent: #006400;
            --sdo-light: #f8f9fa;
            --sdo-dark: #343a40;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--sdo-primary) 0%, var(--sdo-secondary) 100%);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
        }
        
        .navbar-brand {
            padding: 0.5rem 0;
            margin-right: 2rem;
        }
        
        .custom-green {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .navbar-brand .text-muted {
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link:focus {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .nav-link.fw-bold {
            font-weight: 600 !important;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: rgba(128, 0, 0, 0.1);
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.9)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        .divider {
            height: 24px;
            border-left: 1px solid rgba(255, 255, 255, 0.3);
            margin: 0 0.5rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                padding: 1rem 0;
            }
            
            .nav-item {
                margin: 0.2rem 0;
            }
            
            .divider {
                display: none;
            }
        }
        
        /* Animation for dropdown */
        .dropdown-menu {
            animation: fadeIn 0.3s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Navigation Header -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark">
        <div class="container-fluid">
            <!-- Brand with improved styling -->
            <div class="navbar-brand d-flex flex-column align-items-start">
                <span class="custom-green fw-bold">SDO General Trias</span>
                <span class="text-muted fs-6">Partnership and Linkages</span>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGenTri" 
                    aria-controls="navbarGenTri" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarGenTri">
                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                    <!-- Visual divider for desktop -->
                    <li class="nav-item d-none d-lg-flex align-items-center px-2">
                        <div class="divider"></div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="proj-isshed.php">Project ISSHED</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="proj-isshed.php#adopt-a-school">Adopt-a-School</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="proj-isshed.php#brigada-eskwela">Brigada Eskwela</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="taxIncentives.php">Tax Incentives</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            More
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="proj-isshed.php#be-our-partner">
                                <i class="bi bi-handshake me-2"></i>Be Our Partner
                            </a></li>
                            <li><a class="dropdown-item" href="index.php#news-partnership-updates">
                                <i class="bi bi-newspaper me-2"></i>News & Updates
                            </a></li>
                            <li><a class="dropdown-item" href="smn-forms.php">
                                <i class="bi bi-file-earmark-text me-2"></i>SMN Forms
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#contact">
                                <i class="bi bi-envelope me-2"></i>Contact Us
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Spacer to account for fixed navbar -->
    <div style="height: 80px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if(this.getAttribute('href') !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if(target) {
                        const navHeight = document.querySelector('.navbar').offsetHeight;
                        window.scrollTo({
                            top: target.offsetTop - navHeight,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
        
        // Highlight active nav link based on scroll position
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if(pageYOffset >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if(link.getAttribute('href').includes(current)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>