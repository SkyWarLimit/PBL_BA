<?php
// 1. KONEKSI DATABASE
// Mundur satu folder (../) karena file ini ada di folder 'pages'
require_once '../admin/config/database.php';
$db = (new Database())->getConnection();

// 2. AMBIL DATA BERITA
try {
    // Ambil semua artikel yang terbit, urutkan dari yang terbaru
    $query = "SELECT * FROM artikel WHERE is_published = true ORDER BY tanggal_upload DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $beritaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $beritaList = []; // Jika error, anggap data kosong
}

// Helper untuk memotong teks deskripsi yang terlalu panjang
function potongTeks($teks, $panjang = 100) {
    return (strlen($teks) > $panjang) ? substr($teks, 0, $panjang) . '...' : $teks;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Business Analytics - Berita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/beritaStyle.css">
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
        
        <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
    </nav>

    <section class="hero-section">
        <div class="hero-rectangle"></div>
        <div class="hero-container">
            <div class="hero-inner-rectangle"></div>
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
                        Dapatkan informasi terbaru seputar kegiatan, prestasi, dan pengumuman penting dari Laboratorium Business Analytics.
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
                        <a class="section-nav-link active" href="#latest-news">
                            <span class="nav-text">News Latest</span>
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

    <?php
    // Pisahkan berita berdasarkan kategori
    $newsLatest = array_filter($beritaList, function($item) { return $item['kategori'] === 'News Latest'; });
    $prestasi = array_filter($beritaList, function($item) { return $item['kategori'] === 'Prestasi'; });
    $announcement = array_filter($beritaList, function($item) { return $item['kategori'] === 'Announcement'; });
    
    // Fungsi Template Card Berita
    function renderNewsCard($berita) {
        // Path Gambar: Ambil dari folder admin/uploads
        $imgPath = !empty($berita['file_path']) ? '../admin/' . $berita['file_path'] : '../assets/img/default-news.jpg';
        
        $judul = htmlspecialchars($berita['judul']);
        $konten = htmlspecialchars(potongTeks($berita['konten'], 150));
        $tanggal = date('F d, Y', strtotime($berita['tanggal_upload']));
        $uploader = htmlspecialchars($berita['nama_pengupload'] ?? 'Admin');
        $role = htmlspecialchars($berita['role_pengupload'] ?? 'Admin Lab');
        
        // Link ke detail berita
        $linkDetail = "beritaUtama.php?id=" . $berita['id_artikel'];

        // MODIFIKASI: onclick ditaruh di news-card wrapper agar semua area bisa diklik
        echo '
        <div class="news-card" style="cursor: pointer;" onclick="window.location.href=\'' . $linkDetail . '\'">
            <div class="news-image">
                <img src="' . $imgPath . '" alt="' . $judul . '" onerror="this.src=\'../assets/img/default-news.jpg\'">
            </div>
            <div class="news-content">
                <h3 class="news-card-title">' . $judul . '</h3>
                <p class="news-excerpt">' . $konten . '</p>
                <div class="news-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span class="meta-text">' . $uploader . '</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span class="meta-text">' . $tanggal . '</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa fa-id-badge"></i>
                        <span class="meta-text">' . $role . '</span>
                    </div>
                </div>
            </div>
        </div>';
    }
    ?>

    <section id="latest-news" class="news-section">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Update Terbaru Kegiatan Laboratorium</p>
                <h2 class="news-title">Latest News Lab Business Analytics</h2>
            </div>

            <div class="news-grid">
                <?php if (empty($newsLatest)): ?>
                    <p class="text-center text-muted col-12">Belum ada berita terbaru.</p>
                <?php else: ?>
                    <?php foreach ($newsLatest as $item) renderNewsCard($item); ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="prestasi" class="news-section" style="background-color: #f9f9f9;">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Pencapaian Mahasiswa & Dosen</p>
                <h2 class="news-title">Prestasi Lab Business Analytics</h2>
            </div>

            <div class="news-grid">
                <?php if (empty($prestasi)): ?>
                    <p class="text-center text-muted col-12">Belum ada data prestasi.</p>
                <?php else: ?>
                    <?php foreach ($prestasi as $item) renderNewsCard($item); ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="announcement" class="news-section">
        <div class="container">
            <div class="news-header">
                <p class="news-subtitle">Pengumuman Penting</p>
                <h2 class="news-title">Announcement Lab Business Analytics</h2>
            </div>

            <div class="news-grid">
                <?php if (empty($announcement)): ?>
                    <p class="text-center text-muted col-12">Belum ada pengumuman.</p>
                <?php else: ?>
                    <?php foreach ($announcement as $item) renderNewsCard($item); ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sectionLinks = document.querySelectorAll('.section-nav-link');

            // Scroll Smooth saat klik menu
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

            // Update active menu saat scroll
            window.addEventListener('scroll', function () {
                const sections = document.querySelectorAll('.news-section'); // Target class news-section
                const scrollPos = window.scrollY + 300; // Offset

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