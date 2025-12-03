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
    <link rel="stylesheet" href="../assets/css/beritaUtamaStyle.css">
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
        
        <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
    </nav>

    <!-- Content Section -->
    <div class="content-container">
        <div class="row">
            <!-- Main Content (Left) -->
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="beranda.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="berita.php">Berita</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Berita Terkini</li>
                    </ol>
                </nav>
                
                <h1 class="news-title">Lorem Ipsum is simply dummy text of the printing and typesetting industry</h1>
                
                <div class="news-meta">
                    <span><i class="fas fa-user"></i> Username</span>
                    <span><i class="fas fa-calendar-alt"></i> 31 Januari 2012</span>
                    <span><i class="fa fa-id-badge"></i> Alumni Polinema</span>
                </div>
                
                <img src="./img/MissingPicturee.jpg" alt="Gambar Berita" class="news-image">
                
                <div class="news-content">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book. 
                        It has survived not only five centuries, but also the leap into electronic typesetting, 
                        remaining essentially unchanged.
                    </p>
                    
                    <p>It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, 
                        and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                    </p>
                    
                    <p>Contrary to popular belief, Lorem Ipsum is not simply random text. 
                        It has roots in a piece of classical Latin literature from 45 BC, 
                        making it over 2000 years old. Richard McClintock, 
                        a Latin professor at Hampden-Sydney College in Virginia, 
                        looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, 
                        and going through the cites of the word in classical literature, discovered the undoubtable source.
                    </p>
                    
                    <p>Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" 
                        (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, 
                        very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", 
                        comes from a line in section 1.10.32.
                    </p>
                </div>

                <img src="./img/MissingPicturee.jpg" alt="Gambar Berita" class="news-image">
                
                <div class="news-content">
                    <p>
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, 
                        totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. 
                        Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, 
                        sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. 
                        Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, 
                        sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. 
                        Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, 
                        nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, 
                        vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
                    </p>
                </div>
            </div>
            
            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Prestasi</h3>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 1" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Prestasi Mahasiswa dalam Kompetisi Data Science Nasional</a>
                            </h4>
                            <p class="news-sidebar-desc">Mahasiswa Laboratorium Business Analytics berhasil meraih juara pertama dalam kompetisi data science tingkat nasional.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 15 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 2" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Penghargaan untuk Inovasi dalam Analisis Bisnis</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics menerima penghargaan atas inovasi dalam pengembangan solusi analisis bisnis.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 10 Feb 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Prestasi 3" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Laboratorium Business Analytics Raih Akreditasi A</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics berhasil meraih akreditasi A dari lembaga akreditasi nasional.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 5 Jan 2023</span>
                                <span><i class="fas fa-tag"></i> Prestasi</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Announcement</h3>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 1" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Jadwal Baru Penggunaan Laboratorium Semester Genap</a>
                            </h4>
                            <p class="news-sidebar-desc">Pengumuman jadwal baru penggunaan laboratorium untuk semester genap tahun akademik 2022/2023.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 20 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 2" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Workshop Business Analytics untuk Mahasiswa Baru</a>
                            </h4>
                            <p class="news-sidebar-desc">Laboratorium Business Analytics akan menyelenggarakan workshop untuk mahasiswa baru angkatan 2022.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 12 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="news-item">
                        <img src="./img/MissingPicturee.jpg" alt="Announcement 3" class="news-thumbnail">
                        <div class="news-info">
                            <h4 class="news-sidebar-title">
                                <a href="#">Pendaftaran Program Magang di Industri Mitra</a>
                            </h4>
                            <p class="news-sidebar-desc">Pembukaan pendaftaran program magang di perusahaan mitra Laboratorium Business Analytics.</p>
                            <div class="news-sidebar-meta">
                                <span><i class="fas fa-user"></i> Admin</span>
                                <span><i class="fas fa-calendar-alt"></i> 8 Mar 2023</span>
                                <span><i class="fas fa-tag"></i> Pengumuman</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Category</h3>
                    <ul class="category-list">
                        <li><a href="#"><i class="fas fa-images"></i> Gallery</a></li>
                        <li><a href="#"><i class="fas fa-cogs"></i> Content Management System</a></li>
                    </ul>
                </div>
            </div>
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

        // Fungsi untuk set menu aktif berdasarkan URL halaman saat ini
        function setActiveMenuBasedOnCurrentPage() {
            const currentPath = window.location.pathname;
            const currentPage = currentPath.split('/').pop();
            
            // Reset semua menu
            allNavLinks.forEach(link => link.classList.remove('active'));
            
            // Set menu aktif berdasarkan halaman saat ini
            switch(currentPage) {
                case 'index.php':
                case '':
                    // Untuk halaman beranda
                    document.querySelector('a[href="../index.php"]').classList.add('active');
                    break;
                case 'profile.php':
                    document.querySelector('a[href="profile.php"]').classList.add('active');
                    break;
                case 'berita.php':
                case 'beritaUtama.php':
                    // Aktifkan menu Publikasi dan submenu Berita jika di halaman berita
                    const publikasiMenu = document.querySelector('a.nav-link[href="#"]');
                    if (publikasiMenu && publikasiMenu.textContent.includes('Publikasi')) {
                        publikasiMenu.classList.add('active');
                    }
                    break;
                case 'galeri.php':
                    const galeriDropdown = document.querySelector('.dropdown-item[href="galeri.php"]');
                    if (galeriDropdown) {
                        const parentDropdown = galeriDropdown.closest('.dropdown');
                        const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                        dropdownToggle.classList.add('active');
                    }
                    break;
                case 'infoPeminjaman.php':
                case 'tableBooking.php':
                case 'booking.php':
                    // Aktifkan menu Peminjaman Lab
                    const peminjamanMenus = document.querySelectorAll('.nav-link');
                    peminjamanMenus.forEach(menu => {
                        if (menu.textContent.includes('Peminjaman Lab')) {
                            menu.classList.add('active');
                        }
                    });
                    break;
                case 'kontak.php':
                    document.querySelector('a[href="kontak.php"]').classList.add('active');
                    break;
            }
        }

        // Fungsi untuk menangani klik pada menu
        function handleMenuClick(e) {
            const clickedElement = e.currentTarget;
            
            // Jika yang diklik adalah link dengan href yang valid (bukan "#")
            if (clickedElement.getAttribute('href') && clickedElement.getAttribute('href') !== '#') {
                // Biarkan browser menavigasi ke halaman yang dituju
                // Tidak perlu e.preventDefault()
                
                // Set menu aktif
                setActiveMenu(clickedElement);
            } else if (clickedElement.classList.contains('dropdown-toggle')) {
                // Untuk dropdown toggle di desktop, biarkan Bootstrap menangani
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    const dropdownMenu = clickedElement.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                }
            }
        }

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
            link.addEventListener('click', handleMenuClick);
        });

        // Event listeners untuk dropdown items
        dropdownItems.forEach(item => {
            item.addEventListener('click', function (e) {
                // Biarkan browser menavigasi ke halaman dropdown
                // Tidak perlu e.preventDefault()
                setActiveMenu(this);
            });
        });

        // Set menu aktif berdasarkan halaman saat ini saat load
        setActiveMenuBasedOnCurrentPage();

        // Tutup dropdown ketika klik di luar (hanya untuk mobile)
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            }
        });

        // Handle login button click
        const loginBtn = document.querySelector('.login-btn');
        if (loginBtn) {
            loginBtn.addEventListener('click', function() {
                // Arahkan ke halaman login (sesuaikan dengan path yang benar)
                window.location.href = 'login.php';
            });
        }
    });
</script>
</body>

</html>