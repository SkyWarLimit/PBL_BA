<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// --- 1. KONEKSI DATABASE ---
$dbPath = __DIR__ . '/../admin/config/database.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    $dbPathAlternative = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php';
    if (file_exists($dbPathAlternative)) {
        require_once $dbPathAlternative;
    } else {
        die("Error: Config database tidak ditemukan. Cek path file.");
    }
}

$db = (new Database())->getConnection();

// --- 2. VARIABEL DEFAULT ---
// Nilai default jika database kosong atau error
$logoSrc = '../assets/images/logo.png'; 
$namaLabText = 'Laboratorium Business Analytics';
$kontenLogo = '<p>Data deskripsi belum tersedia di database.</p>';

try {
    // --- AMBIL DATA DARI DATABASE ---
    // Kita ambil key 'logo' dan 'nama_lab'.
    // Kolom 'value' pada key 'logo' berisi DESKRIPSI.
    // Kolom 'file_path' pada key 'logo' berisi GAMBAR.
    $stmt = $db->query("SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'nama_lab')");
    $resultRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $settings = [];
    foreach ($resultRaw as $row) {
        $settings[$row['key']] = $row;
    }

    // 1. Set Gambar Logo & Deskripsi dari Database
    if (isset($settings['logo'])) {
        // Ambil Gambar
        if (!empty($settings['logo']['file_path'])) {
            $logoSrc = '../admin/' . $settings['logo']['file_path'];
        }
        
        // Ambil Deskripsi Teks (Ini yang sebelumnya statis)
        if (!empty($settings['logo']['value'])) {
            // nl2br() digunakan agar jika ada enter di database, tetap muncul sebagai baris baru di HTML
            $kontenLogo = nl2br($settings['logo']['value']); 
        }
    }

    // 2. Set Nama Lab
    if (!empty($settings['nama_lab']['value'])) {
        $namaLabText = $settings['nama_lab']['value'];
    }
    
} catch (Exception $e) { 
    /* Silent error: jika gagal, tetap pakai default */
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Makna Logo - <?php echo htmlspecialchars($namaLabText); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/maknaLogoStyle.css?v=<?php echo time(); ?>">
</head>

<body>
    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo Navbar">
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
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=0D8ABC&color=fff&size=128" alt="Avatar">
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
            <a class="user-profile-link" href="profile.php">
                <div class="text-end me-2">
                    <div class="user-name-label"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role-label"><?php echo htmlspecialchars($userRole); ?></div>
                </div>
                <div class="avatar-circle">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random&size=128" alt="Avatar">
                </div>
            </a>
            <a class="desktop-logout-btn" href="../admin/logout.php">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        <?php else: ?>
        <button class="login-btn desktop-login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
        <?php endif; ?>
    </nav>

    <div class="content-container">
        <div class="breadcrumb">
            <a href="../index.php">Beranda</a>
            <span class="separator">/</span>
            <a href="profile.php">Profil</a>
            <span class="separator">/</span>
            <span class="active">Makna Logo</span>
        </div>
        
        <h1 class="title">Makna Logo</h1>
        
        <div class="logo-center">
            <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Filosofi Logo Laboratorium">
        </div>

        <div class="content-text">
            <?php echo $kontenLogo; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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