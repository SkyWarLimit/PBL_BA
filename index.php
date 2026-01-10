<?php
session_start();

// --- 1. KONEKSI DATABASE & LOGIKA SESSION ---
require_once './admin/config/database.php';
$db = (new Database())->getConnection();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// --- 2. LOGIKA PENGAMBILAN DATA GALERI (DIBATASI 3 PER KOLOM) ---
try {
    // Kita ambil data lebih banyak (misal 30) dari database
    $query = "SELECT * FROM galeri ORDER BY id_galeri DESC LIMIT 30";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $galeriList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $galeriList = [];
}

// Inisialisasi Array Kolom
$kolom1 = [];
$kolom2 = [];
$kolom3 = [];
$kolom4 = [];

// Distribusi Data
foreach ($galeriList as $item) {
    if (count($kolom1) >= 3 && count($kolom2) >= 3 && count($kolom3) >= 3 && count($kolom4) >= 3) {
        break;
    }

    switch ($item['kategori']) {
        case 'Kategori 1':
            if (count($kolom1) < 3) $kolom1[] = $item;
            break;
        case 'Kategori 2':
            if (count($kolom2) < 3) $kolom2[] = $item;
            break;
        case 'Kategori 3':
            if (count($kolom3) < 3) $kolom3[] = $item;
            break;
        case 'Kategori 4':
            if (count($kolom4) < 3) $kolom4[] = $item;
            break;
        default:
            if (count($kolom1) < 3) $kolom1[] = $item;
    }
}

// Fungsi helper untuk merender item galeri
function renderGalleryItem($item)
{
    $imgPath = !empty($item['file_path']) ? './admin/' . $item['file_path'] : './assets/img/default-gallery.jpg';
    $judul = htmlspecialchars($item['judul'], ENT_QUOTES);
    $deskripsi = htmlspecialchars($item['deskripsi'], ENT_QUOTES);

    echo '
    <div class="gallery-item" 
            onclick="openModal(this)" 
            data-img="' . $imgPath . '" 
            data-judul="' . $judul . '" 
            data-deskripsi="' . $deskripsi . '">
        <img src="' . $imgPath . '" alt="' . $judul . '" onerror="this.src=\'./assets/img/default-gallery.jpg\'">
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
    <title>Laboratory Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;1,700;1,800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/berandaStyle.css?v=<?php echo time(); ?>">

    <?php
    $pakai_galeri = true; // membuat Variable pemicu
    if (isset($pakai_galeri) && $pakai_galeri == true) :
    ?>
        <link rel="stylesheet" href="./assets/css/galeri-addon.css?v=<?php echo time(); ?>">
    <?php endif; ?>


    <style>
        /* CSS Table Booking */
        .table-booking-wrapper {
            padding: 20px 0;
            background-color: #f8f9fa;
        }

        .table-booking-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .booking-table th.today {
            background-color: #e3f2fd !important;
            border: 1px solid #90caf9 !important;
        }

        .booking-rectangle {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 5px 8px !important;
            border-left: 4px solid !important;
            position: absolute;
            width: calc(100% - 4px);
            left: 2px;
            font-size: 11px;
            z-index: 5;
            transition: 0.2s;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .booking-rectangle:hover {
            z-index: 10;
            transform: scale(1.02);
        }

        .booking-time {
            font-size: 10px;
            font-weight: 800;
            margin-bottom: 2px;
            color: #333;
        }

        .booking-instansi {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 2px;
            color: #000;
            text-transform: uppercase;
        }

        .booking-name {
            font-size: 10px;
            color: #555;
            font-style: italic;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .date-navigation {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .nav-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .today-btn {
            padding: 0 15px;
            height: 35px;
            border: none;
            background: #2b95fd;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .date-range {
            font-weight: 700;
            font-size: 16px;
            min-width: 200px;
            text-align: center;
        }

        .table-responsive-custom {
            border: 1px solid #e0e0e0;
            max-height: 700px;
            overflow-y: auto;
            position: relative;
            border-radius: 8px;
        }

        .booking-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 600px;
        }

        .booking-table th {
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 20;
            padding: 10px;
            border-bottom: 2px solid #ddd;
            border-right: 1px solid #ddd;
            text-align: center;
        }

        .booking-table th:first-child {
            left: 0;
            z-index: 30;
            width: 70px;
            border-right: 2px solid #ccc;
        }

        .time-header {
            width: 70px;
            background: #fff;
            position: sticky;
            left: 0;
            z-index: 10;
            border-right: 2px solid #ccc;
            padding: 0 !important;
            vertical-align: top;
        }

        .day-cell {
            padding: 0 !important;
            height: 840px;
            position: relative;
            background: #fff;
            border-right: 1px solid #eee;
            vertical-align: top;
        }

        .day-container {
            position: relative;
            height: 100%;
            width: 100%;
        }

        .time-line {
            position: absolute;
            width: 100%;
            left: 0;
            height: 1px;
            pointer-events: none;
        }

        .time-line.line-hour {
            background: #e0e0e0;
        }

        .time-line.line-half {
            border-top: 1px dashed #eee;
        }

        .time-label {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #666;
        }

        /* CSS Calendar & Form */
        .calendar-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            height: 100%;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-month {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .calendar-nav button {
            background: #f8f9fa;
            border: 1px solid #ddd;
            width: 35px;
            height: 35px;
            border-radius: 5px;
            cursor: pointer;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .calendar-table th {
            text-align: center;
            padding: 10px 0;
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .calendar-table td {
            text-align: center;
            padding: 10px 0;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .calendar-table td:hover:not(.disabled) {
            background: #f0f8ff;
            color: #2b95fd;
        }

        .calendar-table td.active {
            background: #2b95fd;
            color: white;
            font-weight: bold;
        }

        .calendar-table td.disabled {
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }

        .calendar-table td.other-month {
            color: #ccc;
        }

        .selected-date-display {
            background: #eaf0ff;
            color: #2b95fd;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            border: 1px solid #dbeafe;
            margin-bottom: 15px;
        }

        .booking-btn {
            background: #2b95fd;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 10px;
            transition: 0.3s;
        }

        .booking-btn:hover {
            background: #1a7fd8;
        }

        /* Modal Detail Table */
        .modal-overlay-detail {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content-detail {
            background: white;
            width: 90%;
            max-width: 450px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-header-detail {
            background: #2b95fd;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
        }

        .modal-body-detail {
            padding: 20px;
        }

        .detail-row {
            margin-bottom: 10px;
        }

        .detail-label {
            font-size: 12px;
            color: #888;
            font-weight: 700;
            display: block;
        }

        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
    </style>
</head>

<body>

    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="./assets/img/logo.png" alt="Laboratorium Business Analytics Logo">
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
            <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="./pages/profile.php">Profil</a></li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>Publikasi</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="./pages/berita.php">Berita</a></li>
                    <li><a class="dropdown-item" href="./pages/galeri.php">Gallery</a></li>
                    <li><a class="dropdown-item" href="./pages/newsInputService.php">News Input Service</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>Peminjaman Lab</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="./pages/infoPeminjaman.php">Informasi Laboratorium</a></li>
                    <li><a class="dropdown-item" href="./pages/tableBooking.php">Table Peminjaman</a></li>
                    <li><a class="dropdown-item" href="./pages/booking.php">Pemesanan Lab</a></li>
                </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="./pages/kontak.php">Kontak</a></li>

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
                        <a class="logout-btn-modern" href="../admin/logout.php" title="Logout">
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

                <a class="desktop-logout-btn" onclick="window.location.href='./admin/logout.php'" title="Keluar / Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>

            </div>
        <?php else: ?>
            <button class="login-btn desktop-login-btn" onclick="window.location.href='./admin/login.php'">Login</button>
        <?php endif; ?>

    </nav>

    <section class="hero-section">
        <div class="hero-rectangle">
        </div>
        <div class="hero-container">
            <div class="hero-text-content">
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">Empowering Data-Driven Decision Making</span>
                        <span class="title-line business-analytics">Welcome to the Business Analytics Laboratory</span>
                    </h1>
                    <p class="hero-detailed-description">
                        Menciptakan Solusi, Bukan Sekedar Analisis
                    </p>
                    <div class="buttons-container">
                        <button class="feature-btn btn-short">
                            <span class="btn-content">
                                <i class="fas fa-chart-line btn-icon"></i>
                                <span class="btn-text">Data Analytics Skills</span>
                            </span>
                        </button>
                        <button class="feature-btn btn-medium">
                            <span class="btn-content">
                                <i class="fas fa-chart-bar btn-icon"></i>
                                <span class="btn-text">Visualization & BI Tools</span>
                            </span>
                        </button>
                        <button class="feature-btn btn-long">
                            <span class="btn-content">
                                <i class="fas fa-users btn-icon"></i>
                                <span class="btn-text">Collaboration & Research</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="hero-maskot">
                <img src="./assets/img/MaskotLab.png" alt="Maskot Business Analytics Laboratory" class="maskot-image">
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-container">
            <h2 class="about-title">About the Laboratory</h2>
            <div class="about-line"></div>
            <p class="about-description">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the
                industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and
                scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap
                into electronic typesetting, remaining essentially unchanged. Lorem Ipsum is simply dummy text of the
                printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since
                the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                It has survived not only five centuries, but also the leap into electronic typesetting, remaining
                essentially unchanged.
            </p>
        </div>
    </section>

    <section class="profile-carousel-section">
        <div class="custom-container-relative">
            <div class="gray-backdrop-box">
                <img id="backdrop-image" src="../assets/img/468271953_2965587063594573_732085040912103659_n.jpg" alt="Research Project Image" class="backdrop-img-content">
            </div>
            <div class="carousel-wrapper">
                <div class="carousel-track" id="track">
                    <div class="custom-card" data-bg-img="../assets/img/468271953_2965587063594573_732085040912103659_n.jpg">
                        <div class="card-content">
                            <h3>Project 1</h3>
                            <p>Deskripsi singkat 1</p>
                        </div>
                    </div>
                    <div class="custom-card" data-bg-img="../assets/img/472123358_1124447422368489_4166635474059537722_n.jpg">
                        <div class="card-content">
                            <h3>Project 2</h3>
                            <p>Deskripsi singkat 2</p>
                        </div>
                    </div>
                    <div class="custom-card" data-bg-img="../assets/img/480910804_18103876279490907_972057783880120162_n.jpg">
                        <div class="card-content">
                            <h3>Project 3</h3>
                            <p>Deskripsi singkat 3</p>
                        </div>
                    </div>
                    <div class="custom-card" data-bg-img="../assets/img/472009416_9031096613644839_2631417119897194052_n.jpg">
                        <div class="card-content">
                            <h3>Project 4</h3>
                            <p>Deskripsi singkat 4</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-nav">
                <button class="nav-btn prev-btn" id="prevBtn">
                    <i class="fa fa-arrow-left"></i>
                </button>
                <button class="nav-btn next-btn" id="nextBtn">
                    <i class="fa fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="profile lab section">
        <div class="container-xl">
            <div class="row mb-5 align-items-center">
                <div class="col-lg-5">
                    <p class="text-uppercase fw-bold text-secondary mb-1">Profile</p>
                    <h2 class="fw-bold mb-3">Profile About Lab</h2>
                    <div class="profile-btn-container">
                        <button class="profile-btn">
                            <span class="btn-text">
                                Read More
                                <i class="fas fa-arrow-up arrow-icon"></i>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="col-lg-7">
                    <p class="text-muted mt-3 mt-lg-0">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                        laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in
                        voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                        non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                    </p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card card-preview-profile bg-dark text-white overflow-hidden shadow">
                        <img src="./assets/img/untitled.jpeg" class="card-bg-img" alt="Publikasi">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold mb-1">Publikasi</h5>
                            <h6 class="card-subtitle mb-2 text-white">Photo About Lab</h6>
                            <p class="card-text small mb-3 text-white">www.personal-admin.dummy.nus/ of the printing and
                                typesetting industry. Lorem ipsum has been the industry's</p>
                            <div class="respon-btn-container">
                                <button class="respon-btn">
                                    <span class="btn-text">
                                        Read More
                                        <i class="fas fa-arrow-up arrow-icon"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card card-preview-profile bg-dark text-white overflow-hidden shadow">
                        <img src="./assets/img/untitled.jpeg" class="card-bg-img" alt="Peninjarman Lab">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold mb-1">Peminjaman Lab</h5>
                            <h6 class="card-subtitle mb-2 text-white">Laboratory Borrowing</h6>
                            <p class="card-text small mb-3 text-white">Lecture again 3 billion dollars (TCM) for the
                                printing and typesetting industry. Lorem ipsum has been the industry's</p>
                            <div class="respon-btn-container">
                                <button class="respon-btn">
                                    <span class="btn-text">
                                        Read More
                                        <i class="fas fa-arrow-up arrow-icon"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card card-preview-profile bg-dark text-white overflow-hidden shadow">
                        <img src="./assets/img/untitled.jpeg" class="card-bg-img" alt="Berita">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold mb-1">Berita</h5>
                            <h6 class="card-subtitle mb-2 text-white">Content Management System</h6>
                            <p class="card-text small mb-3 text-white">Includes many 3 billion alumni, each of the articles
                                and typesetting industry. Lorem ipsum has been the industry's</p>
                            <div class="respon-btn-container">
                                <button class="respon-btn">
                                    <span class="btn-text">
                                        Read More
                                        <i class="fas fa-arrow-up arrow-icon"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="news-section py-5 bg-white">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-2">Today's Lab News</h2>
                    <div class="section-title-underline2"></div>
                </div>
                <div class="news-btn-container">
                    <button class="news-btn">
                        <span class="btn-text">
                            Read More
                            <i class="fas fa-arrow-up arrow-icon"></i>
                        </span>
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card news-card-lg rounded-3 overflow-hidden border-0 shadow-sm h-100">
                        <div class="news-lg-img-container position-relative h-100">
                            <img src="./assets/img/untitled.jpeg" alt="News Image" class="news-lg-img">

                            <div class="news-lg-content position-absolute bottom-0 start-0 w-100 p-4 p-lg-5">
                                <h3 class="fw-bold text-white mb-3">Lorem Ipsum is simply dummy text of the printing and
                                    typesetting industry.</h3>

                                <div class="d-flex flex-wrap gap-3 text-white-50 small mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2"></i> Nm. User
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar4-event me-2"></i> 31 November, 2025
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people-fill me-2"></i> Mahasiswa Polinema
                                    </div>
                                </div>

                                <p class="text-white-50 small mb-0 line-clamp-2">
                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem
                                    Ipsum has been the industry's standard dummy text ever since the 1500s...
                                </p>
                            </div>

                            <div class="news-hover-overlay"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex flex-column gap-4">
                    <div class="news-item-sm d-flex align-items-start gap-3 position-relative">
                        <div class="news-sm-img rounded-3 flex-shrink-0 position-relative overflow-hidden">
                            <img src="./assets/img/untitled.jpeg" alt="Profile" class="news-profile-img">
                            <div class="news-sm-hover-overlay"></div>
                        </div>

                        <div class="news-sm-content">
                            <h5 class="fw-bold text-dark mb-2 line-clamp-2">Lorem Ipsum is simply dummy text of the
                                printing and typesetting industry.</h5>

                            <div class="d-flex flex-wrap gap-3 text-muted small mb-2" style="font-size: 0.75rem;">
                                <span class="d-flex align-items-center"><i class="bi bi-person-circle me-1"></i> Nm.
                                    User</span>
                                <span class="d-flex align-items-center"><i class="bi bi-calendar4-event me-1"></i> 31
                                    Nov, 2025</span>
                                <span class="d-flex align-items-center"><i class="bi bi-people-fill me-1"></i> Mhs.
                                    Polinema</span>
                            </div>

                            <p class="text-muted small mb-0 line-clamp-2">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text...
                            </p>
                        </div>
                    </div>

                    <div class="news-item-sm d-flex align-items-start gap-3 position-relative">
                        <div class="news-sm-img rounded-3 flex-shrink-0 position-relative overflow-hidden">
                            <img src="./assets/img/untitled.jpeg" alt="Profile" class="news-profile-img">
                            <div class="news-sm-hover-overlay"></div>
                        </div>
                        <div class="news-sm-content">
                            <h5 class="fw-bold text-dark mb-2 line-clamp-2">Lorem Ipsum is simply dummy text of the
                                printing and typesetting industry.</h5>
                            <div class="d-flex flex-wrap gap-3 text-muted small mb-2" style="font-size: 0.75rem;">
                                <span class="d-flex align-items-center"><i class="bi bi-person-circle me-1"></i> Nm.
                                    User</span>
                                <span class="d-flex align-items-center"><i class="bi bi-calendar4-event me-1"></i> 31
                                    Nov, 2025</span>
                                <span class="d-flex align-items-center"><i class="bi bi-people-fill me-1"></i> Mhs.
                                    Polinema</span>
                            </div>
                            <p class="text-muted small mb-0 line-clamp-2">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text...
                            </p>
                        </div>
                    </div>

                    <div class="news-item-sm d-flex align-items-start gap-3 position-relative">
                        <div class="news-sm-img rounded-3 flex-shrink-0 position-relative overflow-hidden">
                            <img src="./assets/img/untitled.jpeg" alt="Profile" class="news-profile-img">
                            <div class="news-sm-hover-overlay"></div>
                        </div>
                        <div class="news-sm-content">
                            <h5 class="fw-bold text-dark mb-2 line-clamp-2">Lorem Ipsum is simply dummy text of the
                                printing and typesetting industry.</h5>
                            <div class="d-flex flex-wrap gap-3 text-muted small mb-2" style="font-size: 0.75rem;">
                                <span class="d-flex align-items-center"><i class="bi bi-person-circle me-1"></i> Nm.
                                    User</span>
                                <span class="d-flex align-items-center"><i class="bi bi-calendar4-event me-1"></i> 31
                                    Nov, 2025</span>
                                <span class="d-flex align-items-center"><i class="bi bi-people-fill me-1"></i> Mhs.
                                    Polinema</span>
                            </div>
                            <p class="text-muted small mb-0 line-clamp-2">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gallery-section py-5 bg-white">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-2">Today's Lab Gallery</h2>
                    <div class="section-title-underline2"></div>
                </div>
                
                <div class="gallery-btn-container">
                    <button class="gallery-btn" onclick="window.location.href='./pages/galeri.php'">
                        <span class="btn-text">
                            View More
                            <i class="fas fa-arrow-up arrow-icon"></i>
                        </span>
                    </button>
                </div>
            </div>

            <div class="gallery-grid">
                <div class="gallery-column">
                    <?php if (!empty($kolom1)) {
                        foreach ($kolom1 as $item) renderGalleryItem($item);
                    } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom2)) {
                        foreach ($kolom2 as $item) renderGalleryItem($item);
                    } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom3)) {
                        foreach ($kolom3 as $item) renderGalleryItem($item);
                    } ?>
                </div>
                <div class="gallery-column">
                    <?php if (!empty($kolom4)) {
                        foreach ($kolom4 as $item) renderGalleryItem($item);
                    } ?>
                </div>
            </div>

            <?php if (empty($galeriList)): ?>
                <div class="text-center text-muted">Belum ada galeri yang diunggah.</div>
            <?php endif; ?>

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

    <section class="gallery-section py-5 bg-white">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-2">Borrowing Laboratory</h2>
                    <div class="section-title-underline2"></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="table-booking-container">
                        <div class="table-header">
                            <div class="date-navigation">
                                <button class="nav-btn" id="prev-week"><i class="fas fa-chevron-left"></i></button>
                                <div class="date-range" id="date-range">Loading...</div>
                                <button class="nav-btn" id="next-week"><i class="fas fa-chevron-right"></i></button>
                                <button class="today-btn" id="today-btn">Today</button>
                            </div>
                        </div>

                        <div class="table-responsive-custom">
                            <table class="booking-table" id="booking-table">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-month" id="calendar-month-display">September 2023</div>
                            <div class="calendar-nav">
                                <button id="cal-prev"><i class="fas fa-chevron-left"></i></button>
                                <button id="cal-next"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <table class="calendar-table" id="calendar-body">
                            <thead>
                                <tr>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                    <th>Sun</th>
                                </tr>
                            </thead>
                            <tbody id="calendar-days">
                            </tbody>
                        </table>

                        <div class="booking-form">
                            <h5 style="font-weight: 700; color: #333; margin-bottom: 15px;">Booking</h5>
                            <input type="hidden" id="selected-date-value">
                            <div id="selected-date-display" class="selected-date-display">Pilih tanggal di kalender</div>

                            <div class="mb-3">
                                <label style="font-size: 13px; font-weight: 600;">Check-in (07:00 - 21:00)</label>
                                <input type="time" class="form-control" id="checkin-time" min="07:00" max="21:00" value="08:00">
                            </div>
                            <div class="mb-3">
                                <label style="font-size: 13px; font-weight: 600;">Check-out (07:00 - 21:00)</label>
                                <input type="time" class="form-control" id="checkout-time" min="07:00" max="21:00" value="10:00">
                            </div>

                            <button class="booking-btn" id="btn-process-booking">Lanjut Isi Data Diri</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal-overlay-detail" id="detail-modal">
        <div class="modal-content-detail">
            <div class="modal-header-detail">
                <span>Detail Booking</span>
                <span style="cursor:pointer;" onclick="closeModalDetail()">&times;</span>
            </div>
            <div class="modal-body-detail">
                <div class="detail-row"><label class="detail-label">Tanggal:</label>
                    <div class="detail-value" id="d-date">-</div>
                </div>
                <div class="detail-row"><label class="detail-label">Waktu:</label>
                    <div class="detail-value" id="d-time">-</div>
                </div>
                <div class="detail-row"><label class="detail-label">Instansi:</label>
                    <div class="detail-value" id="d-instansi">-</div>
                </div>
                <div class="detail-row"><label class="detail-label">Peminjam:</label>
                    <div class="detail-value" id="d-booker">-</div>
                </div>
                <div class="detail-row"><label class="detail-label">Keperluan:</label>
                    <div class="detail-value" id="d-purpose">-</div>
                </div>
                <div class="detail-row"><label class="detail-label">Status:</label>
                    <div class="detail-value" id="d-status" style="color: green; font-weight:bold;">-</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- SCRIPT BAWAAN INDEX.PHP (CAROUSEL) ---
            const track = document.getElementById('track');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const backdropImage = document.getElementById('backdrop-image');

            let isAnimating = false;

            // Mengambil semua kartu saat ini
            const getCards = () => document.querySelectorAll('.custom-card');

            /* ========================================= */
            /* FUNGSI UPDATE TAMPILAN AKTIF */
            /* ========================================= */
            function updateActiveState() {
                const cards = getCards();

                // Reset semua kartu jadi putih
                cards.forEach(c => c.classList.remove('active'));

                // Kartu PERTAMA di DOM adalah kartu yang "Aktif"
                const activeCard = cards[0];
                activeCard.classList.add('active');

                // Ambil URL gambar dari atribut data-bg-img kartu yang aktif
                const newImageSrc = activeCard.getAttribute('data-bg-img');

                // Efek transisi halus untuk gambar background
                backdropImage.classList.add('fade-out');

                setTimeout(() => {
                    backdropImage.src = newImageSrc;
                    backdropImage.classList.remove('fade-out');
                }, 300); // Waktu tunggu sesuai durasi CSS opacity
            }

            /* ========================================= */
            /* FUNGSI GESER KE KANAN (NEXT) */
            /* ========================================= */
            function moveNext() {
                if (isAnimating) return;
                isAnimating = true;

                const cards = getCards();
                const cardWidth = cards[0].offsetWidth;
                // Ambil gap dari CSS (24px) atau hitung manual
                const gap = 24;
                const moveDistance = cardWidth + gap;

                // 1. Geser Track ke Kiri
                track.style.transition = 'transform 0.5s ease-in-out';
                track.style.transform = `translateX(-${moveDistance}px)`;

                // 2. Setelah animasi selesai, pindahkan elemen DOM
                setTimeout(() => {
                    track.style.transition = 'none'; // Matikan animasi sebentar

                    // Pindahkan kartu pertama ke urutan terakhir
                    track.appendChild(cards[0]);

                    // Reset posisi track ke 0
                    track.style.transform = 'translateX(0)';

                    // Update status warna dan gambar background
                    updateActiveState();

                    isAnimating = false;
                }, 500); // Harus sama dengan durasi transition CSS
            }

            /* ========================================= */
            /* FUNGSI GESER KE KIRI (PREVIOUS) */
            /* ========================================= */
            function movePrev() {
                if (isAnimating) return;
                isAnimating = true;

                const cards = getCards();
                const lastCard = cards[cards.length - 1];
                const cardWidth = cards[0].offsetWidth;
                const gap = 24;
                const moveDistance = cardWidth + gap;

                // 1. Pindahkan kartu terakhir ke depan DULU (secara instan)
                track.style.transition = 'none';
                track.prepend(lastCard);

                // 2. Geser track ke posisi minus (seolah-olah belum pindah)
                track.style.transform = `translateX(-${moveDistance}px)`;

                // 3. Force Reflow (agar browser sadar perubahan posisi)
                void track.offsetWidth;

                // 4. Animate balik ke 0
                track.style.transition = 'transform 0.5s ease-in-out';
                track.style.transform = 'translateX(0)';

                setTimeout(() => {
                    updateActiveState();
                    isAnimating = false;
                }, 500);
            }

            /* ========================================= */
            /* EVENT LISTENERS DAN INISIALISASI */
            /* ========================================= */
            nextBtn.addEventListener('click', moveNext);
            prevBtn.addEventListener('click', movePrev);

            // Inisialisasi awal
            updateActiveState();

            // --- TAMBAHAN: SCRIPT LAZY LOAD IMAGE UNTUK GALERI ---
            const galleryItems = document.querySelectorAll('.gallery-item img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src;
                        imageObserver.unobserve(img);
                    }
                });
            });
            galleryItems.forEach(img => imageObserver.observe(img));
        });

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

        // --- TAMBAHAN: FUNGSI MODAL GALERI ---
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
        document.getElementById('galleryModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        /* ========================================= */
        // --- TAMBAHAN: SCRIPT BOOKING TABLE ---
        /* ========================================= */
        document.addEventListener('DOMContentLoaded', function () {
    
    // --- KONFIGURASI GLOBAL ---
    // Sesuaikan path ini dengan lokasi file API PHP Anda
    const API_URL = './admin/api/peminjaman.php'; 
    
    // Konfigurasi Grid Waktu
    const START_HOUR = 7;       // Jam 07:00
    const END_HOUR = 22;        // Jam 22:00
    const PIXELS_PER_HOUR = 60; // Tinggi 1 jam = 60px (Penting untuk presisi)
    const TOTAL_HOURS = END_HOUR - START_HOUR;
    const TOTAL_HEIGHT = TOTAL_HOURS * PIXELS_PER_HOUR; // Total tinggi grid (840px)

    // State Variables
    let bookingsData = []; 
    let tableCurrentDate = new Date();     // Tanggal aktif untuk Tabel (Kiri)
    let calendarCurrentDate = new Date();  // Tanggal aktif untuk Kalender (Kanan)

    // Ambil status login dari PHP Session
    const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

    // ============================================================
    // 1. INISIALISASI & EVENT LISTENER
    // ============================================================
    
    // Init Tampilan Awal
    generateTableStructure();
    loadBookingsFromAPI();
    initCalendar();

    // Event Listener Navigasi Tabel (Kiri)
    document.getElementById('prev-week').addEventListener('click', () => changeTableWeek(-7));
    document.getElementById('next-week').addEventListener('click', () => changeTableWeek(7));
    document.getElementById('today-btn').addEventListener('click', () => { 
        tableCurrentDate = new Date(); 
        generateTableStructure(); 
    });

    // Event Listener Navigasi Kalender (Kanan)
    document.getElementById('cal-prev').addEventListener('click', () => { 
        calendarCurrentDate.setMonth(calendarCurrentDate.getMonth() - 1); 
        generateCalendar(calendarCurrentDate); 
    });
    document.getElementById('cal-next').addEventListener('click', () => { 
        calendarCurrentDate.setMonth(calendarCurrentDate.getMonth() + 1); 
        generateCalendar(calendarCurrentDate); 
    });

    // Tombol Submit Booking Form
    const btnProcess = document.getElementById('btn-process-booking');
    if(btnProcess) btnProcess.addEventListener('click', processBooking);

    // Event Listener Modal & Menu (Gallery/Navbar)
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    if(nextBtn) nextBtn.addEventListener('click', moveNext);
    if(prevBtn) prevBtn.addEventListener('click', movePrev);
    
    // Klik area luar modal untuk menutup
    window.onclick = function(e) { 
        if(e.target == document.getElementById('detail-modal')) closeModalDetail(); 
        if(e.target == document.getElementById('galleryModal')) closeModal(); 
    }

    // ============================================================
    // 2. LOGIKA TABEL JADWAL (BAGIAN KIRI)
    // ============================================================

    // 2.A. Fetch Data dari Database
    async function loadBookingsFromAPI() {
        try {
            const response = await fetch(API_URL);
            const result = await response.json();
            
            if (result.success) {
                // Filter hanya yang disetujui/confirmed
                bookingsData = result.data
                    .filter(item => ['approved', 'confirmed'].includes(item.status.toLowerCase()))
                    .map(item => {
                        const checkIn = item.check_in.split(' '); 
                        const checkOut = item.check_out.split(' ');
                        return { 
                            id: item.id_peminjaman, 
                            date: checkIn[0], 
                            startTime: checkIn[1].substring(0, 5), // Ambil HH:MM
                            endTime: checkOut[1].substring(0, 5),   // Ambil HH:MM
                            instansi: item.asal_instansi, 
                            bookerName: item.nama_akun, 
                            purpose: item.tujuan, 
                            status: item.status, 
                            color: 'rgba(209, 231, 221, 1)', // Warna background kotak (Hijau muda lembut)
                            borderColor: 'rgba(25, 135, 84, 1)' // Border hijau tua
                        };
                    });
                renderAllBookings();
            }
        } catch (e) { 
            console.error("Gagal memuat data booking:", e); 
        }
    }

    // 2.B. Generate Struktur Grid (Garis & Jam)
    function generateTableStructure() {
        const tableHead = document.querySelector('#booking-table thead tr');
        const tableBody = document.querySelector('#booking-table tbody');
        
        // Reset Header & Body
        while (tableHead.children.length > 1) { tableHead.removeChild(tableHead.lastChild); }
        tableBody.innerHTML = '';

        // Hitung Rentang Tanggal (Senin - Jumat)
        const mondayDate = getMonday(tableCurrentDate);
        const fridayDate = new Date(mondayDate); 
        fridayDate.setDate(mondayDate.getDate() + 4);
        
        // Update Teks Judul Tanggal
        document.getElementById('date-range').textContent = 
            `${getShortMonthName(mondayDate.getMonth())} ${mondayDate.getDate()} - ${fridayDate.getDate()}, ${fridayDate.getFullYear()}`;

        // Render Header Hari (Senin - Jumat)
        for (let i = 0; i < 5; i++) {
            const date = new Date(mondayDate); 
            date.setDate(date.getDate() + i);
            const th = document.createElement('th'); 
            if (isToday(date)) th.classList.add('today');
            th.innerHTML = `<div>${getShortDayName(date.getDay())}</div><div>${date.getDate()}/${date.getMonth() + 1}</div>`;
            tableHead.appendChild(th);
        }

        // Render Body Row (Kolom Waktu + 5 Kolom Hari)
        const row = document.createElement('tr');
        
        // --- 1. Kolom Penunjuk Waktu (Kiri) ---
        const timeCell = document.createElement('td'); 
        timeCell.className = 'time-header';
        const timeList = document.createElement('div'); 
        timeList.style.position = 'relative'; 
        timeList.style.height = `${TOTAL_HEIGHT}px`; // Tinggi Presisi

        for (let h = START_HOUR; h <= END_HOUR; h++) {
            const top = (h - START_HOUR) * PIXELS_PER_HOUR;
            
            // Label Jam (07:00, 08:00...)
            const lbl = document.createElement('div'); 
            lbl.className = 'time-label'; 
            lbl.textContent = `${String(h).padStart(2,'0')}:00`; 
            lbl.style.top = `${top}px`; 
            timeList.appendChild(lbl);
        }
        timeCell.appendChild(timeList); 
        row.appendChild(timeCell);

        // --- 2. Kolom Hari (Grid Tempat Booking) ---
        for (let d = 0; d < 5; d++) {
            const cell = document.createElement('td'); 
            cell.className = 'day-cell';
            
            const cont = document.createElement('div'); 
            cont.className = 'day-container';
            cont.style.height = `${TOTAL_HEIGHT}px`; // Tinggi Presisi

            // Gambar Garis-Garis
            for (let h = START_HOUR; h <= END_HOUR; h++) {
                const top = (h - START_HOUR) * PIXELS_PER_HOUR;
                
                // Garis Jam Penuh
                if(h !== END_HOUR) { 
                    const l = document.createElement('div'); 
                    l.className = 'time-line line-hour'; 
                    l.style.top = `${top}px`; 
                    cont.appendChild(l); 
                }
                
                // Garis Setengah Jam (Titik-titik)
                if(h !== END_HOUR) { 
                    const l2 = document.createElement('div'); 
                    l2.className = 'time-line line-half'; 
                    l2.style.top = `${top + (PIXELS_PER_HOUR / 2)}px`; 
                    cont.appendChild(l2); 
                }
            }
            cell.appendChild(cont); 
            row.appendChild(cell);
        }
        tableBody.appendChild(row); 
        
        // Setelah struktur jadi, render kotak bookingnya
        renderAllBookings();
    }

    // 2.C. Render Kotak Booking (LOGIKA YANG DIPERBAIKI)
    function renderAllBookings() {
        // Hapus booking lama agar tidak duplikat
        document.querySelectorAll('.booking-rectangle').forEach(el => el.remove());
        
        bookingsData.forEach(b => {
            const date = new Date(b.date); 
            const mondayDate = getMonday(tableCurrentDate);
            
            // Hitung selisih hari dari Senin minggu ini
            // 86400000 = ms dalam 1 hari
            const diffDays = Math.floor((date.getTime() - mondayDate.getTime()) / 86400000);
            
            // Skip jika booking bukan di Senin-Jumat minggu yang sedang dilihat
            if (diffDays < 0 || diffDays > 4) return;

            // Parsing Waktu (Jam:Menit)
            const [sh, sm] = b.startTime.split(':').map(Number); 
            const [eh, em] = b.endTime.split(':').map(Number);
            
            // Konversi ke total menit dari jam 00:00
            const startTotalMin = (sh * 60) + sm; 
            const endTotalMin = (eh * 60) + em;
            
            // Waktu mulai grid dalam menit (07:00 = 420 menit)
            const gridStartMin = START_HOUR * 60;
            
            // --- RUMUS POSISI (YANG BENAR) ---
            // Top = ((Menit Mulai - Menit Awal Grid) / 60) * Tinggi Per Jam
            const topPos = ((startTotalMin - gridStartMin) / 60) * PIXELS_PER_HOUR;
            
            // Tinggi = ((Durasi Menit) / 60) * Tinggi Per Jam
            const height = ((endTotalMin - startTotalMin) / 60) * PIXELS_PER_HOUR;

            // Validasi agar tidak render di luar jam operasional
            if (topPos < 0) return; 

            // Cari kolom hari yang sesuai
            const containers = document.querySelectorAll('.day-container');
            const targetContainer = containers[diffDays];

            if (targetContainer) {
                const el = document.createElement('div'); 
                el.className = 'booking-rectangle';
                
                // Set Style Posisi
                el.style.top = `${topPos}px`; 
                el.style.height = `${height}px`; 
                el.style.backgroundColor = b.color; 
                el.style.borderLeft = `4px solid ${b.borderColor}`; // Aksen border kiri
                
                // Isi Konten Kotak
                el.innerHTML = `
                    <div class="booking-time">${b.startTime} - ${b.endTime}</div>
                    <div class="booking-instansi">${b.instansi}</div>
                    <div class="booking-name">${b.bookerName}</div>
                `;
                
                // Klik untuk detail
                el.onclick = (e) => { 
                    e.stopPropagation(); 
                    showDetail(b); 
                };
                
                targetContainer.appendChild(el);
            }
        });
    }

    function changeTableWeek(days) { 
        tableCurrentDate.setDate(tableCurrentDate.getDate() + days); 
        generateTableStructure(); 
    }

    // ============================================================
    // 3. LOGIKA KALENDER & FORM (BAGIAN KANAN)
    // ============================================================
    function initCalendar() {
        const today = new Date();
        document.getElementById('selected-date-value').value = formatDate(today);
        updateDateDisplay(today);
        generateCalendar(calendarCurrentDate);
    }

    function generateCalendar(date) {
        const y = date.getFullYear(); 
        const m = date.getMonth();
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        document.getElementById('calendar-month-display').textContent = `${monthNames[m]} ${y}`;
        
        const tbody = document.getElementById('calendar-days'); 
        tbody.innerHTML = '';
        
        const firstDay = new Date(y, m, 1); 
        const lastDay = new Date(y, m + 1, 0); 
        const today = new Date(); today.setHours(0,0,0,0);
        
        // Adjustment agar Senin jadi kolom pertama (0)
        let startIdx = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
        
        let dayCount = 1; 
        let nextMonthCount = 1;

        for (let i = 0; i < 6; i++) {
            const tr = document.createElement('tr');
            for (let j = 0; j < 7; j++) {
                const td = document.createElement('td');
                
                if (i === 0 && j < startIdx) { 
                    td.className = 'other-month'; // Kosongkan hari bulan lalu
                } else if (dayCount > lastDay.getDate()) { 
                    td.textContent = nextMonthCount++; 
                    td.className = 'other-month'; 
                } else {
                    const currentCellDate = new Date(y, m, dayCount);
                    td.textContent = dayCount;
                    
                    // Disable tanggal lewat
                    if (currentCellDate < today) { 
                        td.className = 'disabled'; 
                    } else {
                        // Highlight tanggal terpilih
                        if (document.getElementById('selected-date-value').value === formatDate(currentCellDate)) {
                            td.className = 'active';
                        }
                        // Event Klik Tanggal
                        td.onclick = function() {
                            document.querySelectorAll('#calendar-days td').forEach(c => c.classList.remove('active'));
                            this.classList.add('active');
                            document.getElementById('selected-date-value').value = formatDate(currentCellDate);
                            updateDateDisplay(currentCellDate);
                        };
                    }
                    dayCount++;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr); 
            if (dayCount > lastDay.getDate()) break;
        }
    }

    function updateDateDisplay(d) { 
        document.getElementById('selected-date-display').textContent = d.toLocaleDateString('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }); 
    }

    // ============================================================
    // 4. PROSES SUBMIT BOOKING
    // ============================================================
    function processBooking() {
        if (!isLoggedIn) { 
            Swal.fire({
                icon: 'warning', 
                title: 'Login Diperlukan', 
                text: 'Silakan login terlebih dahulu untuk melakukan booking.', 
                confirmButtonText: 'Ke Halaman Login',
                confirmButtonColor: '#2b95fd'
            }).then((result)=>{ 
                if(result.isConfirmed) window.location.href='./admin/login.php'; 
            }); 
            return; 
        }

        const dateVal = document.getElementById('selected-date-value').value;
        const timeIn = document.getElementById('checkin-time').value;
        const timeOut = document.getElementById('checkout-time').value;

        if(!dateVal || !timeIn || !timeOut) { 
            Swal.fire({icon:'error', text:'Mohon lengkapi tanggal dan jam booking.'}); 
            return; 
        }

        const [h1, m1] = timeIn.split(':').map(Number); 
        const [h2, m2] = timeOut.split(':').map(Number);
        
        // Konversi ke menit total
        const t1 = h1 * 60 + m1; 
        const t2 = h2 * 60 + m2;
        
        // Batas Operasional (07:00 - 21:00)
        const openTime = START_HOUR * 60; 
        const closeTime = END_HOUR * 60;

        if (t1 < openTime || t2 > closeTime) { 
            Swal.fire({icon:'warning', text:`Peminjaman hanya dilayani pukul ${String(START_HOUR).padStart(2,'0')}:00 - ${String(END_HOUR).padStart(2,'0')}:00 WIB.`}); 
            return; 
        }
        if (t1 >= t2) { 
            Swal.fire({icon:'error', text:'Waktu selesai harus lebih besar dari waktu mulai.'}); 
            return; 
        }

        // Redirect ke form pengisian detail
        window.location.href = `./pages/formBooking.php?date=${dateVal}&checkin=${timeIn}&checkout=${timeOut}`;
    }

    // ============================================================
    // 5. HELPER FUNCTIONS & MODAL
    // ============================================================
    function getMonday(d) { 
        d = new Date(d); 
        const day = d.getDay(); 
        const diff = d.getDate() - day + (day === 0 ? -6 : 1); 
        return new Date(d.setDate(diff)); 
    }
    
    function isToday(d) { 
        const t = new Date(); 
        return d.getDate() === t.getDate() && d.getMonth() === t.getMonth() && d.getFullYear() === t.getFullYear(); 
    }
    
    function getShortMonthName(i) { return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'][i]; }
    function getShortDayName(i) { return ['Min','Sen','Sel','Rab','Kam','Jum','Sab'][i]; }
    
    function formatDate(d) { 
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; 
    }
    
    function showDetail(b) {
        document.getElementById('d-date').textContent = b.date; 
        document.getElementById('d-time').textContent = `${b.startTime} - ${b.endTime}`;
        document.getElementById('d-instansi').textContent = b.instansi; 
        document.getElementById('d-booker').textContent = b.bookerName;
        document.getElementById('d-purpose').textContent = b.purpose; 
        document.getElementById('d-status').textContent = b.status.toUpperCase();
        
        document.getElementById('detail-modal').style.display = 'flex';
    }
    
    window.closeModalDetail = function() { 
        document.getElementById('detail-modal').style.display = 'none'; 
    };

    // --- SCRIPT LAIN (NAVBAR MOBILE & GALLERY) ---
    // Pastikan variabel ini unik atau gunakan scope function
    let isCarouselAnimating = false;
    const updateActiveState = () => {
        const cards = document.querySelectorAll('.custom-card'); 
        const backdrop = document.getElementById('backdrop-image');
        if(cards.length > 0 && backdrop) {
            cards.forEach(c => c.classList.remove('active'));
            const activeCard = cards[0]; 
            activeCard.classList.add('active');
            const newImageSrc = activeCard.getAttribute('data-bg-img');
            backdrop.classList.add('fade-out'); 
            setTimeout(() => { 
                backdrop.src = newImageSrc; 
                backdrop.classList.remove('fade-out'); 
            }, 300);
        }
    };

    // Fungsi Navigasi Carousel
    const track = document.getElementById('track');
    function moveNext() { 
        if (isCarouselAnimating || !track) return; 
        isCarouselAnimating = true; 
        const cards = document.querySelectorAll('.custom-card'); 
        if(cards.length === 0) return;
        
        const w = cards[0].offsetWidth + 24; 
        track.style.transition = 'transform 0.5s ease-in-out'; 
        track.style.transform = `translateX(-${w}px)`; 
        setTimeout(() => { 
            track.style.transition = 'none'; 
            track.appendChild(cards[0]); 
            track.style.transform = 'translateX(0)'; 
            updateActiveState(); 
            isCarouselAnimating = false; 
        }, 500); 
    }
    
    function movePrev() { 
        if (isCarouselAnimating || !track) return; 
        isCarouselAnimating = true; 
        const cards = document.querySelectorAll('.custom-card'); 
        if(cards.length === 0) return;

        const last = cards[cards.length - 1]; 
        const w = cards[0].offsetWidth + 24; 
        track.style.transition = 'none'; 
        track.prepend(last); 
        track.style.transform = `translateX(-${w}px)`; 
        void track.offsetWidth; // Force reflow
        track.style.transition = 'transform 0.5s ease-in-out'; 
        track.style.transform = 'translateX(0)'; 
        setTimeout(() => { 
            updateActiveState(); 
            isCarouselAnimating = false; 
        }, 500); 
    }

    // Init Carousel jika ada elemennya
    if(document.querySelector('.custom-card')) updateActiveState();

    // Fungsi Global (Window Scope) untuk HTML onclick attribute
    window.toggleMenu = function() { 
        const m = document.getElementById('navMenu'); 
        const icon = document.querySelector('.hamburger i');
        m.classList.toggle('active'); 
        if (m.classList.contains('active')) {
            icon.classList.remove('fa-bars'); icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times'); icon.classList.add('fa-bars');
        }
    };
    
    window.openModal = function(e) { 
        document.getElementById('modalImg').src = e.getAttribute('data-img'); 
        document.getElementById('modalTitle').innerText = e.getAttribute('data-judul'); 
        document.getElementById('modalDesc').innerText = e.getAttribute('data-deskripsi'); 
        document.getElementById('galleryModal').classList.add('active'); 
        document.body.style.overflow = 'hidden'; 
    };
    
    window.closeModal = function() { 
        document.getElementById('galleryModal').classList.remove('active'); 
        document.body.style.overflow = 'auto'; 
    };
});
    </script>
</body>

</html>