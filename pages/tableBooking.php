<?php
session_start();

// --- LOGIKA SESSION ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['nama'] : '';
$userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Business Analytics - Table Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/tableBookingStyle.css?v=<?php echo time(); ?>">

    <style>
        .booking-table th.today {
            background-color: #f5f5f5 !important;
            border: 1px solid #e0e0e0 !important;
            border-bottom: 2px solid #e0e0e0 !important;
            color: #333 !important;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        
        /* CSS Tambahan untuk Tampilan Kotak Booking yang Lebih Rapi */
        .booking-rectangle {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 5px 8px !important;
            border-left: 4px solid !important; /* Aksen garis di kiri */
        }
        .booking-time {
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 2px;
            color: #333;
        }
        .booking-instansi {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 2px;
            color: #000;
            text-transform: uppercase;
        }
        .booking-name {
            font-size: 10px;
            color: #555;
            font-style: italic;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                <a class="nav-link dropdown-toggle" href="#" role="button" aria-expanded="false">
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
                <a class="nav-link dropdown-toggle active" href="#" role="button" aria-expanded="false">
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

    <div class="table-booking-container">
        <div class="table-header">
            <div class="date-navigation">
                <button class="nav-btn" id="prev-week">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="date-range" id="date-range">Loading...</div>
                <button class="nav-btn" id="next-week">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button class="today-btn" id="today-btn">Today</button>
            </div>
            <div class="action-buttons">
                <?php if ($isLoggedIn): ?>
                    <button class="btn-add-booking" id="open-add-booking">Add Booking</button>
                <?php else: ?>
                    <button class="btn-add-booking" onclick="alert('Silakan login terlebih dahulu untuk melakukan booking.')">Login to Book</button>
                <?php endif; ?>
                <button class="btn-cancel-booking">Cancel Booking</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="booking-table" id="booking-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="booking-detail-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Detail Booking</div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="booking-detail">
                    <label>Hari & Tanggal:</label>
                    <p id="detail-date">-</p>
                </div>
                <div class="booking-detail">
                    <label>Waktu:</label>
                    <p id="detail-time">-</p>
                </div>
                <div class="booking-detail">
                    <label>Instansi / Prodi:</label>
                    <p id="detail-instansi">-</p>
                </div>
                <div class="booking-detail">
                    <label>Nama Peminjam:</label>
                    <p id="detail-booker">-</p>
                </div>
                <div class="booking-detail">
                    <label>Keperluan:</label>
                    <p id="detail-purpose">-</p>
                </div>
                <div class="booking-detail">
                    <label>Status:</label>
                    <p id="detail-status">-</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-close-modal">Tutup</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="add-booking-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Tambah Booking Baru</div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="booking-date">Tanggal:</label>
                    <select id="booking-date" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label for="booking-time">Waktu:</label>
                    <div class="time-inputs">
                        <select id="start-hour" class="form-control"></select>
                        <select id="start-minute" class="form-control">
                            <option value="00">00</option><option value="15">15</option>
                            <option value="30">30</option><option value="45">45</option>
                        </select>
                        <span style="line-height: 38px;">-</span>
                        <select id="end-hour" class="form-control"></select>
                        <select id="end-minute" class="form-control">
                            <option value="00">00</option><option value="15">15</option>
                            <option value="30">30</option><option value="45">45</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="booker-instansi">Instansi / Prodi:</label>
                    <input type="text" id="booker-instansi" class="form-control" placeholder="Contoh: TI, Manajemen, BEM">
                </div>
                <div class="form-group">
                    <label for="booking-purpose">Keperluan:</label>
                    <input type="text" id="booking-purpose" class="form-control" placeholder="Masukkan keperluan">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-add-booking-modal" id="submit-booking">Simpan Booking</button>
                <button class="btn-modal btn-close-modal">Batal</button>
            </div>
        </div>
    </div>

    <script>
        // --- NAV MENU LOGIC ---
        function toggleMenu() {
            const navMenu = document.getElementById('navMenu');
            const hamburgerIcon = document.querySelector('.hamburger i');
            navMenu.classList.toggle('active');
            if (navMenu.classList.contains('active')) {
                hamburgerIcon.classList.remove('fa-bars'); hamburgerIcon.classList.add('fa-times');
            } else {
                hamburgerIcon.classList.remove('fa-times'); hamburgerIcon.classList.add('fa-bars');
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991) {
                        e.preventDefault(); e.stopPropagation(); 
                        const dropdownMenu = this.nextElementSibling; 
                        if (dropdownMenu) {
                            document.querySelectorAll('.dropdown-menu.show').forEach(menu => { if (menu !== dropdownMenu) menu.classList.remove('show'); });
                            dropdownMenu.classList.toggle('show'); this.classList.toggle('show');
                        }
                    }
                });
            });
            document.addEventListener('click', function(e) {
                const navMenu = document.getElementById('navMenu');
                const hamburger = document.querySelector('.hamburger');
                if (window.innerWidth <= 991 && navMenu.classList.contains('active') && !navMenu.contains(e.target) && !hamburger.contains(e.target)) toggleMenu();
            });
        });

        // --- KONFIGURASI API ---
        const API_URL = '../admin/api/peminjaman.php'; 

        let bookingsData = []; 
        let currentDate = new Date(); 
        let renderedBookings = new Set();

        const START_HOUR = 7;
        const END_HOUR = 21;
        const PIXELS_PER_HOUR = 60;
        const TOTAL_HOURS = END_HOUR - START_HOUR;
        const TOTAL_HEIGHT = TOTAL_HOURS * PIXELS_PER_HOUR;

        document.addEventListener('DOMContentLoaded', function () {
            if(typeof setupNavbar === 'function') setupNavbar();
            document.getElementById('prev-week').addEventListener('click', () => changeWeek(-5));
            document.getElementById('next-week').addEventListener('click', () => changeWeek(5));
            document.getElementById('today-btn').addEventListener('click', goToToday);
            setupModalEvents();
            generateHourOptions();
            loadBookingsFromAPI();
        });

        // --- FETCH DATA (FILTER ONLY APPROVED) ---
        async function loadBookingsFromAPI() {
            try {
                const response = await fetch(API_URL);
                const result = await response.json();

                if (result.success) {
                    // FILTER DAN MAPPING DATA
                    bookingsData = result.data
                        .filter(item => {
                            // LOGIKA FILTER: Hanya status Approved / Confirmed
                            const s = item.status.toLowerCase();
                            return s === 'approved' || s === 'confirmed';
                        })
                        .map(item => {
                            const checkInParts = item.check_in.split(' ');
                            const checkOutParts = item.check_out.split(' ');
                            
                            return {
                                id: item.id_peminjaman,
                                date: checkInParts[0],
                                startTime: checkInParts[1].substring(0, 5),
                                endTime: checkOutParts[1].substring(0, 5),
                                
                                // Mapping untuk tampilan
                                instansi: item.asal_instansi, // Untuk judul kotak (Prodi/Instansi)
                                bookerName: item.nama_akun,   // Untuk nama kecil (Peminjam)
                                purpose: item.tujuan,
                                status: item.status,
                                
                                // Warna Khusus Approved (Hijau)
                                color: 'rgba(76, 175, 80, 0.15)',
                                borderColor: 'rgba(76, 175, 80, 0.6)'
                            };
                        });
                    generateTableData();
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        // --- SUBMIT DATA (POST) ---
        async function submitBookingToAPI() {
            const btnSubmit = document.getElementById('submit-booking');
            
            const dateVal = document.getElementById('booking-date').value;
            const startH = document.getElementById('start-hour').value;
            const startM = document.getElementById('start-minute').value;
            const endH = document.getElementById('end-hour').value;
            const endM = document.getElementById('end-minute').value;
            
            // Saya ubah ID input ini agar sesuai dengan Instansi yang diminta
            const instansi = document.getElementById('booker-instansi').value; 
            const purpose = document.getElementById('booking-purpose').value; 

            if (!instansi || !purpose) {
                alert("Instansi dan Keperluan harus diisi!");
                return;
            }

            const checkIn = `${dateVal} ${startH}:${startM}:00`;
            const checkOut = `${dateVal} ${endH}:${endM}:00`;

            if (checkOut <= checkIn) {
                alert("Waktu selesai harus setelah waktu mulai.");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('check_in', checkIn);
            formData.append('check_out', checkOut);
            formData.append('tujuan', purpose);
            formData.append('asal_instansi', instansi); 
            // Default Values
            formData.append('kategori_pemohon', 'Umum'); 
            formData.append('nomor_identitas', '-');     
            formData.append('no_handphone', '-');        

            btnSubmit.textContent = 'Menyimpan...';
            btnSubmit.disabled = true;

            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    alert('Booking berhasil diajukan! Menunggu konfirmasi Admin.');
                    document.getElementById('add-booking-modal').style.display = 'none';
                    loadBookingsFromAPI(); 
                } else {
                    alert('Gagal: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                btnSubmit.textContent = 'Simpan Booking';
                btnSubmit.disabled = false;
            }
        }

        function generateTableData() {
            const tableHead = document.querySelector('.booking-table thead tr');
            const tableBody = document.querySelector('.booking-table tbody');
            renderedBookings.clear();

            while (tableHead.children.length > 1) { tableHead.removeChild(tableHead.lastChild); }
            tableBody.innerHTML = '';

            const mondayDate = getMonday(currentDate);
            const fridayDate = new Date(mondayDate); fridayDate.setDate(mondayDate.getDate() + 4);
            document.getElementById('date-range').textContent = 
                `${getShortMonthName(mondayDate.getMonth())} ${mondayDate.getDate()} - ${fridayDate.getDate()}, ${fridayDate.getFullYear()}`;

            for (let i = 0; i < 5; i++) {
                const date = new Date(mondayDate);
                date.setDate(date.getDate() + i);
                const th = document.createElement('th');
                th.classList.add('day-header');
                if (isToday(date)) th.classList.add('today');
                th.innerHTML = `<div>${getShortDayName(date.getDay())}</div><div>${date.getDate()}/${date.getMonth() + 1}</div>`;
                tableHead.appendChild(th);
            }

            const containerRow = document.createElement('tr');
            const timeCell = document.createElement('td');
            timeCell.className = 'time-header';
            const timeList = document.createElement('div');
            timeList.style.position = 'relative';
            timeList.style.height = `${TOTAL_HEIGHT + 50}px`; 

            for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
                const topPos = (hour - START_HOUR) * PIXELS_PER_HOUR;
                if (hour !== START_HOUR) {
                    const line = document.createElement('div'); line.className = 'time-header-line line-hour'; line.style.top = `${topPos}px`; timeList.appendChild(line);
                }
                const timeLabel = document.createElement('div'); timeLabel.className = 'time-label is-hour'; timeLabel.textContent = `${String(hour).padStart(2,'0')}:00`; timeLabel.style.top = `${topPos}px`; timeList.appendChild(timeLabel);
                if (hour !== END_HOUR) {
                    const halfLine = document.createElement('div'); halfLine.className = 'time-header-line line-half'; halfLine.style.top = `${topPos + 30}px`; timeList.appendChild(halfLine);
                }
            }
            timeCell.appendChild(timeList); containerRow.appendChild(timeCell);

            for (let day = 0; day < 5; day++) {
                const date = new Date(mondayDate); date.setDate(date.getDate() + day);
                const cell = document.createElement('td'); cell.className = 'day-cell';
                const container = document.createElement('div'); container.className = 'day-container'; container.style.height = `${TOTAL_HEIGHT + 50}px`;
                for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
                    const topPos = (hour - START_HOUR) * PIXELS_PER_HOUR;
                    if(hour !== START_HOUR) { 
                         const line = document.createElement('div'); line.className = 'time-line line-hour'; line.style.top = `${topPos}px`; container.appendChild(line);
                    }
                    if (hour !== END_HOUR) {
                        const halfLine = document.createElement('div'); halfLine.className = 'time-line line-half'; halfLine.style.top = `${topPos + 30}px`; container.appendChild(halfLine);
                    }
                }
                cell.appendChild(container); containerRow.appendChild(cell);
            }
            tableBody.appendChild(containerRow);
            renderAllBookings();
            updateBookingDateOptions();
        }

        function renderAllBookings() {
            document.querySelectorAll('.booking-rectangle').forEach(el => el.remove());
            bookingsData.forEach(booking => renderBooking(booking));
        }

        // --- RENDER KOTAK BOOKING (MODIFIKASI TAMPILAN) ---
        function renderBooking(booking) {
            const date = new Date(booking.date);
            const mondayDate = getMonday(currentDate);
            const diffTime = date.getTime() - mondayDate.getTime();
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0 || diffDays > 4) return;

            const [startH, startM] = booking.startTime.split(':').map(Number);
            const [endH, endM] = booking.endTime.split(':').map(Number);
            const startTotalMinutes = (startH * 60) + startM;
            const endTotalMinutes = (endH * 60) + endM;
            const gridStartMinutes = START_HOUR * 60;
            const topPos = ((startTotalMinutes - gridStartMinutes) / 60) * PIXELS_PER_HOUR;
            const height = ((endTotalMinutes - startTotalMinutes) / 60) * PIXELS_PER_HOUR;

            if (topPos < 0) return; 

            const containers = document.querySelectorAll('.day-container');
            if (!containers[diffDays]) return;

            const el = document.createElement('div');
            el.className = 'booking-rectangle';
            el.style.top = `${topPos}px`;
            el.style.height = `${height}px`;
            el.style.backgroundColor = booking.color;
            el.style.borderColor = booking.borderColor;
            
            // TAMPILAN ISI KOTAK: JAM - INSTANSI - NAMA
            el.innerHTML = `
                <div class="booking-time">${booking.startTime} - ${booking.endTime}</div>
                <div class="booking-instansi">${booking.instansi}</div>
                <div class="booking-name">${booking.bookerName}</div>
            `;
            
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                showBookingDetail(booking);
            });
            containers[diffDays].appendChild(el);
        }

        function showBookingDetail(booking) {
            document.getElementById('detail-date').textContent = booking.date; 
            document.getElementById('detail-time').textContent = `${booking.startTime} - ${booking.endTime}`;
            document.getElementById('detail-instansi').textContent = booking.instansi; // Tampilkan Instansi
            document.getElementById('detail-booker').textContent = booking.bookerName; // Tampilkan Nama
            document.getElementById('detail-purpose').textContent = booking.purpose;
            document.getElementById('detail-status').textContent = booking.status;
            
            const statusEl = document.getElementById('detail-status');
            statusEl.style.color = '#2ecc71'; // Hijau karena pasti approved
            statusEl.style.fontWeight = 'bold';
            document.getElementById('booking-detail-modal').style.display = 'flex';
        }

        function getMonday(d) {
            d = new Date(d); const day = d.getDay(); const diff = d.getDate() - day + (day === 0 ? -6 : 1); return new Date(d.setDate(diff));
        }
        function isToday(date) {
            const today = new Date(); return date.getDate() === today.getDate() && date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
        }
        function getShortMonthName(idx) { return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'][idx]; }
        function getShortDayName(idx) { return ['Min','Sen','Sel','Rab','Kam','Jum','Sab'][idx]; }
        function formatDate(date) { const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0'); const d = String(date.getDate()).padStart(2, '0'); return `${y}-${m}-${d}`; }

        function updateBookingDateOptions() {
            const select = document.getElementById('booking-date'); select.innerHTML = ''; const monday = getMonday(currentDate);
            for(let i=0; i<5; i++) {
                const d = new Date(monday); d.setDate(d.getDate() + i); const opt = document.createElement('option'); opt.value = formatDate(d); opt.textContent = `${getShortDayName(d.getDay())}, ${d.getDate()} ${getShortMonthName(d.getMonth())} ${d.getFullYear()}`; select.appendChild(opt);
            }
        }
        function generateHourOptions() {
            const startSel = document.getElementById('start-hour'); const endSel = document.getElementById('end-hour'); startSel.innerHTML = ''; endSel.innerHTML = '';
            for(let i=START_HOUR; i<=END_HOUR; i++) {
                const val = String(i).padStart(2,'0'); startSel.add(new Option(val, val)); endSel.add(new Option(val, val));
            }
            startSel.value = '08'; endSel.value = '09';
        }

        function setupModalEvents() {
            document.querySelectorAll('.close-modal, .btn-close-modal').forEach(btn => {
                btn.addEventListener('click', () => { document.getElementById('booking-detail-modal').style.display = 'none'; document.getElementById('add-booking-modal').style.display = 'none'; });
            });
            const btnAdd = document.getElementById('open-add-booking'); if(btnAdd) btnAdd.addEventListener('click', () => document.getElementById('add-booking-modal').style.display = 'flex');
            const btnSubmit = document.getElementById('submit-booking'); if(btnSubmit) btnSubmit.addEventListener('click', submitBookingToAPI);
        }
        
        function changeWeek(days) { currentDate.setDate(currentDate.getDate() + days); generateTableData(); }
        function goToToday() { currentDate = new Date(); generateTableData(); }
        function setupNavbar() {
            const currentPath = window.location.pathname; const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);
            document.querySelectorAll('.nav-link').forEach(link => { const href = link.getAttribute('href'); if (href && href.includes(currentPage)) { link.classList.add('active'); if(link.closest('.dropdown')) link.closest('.dropdown').querySelector('.dropdown-toggle').classList.add('active'); } });
        }
    </script>
</body>
</html>