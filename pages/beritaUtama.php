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

// =================================================================
// LOGIKA 1: AMBIL DATA BERITA UTAMA (YANG SEDANG DIBUKA)
// =================================================================
$berita = null; 
$currentId = 0; // Default ID untuk pencegahan error

if (isset($_GET['id'])) {
    $currentId = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM view_artikel WHERE id_artikel = ?");
    $stmt->execute([$currentId]);
    $berita = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Jika berita tidak ditemukan, kembalikan ke halaman berita
if (!$berita) {
    echo "<script>alert('Berita tidak ditemukan atau telah dihapus.'); window.location='berita.php';</script>";
    exit;
}

// =================================================================
// LOGIKA 2: AMBIL DATA SIDEBAR (PRESTASI) - KECUALI ID SAAT INI
// =================================================================
// Mengambil 3 berita kategori 'Prestasi', tapi BUKAN berita yang sedang dibuka
$stmtPrestasi = $db->prepare("SELECT * FROM view_artikel WHERE kategori LIKE '%Prestasi%' AND id_artikel != ? ORDER BY tanggal_upload DESC LIMIT 3");
$stmtPrestasi->execute([$currentId]);
$sidebarPrestasi = $stmtPrestasi->fetchAll(PDO::FETCH_ASSOC);

// =================================================================
// LOGIKA 3: AMBIL DATA SIDEBAR (ANNOUNCEMENT) - KECUALI ID SAAT INI
// =================================================================
// Mengambil 3 berita kategori 'Pengumuman' atau 'Announcement', tapi BUKAN berita yang sedang dibuka
$stmtAnnouncement = $db->prepare("SELECT * FROM view_artikel WHERE (kategori LIKE '%Pengumuman%' OR kategori LIKE '%Announcement%') AND id_artikel != ? ORDER BY tanggal_upload DESC LIMIT 3");
$stmtAnnouncement->execute([$currentId]);
$sidebarAnnouncement = $stmtAnnouncement->fetchAll(PDO::FETCH_ASSOC);


// --- 4. AMBIL DATA SETTING (Logo & Nama Lab) ---
$logoSrc = '../assets/images/logo.png';
$namaLabText = 'Laboratorium Business Analytics'; 

try {
    $stmt = $db->query("SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'nama_lab')");
    $resultRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $settings = [];
    foreach ($resultRaw as $row) { $settings[$row['key']] = $row; }

    if (!empty($settings['logo']['file_path'])) { $logoSrc = '../admin/' . $settings['logo']['file_path']; }
    if (!empty($settings['nama_lab']['value'])) { $namaLabText = $settings['nama_lab']['value']; }
} catch (Exception $e) { /* Ignore error */ }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($berita['judul']); ?> - <?= htmlspecialchars($namaLabText); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/beritaUtamaStyle.css?v=<?php echo time(); ?>">
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

    <div class="content-container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="berita.php">Berita</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= substr(htmlspecialchars($berita['judul']), 0, 50) . '...'; ?>
                        </li>
                    </ol>
                </nav>
                
                <h1 class="news-title"><?= htmlspecialchars($berita['judul']); ?></h1>
                
                <div class="news-meta">
                    <span><i class="fas fa-user"></i> <?= !empty($berita['nama_user']) ? htmlspecialchars($berita['nama_user']) : 'Admin'; ?></span>
                    <span><i class="fas fa-calendar-alt"></i> <?= date('d F Y', strtotime($berita['tanggal_upload'])); ?></span>
                    <span><i class="fa fa-tag"></i> <?= htmlspecialchars($berita['kategori']); ?></span>
                </div>
                
                <?php 
                    $gambarUtama = !empty($berita['file_path']) ? '../admin/' . $berita['file_path'] : './img/MissingPicturee.jpg';
                ?>
                <img src="<?= $gambarUtama; ?>" alt="Gambar Berita" class="news-image">
                
                <div class="news-content" style="text-align: justify; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars_decode($berita['konten'])); ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Prestasi Lainnya</h3>
                    
                    <?php if (count($sidebarPrestasi) > 0): ?>
                        <?php foreach($sidebarPrestasi as $row): ?>
                            <?php 
                                $imgSide = !empty($row['file_path']) ? '../admin/' . $row['file_path'] : './img/MissingPicturee.jpg';
                            ?>
                            <div class="news-item">
                                <img src="<?= $imgSide; ?>" alt="Thumbnail" class="news-thumbnail">
                                <div class="news-info">
                                    <h4 class="news-sidebar-title">
                                        <a href="beritaUtama.php?id=<?= $row['id_artikel']; ?>">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </a>
                                    </h4>
                                    <p class="news-sidebar-desc">
                                        <?= substr(strip_tags($row['konten']), 0, 80); ?>...
                                    </p>
                                    <div class="news-sidebar-meta">
                                        <span><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($row['tanggal_upload'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Belum ada data prestasi lainnya.</p>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Announcement</h3>
                    
                    <?php if (count($sidebarAnnouncement) > 0): ?>
                        <?php foreach($sidebarAnnouncement as $row): ?>
                            <?php 
                                $imgSide = !empty($row['file_path']) ? '../admin/' . $row['file_path'] : './img/MissingPicturee.jpg';
                            ?>
                            <div class="news-item">
                                <img src="<?= $imgSide; ?>" alt="Thumbnail" class="news-thumbnail">
                                <div class="news-info">
                                    <h4 class="news-sidebar-title">
                                        <a href="beritaUtama.php?id=<?= $row['id_artikel']; ?>">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </a>
                                    </h4>
                                    <p class="news-sidebar-desc">
                                        <?= substr(strip_tags($row['konten']), 0, 80); ?>...
                                    </p>
                                    <div class="news-sidebar-meta">
                                        <span><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($row['tanggal_upload'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small">Belum ada pengumuman lainnya.</p>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Category</h3>
                    <ul class="category-list">
                        <li><a href="galeri.php"><i class="fas fa-images"></i> Gallery</a></li>
                        <li><a href="newsInputService.php"><i class="fas fa-cogs"></i> Content Management System</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
    </script>
</body>
</html>