<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Business Analytics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/headerStyle.css">
</head>

<body>
    <!-- Sticky Navigation Bar -->
    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="Laboratorium Business Analytics Logo">
            </div>
            <div class="lab-name-container">
                <div class="lab-name">Laboratorium Business Analytics</div>
                <div class="lab-tagline">Transforming Data into Decisions</div>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">Profil</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#">
                    Publikasi
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="berita.php">Berita</a></li>
                    <li><a class="dropdown-item" href="galeri.php">Gallery</a></li>
                    <li><a class="dropdown-item" href="newsInputService.php">News Input Service</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#">
                    Peminjaman Lab
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="infoPeminjaman.php">Informasi Laboratorium</a></li>
                    <li><a class="dropdown-item" href="tableBooking.php">Table Peminjaman</a></li>
                    <li><a class="dropdown-item" href="booking.php">Pemesanan Lab</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kontak.php">Kontak</a>
            </li>
        </ul>
        
        <button class="login-btn">Login</button>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Sticky Navigation Script
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.sticky-navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    // Add scroll event listener
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Add active class to current page link
    function setActiveLink() {
        const currentPath = window.location.pathname;
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            
            // Exact match
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
            
            // Handle parent pages
            if (currentPath.includes('pages/') && link.getAttribute('href') === './pages/' + currentPath.split('/').pop()) {
                link.classList.add('active');
            }
            
            // Handle index.php
            if (currentPath.endsWith('/') || currentPath.endsWith('index.php')) {
                if (link.getAttribute('href') === 'index.php') {
                    link.classList.add('active');
                }
            }
        });
        
        // Also check dropdown items
        dropdownItems.forEach(item => {
            if (item.getAttribute('href') === currentPath) {
                // Find parent dropdown and mark it as active
                const parentLink = item.closest('.nav-item.dropdown').querySelector('.nav-link');
                if (parentLink) {
                    parentLink.classList.add('active');
                }
            }
        });
    }
    
    // Initialize
    setActiveLink();
    
    // Login button functionality
    const loginBtn = document.querySelector('.login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            // You can change this to your login page URL
            window.location.href = './pages/login.php';
        });
    }
    
    // Mobile menu toggle (optional - add if needed)
    function initMobileMenu() {
        const mobileMenuBtn = document.createElement('button');
        mobileMenuBtn.className = 'mobile-menu-toggle';
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        mobileMenuBtn.style.cssText = `
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        `;
        
        navbar.appendChild(mobileMenuBtn);
        
        mobileMenuBtn.addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
        });
        
        // Responsive behavior
        function handleResize() {
            const navMenu = document.querySelector('.nav-menu');
            if (window.innerWidth <= 992) {
                mobileMenuBtn.style.display = 'block';
                navMenu.style.display = 'none';
                navMenu.style.flexDirection = 'column';
                navMenu.style.width = '100%';
                navMenu.style.marginTop = '20px';
            } else {
                mobileMenuBtn.style.display = 'none';
                navMenu.style.display = 'flex';
            }
        }
        
        window.addEventListener('resize', handleResize);
        handleResize();
    }
    
    // Initialize mobile menu if needed
    // initMobileMenu();
});
    </script>
</body>

</html>