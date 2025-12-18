<?php
session_start();

// --- 1. KONEKSI DATABASE & LOGIKA SESSION ---
require_once '../admin/config/database.php';
$db = (new Database())->getConnection();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// Variabel untuk digunakan dalam JS (Penting untuk Path API)
$apiPath = $isLoggedIn ? '../admin/api/berita.php' : '../api/berita.php'; 

// CATATAN PENTING: Jika API/berita.php berada di level yang sama dengan pages/berita.php, 
// gunakan $apiPath = 'api/berita.php';
// Jika API/berita.php ada di admin/api/, dan pages/berita.php ada di pages/, 
// maka path yang benar dari pages/ adalah ../admin/api/berita.php.
// Saya berasumsi struktur anda: /pages/berita.php dan /admin/api/berita.php

// --- 2. AMBIL DATA SETTING (Logo & Nama Lab) ---
$logoSrc = '../assets/images/logo.png';
$namaLabText = 'Laboratorium Business Analytics'; // Default text

try {
    // PERBAIKAN: Ambil kolom 'value' (untuk teks) DAN 'file_path' (untuk gambar)
    $stmt = $db->query("SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'nama_lab')");
    $resultRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kita susun ulang array-nya biar gampang dipanggil berdasarkan key
    $settings = [];
    foreach ($resultRaw as $row) {
        $settings[$row['key']] = $row;
    }

    // 1. Set Logo (Ambil dari kolom file_path)
    if (!empty($settings['logo']['file_path'])) {
        $logoSrc = '../admin/' . $settings['logo']['file_path'];
    }

    // 2. Set Nama Lab (Ambil dari kolom value - SESUAI DATABASE KAMU)
    if (!empty($settings['nama_lab']['value'])) {
        $namaLabText = $settings['nama_lab']['value'];
    }
    
} catch (Exception $e) { 
    /* Ignore error agar web tetap jalan pakai default */
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/beritaStyle.css?v=<?php echo time(); ?>">
</head>

<body>
    
    <nav class="sticky-navbar" id="mainNavbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Business Analytics Logo">
            </div>
            <div class="lab-name-container">
                <div class="lab-name"><?php echo htmlspecialchars($namaLabText); ?></div>
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
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

    <section class="hero-section">
        <div class="hero-rectangle">
            </div>
        <div class="hero-container">
            <div class="hero-inner-rectangle">
                </div>
            <div class="hero-text-content">
                <div class="about-overlay">
                    <span class="about-text">News</span>
                </div>
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">The Laboratory</span>
                        <span class="title-line business-analytics">Business Analytics</span>
                    </h1>
                    <p class="hero-detailed-description">
                        Dapatkan informasi terbaru, prestasi mahasiswa, dan pengumuman penting seputar Laboratorium Business Analytics Politeknik Negeri Malang.
                    </p>
                    <div class="register-btn-container">
                        <button class="register-btn" onclick="window.location.href='newsInputService.php'">
                            <span class="btn-text">
                                news input service
                                <i class="fas fa-arrow-up arrow-icon"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <nav class="section-navbar" id="sectionNavbar">
        <div class="section-nav-rectangle">
            <div class="section-nav-container">
                <ul class="section-nav-menu">
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#latest-news">
                            <span class="nav-text">Newst Latest</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#prestasi">
                            <span class="nav-text">Prestasi</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#announcement">
                            <span class="nav-text">Announcement</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="latest-news" class="news-section">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Update Informasi Terkini</p>
                <h2 class="news-title">Latest News Lab Business Analytics</h2>
            </div>

            <div class="news-grid" id="container-latest-news">
                <div class="text-center w-100 py-5 loading-spinner-news">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="prestasi" class="news-section">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Pencapaian Membanggakan</p>
                <h2 class="news-title">Prestasi Lab Business Analytics</h2>
            </div>

            <div class="news-grid" id="container-prestasi">
                <div class="text-center w-100 py-5 loading-spinner-news">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="announcement" class="news-section">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Pengumuman Penting</p>
                <h2 class="news-title">Announcement Lab Business Analytics</h2>
            </div>

            <div class="news-grid" id="container-announcement">
                <div class="text-center w-100 py-5 loading-spinner-news">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Ambil path API dari PHP
        const API_URL = '<?php echo $apiPath; ?>';
        const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            fetchBerita();
            
            // Inisialisasi UI
            new NavbarScrollBehavior();
            new CompactNavbar();
        });

        function fetchBerita() {
            fetch(API_URL) 
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        renderBerita(result.data);
                    } else {
                        // Jika API menolak (kemungkinan UNAUTHORIZED)
                        let message = result.message || 'API menolak akses (Kemungkinan butuh login untuk mengakses data ini).';
                        if (!IS_LOGGED_IN) {
                            message += ' Silakan login atau periksa izin API Anda.';
                        }
                        showErrorState(message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    showErrorState('Gagal memuat berita. Periksa koneksi internet atau jalur API.');
                });
        }

        function showEmptyState() {
            const msg = '<p class="text-center text-muted py-5">Belum ada berita yang tersedia.</p>';
            document.getElementById('container-latest-news').innerHTML = msg;
            document.getElementById('container-prestasi').innerHTML = msg;
            document.getElementById('container-announcement').innerHTML = msg;
        }

        function showErrorState(message) {
            const msg = `<p class="text-center text-danger py-5">
                <i class="fas fa-exclamation-triangle me-2"></i> ${message}
            </p>`;
            document.getElementById('container-latest-news').innerHTML = msg;
            document.getElementById('container-prestasi').innerHTML = msg;
            document.getElementById('container-announcement').innerHTML = msg;
        }

        function renderBerita(data) {
            const containerLatest = document.getElementById('container-latest-news');
            const containerPrestasi = document.getElementById('container-prestasi');
            const containerAnnouncement = document.getElementById('container-announcement');

            containerLatest.innerHTML = '';
            containerPrestasi.innerHTML = '';
            containerAnnouncement.innerHTML = '';

            let hasLatest = false;
            let hasPrestasi = false;
            let hasAnnouncement = false;

            if (data.length > 0) {
                data.forEach(item => {
                    const cardHTML = createNewsCard(item);
                    const kategori = item.kategori ? item.kategori.toLowerCase() : '';

                    if (kategori.includes('news') || kategori.includes('latest')) {
                        containerLatest.insertAdjacentHTML('beforeend', cardHTML);
                        hasLatest = true;
                    } else if (kategori.includes('prestasi')) {
                        containerPrestasi.insertAdjacentHTML('beforeend', cardHTML);
                        hasPrestasi = true;
                    } else if (kategori.includes('announcement') || kategori.includes('pengumuman')) {
                        containerAnnouncement.insertAdjacentHTML('beforeend', cardHTML);
                        hasAnnouncement = true;
                    } else {
                        // Default masuk Latest jika kategori tidak spesifik
                        containerLatest.insertAdjacentHTML('beforeend', cardHTML);
                        hasLatest = true;
                    }
                });
            }

            // Tampilkan pesan kosong jika tidak ada data di kategori tertentu
            if (!hasLatest) containerLatest.innerHTML = '<p class="text-center text-muted">Belum ada berita terbaru.</p>';
            if (!hasPrestasi) containerPrestasi.innerHTML = '<p class="text-center text-muted">Belum ada data prestasi.</p>';
            if (!hasAnnouncement) containerAnnouncement.innerHTML = '<p class="text-center text-muted">Belum ada pengumuman.</p>';
        }

        function createNewsCard(item) {
            let formattedDate = '-';
            if (item.tanggal_upload) {
                const dateObj = new Date(item.tanggal_upload);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                // Menggunakan 'id-ID' untuk format tanggal Indonesia
                formattedDate = dateObj.toLocaleDateString('id-ID', options);
            }

            // Path Gambar: Disesuaikan dengan asumsi API berada di admin/
            // Path dari DB: uploads/berita/file.jpg
            // Path yang dibutuhkan dari pages/: ../admin/uploads/berita/file.jpg
            const imgSrc = item.file_path ? `../admin/${item.file_path}` : '../assets/img/untitled.jpeg';
            
            const userName = item.nama_user || 'Admin Lab';
            const userRole = item.role_user || 'Contributor';

            // Menggunakan ringkasan, jika konten tidak ada
            const excerpt = item.ringkasan || (item.konten ? item.konten.substring(0, 150) + (item.konten.length > 150 ? '...' : '') : 'Deskripsi tidak tersedia.');

            return `
                <div class="news-card">
                    <div class="news-image">
                        <img src="${imgSrc}" alt="${item.judul}" onerror="this.src='../assets/img/untitled.jpeg'">
                    </div>
                    <div class="news-content">
                        <h3 class="news-card-title">${item.judul}</h3>
                        <p class="news-excerpt">
                            ${excerpt}
                        </p>
                        <div class="news-meta">
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span class="meta-text">${userName}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <span class="meta-text">${formattedDate}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fa fa-id-badge"></i>
                                <span class="meta-text">${userRole}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // --- SCRIPT UI INTERAKSI (SAMA SEPERTI ASLI) ---
        function toggleMenu() {
            document.getElementById('navMenu').classList.toggle('active');
        }

        class NavbarScrollBehavior {
            // ... (Kode NavbarScrollBehavior) ...
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                this.sectionNavbar = document.getElementById('sectionNavbar');
                this.lastScrollY = window.scrollY;
                this.scrollThreshold = 100;
                this.isMainNavHidden = false;
                if(this.mainNavbar) this.init();
            }
            init() {
                window.addEventListener('scroll', () => { this.handleScroll(); });
            }
            handleScroll() {
                const currentScrollY = window.scrollY;
                const scrollDirection = currentScrollY > this.lastScrollY ? 'down' : 'up';
                if (scrollDirection === 'down' && currentScrollY > this.scrollThreshold && !this.isMainNavHidden) {
                    this.mainNavbar.classList.add('hidden');
                    this.sectionNavbar.classList.add('main-sticky');
                    document.body.classList.add('nav-main-hidden');
                    this.isMainNavHidden = true;
                } else if ((scrollDirection === 'up' || currentScrollY <= this.scrollThreshold) && this.isMainNavHidden) {
                    this.mainNavbar.classList.remove('hidden');
                    this.sectionNavbar.classList.remove('main-sticky');
                    document.body.classList.remove('nav-main-hidden');
                    this.isMainNavHidden = false;
                }
                this.lastScrollY = currentScrollY;
            }
        }

        class CompactNavbar {
            // ... (Kode CompactNavbar) ...
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                if(this.mainNavbar) this.init();
            }
            init() {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) this.mainNavbar.classList.add('compact');
                    else this.mainNavbar.classList.remove('compact');
                });
            }
        }
        
        // Perbaikan Scroll ke Section
        document.addEventListener('DOMContentLoaded', function () {
            const sectionLinks = document.querySelectorAll('.section-nav-link');

            sectionLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    sectionLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    if (targetSection) {
                        const navbarHeight = document.querySelector('.sticky-navbar').offsetHeight;
                        const sectionNavHeight = document.querySelector('.section-navbar').offsetHeight;
                        const offsetTop = targetSection.offsetTop - navbarHeight - sectionNavHeight;

                        window.scrollTo({
                            top: Math.max(0, offsetTop),
                            behavior: 'smooth'
                        });
                    }
                });
            });

            window.addEventListener('scroll', function () {
                const sections = document.querySelectorAll('section[id]');
                const scrollPos = window.scrollY + 250; 

                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;
                    const sectionId = section.getAttribute('id');

                    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                        sectionLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${sectionId}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>