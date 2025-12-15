<?php
session_start();

// --- LOGIKA SESSION ---
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/formCancelBookStyle.css?v=<?php echo time(); ?>">
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
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
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
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
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
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=0D8ABC&color=fff&size=128"
                                alt="User Avatar">
                        </div>
                        <div class="mobile-info-modern">
                            <span class="greeting-text">Halo,</span>
                            <span class="username-text">
                                <?php echo htmlspecialchars($userName); ?>
                            </span>
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
                    <div class="user-name-label">
                        <?php echo htmlspecialchars($userName); ?>
                    </div>
                    <div class="user-role-label">
                        <?php echo htmlspecialchars($userRole); ?>
                    </div>
                </div>
                <div class="avatar-circle">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random&size=128"
                        alt="User Avatar">
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

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <a href="#" class="breadcrumb-link">Beranda</a>
            <span class="breadcrumb-separator">/</span>
            <a href="infoPeminjaman.html" class="breadcrumb-link">Informasi Peminjaman</a>
            <span class="breadcrumb-separator">/</span>
            <a href="booking.html" class="breadcrumb-link">Booking</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Form Pembatalan</span>
        </div>

        <div class="row">
            <!-- Left Column - Form Input -->
            <div class="col-md-7 order-2 order-md-1">
                <div class="form-container">
                    <h1 class="page-title">Form Pembatalan</h1>
                    <h2 class="section-title">Informasi Kontak :</h2>
                    
                    <form id="bookingForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Masukkan nama lengkap">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Masukkan alamat email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status/Kategori Pemohon</label>
                            <select class="form-select" id="status">
                                <option selected disabled>Pilih status</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nim-nip" class="form-label">NIM/NIP</label>
                            <input type="text" class="form-control" id="nim-nip" placeholder="Masukkan NIM/NIP/Instansi">
                        </div>
                        
                        <div class="mb-3">
                            <label for="program" class="form-label">Program Studi/Departemen</label>
                            <input type="text" class="form-control" id="program" placeholder="Masukkan program studi/departemen">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Handphone</label>
                            <input type="tel" class="form-control" id="phone" placeholder="Masukkan nomor handphone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Alasan Pembatalan Booking</label>
                            <textarea class="form-control" id="purpose" rows="3" placeholder="Jelaskan alasan pembatalan peminjaman laboratorium"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary confirm-btn">Konfirmasi</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right Column - Booking Summary -->
            <div class="col-md-5 order-1 order-md-2 mb-4">
                <div class="booking-summary">
                    <h3 class="summary-title">Booking Summary</h3>
                    <div class="summary-content">
                        <p class="summary-text">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                        </p>
                        
                        <div class="booking-details">
                            <div class="detail-item">
                                <span class="detail-label">Tanggal:</span>
                                <span class="detail-value">15 November 2023</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Waktu:</span>
                                <span class="detail-value">10:00 - 12:00 WIB</span>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        // --- 0. NAV MENU LOGIC (HAMBURGER) ---
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

    </script>
</body>

</html>