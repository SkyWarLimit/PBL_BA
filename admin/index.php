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
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
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
        
        .card-header span {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
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
            background-color: white; /* Checkerboard for transparency */
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
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.15));
            transition: all 0.5s ease;
        }
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
        // === 3. MANAJEMEN BERITA (MODERN) ===
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
                allBeritaData = result.data || []; 
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
                                                <th style="width: 35%;">Judul Berita</th>
                                                <th class="text-center">Kategori</th>
                                                <th class="text-start ps-4">Uploader & Role</th>
                                                <th class="text-center">Tanggal</th>
                                                <th class="text-end pe-4">Aksi</th>
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
            if (!data.length) { tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-newspaper fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data berita.</p></div></td></tr>`; return; }
            
            tbody.innerHTML = data.map(i => {
                let displayImage = fixImagePath(i.file_path);
                const initial = (i.nama_pengupload || 'A').charAt(0).toUpperCase();
                const userRole = i.role || 'Administrator'; 

                return `<tr>
                <td class="ps-4">
                    <img src="${displayImage}" class="content-thumbnail" onerror="this.src='https://via.placeholder.com/80x55?text=No+Img'">
                </td>
                <td>
                    <div class="content-title text-truncate" style="max-width: 300px;">${i.judul}</div>
                </td>
                <td class="text-center">
                    <span class="badge-modern ${getBeritaBadge(i.kategori)}">${i.kategori}</span>
                </td>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 35px; height: 35px;">
                            ${initial}
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">${i.nama_pengupload || 'Admin'}</div>
                            <span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill" style="font-size: 0.65rem; padding: 2px 8px;">
                                ${userRole}
                            </span>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                     <span class="small text-muted"><i class="far fa-calendar-alt me-1"></i> ${i.tanggal_upload}</span>
                </td>
                <td class="text-end pe-4">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick="openBeritaForm(${i.id_artikel})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deleteItem('api/berita.php', ${i.id_artikel})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>`;
            }).join('');
        }

        function filterBerita() { 
            const cat = document.getElementById('filterKategori').value; 
            const search = document.getElementById('searchBerita').value.toLowerCase();
            const filtered = allBeritaData.filter(i => {
                return (cat === 'all' || i.kategori === cat) && (i.judul.toLowerCase().includes(search) || (i.nama_pengupload && i.nama_pengupload.toLowerCase().includes(search)));
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
                                            <input type="date" name="tanggal" class="form-control" value="${isEdit?data.tanggal_upload:new Date().toISOString().split('T')[0]}" required>
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
            document.getElementById('formBerita').onsubmit = (e) => { e.preventDefault(); submitFormPage('api/berita.php', new FormData(e.target), loadBerita); };
        }


        // ==========================================
        // === 4. MANAJEMEN GALERI (MODERN) ===
        // ==========================================

        let allGaleriData = [];

        function getGaleriBadge(category) {
            switch ((category || '').toLowerCase()) {
                case 'kegiatan': return 'badge-kegiatan';
                case 'fasilitas': return 'badge-fasilitas';
                default: return 'badge-lainnya';
            }
        }

        function loadGaleri() {
            document.getElementById('page-title-text').innerText = 'Manajemen Galeri';
            fetch('api/galeri.php').then(r => r.json()).then(result => {
                allGaleriData = result.data || []; 
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
                                    <option value="Kegiatan">Kegiatan</option>
                                    <option value="Fasilitas">Fasilitas</option>
                                    <option value="Lainnya">Lainnya</option>
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
            if (!data.length) { tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted"><div class="py-4"><i class="fas fa-images fa-3x mb-3 text-light-emphasis"></i><p>Belum ada data galeri.</p></div></td></tr>`; return; }
            
            tbody.innerHTML = data.map(i => {
                let displayImage = fixImagePath(i.file_path);
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
                <td class="text-end pe-4">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light text-primary bg-white shadow-sm border me-1 rounded-2" onclick="openGaleriForm(${i.id_galeri})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-light text-danger bg-white shadow-sm border rounded-2" onclick="deleteItem('api/galeri.php', ${i.id_galeri})" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>`;
            }).join('');
        }

        function filterGaleri() {
            const cat = document.getElementById('filterKategoriGaleri').value;
            const search = document.getElementById('searchGaleri').value.toLowerCase();
            const filtered = allGaleriData.filter(i => {
                return (cat === 'all' || i.kategori === cat) && (i.judul.toLowerCase().includes(search));
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
                                            <option value="Kegiatan" ${isEdit && data.kategori=='Kegiatan'?'selected':''}>Kegiatan</option>
                                            <option value="Fasilitas" ${isEdit && data.kategori=='Fasilitas'?'selected':''}>Fasilitas</option>
                                            <option value="Lainnya" ${isEdit && data.kategori=='Lainnya'?'selected':''}>Lainnya</option>
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
            if (!confirm(`Apakah Anda yakin ingin ${action} berita ini?`)) return;
            const formData = new FormData(); formData.append('action', action); formData.append('id', id);
            fetch('api/news_service.php', { method: 'POST', body: formData }).then(r => r.json()).then(res => {
                if (res.success) { alert(res.message); loadNewsService(); } else { alert('Gagal: ' + res.message); }
            }).catch(err => { console.error(err); alert('Terjadi kesalahan koneksi'); });
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
        // === HELPER LAINNYA ===
        // ==========================================

        function loadVisiMisi(){ 
            document.getElementById('page-title-text').innerText = 'Visi & Misi';
            fetch('api/settings.php').then(r=>r.json()).then(res=>{ 
                const s=res.data||{}; 
                const html=`
                <div class="row fade-in">
                    <div class="col-md-12 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between">
                                <span>Preview Visi & Misi</span>
                                <button class="btn-primary-custom btn-sm" onclick="openVisiMisiForm('${escapeHtml(s.visi?.value)}','${escapeHtml(s.misi?.value)}')"><i class="fas fa-edit"></i> Edit Konten</button>
                            </div>
                            <div class="card-body">
                                <h5 class="text-primary fw-bold">Visi</h5>
                                <p class="mb-4" style="white-space:pre-line;">${s.visi?.value||'-'}</p>
                                <h5 class="text-primary fw-bold">Misi</h5>
                                <p style="white-space:pre-line;">${s.misi?.value||'-'}</p>
                            </div>
                        </div>
                    </div>
                </div>`; 
                document.getElementById('content-area').innerHTML=html; 
            }); 
        }

        function openVisiMisiForm(v,m){
            const html = `
            <div class="form-container-view fade-in">
                <div class="card">
                    <div class="card-header"><span>Edit Visi Misi</span><button class="btn btn-light btn-sm border" onclick="loadVisiMisi()"><i class="fas fa-arrow-left"></i> Kembali</button></div>
                    <div class="card-body">
                        <form id="fVm">
                            <div class="mb-3"><label class="fw-bold">Visi</label><textarea name="visi" class="form-control" rows="5">${v}</textarea></div>
                            <div class="mb-3"><label class="fw-bold">Misi</label><textarea name="misi" class="form-control" rows="10">${m}</textarea></div>
                            <button class="btn-primary-custom w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>`;
            document.getElementById('content-area').innerHTML = html;
            document.getElementById('fVm').onsubmit=(e)=>{e.preventDefault(); submitFormPage('api/settings.php',new FormData(e.target),loadVisiMisi);}
        }

        function submitFormPage(url, data, callback) {
            fetch(url, {method:'POST', body:data})
            .then(r=>r.json())
            .then(res => {
                if(res.success){
                    alert('Data berhasil disimpan!'); 
                    if(callback) callback(); 
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => alert('Terjadi kesalahan koneksi'));
        }

        function loadIdentitas(){ document.getElementById('page-title-text').innerText = 'Identitas Lab'; fetch('api/settings.php').then(r=>r.json()).then(res=>{ const s=res.data||{}; const v=(k)=>s[k]?s[k].value:''; const html=`<div class="card fade-in"><div class="card-header"><span>Form Identitas</span></div><div class="card-body"><form id="fId"><div class="row"><div class="col-6 mb-3"><label class="fw-bold">Nama Lab</label><input type="text" name="nama_lab" class="form-control" value="${v('nama_lab')}"></div><div class="col-6 mb-3"><label class="fw-bold">Email</label><input type="email" name="email" class="form-control" value="${v('email')}"></div><div class="col-6 mb-3"><label class="fw-bold">No. Telepon</label><input type="text" name="no_telp" class="form-control" value="${v('no_telp')}"></div><div class="col-12 mb-3"><label class="fw-bold">Alamat Lengkap</label><textarea name="alamat" class="form-control" rows="3">${v('alamat')}</textarea></div></div><div class="text-end"><button class="btn-primary-custom">Simpan Perubahan</button></div></form></div></div>`; document.getElementById('content-area').innerHTML=html; document.getElementById('fId').onsubmit=(e)=>{e.preventDefault();submitFormPage('api/settings.php', new FormData(e.target), null, loadIdentitas);} }); }
        
        function loadLog(){ document.getElementById('page-title-text').innerText = 'Log Aktivitas'; fetchData('api/log.php?action=list', ['Waktu','User','Aktivitas','Deskripsi','IP'], (i)=>`<td>${i.waktu}</td><td>${i.nama}</td><td><span class="badge bg-info text-dark">${i.aktivitas}</span></td><td class="text-start">${i.deskripsi}</td><td>${i.ip_address}</td>`, 'Riwayat Aktivitas', null); }

        function fetchData(url, heads, rowFn, title, modalFn){ fetch(url).then(r=>r.json()).then(res=>{ if(!res.success)return alert(res.message); const h=heads.map(x=>`<th>${x}</th>`).join('')+(modalFn?'<th>Aksi</th>':''); const b=res.data.length?res.data.map(x=>`<tr>${rowFn(x)}${modalFn?`<td class="text-center"><button class="btn btn-sm btn-light border text-warning me-1" onclick="${modalFn}(${Object.values(x)[0]})"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-light border text-danger" onclick="deleteItem('${url}',${Object.values(x)[0]})"><i class="fas fa-trash"></i></button></td>`:''}</tr>`).join(''):'<tr><td colspan="10" class="text-center py-5 text-muted">Kosong.</td></tr>'; document.getElementById('content-area').innerHTML=`<div class="card fade-in"><div class="card-header"><span>${title}</span>${modalFn?`<button class="btn-primary-custom" onclick="${modalFn}()"><i class="fas fa-plus"></i> Tambah</button>`:''}</div><div class="card-body p-0"><table class="table"><thead><tr>${h}</tr></thead><tbody>${b}</tbody></table></div></div>`; }); }
        
        function deleteItem(u,id){ if(confirm('Hapus data ini secara permanen?')){ const d=new FormData(); d.append('action','delete'); d.append('id',id); fetch(u,{method:'POST',body:d}).then(r=>r.json()).then(res=>{ if(res.success){ loadPage(currentPage); }else{ alert('Gagal hapus'); } }); } }
        
        function escapeHtml(s){ return s?s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'):''; }
        
        function loadBeranda() { document.getElementById('page-title-text').innerText = 'Dashboard'; document.getElementById('content-area').innerHTML = `<div class="row g-4 fade-in"><div class="col-md-3"><div class="card p-4 text-center"><h2 class="fw-bold text-primary">12</h2><small class="text-muted">Booking Pending</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h2 class="fw-bold text-success">5</h2><small class="text-muted">News Request</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h2 class="fw-bold text-warning">24</h2><small class="text-muted">Total Berita</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h2 class="fw-bold text-danger">8</h2><small class="text-muted">Pesan Masuk</small></div></div></div>`; }
        
        // === UNIVERSAL IMAGE PREVIEW ===
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
            // Jika Anda ingin mengembalikan gambar asli saat reset (khusus edit form), logika perlu disesuaikan,
            // tapi biasanya reset berarti 'batal upload file baru', jadi kembali ke state awal form.
            // Di sini kita trigger click input file lagi agar user bisa pilih file lain langsung.
            document.getElementById('fileInput').click();
        }

        let currentPage = 'beranda';
        loadPage('beranda');
    </script>
</body>
</html>