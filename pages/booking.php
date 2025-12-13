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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="../assets/css/bookingStyle.css?v=<?php echo time(); ?>">
    <style>
        /* CSS Tambahan untuk logika disabled tanggal */
        .calendar-table td.disabled {
            color: #d1d5db; /* Abu-abu terang */
            background-color: #f9fafb;
            cursor: not-allowed;
            pointer-events: none; /* Mencegah klik */
        }
        
        /* Style untuk display tanggal terpilih */
        .selected-date-display {
            font-size: 1rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 15px;
            text-align: center;
            padding: 12px;
            background-color: #eaf0ff;
            border-radius: 10px;
            border: 1px solid #dbeafe;
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

    <div class="content-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="infoPeminjaman.php">Informasi Peminjaman</a></li>
                            <li class="breadcrumb-item" aria-current="page">Request Laboratory Access</li>
                        </ol>
                    </nav>

                    <h1 class="section-title">Request Laboratory Access</h1>
                    <p class="section-content">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                        laboris nisi ut aliquip ex ea commodo consequat.
                    </p>

                    <h2 class="section-title">Consultation</h2>
                    <p class="section-content">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                        laboris nisi ut aliquip ex ea commodo consequat.
                    </p>
                </div>

                <div class="col-lg-5">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-month" id="current-month">September 2023</div>
                            <div class="calendar-nav">
                                <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                                <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <table class="calendar-table" id="calendar-body">
                            <thead>
                                <tr>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                    <th>Sun</th>
                                </tr>
                            </thead>
                            <tbody id="calendar-days">
                                </tbody>
                        </table>
                    </div>

                    <div class="booking-form">
                        <h3 class="section-title" style="font-size: 24px;">Booking</h3>

                        <div class="form-group">
                            <input type="hidden" id="booking-date-value"> 
                            <div id="booking-date-display" class="selected-date-display">Pilih tanggal di kalender</div>
                        </div>

                        <div class="time-inputs">
                            <div class="form-group">
                                <label class="form-label">Check-in (07:00 - 21:00)</label>
                                <input type="time" class="form-control" id="checkin-time" min="07:00" max="21:00">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Check-out (07:00 - 21:00)</label>
                                <input type="time" class="form-control" id="checkout-time" min="07:00" max="21:00">
                            </div>
                        </div>

                        <button class="booking-btn" id="btn-next-booking">Lanjut Isi Data Diri</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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


        document.addEventListener('DOMContentLoaded', function () {
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

            // --- 1. SETUP KALENDER ---
            let currentDate = new Date();
            const calendarMonth = document.getElementById('current-month');
            const calendarDays = document.getElementById('calendar-days');
            const prevMonthBtn = document.getElementById('prev-month');
            const nextMonthBtn = document.getElementById('next-month');
            
            // Input & Display
            const bookingDateValue = document.getElementById('booking-date-value');
            const bookingDateDisplay = document.getElementById('booking-date-display');

            // Set Hari Ini
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset jam ke 00:00 untuk perbandingan tanggal

            // Format opsi untuk tanggal Indonesia
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            
            // Default: Isi tanggal hari ini
            const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            bookingDateValue.value = todayStr;
            bookingDateDisplay.textContent = today.toLocaleDateString('id-ID', options);

            function generateCalendar(date) {
                const year = date.getFullYear();
                const month = date.getMonth();
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                
                calendarMonth.textContent = `${monthNames[month]} ${year}`;
                calendarDays.innerHTML = '';

                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const lastDayPrevMonth = new Date(year, month, 0).getDate();
                
                // Adjust index (0 = Minggu di JS, tapi kita mau Senin di awal)
                let firstDayIndex = firstDay.getDay(); 
                firstDayIndex = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

                let dayCount = 1;
                let nextMonthDayCount = 1;

                for (let i = 0; i < 6; i++) {
                    const row = document.createElement('tr');

                    for (let j = 0; j < 7; j++) {
                        const cell = document.createElement('td');

                        if (i === 0 && j < firstDayIndex) {
                            // Tanggal bulan lalu
                            cell.textContent = lastDayPrevMonth - firstDayIndex + j + 1;
                            cell.classList.add('other-month', 'disabled'); // Pasti disabled karena masa lalu
                        } else if (dayCount > lastDay.getDate()) {
                            // Tanggal bulan depan
                            cell.textContent = nextMonthDayCount++;
                            cell.classList.add('other-month');
                            // Cek apakah bulan depan itu masa depan?
                            const nextMonthDate = new Date(year, month + 1, nextMonthDayCount - 1);
                            if (nextMonthDate < today) {
                                cell.classList.add('disabled');
                            }
                        } else {
                            // Tanggal bulan ini
                            cell.textContent = dayCount;
                            
                            // Buat objek tanggal untuk sel ini
                            const cellDate = new Date(year, month, dayCount);
                            cellDate.setHours(0, 0, 0, 0);

                            // Format YYYY-MM-DD
                            const cellDateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(dayCount).padStart(2, '0');

                            // Logika Disabled: Jika tanggal < hari ini
                            if (cellDate < today) {
                                cell.classList.add('disabled');
                            } else {
                                // Highlight jika sama dengan nilai yang tersimpan di input hidden
                                if (bookingDateValue.value === cellDateStr) {
                                    cell.classList.add('active');
                                    // Sinkronkan text display jika view di-refresh
                                    bookingDateDisplay.textContent = cellDate.toLocaleDateString('id-ID', options);
                                }

                                // Event Listener Klik Tanggal
                                cell.addEventListener('click', function () {
                                    // Reset active class
                                    document.querySelectorAll('.calendar-table td').forEach(d => d.classList.remove('active'));
                                    
                                    // Set active class
                                    this.classList.add('active');

                                    // Update Input & Display
                                    bookingDateValue.value = cellDateStr;
                                    bookingDateDisplay.textContent = cellDate.toLocaleDateString('id-ID', options);
                                });
                            }
                            dayCount++;
                        }
                        row.appendChild(cell);
                    }
                    calendarDays.appendChild(row);
                    if (dayCount > lastDay.getDate() && nextMonthDayCount > 7) break;
                }
            }

            generateCalendar(currentDate);

            // Navigasi Bulan
            prevMonthBtn.addEventListener('click', function () {
                currentDate.setMonth(currentDate.getMonth() - 1);
                generateCalendar(currentDate);
            });

            nextMonthBtn.addEventListener('click', function () {
                currentDate.setMonth(currentDate.getMonth() + 1);
                generateCalendar(currentDate);
            });

            // --- 2. VALIDASI & NAVIGASI HALAMAN ---
            const btnNext = document.getElementById('btn-next-booking');
            
            btnNext.addEventListener('click', function() {
                // Cek Login
                if (!isLoggedIn) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Diperlukan',
                        text: 'Anda harus login terlebih dahulu untuk melakukan booking.',
                        confirmButtonText: 'Ke Halaman Login',
                        confirmButtonColor: '#4361ee'
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = '../admin/login.php';
                    });
                    return;
                }

                // Ambil Data
                const dateVal = bookingDateValue.value;
                const timeIn = document.getElementById('checkin-time').value;
                const timeOut = document.getElementById('checkout-time').value;

                // Cek Kelengkapan
                if (!dateVal || !timeIn || !timeOut) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Belum Lengkap',
                        text: 'Harap pastikan tanggal, jam check-in, dan jam check-out sudah terisi.',
                        confirmButtonColor: '#4361ee'
                    });
                    return;
                }

                // --- VALIDASI JAM OPERASIONAL (07:00 - 21:00) ---
                // Kita ubah ke menit untuk perbandingan akurat
                function timeToMinutes(t) {
                    const [h, m] = t.split(':').map(Number);
                    return h * 60 + m;
                }

                const minLimit = timeToMinutes("07:00");
                const maxLimit = timeToMinutes("21:00");
                const inMinutes = timeToMinutes(timeIn);
                const outMinutes = timeToMinutes(timeOut);

                // Cek Range
                if (inMinutes < minLimit || inMinutes > maxLimit || outMinutes < minLimit || outMinutes > maxLimit) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Di Luar Jam Operasional',
                        text: 'Laboratorium hanya beroperasi pukul 07:00 - 21:00 WIB.',
                        confirmButtonColor: '#4361ee'
                    });
                    return;
                }

                // Cek Logika Waktu (Check-out harus > Check-in)
                if (inMinutes >= outMinutes) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Waktu Tidak Valid',
                        text: 'Jam check-out harus lebih besar dari jam check-in.',
                        confirmButtonColor: '#4361ee'
                    });
                    return;
                }

                // --- NAVIGASI KE formBooking.php ---
                // Mengirim data via URL Parameter
                const targetUrl = `formBooking.php?date=${dateVal}&checkin=${timeIn}&checkout=${timeOut}`;
                window.location.href = targetUrl;
            });
        });
    </script>
</body>
</html>