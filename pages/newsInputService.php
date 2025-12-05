<?php
session_start();

// Cek status login & Ambil nama user
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
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
    <link rel="stylesheet" href="../assets/css/newsInputServiceStyle.css">

    <style>
        /* === MODERN MODAL STYLING === */
        
        .modal-backdrop.show {
            opacity: 1;
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .modern-modal-content {
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .modern-modal-content::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            opacity: 0.1;
            border-radius: 50%;
        }

        .modal-icon-wrapper {
            width: 90px;
            height: 90px;
            background: #eaf0ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
            position: relative;
            z-index: 1;
        }

        .modal-icon-wrapper i {
            font-size: 2.5rem;
            color: #4361ee;
            animation: floatIcon 3s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .modal-title-custom {
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            color: #1e293b;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .modal-text-custom {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-modal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.4);
            color: white;
        }

        .btn-modal-secondary {
            background: transparent;
            color: #64748b;
            border: 2px solid #e2e8f0;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-modal-secondary:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }
    </style>
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
        
        <?php if(!$isLoggedIn): ?>
            <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
        <?php else: ?>
            <button class="login-btn" style="background-color: #e63946;" onclick="window.location.href='../admin/logout.php'">Logout</button>
        <?php endif; ?>
    </nav>

    <div class="container-fluid news-input-container">
        <div class="row">
            <div class="col-lg-8">
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

            <div class="col-lg-4">
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

    <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content modern-modal-content">
                <div class="modal-icon-wrapper">
                    <i class="fas fa-lock"></i>
                </div>
                
                <h3 class="modal-title-custom">Akses Terbatas</h3>
                <p class="modal-text-custom">
                    Halo! Untuk berkontribusi mengirimkan berita ke Laboratorium, silakan login terlebih dahulu.
                </p>

                <div class="d-flex flex-column gap-2">
                    <button class="btn-modal-primary" onclick="window.location.href='../admin/login.php'">
                        <i class="fas fa-sign-in-alt me-2"></i>Login Akun
                    </button>
                    <button class="btn-modal-secondary" onclick="window.location.href='../index.php'">
                        Kembali ke Beranda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. Cek Login Status
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            if (!isLoggedIn) {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }
        });

        // 2. Logic Upload Gambar
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
            fileUploadContainer.style.borderColor = '#4361ee';
            fileUploadContainer.style.backgroundColor = '#f8faff';
        });

        fileUploadContainer.addEventListener('dragleave', () => {
            fileUploadContainer.style.borderColor = '#cbd5e1';
            fileUploadContainer.style.backgroundColor = '#fff';
        });

        fileUploadContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#cbd5e1';
            fileUploadContainer.style.backgroundColor = '#fff';
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('Mohon upload file gambar yang valid (JPG/PNG).');
                return;
            }
            uploadedFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                imagesPreviewContainer.innerHTML = `
                    <div class="image-preview-item" style="box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <img src="${e.target.result}" class="image-preview" style="object-fit: cover;">
                    </div>`;
                uploadPlaceholder.style.display = 'none';
                imagesPreviewContainer.style.display = 'grid';
                uploadInfo.style.display = 'flex';
                uploadedCount.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Foto Siap Diupload`;
            }
            reader.readAsDataURL(file);
        }

        removeAllImagesBtn.addEventListener('click', () => {
            uploadedFile = null;
            fileInput.value = '';
            imagesPreviewContainer.innerHTML = '';
            uploadPlaceholder.style.display = 'flex';
            imagesPreviewContainer.style.display = 'none';
            uploadInfo.style.display = 'none';
        });
        
        addMoreImagesBtn.addEventListener('click', () => {
             fileInput.click();
        });

        // 3. Logic Submit Form ke API
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!uploadedFile) {
                alert('Silakan sertakan minimal satu foto untuk berita ini.');
                return;
            }

            // UI Loading
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btn.disabled = true;
            btnText.textContent = 'Sedang Mengirim...';
            btnSpinner.classList.remove('d-none');

            // Persiapan Data
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value); 
            formData.append('judul', document.getElementById('newsTitle').value);
            formData.append('kategori', document.getElementById('newsCategory').value); // Kategori Baru
            formData.append('deskripsi', document.getElementById('newsDescription').value);
            formData.append('tanggal', document.getElementById('newsDate').value);
            formData.append('foto', uploadedFile); 

            // Fetch ke API Backend
            fetch('../admin/api/submit_news.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('🎉 Berhasil! ' + data.message);
                    document.getElementById('newsForm').reset();
                    removeAllImagesBtn.click();
                } else {
                    alert('❌ Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Terjadi kesalahan koneksi ke server.');
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Kirim Berita';
                btnSpinner.classList.add('d-none');
            });
        });
    </script>
</body>
</html>