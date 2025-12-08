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
    <link rel="stylesheet" href="../assets/css/kontakStyle.css">
</head>

<body>
    <!-- Sticky Navigation Bar -->
    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="Laboratorium Business Analytics Logo">
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
                <a class="nav-link" href="../pages/profile.php">Profil</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#">
                    Publikasi
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="../pages/berita.php">Berita</a></li>
                    <li><a class="dropdown-item" href="../pages/galeri.php">Gallery</a></li>
                    <li><a class="dropdown-item" href="../pages/newsInputService.php">News Input Service</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#">
                    Peminjaman Lab
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="../pages/infoPeminjaman.php">Informasi Laboratorium</a></li>
                    <li><a class="dropdown-item" href="../pages/tableBooking.php">Table Peminjaman</a></li>
                    <li><a class="dropdown-item" href="../pages/booking.php">Pemesanan Lab</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="../pages/kontak.php">Kontak</a>
            </li>
        </ul>

        <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
    </nav>

    <!-- Contact Us Section -->
    <section class="contact-hero">
        <div class="contact-hero-bg"></div>
        <div class="contact-hero-content">
            <h1>Contact Us</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        </div>
    </section>

    <!-- Main Contact Section -->
    <section class="contact-main-section">
        <div class="contact-container">
            <!-- Contact Info Section (Left) -->
            <div class="contact-info-section">
                <div class="contact-header">
                    <h2>Contact</h2>
                    <h3>Get In Touch</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                </div>

                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Address</h4>
                            <p>Getting Ideas Set U.S. J. Sodanno Nata No 3, Jatemiya, Koc Luwakwara, Kota Makina, Jana Trustt 65141</p>
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
                            <a href="mailto:email@gmail.com">email@gmail.com</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Instagram</h4>
                            <a href="https://instagram.com/bidau.fansemyu" target="_blank">@bidau.fansemyu</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form Section (Right) -->
            <div class="contact-form-section">
                <div class="contact-form-header">
                    <h3>ready to get started?</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>

                <form class="contact-form">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" class="form-control" placeholder="Your Name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="Your Email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" class="form-control" placeholder="Your Phone Number">
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" class="form-control" placeholder="Your Message"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Perbaikan untuk navigasi section
        document.addEventListener('DOMContentLoaded', function () {
            const sectionLinks = document.querySelectorAll('.section-nav-link');

            sectionLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Hapus kelas active dari semua link
                    sectionLinks.forEach(l => l.classList.remove('active'));

                    // Tambah kelas active ke link yang diklik
                    this.classList.add('active');

                    // Dapatkan target section
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    if (targetSection) {
                        // Hitung offset dengan mempertimbangkan tinggi navbar
                        const navbarHeight = document.querySelector('.sticky-navbar').offsetHeight;
                        const sectionNavHeight = document.querySelector('.section-navbar').offsetHeight;
                        const offsetTop = targetSection.offsetTop - navbarHeight - sectionNavHeight;

                        // Scroll ke section
                        window.scrollTo({
                            top: Math.max(0, offsetTop),
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Update active section saat scroll
            window.addEventListener('scroll', function () {
                const sections = document.querySelectorAll('section[id], div[id]');
                const scrollPos = window.scrollY + 200; // Offset untuk menentukan section aktif

                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;
                    const sectionId = section.getAttribute('id');

                    if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                        sectionLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === `#${sectionId}`) {
                                link.classList.add('active');
                            }
                        });
                    }
                });
            });
        });

        // Fallback untuk smooth scroll di browser lama
        if (!('scrollBehavior' in document.documentElement.style)) {
            document.querySelectorAll('.section-nav-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    if (targetSection) {
                        const navbar = document.querySelector('.sticky-navbar');
                        const sectionNav = document.querySelector('.section-navbar');
                        const offsetTop = targetSection.offsetTop -
                            (navbar ? navbar.offsetHeight : 0) -
                            (sectionNav ? sectionNav.offsetHeight : 0) - 10;

                        window.scrollTo(0, Math.max(0, offsetTop));
                    }
                });
            });
        }

        // Scroll Behavior untuk Navbar
        class NavbarScrollBehavior {
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                this.sectionNavbar = document.getElementById('sectionNavbar');
                this.lastScrollY = window.scrollY;
                this.scrollThreshold = 100; // Scroll berapa pixel sebelum navbar utama hilang
                this.isMainNavHidden = false;

                this.init();
            }

            init() {
                window.addEventListener('scroll', () => {
                    this.handleScroll();
                });

                // Initial check
                this.handleScroll();
            }

            handleScroll() {
                const currentScrollY = window.scrollY;
                const scrollDirection = currentScrollY > this.lastScrollY ? 'down' : 'up';

                // Jika scroll ke bawah melebihi threshold, sembunyikan navbar utama
                if (scrollDirection === 'down' && currentScrollY > this.scrollThreshold && !this.isMainNavHidden) {
                    this.hideMainNavbar();
                }
                // Jika scroll ke atas, tampilkan navbar utama
                else if (scrollDirection === 'up' && this.isMainNavHidden) {
                    this.showMainNavbar();
                }
                // Jika di paling atas, pastikan navbar utama visible
                else if (currentScrollY <= this.scrollThreshold && this.isMainNavHidden) {
                    this.showMainNavbar();
                }

                this.lastScrollY = currentScrollY;
            }

            hideMainNavbar() {
                this.mainNavbar.classList.add('hidden');
                this.sectionNavbar.classList.add('main-sticky');
                document.body.classList.add('nav-main-hidden');
                this.isMainNavHidden = true;
            }

            showMainNavbar() {
                this.mainNavbar.classList.remove('hidden');
                this.sectionNavbar.classList.remove('main-sticky');
                document.body.classList.remove('nav-main-hidden');
                this.isMainNavHidden = false;
            }
        }

        // Compact navbar saat scroll
        class CompactNavbar {
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                this.compactThreshold = 50;

                this.init();
            }

            init() {
                window.addEventListener('scroll', () => {
                    this.toggleCompact();
                });
            }

            toggleCompact() {
                if (window.scrollY > this.compactThreshold) {
                    this.mainNavbar.classList.add('compact');
                } else {
                    this.mainNavbar.classList.remove('compact');
                }
            }
        }

        // Initialize semua behavior ketika DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize scroll behavior
            new NavbarScrollBehavior();
            new CompactNavbar();

            // Initialize roadmap carousel jika ada
            const roadmapCards = document.querySelectorAll('.roadmap-card');
            if (roadmapCards.length > 0) {
                new RoadmapCarousel();
            }

            // Initialize section navigation
            new SectionNavigation();
        });

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new RoadmapCarousel();
            new SectionNavigation();
        });

        // Initialize section navigation when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new RoadmapCarousel();
            new SectionNavigation();
        });
    </script>
</body>

</html>