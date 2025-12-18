<?php
session_start(); 

// --- CEK SESSION (Sesuaikan dengan logika login Anda) ---
$isLoggedIn = isset($_SESSION['user_id']);
// Ambil Nama dari Session
$userName = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Pengunjung';
// Ambil Role (Opsional)
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

require_once '../admin/config/database.php';
$db = (new Database())->getConnection();

// --- LOGIKA PENGAMBILAN DATA GALERI ---
try {
    $query = "SELECT * FROM galeri ORDER BY id_galeri DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $galeriList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $galeriList = [];
}

// Membagi data ke dalam 4 kolom untuk layout Masonry/Grid
$kolom1 = []; $kolom2 = []; $kolom3 = []; $kolom4 = [];
foreach ($galeriList as $item) {
    switch ($item['kategori']) {
        case 'Kategori 1': $kolom1[] = $item; break;
        case 'Kategori 2': $kolom2[] = $item; break;
        case 'Kategori 3': $kolom3[] = $item; break;
        case 'Kategori 4': $kolom4[] = $item; break;
        default: $kolom1[] = $item;
    }
}

// Fungsi helper untuk merender item galeri
function renderGalleryItem($item) {
    $imgPath = !empty($item['file_path']) ? '../admin/' . $item['file_path'] : '../assets/img/default-gallery.jpg';
    $judul = htmlspecialchars($item['judul'], ENT_QUOTES); 
    $deskripsi = htmlspecialchars($item['deskripsi'], ENT_QUOTES);

    echo '
    <div class="gallery-item" 
            onclick="openModal(this)" 
            data-img="' . $imgPath . '" 
            data-judul="' . $judul . '" 
            data-deskripsi="' . $deskripsi . '">
        <img src="' . $imgPath . '" alt="' . $judul . '" onerror="this.src=\'../assets/img/default-gallery.jpg\'">
        <div class="gallery-overlay">
            <div class="gallery-text">
                <h3>' . $judul . '</h3>
                <p>' . $deskripsi . '</p>
            </div>
        </div>
    </div>';
}

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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/galeriStyle.css">
</head>
<body>

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
        <div class="hero-rectangle"></div>
        <div class="hero-container">
            <div class="hero-inner-rectangle"></div>
            <div class="hero-text-content">
                <div class="about-overlay"><span class="about-text">Gallery</span></div>
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">The Laboratory</span>
                        <span class="title-line business-analytics">Business Analytics</span>
                    </h1>
                    <p class="hero-detailed-description">Kumpulan dokumentasi kegiatan, workshop, dan momen berharga di Laboratorium Business Analytics.</p>
                    <div class="register-btn-container">
                        <button class="register-btn">
                            <span class="btn-text">news input service <i class="fas fa-arrow-up arrow-icon"></i></span>
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
                    <?php if (!empty($kolom1)) { foreach ($kolom1 as $item) renderGalleryItem($item); } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom2)) { foreach ($kolom2 as $item) renderGalleryItem($item); } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom3)) { foreach ($kolom3 as $item) renderGalleryItem($item); } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom4)) { foreach ($kolom4 as $item) renderGalleryItem($item); } ?>
                </div>
            </div>
        </div>
    </section>

    <div id="galleryModal" class="modal-overlay">
        <div class="modal-content-box">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="modal-img-container"><img id="modalImg" src="" alt="Gallery Image"></div>
            <div class="modal-text-container">
                <h3 id="modalTitle">Judul Galeri</h3>
                <p id="modalDesc">Deskripsi galeri akan muncul di sini.</p>
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
                hamburgerIcon.classList.remove('fa-bars'); hamburgerIcon.classList.add('fa-times');
            } else {
                hamburgerIcon.classList.remove('fa-times'); hamburgerIcon.classList.add('fa-bars');
            }
        }
        function openModal(element) {
            const imgSrc = element.getAttribute('data-img');
            const title = element.getAttribute('data-judul');
            const desc = element.getAttribute('data-deskripsi');
            document.getElementById('modalImg').src = imgSrc;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalDesc').innerText = desc;
            const modal = document.getElementById('galleryModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            const modal = document.getElementById('galleryModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        document.getElementById('galleryModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
        document.addEventListener('DOMContentLoaded', function () {
            const galleryItems = document.querySelectorAll('.gallery-item img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target; img.src = img.src; imageObserver.unobserve(img);
                    }
                });
            });
            galleryItems.forEach(img => imageObserver.observe(img));
        });
    </script>
</body>
</html>