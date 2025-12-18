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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/maknaMaskotStyle.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Sticky Navigation Bar -->
    <nav class="sticky-navbar">
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
            <li class="nav-item"><a class="nav-link active" href="profile.php">Profil</a></li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
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
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
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
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=0D8ABC&color=fff&size=128"
                                alt="User Avatar">
                        </div>
                        <div class="mobile-info-modern">
                            <span class="greeting-text">Halo,</span>
                            <span class="username-text">
                                <?php echo htmlspecialchars($userName); ?>
                            </span>
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
                    <div class="user-name-label">
                        <?php echo htmlspecialchars($userName); ?>
                    </div>
                    <div class="user-role-label">
                        <?php echo htmlspecialchars($userRole); ?>
                    </div>
                </div>
                <div class="avatar-circle">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random&size=128"
                        alt="User Avatar">
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

    <!-- <!-- Konten Makna Maskot -->
    <div class="content-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php">Beranda</a>
            <span class="separator">/</span>
            <a href="profile.php">Profil</a>
            <span class="separator">/</span>
            <span class="active">Makna Maskot</span>
        </div>
        
        <!-- Judul -->
        <h1 class="title">Makna Maskot</h1>
        
        <!-- Paragraf Pertama -->
        <div class="content-text">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        </div>
        
        <!-- maskot di Tengah -->
        <div class="logo-center">
            <img src="<?php echo htmlspecialchars($maskotSrc); ?>" alt="Maskot Business Analytics Laboratory" class="maskot-image">
        </div>
        
        <!-- Paragraf Kedua -->
        <div class="content-text">
            But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete 
            account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. 
            No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. 
            Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. 
            To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, 
            or one who avoids a pain that produces no resultant pleasure?
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