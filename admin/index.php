<?php
require_once __DIR__ . '/config/database.php';
// Pastikan fungsi checkAuth() sudah ada di config atau file helper Anda
if (!function_exists('checkAuth')) {
    session_start();
    function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}
checkAuth(); 
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
            --shadow-sm: 0 2px 15px rgba(0,0,0,0.03);
            --shadow-md: 0 5px 20px rgba(0,0,0,0.05);
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Kategori Badges */
        .badge-news-latest { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-prestasi { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-announcement { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-kegiatan { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
        .badge-fasilitas { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-lainnya { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-default { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* === SIDEBAR MODERN === */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 5px 0 20px rgba(0,0,0,0.02);
            padding: 20px;
            overflow-y: auto;
        }
        
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #eee; border-radius: 10px; }

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

        .nav-link.active i { color: white; }
        
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
        .btn-logout i { color: #e63946; }
        .btn-logout:hover i { color: white; }

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

        .user-info h6 { margin: 0; font-size: 0.9rem; font-weight: 700; }
        .user-info span { font-size: 0.75rem; color: var(--text-muted); }

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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header span, .card-header h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            margin: 0;
        }
        
        .card-header i { margin-right: 10px; color: var(--primary); }

        .card-body { padding: 25px; }
        .card-body.p-0 { padding: 0; }

        /* Tabel Modern - General */
        .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; width: 100%; }
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            z-index: 10;
            position: relative;
        }
        
        /* Thumbnail */
        .content-thumbnail {
            width: 80px;
            height: 55px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        .form-control, .form-select {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #fcfcfc;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            background-color: white;
        }
        
        /* Search Input Modern */
        .search-input-wrapper { position: relative; }
        .search-input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-input { padding-left: 40px !important; border-radius: 50px !important; border: 1px solid #e2e8f0; background: #f8fafc; }
        .search-input:focus { background: white; }
        
        /* Form Container Style */
        .form-container-view { max-width: 900px; margin: 0 auto; }

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
        .upload-box:hover { border-color: var(--primary); background-color: #f8faff; }
        .upload-icon-circle { width: 60px; height: 60px; background-color: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .upload-icon-circle i { font-size: 1.5rem; color: var(--primary); }
        .upload-text-title { font-weight: 700; color: var(--text-main); margin-bottom: 5px; }
        .upload-text-muted { font-size: 0.8rem; color: var(--text-muted); }
        .file-input-hidden { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }

        /* Tampilan Preview */
        .preview-box { position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #eee; background: #fff; padding: 10px; }
        .preview-box img { width: 100%; height: auto; max-height: 400px; object-fit: contain; display: block; border-radius: 8px; }
        .btn-remove-preview { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.95); color: #e63946; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: all 0.2s; z-index: 10; }
        .btn-remove-preview:hover { transform: scale(1.1); color: red; }
        
        /* === ASSET SHOWCASE STYLES (LOGO & MASKOT) === */
        .asset-card { transition: transform 0.3s ease; border: 0; overflow: hidden; border-radius: 20px; }
        .asset-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .asset-img-wrapper {
            background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            background-color: white; 
            height: 280px;
            display: flex; align-items: center; justify-content: center;
            border-bottom: 1px solid #f0f0f0; position: relative;
        }
        .asset-img-wrapper img { max-width: 70%; max-height: 80%; object-fit: contain; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.15)); transition: all 0.5s ease; }
        .asset-card:hover .asset-img-wrapper img { transform: scale(1.05); }
        .asset-badge { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.9); backdrop-filter: blur(5px); padding: 5px 15px; border-radius: 30px; font-weight: 700; color: var(--primary); font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        /* Submission Detail Styles */
        .submission-detail-header { background: white; padding: 30px; border-radius: var(--card-radius); box-shadow: var(--shadow-md); margin-bottom: 25px; }
        .submission-meta-badge { background: #f1f5f9; color: var(--text-muted); padding: 8px 16px; border-radius: 50px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 8px; margin-right: 10px; font-weight: 600; }
        .submission-content-card { background: white; border-radius: var(--card-radius); box-shadow: var(--shadow-md); overflow: hidden; }
        .submission-image-wrapper { background-color: #f8f9fa; width: 100%; padding: 20px; text-align: center; border-bottom: 1px solid #eee; }
        .submission-hero-img { max-width: 100%; height: auto; max-height: 600px; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .submission-body { padding: 40px; }
        .submission-body p { line-height: 1.8; font-size: 1rem; color: #4b5563; }
        .action-bar { position: sticky; bottom: 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 15px 30px; border-radius: 50px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.5); display: flex; justify-content: space-between; align-items: center; margin-top: 30px; z-index: 900; }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; padding: 20px; }
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
        .contact-view-table td { padding: 10px; vertical-align: top; }
        .contact-view-label { font-weight: 700; color: var(--text-main); width: 100px; }
        .contact-view-value { color: #555; }

        /* Timeline Style (For Dashboard) */
        .timeline .border-start { border-color: #e9ecef !important; }
        .timeline .rounded-circle { box-shadow: 0 0 0 4px #fff; }

        /* Dashboard Minimal Stats */
        .dash-stat-item { display: flex; align-items: center; padding: 15px 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-sm); transition: transform 0.2s; }
        .dash-stat-item:hover { transform: translateY(-3px); }
        .dash-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 1.1rem; }
        .dash-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .dash-value { font-size: 1.2rem; font-weight: 800; color: var(--text-main); line-height: 1; }

    </style>
    <script>
        window.onpageshow = function(event) { if (event.persisted) window.location.reload(); };
    </script>
</head>
<body>
    
    <div class="sidebar">
        <a href="#" class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="Logo"> 
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

            switch(page) {
                case 'beranda': loadBeranda(); break;
                case 'identitas': loadIdentitas(); break;
                case 'visi_misi': loadVisiMisi(); break;
                case 'roadmap': loadRoadmap(); break;
                case 'research_focus': loadResearchFocus(); break;
                case 'makna_logo': loadMaknaLogo(); break;
                case 'anggota': loadAnggota(); break;
                case 'berita': loadBerita(); break;
                case 'galeri': loadGaleri(); break;
                case 'news_service': loadNewsService(); break;
                case 'fasilitas': loadFasilitas(); break;
                case 'booking': loadBooking(); break;
                case 'kontak': loadKontak(); break;
                case 'log': loadLog(); break;
                default: content.innerHTML = '<div class="alert alert-warning">Halaman tidak ditemukan.</div>';
            }
        }

        // ==========================================
        // === DASHBOARD (MODERN CLEAN REDESIGN) ===
        // ==========================================

        function loadBeranda() {
            document.getElementById('page-title-text').innerText = 'Dashboard Overview';
            
            // Fetch Semua Data Secara Paralel
            Promise.all([
                fetch('api/booking.php').then(r => r.json()).catch(() => ({data: []})),       // Booking
                fetch('api/news_service.php').then(r => r.json()).catch(() => ({data: []})),  // News Request
                fetch('api/berita.php').then(r => r.json()).catch(() => ({data: []})),        // Berita
                fetch('api/kontak.php').then(r => r.json()).catch(() => ({data: []})),        // Pesan
                fetch('api/galeri.php').then(r => r.json()).catch(() => ({data: []})),        // Galeri (Baru ditambahkan untuk chart)
                fetch('api/log.php?action=list').then(r => r.json()).catch(() => ({data: []})) // Log Aktivitas
            ]).then(([bookingRes, newsRes, beritaRes, kontakRes, galeriRes, logRes]) => {

                // Hitung Statistik Real-time
                const pendingBooking = (bookingRes.data || []).filter(b => b.status === 'Pending').length;
                const pendingNews = (newsRes.data || []).length;
                const totalBerita = (beritaRes.data || []).length;
                const totalGaleri = (galeriRes.data || []).length;
                const unreadPesan = (kontakRes.data || []).filter(k => k.is_read == 0).length;
                
                // Ambil 5 Log Terakhir
                const logs = (logRes.data || []).slice(0, 5);

                // Data untuk Chart (Total Item per Kategori)
                const chartDataCounts = [totalBerita, totalGaleri, (bookingRes.data || []).length, (kontakRes.data || []).length];

                // Nama Admin Saat Ini (Contoh untuk widget admin)
                const currentAdminName = "<?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?>";
                const currentAdminRole = "Administrator Utama"; // Bisa disesuaikan jika ada role di session

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
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="timeline">
                                        ${logs.length > 0 ? logs.map((log, index) => `
                                            <div class="d-flex pb-4 position-relative">
                                                ${index !== logs.length - 1 ? `<div class="position-absolute top-0 start-0 h-100 border-start border-2 border-light ms-3" style="z-index: 0; margin-top: 10px;"></div>` : ''}
                                                <div class="flex-shrink-0 position-relative z-1">
                                                    <div class="bg-white border border-2 border-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        <i class="fas fa-dot-circle text-primary small"></i>
                                                    </div>
                                                </div>
                                                <div class="ms-3 w-100">
                                                    <div class="d-flex align-items-center mb-1 justify-content-between">
                                                        <div>
                                                            <span class="fw-bold text-dark me-2">${log.nama}</span>
                                                            <span class="badge bg-light text-secondary border small" style="font-size: 0.7rem;">${log.aktivitas}</span>
                                                        </div>
                                                        <small class="text-muted" style="font-size: 0.75rem;">${log.waktu}</small>
                                                    </div>
                                                    <p class="text-muted small mb-0 bg-light p-2 rounded mt-1">${log.deskripsi}</p>
                                                </div>
                                            </div>
                                        `).join('') : '<div class="text-center py-5 text-muted"><i class="fas fa-history fa-3x mb-3 opacity-25"></i><p>Belum ada aktivitas tercatat.</p></div>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi Konten</h5>
                                </div>
                                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="min-height: 250px;">
                                    <canvas id="contentDistributionChart"></canvas>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 pt-4 ps-4 pe-4 d-flex justify-content-between">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-users-cog me-2"></i>Tim Administrator</h5>
                                    <span class="badge bg-primary bg-opacity-10 text-primary small">Online</span>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(currentAdminName)}&background=4361ee&color=fff" class="rounded-circle me-3" width="45" height="45" alt="Admin Avatar">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">${currentAdminName} (Anda)</h6>
                                            <small class="text-muted">${currentAdminRole}</small>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                document.getElementById('content-area').innerHTML = html;

                // --- RENDER CHART.JS SETELAH HTML DIMUAT ---
                const ctx = document.getElementById('contentDistributionChart').getContext('2d');
                window.dashboardChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Berita', 'Foto Galeri', 'Total Booking', 'Pesan Masuk'],
                        datasets: [{
                            data: chartDataCounts,
                            backgroundColor: [
                                '#4361ee', // Primary
                                '#3f37c9', // Secondary
                                '#f72585', // Pink/Magenta
                                '#4cc9f0'  // Light Blue
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        family: "'Nunito', sans-serif",
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.parsed + ' Item';
                                        return label;
                                    }
                                }
                            }
                        },
                        cutout: '65%', // Membuat lingkaran tengah lebih besar (donat)
                        layout: {
                            padding: 10
                        }
                    }
                });

            }).catch(err => {
                console.error(err);
                document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat dashboard. Periksa koneksi API.</div>`;
            });
        }

        // ==========================================
        // === 3. MANAJEMEN BERITA (SESUAI API) ===
        // ==========================================
        
        let allBeritaData = []; 

        function getBeritaBadge(category) {
            switch ((category || '').toLowerCase()) {
                case 'news latest': return 'badge-news-latest';
                case 'prestasi': return 'badge-prestasi';
                case 'announcement': return 'badge-announcement';
                default: return 'badge-default';
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
            if (!data.length) { tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-newspaper fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data berita.</p></div></td></tr>`; return; }
            
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
                    fetch('api/berita.php', { method: 'POST', body: formData })
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
            if(id) fetch(`api/berita.php?id=${id}`).then(r=>r.json()).then(res => renderBeritaFormHTML(res.data));
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
            document.getElementById('formBerita').onsubmit = (e) => { e.preventDefault(); submitFormPage('api/berita.php', new FormData(e.target), loadBerita); };
        }


        // ==========================================
        // === 4. MANAJEMEN GALERI (TABLE VIEW) ===
        // ==========================================

        let allGaleriData = [];

        function getGaleriBadge(category) {
            switch ((category || '').toLowerCase()) {
                case 'kategori 1': return 'badge-news-latest';
                case 'kategori 2': return 'badge-prestasi';
                case 'kategori 3': return 'badge-announcement';
                case 'kategori 4': return 'badge-kegiatan';
                default: return 'badge-default';
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
            if (!data.length) { tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-images fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data galeri.</p></div></td></tr>`; return; }
            
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
                    fetch('api/galeri.php', { method: 'POST', body: formData })
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

        function openGaleriForm(id=null){
            if(id) fetch(`api/galeri.php?id=${id}`).then(r=>r.json()).then(res=>renderGaleriFormHTML(res.data));
            else renderGaleriFormHTML(null);
        }

        function renderGaleriFormHTML(data=null){
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
            document.getElementById('formGaleri').onsubmit=(e)=>{e.preventDefault(); submitFormPage('api/galeri.php', new FormData(e.target), loadGaleri);}
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
                    if(data.length > 0) renderNewsServiceTable(data);
                }).catch(err => { document.getElementById('content-area').innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${err}</div>`; });
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
                    const formData = new FormData(); formData.append('action', action); formData.append('id', id);
                    fetch('api/news_service.php', { method: 'POST', body: formData }).then(r => r.json()).then(res => {
                        if (res.success) { 
                            Swal.fire('Berhasil', res.message, 'success'); 
                            loadNewsService(); 
                        } else { 
                            Swal.fire('Gagal', res.message, 'error'); 
                        }
                    }).catch(err => { console.error(err); Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error'); });
                }
            });
        }

        // ==========================================
        // === 6. HELPER & UTILITIES ===
        // ==========================================
        
        function fixImagePath(path) {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('../')) return path;
            if (path.startsWith('admin/')) return '../' + path;
            if (path.startsWith('assets/')) return '../' + path;
            return path;
        }

        // ==========================================
        // === [UPDATED] LOGO & MASKOT UI ===
        // ==========================================

        function loadMaknaLogo(){ 
            document.getElementById('page-title-text').innerText = 'Identitas Visual';
            fetch('api/settings.php').then(r=>r.json()).then(res=>{ 
                const s = res.data||{}; 
                const v = (k) => s[k]?.value||''; 
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

        function openSetForm(k, t, v, currentImg){ 
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
            document.getElementById('fSet').onsubmit = (e) => { e.preventDefault(); submitFormPage('api/settings.php', new FormData(e.target), loadMaknaLogo); }
        }

        // ==========================================
        // === [UPDATED] VISI & MISI UI (UNIFIED STYLE) ===
        // ==========================================

        function loadVisiMisi(){ 
            document.getElementById('page-title-text').innerText = 'Arah & Tujuan';
            fetch('api/settings.php').then(r=>r.json()).then(res=>{ 
                const s=res.data||{}; 
                const vVisi = s.visi?.value || 'Belum ada data visi.';
                const vMisi = s.misi?.value || 'Belum ada data misi.';

                const html=`
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
                document.getElementById('content-area').innerHTML=html; 
            }); 
        }

        function openVisiMisiForm(){
            // Fetch data first to prevent quotes issues in onclick
            document.getElementById('content-area').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
            
            fetch('api/settings.php').then(r=>r.json()).then(res=>{
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
                document.getElementById('fVm').onsubmit=(e)=>{e.preventDefault(); submitFormPage('api/settings.php',new FormData(e.target),loadVisiMisi);}
            });
        }

        // ==========================================
        // === [UPDATED] IDENTITAS LAB UI ===
        // ==========================================

        function loadIdentitas(){ 
            document.getElementById('page-title-text').innerText = 'Profil Laboratorium';
            fetch('api/settings.php').then(r=>r.json()).then(res=>{ 
                const s=res.data||{}; 
                const v=(k)=>s[k]?s[k].value:''; 
                
                const html=`
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
                document.getElementById('content-area').innerHTML=html; 
                document.getElementById('fId').onsubmit=(e)=>{e.preventDefault();submitFormPage('api/settings.php', new FormData(e.target), loadIdentitas);} 
            }); 
        }

        // ==========================================
        // === UTILS (UPDATED WITH SWEETALERT2) ===
        // ==========================================

        function submitFormPage(url, data, callback) {
            fetch(url, {method:'POST', body:data})
            .then(r=>r.json())
            .then(res => {
                if(res.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    if(callback) callback(); 
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
        
        function loadLog(){ document.getElementById('page-title-text').innerText = 'Log Aktivitas'; fetchData('api/log.php?action=list', ['Waktu','User','Aktivitas','Deskripsi','IP'], (i)=>`<td>${i.waktu}</td><td>${i.nama}</td><td><span class="badge bg-info text-dark">${i.aktivitas}</span></td><td class="text-start">${i.deskripsi}</td><td>${i.ip_address}</td>`, 'Riwayat Aktivitas', null); }

        function fetchData(url, heads, rowFn, title, modalFn){ fetch(url).then(r=>r.json()).then(res=>{ if(!res.success)return Swal.fire('Error', res.message, 'error'); const h=heads.map(x=>`<th>${x}</th>`).join('')+(modalFn?'<th>Aksi</th>':''); const b=res.data.length?res.data.map(x=>`<tr>${rowFn(x)}${modalFn?`<td class="text-center"><button class="btn btn-sm btn-light border text-warning me-1" onclick="${modalFn}(${Object.values(x)[0]})"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-light border text-danger" onclick="deleteItem('${url}',${Object.values(x)[0]})"><i class="fas fa-trash"></i></button></td>`:''}</tr>`).join(''):'<tr><td colspan="10" class="text-center py-5 text-muted">Kosong.</td></tr>'; document.getElementById('content-area').innerHTML=`<div class="card fade-in"><div class="card-header"><span>${title}</span>${modalFn?`<button class="btn-primary-custom" onclick="${modalFn}()"><i class="fas fa-plus"></i> Tambah</button>`:''}</div><div class="card-body p-0"><table class="table"><thead><tr>${h}</tr></thead><tbody>${b}</tbody></table></div></div>`; }); }
        
        function deleteItem(u,id){ 
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
                    const d=new FormData(); d.append('action','delete'); d.append('id',id); 
                    fetch(u,{method:'POST',body:d}).then(r=>r.json()).then(res=>{ 
                        if(res.success){ 
                            loadPage(currentPage); 
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                        }else{ 
                            Swal.fire('Gagal!', 'Data gagal dihapus.', 'error'); 
                        } 
                    });
                }
            });
        }
        
        function escapeHtml(s){ return s?s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'):''; }
        
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
                const badgeStatus = isRead 
                    ? '<span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill"><i class="fas fa-check-double me-1"></i> Dibaca</span>' 
                    : '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i class="fas fa-circle me-1" style="font-size:8px;"></i> Baru</span>';
                let dateDisplay = item.created_at;
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
                            <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick='viewPesan(${JSON.stringify(item)})' title="Lihat"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deletePesan(${item.id_kontak})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
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