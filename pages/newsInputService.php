<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
// Role default jika tidak ada session
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

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
    <title>Input Berita - Laboratorium Business Analytics</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/newsInputServiceStyle.css?v=<?php echo time(); ?>">
</head>

<body>

    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Business Analytics Logo">
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
    
    <div class="container-fluid news-input-container">
        <div class="row">
            
            <div class="col-lg-8 order-2 order-lg-1">
                <h1 class="news-input-title">News Input From Users</h1>
                <p class="news-input-subtitle">enter the following content :</p>

                <form id="newsForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($userName); ?>" 
                               <?php echo $isLoggedIn ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed;"' : ''; ?> 
                               placeholder="Nama User">
                    </div>

                    <div class="mb-3">
                        <label for="newsDate" class="form-label">Tanggal Berita</label>
                        <input type="date" class="form-control" id="newsDate" name="tanggal" required>
                    </div>

                    <div class="mb-3">
                        <label for="newsTitle" class="form-label">Judul Berita</label>
                        <input type="text" class="form-control" id="newsTitle" name="judul" placeholder="Masukkan judul berita yang menarik" required>
                    </div>

                    <div class="mb-3">
                        <label for="newsCategory" class="form-label">Kategori Berita</label>
                        <select class="form-select" id="newsCategory" name="kategori" required>
                            <option value="" selected disabled>-- Pilih Kategori --</option>
                            <option value="News Latest">News Latest</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Announcement">Announcement</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Masukkan Foto Berita</label>
                        <div class="file-upload-container" id="fileUploadContainer">
                            <div id="uploadPlaceholder" class="file-upload-placeholder">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <p class="file-upload-text">Upload Foto Berita</p>
                                <p class="file-upload-subtext">Klik atau seret file ke sini</p>
                            </div>

                            <div id="imagesPreviewContainer" class="images-preview-container" style="display: none;"></div>

                            <div id="uploadInfo" class="upload-info" style="display: none;">
                                <div id="uploadedCount" class="uploaded-count"></div>
                                <div class="preview-actions">
                                    <button type="button" class="preview-btn" id="addMoreImagesBtn">
                                        <i class="fas fa-sync-alt"></i> Ganti Gambar
                                    </button>
                                    <button type="button" class="preview-btn remove" id="removeAllImagesBtn">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            <input type="file" class="file-input" id="newsPhoto" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="newsDescription" class="form-label">Deskripsi Berita</label>
                        <textarea class="form-control" id="newsDescription" name="deskripsi" rows="5"
                            placeholder="Jelaskan detail berita atau kegiatan..." required></textarea>
                    </div>

                    <button type="submit" class="confirm-btn" id="submitBtn">
                        <span id="btnText">Kirim Berita</span>
                        <div id="btnSpinner" class="spinner-border spinner-border-sm text-light d-none" role="status"></div>
                    </button>
                </form>
            </div>

            <div class="col-lg-4 order-1 order-lg-2">
                <div class="rules-container">
                    <h3 class="rules-title"><i class="fas fa-book me-2"></i>Aturan Pengiriman</h3>
                    <p class="rules-content">
                        Pastikan berita yang Anda kirimkan relevan dengan kegiatan laboratorium atau akademik.
                        Hindari konten yang mengandung unsur SARA atau Hoax.
                    </p>

                    <div class="rules-divider"></div>

                    <h3 class="rules-title"><i class="fas fa-check-circle me-2"></i>Proses Persetujuan</h3>
                    <p class="rules-content">
                        Berita yang Anda kirimkan akan masuk ke status <strong>"Pending"</strong>.
                        Admin akan meninjau konten Anda sebelum diterbitkan ke halaman publik.
                        Silakan cek status secara berkala.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- 0. NAV MENU LOGIC ---
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

        // --- 1. Cek Login Status ---
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Terbatas',
                    text: 'Halo! Untuk berkontribusi mengirimkan berita ke Laboratorium, silakan login terlebih dahulu.',
                    showCancelButton: true,
                    confirmButtonText: 'Login Akun',
                    cancelButtonText: 'Kembali ke Beranda',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    backdrop: `rgba(15, 23, 42, 0.6)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../admin/login.php';
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = '../index.php';
                    }
                });
            }
        });

        // --- 2. Logic Upload Gambar ---
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const fileInput = document.getElementById('newsPhoto');
        const imagesPreviewContainer = document.getElementById('imagesPreviewContainer');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadInfo = document.getElementById('uploadInfo');
        const uploadedCount = document.getElementById('uploadedCount');
        const removeAllImagesBtn = document.getElementById('removeAllImagesBtn');
        const addMoreImagesBtn = document.getElementById('addMoreImagesBtn');

        let uploadedFile = null;

        fileUploadContainer.addEventListener('click', (e) => {
            if (!e.target.closest('.preview-btn')) fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });

        fileUploadContainer.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        fileUploadContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'error', title: 'File Tidak Valid', text: 'Format harus JPG, JPEG, atau PNG.' });
                return;
            }
            uploadedFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                imagesPreviewContainer.innerHTML = `<div class="image-preview-item"><img src="${e.target.result}"></div>`;
                uploadPlaceholder.style.display = 'none';
                imagesPreviewContainer.style.display = 'grid';
                uploadInfo.style.display = 'flex';
                uploadedCount.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Foto Siap Diupload`;
            }
            reader.readAsDataURL(file);
        }

        removeAllImagesBtn.addEventListener('click', () => {
            uploadedFile = null; fileInput.value = ''; imagesPreviewContainer.innerHTML = '';
            uploadPlaceholder.style.display = 'flex'; imagesPreviewContainer.style.display = 'none'; uploadInfo.style.display = 'none';
        });
        
        addMoreImagesBtn.addEventListener('click', () => fileInput.click());

        // --- 3. Logic Submit Form ---
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!uploadedFile) {
                Swal.fire({ icon: 'warning', title: 'Foto Belum Ada', text: 'Sertakan minimal satu foto.' });
                return;
            }

            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btn.disabled = true; btnText.textContent = 'Sedang Mengirim...'; btnSpinner.classList.remove('d-none');

            const formData = new FormData();
            formData.append('name', document.getElementById('name').value); 
            formData.append('judul', document.getElementById('newsTitle').value);
            formData.append('kategori', document.getElementById('newsCategory').value);
            formData.append('deskripsi', document.getElementById('newsDescription').value);
            formData.append('tanggal', document.getElementById('newsDate').value);
            formData.append('foto', uploadedFile); 

            fetch('../admin/api/submit_news.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message }).then(() => {
                        document.getElementById('newsForm').reset(); removeAllImagesBtn.click();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(error => Swal.fire({ icon: 'error', title: 'Error', text: 'Kesalahan server.' }))
            .finally(() => {
                btn.disabled = false; btnText.textContent = 'Kirim Berita'; btnSpinner.classList.add('d-none');
            });
        });
    </script>
</body>
</html>