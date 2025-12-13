<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
// Role default jika tidak ada session
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Business Analytics</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;1,700;1,800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/infoPeminjamanStyle.css?v=<?php echo time(); ?>">
</head>

<body>

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

        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-menu" id="navMenu">
            <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php">Profil</a></li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>Publikasi</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="berita.php">Berita</a></li>
                    <li><a class="dropdown-item" href="galeri.php">Gallery</a></li>
                    <li><a class="dropdown-item" href="newsInputService.php">News Input Service</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>Peminjaman Lab</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="infoPeminjaman.php">Informasi Laboratorium</a></li>
                    <li><a class="dropdown-item" href="tableBooking.php">Table Peminjaman</a></li>
                    <li><a class="dropdown-item" href="booking.php">Pemesanan Lab</a></li>
                </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
            
            <li class="nav-item mobile-auth-section">
                <?php if ($isLoggedIn): ?>
                    <div class="mobile-user-profile-modern">
                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <div class="mobile-avatar-modern">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=0D8ABC&color=fff&size=128" alt="User Avatar">
                            </div>
                            <div class="mobile-info-modern">
                                <span class="greeting-text">Halo,</span>
                                <span class="username-text"><?php echo htmlspecialchars($userName); ?></span>
                            </div>
                        </div>
                        <a href="../admin/logout.php" class="logout-btn-modern" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mobile-login-btn">
                        <a class="nav-link login-link" href="../admin/login.php">Login</a>
                    </div>
                <?php endif; ?>
            </li>
        </ul>
        
        <?php if ($isLoggedIn): ?>
            <div class="desktop-user-action">
                
                <a class="user-profile-link" href="profile.php" title="Lihat Profil Saya">
                    <div class="text-end me-2">
                        <div class="user-name-label"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="user-role-label"><?php echo htmlspecialchars($userRole); ?></div>
                    </div>
                    <div class="avatar-circle">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random&size=128" alt="User Avatar">
                    </div>
                </a>

                <a class="desktop-logout-btn" href="../admin/logout.php" title="Keluar / Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>

            </div>
        <?php else: ?>
            <button class="login-btn desktop-login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
        <?php endif; ?>

    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-rectangle">
            <!-- Background rectangle dengan opacity -->
        </div>
        <div class="hero-container">
            <div class="hero-inner-rectangle">
                <!-- Inner rectangle placeholder -->
            </div>
            <div class="hero-text-content">
                <div class="about-overlay">
                    <span class="about-text">Borrowing</span>
                </div>
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">The Laboratory</span>
                        <span class="title-line business-analytics">Business Analytics</span>
                    </h1>
                    <p class="hero-detailed-description">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore et dolore magna aliqua.
                        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
                        commodo consequat.
                        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                        pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.
                    </p>
                    <!-- Tombol Register For Laboratory Booking -->
                    <div class="register-btn-container">
                        <button class="register-btn">
                            <span class="btn-text">
                                Register For Laboratory Booking
                                <i class="fas fa-arrow-up arrow-icon"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Laboratory Business Analytics Section -->
<section class="info-section">
    <div class="info-container">
        <h2 class="info-title">Laboratory Business Analytics</h2>
        <div class="info-content">
            <div class="info-text">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
            
            <div class="info-block">
                <h3 class="info-subtitle">Lab Facilities</h3>
                <div class="info-text">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                </div>
                
                <!-- Carousel Container -->
                <div class="facilities-carousel-container">
                    <div class="carousel-track" id="facilitiesCarousel">
                        <!-- Kotak fasilitas akan ditambahkan melalui JavaScript -->
                    </div>
                    <div class="carousel-controls">
                        <button class="carousel-prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lab Borrowing Rules Section -->
<section class="borrowing-rules-section">
    <div class="rules-container">
        <h2 class="rules-title">Lab Borrowing Rules</h2>
        <div class="rules-content">
            <div class="rules-list">
                <div class="rule-item">
                    <div class="rule-number">1</div>
                    <div class="rule-text">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
                    </div>
                </div>
                <div class="rule-item">
                    <div class="rule-number">2</div>
                    <div class="rule-text">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                    </div>
                </div>
                <div class="rule-item">
                    <div class="rule-number">3</div>
                    <div class="rule-text">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                    </div>
                </div>
                <div class="rule-item">
                    <div class="rule-number">4</div>
                    <div class="rule-text">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                    </div>
                </div>
                <div class="rule-item">
                    <div class="rule-number">5</div>
                    <div class="rule-text">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
function toggleMenu() {
    const navMenu = document.getElementById('navMenu');
    const hamburgerIcon = document.querySelector('.hamburger i');
    navMenu.classList.toggle('active');
    
    if (navMenu.classList.contains('active')) {
        hamburgerIcon.classList.remove('fa-bars');
        hamburgerIcon.classList.add('fa-times');
    } else {
        hamburgerIcon.classList.remove('fa-times');
        hamburgerIcon.classList.add('fa-bars');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // --- KONFIGURASI API ---
    const API_URL = '/PBL_BA/admin/api/fasilitas.php'; 

    const carousel = document.getElementById('facilitiesCarousel');
    const carouselContainer = document.querySelector('.facilities-carousel-container');
    
    let facilities = [];
    let currentIndex = 0;
    let isAnimating = false;
    let autoPlayInterval;

    // --- FETCH DATA ---
    fetch(API_URL)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
            return response.json();
        })
        .then(result => {
            if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                // Mapping Data
                facilities = result.data.map(item => ({
                    name: item.nama_fasilitas,
                    icon: item.icon ? item.icon.trim() : 'bi bi-box' // Trim spasi & default icon
                }));

                currentIndex = Math.floor(facilities.length / 2);
                initCarousel();
            } else {
                showEmptyState("Belum ada data fasilitas.");
            }
        })
        .catch(error => {
            console.error(error);
            showEmptyState("Gagal memuat data fasilitas.");
        });

    function showEmptyState(msg) {
        if (carousel) carousel.innerHTML = `<div class="text-center p-5 text-muted w-100">${msg}</div>`;
    }

    // --- CAROUSEL LOGIC ---
    function initCarousel() {
        if (!carousel) return;
        createFacilityItems();
        createProgressIndicator();
        updateCarousel(true);
        startAutoPlay();
        setupControls();
    }

    function createFacilityItems() {
        carousel.innerHTML = '';
        facilities.forEach((facility, index) => {
            const facilityItem = document.createElement('div');
            facilityItem.className = 'facility-item';
            facilityItem.setAttribute('data-index', index);
            
            // LOGIKA DETEKSI ICON
            let iconClass = facility.icon;
            
            // Jika user hanya input 1 kata (misal: "wifi"), kita tambah prefix default
            if (!iconClass.includes(' ')) {
                // Cek awalan untuk menebak library
                if (iconClass.startsWith('bi-')) iconClass = 'bi ' + iconClass;
                else if (iconClass.startsWith('fa-')) iconClass = 'fas ' + iconClass;
                else iconClass = 'fas fa-' + iconClass.replace(/^fa-/, ''); // Default ke FontAwesome
            }
            // Jika database sudah lengkap "bi bi-wifi" atau "fa-solid fa-snowflake", biarkan saja.

            facilityItem.innerHTML = `
                <i class="${iconClass} facility-icon"></i>
                <div class="facility-name">${facility.name}</div>
            `;
            carousel.appendChild(facilityItem);
        });
    }

    function createProgressIndicator() {
        const oldInd = document.querySelector('.carousel-progress');
        if(oldInd) oldInd.remove();

        const progressContainer = document.createElement('div');
        progressContainer.className = 'carousel-progress';
        
        facilities.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = `progress-dot ${index === currentIndex ? 'active' : ''}`;
            dot.addEventListener('click', () => {
                if (!isAnimating && index !== currentIndex) {
                    isAnimating = true;
                    currentIndex = index;
                    updateCarousel();
                    setTimeout(() => isAnimating = false, 600);
                }
            });
            progressContainer.appendChild(dot);
        });
        if(carouselContainer) carouselContainer.appendChild(progressContainer);
    }

    function updateCarousel(instant = false) {
        const items = carousel.querySelectorAll('.facility-item');
        if (items.length === 0) return;

        items.forEach((item, index) => {
            if (instant) item.style.transition = 'none';
            const len = facilities.length;
            const relativePos = (index - currentIndex + len) % len;
            
            item.className = 'facility-item'; 
            
            if (relativePos === 0) item.classList.add('center', 'active');
            else if (relativePos === 1) item.classList.add('side', 'right-side');
            else if (relativePos === len - 1) {
                item.classList.add('side', 'left-side');
                if (!instant) item.classList.add('leaving');
            } else if (relativePos === 2) item.classList.add('outer', 'far-right', 'entering');
            else if (relativePos === len - 2) item.classList.add('outer', 'far-left', 'entering');
            else item.classList.add('outer');

            if (instant) setTimeout(() => { item.style.transition = ''; }, 50);
        });
        
        // Update Dots Active State
        const dots = document.querySelectorAll('.progress-dot');
        dots.forEach((dot, idx) => dot.classList.toggle('active', idx === currentIndex));
    }

    function setupControls() {
        const nextBtn = document.querySelector('.carousel-next');
        const prevBtn = document.querySelector('.carousel-prev');

        const move = (dir) => {
            if (isAnimating) return;
            stopAutoPlay();
            isAnimating = true;
            currentIndex = (currentIndex + dir + facilities.length) % facilities.length;
            updateCarousel();
            setTimeout(() => isAnimating = false, 600);
            startAutoPlay();
        };

        if (nextBtn) nextBtn.onclick = () => move(1);
        if (prevBtn) prevBtn.onclick = () => move(-1);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') move(-1);
            if (e.key === 'ArrowRight') move(1);
        });
        
        if (carousel) {
            carousel.addEventListener('mouseenter', stopAutoPlay);
            carousel.addEventListener('mouseleave', startAutoPlay);
        }
    }

    function startAutoPlay() {
        stopAutoPlay();
        autoPlayInterval = setInterval(() => {
            const nextBtn = document.querySelector('.carousel-next');
            if (nextBtn && !isAnimating && !document.hidden) nextBtn.click();
        }, 4000);
    }

    function stopAutoPlay() {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
    }

    // Scroll Reveal for Rules
    const ruleItems = document.querySelectorAll('.rule-item');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    }, { threshold: 0.1 });
    
    ruleItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        item.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(item);
    });
});
</script>

</body>

</html>