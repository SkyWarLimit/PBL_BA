<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Laboratorium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-size: 0.9rem; }
        .sidebar { min-height: 100vh; background: #212529; position: fixed; top: 0; left: 0; width: 250px; z-index: 100; }
        .sidebar .nav-link { color: #adb5bd; padding: 0.75rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: #495057; }
        .sidebar .nav-link i { width: 20px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); margin-bottom: 1.5rem; }
        .stats-card { border-left: 4px solid #0d6efd; }
        .stats-card.warning { border-left-color: #ffc107; }
        .stats-card.danger { border-left-color: #dc3545; }
        .stats-card.success { border-left-color: #198754; }
        .modal-backdrop { z-index: 1040; }
        .modal { z-index: 1050; }
        .table-responsive { max-height: 500px; overflow-y: auto; }
        .img-preview { max-width: 200px; max-height: 200px; margin-top: 10px; }
    </style>
</head>
<body>
    <?php
    require_once '../config/database.php';
    checkAuth();
    ?>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3 text-white border-bottom">
            <h5><i class="fas fa-flask me-2"></i>Lab Admin</h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="#" data-page="beranda"><i class="fas fa-home me-2"></i>Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="peminjaman"><i class="fas fa-clipboard-list me-2"></i>Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="kontak"><i class="fas fa-envelope me-2"></i>Pesan Kontak</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="pengaturan"><i class="fas fa-cog me-2"></i>Pengaturan Web</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="anggota"><i class="fas fa-users me-2"></i>Kelola Anggota</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="sosmed"><i class="fas fa-share-alt me-2"></i>Sosial Media</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="galeri"><i class="fas fa-images me-2"></i>Galeri</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="artikel"><i class="fas fa-newspaper me-2"></i>Artikel</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="penelitian"><i class="fas fa-microscope me-2"></i>Penelitian</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="pengabdian"><i class="fas fa-hands-helping me-2"></i>Pengabdian</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-page="log"><i class="fas fa-history me-2"></i>Log Aktivitas</a></li>
            <li class="nav-item mt-3"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div id="content-area">
            <!-- Content will be loaded here -->
        </div>
    </div>

    <!-- Modals will be added dynamically -->
    <div id="modal-container"></div>

    <script src="admin.js"></script>
    <script src="./admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentPage = 'beranda';
        
        // Navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '../logout.php') return;
                e.preventDefault();
                
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                currentPage = this.dataset.page;
                loadPage(currentPage);
            });
        });

        // Load page content
        function loadPage(page) {
            switch(page) {
                case 'beranda': loadBeranda(); break;
                case 'peminjaman': loadPeminjaman(); break;
                case 'kontak': loadKontak(); break;
                case 'pengaturan': loadPengaturan(); break;
                case 'anggota': loadAnggota(); break;
                case 'sosmed': loadSosmed(); break;
                case 'galeri': loadGaleri(); break;
                case 'artikel': loadArtikel(); break;
                case 'penelitian': loadPenelitian(); break;
                case 'pengabdian': loadPengabdian(); break;
                case 'log': loadLog(); break;
            }
        }

        // Beranda
        function loadBeranda() {
            const content = `
                <h2 class="mb-4">Dashboard</h2>
                <div class="row" id="stats-row">
                    <div class="col-md-3">
                        <div class="card stats-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Pending Peminjaman</h6>
                                        <h3 id="stat-pending">0</h3>
                                    </div>
                                    <div><i class="fas fa-clock fa-2x text-warning"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Request Pembatalan</h6>
                                        <h3 id="stat-pembatalan">0</h3>
                                    </div>
                                    <div><i class="fas fa-times-circle fa-2x text-danger"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Pesan Baru</h6>
                                        <h3 id="stat-pesan">0</h3>
                                    </div>
                                    <div><i class="fas fa-envelope fa-2x text-primary"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted">Total Galeri</h6>
                                        <h3 id="stat-galeri">0</h3>
                                    </div>
                                    <div><i class="fas fa-images fa-2x text-success"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-activity"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('content-area').innerHTML = content;
            loadDashboardStats();
            loadRecentActivity();
        }

        function loadDashboardStats() {
            fetch('../api/dashboard.php?action=stats')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('stat-pending').textContent = data.data.pending_peminjaman || 0;
                        document.getElementById('stat-pembatalan').textContent = data.data.request_pembatalan || 0;
                        document.getElementById('stat-pesan').textContent = data.data.unread_kontak || 0;
                        document.getElementById('stat-galeri').textContent = data.data.total_galeri || 0;
                    }
                });
        }

        function loadRecentActivity() {
            fetch('../api/log.php?action=recent&limit=10')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('recent-activity');
                        tbody.innerHTML = data.data.map(log => `
                            <tr>
                                <td>${new Date(log.waktu).toLocaleString('id-ID')}</td>
                                <td>${log.nama || 'System'}</td>
                                <td><span class="badge bg-info">${log.aktivitas}</span></td>
                                <td>${log.deskripsi}</td>
                            </tr>
                        `).join('');
                    }
                });
        }

        // Galeri Functions
        function loadGaleri() {
            const content = `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Kelola Galeri</h2>
                    <button class="btn btn-primary" onclick="showGaleriModal()">
                        <i class="fas fa-plus me-2"></i>Tambah Galeri
                    </button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Tanggal Upload</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="galeri-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('content-area').innerHTML = content;
            loadGaleriData();
        }

        function loadGaleriData() {
            fetch('../api/galeri.php?action=list')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('galeri-table');
                        tbody.innerHTML = data.data.map(item => `
                            <tr>
                                <td><img src="../${item.file_path}" style="width:80px;height:60px;object-fit:cover;"></td>
                                <td>${item.judul}</td>
                                <td>${item.kategori || '-'}</td>
                                <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                                <td>${new Date(item.tanggal_upload).toLocaleDateString('id-ID')}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editGaleri(${item.id_galeri})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteGaleri(${item.id_galeri})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('');
                    }
                });
        }

        function showGaleriModal(id = null) {
            const modal = `
                <div class="modal fade" id="galeriModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Galeri</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="galeriForm" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="id_galeri" id="id_galeri" value="${id || ''}">
                                    <div class="mb-3">
                                        <label class="form-label">Judul *</label>
                                        <input type="text" class="form-control" name="judul" id="judul" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Kategori</label>
                                        <input type="text" class="form-control" name="kategori" id="kategori">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Foto ${id ? '' : '*'}</label>
                                        <input type="file" class="form-control" name="foto" id="foto" accept="image/*" ${id ? '' : 'required'} onchange="previewImage(this, 'galeri-preview')">
                                        <img id="galeri-preview" class="img-preview" style="display:none;">
                                    </div>
                                    ${id ? '<div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"><label class="form-check-label" for="is_active">Aktif</label></div></div>' : ''}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-container').innerHTML = modal;
            const modalEl = new bootstrap.Modal(document.getElementById('galeriModal'));
            modalEl.show();
            
            if (id) {
                fetch(`../api/galeri.php?action=get&id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('judul').value = data.data.judul;
                            document.getElementById('deskripsi').value = data.data.deskripsi || '';
                            document.getElementById('kategori').value = data.data.kategori || '';
                            document.getElementById('is_active').checked = data.data.is_active;
                            document.getElementById('galeri-preview').src = '../' + data.data.file_path;
                            document.getElementById('galeri-preview').style.display = 'block';
                        }
                    });
            }
            
            document.getElementById('galeriForm').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const action = id ? 'update' : 'add';
                
                fetch(`../api/galeri.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modalEl.hide();
                        loadGaleriData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            };
        }

        function editGaleri(id) {
            showGaleriModal(id);
        }

        function deleteGaleri(id) {
            if (!confirm('Yakin ingin menghapus galeri ini?')) return;
            
            const formData = new FormData();
            formData.append('id_galeri', id);
            
            fetch('../api/galeri.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) loadGaleriData();
            });
        }

        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Initialize
        loadPage('beranda');
    </script>
</body>
</html>