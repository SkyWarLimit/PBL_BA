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
    <link rel="stylesheet" href="../assets/css/maknaLogoStyle.css">
</head>

<body>
    <!-- Sticky Navigation Bar -->
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
                <a class="nav-link" href="beranda.php">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="profile.php">Profil</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#">
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
        
        <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
    </nav>

    <!-- Konten Makna Logo -->
    <div class="content-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.html">Beranda</a>
            <span class="separator">/</span>
            <a href="profile.html">Profil</a>
            <span class="separator">/</span>
            <span class="active">Makna Logo</span>
        </div>
        
        <!-- Judul -->
        <h1 class="title">Makna Logo</h1>
        
        <!-- Paragraf Pertama -->
        <div class="content-text">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        </div>
        
        <!-- Logo di Tengah -->
        <div class="logo-center">
            <img src="img/logo.png" alt="Logo Laboratorium Business Analytics">
        </div>
        
        <!-- Paragraf Kedua -->
        <div class="content-text">
            But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete 
            account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. 
            No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. 
            Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. 
            To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, 
            or one who avoids a pain that produces no resultant pleasure?
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // JavaScript untuk Sticky Navbar
        document.addEventListener('DOMContentLoaded', function () {
            const allNavLinks = document.querySelectorAll('.nav-link');
            const dropdownItems = document.querySelectorAll('.dropdown-item');

            // Fungsi untuk set menu aktif
            function setActiveMenu(clickedElement) {
                // Remove active class dari semua menu
                allNavLinks.forEach(link => link.classList.remove('active'));

                // Add active class ke element yang diklik
                if (clickedElement.classList.contains('dropdown-item')) {
                    // Jika yang diklik adalah dropdown item, aktifkan parent dropdown
                    const parentDropdown = clickedElement.closest('.dropdown');
                    const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                    dropdownToggle.classList.add('active');
                } else {
                    // Jika yang diklik adalah menu biasa
                    clickedElement.classList.add('active');
                }
            }

            // Event listeners untuk menu biasa
            allNavLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Jangan set active untuk dropdown toggle (karena sudah hover)
                    if (!this.classList.contains('dropdown-toggle')) {
                        setActiveMenu(this);
                    }
                });
            });

            // Event listeners untuk dropdown items
            dropdownItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    setActiveMenu(this);
                });
            });

            // Untuk mobile devices, tetap gunakan Bootstrap dropdown
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function (e) {
                    if (window.innerWidth <= 992) {
                        e.preventDefault();
                        const dropdownMenu = this.nextElementSibling;
                        dropdownMenu.classList.toggle('show');
                    }
                });
            });

            // Tutup dropdown ketika klik di luar
            document.addEventListener('click', function (e) {
                if (window.innerWidth <= 992) {
                    if (!e.target.closest('.dropdown')) {
                        document.querySelectorAll('.dropdown-menu').forEach(menu => {
                            menu.classList.remove('show');
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>