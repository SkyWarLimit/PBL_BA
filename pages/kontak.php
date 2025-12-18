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
    <title>Laboratorium Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/kontakStyle.css?v=<?php echo time(); ?>">
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
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
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

            <li class="nav-item"><a class="nav-link active" href="kontak.php">Kontak</a></li>

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

    <section class="contact-hero">
        <div class="contact-hero-bg"></div>
        <div class="contact-hero-content">
            <h1>Contact Us</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>
    </section>

    <section class="contact-main-section">
        <div class="contact-container">
            <div class="contact-info-section">
                <div class="contact-header">
                    <h2>Contact</h2>
                    <h3>Get In Touch</h3>
                    <p>Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan atau ingin berkolaborasi.</p>
                </div>

                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Address</h4>
                            <p>Gedung Kuliah Bersama, Laboratorium Business Analytics, Kampus Utama.</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone</h4>
                            <a href="tel:+62812345678">(+62) 812345678</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <a href="mailto:admin@lab.com">admin@lab.com</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Instagram</h4>
                            <a href="https://instagram.com/lab_business_analytics" target="_blank">@lab_business_analytics</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-form-section">
                <div class="contact-form-header">
                    <h3>ready to get started?</h3>
                    <p>Kirimkan pesan Anda, kami akan membalas secepatnya.</p>
                </div>

                <form class="contact-form" id="contactForm">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Your Phone Number (Optional)">
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" placeholder="Your Message" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn" id="btnSubmit">Send Message</button>
                </form>
            </div>
        </div>
    </section>

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

        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            const btnSubmit = document.getElementById('btnSubmit');

            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // 1. Ubah tombol jadi loading
                const originalText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                btnSubmit.disabled = true;

                // 2. Ambil data form
                const formData = new FormData(contactForm);

                // 3. Kirim ke API (Sesuaikan path API jika perlu)
                // Asumsi: File ini ada di folder /pages/, maka mundur satu folder (../) lalu masuk ke admin/api/
                fetch('../admin/api/public_kontak.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terima Kasih!',
                            text: 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.',
                            confirmButtonColor: '#4361ee'
                        });
                        contactForm.reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan saat mengirim pesan.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan koneksi. Silakan coba lagi nanti.',
                    });
                })
                .finally(() => {
                    // 4. Kembalikan tombol seperti semula
                    btnSubmit.innerHTML = originalText;
                    btnSubmit.disabled = false;
                });
            });
        });
    </script>
</body>
</html>