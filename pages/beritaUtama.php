<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
// Role default jika tidak ada session
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// --- 1. KONEKSI DATABASE & LOGIKA UTAMA (DI ATAS HTML) ---
// Sesuaikan path ini jika file ini ada di dalam folder 'public' atau 'pages'
// Gunakan __DIR__ agar path relatifnya aman
$dbPath = __DIR__ . '/../admin/config/database.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    // Fallback jika path beda
    $dbPathAlternative = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php';
    if (file_exists($dbPathAlternative)) {
        require_once $dbPathAlternative;
    } else {
        die("Error: Config database tidak ditemukan. Cek path file.");
    }
}

$db = (new Database())->getConnection();

// Logika User Session
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// --- 2. AMBIL DATA SETTING (Logo & Maskot) ---
$logoSrc = '../assets/images/logo.png';
$maskotSrc = '../assets/img/MaskotLab.png';

try {
    $stmt = $db->query("SELECT key, file_path FROM settings WHERE key IN ('logo', 'maskot')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if (!empty($settings['logo'])) $logoSrc = '../admin/' . $settings['logo'];
    if (!empty($settings['maskot'])) $maskotSrc = '../admin/' . $settings['maskot'];
} catch (Exception $e) { /* Ignore */
}

?>

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
    <link rel="stylesheet" href="../assets/css/beritaUtamaStyle.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Sticky Navigation Bar -->
    <nav class="sticky-navbar" id="mainNavbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Business Analytics Logo">
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

    <!-- Content Section -->
    <div class="content-container">
        <div class="row">
            <!-- Main Content (Left) -->
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="berita.php">Berita</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Berita Terkini</li>
                    </ol>
                </nav>
                
                <h1 class="news-title">Lorem Ipsum is simply dummy text of the printing and typesetting industry</h1>
                
                <div class="news-meta">
                    <span><i class="fas fa-user"></i> Username</span>
                    <span><i class="fas fa-calendar-alt"></i> 31 Januari 2012</span>
                    <span><i class="fa fa-id-badge"></i> Alumni Polinema</span>
                </div>
                
                <img src="./img/MissingPicturee.jpg" alt="Gambar Berita" class="news-image">
                
                <div class="news-content">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book. 
                        It has survived not only five centuries, but also the leap into electronic typesetting, 
                        remaining essentially unchanged.
                    </p>
                    
                    <p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, 
                        and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                    </p>
                    
                    <p>Contrary to popular belief, Lorem Ipsum is not simply random text. 
                        It has roots in a piece of classical Latin literature from 45 BC, 
                        making it over 2000 years old. Richard McClintock, 
                        a Latin professor at Hampden-Sydney College in Virginia, 
                        looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, 
                        and going through the cites of the word in classical literature, discovered the undoubtable source.
                    </p>
                    
                    <p>Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" 
                        (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, 
                        very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", 
                        comes from a line in section 1.10.32.
                    </p>
                </div>

                <img src="./img/MissingPicturee.jpg" alt="Gambar Berita" class="news-image">
                
                <div class="news-content">
                    <p>
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, 
                        totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. 
                        Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, 
                        sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. 
                        Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, 
                        sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. 
                        Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, 
                        nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, 
                        vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
                    </p>
                </div>
            </div>
            
            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Prestasi</h3>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 1" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Prestasi Mahasiswa dalam Kompetisi Data Science Nasional</a>
                            </h4>
                            <p class="news-sidebar-desc">Mahasiswa Laboratorium Business Analytics berhasil meraih juara pertama dalam kompetisi data science tingkat nasional.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 15 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 2" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Penghargaan untuk Inovasi dalam Analisis Bisnis</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics menerima penghargaan atas inovasi dalam pengembangan solusi analisis bisnis.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 10 Feb 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 3" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Laboratorium Business Analytics Raih Akreditasi A</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics berhasil meraih akreditasi A dari lembaga akreditasi nasional.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 5 Jan 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Announcement</h3>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 1" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Jadwal Baru Penggunaan Laboratorium Semester Genap</a>
                            </h4>
                            <p class="news-sidebar-desc">Pengumuman jadwal baru penggunaan laboratorium untuk semester genap tahun akademik 2022/2023.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 20 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 2" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Workshop Business Analytics untuk Mahasiswa Baru</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics akan menyelenggarakan workshop untuk mahasiswa baru angkatan 2022.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 12 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 3" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Pendaftaran Program Magang di Industri Mitra</a>
                            </h4>
                            <p class="news-sidebar-desc">Pembukaan pendaftaran program magang di perusahaan mitra Laboratorium Business Analytics.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 8 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Category</h3>
                    <ul class="category-list">
                        <li><a href="#"><i class="fas fa-images"></i> Gallery</a></li>
                        <li><a href="#"><i class="fas fa-cogs"></i> Content Management System</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
<script>
    // --- 0. NAV MENU LOGIC (HAMBURGER) ---
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

        // --- 1. Cek Login Status ---
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

        function toggleMenu() {
            const navMenu = document.getElementById('navMenu');
            const icon = document.querySelector('.hamburger i');

            navMenu.classList.toggle('active');

            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
</script>
</body>

</html>