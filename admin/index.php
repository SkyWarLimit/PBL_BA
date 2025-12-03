<?php
// WAJIB: PHP dimulai di baris paling pertama
require_once __DIR__ . '/config/database.php';
checkAuth(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Laboratorium Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* === STYLE: White & Blue Theme === */
        :root { --primary-blue: #2B95FD; --primary-light: #e6f2ff; --text-dark: #333; --bg-gray: #f8f9fe; }
        body { font-size: 0.85rem; background-color: var(--bg-gray); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .sidebar { min-height: 100vh; background: #ffffff; position: fixed; top: 0; left: 0; width: 240px; z-index: 100; box-shadow: 2px 0 10px rgba(0,0,0,0.03); border-right: 1px solid #eee; }
        .sidebar-brand { padding: 1.2rem; color: var(--primary-blue); font-weight: 800; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; }
        .sidebar-brand img { width: 28px; margin-right: 10px; }
        .sidebar-menu { height: calc(100vh - 70px); overflow-y: auto; scrollbar-width: none; }
        .sidebar-menu::-webkit-scrollbar { display: none; }
        .sidebar-header { color: #999; font-size: 0.7rem; padding: 15px 20px 5px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        .sidebar .nav-link { color: #666; padding: 0.6rem 1.2rem; margin: 2px 10px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; transition: all 0.2s; }
        .sidebar .nav-link i { width: 25px; text-align: center; margin-right: 5px; font-size: 0.9rem; }
        .sidebar .nav-link:hover { color: var(--primary-blue); background: #fff; transform: translateX(3px); }
        .sidebar .nav-link.active { color: var(--primary-blue); background: var(--primary-light); font-weight: 600; }
        .nav-link.text-danger { margin-top: 20px; border: 1px solid #fee; }
        .nav-link.text-danger:hover { background: #fff5f5; color: #dc3545; }

        .main-content { margin-left: 240px; padding: 30px; }
        
        /* CARD & HEADER FULL WIDTH FIX */
        .card { border: none; border-radius: 12px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 1.5rem; }
        .card-header { 
            background: #fff; border-bottom: 1px solid #f0f0f0; padding: 1.2rem 1.5rem; 
            border-radius: 12px 12px 0 0 !important; font-weight: 600; color: var(--text-dark);
            display: flex; justify-content: space-between; align-items: center; width: 100%;
        }
        .card-body { padding: 1.5rem; }
        .card-body.p-0 { padding: 0; }

        .img-preview { max-width: 60px; max-height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid #eee; }
        
        /* TABLE STYLES */
        .table { width: 100%; margin-bottom: 0; border-collapse: collapse; table-layout: fixed; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #eee; color: #555; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; vertical-align: middle; padding: 15px 10px; text-align: center; white-space: nowrap; }
        .table tbody td { vertical-align: middle !important; font-size: 0.85rem; padding: 12px 10px; text-align: center; word-wrap: break-word;}
        .table tbody tr { border-bottom: 1px solid #f9f9f9; }

        /* Column Alignment & Width */
        .table th:first-child, .table td:first-child { width: 80px; } /* Foto/No */
        .table th:nth-child(2), .table td:nth-child(2) { width: 25%; text-align: left; padding-left: 15px; } /* Judul/Nama */
        .table th:last-child, .table td:last-child { width: 120px; } /* Aksi */
        .text-start { text-align: left !important; }
        
        .btn-primary-custom { background-color: var(--primary-blue); border-color: var(--primary-blue); color: white; padding: 6px 16px; }
        .btn-primary-custom:hover { background-color: #1a84e6; border-color: #1a84e6; color: white; }
    </style>
    <script>
        window.onpageshow = function(event) { if (event.persisted) window.location.reload(); };
    </script>
</head>
<body>
    
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="Logo"> <span>Lab Admin</span>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column mt-2">
                <li class="nav-item"><a class="nav-link active" href="#" data-page="beranda"><i class="fas fa-th-large"></i> Dashboard</a></li>
                
                <li class="sidebar-header">Manajemen Profil</li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="identitas"><i class="fas fa-id-card"></i> Identitas Lab</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="visi_misi"><i class="fas fa-bullseye"></i> Visi & Misi</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="roadmap"><i class="fas fa-route"></i> Roadmap</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="research_focus"><i class="fas fa-microscope"></i> Research Focus</a></li>
                
                <li class="nav-item"><a class="nav-link" href="#" data-page="makna_logo"><i class="fas fa-shapes"></i> Logo & Maskot</a></li>
                
                <li class="nav-item"><a class="nav-link" href="#" data-page="anggota"><i class="fas fa-user-tie"></i> Dosen/Anggota</a></li>

                <li class="sidebar-header">Konten Website</li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="berita"><i class="fas fa-newspaper"></i> Berita</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="galeri"><i class="fas fa-images"></i> Galeri Foto</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="news_service"><i class="fas fa-inbox"></i> News Input Service</a></li>

                <li class="sidebar-header">Fasilitas & Booking</li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="fasilitas"><i class="fas fa-desktop"></i> Fasilitas Lab</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="booking"><i class="fas fa-calendar-check"></i> Data Booking</a></li>

                <li class="sidebar-header">Lainnya</li>
                <li class="nav-item"><a class="nav-link" href="#" data-page="kontak"><i class="fas fa-envelope"></i> Pesan Masuk</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 id="page-title" style="color:var(--primary-blue); font-weight:700;">Dashboard</h4>
                <small class="text-muted">Selamat datang, <?php echo $_SESSION['nama'] ?? 'Admin'; ?></small>
            </div>
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama'] ?? 'A'); ?>&background=2B95FD&color=fff" class="rounded-circle" width="35">
            </div>
        </div>
        <div id="content-area"></div>
    </div>

    <div id="modal-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- NAVIGATION ---
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === 'logout.php') return;
                e.preventDefault();
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const page = this.dataset.page;
                document.getElementById('page-title').innerText = this.innerText.trim();
                loadPage(page);
            });
        });

        function loadPage(page) {
            const content = document.getElementById('content-area');
            content.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>'; 
            switch(page) {
                case 'beranda': loadBeranda(); break;
                
                case 'identitas': loadIdentitas(); break;
                case 'visi_misi': loadVisiMisi(); break;
                case 'roadmap': loadRoadmap(); break;
                case 'research_focus': loadResearchFocus(); break;
                
                // CASE LOGO & MASKOT
                case 'makna_logo': loadMaknaLogo(); break;
                
                case 'anggota': loadAnggota(); break;

                case 'berita': loadBerita(); break;
                case 'galeri': loadGaleri(); break;
                case 'news_service': loadNewsService(); break;

                case 'fasilitas': loadFasilitas(); break;
                case 'booking': loadBooking(); break;
                case 'kontak': loadKontak(); break;
                
                case 'pengaturan': content.innerHTML = '<div class="alert alert-info">Halaman Pengaturan Website</div>'; break;
                default: content.innerHTML = '<div class="alert alert-warning">Halaman tidak ditemukan.</div>';
            }
        }

        // ============================================================
        // 1. IDENTITAS LAB
        // ============================================================
        function loadIdentitas() {
            fetch('api/settings.php').then(r => r.json()).then(res => {
                if(!res.success) { alert(res.message); return; }
                const s = res.data || {};
                const val = (key) => s[key] ? s[key].value : '';
                const contentHtml = `
                    <div class="card"><div class="card-header"><span class="fw-bold"><i class="fas fa-id-card me-2"></i>Identitas & Kontak</span></div><div class="card-body">
                        <form id="identitasForm"><div class="row"><div class="col-md-6 mb-3"><label class="fw-bold text-muted">Nama Lab</label><input type="text" name="nama_lab" class="form-control" value="${val('nama_lab')}"></div><div class="col-md-6 mb-3"><label class="fw-bold text-muted">Email</label><input type="email" name="email" class="form-control" value="${val('email')}"></div><div class="col-md-6 mb-3"><label class="fw-bold text-muted">No. Telp</label><input type="text" name="no_telp" class="form-control" value="${val('no_telp')}"></div><div class="col-md-6 mb-3"><label class="fw-bold text-muted">Alamat</label><textarea name="alamat" class="form-control" rows="1">${val('alamat')}</textarea></div></div><hr><div class="text-end"><button type="submit" class="btn btn-primary-custom px-4">Simpan</button></div></form>
                    </div></div>`;
                document.getElementById('content-area').innerHTML = contentHtml;
                document.getElementById('identitasForm').onsubmit = (e) => { e.preventDefault(); submitForm('api/settings.php', new FormData(e.target), null, loadIdentitas); };
            });
        }

        // ============================================================
        // 2. VISI MISI 
        // ============================================================
        function loadVisiMisi() {
            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const contentHtml = `
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div><h5 class="fw-bold text-dark m-0">Visi & Misi Laboratorium</h5><small class="text-muted">Kelola tujuan dan arah laboratorium</small></div>
                        <button class="btn btn-primary-custom px-4 py-2 shadow-sm" onclick="showVisiMisiModal('${escapeHtml(s.visi?.value)}', '${escapeHtml(s.misi?.value)}')"><i class="fas fa-edit me-2"></i> Ubah Visi & Misi</button>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-4"><div class="card h-100 border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><span class="fw-bold text-primary"><i class="fas fa-eye me-2"></i>Visi</span></div><div class="card-body" style="background-color: #fff; min-height: 100px;"><p class="mb-0 text-dark" style="white-space: pre-line; font-size: 1.1rem; line-height: 1.6;">${s.visi?.value || '<em class="text-muted">Belum diisi</em>'}</p></div></div></div>
                        <div class="col-md-12"><div class="card h-100 border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><span class="fw-bold text-primary"><i class="fas fa-list-ul me-2"></i>Misi</span></div><div class="card-body" style="background-color: #fff; min-height: 150px;"><p class="mb-0 text-dark" style="white-space: pre-line; font-size: 1rem; line-height: 1.6;">${s.misi?.value || '<em class="text-muted">Belum diisi</em>'}</p></div></div></div>
                    </div>`;
                document.getElementById('content-area').innerHTML = contentHtml;
            });
        }
        function showVisiMisiModal(v, m) {
            const decode = (h) => { var t = document.createElement("textarea"); t.innerHTML = h; return t.value; };
            const formHtml = `<div class="mb-3"><label class="fw-bold">Visi</label><textarea name="visi" class="form-control" rows="4">${decode(v)}</textarea></div><div class="mb-3"><label class="fw-bold">Misi</label><textarea name="misi" class="form-control" rows="8">${decode(m)}</textarea></div>`;
            showModal('Edit Visi & Misi', formHtml, 'api/settings.php', loadVisiMisi);
            setTimeout(() => { document.querySelector('#dynamicModal .modal-dialog').classList.add('modal-lg'); }, 100);
        }
        function escapeHtml(text) { if (!text) return ""; return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/\r\n/g, "\\n").replace(/\n/g, "\\n").replace(/\r/g, "\\n"); }

        // ============================================================
        // 3. LOGO & MASKOT (MODIFIKASI: NAME INPUT DISESUAIKAN)
        // ============================================================
        function loadMaknaLogo() {
            fetch('api/settings.php').then(r => r.json()).then(res => {
                const s = res.data || {};
                const getVal = (k) => s[k]?.value || '';
                const getImg = (k) => s[k]?.file_path || 'https://via.placeholder.com/150?text=No+Img';
                const contentHtml = `
                    <div class="row">
                        <div class="col-md-6 mb-4"><div class="card h-100 shadow-sm border-0"><div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center"><span class="fw-bold text-primary"><i class="fas fa-shapes me-2"></i>Logo Laboratorium</span><button class="btn btn-sm btn-light text-warning border" onclick="showSettingModal('logo', 'Logo', '${escapeHtml(getVal('logo'))}')"><i class="fas fa-edit"></i> Edit</button></div><div class="card-body text-center"><div class="p-3 border rounded bg-light mb-3 d-inline-block"><img src="${getImg('logo')}" style="max-height:150px;max-width:100%;object-fit:contain;"></div><div class="text-start mt-2"><label class="small fw-bold text-muted">Filosofi:</label><p class="text-dark small" style="white-space:pre-line;">${getVal('logo')}</p></div></div></div></div>
                        <div class="col-md-6 mb-4"><div class="card h-100 shadow-sm border-0"><div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center"><span class="fw-bold text-primary"><i class="fas fa-robot me-2"></i>Maskot Laboratorium</span><button class="btn btn-sm btn-light text-warning border" onclick="showSettingModal('maskot', 'Maskot', '${escapeHtml(getVal('maskot'))}')"><i class="fas fa-edit"></i> Edit</button></div><div class="card-body text-center"><div class="p-3 border rounded bg-light mb-3 d-inline-block"><img src="${getImg('maskot')}" style="max-height:150px;max-width:100%;object-fit:contain;"></div><div class="text-start mt-2"><label class="small fw-bold text-muted">Filosofi:</label><p class="text-dark small" style="white-space:pre-line;">${getVal('maskot')}</p></div></div></div></div>
                    </div>`;
                document.getElementById('content-area').innerHTML = contentHtml;
            });
        }
        
        // MODIFIKASI PENTING: name input disamakan dengan KEY agar API settings.php bisa menangkap
        function showSettingModal(key, title, val) {
            const decode = (h) => { var t = document.createElement("textarea"); t.innerHTML = h; return t.value; };
            
            const formHtml = `
                <div class="mb-3">
                    <label class="fw-bold">Filosofi ${title}</label>
                    <textarea name="${key}" class="form-control" rows="5" required>${decode(val)}</textarea>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Ganti Gambar</label>
                    <input type="file" name="${key}" class="form-control" accept="image/*">
                </div>
            `;
            showModal('Edit ' + title, formHtml, 'api/settings.php', loadMaknaLogo);
        }

        // --- 4. BERITA ---
        let allBeritaData = []; 
        function loadBerita() {
            fetch('api/berita.php').then(r => r.json()).then(result => {
                if(!result.success) { alert(result.message); return; }
                allBeritaData = result.data; 
                const contentHtml = `
                    <div class="card">
                        <div class="card-header"><span class="fw-bold"><i class="fas fa-newspaper me-2"></i>Manajemen Berita</span><div class="d-flex gap-2"><select id="filterKategori" class="form-select form-select-sm" style="width: 160px;" onchange="filterBerita()"><option value="all">Semua Kategori</option><option value="News Latest">News Latest</option><option value="Prestasi">Prestasi</option><option value="Announcement">Announcement</option></select><button class="btn btn-sm btn-primary-custom" onclick="prepareBeritaModal()"><i class="fas fa-plus me-1"></i> Tambah</button></div></div>
                        <div class="card-body p-0"><table class="table table-hover mb-0"><thead><tr><th>Foto</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Uploader</th><th>Aksi</th></tr></thead><tbody id="beritaTableBody"></tbody></table></div>
                    </div>`;
                document.getElementById('content-area').innerHTML = contentHtml;
                renderBeritaTable(allBeritaData);
            });
        }
        function renderBeritaTable(data) {
            const tbody = document.getElementById('beritaTableBody');
            if (data.length === 0) { tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada berita.</td></tr>`; return; }
            tbody.innerHTML = data.map(item => {
                const imgPath = item.file_path ? item.file_path : 'https://via.placeholder.com/60?text=No+Img';
                return `<tr><td><img src="${imgPath}" class="img-preview shadow-sm" style="width:50px;height:40px;object-fit:cover;border-radius:4px;"></td><td class="fw-bold text-dark text-start">${item.judul}</td><td><span class="badge ${item.kategori==='Announcement'?'bg-danger':(item.kategori==='Prestasi'?'bg-success':'bg-primary')} px-3 py-1 rounded-pill">${item.kategori}</span></td><td class="small text-muted">${item.tanggal_upload}</td><td class="small text-start"><div class="fw-bold text-dark">${item.nama_pengupload||'-'}</div><div class="text-muted" style="font-size:0.7rem">${item.role_pengupload||'-'}</div></td><td class="text-center"><button class="btn btn-sm btn-light text-warning border me-1" onclick="prepareBeritaModal(${item.id_artikel})"><i class="fas fa-pencil-alt"></i></button><button class="btn btn-sm btn-light text-danger border" onclick="deleteItem('api/berita.php', ${item.id_artikel})"><i class="fas fa-trash-alt"></i></button></td></tr>`;
            }).join('');
        }
        function filterBerita() { const cat = document.getElementById('filterKategori').value; renderBeritaTable(cat === 'all' ? allBeritaData : allBeritaData.filter(i => i.kategori === cat)); }
        function prepareBeritaModal(id = null) { if(id) fetch(`api/berita.php?id=${id}`).then(r=>r.json()).then(res=>{if(res.success)showBeritaModal(res.data);}); else showBeritaModal(null); }
        function showBeritaModal(data = null) {
            const isEdit = data !== null;
            const formHtml = `<input type="hidden" name="id_artikel" value="${isEdit?data.id_artikel:''}"><div class="row"><div class="col-md-8"><div class="mb-3"><label class="form-label fw-bold">Judul</label><input type="text" name="judul" class="form-control" value="${isEdit?data.judul:''}" required></div><div class="mb-3"><label class="form-label fw-bold">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="6" required>${isEdit?(data.konten||''):''}</textarea></div></div><div class="col-md-4 border-start"><div class="mb-3"><label class="form-label fw-bold">Kategori</label><select name="kategori" class="form-select" required><option value="News Latest" ${isEdit&&data.kategori==='News Latest'?'selected':''}>News Latest</option><option value="Prestasi" ${isEdit&&data.kategori==='Prestasi'?'selected':''}>Prestasi</option><option value="Announcement" ${isEdit&&data.kategori==='Announcement'?'selected':''}>Announcement</option></select></div><div class="mb-3"><label class="form-label fw-bold">Tanggal</label><input type="date" name="tanggal" class="form-control" value="${isEdit?data.tanggal_upload:new Date().toISOString().split('T')[0]}" required></div><div class="mb-3"><label class="form-label fw-bold">Foto</label><input type="file" name="foto" class="form-control" ${isEdit?'':'required'}></div><div class="p-3 bg-light rounded mt-4 border"><label class="small text-muted d-block">Uploader</label><input type="text" name="nama_pengupload" class="form-control form-control-sm mb-2" value="${isEdit?(data.nama_pengupload||''):"<?php echo $_SESSION['nama'] ?? 'Admin'; ?>"}"><select name="role_pengupload" class="form-select form-select-sm"><option value="admin">Admin</option><option value="dosen">Dosen</option><option value="mahasiswa">Mahasiswa</option></select></div></div></div>`;
            showModal(isEdit?'Edit Berita':'Tambah Berita', formHtml, 'api/berita.php', loadBerita);
            setTimeout(() => { document.querySelector('#dynamicModal .modal-dialog').classList.add('modal-lg'); }, 100);
        }

        // --- 5. GALERI ---
        function loadGaleri() {
            fetch('api/galeri.php').then(r => r.json()).then(result => {
                if(!result.success) { alert(result.message); return; }
                const contentHtml = `
                    <div class="card"><div class="card-header"><span class="fw-bold"><i class="fas fa-images me-2"></i>Manajemen Galeri Foto</span><button class="btn btn-sm btn-primary-custom" onclick="prepareGaleriModal()"><i class="fas fa-plus me-1"></i> Tambah</button></div><div class="card-body p-0"><table class="table table-hover mb-0"><thead><tr><th>Foto</th><th>Judul</th><th>Deskripsi</th><th>Kategori</th><th>Aksi</th></tr></thead><tbody>${result.data.length ? result.data.map(item => {
                        const imgPath = item.file_path ? item.file_path : 'https://via.placeholder.com/60?text=No+Img';
                        let badgeClass = 'bg-secondary'; if(item.kategori==='Kategori 1') badgeClass='bg-primary'; else if(item.kategori==='Kategori 2') badgeClass='bg-success'; else if(item.kategori==='Kategori 3') badgeClass='bg-warning text-dark'; else if(item.kategori==='Kategori 4') badgeClass='bg-info text-dark';
                        return `<tr><td><img src="${imgPath}" class="img-preview shadow-sm" style="width:60px;height:60px;object-fit:cover;border-radius:6px;" onerror="this.onerror=null;this.src='https://via.placeholder.com/60?text=Error';"></td><td class="fw-bold text-dark text-start">${item.judul}</td><td class="text-muted small text-start">${item.deskripsi || '-'}</td><td><span class="badge ${badgeClass} px-3 py-1 rounded-pill">${item.kategori}</span></td><td class="text-center"><button class="btn btn-sm btn-light text-warning border me-1" onclick="prepareGaleriModal(${item.id_galeri})"><i class="fas fa-pencil-alt"></i></button><button class="btn btn-sm btn-light text-danger border" onclick="deleteItem('api/galeri.php', ${item.id_galeri})"><i class="fas fa-trash-alt"></i></button></td></tr>`;
                    }).join('') : `<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada foto.</td></tr>`}</tbody></table></div></div>`;
                document.getElementById('content-area').innerHTML = contentHtml;
            });
        }
        function prepareGaleriModal(id = null) { if(id) fetch(`api/galeri.php?id=${id}`).then(r=>r.json()).then(res=>{if(res.success)showGaleriModal(res.data);}); else showGaleriModal(null); }
        function showGaleriModal(data = null) {
            const isEdit = data !== null;
            const formHtml = `<input type="hidden" name="id_galeri" value="${isEdit?data.id_galeri:''}"><input type="hidden" name="uploaded_by" value="${isEdit?(data.uploaded_by||''):"<?php echo $_SESSION['nama'] ?? 'Admin'; ?>"}"><div class="row"><div class="col-md-8"><div class="mb-3"><label class="form-label fw-bold">Judul Foto</label><input type="text" name="judul" class="form-control" value="${isEdit?data.judul:''}" required></div><div class="mb-3"><label class="form-label fw-bold">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3">${isEdit?data.deskripsi:''}</textarea></div></div><div class="col-md-4 border-start"><div class="mb-3"><label class="form-label fw-bold">Layout</label><select name="kategori" class="form-select" required><option value="Kategori 1" ${isEdit&&data.kategori==='Kategori 1'?'selected':''}>Kategori 1</option><option value="Kategori 2" ${isEdit&&data.kategori==='Kategori 2'?'selected':''}>Kategori 2</option><option value="Kategori 3" ${isEdit&&data.kategori==='Kategori 3'?'selected':''}>Kategori 3</option><option value="Kategori 4" ${isEdit&&data.kategori==='Kategori 4'?'selected':''}>Kategori 4</option></select><small class="text-muted">Kolom Masonry</small></div><div class="mb-3"><label class="form-label fw-bold">Foto</label><input type="file" name="foto" class="form-control" ${isEdit?'':'required'}></div></div></div>`;
            showModal(isEdit?'Edit Galeri':'Tambah Foto', formHtml, 'api/galeri.php', loadGaleri);
            setTimeout(() => { document.querySelector('#dynamicModal .modal-dialog').classList.add('modal-lg'); }, 100);
        }

        // --- 6. FITUR LAIN (CRUD GENERAL) ---
        function loadResearchFocus() { fetchData('api/research_focus.php', ['Foto', 'Judul', 'Deskripsi'], (i) => `<td><img src="${i.foto}" class="img-preview shadow-sm" style="width:50px;height:40px;object-fit:cover;border-radius:4px;"></td><td class="fw-bold text-start">${i.judul}</td><td class="text-start">${i.deskripsi}</td>`, 'Research Focus', 'prepareResearchModal'); }
        function prepareResearchModal(id=null) { if(id) fetch(`api/research_focus.php?id=${id}`).then(r=>r.json()).then(res=>showResearchModal(res.data)); else showResearchModal(null); }
        function showResearchModal(data=null) {
            const isEdit = data!==null; const formHtml = `<input type="hidden" name="id_research" value="${isEdit?data.id_research:''}"><div class="mb-3"><label>Judul</label><input type="text" name="judul" class="form-control" value="${isEdit?data.judul:''}" required></div><div class="mb-3"><label>Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3">${isEdit?data.deskripsi:''}</textarea></div><div class="mb-3"><label>Foto</label><input type="file" name="foto" class="form-control" ${isEdit?'':'required'}></div>`;
            showModal(isEdit?'Edit Research':'Tambah Research', formHtml, 'api/research_focus.php', loadResearchFocus);
        }

        function loadAnggota() { fetchData('api/anggota.php', ['Foto', 'Nama', 'NIDN', 'Keahlian'], (i) => `<td><img src="${i.foto}" class="img-preview shadow-sm" style="width:50px;height:40px;object-fit:cover;border-radius:4px;"></td><td class="fw-bold text-start">${i.nama_lengkap}</td><td>${i.nidn}</td><td>${i.bidang_keahlian}</td>`, 'Dosen / Anggota', 'prepareAnggotaModal'); }
        function prepareAnggotaModal(id=null) { if(id) fetch(`api/anggota.php?id=${id}`).then(r=>r.json()).then(res=>showAnggotaModal(res.data)); else showAnggotaModal(null); }
        function showAnggotaModal(data=null) {
            const isEdit = data!==null; const formHtml = `<input type="hidden" name="id_dosen" value="${isEdit?data.id_dosen:''}"><div class="mb-3"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" value="${isEdit?data.nama_lengkap:''}" required></div><div class="mb-3"><label>NIDN</label><input type="text" name="nidn" class="form-control" value="${isEdit?data.nidn:''}"></div><div class="mb-3"><label>Keahlian</label><input type="text" name="keahlian" class="form-control" value="${isEdit?data.bidang_keahlian:''}"></div><div class="mb-3"><label>Foto</label><input type="file" name="foto" class="form-control"></div>`;
            showModal(isEdit?'Edit Dosen':'Tambah Dosen', formHtml, 'api/anggota.php', loadAnggota);
        }

        function loadRoadmap() { fetchData('api/roadmap.php', ['Tahun', 'Judul', 'Deskripsi'], (i) => `<td class="fw-bold text-start">${i.tahun}</td><td class="text-start">${i.judul}</td><td class="text-start">${i.deskripsi}</td>`, 'Roadmap', 'prepareRoadmapModal'); }
        function prepareRoadmapModal(id=null) { if(id) fetch(`api/roadmap.php?id=${id}`).then(r=>r.json()).then(res=>showRoadmapModal(res.data)); else showRoadmapModal(null); }
        function showRoadmapModal(data=null) {
            const isEdit = data!==null; const formHtml = `<input type="hidden" name="id_roadmap" value="${isEdit?data.id_roadmap:''}"><div class="mb-3"><label>Tahun</label><input type="text" name="tahun" class="form-control" value="${isEdit?data.tahun:''}" required></div><div class="mb-3"><label>Judul</label><input type="text" name="judul" class="form-control" value="${isEdit?data.judul:''}" required></div><div class="mb-3"><label>Deskripsi</label><textarea name="deskripsi" class="form-control">${isEdit?data.deskripsi:''}</textarea></div>`;
            showModal(isEdit?'Edit Roadmap':'Tambah Roadmap', formHtml, 'api/roadmap.php', loadRoadmap);
        }

        function loadFasilitas() { fetchData('api/fasilitas.php', ['Nama', 'Jumlah', 'Deskripsi'], (i) => `<td class="fw-bold text-start">${i.nama_fasilitas}</td><td class="text-start">${i.jumlah}</td><td class="text-start">${i.deskripsi}</td>`, 'Fasilitas Lab', 'prepareFasilitasModal'); }
        function prepareFasilitasModal(id=null) { if(id) fetch(`api/fasilitas.php?id=${id}`).then(r=>r.json()).then(res=>showFasilitasModal(res.data)); else showFasilitasModal(null); }
        function showFasilitasModal(data=null) {
            const isEdit = data!==null; const formHtml = `<input type="hidden" name="id_facility" value="${isEdit?data.id_facility:''}"><div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" value="${isEdit?data.nama_fasilitas:''}" required></div><div class="mb-3"><label>Jumlah</label><input type="number" name="jumlah" class="form-control" value="${isEdit?data.jumlah:''}"></div><div class="mb-3"><label>Deskripsi</label><textarea name="deskripsi" class="form-control">${isEdit?data.deskripsi:''}</textarea></div>`;
            showModal(isEdit?'Edit Fasilitas':'Tambah Fasilitas', formHtml, 'api/fasilitas.php', loadFasilitas);
        }

        // --- READ ONLY ---
        function loadBeranda() { document.getElementById('content-area').innerHTML = `<div class="row"><div class="col-md-3"><div class="card p-4 text-center"><h3 class="fw-bold text-primary">12</h3><small>Booking</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h3 class="fw-bold text-success">5</h3><small>News</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h3 class="fw-bold text-warning">24</h3><small>Berita</small></div></div><div class="col-md-3"><div class="card p-4 text-center"><h3 class="fw-bold text-danger">8</h3><small>Pesan</small></div></div></div>`; }
        function loadNewsService() { fetchData('api/news_service.php', ['Pengaju', 'Judul', 'Status', 'Aksi'], (i) => `<td class="text-start">${i.nama_pengaju}</td><td class="text-start">${i.judul_berita}</td><td>${i.status_pengajuan}</td><td><button class="btn btn-sm btn-success">Approve</button></td>`, 'Approval', null); }
        function loadBooking() { fetchData('api/booking.php', ['Peminjam', 'Tanggal', 'Status'], (i) => `<td class="text-start">${i.nama_peminjam}</td><td>${i.tanggal_peminjaman}</td><td>${i.request_pembatalan||'Pending'}</td>`, 'Booking', null); }
        function loadKontak() { fetchData('api/kontak.php', ['Nama', 'Email', 'Pesan'], (i) => `<td class="fw-bold text-start">${i.nama}</td><td class="text-start">${i.email}</td><td class="text-start">${i.pesan}</td>`, 'Pesan', null); }

        // --- HELPER FUNCTIONS ---
        function fetchData(apiUrl, headers, rowRenderer, title, modalFunc) {
            fetch(apiUrl).then(res => res.json()).then(result => {
                if(!result.success) { alert(result.message); return; }
                let thead = headers.map(h => `<th>${h}</th>`).join('');
                if(modalFunc || headers.includes('Aksi')) thead += `<th>Aksi</th>`;
                let tbody = result.data.length ? result.data.map(item => `<tr>${rowRenderer(item)}${modalFunc ? `<td class="text-center"><button class="btn btn-sm btn-light text-warning border me-1" onclick="${modalFunc}(${Object.values(item)[0]})"><i class="fas fa-pencil-alt"></i></button><button class="btn btn-sm btn-light text-danger border" onclick="deleteItem('${apiUrl}', ${Object.values(item)[0]})"><i class="fas fa-trash-alt"></i></button></td>` : ''}</tr>`).join('') : `<tr><td colspan="${headers.length + 2}" class="text-center py-5 text-muted">Data kosong.</td></tr>`;
                document.getElementById('content-area').innerHTML = `<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><span>${title}</span>${modalFunc ? `<button class="btn btn-sm btn-primary-custom" onclick="${modalFunc}()"><i class="fas fa-plus"></i> Tambah</button>` : ''}</div><div class="card-body p-0"><table class="table table-hover mb-0"><thead><tr>${thead}</tr></thead><tbody>${tbody}</tbody></table></div></div>`;
            }).catch(err => console.error(err));
        }
        function showModal(title, bodyHtml, apiUrl, refreshCallback) {
            const modalId = 'dynamicModal';
            document.getElementById('modal-container').innerHTML = `<div class="modal fade" id="${modalId}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">${title}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="dynamicForm"><div class="modal-body">${bodyHtml}</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Simpan</button></div></form></div></div></div>`;
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
            document.getElementById('dynamicForm').onsubmit = (e) => { e.preventDefault(); submitForm(apiUrl, new FormData(e.target), modal, refreshCallback); };
        }
        function submitForm(apiUrl, formData, modal, refreshFunc) {
            fetch(apiUrl, { method: 'POST', body: formData }).then(r => r.json()).then(data => { if(data.success) { alert('Berhasil!'); if(modal) modal.hide(); if(refreshFunc) refreshFunc(); } else { alert('Gagal: ' + data.message); } });
        }
        function deleteItem(apiUrl, id) {
            if(!confirm('Hapus data ini?')) return;
            const formData = new FormData(); formData.append('action', 'delete'); formData.append('id', id);
            fetch(apiUrl, { method: 'POST', body: formData }).then(r => r.json()).then(data => { if(data.success) { loadPage(currentPage); } else { alert('Gagal menghapus'); } });
        }
        loadPage('beranda');
    </script>
</body>
</html>