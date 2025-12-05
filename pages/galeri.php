<?php
// 1. KONEKSI DATABASE
require_once '../admin/config/database.php';
$db = (new Database())->getConnection();

// 2. AMBIL DATA GALERI
try {
    $query = "SELECT * FROM galeri ORDER BY id_galeri DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $galeriList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $galeriList = [];
}

// 3. PISAHKAN DATA BERDASARKAN KATEGORI (UNTUK KOLOM MASONRY)
// Inisialisasi array kosong untuk 4 kolom
$kolom1 = [];
$kolom2 = [];
$kolom3 = [];
$kolom4 = [];

foreach ($galeriList as $item) {
    // Masukkan item ke array yang sesuai dengan kategorinya
    switch ($item['kategori']) {
        case 'Kategori 1': $kolom1[] = $item; break;
        case 'Kategori 2': $kolom2[] = $item; break;
        case 'Kategori 3': $kolom3[] = $item; break;
        case 'Kategori 4': $kolom4[] = $item; break;
        default: $kolom1[] = $item; // Jika kategori lain, masukkan ke kolom 1 default
    }
}

// Helper Function untuk Render Item Galeri (Supaya kode HTML tidak berulang)
function renderGalleryItem($item) {
    // Path Gambar: Ambil dari folder admin/uploads
    $imgPath = !empty($item['file_path']) ? '../admin/' . $item['file_path'] : '../assets/img/default-gallery.jpg';
    $judul = htmlspecialchars($item['judul']);
    $deskripsi = htmlspecialchars($item['deskripsi']);

    echo '
    <div class="gallery-item">
        <img src="' . $imgPath . '" alt="' . $judul . '" onerror="this.src=\'../assets/img/default-gallery.jpg\'">
        <div class="gallery-overlay">
            <div class="gallery-text">
                <h3>' . $judul . '</h3>
                <p>' . $deskripsi . '</p>
            </div>
        </div>
    </div>';
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
    <link rel="stylesheet" href="../assets/css/galeriStyle.css">
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
        <div class="hero-rectangle">
            </div>
        <div class="hero-container">
            <div class="hero-inner-rectangle">
                </div>
            <div class="hero-text-content">
                <div class="about-overlay">
                    <span class="about-text">Gallery</span>
                </div>
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">The Laboratory</span>
                        <span class="title-line business-analytics">Business Analytics</span>
                    </h1>
                    <p class="hero-detailed-description">
                        Kumpulan dokumentasi kegiatan, workshop, dan momen berharga di Laboratorium Business Analytics.
                    </p>
                    <div class="register-btn-container">
                        <button class="register-btn">
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

    <div class="py-5"></div>

    <section class="gallery-section">
        <div class="container">
            <div class="gallery-grid">
                
                <div class="gallery-column">
                    <?php 
                    if (!empty($kolom1)) {
                        foreach ($kolom1 as $item) renderGalleryItem($item);
                    } else {
                        // Placeholder jika kosong (Opsional)
                        echo '<p class="text-muted text-center p-3">Kategori 1 Kosong</p>';
                    }
                    ?>
                </div>

                <div class="gallery-column">
                    <?php 
                    if (!empty($kolom2)) {
                        foreach ($kolom2 as $item) renderGalleryItem($item);
                    } else {
                        echo '<p class="text-muted text-center p-3">Kategori 2 Kosong</p>';
                    }
                    ?>
                </div>

                <div class="gallery-column">
                    <?php 
                    if (!empty($kolom3)) {
                        foreach ($kolom3 as $item) renderGalleryItem($item);
                    } else {
                        echo '<p class="text-muted text-center p-3">Kategori 3 Kosong</p>';
                    }
                    ?>
                </div>

                <div class="gallery-column">
                    <?php 
                    if (!empty($kolom4)) {
                        foreach ($kolom4 as $item) renderGalleryItem($item);
                    } else {
                        echo '<p class="text-muted text-center p-3">Kategori 4 Kosong</p>';
                    }
                    ?>
                </div>

            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // JavaScript untuk optimalisasi layout Pinterest (TETAP DIPERTAHANKAN)
        document.addEventListener('DOMContentLoaded', function () {
            function optimizeMasonryLayout() {
                // Logika resize window untuk responsif (1 kolom di HP)
                if (window.innerWidth <= 768) {
                   // Biarkan CSS menangani stacking di mobile
                   return;
                }
                // Logika redistribusi item jika perlu (Opsional karena PHP sudah membagi kolom)
            }

            // Jalankan optimisasi saat load dan resize
            optimizeMasonryLayout();
            window.addEventListener('resize', optimizeMasonryLayout);

            // Tambahkan lazy loading untuk gambar
            const galleryItems = document.querySelectorAll('.gallery-item img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src; // Trigger load
                        imageObserver.unobserve(img);
                    }
                });
            });

            galleryItems.forEach(img => imageObserver.observe(img));
        });
    </script>
</body>

</html>