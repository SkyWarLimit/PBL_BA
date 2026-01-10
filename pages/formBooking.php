<?php
session_start();

// 1. Cek Login (Wajib)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../admin/login.php';</script>";
    exit;
}

// 2. Ambil Data dari URL (dikirim dari booking.php)
$dateParam = $_GET['date'] ?? date('Y-m-d');
$checkinParam = $_GET['checkin'] ?? '00:00';
$checkoutParam = $_GET['checkout'] ?? '00:00';

// Format Data untuk Tampilan Summary
setlocale(LC_TIME, 'id_ID');
$dateDisplay = date("d F Y", strtotime($dateParam));
$timeDisplay = "$checkinParam - $checkoutParam WIB";

// Format Data untuk API (YYYY-MM-DD HH:MM:SS)
$apiCheckin = "$dateParam $checkinParam:00";
$apiCheckout = "$dateParam $checkoutParam:00";

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
// Role default jika tidak ada session
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/formBookingStyle.css?v=<?php echo time(); ?>">
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

        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-menu" id="navMenu">
            <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php">Profil</a></li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

    <div class="container-fluid main-content">
        <div class="breadcrumb-container">
            <a href="#" class="breadcrumb-link">Beranda</a>
            <span class="breadcrumb-separator">/</span>
            <a href="infoPeminjaman.html" class="breadcrumb-link">Informasi Peminjaman</a>
            <span class="breadcrumb-separator">/</span>
            <a href="booking.php" class="breadcrumb-link">Booking</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Review Your Booking</span>
        </div>

        <div class="row">
            <div class="col-md-7 order-2 order-md-1">
                <div class="form-container">
                    <h1 class="page-title">Review Your Booking</h1>
                    <h2 class="section-title">Contact Information :</h2>
                    
                    <form id="bookingForm">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="check_in" value="<?php echo $apiCheckin; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $apiCheckout; ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?>" readonly style="background-color: #e9ecef;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly style="background-color: #e9ecef;">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status/Kategori Pemohon</label>
                            <select class="form-select" id="status" name="kategori_pemohon" required>
                                <option selected disabled value="">Pilih status</option>
                                <option value="Mahasiswa">Mahasiswa</option>
                                <option value="Dosen">Dosen</option>
                                <option value="Umum">Umum</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nim-nip" class="form-label">NIM/NIP/Instansi</label>
                            <input type="text" class="form-control" id="nim-nip" name="nomor_identitas" placeholder="Masukkan NIM/NIP/Instansi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="program" class="form-label">Program Studi/Departemen</label>
                            <input type="text" class="form-control" id="program" name="asal_instansi" placeholder="Masukkan program studi/departemen" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Handphone</label>
                            <input type="tel" class="form-control" id="phone" name="no_handphone" placeholder="Masukkan nomor handphone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Tujuan Peminjaman</label>
                            <textarea class="form-control" id="purpose" name="tujuan" rows="3" placeholder="Jelaskan tujuan peminjaman laboratorium" required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary confirm-btn">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-5 order-1 order-md-2 mb-4 mb-md-0">
                <div class="booking-summary">
                    <h3 class="summary-title">Booking Summary</h3>
                    <div class="summary-content">
                        <p class="summary-text">
                            Silakan periksa kembali detail peminjaman Anda di bawah ini. Pastikan tanggal dan waktu yang dipilih sudah sesuai dengan rencana kegiatan Anda.
                        </p>
                        
                        <div class="booking-details">
                            <div class="detail-item">
                                <span class="detail-label">Tanggal:</span>
                                <span class="detail-value"><?php echo $dateDisplay; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Waktu:</span>
                                <span class="detail-value"><?php echo $timeDisplay; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Laboratorium:</span>
                                <span class="detail-value">Lab Business Analytics</span>
                            </div>
                        </div>
                    </div>
                </div>
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
                hamburgerIcon.classList.remove('fa-bars');
                hamburgerIcon.classList.add('fa-times');
            } else {
                hamburgerIcon.classList.remove('fa-times');
                hamburgerIcon.classList.add('fa-bars');
            }
        }

        // --- 1. Cek Login Status ---
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

        function toggleMenu() {
    const navMenu = document.getElementById('navMenu');
    const icon = document.querySelector('.hamburger i');

    navMenu.classList.toggle('active');

    if (navMenu.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
}

        // JavaScript untuk Sticky Navbar
        document.addEventListener('DOMContentLoaded', function () {
            const allNavLinks = document.querySelectorAll('.nav-link');
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            function setActiveMenu(clickedElement) {
                allNavLinks.forEach(link => link.classList.remove('active'));
                if (clickedElement.classList.contains('dropdown-item')) {
                    const parentDropdown = clickedElement.closest('.dropdown');
                    const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                    dropdownToggle.classList.add('active');
                } else {
                    clickedElement.classList.add('active');
                }
            }

            allNavLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    if (!this.classList.contains('dropdown-toggle')) {
                        setActiveMenu(this);
                    }
                });
            });

            dropdownItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    setActiveMenu(this);
                });
            });

            // LOGIKA SUBMIT FORM KE API ADMIN
            const bookingForm = document.getElementById('bookingForm');
            
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Mencegah reload halaman

                // Validasi manual status (karena select default disabled value)
                const status = document.getElementById('status').value;
                if (!status) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Silakan pilih Status/Kategori Pemohon.',
                        confirmButtonColor: '#4361ee'
                    });
                    return;
                }

                const formData = new FormData(this);

                // Tampilkan Loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Fetch ke API Admin
                fetch('../admin/api/peminjaman.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Permohonan peminjaman berhasil dikirim. Silakan cek status di dashboard.',
                            confirmButtonColor: '#4361ee'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirect ke Dashboard User (jika ada) atau kembali ke beranda
                                window.location.href = '../admin/index.php'; 
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan saat menyimpan data.',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Koneksi',
                        text: 'Gagal menghubungi server.',
                        confirmButtonColor: '#d33'
                    });
                });
            });
        });
    </script>
</body>
</html>