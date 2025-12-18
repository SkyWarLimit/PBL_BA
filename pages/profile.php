<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
// Role default jika tidak ada session
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';

// --- TAMBAHKAN FUNGSI INI ---
function potongTeks($teks, $jumlahKata = 20) {
    $words = explode(" ", strip_tags($teks)); // Pecah jadi array kata & hapus tag HTML
    if (count($words) > $jumlahKata) {
        return implode(" ", array_slice($words, 0, $jumlahKata)) . '...';
    }
    return $teks;
}

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

// --- 2. AMBIL DATA SETTING (Logo & Maskot) ---
$logoSrc = '../assets/images/logo.png';
$maskotSrc = '../assets/img/MaskotLab.png';
$maknaLogoText = 'Deskripsi makna logo belum diatur oleh admin.';
$maknaMaskotText = 'Deskripsi makna maskot belum diatur oleh admin.';
$namaLabText = 'Laboratorium Business Analytics'; // Default jika db kosong

try {
    // Ambil data berdasarkan key 'logo' dan 'maskot'
    // Kita ambil 'value' (untuk deskripsi) dan 'file_path' (untuk gambar)
    $sql = "SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'maskot', 'nama_lab')";

    $stmt = $db->query($sql);
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi ke array asosiatif
    $settings = [];
    foreach ($settingsRaw as $row) {
        $settings[$row['key']] = $row;
    }

    // --- LOGIKA PERBAIKAN DI SINI ---

    // 1. SET LOGO (Gambar & Deskripsi)
    if (isset($settings['logo'])) {
        // Ambil Gambar
        if (!empty($settings['logo']['file_path'])) {
            $logoSrc = '../admin/' . $settings['logo']['file_path'];
        }
        // Ambil Deskripsi (dari kolom value milik key 'logo')
        if (!empty($settings['logo']['value'])) {
            $maknaLogoText = $settings['logo']['value'];
        }
    }

    // 2. SET MASKOT (Gambar & Deskripsi)
    if (isset($settings['maskot'])) {
        // Ambil Gambar
        if (!empty($settings['maskot']['file_path'])) {
            $maskotSrc = '../admin/' . $settings['maskot']['file_path'];
        }
        // Ambil Deskripsi (dari kolom value milik key 'maskot')
        if (!empty($settings['maskot']['value'])) {
            $maknaMaskotText = $settings['maskot']['value'];
        }
    }

    if (!empty($settings['nama_lab']['value'])) {
        $namaLabText = $settings['nama_lab']['value'];
    }
} catch (Exception $e) {
    // Silent fail
}

// --- 3. AMBIL DATA DOSEN / ANGGOTA (QUERY POSTGRESQL) ---
$dosenList = [];
try {
    // Query ini menggabungkan tabel dosen, user, dan anggota
    $sql = "SELECT 
                d.id_dosen,
                u.nama as nama_lengkap,
                d.nidn,
                d.foto,
                
                -- Ambil Keahlian (Gabungan dari tabel anak)
                (
                    SELECT STRING_AGG(nama_bidang, ', ') 
                    FROM bidang_keahlian bk 
                    WHERE bk.id_dosen = d.id_dosen
                ) as keahlian_list,
                
                -- Fallback ke kolom tabel induk
                d.bidang_keahlian as keahlian_single,
                
                -- Ambil Link Scholar
                (SELECT link_url FROM link_akademik_dosen WHERE id_dosen = d.id_dosen AND platform ILIKE '%scholar%' LIMIT 1) as link_scholar,
                
                -- Ambil Link Sinta
                (SELECT link_url FROM link_akademik_dosen WHERE id_dosen = d.id_dosen AND platform ILIKE '%sinta%' LIMIT 1) as link_sinta

            FROM dosen d
            JOIN users u ON d.id_user = u.id_user
            JOIN anggota a ON d.id_dosen = a.id_dosen
            WHERE a.is_active = true
            
            -- BAGIAN INI YANG DIUBAH:
            ORDER BY d.id_dosen ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $dosenList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching dosen: " . $e->getMessage());
}

try {
    // Tambahkan 'visi' dan 'misi' ke dalam query IN clause
    $stmt = $db->query("SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'maskot', 'visi', 'misi')");
    $settingsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert ke array asosiatif biar gampang dipanggil
    $settings = [];
    foreach ($settingsRaw as $row) {
        $settings[$row['key']] = $row;
    }

    if (!empty($settings['logo']['file_path'])) $logoSrc = '../admin/' . $settings['logo']['file_path'];
    if (!empty($settings['maskot']['file_path'])) $maskotSrc = '../admin/' . $settings['maskot']['file_path'];

    // Ambil value visi misi
    if (!empty($settings['visi']['value'])) $visiText = $settings['visi']['value'];
    if (!empty($settings['misi']['value'])) $misiText = $settings['misi']['value'];
} catch (Exception $e) { /* Ignore */
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/profileStyle.css?v=<?php echo time(); ?>">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;1,700;1,800&display=swap"
        rel="stylesheet">
</head>

<body>

    <nav class="sticky-navbar" id="mainNavbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Business Analytics Logo">
            </div>
            <div class="lab-name-container">
                <div class="lab-name">
                    <?php echo htmlspecialchars($namaLabText); ?>
                </div>
                <div class="lab-tagline">Transforming Data into Decisions</div>
            </div>
        </div>

        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-menu" id="navMenu">
            <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
            <li class="nav-item"><a class="nav-link active" href="profile.php">Profil</a></li>

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

                <a class="user-profile-link">
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-rectangle">
            <!-- Background rectangle dengan opacity -->
        </div>
        <div class="hero-container">
            <div class="hero-inner-rectangle">
                <!-- Inner rectangle placeholder -->
            </div>
            <div class="hero-text-content">
                <div class="about-overlay">
                    <span class="about-text">About</span>
                </div>
                <div class="title-content">
                    <h1 class="hero-main-title">
                        <span class="title-line">The Laboratory</span>
                        <span class="title-line business-analytics">Business Analytics</span>
                    </h1>
                    <p class="hero-detailed-description">
                        Laboratorium Business Analytics didirikan untuk menjembatani kesenjangan antara kurikulum akademik 
                        dengan kebutuhan industri analitik modern. Laboratorium ini menyediakan lingkungan uji yang stabil, 
                        repositori data terkurasi, dan prosedur operasional berstandar profesional. Fokus kami adalah memastikan 
                        setiap tahapan analitik—mulai dari akuisisi data hingga visualisasi strategis—dijalankan dengan 
                        integritas dan metodologi yang tepat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sticky Section Navigation -->
    <nav class="section-navbar" id="sectionNavbar">
        <div class="section-nav-rectangle">
            <div class="section-nav-container">
                <ul class="section-nav-menu">
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#visi-misi">
                            <span class="nav-text">Visi & Misi</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#roadmap">
                            <span class="nav-text">Road Map</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#struktur-organisasi">
                            <span class="nav-text">Struktur Organisasi</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#anggota-laboratory">
                            <span class="nav-text">Anggota Laboratory</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#makna-logo-maskot">
                            <span class="nav-text">Makna Logo & Maskot</span>
                        </a>
                    </li>
                    <li class="section-nav-item">
                        <a class="section-nav-link" href="#research-focus">
                            <span class="nav-text">Research Focus</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-2">

        <!-- Visi & Misi Section -->
        <div class="row mb-5" id="visi-misi">
            <div class="col-12">
                <h2 class="section-title mb-3">Visi & Misi</h2>
                <p class="section-description mb-5">
                    Visi dan misi kami merupakan kompas strategis yang memandu seluruh aktivitas laboratorium. 
                    Kami berkomitmen untuk menjadi garda terdepan dalam inovasi analitik yang memberikan nilai tambah nyata bagi 
                    dunia pendidikan, industri, dan masyarakat luas.
                </p>
                <div class="vision-mission-card mb-4">
                    <h3 class="vision-mission-title">Visi</h3>
                    <p class="vision-mission-text">
                        <?php echo nl2br(htmlspecialchars($visiText)); ?>
                    </p>
                </div>

                <div class="vision-mission-card">
                    <h3 class="vision-mission-title">Misi</h3>
                    <div class="vision-mission-list-container">
                        <?php
                        // Cek apakah isi misi mengandung tag HTML list
                        if (strpos($misiText, '<li>') !== false) {
                            // Jika user input pake HTML (misal dari summernote/text editor)
                            echo $misiText;
                        } else {
                            // Jika input teks biasa (misal dipisah enter), kita buat list manual
                            echo '<ol class="vision-mission-list">';
                            $misiLines = explode("\n", $misiText);
                            foreach ($misiLines as $line) {
                                if (trim($line)) {
                                    echo '<li>' . htmlspecialchars($line) . '</li>';
                                }
                            }
                            echo '</ol>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-5"></div>

        <!-- Roadmap Section -->
        <div class="row align-items-center gx-lg-5" id="roadmap">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <h2 class="roadmap-title mb-3">Road Map<br>Laboratory Business Analytics</h2>
                <p class="section-description">
                    Peta jalan Laboratorium Business Analytics dirancang secara sistematis untuk memastikan keberlanjutan dan 
                    pertumbuhan yang terukur dalam jangka panjang. Rencana ini mencakup berbagai aspek mulai dari penguatan kualitas 
                    lulusan melalui kurikulum yang relevan, pengembangan riset terapan yang mendalam, hingga perluasan jejaring kemitraan 
                    strategis. Setiap fase dalam roadmap ini merupakan batu loncatan untuk mewujudkan visi laboratorium sebagai pusat 
                    keunggulan analitik yang berdampak signifikan di tingkat regional maupun internasional.
                </p>
            </div>

            <div class="col-lg-7">
                <div class="roadmap-container">

                    <div class="roadmap-track" id="roadmapTrack">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="roadmap-indicators" id="roadmapIndicators">
                    </div>

                </div>
            </div>
        </div>

        <div class="py-4"></div>

        <!-- SECTION ANGGOTA LABORATORY (DYNAMIC) -->
        <div class="org-structure py-5" id="struktur-organisasi">
            <h2 class="section-title mb-3">Struktur Organisasi</h2>
            <p class="section-description mb-5">
                Pengelolaan laboratorium didukung oleh struktur organisasi yang solid dan profesional, memastikan setiap fungsi 
                mulai dari operasional, riset, hingga kemitraan berjalan dengan optimal sesuai dengan tata kelola yang transparan.
            </p>

            <div class="tree-container">
                <div class="tree">
                    <ul>
                        <li>
                            <div class="org-node main-node">
                                <span>Kepala Laboratory</span>
                            </div>
                            <ul>
                                <li class="support-row">
                                    <div class="org-node child-node">
                                        <span>Sekretaris</span>
                                    </div>
                                    <div class="org-node child-node">
                                        <span>Bendahara</span>
                                    </div>
                                </li>

                                <li class="coordinator-row">
                                    <div class="coordinator-container">
                                        <div class="org-node child-node">
                                            <span>Koor. Pengembangan Kelimuan</span>
                                        </div>
                                        <div class="org-node child-node">
                                            <span>Koor. Riset & PkM</span>
                                        </div>
                                        <div class="org-node child-node">
                                            <span>Koor. Kemitraan</span>
                                        </div>
                                        <div class="org-node child-node">
                                            <span>Koor. Sarana & Prasarana</span>
                                        </div>
                                        <div class="org-node child-node">
                                            <span>Koor. Publikasi</span>
                                        </div>
                                        <div class="org-node child-node">
                                            <span>Koor. Pengelolaan Tugas Akhir</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="researcher-section py-4" id="anggota-laboratory">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="section-title">Anggota Laboratory</h2>
                        <p class="section-description">
                            Laboratorium ini didukung oleh para ahli dan akademisi yang memiliki dedikasi tinggi serta keahlian 
                            mendalam di berbagai bidang analitik bisnis, pengolahan bahasa alami, hingga sistem cerdas.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">

                        <div class="table-responsive shadow-sm rounded-3">
                            <table class="table table-hover custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" class="ps-4">Researcher Name</th>
                                        <th scope="col">Role / Position</th>
                                        <th scope="col" class="text-end pe-4">Profile Links</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dosenList)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4">Belum ada data dosen.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dosenList as $dosen): ?>
                                            <?php
                                            // Tentukan bidang keahlian (prioritas dari tabel relasi, fallback ke kolom dosen)
                                            $keahlian = !empty($dosen['keahlian_list']) ? $dosen['keahlian_list'] : $dosen['keahlian_single'];

                                            // Jika masih kosong atau hanya angka (foreign key mentah), tampilkan dash
                                            if (empty($keahlian) || is_numeric($keahlian)) {
                                                $keahlian = '-';
                                            }

                                            // Logika Foto
                                            $fotoPath = !empty($dosen['foto'])
                                                ? '../admin/' . htmlspecialchars($dosen['foto']) // Asumsi path relatif sama dengan logo
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($dosen['nama_lengkap']) . '&background=eef2f7&color=1f3a60&bold=true';
                                            ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $fotoPath; ?>"
                                                            alt="<?php echo htmlspecialchars($dosen['nama_lengkap']); ?>"
                                                            class="table-avatar me-3"
                                                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($dosen['nama_lengkap']); ?>&background=eef2f7&color=1f3a60&bold=true';">
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></h6>
                                                            <small class="text-muted">NIDN: <?php echo htmlspecialchars($dosen['nidn'] ?? '-'); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-primary fw-semibold">
                                                    <?php echo htmlspecialchars($keahlian); ?>
                                                </td>
                                                <td class="align-middle text-end pe-4">
                                                    <?php if (!empty($dosen['link_scholar'])): ?>
                                                        <a href="<?php echo htmlspecialchars($dosen['link_scholar']); ?>" target="_blank" class="btn-link-custom">Scholar</a>
                                                    <?php else: ?>
                                                        <span class="btn-link-custom disabled" style="opacity: 0.5; cursor: default;">Scholar</span>
                                                    <?php endif; ?>

                                                    <?php if (!empty($dosen['link_sinta'])): ?>
                                                        <a href="<?php echo htmlspecialchars($dosen['link_sinta']); ?>" target="_blank" class="btn-link-custom">Sinta</a>
                                                    <?php else: ?>
                                                        <span class="btn-link-custom disabled" style="opacity: 0.5; cursor: default;">Sinta</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mobile-researcher-grid">
                            <?php if (empty($dosenList)): ?>
                                <div class="col-12 text-center">Belum ada data.</div>
                            <?php else: ?>
                                <?php foreach ($dosenList as $dosen): ?>
                                    <?php
                                    $keahlian = !empty($dosen['keahlian_list']) ? $dosen['keahlian_list'] : $dosen['keahlian_single'];
                                    if (empty($keahlian) || is_numeric($keahlian)) {
                                        $keahlian = '-';
                                    }

                                    $fotoPath = !empty($dosen['foto'])
                                        ? '../admin/' . htmlspecialchars($dosen['foto'])
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($dosen['nama_lengkap']) . '&background=eef2f7&color=1f3a60&bold=true';
                                    ?>
                                    <div class="res-card">
                                        <div class="res-card-img-wrapper">
                                            <img src="<?php echo $fotoPath; ?>" class="res-card-img"
                                                alt="<?php echo htmlspecialchars($dosen['nama_lengkap']); ?>"
                                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($dosen['nama_lengkap']); ?>&background=eef2f7&color=1f3a60&bold=true';">
                                        </div>
                                        <h3 class="res-card-name"><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></h3>
                                        <span class="res-card-nidn">NIDN: <?php echo htmlspecialchars($dosen['nidn'] ?? '-'); ?></span>
                                        <p class="res-card-role"><?php echo htmlspecialchars($keahlian); ?></p>
                                        <div class="res-card-links">
                                            <?php if (!empty($dosen['link_scholar'])): ?>
                                                <a href="<?php echo htmlspecialchars($dosen['link_scholar']); ?>" target="_blank" class="btn-link-custom">Scholar</a>
                                            <?php else: ?>
                                                <span class="btn-link-custom disabled" style="opacity: 0.5;">Scholar</span>
                                            <?php endif; ?>

                                            <?php if (!empty($dosen['link_sinta'])): ?>
                                                <a href="<?php echo htmlspecialchars($dosen['link_sinta']); ?>" target="_blank" class="btn-link-custom">Sinta</a>
                                            <?php else: ?>
                                                <span class="btn-link-custom disabled" style="opacity: 0.5;">Sinta</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="mobile-pagination mt-4" id="mobilePagination">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="py-5"></div>

    <!-- Makna Logo & Maskot Section -->
    <div class="container py-2">
        <section class="researcher-section py-4" id="makna-logo-maskot">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="section-title mb-3">Makna Logo & Maskot</h2>
                        <p class="section-description">
                            Berikut adalah filosofi di balik identitas visual Laboratorium Business Analytics.
                            Logo dan maskot kami merepresentasikan nilai-nilai inti dan visi kami dalam dunia analitik data.
                        </p>
                    </div>
                </div>

                <div class="row align-items-stretch">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="logo-section h-100">
                            <div class="symbol-preview-large mb-4 text-center">
                                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Logo" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                            </div>
                            <h3 class="symbol-title mb-3">Makna Logo</h3>
                            <div class="symbol-content mb-4">
                                <div class="symbol-description">
                                    <?php echo nl2br(htmlspecialchars(potongTeks($maknaLogoText, 25))); ?>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <a href="maknaLogo.php" class="btn-read-more">Read More</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mascot-section h-100">
                            <div class="symbol-preview-large mb-4 text-center">
                                <img src="<?php echo htmlspecialchars($maskotSrc); ?>" alt="Maskot" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                            </div>
                            <h3 class="symbol-title mb-3">Makna Maskot</h3>
                            <div class="symbol-content mb-4">
                                <div class="symbol-description">
                                    <?php echo nl2br(htmlspecialchars(potongTeks($maknaMaskotText, 25))); ?>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <a href="maknaMaskot.php" class="btn-read-more">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="py-5"></div>

    <!-- research focus -->
    <div class="container py-2">
        <section class="researcher-section py-4" id="research-focus">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="section-title mb-3">Research Focus</h2>
                        <p class="section-description">
                            Berikut adalah fokus penelitian utama di Laboratorium Business Analytics,
                            mencakup pengembangan platform data hingga penerapan machine learning.
                        </p>
                    </div>
                </div>

                <section class="profile-carousel-section">
                    <div class="custom-container-relative">
                        <div class="gray-backdrop-box">
                            <img id="backdrop-image"
                                src="assets/img/default-research.jpg"
                                alt="Research Background"
                                class="backdrop-img-content"
                                onerror="this.src='https://via.placeholder.com/800x600?text=No+Image'">
                        </div>

                        <div class="carousel-wrapper">
                            <div class="carousel-track" id="track">
                                <div class="text-center w-100 mt-5 text-white">
                                    <div class="spinner-border" role="status"></div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-nav">
                            <button class="nav-btn prev-btn" id="prevBtn">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <button class="nav-btn next-btn" id="nextBtn">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ========================================= */
        /* 1. ROADMAP CAROUSEL CLASS                 */
        /* ========================================= */
        class RoadmapCarousel {
            constructor() {
                this.cards = document.querySelectorAll('.roadmap-card');
                this.indicators = document.querySelectorAll('.indicator');
                this.track = document.querySelector('.roadmap-track');
                this.currentIndex = 0;
                this.totalCards = this.cards.length;
                this.autoSlideInterval = null;
                this.slideDuration = 3000;

                // Cek elemen ada sebelum jalan
                if (this.cards.length > 0 && this.track) {
                    this.init();
                }
            }

            init() {
                this.updateCardPositions();
                this.startAutoSlide();

                this.indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => {
                        this.goToSlide(index);
                    });
                });

                if (this.track) {
                    this.track.addEventListener('mouseenter', () => this.stopAutoSlide());
                    this.track.addEventListener('mouseleave', () => this.startAutoSlide());
                }
            }

            updateCardPositions() {
                this.cards.forEach((card, index) => {
                    card.classList.remove('active', 'prev', 'next');
                    if (index === this.currentIndex) {
                        card.classList.add('active');
                    } else if (index === (this.currentIndex + 1) % this.totalCards) {
                        card.classList.add('next');
                    } else if (index === (this.currentIndex - 1 + this.totalCards) % this.totalCards) {
                        card.classList.add('prev');
                    }
                });

                this.indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === this.currentIndex);
                });
            }

            nextSlide() {
                this.currentIndex = (this.currentIndex + 1) % this.totalCards;
                this.updateCardPositions();
            }

            goToSlide(index) {
                this.currentIndex = index;
                this.updateCardPositions();
                this.restartAutoSlide();
            }

            startAutoSlide() {
                this.stopAutoSlide();
                this.autoSlideInterval = setInterval(() => this.nextSlide(), this.slideDuration);
            }

            stopAutoSlide() {
                if (this.autoSlideInterval) {
                    clearInterval(this.autoSlideInterval);
                    this.autoSlideInterval = null;
                }
            }

            restartAutoSlide() {
                this.stopAutoSlide();
                this.startAutoSlide();
            }
        }

        /* ========================================= */
        /* 2. RESEARCH FOCUS CAROUSEL CLASS          */
        /* ========================================= */
        class ResearchFocusCarousel {
            constructor() {
                this.track = document.getElementById('track');
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                this.backdropImage = document.getElementById('backdrop-image');
                this.isAnimating = false;

                // Pastikan track ada dan memiliki kartu di dalamnya
                if (this.track && this.track.querySelectorAll('.custom-card').length > 0) {
                    this.init();
                }
            }

            init() {
                // Hapus event listener lama (cloning node) agar tidak double klik
                const newNext = this.nextBtn.cloneNode(true);
                const newPrev = this.prevBtn.cloneNode(true);
                this.nextBtn.parentNode.replaceChild(newNext, this.nextBtn);
                this.prevBtn.parentNode.replaceChild(newPrev, this.prevBtn);
                this.nextBtn = newNext;
                this.prevBtn = newPrev;

                this.nextBtn.addEventListener('click', () => this.moveNext());
                this.prevBtn.addEventListener('click', () => this.movePrev());

                this.updateActiveState();
            }

            getCards() {
                return this.track.querySelectorAll('.custom-card');
            }

            updateActiveState() {
                const cards = this.getCards();
                cards.forEach(c => c.classList.remove('active'));

                // Kartu pertama di DOM adalah yang aktif
                const activeCard = cards[0];
                if (activeCard) {
                    activeCard.classList.add('active');

                    // Ganti Background sesuai data-bg-img kartu aktif
                    const newImageSrc = activeCard.getAttribute('data-bg-img');
                    if (this.backdropImage && newImageSrc) {
                        this.backdropImage.classList.add('fade-out');
                        setTimeout(() => {
                            this.backdropImage.src = newImageSrc;
                            this.backdropImage.classList.remove('fade-out');
                        }, 300);
                    }
                }
            }

            moveNext() {
                if (this.isAnimating) return;
                this.isAnimating = true;

                const cards = this.getCards();
                if (cards.length === 0) return;

                const cardWidth = cards[0].offsetWidth;
                const gap = 24; // Sesuaikan dengan CSS gap: 1.5rem (24px)
                const moveDistance = cardWidth + gap;

                this.track.style.transition = 'transform 0.5s ease-in-out';
                this.track.style.transform = `translateX(-${moveDistance}px)`;

                setTimeout(() => {
                    this.track.style.transition = 'none';
                    this.track.appendChild(cards[0]); // Pindah kartu pertama ke belakang
                    this.track.style.transform = 'translateX(0)';
                    this.updateActiveState();
                    this.isAnimating = false;
                }, 500);
            }

            movePrev() {
                if (this.isAnimating) return;
                this.isAnimating = true;

                const cards = this.getCards();
                if (cards.length === 0) return;

                const lastCard = cards[cards.length - 1];
                const cardWidth = cards[0].offsetWidth;
                const gap = 24;
                const moveDistance = cardWidth + gap;

                this.track.style.transition = 'none';
                this.track.prepend(lastCard); // Pindah kartu terakhir ke depan
                this.track.style.transform = `translateX(-${moveDistance}px)`;

                // Force Reflow
                void this.track.offsetWidth;

                this.track.style.transition = 'transform 0.5s ease-in-out';
                this.track.style.transform = 'translateX(0)';

                setTimeout(() => {
                    this.updateActiveState();
                    this.isAnimating = false;
                }, 500);
            }
        }

        /* ========================================= */
        /* 3. INISIALISASI & FETCH DATA (GABUNGAN)   */
        /* ========================================= */

        document.addEventListener('DOMContentLoaded', function() {
            loadRoadmapData();
            loadResearchData();
        });

        // --- A. LOAD RESEARCH FOCUS ---
        function loadResearchData() {
            const apiUrl = '../admin/api/research_focus.php';
            const track = document.getElementById('track');

            fetch(apiUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Jaringan bermasalah / File tidak ditemukan');
                    return res.json();
                })
                .then(response => {
                    if (response.success) {
                        renderResearch(response.data);
                    } else {
                        console.error("API Research Error:", response.message);
                        track.innerHTML = '<div class="text-white text-center py-5">Gagal memuat data research.</div>';
                    }
                })
                .catch(err => {
                    console.error("Fetch Research Error:", err);
                    if (track) track.innerHTML = '<div class="text-white text-center py-5">Koneksi Error: ' + err.message + '</div>';
                });
        }

        function renderResearch(data) {
            const track = document.getElementById('track');
            track.innerHTML = ''; // Bersihkan spinner loading

            if (data.length === 0) {
                track.innerHTML = '<div class="text-white py-5">Belum ada data research.</div>';
                return;
            }

            data.forEach(item => {
                // Path Gambar: Tambahkan 'admin/' di depan karena file index ada di root
                // Fallback ke gambar default jika file_path kosong
                const imgSrc = item.file_path ? `../admin/${item.file_path}` : 'assets/img/default.jpg';

                const cardHtml = `
                <div class="custom-card" data-bg-img="${imgSrc}">
                    <div class="card-content">
                        <h3>${item.judul}</h3>
                        <p>${nl2br(item.deskripsi)}</p>
                    </div>
                </div>
            `;
                track.innerHTML += cardHtml;
            });

            // Set Background Awal (item pertama)
            if (data.length > 0) {
                const firstImg = data[0].file_path ? `../admin/${data[0].file_path}` : 'assets/img/default.jpg';
                const backdrop = document.getElementById('backdrop-image');
                if (backdrop) backdrop.src = firstImg;
            }

            // Jalankan Carousel Research setelah data masuk
            new ResearchFocusCarousel();
        }

        // --- B. LOAD ROADMAP ---
        function loadRoadmapData() {
            const apiUrl = 'admin/api/roadmap.php';
            fetch(apiUrl)
                .then(res => res.json())
                .then(res => {
                    if (res.success) renderRoadmap(res.data);
                    else document.getElementById('roadmapTrack').innerHTML = `<p class="text-center text-muted">${res.message}</p>`;
                })
                .catch(err => console.error('Fetch Roadmap Error:', err));
        }

        function renderRoadmap(data) {
            const track = document.getElementById('roadmapTrack');
            const indicators = document.getElementById('roadmapIndicators');

            if (!track || !indicators) return;

            track.innerHTML = '';
            indicators.innerHTML = '';

            if (data.length === 0) {
                track.innerHTML = '<p class="text-center text-muted">Belum ada data roadmap.</p>';
                return;
            }

            const periods = ['short', 'medium', 'long'];
            data.forEach((item, index) => {
                const periodStyle = periods[index % periods.length];
                const activeClass = index === 0 ? 'active' : '';

                track.innerHTML += `
                <div class="roadmap-card main-card ${activeClass}" data-period="${periodStyle}">
                    <div class="roadmap-header">
                        <span class="dot-indicator"></span>
                        <h4 class="term-title">${item.judul} <small class="text-muted ms-1" style="font-size: 0.8em;">(${item.tahun})</small></h4>
                    </div>
                    <hr class="roadmap-divider">
                    <div class="roadmap-content">
                        <div class="mb-0 roadmap-desc">${nl2br(item.deskripsi)}</div>
                    </div>
                </div>`;

                indicators.innerHTML += `<button class="indicator ${activeClass}" data-period="${periodStyle}"></button>`;
            });

            // Jalankan Carousel Roadmap setelah data masuk
            new RoadmapCarousel();
        }

        // Helper: Ubah enter (\n) jadi <br>
        function nl2br(str) {
            return str ? str.replace(/(?:\r\n|\r|\n)/g, '<br>') : '';
        }
        /* ========================================= */
        /* 3. INISIALISASI & FETCH DATA              */
        /* ========================================= */

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Jalankan Research Focus (Slider Bawah)
            new ResearchFocusCarousel();

            // 2. Jalankan Roadmap (Ambil Data Dulu)
            loadRoadmapData();
        });

        function loadRoadmapData() {
            // Ganti path ini jika perlu
            const apiUrl = '../admin/api/roadmap.php';

            console.log("Memulai fetch data dari:", apiUrl);

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(res => {
                    if (res.success) {
                        renderRoadmap(res.data);
                    } else {
                        console.error('API Error:', res.message);
                        document.getElementById('roadmapTrack').innerHTML = `<p class="text-center text-muted">Gagal: ${res.message}</p>`;
                    }
                })
                .catch(err => {
                    console.error('Fetch Error:', err);
                    const track = document.getElementById('roadmapTrack');
                    if (track) {
                        track.innerHTML = `<div class="alert alert-danger text-center">Gagal memuat data.<br><small>${err}</small></div>`;
                    }
                });
        }

        function renderRoadmap(data) {
            const track = document.getElementById('roadmapTrack');
            const indicatorsContainer = document.getElementById('roadmapIndicators');

            // HAPUS LOADING SPINNER
            track.innerHTML = '';
            indicatorsContainer.innerHTML = '';

            if (data.length === 0) {
                track.innerHTML = '<p class="text-center text-muted">Belum ada data roadmap.</p>';
                return;
            }

            const periods = ['short', 'medium', 'long'];

            data.forEach((item, index) => {
                const periodStyle = periods[index % periods.length];
                const activeClass = index === 0 ? 'active' : '';

                // Render Kartu
                const cardHtml = `
                <div class="roadmap-card main-card ${activeClass}" data-period="${periodStyle}">
                    <div class="roadmap-header">
                        <span class="dot-indicator"></span>
                        <h4 class="term-title">${item.judul} <small class="text-muted ms-1" style="font-size: 0.8em;">(${item.tahun})</small></h4>
                    </div>
                    <hr class="roadmap-divider">
                    <div class="roadmap-content">
                        <div class="mb-0 roadmap-desc">
                            ${nl2br(item.deskripsi)} 
                        </div>
                    </div>
                </div>
            `;
                track.innerHTML += cardHtml;

                // Render Indikator
                const indicatorHtml = `<button class="indicator ${activeClass}" data-period="${periodStyle}"></button>`;
                indicatorsContainer.innerHTML += indicatorHtml;
            });

            // Jalankan Carousel Roadmap setelah data masuk
            try {
                new RoadmapCarousel();
            } catch (e) {
                console.error("Gagal menjalankan Roadmap Carousel:", e);
            }
        }

        function nl2br(str) {
            return str ? str.replace(/(?:\r\n|\r|\n)/g, '<br>') : '';
        }

        /* ========================================= */
        /* SECTION NAVIGATION FUNCTIONALITY */
        /* ========================================= */
        class SectionNavigation {
            constructor() {
                this.sectionNavbar = document.getElementById('sectionNavbar');
                this.sectionLinks = document.querySelectorAll('.section-nav-link');
                this.sections = [];
                this.navbarHeight = document.querySelector('.sticky-navbar').offsetHeight;
                this.sectionNavbarHeight = this.sectionNavbar ? this.sectionNavbar.offsetHeight : 0;
                this.currentActive = null;

                this.init();
            }

            /* ========================================= */
            /* INITIALIZE SECTION NAVIGATION */
            /* ========================================= */
            init() {
                // Collect all sections with IDs that are targeted by navigation
                const targetIds = ['visi-misi', 'roadmap', 'struktur-organisasi', 'anggota-laboratory', 'makna-logo-maskot', 'research-focus'];

                targetIds.forEach(id => {
                    const section = document.getElementById(id);
                    if (section) {
                        this.sections.push(section);
                    }
                });

                // Add click event to section links with PROPER scroll
                this.sectionLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = link.getAttribute('href').substring(1);
                        this.scrollToSection(targetId);

                        // Update active state immediately
                        this.sectionLinks.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                    });
                });

                let scrollTimeout;
                window.addEventListener('scroll', () => {
                    if (!scrollTimeout) {
                        scrollTimeout = setTimeout(() => {
                            this.updateActiveSection();
                            scrollTimeout = null;
                        }, 10);
                    }
                });

                // Initial update
                this.updateActiveSection();
            }

            /* ========================================= */
            /* SMOOTH SCROLL TO SPECIFIC SECTION */
            /* ========================================= */
            scrollToSection(sectionId) {
                const targetSection = document.getElementById(sectionId);

                if (targetSection) {
                    // Calculate offset - IMPROVED for precise scrolling
                    const offsetTop = targetSection.offsetTop - this.navbarHeight - this.sectionNavbarHeight - 10;

                    console.log('Scrolling to:', sectionId, 'Offset:', offsetTop); // Debug log

                    // Use smooth scroll if supported
                    if ('scrollBehavior' in document.documentElement.style) {
                        window.scrollTo({
                            top: Math.max(0, offsetTop), // Ensure not negative
                            behavior: 'smooth'
                        });
                    } else {
                        // Fallback for older browsers
                        window.scrollTo(0, Math.max(0, offsetTop));
                    }
                }
            }

            /* ========================================= */
            /* UPDATE ACTIVE SECTION BASED ON SCROLL POSITION */
            /* ========================================= */
            updateActiveSection() {
                let currentSection = '';
                const scrollPosition = window.scrollY + this.navbarHeight + this.sectionNavbarHeight + 50; // Reduced threshold

                // Find currently active section
                this.sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;
                    const sectionId = section.getAttribute('id');

                    // Debug log for position tracking
                    if (sectionId === 'visi-misi') {
                        console.log('Visi Misi - Top:', sectionTop, 'Scroll Pos:', scrollPosition);
                    }

                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight && sectionId) {
                        currentSection = sectionId;
                    }
                });

                // If no active section, find the closest one
                if (!currentSection && this.sections.length > 0) {
                    let closestSection = this.sections[0];
                    let closestDistance = Math.abs(this.sections[0].offsetTop - scrollPosition);

                    this.sections.forEach(section => {
                        const distance = Math.abs(section.offsetTop - scrollPosition);
                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestSection = section;
                        }
                    });

                    currentSection = closestSection.getAttribute('id');
                }

                if (currentSection && currentSection !== this.currentActive) {
                    this.currentActive = currentSection;

                    console.log('Active section changed to:', currentSection); // Debug log

                    // Update active link
                    this.sectionLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${currentSection}`) {
                            link.classList.add('active');
                        }
                    });
                }
            }

            /* ========================================= */
            /* HANDLE SECTION NAVBAR VISIBILITY */
            /* ========================================= */
            handleSectionNavbarVisibility() {
                const heroSection = document.querySelector('.hero-section');
                if (!heroSection) return;

                const heroBottom = heroSection.offsetTop + heroSection.offsetHeight;
                const scrollPosition = window.scrollY;

                if (scrollPosition >= heroBottom - this.navbarHeight) {
                    this.sectionNavbar.style.position = 'fixed';
                    this.sectionNavbar.style.top = `${this.navbarHeight}px`;
                    this.sectionNavbar.style.width = '100%';
                } else {
                    this.sectionNavbar.style.position = 'sticky';
                    this.sectionNavbar.style.top = `${this.navbarHeight}px`;
                    this.sectionNavbar.style.width = '100%';
                }
            }
        }

        /* ========================================= */
        /* NAVBAR SCROLL BEHAVIOR */
        /* ========================================= */
        class NavbarScrollBehavior {
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                this.sectionNavbar = document.getElementById('sectionNavbar');
                this.lastScrollY = window.scrollY;
                this.scrollThreshold = 100; // Pixels to scroll before main navbar hides
                this.isMainNavHidden = false;

                this.init();
            }

            /* ========================================= */
            /* INITIALIZE NAVBAR SCROLL BEHAVIOR */
            /* ========================================= */
            init() {
                window.addEventListener('scroll', () => {
                    this.handleScroll();
                });

                // Initial check
                this.handleScroll();
            }

            /* ========================================= */
            /* HANDLE SCROLL EVENTS FOR NAVBAR */
            /* ========================================= */
            handleScroll() {
                const currentScrollY = window.scrollY;
                const scrollDirection = currentScrollY > this.lastScrollY ? 'down' : 'up';

                // If scrolling down beyond threshold, hide main navbar
                if (scrollDirection === 'down' && currentScrollY > this.scrollThreshold && !this.isMainNavHidden) {
                    this.hideMainNavbar();
                }
                // If scrolling up, show main navbar
                else if (scrollDirection === 'up' && this.isMainNavHidden) {
                    this.showMainNavbar();
                }
                // If at the very top, ensure main navbar is visible
                else if (currentScrollY <= this.scrollThreshold && this.isMainNavHidden) {
                    this.showMainNavbar();
                }

                this.lastScrollY = currentScrollY;
            }

            /* ========================================= */
            /* HIDE MAIN NAVBAR */
            /* ========================================= */
            hideMainNavbar() {
                this.mainNavbar.classList.add('hidden');
                this.sectionNavbar.classList.add('main-sticky');
                document.body.classList.add('nav-main-hidden');
                this.isMainNavHidden = true;
            }

            /* ========================================= */
            /* SHOW MAIN NAVBAR */
            /* ========================================= */
            showMainNavbar() {
                this.mainNavbar.classList.remove('hidden');
                this.sectionNavbar.classList.remove('main-sticky');
                document.body.classList.remove('nav-main-hidden');
                this.isMainNavHidden = false;
            }
        }

        /* ========================================= */
        /* COMPACT NAVBAR ON SCROLL */
        /* ========================================= */
        class CompactNavbar {
            constructor() {
                this.mainNavbar = document.getElementById('mainNavbar');
                this.compactThreshold = 50;

                this.init();
            }

            /* ========================================= */
            /* INITIALIZE COMPACT NAVBAR */
            /* ========================================= */
            init() {
                window.addEventListener('scroll', () => {
                    this.toggleCompact();
                });
            }

            /* ========================================= */
            /* TOGGLE COMPACT STATE BASED ON SCROLL */
            /* ========================================= */
            toggleCompact() {
                if (window.scrollY > this.compactThreshold) {
                    this.mainNavbar.classList.add('compact');
                } else {
                    this.mainNavbar.classList.remove('compact');
                }
            }
        }

        /* ========================================= */
        /* MAIN INITIALIZATION WHEN DOM IS READY */
        /* ========================================= */
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize all components

            // Initialize Roadmap Carousel if exists
            const roadmapCards = document.querySelectorAll('.roadmap-card');
            if (roadmapCards.length > 0) {
                new RoadmapCarousel();
            }

            // Initialize Research Focus Carousel if exists
            const researchTrack = document.getElementById('track');
            if (researchTrack) {
                new ResearchFocusCarousel();
            }

            // Initialize navigation components
            new SectionNavigation();
            // new NavbarScrollBehavior();
            new CompactNavbar();

            console.log('All JavaScript components initialized successfully');
        });

        /* ========================================= */
        /* FALLBACK FOR SMOOTH SCROLL IN OLD BROWSERS */
        /* ========================================= */
        if (!('scrollBehavior' in document.documentElement.style)) {
            document.querySelectorAll('.section-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
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

        /* ========================================= */
        /* MOBILE PAGINATION LOGIC                   */
        /* ========================================= */
        document.addEventListener('DOMContentLoaded', function() {
            const gridContainer = document.querySelector('.mobile-researcher-grid');
            const cards = document.querySelectorAll('.mobile-researcher-grid .res-card');
            const paginationContainer = document.getElementById('mobilePagination');

            let currentPage = 1;
            let itemsPerPage = 3;
            let totalPages = 1;
            let isAnimating = false;

            function updateConfig() {
                const width = window.innerWidth;
                if (width <= 576) {
                    itemsPerPage = 2; // HP: 2 Kartu
                } else {
                    itemsPerPage = 3; // Tablet: 3 Kartu
                }

                totalPages = Math.ceil(cards.length / itemsPerPage);
                if (currentPage > totalPages) currentPage = 1;

                renderPagination();
                // Load awal langsung tanpa animasi exit, tapi tetap stagger masuk
                swapCards(currentPage, true);
            }

            // FUNGSI UTAMA: EXIT -> SWAP -> STAGGER ENTER
            function showPage(page) {
                if (isAnimating) return;
                isAnimating = true;

                // 1. Fase Exit: Container menghilang (Fade Out)
                gridContainer.classList.add('is-exiting');

                // 2. Tunggu 200ms (sesuai CSS transition opacity)
                setTimeout(() => {

                    // 3. Fase Swap & Enter
                    swapCards(page, true); // true = aktifkan animasi masuk

                    // Kembalikan Opacity Container
                    gridContainer.classList.remove('is-exiting');

                    // Kunci animasi sebentar sampai efek selesai semua
                    setTimeout(() => {
                        isAnimating = false;
                    }, 600); // Buffer aman

                }, 200);
            }

            function swapCards(page, triggerAnimation = false) {
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;

                let visibleIndex = 0; // Counter untuk urutan animasi (0, 1, 2...)

                cards.forEach((card, index) => {
                    // Reset animasi lama dulu
                    card.classList.remove('card-animate-enter');
                    card.style.animationDelay = '0s';
                    card.style.opacity = ''; // Reset opacity inline

                    if (index >= start && index < end) {
                        card.style.display = 'flex';

                        if (triggerAnimation) {
                            // Trik Force Reflow agar animasi bisa restart
                            void card.offsetWidth;

                            // Tambah class animasi
                            card.classList.add('card-animate-enter');

                            // LOGIKA "KIRI KE KANAN":
                            // Beri delay bertingkat: 0ms, 100ms, 200ms...
                            card.style.animationDelay = `${visibleIndex * 0.1}s`;
                            visibleIndex++;
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            // --- Bagian Pagination (Tetap Sama) ---
            function renderPagination() {
                paginationContainer.innerHTML = '';
                if (totalPages > 1) {
                    // Prev
                    const prevBtn = createBtn('<i class="fas fa-chevron-left"></i>', () => {
                        let nextPage = currentPage > 1 ? currentPage - 1 : totalPages;
                        changePage(nextPage);
                    });
                    paginationContainer.appendChild(prevBtn);

                    // Angka
                    for (let i = 1; i <= totalPages; i++) {
                        const link = createBtn(i, () => changePage(i));
                        if (i === currentPage) link.classList.add('active');
                        paginationContainer.appendChild(link);
                    }

                    // Next
                    const nextBtn = createBtn('<i class="fas fa-chevron-right"></i>', () => {
                        let nextPage = currentPage < totalPages ? currentPage + 1 : 1;
                        changePage(nextPage);
                    });
                    paginationContainer.appendChild(nextBtn);
                }
            }

            function createBtn(content, onClick) {
                const btn = document.createElement('a');
                btn.href = 'javascript:void(0)';
                btn.className = 'page-link-custom';
                btn.innerHTML = content;
                btn.addEventListener('click', onClick);
                return btn;
            }

            function changePage(newPage) {
                if (newPage !== currentPage) {
                    currentPage = newPage;
                    showPage(currentPage);
                    renderPagination();
                }
            }

            updateConfig();

            window.addEventListener('resize', () => {
                clearTimeout(window.resizeTimer);
                window.resizeTimer = setTimeout(updateConfig, 100);
            });
        });
    </script>

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
        // const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

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