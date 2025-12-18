<?php
require_once __DIR__ . '/config/database.php';

// Fungsi cek auth sederhana
if (!function_exists('checkAuth')) {
    session_start();
    function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}
checkAuth();

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
    <title>Admin Panel - Laboratorium Business Analytics</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* === MODERN THEME VARIABLES === */
        :root {
            --primary: #4361ee;
            --primary-light: #eaf0ff;
            --secondary: #3f37c9;
            --text-main: #2b2d42;
            --text-muted: #8d99ae;
            --bg-body: #f8f9fa;
            --sidebar-width: 260px;
            --card-radius: 16px;
            --shadow-sm: 0 2px 15px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-body);
            font-size: 0.9rem;
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* === CUSTOM BADGES (MODERN LOOK) === */
        .badge-modern {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Kategori Badges */
        .badge-news-latest {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-prestasi {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-announcement {
            background-color: #ffedd5;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .badge-kegiatan {
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }

        .badge-fasilitas {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .badge-lainnya {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .badge-default {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* === SIDEBAR MODERN === */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.02);
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: #eee;
            border-radius: 10px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 30px;
            color: var(--primary);
            font-weight: 800;
            font-size: 1.3rem;
            text-decoration: none;
        }

        .sidebar-brand img {
            width: 35px;
            height: auto;
            margin-right: 12px;
            filter: drop-shadow(0 4px 6px rgba(67, 97, 238, 0.3));
        }

        .sidebar-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin: 20px 0 10px 15px;
            font-weight: 700;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-main);
            border-radius: 12px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
        }

        .nav-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            color: var(--text-muted);
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link:hover i {
            color: var(--primary);
            transform: translateX(3px);
        }

        .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .nav-link.active i {
            color: white;
        }

        .btn-logout {
            margin-top: 30px;
            background-color: #fff5f5;
            color: #e63946;
            border: 1px solid #ffe5e5;
        }

        .btn-logout:hover {
            background-color: #e63946;
            color: white;
            border-color: #e63946;
        }

        .btn-logout i {
            color: #e63946;
        }

        .btn-logout:hover i {
            color: white;
        }

        /* === MAIN CONTENT === */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px 40px;
            min-height: 100vh;
        }

        /* === HEADER SECTION === */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            background: white;
            padding: 8px 15px;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f0f0f0;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            margin-right: 12px;
        }

        .user-info h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .user-info span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* === CARDS & TABLES === */
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-md);
            background: white;
            overflow: hidden;
            margin-bottom: 25px;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header span,
        .card-header h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            margin: 0;
        }

        .card-header i {
            margin-right: 10px;
            color: var(--primary);
        }

        .card-body {
            padding: 25px;
        }

        .card-body.p-0 {
            padding: 0;
        }

        /* Tabel Modern - General */
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #f9fafb;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 18px 25px;
            border-bottom: 1px solid #eee;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 18px 25px;
            vertical-align: middle;
            border-bottom: 1px solid #f4f4f4;
            color: #555;
            font-weight: 500;
        }

        /* === TABLE STYLES SPECIFIC FOR BERITA & GALERI (MODERN) === */
        .table-modern tbody tr {
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8faff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            z-index: 10;
            position: relative;
        }

        /* Thumbnail */
        .content-thumbnail {
            width: 80px;
            height: 55px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content-title {
            font-weight: 700;
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
            font-size: 0.95rem;
            text-decoration: none;
        }

        .content-desc {
            font-size: 0.8rem;
            color: #94a3b8;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        /* Helpers */
        .img-preview {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            object-fit: cover;
        }

        /* Tombol Kustom */
        .btn-primary-custom {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .btn-primary-custom:hover {
            background-color: var(--secondary);
            color: white;
            transform: translateY(-2px);
        }

        /* Input Form Modern */
        .form-control,
        .form-select {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #fcfcfc;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            background-color: white;
        }

        /* Search Input Modern */
        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input {
            padding-left: 40px !important;
            border-radius: 50px !important;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .search-input:focus {
            background: white;
        }

        /* Form Container Style */
        .form-container-view {
            max-width: 900px;
            margin: 0 auto;
        }

        /* === MODERN UPLOAD BOX STYLE === */
        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .upload-box:hover {
            border-color: var(--primary);
            background-color: #f8faff;
        }

        .upload-icon-circle {
            width: 60px;
            height: 60px;
            background-color: #e0e7ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .upload-icon-circle i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .upload-text-title {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .upload-text-muted {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .file-input-hidden {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Tampilan Preview */
        .preview-box {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
            background: #fff;
            padding: 10px;
        }

        .preview-box img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            display: block;
            border-radius: 8px;
        }

        .btn-remove-preview {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            color: #e63946;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.2s;
            z-index: 10;
        }

        .btn-remove-preview:hover {
            transform: scale(1.1);
            color: red;
        }

        /* === ASSET SHOWCASE STYLES (LOGO & MASKOT) === */
        .asset-card {
            transition: transform 0.3s ease;
            border: 0;
            overflow: hidden;
            border-radius: 20px;
        }

        .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .asset-img-wrapper {
            background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            background-color: white;
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }

        .asset-img-wrapper img {
            max-width: 70%;
            max-height: 80%;
            object-fit: contain;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.15));
            transition: all 0.5s ease;
        }

        .asset-card:hover .asset-img-wrapper img {
            transform: scale(1.05);
        }

        .asset-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 700;
            color: var(--primary);
            font-size: 0.8rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        /* Submission Detail Styles */
        .submission-detail-header {
            background: white;
            padding: 30px;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 25px;
        }

        .submission-meta-badge {
            background: #f1f5f9;
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
            font-weight: 600;
        }

        .submission-content-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .submission-image-wrapper {
            background-color: #f8f9fa;
            width: 100%;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .submission-hero-img {
            max-width: 100%;
            height: auto;
            max-height: 600px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .submission-body {
            padding: 40px;
        }

        .submission-body p {
            line-height: 1.8;
            font-size: 1rem;
            color: #4b5563;
        }

        .action-bar {
            position: sticky;
            bottom: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            z-index: 900;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: -260px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* SweetAlert Customization to match theme */
        .swal2-popup {
            font-family: 'Nunito', sans-serif;
            border-radius: 16px;
        }

        .swal2-confirm {
            background-color: var(--primary) !important;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3) !important;
        }

        .swal2-confirm:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.5) !important;
        }

        /* Style for Contact Message View */
        .contact-view-table td {
            padding: 10px;
            vertical-align: top;
        }

        .contact-view-label {
            font-weight: 700;
            color: var(--text-main);
            width: 100px;
        }

        .contact-view-value {
            color: #555;
        }

        /* Timeline Style (For Dashboard) */
        .timeline .border-start {
            border-color: #e9ecef !important;
        }

        .timeline .rounded-circle {
            box-shadow: 0 0 0 4px #fff;
        }

        /* Dashboard Minimal Stats */
        .dash-stat-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s;
        }

        .dash-stat-item:hover {
            transform: translateY(-3px);
        }

        .dash-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1rem;
        }

        .dash-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dash-value {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }

        /* Dropdown Custom for Chart Filter */
        .chart-filter-select {
            padding: 5px 15px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            background-color: #fff;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            outline: none;
        }

        .chart-filter-select:focus {
            border-color: var(--primary);
        }

        /* Chart Navigation Buttons */
        .chart-range-btn {
            padding: 5px 15px;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
            background: white;
            color: var(--text-muted);
            font-weight: 600;
            transition: all 0.2s;
        }

        .chart-range-btn:hover {
            background: #f8f9fa;
            color: var(--primary);
        }

        .btn-group-chart .btn:first-child {
            border-radius: 50px 0 0 50px;
        }

        .btn-group-chart .btn:last-child {
            border-radius: 0 50px 50px 0;
        }

        .btn-group-chart .btn.active {
            background-color: #f1f5f9;
            color: var(--primary);
        }

        /* Icon Preview Box for Fasilitas */
        .icon-preview {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
        }
    </style>
    <script>
        window.onpageshow = function(event) {
            if (event.persisted) window.location.reload();
        };
    </script>
</head>

<body>

    <div class="sidebar">
        <a href="#" class="sidebar-brand">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Business Analytics Logo">
            <span>Lab Admin</span>
        </a>

        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="sidebar-header">Utama</li>
                <li><a class="nav-link active" href="#" data-page="beranda"><i class="fas fa-th-large"></i><span>Dashboard</span></a></li>

                <li class="sidebar-header">Manajemen Profil</li>
                <li><a class="nav-link" href="#" data-page="identitas"><i class="fas fa-id-card"></i><span>Identitas Lab</span></a></li>
                <li><a class="nav-link" href="#" data-page="visi_misi"><i class="fas fa-bullseye"></i><span>Visi & Misi</span></a></li>
                <li><a class="nav-link" href="#" data-page="roadmap"><i class="fas fa-map-signs"></i><span>Roadmap</span></a></li>
                <li><a class="nav-link" href="#" data-page="research_focus"><i class="fas fa-microscope"></i><span>Research Focus</span></a></li>
                <li><a class="nav-link" href="#" data-page="makna_logo"><i class="fas fa-shapes"></i><span>Logo & Maskot</span></a></li>
                <li><a class="nav-link" href="#" data-page="anggota"><i class="fas fa-user-tie"></i><span>Dosen/Anggota</span></a></li>

                <li class="sidebar-header">Konten Website</li>
                <li><a class="nav-link" href="#" data-page="berita"><i class="fas fa-newspaper"></i><span>Berita</span></a></li>
                <li><a class="nav-link" href="#" data-page="galeri"><i class="fas fa-images"></i><span>Galeri Foto</span></a></li>
                <li><a class="nav-link" href="#" data-page="news_service"><i class="fas fa-inbox"></i><span>News Service</span></a></li>

                <li class="sidebar-header">Fasilitas & Booking</li>
                <li><a class="nav-link" href="#" data-page="fasilitas"><i class="fas fa-desktop"></i><span>Fasilitas Lab</span></a></li>
                <li><a class="nav-link" href="#" data-page="booking"><i class="fas fa-calendar-alt"></i><span>Data Booking</span></a></li>

                <li class="sidebar-header">Lainnya</li>
                <li><a class="nav-link" href="#" data-page="kontak"><i class="fas fa-envelope"></i><span>Pesan Masuk</span></a></li>
                <li><a class="nav-link" href="#" data-page="users"><i class="fas fa-users-cog"></i><span>Manajemen User</span></a></li>
                <li><a class="nav-link" href="#" data-page="log"><i class="fas fa-history"></i><span>Log Aktivitas</span></a></li>

                <li>
                    <a class="nav-link btn-logout" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i><span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title" id="page-title-text">Dashboard</h1>
                <p class="text-muted m-0">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></p>
            </div>

            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama'] ?? 'A'); ?>&background=4361ee&color=fff" class="user-avatar">
                <div class="user-info d-none d-md-block me-2">
                    <h6><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></h6>
                    <span>Administrator</span>
                </div>
                <i class="fas fa-chevron-down text-muted ms-2 small"></i>
            </div>
        </div>

        <div id="content-area"></div>
    </div>

    <div id="modal-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // === 1. NAVIGATION HANDLER ===
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === 'logout.php') return;
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                const titleText = this.querySelector('span').innerText;
                document.getElementById('page-title-text').innerText = titleText;
                loadPage(this.dataset.page);
            });
        });

        // === 2. ROUTER HALAMAN ===
        function loadPage(page) {
            const content = document.getElementById('content-area');
            // Hancurkan instance chart lama jika ada sebelum memuat halaman baru
            if (window.dashboardChart instanceof Chart) {
                window.dashboardChart.destroy();
            }
            content.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></div>';

            switch (page) {
                case 'beranda':
                    loadBeranda();
                    break;
                case 'identitas':
                    loadIdentitas();
                    break;
                case 'visi_misi':
                    loadVisiMisi();
                    break;
                case 'roadmap':
                    loadRoadmap();
                    break;
                case 'research_focus':
                    loadResearchFocus();
                    break;
                case 'makna_logo':
                    loadMaknaLogo();
                    break;
                case 'anggota':
                    loadAnggota();
                    break;
                case 'berita':
                    loadBerita();
                    break;
                case 'galeri':
                    loadGaleri();
                    break;
                case 'news_service':
                    loadNewsService();
                    break;
                case 'fasilitas':
                    loadFasilitas();
                    break;
                case 'booking':
                    loadBooking();
                    break;
                case 'users':
                    loadUsers();
                    break;
                case 'kontak':
                    loadKontak();
                    break;
                case 'log':
                    loadLog();
                    break;
                default:
                    content.innerHTML = '<div class="alert alert-warning">Halaman tidak ditemukan.</div>';
            }
        }

        // ==========================================
        // === DASHBOARD (CHART NAVIGATION LOGIC) ===
        // ==========================================

        // Global variables state chart
        window.dashboardData = {};
        window.chartPageOffset = 0; // 0 = Hari ini (Past 7), 1 = Next 7, -1 = Prev 7
        window.isMonthlyView = false;
        window.selectedYear = new Date().getFullYear();
        window.currentChartCategory = 'booking'; // Default category

        function loadBeranda() {
            document.getElementById('page-title-text').innerText = 'Dashboard Overview';

            // Fetch Semua Data Secara Paralel
            Promise.all([
                fetch('api/peminjaman.php').then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch('api/news_service.php').then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch('api/berita.php').then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch('api/kontak.php').then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch('api/galeri.php').then(r => r.json()).catch(() => ({
                    data: []
                })),
                fetch('api/log.php?action=list').then(r => r.json()).catch(() => ({
                    data: []
                }))
            ]).then(([bookingRes, newsRes, beritaRes, kontakRes, galeriRes, logRes]) => {

                // Simpan data mentah
                window.dashboardData = {
                    booking: bookingRes.data || [],
                    newsInput: newsRes.data || [],
                    berita: beritaRes.data || [],
                    kontak: kontakRes.data || [],
                    galeri: galeriRes.data || []
                };

                // Statistik
                const pendingBooking = window.dashboardData.booking.filter(b => b.status === 'Pending').length;
                const pendingNews = window.dashboardData.newsInput.length;
                const totalBerita = window.dashboardData.berita.length;
                const unreadPesan = window.dashboardData.kontak.filter(k => k.is_read == 0).length;
                const logs = (logRes.data || []).slice(0, 10);
                const currentAdminName = "<?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?>";

                const html = `
                <div class="fade-in">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-stat-item">
                                <div class="dash-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-calendar-check"></i></div>
                                <div><div class="dash-value">${pendingBooking}</div><div class="dash-label">Booking Pending</div></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-stat-item">
                                <div class="dash-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-newspaper"></i></div>
                                <div><div class="dash-value">${pendingNews}</div><div class="dash-label">News Request</div></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-stat-item">
                                <div class="dash-icon bg-success bg-opacity-10 text-success"><i class="fas fa-file-alt"></i></div>
                                <div><div class="dash-value">${totalBerita}</div><div class="dash-label">Total Berita</div></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="dash-stat-item">
                                <div class="dash-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-envelope"></i></div>
                                <div><div class="dash-value">${unreadPesan}</div><div class="dash-label">Pesan Baru</div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <h5 class="fw-bold text-dark mb-0">Statistik Data</h5>
                                    
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="btn-group btn-group-chart me-2" role="group">
                                            <button type="button" class="btn chart-range-btn" onclick="navigateChart(-1)" title="7 Hari Sebelumnya"><i class="fas fa-chevron-left"></i></button>
                                            <button type="button" class="btn chart-range-btn fw-bold px-3" onclick="resetChart()" title="Reset ke Hari Ini">7 Hari Saat Ini</button>
                                            <button type="button" class="btn chart-range-btn" onclick="navigateChart(1)" title="7 Hari Kedepan"><i class="fas fa-chevron-right"></i></button>
                                        </div>

                                        <div class="d-flex align-items-center bg-white border rounded px-2" style="height: 31px;">
                                            <i class="fas fa-calendar-alt text-muted small me-2"></i>
                                            <select id="chartYearSelect" class="border-0 bg-transparent text-muted fw-bold small" style="outline:none; cursor:pointer;" onchange="changeYearFilter(this)">
                                                </select>
                                        </div>

                                        <select id="chartFilter" class="chart-filter-select">
                                            <option value="booking">Data Booking</option>
                                            <option value="newsInput">News Input</option>
                                            <option value="galeri">Galeri Foto</option>
                                            <option value="berita">Berita Pub</option>
                                            <option value="kontak">Pesan Masuk</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <p class="text-muted small mb-3" id="chartDesc">Memuat grafik...</p>
                                    <div style="height: 350px; width: 100%;">
                                        <canvas id="dailyStatsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                             <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4 d-flex justify-content-between">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-user-shield me-2"></i>Admin</h5>
                                    <span class="badge bg-success bg-opacity-10 text-success small border border-success border-opacity-25">● Online</span>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(currentAdminName)}&background=4361ee&color=fff" class="rounded-circle me-3" width="50" height="50" alt="Admin Avatar">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">${currentAdminName}</h6>
                                            <span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">Administrator Utama</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="card border-0 shadow-sm" style="max-height: 500px;">
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4 sticky-top">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                                </div>
                                <div class="card-body p-4 overflow-auto" style="max-height: 400px;">
                                    <div class="timeline">
                                        ${logs.map((log, i) => `
                                            <div class="d-flex pb-4 position-relative">
                                                ${i !== logs.length - 1 ? `<div class="position-absolute top-0 start-0 h-100 border-start border-2 border-light ms-3" style="z-index: 0; margin-top: 10px;"></div>` : ''}
                                                <div class="flex-shrink-0 position-relative z-1">
                                                    <div class="bg-white border border-2 border-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;"><i class="fas fa-dot-circle text-primary small"></i></div>
                                                </div>
                                                <div class="ms-3 w-100">
                                                    <div class="d-flex justify-content-between"><span class="fw-bold text-dark small">${log.nama}</span><small class="text-muted" style="font-size: 0.7rem;">${log.waktu}</small></div>
                                                    <div class="mb-1"><span class="badge bg-light text-secondary border small" style="font-size: 0.65rem;">${log.aktivitas}</span></div>
                                                    <p class="text-muted small mb-0 bg-light p-2 rounded">${log.deskripsi}</p>
                                                </div>
                                            </div>`).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                document.getElementById('content-area').innerHTML = html;

                // --- POPULATE YEAR DROPDOWN ---
                const yearSelect = document.getElementById('chartYearSelect');
                const currentYear = new Date().getFullYear();
                // Generate range: 1 year back, current, 1 year forward
                for (let y = currentYear - 1; y <= currentYear + 1; y++) {
                    const opt = document.createElement('option');
                    opt.value = y;
                    opt.text = y;
                    if (y === currentYear) opt.selected = true;
                    yearSelect.appendChild(opt);
                }

                // Setup Canvas
                window.chartCtx = document.getElementById('dailyStatsChart').getContext('2d');

                // Initialize Default State
                window.chartPageOffset = 0;
                window.isMonthlyView = false;
                window.selectedYear = currentYear;
                updateDashboardChart();

                // Listener Dropdown Kategori
                document.getElementById('chartFilter').addEventListener('change', function() {
                    window.currentChartCategory = this.value;
                    updateDashboardChart();
                });

            }).catch(err => {
                console.error(err);
                document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat dashboard.</div>`;
            });
        }

        // === LOGIKA NAVIGASI CHART ===

        function navigateChart(direction) {
            window.isMonthlyView = false; // Matikan mode bulanan jika navigasi harian
            window.chartPageOffset += direction; // Tambah/Kurang offset (1 unit = 7 hari)
            updateDashboardChart();
        }

        function resetChart() {
            window.isMonthlyView = false;
            window.chartPageOffset = 0; // Reset ke hari ini
            updateDashboardChart();
        }

        function changeYearFilter(selectElement) {
            window.isMonthlyView = true;
            window.selectedYear = parseInt(selectElement.value);
            updateDashboardChart();
        }

        function updateDashboardChart() {
            let dataSet = window.dashboardData[window.currentChartCategory];
            let processedData;
            let descText = "";

            if (window.isMonthlyView) {
                // --- MODE BULANAN (BERDASARKAN TAHUN PILIHAN) ---
                processedData = processMonthlyData(dataSet, window.selectedYear);
                descText = `Menampilkan akumulasi data per bulan untuk Tahun ${window.selectedYear}.`;
            } else {
                // --- MODE HARIAN (Navigasi) ---
                // Hitung rentang hari berdasarkan offset

                let endDay = window.chartPageOffset * 7;
                let startDay = endDay - 6;

                // Penyesuaian agar "Next" bergerak ke masa depan (positif) dan "Return" ke masa lalu (negatif)
                if (window.chartPageOffset > 0) {
                    startDay = (window.chartPageOffset - 1) * 7 + 1;
                    endDay = startDay + 6;
                } else if (window.chartPageOffset < 0) {
                    endDay = window.chartPageOffset * 7;
                    startDay = endDay - 6;
                } else {
                    // Offset 0 (Default: -6 s/d 0)
                    endDay = 0;
                    startDay = -6;
                }

                processedData = processDataRange(dataSet, startDay, endDay);

                // Tentukan Text Deskripsi
                if (window.chartPageOffset === 0) descText = "Menampilkan data 7 hari terakhir (Hari Ini).";
                else if (window.chartPageOffset > 0) descText = `Menampilkan proyeksi 7 hari ke depan (Halaman ${window.chartPageOffset}).`;
                else descText = `Menampilkan riwayat data masa lalu (Halaman ${Math.abs(window.chartPageOffset)}).`;
            }

            document.getElementById('chartDesc').innerText = descText;
            renderChart(processedData);
        }

        // Helper: Proses Range Hari
        function processDataRange(dataSet, startOffset, endOffset) {
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const labels = [];
            const counts = [];

            // Loop dari startOffset ke endOffset
            for (let i = startOffset; i <= endOffset; i++) {
                const d = new Date();
                d.setDate(d.getDate() + i);

                const dateString = d.toISOString().split('T')[0];
                const dayName = days[d.getDay()];
                const dayDate = d.getDate() + '/' + (d.getMonth() + 1);

                labels.push(`${dayName} (${dayDate})`);

                const count = dataSet.filter(item => {
                    let itemDate = item.tanggal_pengajuan || item.tanggal_upload || item.created_at || item.tanggal_booking || item.waktu || '';
                    return itemDate.startsWith(dateString);
                }).length;

                counts.push(count);
            }
            return {
                labels,
                counts
            };
        }

        // Helper: Proses Bulanan (Sesuai Tahun Pilihan)
        function processMonthlyData(dataSet, targetYear) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const counts = new Array(12).fill(0);

            dataSet.forEach(item => {
                let itemDateStr = item.tanggal_pengajuan || item.tanggal_upload || item.created_at || item.tanggal_booking || item.waktu || '';
                if (itemDateStr) {
                    const itemDate = new Date(itemDateStr);
                    if (itemDate.getFullYear() === targetYear) {
                        counts[itemDate.getMonth()]++;
                    }
                }
            });
            return {
                labels: months,
                counts: counts
            };
        }

        // Render Chart.js
        function renderChart(chartData) {
            if (window.dashboardChart instanceof Chart) window.dashboardChart.destroy();

            let gradient = window.chartCtx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(67, 97, 238, 0.5)');
            gradient.addColorStop(1, 'rgba(67, 97, 238, 0.05)');

            window.dashboardChart = new Chart(window.chartCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Jumlah Data',
                        data: chartData.counts,
                        borderColor: '#4361ee',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4361ee',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(c) {
                                    return c.parsed.y + ' Item';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Nunito', sans-serif"
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    family: "'Nunito', sans-serif"
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        // ==========================================
        // === [MODIFIED] MANAJEMEN ROADMAP UI ===
        // ==========================================

        let allRoadmapData = [];

        function loadRoadmap() {
            document.getElementById('page-title-text').innerText = 'Manajemen Roadmap';

            // Fetch data dari API roadmap
            fetch('api/roadmap.php')
                .then(r => {
                    if (!r.ok) throw new Error('API Error');
                    return r.text();
                })
                .then(text => {
                    try {
                        const result = JSON.parse(text);
                        allRoadmapData = result.success ? result.data : [];

                        const html = `
                        <div class="fade-in">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Timeline Roadmap</h5>
                                    <p class="text-muted small mb-0">Atur perjalanan dan target laboratorium per tahun.</p>
                                </div>
                                <button class="btn btn-primary-custom rounded-pill px-4" onclick="openRoadmapForm()">
                                    <i class="fas fa-plus me-1"></i> Tambah Roadmap
                                </button>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-modern align-middle">
                                            <thead class="bg-white">
                                                <tr>
                                                    <th class="ps-4 text-center" style="width: 80px;">Urutan</th>
                                                    <th class="text-center" style="width: 150px;">Tahun</th>
                                                    <th>Judul & Deskripsi</th>
                                                    <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="roadmapTableBody">
                                                ${allRoadmapData.length === 0 ? 
                                                    `<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data roadmap.</td></tr>` : 
                                                    renderRoadmapRows(allRoadmapData)}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                        document.getElementById('content-area').innerHTML = html;
                    } catch (e) {
                        console.error('Invalid JSON:', text);
                        document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${e.message}. Cek Console untuk detail.</div>`;
                    }
                })
                .catch(err => {
                    document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${err}</div>`;
                });
        }

        function renderRoadmapRows(data) {
            return data.map(item => `
                <tr>
                    <td class="text-center">
                        <span class="badge bg-light text-secondary border rounded-pill px-3">${item.urutan}</span>
                    </td>
                    <td class="fw-bold text-primary text-center">
                        ${item.tahun}
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${item.judul}</div>
                        <div class="text-muted small text-truncate" style="max-width: 400px;">
                            ${item.deskripsi || '-'}
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light text-primary border shadow-sm me-1 rounded-2" onclick="openRoadmapForm(${item.id_roadmap})" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-danger border shadow-sm rounded-2" onclick="deleteRoadmap(${item.id_roadmap})" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function openRoadmapForm(id = null) {
            if (id) {
                fetch(`api/roadmap.php?id=${id}`).then(r => r.json()).then(res => renderRoadmapFormHTML(res.data));
            } else {
                renderRoadmapFormHTML(null);
            }
        }

        function renderRoadmapFormHTML(data) {
            const isEdit = data !== null;
            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Roadmap' : 'Tambah Roadmap Baru';

            // --- LOGIKA SPLIT TAHUN ---
            // Mengambil string tahun dari DB (misal: "2023 - 2025") dan memecahnya menjadi start dan end
            let valStart = '';
            let valEnd = '';

            if (isEdit && data.tahun) {
                // Cek jika ada pemisah " - " atau " s/d "
                // Kita ambil angka saja untuk memudahkan
                let numbers = data.tahun.match(/\d+/g);
                if (numbers) {
                    if (numbers.length >= 1) valStart = numbers[0];
                    if (numbers.length >= 2) valEnd = numbers[1];
                } else {
                    valStart = data.tahun; // Fallback jika tidak ada angka
                }
            }

            const html = `
            <div class="form-container-view fade-in">
                <form id="formRoadmap">
                    <input type="hidden" name="id_roadmap" value="${isEdit ? data.id_roadmap : ''}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadRoadmap()">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                         </button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill">
                            <i class="fas fa-save me-2"></i> Simpan Data
                         </button>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Detail Roadmap</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small">Judul Target</label>
                                    <input type="text" name="judul" class="form-control" value="${isEdit ? data.judul : ''}" required placeholder="Contoh: Pengembangan Big Data Lab">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Urutan Tampil (Unik)</label>
                                    <input type="number" name="urutan" class="form-control" value="${isEdit ? data.urutan : ''}" required placeholder="Contoh: 1, 2, dst">
                                    <div class="form-text text-muted small">Angka urutan tidak boleh sama dengan roadmap lain.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Periode Waktu (Tahun)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Dari</span>
                                        <input type="number" id="val_tahun_start" class="form-control" placeholder="Tahun Awal (ex: 3)" value="${valStart}" required>
                                        <span class="input-group-text bg-light">Sampai</span>
                                        <input type="number" id="val_tahun_end" class="form-control" placeholder="Tahun Akhir (ex: 5)" value="${valEnd}" required>
                                        <span class="input-group-text bg-light">Tahun</span>
                                    </div>
                                    <div class="form-text text-muted small">Masukkan rentang waktu. Contoh: 3 s/d 5 Tahun.</div>
                                </div>
                                
                                <div class="col-12 mt-3">
                                    <label class="form-label fw-bold small">Deskripsi Lengkap</label>
                                    <textarea name="deskripsi" class="form-control" rows="5" placeholder="Jelaskan detail pencapaian...">${isEdit ? (data.deskripsi || '') : ''}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;

            document.getElementById('content-area').innerHTML = html;

            // --- CUSTOM SUBMIT HANDLER ---
            document.getElementById('formRoadmap').onsubmit = (e) => {
                e.preventDefault();

                const form = e.target;
                const inputUrutan = parseInt(form.urutan.value);
                const currentId = form.id_roadmap.value;

                // 1. VALIDASI URUTAN (Frontend Check)
                // Cek apakah ada data lain di array allRoadmapData yang punya urutan sama
                const isDuplicate = allRoadmapData.some(item => {
                    // Jika sedang edit, abaikan jika urutan sama dengan id diri sendiri
                    if (currentId && item.id_roadmap == currentId) return false;
                    return item.urutan == inputUrutan;
                });

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan!',
                        text: `Urutan nomor ${inputUrutan} sudah digunakan. Harap masukkan angka urutan yang berbeda.`
                    });
                    return; // Stop proses submit
                }

                // 2. GABUNGKAN INPUT TAHUN
                // Mengambil value dari 2 input terpisah
                const tStart = document.getElementById('val_tahun_start').value;
                const tEnd = document.getElementById('val_tahun_end').value;

                // Format gabungan: "start - end" (Contoh: "3 - 5")
                // Backend kemungkinan menyimpan ini sebagai string di kolom 'tahun'
                const combinedTahun = `${tStart} - ${tEnd}`;

                // Buat FormData manual dan inject nilai gabungan
                const formData = new FormData(form);
                formData.set('tahun', combinedTahun); // Override field 'tahun' (atau tambah baru jika belum ada di form)

                // Kirim ke server
                submitFormPage('api/roadmap.php', formData, loadRoadmap);
            };
        }

        function deleteRoadmap(id) {
            Swal.fire({
                title: 'Hapus Roadmap?',
                text: "Data ini tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);

                    fetch('api/roadmap.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                                loadRoadmap();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        });
                }
            });
        }

        // ==========================================
        // === MANAJEMEN RESEARCH FOCUS (LENGKAP) ===
        // ==========================================

        let allResearchData = [];

        function loadResearchFocus() {
            document.getElementById('page-title-text').innerText = 'Fokus Riset';

            // Menggunakan .text() dulu baru parse JSON untuk mencegah error "Unexpected token <"
            fetch('api/research_focus.php')
                .then(response => response.text())
                .then(text => {
                    try {
                        const result = JSON.parse(text);
                        allResearchData = result.success ? result.data : [];
                        renderResearchView();
                    } catch (e) {
                        console.error("Error Parsing JSON:", text);
                        document.getElementById('content-area').innerHTML =
                            `<div class="alert alert-danger">
                                <strong>Gagal memuat data (Server Error).</strong><br>
                                Kemungkinan ada error PHP. Cek console browser untuk detailnya.
                             </div>`;
                    }
                })
                .catch(err => {
                    document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Koneksi Error: ${err}</div>`;
                });
        }

        function renderResearchView() {
            const html = `
            <div class="fade-in">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Bidang Penelitian</h5>
                        <p class="text-muted small mb-0">Kelola daftar fokus riset unggulan laboratorium.</p>
                    </div>
                    <button class="btn btn-primary-custom rounded-pill px-4" onclick="openResearchForm()">
                        <i class="fas fa-plus me-1"></i> Tambah Fokus
                    </button>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="ps-4 text-center" style="width: 80px;">Urutan</th>
                                        <th style="width: 120px;">Cover</th>
                                        <th>Judul & Deskripsi</th>
                                        <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="researchTableBody">
                                    ${allResearchData.length === 0 ? 
                                        `<tr><td colspan="4" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-microscope fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data fokus riset.</p></div></td></tr>` : 
                                        renderResearchRows(allResearchData)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

            document.getElementById('content-area').innerHTML = html;
        }

        function renderResearchRows(data) {
            return data.map(item => {
                const imgPath = fixImagePath(item.file_path);
                return `
                <tr>
                    <td class="text-center ps-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 35px; height: 35px; font-size: 0.9rem;">
                            ${item.urutan}
                        </span>
                    </td>
                    <td>
                        <img src="${imgPath}" class="rounded shadow-sm border" style="width: 80px; height: 55px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/80x55?text=No+Img'">
                    </td>
                    <td>
                        <div class="fw-bold text-dark mb-1 fs-6">${item.judul}</div>
                        <div class="text-muted small text-truncate" style="max-width: 500px;">
                            ${item.deskripsi || '<i class="text-light-emphasis">Tidak ada deskripsi</i>'}
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light text-primary border shadow-sm me-1 rounded-2" onclick="openResearchForm(${item.id_research})" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-danger border shadow-sm rounded-2" onclick="deleteResearch(${item.id_research})" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `
            }).join('');
        }

        function openResearchForm(id = null) {
            if (id) {
                // Fetch single data
                fetch(`api/research_focus.php?id=${id}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) renderResearchFormHTML(res.data);
                        else Swal.fire('Error', 'Data tidak ditemukan', 'error');
                    })
                    .catch(err => Swal.fire('Error', 'Gagal memuat data', 'error'));
            } else {
                renderResearchFormHTML(null);
            }
        }

        function renderResearchFormHTML(data) {
            const isEdit = data !== null;
            const hasImage = isEdit && data.file_path && data.file_path !== '';

            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Fokus Riset' : 'Tambah Fokus Riset';

            const html = `
            <div class="form-container-view fade-in">
                <form id="formResearch">
                    <input type="hidden" name="id_research" value="${isEdit ? data.id_research : ''}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadResearchFocus()">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                         </button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill">
                            <i class="fas fa-save me-2"></i> ${isEdit ? 'Simpan Perubahan' : 'Simpan Data'}
                         </button>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Informasi Detail</h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <label class="form-label fw-bold small">Judul Riset</label>
                                            <input type="text" name="judul" class="form-control fw-bold" value="${isEdit ? data.judul : ''}" required placeholder="Contoh: Big Data Analytics">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Urutan Tampil</label>
                                            <input type="number" name="urutan" class="form-control" value="${isEdit ? data.urutan : ''}" required placeholder="1, 2, ...">
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="form-label fw-bold small">Deskripsi Lengkap</label>
                                            <textarea name="deskripsi" class="form-control" rows="10" placeholder="Jelaskan secara rinci mengenai fokus penelitian ini...">${isEdit ? (data.deskripsi || '') : ''}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Gambar Ilustrasi</h6>
                                    
                                    <div class="upload-container">
                                        <div id="uploadPlaceholder" class="upload-box ${hasImage ? 'd-none' : ''}" style="height: 250px;">
                                            <input type="file" name="foto" id="fileInput" class="file-input-hidden" accept="image/*" onchange="previewFile(this)">
                                            <div class="upload-icon-circle" style="width:60px; height:60px;"><i class="fas fa-image fs-3"></i></div>
                                            <div class="upload-text-title small mt-2">Upload Gambar</div>
                                            <div class="upload-text-muted small">Format JPG/PNG (Max 2MB)</div>
                                        </div>
                                        
                                        <div id="imagePreviewContainer" class="preview-box ${hasImage ? '' : 'd-none'}">
                                            <img src="${hasImage ? fixImagePath(data.file_path) : ''}" id="previewImg" alt="Preview">
                                            <button type="button" class="btn-remove-preview" onclick="resetUpload()" title="Hapus/Ganti Foto">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-light mt-3 small text-muted border border-light mb-0">
                                        <i class="fas fa-info-circle me-1"></i> Gambar ini akan ditampilkan di halaman depan (Landing Page) pada bagian Research Focus.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;

            document.getElementById('content-area').innerHTML = html;

            // Handle Submit Form
            document.getElementById('formResearch').onsubmit = (e) => {
                e.preventDefault();
                submitFormPage('api/research_focus.php', new FormData(e.target), loadResearchFocus);
            };
        }

        function deleteResearch(id) {
            Swal.fire({
                title: 'Hapus Fokus Riset?',
                text: "Data dan foto yang terkait akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);

                    // Menggunakan fetch manual agar bisa handle error text/json
                    fetch('api/research_focus.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.text())
                        .then(text => {
                            try {
                                const res = JSON.parse(text);
                                if (res.success) {
                                    Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                                    loadResearchFocus();
                                } else {
                                    Swal.fire('Gagal!', res.message, 'error');
                                }
                            } catch (e) {
                                console.error(text);
                                Swal.fire('Error', 'Terjadi kesalahan server saat menghapus.', 'error');
                            }
                        });
                }
            });
        }

        // ==========================================
        // === 3. MANAJEMEN BERITA (SESUAI API) ===
        // ==========================================

        let allBeritaData = [];

        function getBeritaBadge(category) {
            switch ((category || '').toLowerCase()) {
                case 'news latest':
                    return 'badge-news-latest';
                case 'prestasi':
                    return 'badge-prestasi';
                case 'announcement':
                    return 'badge-announcement';
                default:
                    return 'badge-default';
            }
        }

        function loadBerita() {
            document.getElementById('page-title-text').innerText = 'Manajemen Berita';
            fetch('api/berita.php').then(r => r.json()).then(result => {
                allBeritaData = result.success ? result.data : [];
                const html = `
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="search-input-wrapper w-50">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchBerita" class="form-control search-input" placeholder="Cari judul berita..." onkeyup="filterBerita()">
                            </div>
                            <div class="d-flex gap-2">
                                <select id="filterKategori" class="form-select border-0 shadow-sm" style="width: 170px; border-radius: 50px; cursor: pointer;" onchange="filterBerita()">
                                    <option value="all">Semua Kategori</option>
                                    <option value="News Latest">News Latest</option>
                                    <option value="Prestasi">Prestasi</option>
                                    <option value="Announcement">Announcement</option>
                                </select>
                                <button class="btn-primary-custom rounded-pill px-4" onclick="openBeritaForm()"><i class="fas fa-plus me-1"></i> Tambah Berita</button>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4" style="width: 120px;">Cover</th>
                                                <th>Judul & Ringkasan</th>
                                                <th class="text-center" style="width: 15%;">Kategori</th>
                                                <th class="text-center" style="width: 20%;">Author</th>
                                                <th class="text-end pe-4" style="width: 15%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="beritaTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('content-area').innerHTML = html;
                renderBeritaTable(allBeritaData);
            });
        }

        function renderBeritaTable(data) {
            const tbody = document.getElementById('beritaTableBody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-newspaper fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data berita.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(i => {
                let displayImage = fixImagePath(i.file_path);
                const uploaderName = i.nama_pengupload || i.uploaded_by || 'Admin';
                const roleName = i.role_pengupload || 'Administrator';
                let dateDisplay = i.tanggal_upload ? i.tanggal_upload.split(' ')[0] : '-';
                let timeDisplay = '00:00';
                if (i.updated_at) {
                    let parts = i.updated_at.split(' ');
                    if (parts.length > 1) {
                        timeDisplay = parts[1].substring(0, 5);
                    }
                }

                return `<tr>
                <td class="ps-4">
                    <img src="${displayImage}" class="content-thumbnail" onerror="this.src='https://via.placeholder.com/80x55?text=No+Img'">
                </td>
                <td>
                    <div class="content-title text-truncate" style="max-width: 350px;">${i.judul}</div>
                    <small class="text-muted"><i class="far fa-clock me-1"></i> ${dateDisplay} ${timeDisplay}</small>
                </td>
                <td class="text-center">
                    <span class="badge-modern ${getBeritaBadge(i.kategori)}">${i.kategori}</span>
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column align-items-center">
                        <span class="small fw-bold text-dark">${uploaderName}</span>
                        <span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill" style="font-size: 0.65rem; padding: 2px 8px;">
                            ${roleName}
                        </span>
                    </div>
                </td>
                <td class="text-end pe-4">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick="openBeritaForm(${i.id_artikel})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deleteBerita(${i.id_artikel}, this)" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>`;
            }).join('');
        }

        function deleteBerita(id, btn) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Berita ini akan dihapus secara permanen beserta fotonya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    fetch('api/berita.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                allBeritaData = allBeritaData.filter(b => b.id_artikel != id);
                                renderBeritaTable(allBeritaData);
                                Swal.fire('Terhapus!', 'Berita berhasil dihapus.', 'success');
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        });
                }
            });
        }

        function filterBerita() {
            const cat = document.getElementById('filterKategori').value;
            const search = document.getElementById('searchBerita').value.toLowerCase();
            const filtered = allBeritaData.filter(i => {
                const uploader = i.nama_pengupload || i.uploaded_by || '';
                return (cat === 'all' || i.kategori === cat) && (i.judul.toLowerCase().includes(search) || uploader.toLowerCase().includes(search));
            });
            renderBeritaTable(filtered);
        }

        function openBeritaForm(id = null) {
            if (id) fetch(`api/berita.php?id=${id}`).then(r => r.json()).then(res => renderBeritaFormHTML(res.data));
            else renderBeritaFormHTML(null);
        }

        function renderBeritaFormHTML(data) {
            const isEdit = data !== null;
            const hasImage = isEdit && data.file_path && data.file_path !== '';
            const previewSrc = hasImage ? fixImagePath(data.file_path) : '';
            const tglVal = isEdit && data.tanggal_upload ? data.tanggal_upload.split(' ')[0] : new Date().toISOString().split('T')[0];
            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Berita' : 'Buat Berita Baru';
            const html = `
                <div class="form-container-view fade-in">
                    <form id="formBerita">
                        <input type="hidden" name="id_artikel" value="${isEdit?data.id_artikel:''}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadBerita()"><i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar</button>
                             <button type="submit" class="btn btn-primary-custom px-5 rounded-pill"><i class="fas fa-save me-2"></i> ${isEdit ? 'Simpan Perubahan' : 'Terbitkan Berita'}</button>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Konten Utama</h6>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Judul Berita</label>
                                            <input type="text" name="judul" class="form-control form-control-lg fw-bold" value="${isEdit?data.judul:''}" required placeholder="Masukkan judul headline berita...">
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Isi Berita</label>
                                            <textarea name="deskripsi" class="form-control" rows="15" required placeholder="Tuliskan isi berita lengkap di sini...">${isEdit?(data.konten||''):''}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Pengaturan Publikasi</h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted">Kategori</label>
                                            <select name="kategori" class="form-select" required>
                                                <option value="">-- Pilih --</option>
                                                <option value="News Latest" ${isEdit && data.kategori == 'News Latest' ? 'selected' : ''}>News Latest</option>
                                                <option value="Prestasi" ${isEdit && data.kategori == 'Prestasi' ? 'selected' : ''}>Prestasi</option>
                                                <option value="Announcement" ${isEdit && data.kategori == 'Announcement' ? 'selected' : ''}>Announcement</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted">Tanggal Tayang</label>
                                            <input type="date" name="tanggal" class="form-control" value="${tglVal}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Media Unggulan</h6>
                                        <div class="upload-container">
                                            <div id="uploadPlaceholder" class="upload-box ${hasImage ? 'd-none' : ''}" style="height: 200px;">
                                                <input type="file" name="foto" id="fileInput" class="file-input-hidden" accept="image/*" onchange="previewFile(this)">
                                                <div class="upload-icon-circle" style="width:40px; height:40px;"><i class="fas fa-image fs-5"></i></div>
                                                <div class="upload-text-title small">Upload Foto</div>
                                                <div class="upload-text-muted small">JPG/PNG Max 2MB</div>
                                            </div>
                                            <div id="imagePreviewContainer" class="preview-box ${hasImage ? '' : 'd-none'}">
                                                <img src="${previewSrc}" id="previewImg" alt="Preview">
                                                <button type="button" class="btn-remove-preview" onclick="resetUpload()" title="Hapus Foto"><i class="fas fa-trash-alt"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>`;
            document.getElementById('content-area').innerHTML = html;
            document.getElementById('formBerita').onsubmit = (e) => {
                e.preventDefault();
                submitFormPage('api/berita.php', new FormData(e.target), loadBerita);
            };
        }


        // ==========================================
        // === 4. MANAJEMEN GALERI (TABLE VIEW) ===
        // ==========================================

        let allGaleriData = [];

        function getGaleriBadge(category) {
            switch ((category || '').toLowerCase()) {
                case 'kategori 1':
                    return 'badge-news-latest';
                case 'kategori 2':
                    return 'badge-prestasi';
                case 'kategori 3':
                    return 'badge-announcement';
                case 'kategori 4':
                    return 'badge-kegiatan';
                default:
                    return 'badge-default';
            }
        }

        function loadGaleri() {
            document.getElementById('page-title-text').innerText = 'Manajemen Galeri';
            fetch('api/galeri.php').then(r => r.json()).then(result => {
                allGaleriData = result.success ? result.data : [];
                const html = `
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="search-input-wrapper w-50">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchGaleri" class="form-control search-input" placeholder="Cari foto galeri..." onkeyup="filterGaleri()">
                            </div>
                            <div class="d-flex gap-2">
                                <select id="filterKategoriGaleri" class="form-select border-0 shadow-sm" style="width: 170px; border-radius: 50px; cursor: pointer;" onchange="filterGaleri()">
                                    <option value="all">Semua Kategori</option>
                                    <option value="Kategori 1">Kategori 1</option>
                                    <option value="Kategori 2">Kategori 2</option>
                                    <option value="Kategori 3">Kategori 3</option>
                                    <option value="Kategori 4">Kategori 4</option>
                                </select>
                                <button class="btn-primary-custom rounded-pill px-4" onclick="openGaleriForm()"><i class="fas fa-plus me-1"></i> Tambah Foto</button>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4" style="width: 120px;">Foto</th>
                                                <th>Judul & Deskripsi</th>
                                                <th class="text-center">Kategori</th>
                                                <th class="text-center">Author</th>
                                                <th class="text-center">Tanggal</th>
                                                <th class="text-end pe-4">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="galeriTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('content-area').innerHTML = html;
                renderGaleriTable(allGaleriData);
            });
        }

        function renderGaleriTable(data) {
            const tbody = document.getElementById('galeriTableBody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-images fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data galeri.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(i => {
                let displayImage = fixImagePath(i.file_path);
                let dateDisplay = i.tanggal_upload ? i.tanggal_upload.split(' ')[0] : '-';
                let timeDisplay = '00:00';
                if (i.updated_at) {
                    let parts = i.updated_at.split(' ');
                    if (parts.length > 1) {
                        timeDisplay = parts[1].substring(0, 5);
                    }
                }
                const uploaderName = i.nama_pengupload || '-';
                const roleName = i.role_pengupload || '-';

                return `<tr>
                <td class="ps-4">
                    <img src="${displayImage}" class="content-thumbnail" onerror="this.src='https://via.placeholder.com/80x55?text=No+Img'">
                </td>
                <td>
                    <div class="content-title text-truncate" style="max-width: 350px;">${i.judul}</div>
                    <small class="content-desc">${i.deskripsi || 'Tidak ada deskripsi'}</small>
                </td>
                <td class="text-center">
                    <span class="badge-modern ${getGaleriBadge(i.kategori)}">${i.kategori}</span>
                </td>
                <td class="text-center">
                      <div class="d-flex flex-column align-items-center">
                        <span class="small fw-bold text-dark">${uploaderName}</span>
                         <span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill" style="font-size: 0.65rem; padding: 2px 8px;">
                            ${roleName}
                        </span>
                    </div>
                </td>
                <td class="text-center text-muted small">
                    <i class="far fa-clock me-1"></i> ${dateDisplay} ${timeDisplay}
                </td>
                <td class="text-end pe-4">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick="openGaleriForm(${i.id_galeri})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deleteGaleri(${i.id_galeri}, this)" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>`;
            }).join('');
        }

        function deleteGaleri(id, btn) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Foto ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    fetch('api/galeri.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                allGaleriData = allGaleriData.filter(g => g.id_galeri != id);
                                renderGaleriTable(allGaleriData);
                                Swal.fire('Terhapus!', 'Foto berhasil dihapus.', 'success');
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        });
                }
            });
        }

        function filterGaleri() {
            const cat = document.getElementById('filterKategoriGaleri').value;
            const search = document.getElementById('searchGaleri').value.toLowerCase();
            const filtered = allGaleriData.filter(i => {
                const uploader = i.nama_pengupload || '';
                return (cat === 'all' || i.kategori === cat) && (i.judul.toLowerCase().includes(search) || uploader.toLowerCase().includes(search));
            });
            renderGaleriTable(filtered);
        }

        function openGaleriForm(id = null) {
            if (id) fetch(`api/galeri.php?id=${id}`).then(r => r.json()).then(res => renderGaleriFormHTML(res.data));
            else renderGaleriFormHTML(null);
        }

        function renderGaleriFormHTML(data = null) {
            const isEdit = data !== null;
            const hasImage = isEdit && data.file_path && data.file_path !== '';
            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Galeri' : 'Tambah Foto Galeri';
            const html = `
            <div class="form-container-view fade-in">
                <form id="formGaleri">
                    <input type="hidden" name="id_galeri" value="${isEdit?data.id_galeri:''}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadGaleri()"><i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar</button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill"><i class="fas fa-save me-2"></i> ${isEdit ? 'Simpan Foto' : 'Upload Foto'}</button>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Informasi Foto</h6>
                                    <div class="mb-4">
                                        <label class="fw-bold form-label">Judul Foto</label>
                                        <input type="text" name="judul" class="form-control form-control-lg fw-bold" value="${isEdit?data.judul:''}" required placeholder="Contoh: Kegiatan Workshop Data...">
                                    </div>
                                    <div class="mb-0">
                                        <label class="fw-bold form-label">Deskripsi Singkat</label>
                                        <textarea name="deskripsi" class="form-control" rows="8" placeholder="Jelaskan sedikit tentang foto ini...">${isEdit?data.deskripsi:''}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Kategori</h6>
                                    <div class="mb-2">
                                        <select name="kategori" class="form-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Kategori 1" ${isEdit && data.kategori=='Kategori 1'?'selected':''}>Kategori 1</option>
                                            <option value="Kategori 2" ${isEdit && data.kategori=='Kategori 2'?'selected':''}>Kategori 2</option>
                                            <option value="Kategori 3" ${isEdit && data.kategori=='Kategori 3'?'selected':''}>Kategori 3</option>
                                            <option value="Kategori 4" ${isEdit && data.kategori=='Kategori 4'?'selected':''}>Kategori 4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">File Foto</h6>
                                    <div class="upload-container">
                                        <div id="uploadPlaceholder" class="upload-box ${hasImage ? 'd-none' : ''}" style="height: 200px;">
                                            <input type="file" name="foto" id="fileInput" class="file-input-hidden" accept="image/*" onchange="previewFile(this)">
                                            <div class="upload-icon-circle" style="width:40px; height:40px;"><i class="fas fa-image fs-5"></i></div>
                                            <div class="upload-text-title small">Upload Foto</div>
                                            <div class="upload-text-muted small">JPG/PNG Max 2MB</div>
                                        </div>
                                        <div id="imagePreviewContainer" class="preview-box ${hasImage ? '' : 'd-none'}">
                                            <img src="${hasImage ? data.file_path : ''}" id="previewImg" alt="Preview">
                                            <button type="button" class="btn-remove-preview" onclick="resetUpload()" title="Hapus Foto"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;
            document.getElementById('content-area').innerHTML = html;
            document.getElementById('formGaleri').onsubmit = (e) => {
                e.preventDefault();
                submitFormPage('api/galeri.php', new FormData(e.target), loadGaleri);
            }
        }

        // ==========================================
        // === 5. NEWS SERVICE ===
        // ==========================================

        function loadNewsService() {
            document.getElementById('page-title-text').innerText = 'News Service';
            fetch('api/news_service.php').then(r => r.json()).then(result => {
                const data = result.data || [];
                const html = `
                    <div class="fade-in">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">User Submissions</h5>
                                    <p class="text-muted small mb-0">Daftar berita yang dikirimkan oleh pengguna menunggu persetujuan.</p>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fs-6 border border-warning border-opacity-25">
                                    <i class="fas fa-clock me-2"></i> ${data.length} Pending
                                </span>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light"><tr><th class="ps-4">Preview</th><th>Judul Berita</th><th class="text-center">Kategori</th> <th>Pengirim</th><th class="text-center">Tanggal</th><th class="text-end pe-4">Aksi</th></tr></thead>
                                        <tbody id="submissionTableBody">${data.length === 0 ? '<tr><td colspan="6" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i><p class="mb-0">Tidak ada pengajuan berita baru.</p></div></td></tr>' : ''}</tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('content-area').innerHTML = html;
                if (data.length > 0) renderNewsServiceTable(data);
            }).catch(err => {
                document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${err}</div>`;
            });
        }

        function renderNewsServiceTable(data) {
            const tbody = document.getElementById('submissionTableBody');
            tbody.innerHTML = data.map(item => `
                <tr style="cursor: pointer;" onclick='viewSubmissionDetail(${JSON.stringify(item)})'>
                    <td class="ps-4" style="width: 100px;"><img src="${item.foto_path}" class="rounded-3 shadow-sm" style="width:70px; height:50px; object-fit:cover;" onerror="this.src='https://via.placeholder.com/60?text=No+Img'"></td>
                    <td><div class="fw-bold text-dark text-truncate" style="max-width: 300px;">${item.judul}</div></td>
                    <td class="text-center"><span class="badge-modern ${getBeritaBadge(item.kategori)}">${item.kategori}</span></td>
                    <td><div class="d-flex align-items-center"><div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">${item.uploaded_by.charAt(0).toUpperCase()}</div><span class="fw-semibold text-dark">${item.uploaded_by}</span></div></td>
                    <td class="text-muted small text-center">${item.tanggal_upload}</td>
                    <td class="text-end pe-4"><button class="btn btn-sm btn-light text-primary border-0 rounded-pill px-3 fw-bold" onclick='event.stopPropagation(); viewSubmissionDetail(${JSON.stringify(item)})'>Review <i class="fas fa-arrow-right ms-1"></i></button></td>
                </tr>`).join('');
        }

        function viewSubmissionDetail(item) {
            window.scrollTo(0, 0);
            document.getElementById('page-title-text').innerText = 'Review Berita';
            const html = `
                <div class="fade-in">
                    <button class="btn btn-link text-decoration-none text-muted mb-3 ps-0" onclick="loadNewsService()"><i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar</button>
                    <div class="submission-detail-header d-flex justify-content-between align-items-start"><div><div class="d-flex mb-3"><div class="submission-meta-badge"><i class="far fa-calendar-alt"></i> ${item.tanggal_upload}</div><div class="submission-meta-badge"><i class="far fa-user"></i> ${item.uploaded_by}</div><div class="submission-meta-badge" style="background: transparent; padding: 0;"><span class="badge-modern ${getBeritaBadge(item.kategori)}"><i class="fas fa-tag me-2"></i> ${item.kategori}</span></div></div><h2 class="fw-bold text-dark mb-2" style="line-height: 1.3;">${item.judul}</h2></div></div>
                    <div class="submission-content-card"><div class="submission-image-wrapper"><img src="${item.foto_path}" class="submission-hero-img" alt="${item.judul}"></div><div class="submission-body"><h5 class="fw-bold mb-3 text-secondary text-uppercase fs-6 tracking-wide">Isi Berita</h5><p style="white-space: pre-line;">${item.deskripsi}</p></div></div>
                    <div class="action-bar"><span class="text-muted fw-bold small"><i class="fas fa-info-circle me-1"></i> Konfirmasi Penerbitan</span><div class="d-flex gap-3"><button class="btn btn-outline-danger px-4 rounded-pill fw-bold" onclick="processSubmission('reject', ${item.id_submission})"><i class="fas fa-times me-2"></i>Tolak Berita</button><button class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" onclick="processSubmission('approve', ${item.id_submission})"><i class="fas fa-check me-2"></i>Setujui & Terbitkan</button></div></div>
                </div>`;
            document.getElementById('content-area').innerHTML = html;
        }

        function processSubmission(action, id) {
            Swal.fire({
                title: action === 'approve' ? 'Terbitkan Berita?' : 'Tolak Berita?',
                text: action === 'approve' ? 'Berita akan dipublikasikan.' : 'Berita akan ditolak.',
                icon: action === 'approve' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4361ee',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Proses!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', action);
                    formData.append('id', id);
                    fetch('api/news_service.php', {
                        method: 'POST',
                        body: formData
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            Swal.fire('Berhasil', res.message, 'success');
                            loadNewsService();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }).catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
                    });
                }
            });
        }

        // ==========================================
        // === 6. MANAJEMEN FASILITAS LAB (BARU) ===
        // ==========================================

        let allFasilitasData = [];

        function loadFasilitas() {
            document.getElementById('page-title-text').innerText = 'Fasilitas Laboratorium';

            // Mengambil semua data dari api/fasilitas.php
            fetch('api/fasilitas.php')
                .then(r => r.json())
                .then(result => {
                    allFasilitasData = result.success ? result.data : [];

                    const html = `
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="search-input-wrapper w-50">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchFasilitas" class="form-control search-input" placeholder="Cari nama fasilitas..." onkeyup="filterFasilitas()">
                            </div>
                            <button class="btn btn-primary-custom rounded-pill px-4" onclick="openFasilitasForm()">
                                <i class="fas fa-plus me-1"></i> Tambah Fasilitas
                            </button>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4 text-center" style="width: 80px;">Icon</th>
                                                <th>Nama Fasilitas</th>
                                                <th class="text-center">Diupdate Oleh</th>
                                                <th class="text-center">Tanggal Dibuat</th>
                                                <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fasilitasTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    document.getElementById('content-area').innerHTML = html;
                    renderFasilitasTable(allFasilitasData);
                })
                .catch(err => {
                    document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data fasilitas: ${err}</div>`;
                });
        }

        function renderFasilitasTable(data) {
            const tbody = document.getElementById('fasilitasTableBody');

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-box-open fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data fasilitas.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(item => `
                <tr>
                    <td class="ps-4 text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 45px; height: 45px;">
                            <i class="${item.icon || 'bi bi-box-seam'} fs-5"></i>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-6">${item.nama_fasilitas}</div>
                        <div class="small text-muted font-monospace">${item.icon}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-secondary border rounded-pill px-3">
                            <i class="fas fa-user me-1 text-muted small"></i> ${item.uploader_name || 'Admin'}
                        </span>
                    </td>
                    <td class="text-center text-muted small">
                        ${item.created_at ? item.created_at.split(' ')[0] : '-'}
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light text-primary border shadow-sm me-1 rounded-2" onclick="openFasilitasForm(${item.id_facility})" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-danger border shadow-sm rounded-2" onclick="deleteFasilitas(${item.id_facility})" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function filterFasilitas() {
            const search = document.getElementById('searchFasilitas').value.toLowerCase();
            const filtered = allFasilitasData.filter(item => {
                return item.nama_fasilitas.toLowerCase().includes(search);
            });
            renderFasilitasTable(filtered);
        }

        function openFasilitasForm(id = null) {
            if (id) {
                // Fetch single data: sesuai logic API "if (isset($_GET['id']))"
                fetch(`api/fasilitas.php?id=${id}`).then(r => r.json()).then(res => renderFasilitasFormHTML(res.data));
            } else {
                renderFasilitasFormHTML(null);
            }
        }

        function renderFasilitasFormHTML(data) {
            const isEdit = data !== null;
            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Fasilitas' : 'Tambah Fasilitas Baru';

            const defaultIcon = 'bi bi-box-seam';
            const currentIcon = isEdit ? (data.icon || defaultIcon) : defaultIcon;

            const html = `
            <div class="form-container-view fade-in" style="max-width: 600px;">
                <form id="formFasilitas">
                    <!-- ID FACILITY untuk Update (Sesuai API: $_POST['id_facility']) -->
                    <input type="hidden" name="id_facility" value="${isEdit ? data.id_facility : ''}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadFasilitas()">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                         </button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill">
                            <i class="fas fa-save me-2"></i> Simpan
                         </button>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Informasi Fasilitas</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Fasilitas</label>
                                <!-- NAMA FASILITAS (Sesuai API: $_POST['nama_fasilitas']) -->
                                <input type="text" name="nama_fasilitas" class="form-control" value="${isEdit ? data.nama_fasilitas : ''}" required placeholder="Contoh: Komputer High-End, Proyektor, dll">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Icon Class (Bootstrap Icons / FontAwesome)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white p-0 border-end-0">
                                        <div class="icon-preview border-0 rounded-0 rounded-start bg-light" style="width:45px; height:38px;">
                                            <i id="iconPreview" class="${currentIcon}"></i>
                                        </div>
                                    </span>
                                    <!-- ICON (Sesuai API: $_POST['icon']) -->
                                    <input type="text" name="icon" id="inputIcon" class="form-control border-start-0" value="${currentIcon}" placeholder="Contoh: bi bi-wifi" onkeyup="updateIconPreview(this.value)">
                                </div>
                                <div class="form-text text-muted small">
                                    Gunakan class dari <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> (bi bi-...) atau <a href="https://fontawesome.com/v5/search" target="_blank">FontAwesome</a> (fas fa-...).
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;

            document.getElementById('content-area').innerHTML = html;

            document.getElementById('formFasilitas').onsubmit = (e) => {
                e.preventDefault();
                // Submit ke api/fasilitas.php (Logic SAVE/UPDATE)
                submitFormPage('api/fasilitas.php', new FormData(e.target), loadFasilitas);
            };
        }

        // Helper untuk preview icon real-time
        function updateIconPreview(val) {
            const preview = document.getElementById('iconPreview');
            if (val.trim() === '') {
                preview.className = 'bi bi-question-circle';
            } else {
                preview.className = val;
            }
        }

        function deleteFasilitas(id) {
            Swal.fire({
                title: 'Hapus Fasilitas?',
                text: "Data ini tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    // Sesuai API: $_POST['action'] === 'delete'
                    formData.append('action', 'delete');
                    // Sesuai API: $id = $_POST['id']
                    formData.append('id', id);

                    fetch('api/fasilitas.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus!', 'Fasilitas berhasil dihapus.', 'success');
                                loadFasilitas();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        });
                }
            });
        }

        // ==========================================
        // === 7. MANAJEMEN DATA BOOKING (LENGKAP) ===
        // ==========================================

        let allBookingData = [];

        function getBookingBadge(status) {
            switch ((status || '').toLowerCase()) {
                case 'approved':
                    return 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                case 'pending':
                    return 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                case 'rejected':
                    return 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                case 'cancellation_requested':
                    return 'bg-info bg-opacity-10 text-info border border-info border-opacity-25';
                case 'cancelled':
                    return 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25';
                case 'completed':
                    return 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                default:
                    return 'bg-light text-muted border';
            }
        }

        function getStatusIcon(status) {
            switch ((status || '').toLowerCase()) {
                case 'approved':
                    return '<i class="fas fa-check-circle me-1"></i>';
                case 'pending':
                    return '<i class="fas fa-clock me-1"></i>';
                case 'rejected':
                    return '<i class="fas fa-times-circle me-1"></i>';
                case 'cancellation_requested':
                    return '<i class="fas fa-exclamation-circle me-1"></i>';
                case 'cancelled':
                    return '<i class="fas fa-ban me-1"></i>';
                case 'completed':
                    return '<i class="fas fa-flag-checkered me-1"></i>';
                default:
                    return '';
            }
        }

        function loadBooking() {
            document.getElementById('page-title-text').innerText = 'Data Booking Lab';

            fetch('api/peminjaman.php')
                .then(r => r.json())
                .then(result => {
                    allBookingData = result.success ? result.data : [];
                    renderBookingView();
                })
                .catch(err => {
                    document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data booking: ${err}</div>`;
                });
        }

        function renderBookingView() {
            const html = `
            <div class="fade-in">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="search-input-wrapper w-50">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchBooking" class="form-control search-input" placeholder="Cari pemohon, tujuan, atau instansi..." onkeyup="filterBooking()">
                    </div>
                    <div class="d-flex gap-2">
                        <select id="filterStatusBooking" class="form-select border-0 shadow-sm" style="width: 170px; border-radius: 50px; cursor: pointer;" onchange="filterBooking()">
                            <option value="all">Semua Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Cancellation_Requested">Req. Batal</option>
                            <option value="Completed">Selesai</option>
                            <option value="Rejected">Ditolak</option>
                            <option value="Cancelled">Dibatalkan</option>
                        </select>
                        <button class="btn btn-light border shadow-sm rounded-pill px-3" onclick="loadBooking()" title="Refresh Data">
                            <i class="fas fa-sync-alt text-muted"></i>
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="ps-4">Pemohon</th>
                                        <th>Kegiatan & Waktu</th>
                                        <th class="text-center">Kategori</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bookingTableBody">
                                    ${allBookingData.length === 0 ? 
                                        `<tr><td colspan="5" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-calendar-times fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data booking.</p></div></td></tr>` : 
                                        renderBookingRows(allBookingData)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

            document.getElementById('content-area').innerHTML = html;
        }

        function renderBookingRows(data) {
            return data.map(item => {
                // --- LOGIKA BARU: DETEKSI STATUS REQ BATAL ---
                // Kita buat variabel displayStatus untuk tampilan saja
                // Jika request_pembatalan = true, kita paksa status jadi 'Cancellation_Requested'
                // kecuali jika admin sudah membatalkan/menolak (status final).

                let displayStatus = item.status;

                // Cek berbagai variasi boolean (1, true, 't', '1') karena beda database beda output
                const isReqCancel = item.request_pembatalan === true || item.request_pembatalan === 1 || item.request_pembatalan === 't' || item.request_pembatalan === '1';

                if (isReqCancel) {
                    if (item.status !== 'Cancelled' && item.status !== 'Rejected') {
                        displayStatus = 'Cancellation_Requested';
                    }
                }

                // Format Tanggal yang rapi
                const start = new Date(item.check_in).toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const end = new Date(item.check_out).toLocaleString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const timeString = `${start} - ${end}`;

                // Safe string untuk dikirim ke fungsi onclick
                const itemSafe = JSON.stringify(item).replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                return `
        <tr style="cursor: pointer;" onclick='viewBookingDetail(${itemSafe})'>
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3 border" style="width: 40px; height: 40px;">
                        ${item.nama_akun ? item.nama_akun.charAt(0).toUpperCase() : '?'}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">${item.nama_akun}</div>
                        <div class="small text-muted">${item.email_akun}</div>
                        <div class="small text-muted" style="font-size: 0.75rem;">${item.no_hp || '-'}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="fw-bold text-primary mb-1">${item.tujuan}</div>
                <div class="text-muted small"><i class="far fa-clock me-1 text-warning"></i> ${timeString}</div>
                <div class="text-muted small mt-1"><i class="fas fa-building me-1 text-secondary"></i> ${item.asal_instansi || '-'}</div>
            </td>
            <td class="text-center">
                <span class="badge bg-light text-dark border rounded-pill px-3">${item.kategori_pemohon}</span>
            </td>
            <td class="text-center">
                <span class="badge ${getBookingBadge(displayStatus)} rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                    ${getStatusIcon(displayStatus)} ${displayStatus.replace('Cancellation_Requested', 'Req. Batal').replace('_', ' ')}
                </span>
                ${displayStatus === 'Cancellation_Requested' ? '<div class="mt-1"><small class="text-danger fw-bold animate__animated animate__flash">Meminta Pembatalan!</small></div>' : ''}
            </td>
            <td class="text-end pe-4">
                <button class="btn btn-sm btn-light text-primary border shadow-sm rounded-pill px-3 fw-bold" onclick='event.stopPropagation(); viewBookingDetail(${itemSafe})'>
                    Detail <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </td>
        </tr>
    `
            }).join('');
        }

        function filterBooking() {
            const search = document.getElementById('searchBooking').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatusBooking').value;

            const filtered = allBookingData.filter(item => {
                const matchSearch = (item.nama_akun || '').toLowerCase().includes(search) ||
                    (item.tujuan || '').toLowerCase().includes(search) ||
                    (item.asal_instansi || '').toLowerCase().includes(search);
                const matchStatus = statusFilter === 'all' || item.status === statusFilter;
                return matchSearch && matchStatus;
            });

            document.getElementById('bookingTableBody').innerHTML = filtered.length === 0 ?
                `<tr><td colspan="5" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>` :
                renderBookingRows(filtered);
        }

        function viewBookingDetail(item) {
            // Scroll ke atas agar user sadar halaman berubah
            window.scrollTo(0, 0);
            document.getElementById('page-title-text').innerText = 'Detail Booking';

            // Format Tanggal Lengkap
            // Fallback ke tanggal hari ini jika created_at kosong
            const tglPengajuan = new Date(item.created_at || new Date()).toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const checkInFull = new Date(item.check_in).toLocaleString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            const checkOutFull = new Date(item.check_out).toLocaleString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            // --- LOGIKA BARU: DETEKSI STATUS ---
            let displayStatus = item.status;
            const isReqCancel = item.request_pembatalan === true || item.request_pembatalan === 1 || item.request_pembatalan === 't' || item.request_pembatalan === '1';

            if (isReqCancel) {
                if (item.status !== 'Cancelled' && item.status !== 'Rejected') {
                    displayStatus = 'Cancellation_Requested';
                }
            }

            // --- GENERATE TOMBOL AKSI SESUAI STATUS ---
            let actionButtons = '';

            if (displayStatus === 'Pending') {
                actionButtons = `
            <button class="btn btn-outline-danger rounded-pill fw-bold px-4 me-2" onclick="updateBookingStatus(${item.id_peminjaman}, 'Rejected')"><i class="fas fa-times me-2"></i>Tolak</button>
            <button class="btn btn-primary-custom rounded-pill fw-bold px-4" onclick="updateBookingStatus(${item.id_peminjaman}, 'Approved')"><i class="fas fa-check me-2"></i>Setujui</button>
        `;
            } else if (displayStatus === 'Approved') {
                actionButtons = `
            <button class="btn btn-outline-secondary rounded-pill fw-bold px-4 me-2" onclick="updateBookingStatus(${item.id_peminjaman}, 'Cancelled')"><i class="fas fa-ban me-2"></i>Batalkan</button>
            <button class="btn btn-success rounded-pill fw-bold px-4" onclick="updateBookingStatus(${item.id_peminjaman}, 'Completed')"><i class="fas fa-flag-checkered me-2"></i>Selesai</button>
        `;
            } else if (displayStatus === 'Cancellation_Requested') {
                // TAMPILAN KHUSUS REQUEST BATAL
                actionButtons = `
            <div class="alert alert-warning border-warning d-flex align-items-center w-100 mb-3">
                <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                <div>
                    <strong>Permintaan Pembatalan!</strong><br>
                    Alasan User: "<em>${item.alasan_pembatalan || '-'}</em>"
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end w-100">
                <button class="btn btn-secondary rounded-pill fw-bold px-4" onclick="updateBookingStatus(${item.id_peminjaman}, 'Approved')"><i class="fas fa-undo me-2"></i>Tolak (Tetap Lanjut)</button>
                <button class="btn btn-danger rounded-pill fw-bold px-4" onclick="updateBookingStatus(${item.id_peminjaman}, 'Cancelled')"><i class="fas fa-check me-2"></i>Konfirmasi Batal</button>
            </div>
        `;
            } else {
                // Status Final (Selesai/Ditolak/Dibatalkan) -> Hanya tombol hapus
                actionButtons = `<button class="btn btn-light border text-danger rounded-pill fw-bold px-4" onclick="deleteBooking(${item.id_peminjaman})"><i class="fas fa-trash-alt me-2"></i>Hapus Permanen</button>`;
            }

            const html = `
    <div class="fade-in">
        <button class="btn btn-link text-decoration-none text-muted mb-3 ps-0" onclick="loadBooking()">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
        </button>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-5">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <span class="badge ${getBookingBadge(displayStatus)} rounded-pill px-3 py-2 mb-2">
                                    ${getStatusIcon(displayStatus)} ${displayStatus.replace('Cancellation_Requested', 'Req. Batal').replace('_', ' ')}
                                </span>
                                <h3 class="fw-bold text-dark mt-2">${item.tujuan}</h3>
                                <p class="text-muted small mb-0"><i class="far fa-calendar-alt me-2"></i> Diajukan pada: ${tglPengajuan}</p>
                            </div>
                            <div class="bg-light p-3 rounded-3 text-center border" style="min-width: 120px;">
                                <div class="small text-muted fw-bold text-uppercase ls-1">ID Booking</div>
                                <div class="fs-4 fw-bold text-primary">#${item.id_peminjaman}</div>
                            </div>
                        </div>

                        <hr class="opacity-10 my-4">

                        <h6 class="fw-bold text-uppercase text-muted small ls-1 mb-3">Jadwal Pemakaian</h6>
                        <div class="d-flex align-items-center mb-4 bg-primary bg-opacity-10 p-4 rounded-3 border border-primary border-opacity-10">
                            <div class="me-4 text-center">
                                <i class="fas fa-clock fa-2x text-primary"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold text-dark">${checkInFull}</div>
                                <div class="text-muted small">Sampai dengan ${checkOutFull}</div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-uppercase text-muted small ls-1 mb-3">Detail Pemohon</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                    <div class="fw-bold text-dark">${item.nama_akun}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <small class="text-muted d-block mb-1">Nomor Identitas (NIM/NIP)</small>
                                    <div class="fw-bold text-dark">${item.nomor_identitas || '-'}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <small class="text-muted d-block mb-1">Kategori</small>
                                    <div class="fw-bold text-dark">${item.kategori_pemohon}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <small class="text-muted d-block mb-1">Instansi / Prodi</small>
                                    <div class="fw-bold text-dark">${item.asal_instansi}</div>
                                </div>
                            </div>
                        </div>

                        ${(item.catatan_admin || item.alasan_pembatalan) ? `
                            <hr class="opacity-10 my-4">
                            
                            ${item.catatan_admin ? `
                            <div class="alert alert-danger border-danger bg-danger bg-opacity-10 mb-2">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-info-circle me-2"></i>Catatan Admin:</h6>
                                <p class="mb-0 text-dark small">${item.catatan_admin}</p>
                            </div>` : ''}
                            
                            ${(item.alasan_pembatalan && displayStatus === 'Cancellation_Requested') ? `
                            <div class="alert alert-warning border-warning bg-warning bg-opacity-10">
                                <h6 class="fw-bold text-warning mb-1"><i class="fas fa-user-edit me-2"></i>Alasan User Membatalkan:</h6>
                                <p class="mb-0 text-dark small">${item.alasan_pembatalan}</p>
                            </div>` : ''}
                        ` : ''}

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-3">Kontak Cepat</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fab fa-whatsapp fs-5"></i>
                            </div>
                            <div>
                                <div class="small text-muted">WhatsApp / HP</div>
                                <a href="https://wa.me/${formatPhone(item.no_hp)}" target="_blank" class="fw-bold text-dark text-decoration-none stretched-link">
                                    ${item.no_hp || '-'}
                                </a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="far fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <div class="small text-muted">Email</div>
                                <a href="mailto:${item.email_akun}" class="fw-bold text-dark text-decoration-none">
                                    ${item.email_akun}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-primary bg-opacity-10">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-3">Tindakan Admin</h6>
                        <div class="d-flex flex-column gap-2">
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;

            document.getElementById('content-area').innerHTML = html;
        }

        // Helper format HP untuk WA (ganti 08 jadi 628)
        function formatPhone(phone) {
            if (!phone) return '';
            let p = phone.replace(/\D/g, ''); // Hapus non-angka
            if (p.startsWith('0')) return '62' + p.substring(1);
            return p;
        }

        function updateBookingStatus(id, newStatus) {
            // Jika status Reject/Cancel, butuh input alasan
            if (newStatus === 'Rejected' || newStatus === 'Cancelled') {
                Swal.fire({
                    title: `Konfirmasi ${newStatus === 'Rejected' ? 'Penolakan' : 'Pembatalan'}`,
                    input: 'textarea',
                    inputLabel: 'Masukkan alasan:',
                    inputPlaceholder: 'Contoh: Jadwal bentrok, Ruangan renovasi...',
                    inputAttributes: {
                        'aria-label': 'Masukkan alasan'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    confirmButtonColor: '#e63946',
                    cancelButtonText: 'Batal',
                    preConfirm: (reason) => {
                        if (!reason) Swal.showValidationMessage('Alasan wajib diisi!');
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendUpdate(id, newStatus, result.value);
                    }
                });
            } else {
                // Status Approved/Completed langsung konfirmasi biasa
                Swal.fire({
                    title: 'Update Status?',
                    text: `Ubah status menjadi ${newStatus}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ubah',
                    confirmButtonColor: '#4361ee'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sendUpdate(id, newStatus, null);
                    }
                });
            }
        }

        function sendUpdate(id, status, reason) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);
            if (reason) formData.append('alasan_batal', reason);

            fetch('api/peminjaman.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        loadBooking(); // Kembali ke list dan refresh
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', 'Gagal koneksi server', 'error'));
        }

        function deleteBooking(id) {
            Swal.fire({
                title: 'Hapus Data Permanen?',
                text: "Data booking ini akan hilang selamanya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);

                    fetch('api/peminjaman.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                                loadBooking();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        });
                }
            });
        }

        // ==========================================
        // === 8. HELPER & UTILITIES ===
        // ==========================================

        function fixImagePath(path) {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('../')) return path;
            if (path.startsWith('admin/')) return '../' + path;
            if (path.startsWith('assets/')) return '../' + path;
            return path;
        }

        // ==========================================
        // === 9. MANAJEMEN ANGGOTA (DOSEN) - FIXED ===
        // ==========================================

        let allAnggotaData = [];
        let allUsersData = [];

        function loadAnggota() {
            document.getElementById('page-title-text').innerText = 'Manajemen Anggota Lab';

            fetch('api/anggota.php')
                .then(r => r.json())
                .then(result => {
                    if (!result.success) {
                        document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                        return;
                    }

                    allAnggotaData = result.data || [];
                    // --- TAMBAHAN KODE: SORTING (LAMA KE BARU) ---
                    // Mengurutkan berdasarkan id_dosen dari kecil ke besar
                    allAnggotaData.sort((a, b) => a.id_dosen - b.id_dosen);
                    // ---------------------------------------------

                    const html = `
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="search-input-wrapper w-50">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchAnggota" class="form-control search-input" placeholder="Cari nama, NIDN..." onkeyup="filterAnggota()">
                            </div>
                            <button class="btn btn-primary-custom rounded-pill px-4" onclick="openAnggotaForm()">
                                <i class="fas fa-user-plus me-1"></i> Tambahkan Anggota
                            </button>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4 text-center" style="width: 100px;">Foto</th>
                                                <th>Nama & NIDN</th>
                                                <th>Keahlian & Pendidikan</th>
                                                <th class="text-center">Link</th>
                                                <th class="text-end pe-4" style="width: 150px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="anggotaTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    document.getElementById('content-area').innerHTML = html;
                    renderAnggotaTable(allAnggotaData);
                })
                .catch(err => console.error(err));
        }

        function renderAnggotaTable(data) {
            const tbody = document.getElementById('anggotaTableBody');

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data anggota.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(item => {
                // Tampilan Foto (Fix Path)
                const photoSrc = (item.foto && item.foto !== '') ?
                    item.foto + '?t=' + new Date().getTime() :
                    `https://ui-avatars.com/api/?name=${encodeURIComponent(item.nama)}&background=random&color=fff`;

                // Link Icons
                let linksHtml = '';
                const links = item.links_map || {};
                if (links['Sinta']) linksHtml += `<a href="${links['Sinta']}" target="_blank" class="btn btn-sm btn-light text-primary mx-1"><i class="fas fa-book"></i></a>`;
                if (links['Scopus']) linksHtml += `<a href="${links['Scopus']}" target="_blank" class="btn btn-sm btn-light text-warning mx-1"><i class="fas fa-star"></i></a>`;
                if (links['Google Scholar']) linksHtml += `<a href="${links['Google Scholar']}" target="_blank" class="btn btn-sm btn-light text-success mx-1"><i class="fas fa-graduation-cap"></i></a>`;

                return `
                <tr>
                    <td class="ps-4 text-center">
                        <img src="${photoSrc}" class="rounded-circle shadow-sm border" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60'">
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${item.nama}</div>
                        <div class="small text-muted">${item.nidn || '-'}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary small">${item.pendidikan || '-'}</div>
                        <div class="text-muted small text-truncate" style="max-width: 200px;">${item.keahlian || '-'}</div>
                    </td>
                    <td class="text-center">${linksHtml || '-'}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-light text-primary me-1" onclick="openAnggotaForm(${item.id_dosen})"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-light text-danger" onclick="deleteAnggota(${item.id_dosen})"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `
            }).join('');
        }

        // Filter sederhana
        function filterAnggota() {
            const search = document.getElementById('searchAnggota').value.toLowerCase();
            const filtered = allAnggotaData.filter(i => i.nama.toLowerCase().includes(search) || (i.nidn && i.nidn.includes(search)));
            renderAnggotaTable(filtered);
        }

        // --- FORM LOGIC (ADD/EDIT) ---
        async function openAnggotaForm(id = null) {
            // 1. Fetch Users List Terlebih Dahulu
            try {
                const resUsers = await fetch('api/anggota.php?action=get_users');
                const jsonUsers = await resUsers.json();

                if (jsonUsers.success) {
                    allUsersData = jsonUsers.data;
                } else {
                    Swal.fire("Gagal Load User", jsonUsers.message, "error");
                    return;
                }
            } catch (e) {
                Swal.fire("Koneksi Error", "Tidak bisa mengambil data user.", "error");
                return;
            }

            // 2. Load Detail Data jika Edit
            let data = null;
            if (id) {
                try {
                    const resDetail = await fetch(`api/anggota.php?action=detail&id=${id}`);
                    const jsonDetail = await resDetail.json();
                    if (jsonDetail.success) {
                        data = jsonDetail.data;
                    } else {
                        Swal.fire("Error", jsonDetail.message, "error");
                        return;
                    }
                } catch (e) {
                    console.error("Gagal load detail");
                }
            }

            const isEdit = data !== null;
            const hasImage = isEdit && data.foto && data.foto !== '';

            document.getElementById('page-title-text').innerText = isEdit ? 'Edit Data Anggota' : 'Tambah Anggota Baru';

            const currentUserName = isEdit ? data.nama : '';
            const currentUserId = isEdit ? data.id_user : '';

            const html = `
            <div class="form-container-view fade-in">
                <form id="formAnggota">
                    <input type="hidden" name="id_dosen" value="${isEdit ? data.id_dosen : ''}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadAnggota()">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                         </button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill">
                            <i class="fas fa-save me-2"></i> Simpan Data
                         </button>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Profil Akademik</h6>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-7 position-relative">
                                            <label class="form-label fw-bold small">Cari Nama Anggota (User)</label>
                                            <input type="hidden" name="id_user" id="input_id_user" value="${currentUserId}" required>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                                <input type="text" id="input_nama_user" class="form-control" 
                                                       value="${currentUserName}" 
                                                       placeholder="Ketik nama user..." 
                                                       autocomplete="off"
                                                       onkeyup="searchUserLocal(this.value)" 
                                                       onfocus="searchUserLocal(this.value)">
                                            </div>
                                            <ul id="userResultList" class="list-group position-absolute w-100 shadow d-none" 
                                                style="z-index: 9999; max-height: 250px; overflow-y: auto; top: 100%;">
                                            </ul>
                                            <div class="form-text text-muted small">Wajib memilih dari daftar yang muncul.</div>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label fw-bold small">NIDN</label>
                                            <input type="text" name="nidn" class="form-control" value="${isEdit ? data.nidn : ''}" required placeholder="Nomor Induk Dosen">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-bold small">Pendidikan Terakhir</label>
                                            <textarea name="pendidikan_terakhir" class="form-control" rows="4" placeholder="Contoh: S1 Teknik Informatika (2010), S2 Ilmu Komputer (2015)...">${isEdit ? (data.pendidikan_terakhir||'') : ''}</textarea>
                                            <div class="form-text text-muted small">Bisa diisi teks panjang.</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label fw-bold small">Bidang Keahlian</label>
                                            <textarea name="bidang_keahlian" class="form-control" rows="4" placeholder="Pisahkan dengan koma. Contoh: Data Mining, AI, Machine Learning, Big Data Analytics">${isEdit ? (data.bidang_keahlian||'') : ''}</textarea>
                                            <div class="form-text text-muted small">Jika lebih dari satu, pisahkan dengan koma (,).</div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                                <h6 class="fw-bold text-dark small text-uppercase mb-0">Tautan Publikasi</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addLinkRow()">
                                                    <i class="fas fa-plus me-1"></i> Tambah Link
                                                </button>
                                            </div>
                                            <div id="linksContainer"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-primary mb-4 text-uppercase small ls-1">Foto Profil</h6>
                                    <div class="upload-container text-center">
                                        <div id="uploadPlaceholder" class="upload-box ${hasImage ? 'd-none' : ''}" style="height: 220px;">
                                            <input type="file" name="foto" id="fileInput" class="file-input-hidden" accept="image/*" onchange="previewFile(this)">
                                            <div class="upload-icon-circle" style="width:60px; height:60px;"><i class="fas fa-camera fs-3"></i></div>
                                            <div class="upload-text-title small mt-2">Upload Foto</div>
                                            <div class="upload-text-muted small">Akan dibentuk lingkaran otomatis</div>
                                        </div>
                                        <div id="imagePreviewContainer" class="preview-box border-0 ${hasImage ? '' : 'd-none'}" style="background: transparent; box-shadow: none;">
                                            <img src="${hasImage ? fixImagePath(data.foto) : ''}" id="previewImg" alt="Preview" class="rounded-circle shadow border border-3 border-white" style="width: 180px; height: 180px; object-fit: cover;">
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="resetUpload()">
                                                    <i class="fas fa-trash-alt me-1"></i> Ganti Foto
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;

            document.getElementById('content-area').innerHTML = html;

            // Load Links
            const linksData = (isEdit && data.links_dynamic) ? data.links_dynamic : [];
            if (linksData.length > 0) {
                linksData.forEach(link => addLinkRow(link.platform, link.url));
            } else {
                addLinkRow();
            }

            // Close user list listener
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#userResultList') && !e.target.closest('#input_nama_user')) {
                    document.getElementById('userResultList').classList.add('d-none');
                }
            });

            // SUBMIT HANDLER
            document.getElementById('formAnggota').onsubmit = (e) => {
                e.preventDefault();

                // Validasi ID User
                if (document.getElementById('input_id_user').value === '') {
                    Swal.fire('Peringatan', 'Silakan cari dan pilih user terlebih dahulu.', 'warning');
                    return;
                }

                // Langsung fetch manual agar responsif
                const formData = new FormData(e.target);

                fetch('api/anggota.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire('Berhasil', 'Data anggota berhasil disimpan.', 'success');
                            loadAnggota();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    });
            };
        }

        // --- FUNGSI DELETE ---
        function deleteAnggota(id) {
            Swal.fire({
                title: 'Hapus?',
                text: "Data tidak bisa kembali!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id', id);

                    fetch('api/anggota.php', {
                            method: 'POST',
                            body: fd
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus', 'Data dihapus', 'success');
                                loadAnggota();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Koneksi error', 'error'));
                }
            });
        }

        function searchUserLocal(val) {
            const listEl = document.getElementById('userResultList');
            const keyword = val ? val.toLowerCase().trim() : '';

            if (!keyword) {
                listEl.classList.add('d-none');
                return;
            }

            // Cek apakah data user sudah dimuat
            if (!allUsersData || allUsersData.length === 0) {
                listEl.innerHTML = `<li class="list-group-item text-danger small">Data user belum siap. Coba refresh halaman.</li>`;
                listEl.classList.remove('d-none');
                return;
            }

            // [FIX SEARCH LOGIC] Gunakan nama atau email
            const filtered = allUsersData.filter(u => {
                const name = u.nama ? u.nama.toLowerCase() : '';
                const email = u.email ? u.email.toLowerCase() : '';
                return name.includes(keyword) || email.includes(keyword);
            });

            if (filtered.length === 0) {
                listEl.innerHTML = `<li class="list-group-item text-muted">User tidak ditemukan.</li>`;
            } else {
                listEl.innerHTML = filtered.map(u => `
                    <li class="list-group-item list-group-item-action cursor-pointer" onclick="selectUser(${u.id_user}, '${u.nama.replace(/'/g, "\\'")}')">
                        <div class="fw-bold">${u.nama}</div>
                        <small class="text-muted">${u.email} (${u.role})</small>
                    </li>`).join('');
            }
            listEl.classList.remove('d-none');
        }

        function selectUser(id, nama) {
            document.getElementById('input_id_user').value = id;
            document.getElementById('input_nama_user').value = nama;
            document.getElementById('userResultList').classList.add('d-none');
        }

        function addLinkRow(platform = '', url = '') {
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <select name="link_platform[]" class="form-select" style="max-width: 130px;">
                    <option value="Sinta" ${platform=='Sinta'?'selected':''}>Sinta</option>
                    <option value="Google Scholar" ${platform=='Google Scholar'?'selected':''}>Scholar</option>
                </select>
                <input type="text" name="link_url[]" class="form-control" placeholder="URL..." value="${url}">
                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">x</button>
            `;
            document.getElementById('linksContainer').appendChild(div);
        }

        function previewFile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ==========================================
        // === 10. MANAJEMEN USER (ROLE & STATUS) ===
        // ==========================================

        let allUsersList = [];

        function loadUsers() {
            document.getElementById('page-title-text').innerText = 'Manajemen Pengguna';

            fetch('api/users.php')
                .then(r => r.json())
                .then(result => {
                    if (!result.success) {
                        document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                        return;
                    }

                    allUsersList = result.data || [];

                    const html = `
                    <div class="fade-in">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="search-input-wrapper w-50">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchUser" class="form-control search-input" placeholder="Cari nama atau email..." onkeyup="filterUsers()">
                            </div>
                            <span class="badge bg-primary rounded-pill px-3 py-2">Total: ${allUsersList.length} User</span>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4">User Info</th>
                                                <th>Email</th>
                                                <th class="text-center">Role</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Last Login</th>
                                                <th class="text-end pe-4">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;

                    document.getElementById('content-area').innerHTML = html;
                    renderUserTable(allUsersList);
                })
                .catch(err => console.error(err));
        }

        function renderUserTable(data) {
            const tbody = document.getElementById('userTableBody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data user.</td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(u => {
                const roleBadge = u.role === 'admin' ?
                    '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">Admin</span>' :
                    '<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill">Mahasiswa</span>';

                const statusBadge = u.is_active == 1 ?
                    '<span class="badge bg-success bg-opacity-10 text-success rounded-pill">Active</span>' :
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Inactive</span>';

                // Safe JSON for onclick
                const userJson = JSON.stringify(u).replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                return `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3 border" style="width: 40px; height: 40px;">
                                ${u.nama.charAt(0).toUpperCase()}
                            </div>
                            <div class="fw-bold text-dark">${u.nama}</div>
                        </div>
                    </td>
                    <td class="text-muted small">${u.email}</td>
                    <td class="text-center">${roleBadge}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center text-muted small">${u.last_login || '-'}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-light text-primary border shadow-sm me-1" onclick='openUserModal(${userJson})' title="Edit Role">
                            <i class="fas fa-user-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-light text-danger border shadow-sm" onclick="deleteUser(${u.id_user})" title="Hapus User">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');
        }

        function filterUsers() {
            const search = document.getElementById('searchUser').value.toLowerCase();
            const filtered = allUsersList.filter(u => u.nama.toLowerCase().includes(search) || u.email.toLowerCase().includes(search));
            renderUserTable(filtered);
        }

        function openUserModal(user) {
            Swal.fire({
                title: 'Edit Role User',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama User</label>
                            <input type="text" class="form-control bg-light" value="${user.nama}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role Akses</label>
                            <select id="swal-role" class="form-select">
                                <option value="mahasiswa" ${user.role === 'mahasiswa' ? 'selected' : ''}>Mahasiswa</option>
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="swal-active" ${user.is_active == 1 ? 'checked' : ''}>
                            <label class="form-check-label small" for="swal-active">Akun Aktif</label>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                confirmButtonColor: '#4361ee',
                preConfirm: () => {
                    return {
                        id_user: user.id_user,
                        role: document.getElementById('swal-role').value,
                        is_active: document.getElementById('swal-active').checked ? 1 : 0
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    const fd = new FormData();
                    fd.append('action', 'update_role');
                    fd.append('id_user', data.id_user);
                    fd.append('role', data.role);
                    if (data.is_active) fd.append('is_active', 1);

                    fetch('api/users.php', {
                            method: 'POST',
                            body: fd
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Berhasil', res.message, 'success');
                                loadUsers();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
                }
            });
        }

        function deleteUser(id) {
            Swal.fire({
                title: 'Hapus User?',
                text: "User ini akan dihapus permanen. Data terkait (peminjaman, dll) mungkin juga terhapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id', id);

                    fetch('api/users.php', {
                            method: 'POST',
                            body: fd
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus!', 'User berhasil dihapus.', 'success');
                                loadUsers();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        });
                }
            });
        }

        // ==========================================
        // === [UPDATED] LOGO & MASKOT UI ===
        // ==========================================

        function loadMaknaLogo() {
            document.getElementById('page-title-text').innerText = 'Identitas Visual';
            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const v = (k) => s[k]?.value || '';
                const img = (k) => fixImagePath(s[k]?.file_path);

                const html = `
                <div class="row g-4 fade-in">
                    <div class="col-md-6">
                        <div class="card h-100 asset-card shadow-sm">
                            <div class="asset-img-wrapper">
                                <span class="asset-badge">Utama</span>
                                <img src="${img('logo')}" alt="Logo Lab" onerror="this.src='https://via.placeholder.com/300?text=No+Logo'">
                            </div>
                            <div class="card-body text-center p-4">
                                <h4 class="fw-bold mb-3 text-dark">Logo Laboratorium</h4>
                                <div class="bg-light rounded-4 p-4 text-start mb-4">
                                    <h6 class="fw-bold text-primary text-uppercase small ls-1 mb-2">Filosofi Logo</h6>
                                    <p class="text-muted mb-0" style="white-space: pre-line; line-height: 1.7;">${v('logo') || 'Belum ada deskripsi.'}</p>
                                </div>
                                <button class="btn btn-primary-custom w-100 rounded-pill py-2" onclick="openSetForm('logo','Logo','${escapeHtml(v('logo'))}', '${img('logo')}')">
                                    <i class="fas fa-pencil-alt me-2"></i> Update Logo
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 asset-card shadow-sm">
                            <div class="asset-img-wrapper">
                                <span class="asset-badge">Maskot</span>
                                <img src="${img('maskot')}" alt="Maskot Lab" onerror="this.src='https://via.placeholder.com/300?text=No+Maskot'">
                            </div>
                            <div class="card-body text-center p-4">
                                <h4 class="fw-bold mb-3 text-dark">Maskot Resmi</h4>
                                <div class="bg-light rounded-4 p-4 text-start mb-4">
                                    <h6 class="fw-bold text-primary text-uppercase small ls-1 mb-2">Deskripsi Maskot</h6>
                                    <p class="text-muted mb-0" style="white-space: pre-line; line-height: 1.7;">${v('maskot') || 'Belum ada deskripsi.'}</p>
                                </div>
                                <button class="btn btn-primary-custom w-100 rounded-pill py-2" onclick="openSetForm('maskot','Maskot','${escapeHtml(v('maskot'))}', '${img('maskot')}')">
                                    <i class="fas fa-pencil-alt me-2"></i> Update Maskot
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.getElementById('content-area').innerHTML = html;
            });
        }

        function openSetForm(k, t, v, currentImg) {
            const html = `
            <div class="form-container-view fade-in">
                <form id="fSet">
                    <input type="hidden" name="key" value="${k}">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <button type="button" class="btn btn-link text-decoration-none text-muted ps-0" onclick="loadMaknaLogo()"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
                         <button type="submit" class="btn btn-primary-custom px-5 rounded-pill"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">File Gambar</h6>
                                    <div class="upload-container">
                                        <div id="uploadPlaceholder" class="upload-box d-none" style="height: 250px;">
                                            <input type="file" name="${k}" id="fileInput" class="file-input-hidden" accept="image/*" onchange="previewFile(this)">
                                            <div class="upload-icon-circle" style="width:50px; height:50px;"><i class="fas fa-cloud-upload-alt fs-4"></i></div>
                                            <div class="upload-text-title small">Ganti Gambar</div>
                                            <div class="upload-text-muted small">Transparan PNG Disarankan</div>
                                        </div>
                                        <div id="imagePreviewContainer" class="preview-box" style="background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%); background-size: 20px 20px;">
                                            <img src="${currentImg}" id="previewImg" alt="Preview" style="max-height: 300px;">
                                            <button type="button" class="btn-remove-preview" onclick="resetUpload()" title="Ganti Gambar"><i class="fas fa-sync-alt"></i></button>
                                        </div>
                                    </div>
                                    <p class="text-center text-muted small mt-3">Klik tombol refresh di atas untuk mengganti gambar.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3 text-uppercase small ls-1">Informasi Detail</h6>
                                    <div class="mb-3">
                                        <label class="fw-bold form-label">Judul Aset</label>
                                        <input type="text" class="form-control" value="${t}" disabled style="background-color: #eee;">
                                    </div>
                                    <div class="mb-0">
                                        <label class="fw-bold form-label">Filosofi & Deskripsi</label>
                                        <textarea name="value" class="form-control" rows="10" placeholder="Jelaskan makna filosofis dari logo atau karakter maskot ini...">${v}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>`;
            document.getElementById('content-area').innerHTML = html;
            document.getElementById('fSet').onsubmit = (e) => {
                e.preventDefault();
                submitFormPage('api/settings.php', new FormData(e.target), loadMaknaLogo);
            }
        }

        // ==========================================
        // === [UPDATED] VISI & MISI UI (UNIFIED STYLE) ===
        // ==========================================

        function loadVisiMisi() {
            document.getElementById('page-title-text').innerText = 'Arah & Tujuan';
            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const vVisi = s.visi?.value || 'Belum ada data visi.';
                const vMisi = s.misi?.value || 'Belum ada data misi.';

                const html = `
                <div class="row justify-content-center fade-in">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-5">
                                <div class="d-flex justify-content-between align-items-start mb-5">
                                    <div>
                                        <h4 class="fw-bold text-dark m-0">Visi & Misi</h4>
                                        <p class="text-muted small">Landasan strategis laboratorium.</p>
                                    </div>
                                    <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary bg-primary bg-opacity-10" onclick="openVisiMisiForm()">
                                        <i class="fas fa-pencil-alt me-1"></i> Edit
                                    </button>
                                </div>

                                <div class="px-md-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <h6 class="text-uppercase text-dark fw-bold small ls-2 m-0">Visi Laboratorium</h6>
                                    </div>
                                    <div class="text-secondary ps-2 ms-5" style="font-size: 1.05rem; line-height: 1.8; white-space: pre-line;">${vVisi}</div>
                                </div>

                                <hr class="my-5 opacity-10">

                                <div class="px-md-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                            <i class="fas fa-list-ul"></i>
                                        </div>
                                        <h6 class="text-uppercase text-dark fw-bold small ls-2 m-0">Misi & Strategi</h6>
                                    </div>
                                    <div class="text-secondary ps-2 ms-5" style="font-size: 1.05rem; line-height: 1.8; white-space: pre-line;">${vMisi}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.getElementById('content-area').innerHTML = html;
            });
        }

        function openVisiMisiForm() {
            // Fetch data first to prevent quotes issues in onclick
            document.getElementById('content-area').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';

            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const v = s.visi?.value || '';
                const m = s.misi?.value || '';

                const html = `
                <div class="row justify-content-center fade-in">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-5">
                                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                                    <h5 class="fw-bold text-primary m-0"><i class="fas fa-edit me-2"></i> Update Visi & Misi</h5>
                                    <button type="button" class="btn btn-close" onclick="loadVisiMisi()"></button>
                                </div>
                                <form id="fVm">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark small text-uppercase ls-1 mb-2">Pernyataan Visi</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0 ps-3"><i class="fas fa-eye"></i></span>
                                            <textarea name="visi" class="form-control border-start-0 bg-white shadow-none" rows="3" placeholder="Masukkan visi laboratorium...">${v}</textarea>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark small text-uppercase ls-1 mb-2">Daftar Misi</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted border-end-0 ps-3 align-items-start pt-3"><i class="fas fa-list-ol"></i></span>
                                            <textarea name="misi" class="form-control border-start-0 bg-white shadow-none" rows="10" placeholder="Masukkan poin-poin misi...">${m}</textarea>
                                        </div>
                                        <div class="form-text text-muted mt-2 small"><i class="fas fa-info-circle me-1"></i> Gunakan baris baru (Enter) untuk memisahkan setiap poin misi.</div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary-custom btn-lg rounded-pill shadow-sm">
                                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.getElementById('content-area').innerHTML = html;
                document.getElementById('fVm').onsubmit = (e) => {
                    e.preventDefault();
                    submitFormPage('api/settings.php', new FormData(e.target), loadVisiMisi);
                }
            });
        }

        // ==========================================
        // === [UPDATED] IDENTITAS LAB UI ===
        // ==========================================

        function loadIdentitas() {
            document.getElementById('page-title-text').innerText = 'Profil Laboratorium';
            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const v = (k) => s[k] ? s[k].value : '';

                const html = `
                <div class="row justify-content-center fade-in">
                    <div class="col-lg-10 col-xl-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 p-md-5">

                                <div class="text-center mb-5">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-university fa-2x"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark">Informasi Umum</h4>
                                    <p class="text-muted text-center" style="max-width: 500px; margin: 0 auto;">
                                        Kelola detail identitas laboratorium yang akan ditampilkan pada halaman publik website.
                                    </p>
                                </div>

                                <form id="fId">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark small text-uppercase ls-1">Nama Laboratorium</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fas fa-building"></i></span>
                                            <input type="text" name="nama_lab" class="form-control border-start-0 bg-white" placeholder="Masukkan nama laboratorium" value="${v('nama_lab')}">
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark small text-uppercase ls-1">Email Resmi</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fas fa-envelope"></i></span>
                                                <input type="email" name="email" class="form-control border-start-0 bg-white" placeholder="email@lab.com" value="${v('email')}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark small text-uppercase ls-1">Nomor Telepon</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 text-muted ps-3"><i class="fas fa-phone"></i></span>
                                                <input type="text" name="no_telp" class="form-control border-start-0 bg-white" placeholder="+62..." value="${v('no_telp')}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-5">
                                        <label class="form-label fw-bold text-dark small text-uppercase ls-1">Alamat Lengkap</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted ps-3 pt-3 align-items-start"><i class="fas fa-map-marker-alt"></i></span>
                                            <textarea name="alamat" class="form-control border-start-0 bg-white" rows="3" placeholder="Nama Jalan, Gedung, Kota...">${v('alamat')}</textarea>
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button class="btn btn-primary-custom btn-lg rounded-pill shadow-sm">
                                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.getElementById('content-area').innerHTML = html;
                document.getElementById('fId').onsubmit = (e) => {
                    e.preventDefault();
                    submitFormPage('api/settings.php', new FormData(e.target), loadIdentitas);
                }
            });
        }

        // ==========================================
        // === UTILS (UPDATED WITH SWEETALERT2) ===
        // ==========================================

        function submitFormPage(url, data, callback) {
            fetch(url, {
                    method: 'POST',
                    body: data
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        if (callback) callback();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan koneksi'
                    });
                });
        }

        function loadLog() {
            document.getElementById('page-title-text').innerText = 'Log Aktivitas';
            fetchData('api/log.php?action=list', ['Waktu', 'User', 'Aktivitas', 'Deskripsi', 'IP'], (i) => `<td>${i.waktu}</td><td>${i.nama}</td><td><span class="badge bg-info text-dark">${i.aktivitas}</span></td><td class="text-start">${i.deskripsi}</td><td>${i.ip_address}</td>`, 'Riwayat Aktivitas', null);
        }

        function fetchData(url, heads, rowFn, title, modalFn) {
            fetch(url).then(r => r.json()).then(res => {
                if (!res.success) return Swal.fire('Error', res.message, 'error');
                const h = heads.map(x => `<th>${x}</th>`).join('') + (modalFn ? '<th>Aksi</th>' : '');
                const b = res.data.length ? res.data.map(x => `<tr>${rowFn(x)}${modalFn?`<td class="text-center"><button class="btn btn-sm btn-light border text-warning me-1" onclick="${modalFn}(${Object.values(x)[0]})"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-light border text-danger" onclick="deleteItem('${url}',${Object.values(x)[0]})"><i class="fas fa-trash"></i></button></td>`:''}</tr>`).join('') : '<tr><td colspan="10" class="text-center py-5 text-muted">Kosong.</td></tr>';
                document.getElementById('content-area').innerHTML = `<div class="card fade-in"><div class="card-header"><span>${title}</span>${modalFn?`<button class="btn-primary-custom" onclick="${modalFn}()"><i class="fas fa-plus"></i> Tambah</button>`:''}</div><div class="card-body p-0"><table class="table"><thead><tr>${h}</tr></thead><tbody>${b}</tbody></table></div></div>`;
            });
        }

        function deleteItem(u, id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const d = new FormData();
                    d.append('action', 'delete');
                    d.append('id', id);
                    fetch(u, {
                        method: 'POST',
                        body: d
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            loadPage(currentPage);
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                        } else {
                            Swal.fire('Gagal!', 'Data gagal dihapus.', 'error');
                        }
                    });
                }
            });
        }

        function escapeHtml(s) {
    if (!s) return '';
    return s
        .replace(/\\/g, '\\\\')       // 1. Handle backslash dulu
        .replace(/\n/g, "\\n")        // 2. Handle ENTER (Penyebab utama error)
        .replace(/\r/g, "")           // 3. Hapus carriage return
        .replace(/'/g, "\\'")         // 4. Handle kutip satu (untuk JS)
        .replace(/"/g, "&quot;");     // 5. Handle kutip dua (untuk HTML)
}

        function loadKontak() {
            document.getElementById('page-title-text').innerText = 'Pesan Masuk';
            fetch('api/kontak.php').then(r => r.json()).then(result => {
                const data = result.data || [];
                const unreadCount = data.filter(item => item.is_read == 0).length;
                const html = `
                    <div class="fade-in">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">Inbox Pesan</h5>
                                    <p class="text-muted small mb-0">Daftar pertanyaan dan pesan dari pengunjung website.</p>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6 border border-primary border-opacity-25">
                                    <i class="fas fa-envelope me-2"></i> ${unreadCount} Belum Dibaca
                                </span>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern align-middle mb-0">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4">Pengirim</th>
                                                <th>Subjek</th>
                                                <th class="text-center">Tanggal</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-end pe-4">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="kontakTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('content-area').innerHTML = html;
                renderKontakTable(data);
            }).catch(err => {
                document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${err}</div>`;
            });
        }

        function renderKontakTable(data) {
            const tbody = document.getElementById('kontakTableBody');
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-inbox fa-3x mb-3 text-light-emphasis"></i><p>Belum ada pesan masuk.</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(item => {
                const isRead = item.is_read == 1;
                const rowClass = isRead ? '' : 'bg-light';
                const badgeStatus = isRead ?
                    '<span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill"><i class="fas fa-check-double me-1"></i> Dibaca</span>' :
                    '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i class="fas fa-circle me-1" style="font-size:8px;"></i> Baru</span>';
                let dateDisplay = item.created_at;

                // PENTING: Mengamankan string JSON agar tidak error jika ada tanda kutip di dalam pesan
                const itemSafe = JSON.stringify(item).replace(/'/g, "&apos;");

                return `<tr class="${rowClass}">
                    <td class="ps-4">
                        <div class="fw-bold text-dark">${item.nama}</div>
                        <div class="small text-muted">${item.email}</div>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 300px; ${!isRead ? 'font-weight:700; color:var(--primary);' : ''}">${item.subjek}</div>
                        <div class="small text-muted text-truncate" style="max-width: 300px;">${item.pesan}</div>
                    </td>
                    <td class="text-center small text-muted">${dateDisplay}</td>
                    <td class="text-center">${badgeStatus}</td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick='viewPesan(${itemSafe})' title="Lihat"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deletePesan(${item.id_kontak})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        function viewPesan(item) {
            Swal.fire({
                title: `<h5 class="fw-bold text-primary mb-0">${item.subjek}</h5>`,
                html: `
                    <div class="text-start mt-3">
                        <div class="mb-3 p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted fw-bold text-uppercase">Pengirim</small>
                                <small class="text-muted">${item.created_at}</small>
                            </div>
                            <div class="fw-bold text-dark">${item.nama}</div>
                            <div class="text-primary small">${item.email}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted fw-bold text-uppercase">Isi Pesan</small>
                        </div>
                        <div class="p-3 border rounded bg-white" style="white-space: pre-line; max-height: 300px; overflow-y: auto;">
                            ${item.pesan}
                        </div>
                    </div>
                `,
                width: '600px',
                showCloseButton: true,
                showConfirmButton: false,
                focusConfirm: false
            });

            if (item.is_read == 0) {
                const formData = new FormData();
                formData.append('action', 'mark_read');
                formData.append('id', item.id_kontak);

                fetch('api/kontak.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        loadKontak();
                    })
                    .catch(err => console.log('Gagal update status read:', err));
            }
        }

        function deletePesan(id) {
            Swal.fire({
                title: 'Hapus Pesan?',
                text: "Pesan ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#ced4da',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);

                    fetch('api/kontak.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire('Terhapus!', 'Pesan berhasil dihapus.', 'success');
                                loadKontak();
                            } else {
                                Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                        });
                }
            });
        }

        function previewFile(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('uploadPlaceholder').classList.add('d-none');
                    document.getElementById('imagePreviewContainer').classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }

        function resetUpload() {
            document.getElementById('fileInput').value = '';
            document.getElementById('uploadPlaceholder').classList.remove('d-none');
            document.getElementById('imagePreviewContainer').classList.add('d-none');
            document.getElementById('fileInput').click();
        }

        let currentPage = 'beranda';
        loadPage('beranda');
    </script>
</body>

</html>