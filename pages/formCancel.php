<?php
// =================================================================
// 1. SESSION START (WAJIB PALING ATAS)
// =================================================================
session_start();

// 2. Cek Login (Sekarang session sudah terbaca, jadi aman)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../admin/login.php';</script>";
    exit;
}

// =================================================================
// 3. SYSTEM DEBUGGING & ANTI-CRASH
// =================================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // =================================================================
    // 4. KONEKSI DATABASE
    // =================================================================
    $dbPath = __DIR__ . '/../admin/config/database.php';

    if (!file_exists($dbPath)) {
        throw new Exception("File database tidak ditemukan di: " . realpath(__DIR__ . '/../admin/') . "/config/database.php");
    }

    require_once $dbPath;

    // Deteksi Variabel Koneksi
    $db = null;
    if (isset($conn)) {
        $db = $conn;
    } elseif (isset($pdo)) {
        $db = $pdo;
    } elseif (class_exists('Database')) {
        $db = (new Database())->getConnection();
    }

    if (!$db) {
        throw new Exception("Koneksi Database Gagal: Variabel \$conn/\$pdo tidak ditemukan.");
    }

    // =================================================================
    // 5. LOGIKA UTAMA
    // =================================================================

    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['nama'] ?? 'User';
    $userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';
    $isLoggedIn = true; // Flag untuk HTML

    // --- QUERY DATA BOOKING AKTIF ---
    $sqlActive = "SELECT p.id_peminjaman, p.tujuan, p.tanggal_peminjaman, 
                          dp.waktu_mulai, dp.waktu_selesai, 
                          u.nama, u.email, 
                          p.nomor_identitas AS nim_nip,
                          p.no_hp, p.asal_instansi, p.kategori_pemohon
                   FROM peminjaman p
                   JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                   JOIN users u ON p.id_user = u.id_user
                   WHERE p.id_user = ? 
                   AND p.status IN ('Pending', 'Approved', 'Confirmed') 
                   AND (p.request_pembatalan IS NULL OR p.request_pembatalan IS FALSE)
                   ORDER BY p.created_at DESC";

    $stmt = $db->prepare($sqlActive);
    $stmt->execute([$userId]);
    $activeBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($activeBookings);

    // Logic Tampilan (Auto Redirect / Select Option)
    $jsAction = 'none';
    $targetId = null;
    $bookingData = [];

    // Skenario 1: Ada ID di URL
    if (isset($_GET['id'])) {
        $targetId = $_GET['id'];
        foreach ($activeBookings as $b) {
            if ($b['id_peminjaman'] == $targetId) {
                $bookingData = $b;
                break;
            }
        }
        if (empty($bookingData)) $jsAction = 'show_invalid_id';
    }
    // Skenario 2: Belum ada ID
    else {
        if ($count === 0) {
            $jsAction = 'show_empty';
        } elseif ($count === 1) {
            // Jika cuma ada 1 booking, langsung redirect ke formnya
            header("Location: formCancel.php?id=" . $activeBookings[0]['id_peminjaman']);
            exit;
        } else {
            $jsAction = 'show_select';
        }
    }

    // --- 6. AMBIL DATA SETTING (Logo & Nama Lab) ---
    $logoSrc = '../assets/images/logo.png';
    $namaLabText = 'Laboratorium Business Analytics'; // Default text

    try {
        // Ambil kolom 'value' (teks) DAN 'file_path' (gambar)
        $stmt = $db->query("SELECT key, value, file_path FROM settings WHERE key IN ('logo', 'nama_lab')");
        $resultRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mapping array
        $settings = [];
        foreach ($resultRaw as $row) {
            $settings[$row['key']] = $row;
        }

        // 1. Set Logo
        if (!empty($settings['logo']['file_path'])) {
            $logoSrc = '../admin/' . $settings['logo']['file_path'];
        }

        // 2. Set Nama Lab
        if (!empty($settings['nama_lab']['value'])) {
            $namaLabText = $settings['nama_lab']['value'];
        }
    } catch (Exception $e) {
        /* Ignore error setting */
    }

} catch (Throwable $e) {
    die('<div style="background:#f8d7da; padding:20px; font-family:sans-serif; border:1px solid #f5c6cb; margin:20px;">
            <h3>Error Sistem:</h3>
            <p>' . $e->getMessage() . '</p>
         </div>');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pembatalan - <?php echo htmlspecialchars($namaLabText); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/formCancelBookStyle.css?v=<?php echo time(); ?>">

    <style>
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <nav class="sticky-navbar">
        <div class="logo-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Laboratorium Logo">
            </div>
            <div class="lab-name-container">
                  <div class="lab-name"><?php echo htmlspecialchars($namaLabText); ?></div>
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
            <a href="../index.php" class="breadcrumb-link">Beranda</a>
            <span class="breadcrumb-separator">/</span>
            <a href="booking.php" class="breadcrumb-link">Booking</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Form Pembatalan</span>
        </div>

        <?php if (!empty($bookingData)): ?>
            <div class="row">
                <div class="col-md-7 order-2 order-md-1">
                    <div class="form-container">
                        <h1 class="page-title">Form Pembatalan</h1>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>Mohon isi alasan pembatalan dengan jelas.</div>
                        </div>

                        <h2 class="section-title mt-4">Informasi Kontak & Booking:</h2>

                        <form id="cancelForm">
                            <input type="hidden" name="id_peminjaman" value="<?php echo $bookingData['id_peminjaman']; ?>">
                            <input type="hidden" name="action" value="request_cancel">

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($bookingData['nama']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($bookingData['email']); ?>" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIM/NIP</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($bookingData['nim_nip'] ?? '-'); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Handphone</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($bookingData['no_hp'] ?? '-'); ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori / Instansi</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(($bookingData['kategori_pemohon'] ?? '') . ' - ' . ($bookingData['asal_instansi'] ?? '')); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="alasan" class="form-label fw-bold text-danger">Alasan Pembatalan Booking <span class="text-danger">*</span></label>
                                <textarea class="form-control border-danger" id="alasan" name="alasan_pembatalan" rows="4" placeholder="Jelaskan secara rinci mengapa Anda ingin membatalkan booking ini..." required></textarea>
                            </div>

                            <div class="form-actions d-flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">Kembali</button>
                                <button type="submit" class="btn btn-danger confirm-btn flex-grow-1">Ajukan Pembatalan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-5 order-1 order-md-2 mb-4">
                    <div class="booking-summary position-sticky" style="top: 100px;">
                        <h3 class="summary-title">Booking Summary</h3>
                        <div class="summary-content">
                            <div class="booking-details">
                                <div class="detail-item">
                                    <span class="detail-label">ID Booking:</span>
                                    <span class="detail-value fw-bold">#<?php echo $bookingData['id_peminjaman']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tujuan:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bookingData['tujuan']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Tanggal:</span>
                                    <span class="detail-value"><?php echo date('d F Y', strtotime($bookingData['tanggal_peminjaman'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Waktu:</span>
                                    <span class="detail-value text-primary">
                                        <?php echo date('H:i', strtotime($bookingData['waktu_mulai'])) . ' - ' . date('H:i', strtotime($bookingData['waktu_selesai'])); ?> WIB
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleMenu() {
            document.getElementById('navMenu').classList.toggle('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const action = "<?php echo $jsAction; ?>";

            if (action === 'show_empty') {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Booking',
                    text: 'Anda tidak memiliki booking aktif yang bisa dibatalkan.',
                    confirmButtonText: 'Kembali ke Booking',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = 'booking.php';
                });
            } else if (action === 'show_invalid_id') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Data booking tidak ditemukan atau bukan milik Anda.',
                    confirmButtonText: 'Kembali'
                }).then(() => {
                    window.location.href = 'booking.php';
                });
            } else if (action === 'show_select') {
                const bookings = <?php echo json_encode($activeBookings); ?>;
                const options = {};
                bookings.forEach(b => {
                    const date = new Date(b.tanggal_peminjaman).toLocaleDateString('id-ID');
                    const time = b.waktu_mulai.substring(0, 5);
                    options[b.id_peminjaman] = `[${date} ${time}] ${b.tujuan}`;
                });

                Swal.fire({
                    title: 'Pilih Booking',
                    text: 'Pilih jadwal yang ingin Anda batalkan:',
                    input: 'select',
                    inputOptions: options,
                    inputPlaceholder: 'Pilih sesi...',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjut',
                    cancelButtonText: 'Batal',
                    allowOutsideClick: false,
                    inputValidator: (value) => {
                        return !value && 'Anda harus memilih salah satu booking!';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'formCancel.php?id=' + result.value;
                    } else {
                        window.location.href = 'booking.php';
                    }
                });
            }

            const form = document.getElementById('cancelForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Pembatalan?',
                        text: "Permintaan akan dikirim ke Admin untuk ditinjau.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Batalkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitCancellation();
                        }
                    });
                });
            }
        });

        function submitCancellation() {
            const formData = new FormData(document.getElementById('cancelForm'));
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../admin/api/peminjaman.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'tableBooking.php';
                        });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'Terjadi kesalahan sistem. Cek konsol browser.', 'error');
                });
        }
    </script>
</body>
</html>