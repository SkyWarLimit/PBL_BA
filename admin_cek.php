<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Lab Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
        }
        
        body {
            background: #ecf0f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: var(--primary);
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar .brand {
            padding: 20px;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 25px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            position: relative;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .badge-pending { background: var(--warning); }
        .badge-approved { background: var(--success); }
        .badge-rejected { background: var(--danger); }
        .badge-cancelled { background: #95a5a6; }
        
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .action-btn {
            padding: 5px 10px;
            font-size: 0.875rem;
            margin: 0 2px;
        }
        
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 10px;
        }
        
        .anggota-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .anggota-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .anggota-foto {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .sosmed-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .sosmed-icon {
            font-size: 2rem;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="bi bi-gear-fill"></i> Lab Admin
        </div>
        <nav class="nav flex-column mt-4">
            <a class="nav-link active" href="#" data-tab="beranda">
                <i class="bi bi-house-door me-2"></i> Beranda
                <span class="notification-badge" id="totalNotif">0</span>
            </a>
            <a class="nav-link" href="#" data-tab="peminjaman">
                <i class="bi bi-calendar-check me-2"></i> Peminjaman
            </a>
            <a class="nav-link" href="#" data-tab="kontak">
                <i class="bi bi-envelope me-2"></i> Pesan Kontak
                <span class="notification-badge" id="kontakNotif">0</span>
            </a>
            <a class="nav-link" href="#" data-tab="pengaturan">
                <i class="bi bi-sliders me-2"></i> Pengaturan Web
            </a>
            <a class="nav-link" href="#" data-tab="anggota">
                <i class="bi bi-people me-2"></i> Kelola Anggota
            </a>
            <a class="nav-link" href="#" data-tab="sosmed">
                <i class="bi bi-share me-2"></i> Sosial Media
            </a>
            <a class="nav-link" href="#" data-tab="galeri">
                <i class="bi bi-images me-2"></i> Galeri
            </a>
            <a class="nav-link" href="#" data-tab="artikel">
                <i class="bi bi-file-text me-2"></i> Artikel
            </a>
            <a class="nav-link" href="#" data-tab="logout">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Tab Beranda -->
        <div class="tab-content" id="berandaTab">
            <h2 class="mb-4">Dashboard</h2>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Peminjaman Baru</h6>
                                <h3 class="mb-0" id="statPending">0</h3>
                            </div>
                            <div class="icon" style="background: var(--warning);">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Request Pembatalan</h6>
                                <h3 class="mb-0" id="statPembatalan"></h3>
                            </div>
                            <div class="icon" style="background: var(--danger);">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pesan Baru</h6>
                                <h3 class="mb-0" id="statKontak">0</h3>
                            </div>
                            <div class="icon" style="background: var(--secondary);">
                                <i class="bi bi-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Approved</h6>
                                <h3 class="mb-0" id="statApproved">0</h3>
                            </div>
                            <div class="icon" style="background: var(--success);">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="table-card">
                <h5 class="mb-3">Aktivitas Terbaru</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Nama</th>
                                <th>Detail</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="recentActivities"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Peminjaman -->
        <div class="tab-content d-none" id="peminjamanTab">
            <h2 class="mb-4">Daftar Peminjaman</h2>
            
            <!-- Filter -->
            <div class="table-card mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filterTanggal" placeholder="Filter Tanggal">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchPeminjam" placeholder="Cari nama peminjam...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="loadPeminjaman()">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- List Peminjaman -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Tujuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="peminjamanList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Kontak -->
        <div class="tab-content d-none" id="kontakTab">
            <h2 class="mb-4">Pesan Kontak</h2>
            
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Subjek</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kontakList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Pengaturan Web -->
        <div class="tab-content d-none" id="pengaturanTab">
            <h2 class="mb-4">Pengaturan Website</h2>
            
            <div class="row">
                <!-- Logo Section -->
                <div class="col-md-6">
                    <div class="table-card">
                        <h5 class="mb-3"><i class="bi bi-image me-2"></i>Logo Website</h5>
                        <form id="formLogo" enctype="multipart/form-data">
                            <div class="mb-3 text-center">
                                <img id="logoPreview" src="uploads/logo.png" alt="Logo" class="preview-image mb-3" onerror="this.src='https://via.placeholder.com/200x200?text=No+Logo'">
                                <p class="text-muted small">Logo saat ini</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Baru</label>
                                <input type="file" class="form-control" id="logoFile" accept="image/*" onchange="previewLogo(this)">
                                <small class="text-muted">Format: PNG, JPG, SVG (Max 2MB)</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Logo
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Lab -->
                <div class="col-md-6">
                    <div class="table-card">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Laboratorium</h5>
                        <form id="formInfoLab">
                            <div class="mb-3">
                                <label class="form-label">Nama Laboratorium</label>
                                <input type="text" class="form-control" id="namaLab" placeholder="Lab Informatika">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="emailLab">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="telpLab">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Informasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Kelola Anggota -->
        <div class="tab-content d-none" id="anggotaTab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Kelola Anggota Tim</h2>
                <button class="btn btn-primary" onclick="showAddAnggota()">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Anggota
                </button>
            </div>
            
            <div class="row" id="anggotaList">
                <!-- Anggota cards akan dimuat di sini -->
            </div>
        </div>

        <!-- Tab Sosial Media -->
        <div class="tab-content d-none" id="sosmedTab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Sosial Media Laboratorium</h2>
                <button class="btn btn-primary" onclick="showAddSosmed()">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Sosial Media
                </button>
            </div>
            
            <div class="table-card">
                <div id="sosmedList">
                    <!-- Sosmed items akan dimuat di sini -->
                </div>
            </div>
        </div>

        <!-- Tab Galeri -->
        <div class="tab-content d-none" id="galeriTab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Kelola Galeri</h2>
                <button class="btn btn-primary" onclick="showAddGaleri()">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Foto
                </button>
            </div>
            
            <!-- Filter -->
            <div class="table-card mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterKategoriGaleri" onchange="loadGaleri()">
                            <option value="">Semua Kategori</option>
                            <option value="Fasilitas">Fasilitas</option>
                            <option value="Kegiatan">Kegiatan</option>
                            <option value="Event">Event</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchGaleri" placeholder="Cari judul foto...">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="loadGaleri()">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Grid Galeri -->
            <div class="row" id="galeriGrid"></div>
        </div>

        <!-- Tab Artikel -->
        <div class="tab-content d-none" id="artikelTab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Kelola Artikel/Berita</h2>
                <button class="btn btn-primary" onclick="showAddArtikel()">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Artikel
                </button>
            </div>
            
            <!-- Filter -->
            <div class="table-card mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterKategoriArtikel" onchange="loadArtikel()">
                            <option value="">Semua Kategori</option>
                            <option value="Berita">Berita</option>
                            <option value="Event">Event</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Pengumuman">Pengumuman</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchArtikel" placeholder="Cari judul atau konten...">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="loadArtikel()">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- List Artikel -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="artikelList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Peminjaman -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Aksi -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTitle">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                    <div id="catatanAdminDiv" class="d-none">
                        <label class="form-label">Catatan Admin:</label>
                        <textarea class="form-control" id="catatanAdmin" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmBtn">Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Anggota -->
    <div class="modal fade" id="anggotaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="anggotaModalTitle">Tambah Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAnggota" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="anggotaId">
                        <div class="mb-3 text-center">
                            <img id="anggotaFotoPreview" src="https://via.placeholder.com/150" class="preview-image mb-2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <input type="file" class="form-control" id="anggotaFoto" accept="image/*" onchange="previewAnggotaFoto(this)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="anggotaNama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="anggotaJabatan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="anggotaEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" class="form-control" id="anggotaUrutan" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Sosmed -->
    <div class="modal fade" id="sosmedModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sosial Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSosmed">
                    <div class="modal-body">
                        <input type="hidden" id="sosmedId">
                        <div class="mb-3">
                            <label class="form-label">Platform <span class="text-danger">*</span></label>
                            <select class="form-select" id="sosmedPlatform" required>
                                <option value="">Pilih Platform</option>
                                <option value="instagram">Instagram</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter (X)</option>
                                <option value="youtube">YouTube</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="tiktok">TikTok</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="sosmedUsername" placeholder="@username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="sosmedUrl" required placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Galeri -->
    <div class="modal fade" id="galeriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galeriModalTitle">Tambah Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formGaleri" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="galeriId">
                        <div class="mb-3 text-center">
                            <img id="galeriFotoPreview" src="https://via.placeholder.com/400x300?text=Preview+Foto" class="preview-image mb-2" style="max-width: 100%; max-height: 300px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="galeriFoto" accept="image/*" onchange="previewGaleriFoto(this)" required>
                            <small class="text-muted">Format: JPG, PNG, WEBP (Max 5MB)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="galeriJudul" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="galeriDeskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" id="galeriKategori">
                                <option value="">Pilih Kategori</option>
                                <option value="Fasilitas">Fasilitas</option>
                                <option value="Kegiatan">Kegiatan</option>
                                <option value="Event">Event</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Artikel -->
    <div class="modal fade" id="artikelModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="artikelModalTitle">Tambah Artikel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formArtikel" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="artikelId">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="artikelJudul" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ringkasan</label>
                                    <textarea class="form-control" id="artikelRingkasan" rows="2" placeholder="Ringkasan singkat artikel..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Konten <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="artikelKonten" rows="10" required></textarea>
                                    <small class="text-muted">Tulis konten artikel lengkap di sini</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-select" id="artikelKategori">
                                        <option value="">Pilih Kategori</option>
                                        <option value="Berita">Berita</option>
                                        <option value="Event">Event</option>
                                        <option value="Tutorial">Tutorial</option>
                                        <option value="Pengumuman">Pengumuman</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">File Pendukung</label>
                                    <input type="file" class="form-control" id="artikelFile" accept=".jpg,.jpeg,.png,.pdf,.docx">
                                    <small class="text-muted">PDF, DOCX, atau Gambar (Max 10MB)</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="artikelPublish" checked>
                                        <label class="form-check-label" for="artikelPublish">
                                            Publikasikan Sekarang
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>