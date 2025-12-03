<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Business Analytics - Table Booking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/tableBookingStyle.css">
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
                <a class="nav-link dropdown-toggle active" href="#">
                    Peminjaman Lab
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="../pages/infoPeminjaman.php">Informasi Laboratorium</a></li>
                    <li><a class="dropdown-item" href="../pages/tableBooking.php">Table Peminjaman</a></li>
                    <li><a class="dropdown-item" href="./booking.php">Pemesanan Lab</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../pages/kontak.php">Kontak</a>
            </li>
        </ul>
        
        <button class="login-btn" onclick="window.location.href='../admin/login.php'">Login</button>
    </nav>

    <!-- Table Booking Section -->
    <div class="table-booking-container">
        <div class="table-header">
            <div class="date-navigation">
                <button class="nav-btn" id="prev-week">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="date-range" id="date-range">Dec 1 - 5, 2025</div>
                <button class="nav-btn" id="next-week">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button class="today-btn" id="today-btn">Today</button>
            </div>
            <div class="action-buttons">
                <button class="btn-add-booking" id="open-add-booking">Add Booking</button>
                <button class="btn-cancel-booking">Cancel Booking</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="booking-table" id="booking-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <!-- Header hari akan diisi oleh JavaScript -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Booking -->
    <div class="modal-overlay" id="booking-detail-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Detail Booking</div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="booking-detail">
                    <label>Hari & Tanggal:</label>
                    <p id="detail-date">Senin, 01 Desember 2025</p>
                </div>
                <div class="booking-detail">
                    <label>Waktu:</label>
                    <p id="detail-time">07:00 - 10:30</p>
                </div>
                <div class="booking-detail">
                    <label>Peminjam:</label>
                    <p id="detail-booker">Team Data Analytics</p>
                </div>
                <div class="booking-detail">
                    <label>Keperluan:</label>
                    <p id="detail-purpose">Weekly Team Meeting & Planning</p>
                </div>
                <div class="booking-detail">
                    <label>Status:</label>
                    <p id="detail-status">Confirmed</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel-booking-modal">Batalkan Booking</button>
                <button class="btn-modal btn-close-modal">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Add Booking -->
    <div class="modal-overlay" id="add-booking-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Tambah Booking Baru</div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="booking-date">Tanggal:</label>
                    <select id="booking-date" class="form-control">
                        <!-- Opsi tanggal akan diisi oleh JavaScript -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="booking-time">Waktu:</label>
                    <div class="time-inputs">
                        <select id="start-hour" class="form-control">
                            <!-- Opsi jam mulai akan diisi oleh JavaScript -->
                        </select>
                        <select id="start-minute" class="form-control">
                            <option value="00">00</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                        </select>
                        <span style="line-height: 38px;">-</span>
                        <select id="end-hour" class="form-control">
                            <!-- Opsi jam selesai akan diisi oleh JavaScript -->
                        </select>
                        <select id="end-minute" class="form-control">
                            <option value="00">00</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="booker-name">Nama Peminjam:</label>
                    <input type="text" id="booker-name" class="form-control" placeholder="Masukkan nama peminjam">
                </div>
                <div class="form-group">
                    <label for="booking-purpose">Keperluan:</label>
                    <input type="text" id="booking-purpose" class="form-control"
                        placeholder="Masukkan keperluan booking">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-add-booking-modal" id="submit-booking">Simpan Booking</button>
                <button class="btn-modal btn-close-modal">Batal</button>
            </div>
        </div>
    </div>
<script>
    // Data contoh booking (Senin 1 Desember 2025 jam 07:00-10:30)
    const sampleBookings = [
        {
            id: 1,
            date: '2025-12-01', // Senin 1 Desember 2025
            startTime: '07:00',
            endTime: '10:30',
            booker: 'Team Data Analytics',
            purpose: 'Weekly Team Meeting & Planning Session',
            status: 'Confirmed',
            color: 'rgba(66, 133, 244, 0.15)',
            borderColor: 'rgba(66, 133, 244, 0.5)'
        },
        {
            id: 2,
            date: '2025-12-02', // Selasa 2 Desember
            startTime: '14:00',
            endTime: '16:30',
            booker: 'Workshop Python',
            purpose: 'Python Programming Workshop for Beginners',
            status: 'Confirmed',
            color: 'rgba(76, 175, 80, 0.15)',
            borderColor: 'rgba(76, 175, 80, 0.5)'
        },
        {
            id: 3,
            date: '2025-12-03', // Rabu 3 Desember
            startTime: '09:00',
            endTime: '12:00',
            booker: 'Kunjungan Sekolah',
            purpose: 'Kunjungan MA MIFTAHUL HUDA PURWOSARI',
            status: 'Confirmed',
            color: 'rgba(255, 152, 0, 0.15)',
            borderColor: 'rgba(255, 152, 0, 0.5)'
        },
        {
            id: 4,
            date: '2025-12-04', // Kamis 4 Desember
            startTime: '13:00',
            endTime: '17:00',
            booker: 'Data Visualization Training',
            purpose: 'Advanced Data Visualization Techniques',
            status: 'Confirmed',
            color: 'rgba(156, 39, 176, 0.15)',
            borderColor: 'rgba(156, 39, 176, 0.5)'
        },
        {
            id: 5,
            date: '2025-12-05', // Jumat 5 Desember
            startTime: '08:30',
            endTime: '11:00',
            booker: 'Research Meeting',
            purpose: 'Monthly Research Progress Meeting',
            status: 'Confirmed',
            color: 'rgba(244, 67, 54, 0.15)',
            borderColor: 'rgba(244, 67, 54, 0.5)'
        },
        {
            id: 6,
            date: '2025-12-01', // Senin 1 Desember
            startTime: '15:00',
            endTime: '18:30',
            booker: 'Data Analysis Workshop',
            purpose: 'Advanced Data Analysis Techniques',
            status: 'Confirmed',
            color: 'rgba(33, 150, 243, 0.15)',
            borderColor: 'rgba(33, 150, 243, 0.5)'
        },
        {
            id: 7,
            date: '2025-12-02', // Selasa 2 Desember
            startTime: '19:00',
            endTime: '21:00',
            booker: 'Night Study Session',
            purpose: 'Group Study for Final Exams',
            status: 'Confirmed',
            color: 'rgba(103, 58, 183, 0.15)',
            borderColor: 'rgba(103, 58, 183, 0.5)'
        }
    ];

    // Variabel global untuk menyimpan tanggal saat ini
    let currentDate = new Date(2025, 11, 1); // 1 Desember 2025

    // Variabel untuk melacak booking yang sudah ditampilkan
    let renderedBookings = new Set();

    // Konstanta untuk perhitungan posisi (07:00 - 21:00)
    const START_HOUR = 7;
    const END_HOUR = 21;
    const TOTAL_HOURS = END_HOUR - START_HOUR;
    const PIXELS_PER_HOUR = 60; // 60px per jam
    const TOTAL_HEIGHT = TOTAL_HOURS * PIXELS_PER_HOUR; // 14 jam × 60px = 840px

    // Fungsi untuk mendapatkan nama bulan dalam bahasa Indonesia
    function getMonthName(monthIndex) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return months[monthIndex];
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Setup navbar
        setupNavbar();

        // Generate table data
        generateTableData();

        // Event listeners untuk navigasi minggu
        document.getElementById('prev-week').addEventListener('click', function () {
            changeWeek(-5);
        });

        document.getElementById('next-week').addEventListener('click', function () {
            changeWeek(5);
        });

        // Event listener untuk tombol Today
        document.getElementById('today-btn').addEventListener('click', function () {
            goToToday();
        });

        // Event listeners untuk modal
        setupModalEvents();

        // Isi opsi jam (07:00 - 21:00)
        generateHourOptions();
    });

    // Fungsi untuk setup navbar - YANG SUDAH DIPERBAIKI
    function setupNavbar() {
        // Set active menu berdasarkan URL saat ini
        setActiveMenuBasedOnURL();

        // Setup dropdown behavior
        setupDropdownBehavior();
    }

    // Fungsi untuk set menu aktif berdasarkan URL - YANG SUDAH DIPERBAIKI
    function setActiveMenuBasedOnURL() {
        const currentPath = window.location.pathname;
        const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);
        
        // Remove active class dari semua item
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });

        // Cari menu yang sesuai dengan halaman saat ini
        const allNavLinks = document.querySelectorAll('.nav-link');
        let foundActive = false;

        allNavLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                // Ambil nama file dari href
                const hrefPage = href.split('/').pop();
                
                // Cek apakah link ini mengarah ke halaman saat ini
                if (hrefPage === currentPage || 
                    (currentPage === '' && (hrefPage === 'index.html' || hrefPage === '../index.html')) ||
                    (currentPage === 'index.html' && (hrefPage === '' || hrefPage === '../index.html'))) {
                    
                    link.classList.add('active');
                    const parentItem = link.closest('.nav-item');
                    if (parentItem) {
                        parentItem.classList.add('active');
                    }
                    foundActive = true;
                }
                
                // Cek untuk dropdown items
                if (link.classList.contains('dropdown-toggle')) {
                    const dropdownMenu = link.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
                        dropdownItems.forEach(item => {
                            const itemHref = item.getAttribute('href');
                            if (itemHref) {
                                const itemPage = itemHref.split('/').pop();
                                if (itemPage === currentPage) {
                                    link.classList.add('active');
                                    const parentItem = link.closest('.nav-item');
                                    if (parentItem) {
                                        parentItem.classList.add('active');
                                    }
                                    foundActive = true;
                                }
                            }
                        });
                    }
                }
            }
        });

        // Default: Jika tidak ditemukan, aktifkan menu berdasarkan halaman
        if (!foundActive) {
            if (currentPage === 'tableBooking.html') {
                const peminjamanMenu = document.querySelector('.nav-item.dropdown a[href="#"]');
                if (peminjamanMenu && peminjamanMenu.textContent.includes('Peminjaman Lab')) {
                    peminjamanMenu.classList.add('active');
                    const parentItem = peminjamanMenu.closest('.nav-item');
                    if (parentItem) {
                        parentItem.classList.add('active');
                    }
                }
            }
        }
    }

    // Fungsi untuk setup dropdown behavior
    function setupDropdownBehavior() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            // Hapus event listener lama jika ada
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.dropdown');
                const isActive = dropdown.classList.contains('active');
                
                // Tutup semua dropdown lainnya
                document.querySelectorAll('.dropdown').forEach(item => {
                    if (item !== dropdown) {
                        item.classList.remove('active');
                    }
                });
                
                // Toggle dropdown saat ini
                dropdown.classList.toggle('active');
            });
        });

        // Tutup dropdown saat klik di luar
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    }

    // Fungsi untuk kembali ke hari ini
    function goToToday() {
        currentDate = new Date();
        // Set ke hari Senin jika weekend
        const day = currentDate.getDay();
        if (day === 0 || day === 6) { // Minggu atau Sabtu
            const diff = day === 0 ? 1 : 2; // Minggu maju 1 hari, Sabtu maju 2 hari
            currentDate.setDate(currentDate.getDate() + diff);
        }
        generateTableData();
    }

    // Fungsi untuk mengubah minggu
    function changeWeek(days) {
        currentDate.setDate(currentDate.getDate() + days);
        generateTableData();
    }

    // Fungsi untuk mendapatkan nama hari dalam bahasa Indonesia
    function getDayName(dayIndex) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return days[dayIndex];
    }

    // Fungsi untuk mendapatkan nama hari dalam bahasa Inggris (pendek)
    function getShortDayName(dayIndex) {
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days[dayIndex];
    }

    // Fungsi untuk mendapatkan nama bulan dalam bahasa Inggris (pendek)
    function getShortMonthName(monthIndex) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months[monthIndex];
    }

    // Fungsi untuk memformat tanggal menjadi YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Fungsi untuk menghitung posisi vertikal berdasarkan waktu
    function calculatePosition(time) {
        const [hour, minute] = time.split(':').map(Number);
        const hoursFromStart = hour - START_HOUR;
        const minutesFraction = minute / 60;
        return (hoursFromStart + minutesFraction) * PIXELS_PER_HOUR;
    }

    // Fungsi untuk menghitung tinggi berdasarkan durasi
    function calculateHeight(startTime, endTime) {
        const startPos = calculatePosition(startTime);
        const endPos = calculatePosition(endTime);
        return endPos - startPos;
    }

    // Fungsi untuk menghasilkan data tabel
    function generateTableData() {
        const tableHead = document.querySelector('.booking-table thead tr');
        const tableBody = document.querySelector('.booking-table tbody');

        // Reset rendered bookings
        renderedBookings.clear();

        // Kosongkan tabel
        while (tableHead.children.length > 1) {
            tableHead.removeChild(tableHead.lastChild);
        }
        tableBody.innerHTML = '';

        // Hitung tanggal Senin-Jumat
        const mondayDate = new Date(currentDate);
        // Pastikan kita mulai dari Senin
        const dayOfWeek = mondayDate.getDay();
        const diff = dayOfWeek === 0 ? 1 : (dayOfWeek === 6 ? 2 : 1 - dayOfWeek);
        mondayDate.setDate(mondayDate.getDate() + diff);

        const fridayDate = new Date(mondayDate);
        fridayDate.setDate(fridayDate.getDate() + 4);

        // Update teks rentang tanggal
        document.getElementById('date-range').textContent =
            `${getShortMonthName(mondayDate.getMonth())} ${mondayDate.getDate()} - ${fridayDate.getDate()}, ${fridayDate.getFullYear()}`;

        // Buat header untuk 5 hari kerja (Senin-Jumat)
        for (let i = 0; i < 5; i++) {
            const date = new Date(mondayDate);
            date.setDate(date.getDate() + i);

            const th = document.createElement('th');
            th.classList.add('day-header');

            // Tambahkan kelas today jika hari ini
            const today = new Date();
            if (date.toDateString() === today.toDateString()) {
                th.classList.add('today');
            }

            // Format: Mon 12/1
            th.textContent = `${getShortDayName(date.getDay())} ${date.getDate()}/${date.getMonth() + 1}`;
            th.setAttribute('data-date', formatDate(date));
            tableHead.appendChild(th);
        }

        // Buat satu baris untuk container hari (bukan banyak baris per jam)
        const containerRow = document.createElement('tr');

        // Kolom waktu (hanya satu sel untuk waktu)
        const timeCell = document.createElement('td');
        timeCell.classList.add('time-header');

        // Buat daftar waktu di kolom kiri (07:00 - 21:00)
        const timeList = document.createElement('div');
        timeList.style.position = 'relative';
        timeList.style.height = `${TOTAL_HEIGHT}px`;

        // Tambahkan label waktu setiap jam dari 07:00 sampai 21:00
        for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
            const timeLabel = document.createElement('div');
            timeLabel.className = 'time-label';
            timeLabel.textContent = `${String(hour).padStart(2, '0')}:00`;
            timeLabel.style.top = `${(hour - START_HOUR) * PIXELS_PER_HOUR}px`;
            timeList.appendChild(timeLabel);
        }

        timeCell.appendChild(timeList);
        timeCell.rowSpan = 1; // Hanya satu baris
        containerRow.appendChild(timeCell);

        // Kolom untuk setiap hari (SENIN-JUMAT)
        for (let day = 0; day < 5; day++) {
            const date = new Date(mondayDate);
            date.setDate(date.getDate() + day);
            const dateStr = formatDate(date);

            const cell = document.createElement('td');
            cell.classList.add('day-cell', 'empty');
            cell.setAttribute('data-date', dateStr);

            // Buat container untuk hari ini
            const container = document.createElement('div');
            container.className = 'day-container';
            container.style.height = `${TOTAL_HEIGHT}px`;

            // Tambahkan grid lines untuk waktu (07:00 - 21:00)
            const timeGrid = document.createElement('div');
            timeGrid.className = 'time-grid';

            // Tambahkan garis setiap jam dari 07:00 sampai 21:00
            for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
                const timeLine = document.createElement('div');
                timeLine.className = 'time-line';
                timeLine.style.top = `${(hour - START_HOUR) * PIXELS_PER_HOUR}px`;
                timeGrid.appendChild(timeLine);
            }

            container.appendChild(timeGrid);
            cell.appendChild(container);
            containerRow.appendChild(cell);
        }

        tableBody.appendChild(containerRow);

        // Render semua booking ke dalam tabel
        renderAllBookings();

        // Update opsi tanggal di modal add booking
        updateBookingDateOptions();
    }

    // Fungsi untuk merender semua booking
    function renderAllBookings() {
        // Hapus semua booking rectangle yang ada
        document.querySelectorAll('.booking-rectangle').forEach(rect => rect.remove());

        // Reset rendered bookings
        renderedBookings.clear();

        // Render setiap booking
        sampleBookings.forEach(booking => {
            renderBooking(booking);
        });
    }

    // Fungsi untuk merender satu booking
    function renderBooking(booking) {
        if (renderedBookings.has(booking.id)) return;

        const date = new Date(booking.date);
        const mondayDate = new Date(currentDate);
        const dayOfWeek = mondayDate.getDay();
        const diff = dayOfWeek === 0 ? 1 : (dayOfWeek === 6 ? 2 : 1 - dayOfWeek);
        mondayDate.setDate(mondayDate.getDate() + diff);

        // Hitung indeks hari (0-4 untuk Senin-Jumat)
        const dayIndex = Math.floor((date - mondayDate) / (1000 * 60 * 60 * 24));

        // Cek apakah booking dalam rentang hari Senin-Jumat
        if (dayIndex < 0 || dayIndex > 4) return;

        // Hitung posisi dan tinggi rectangle
        const topPosition = calculatePosition(booking.startTime);
        const height = calculateHeight(booking.startTime, booking.endTime);

        // Cek jika posisi di luar rentang 07:00-21:00
        if (topPosition < 0 || topPosition + height > TOTAL_HEIGHT) return;

        // Cari container untuk hari ini (kolom ke dayIndex + 1 karena kolom 0 adalah waktu)
        const containers = document.querySelectorAll('.day-container');
        if (containers.length <= dayIndex) return;

        const targetContainer = containers[dayIndex];

        // Buat rectangle untuk booking
        const rectangle = document.createElement('div');
        rectangle.className = 'booking-rectangle';
        rectangle.innerHTML = `
        <div class="booking-time">${booking.startTime} - ${booking.endTime}</div>
        <div class="booking-title">${booking.booker}</div>
        <div class="booking-desc">${booking.purpose}</div>
    `;

        // Set posisi dan ukuran
        rectangle.style.position = 'absolute';
        rectangle.style.top = `${topPosition}px`;
        rectangle.style.height = `${height}px`;
        rectangle.style.backgroundColor = booking.color || 'rgba(66, 133, 244, 0.15)';
        rectangle.style.borderColor = booking.borderColor || 'rgba(66, 133, 244, 0.5)';
        rectangle.setAttribute('data-booking-id', booking.id);

        // Tambahkan event listener untuk detail booking
        rectangle.addEventListener('click', function (e) {
            e.stopPropagation();
            showBookingDetail(booking);
        });

        // Tambahkan ke dalam container
        targetContainer.appendChild(rectangle);

        renderedBookings.add(booking.id);
    }

    // Fungsi untuk menampilkan modal detail booking
    function showBookingDetail(booking) {
        const modal = document.getElementById('booking-detail-modal');

        // Format tanggal untuk ditampilkan
        const date = new Date(booking.date);
        const formattedDate = `${getDayName(date.getDay())}, ${date.getDate()} ${getMonthName(date.getMonth())} ${date.getFullYear()}`;

        // Isi data di modal
        document.getElementById('detail-date').textContent = formattedDate;
        document.getElementById('detail-time').textContent = `${booking.startTime} - ${booking.endTime}`;
        document.getElementById('detail-booker').textContent = booking.booker;
        document.getElementById('detail-purpose').textContent = booking.purpose;
        document.getElementById('detail-status').textContent = booking.status;

        // Tampilkan modal
        modal.style.display = 'flex';
    }

    // Fungsi untuk membuka modal add booking
    function openAddBookingModal(date, time) {
        const modal = document.getElementById('add-booking-modal');

        // Set nilai default
        document.getElementById('booking-date').value = date;

        // Parse waktu untuk set nilai default
        const [hour, minute] = time ? time.split(':') : ['08', '00'];
        document.getElementById('start-hour').value = hour;
        document.getElementById('start-minute').value = minute;

        // Set end time default (1 jam setelah start time)
        let endHour = parseInt(hour) + 1;
        if (endHour > 21) endHour = 21;
        document.getElementById('end-hour').value = String(endHour).padStart(2, '0');
        document.getElementById('end-minute').value = minute;

        // Kosongkan field lainnya
        document.getElementById('booker-name').value = '';
        document.getElementById('booking-purpose').value = '';

        // Tampilkan modal
        modal.style.display = 'flex';
    }

    // Fungsi untuk mengatur event listener modal
    function setupModalEvents() {
        // Modal detail booking
        const detailModal = document.getElementById('booking-detail-modal');
        const addModal = document.getElementById('add-booking-modal');

        // Tombol tutup modal
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                detailModal.style.display = 'none';
                addModal.style.display = 'none';
            });
        });

        // Tombol tutup di footer modal
        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                detailModal.style.display = 'none';
                addModal.style.display = 'none';
            });
        });

        // Tutup modal ketika klik di luar konten modal
        window.addEventListener('click', function (event) {
            if (event.target === detailModal) {
                detailModal.style.display = 'none';
            }
            if (event.target === addModal) {
                addModal.style.display = 'none';
            }
        });

        // Tombol add booking di header
        document.getElementById('open-add-booking').addEventListener('click', function () {
            const today = new Date();
            const todayStr = formatDate(today);
            openAddBookingModal(todayStr, '08:00');
        });

        // Submit booking baru
        document.getElementById('submit-booking').addEventListener('click', function () {
            const date = document.getElementById('booking-date').value;
            const startHour = document.getElementById('start-hour').value;
            const startMinute = document.getElementById('start-minute').value;
            const endHour = document.getElementById('end-hour').value;
            const endMinute = document.getElementById('end-minute').value;
            const booker = document.getElementById('booker-name').value;
            const purpose = document.getElementById('booking-purpose').value;

            // Validasi input
            if (!booker || !purpose) {
                alert('Nama peminjam dan keperluan harus diisi!');
                return;
            }

            // Validasi waktu
            const startTimeNum = parseInt(startHour) * 60 + parseInt(startMinute);
            const endTimeNum = parseInt(endHour) * 60 + parseInt(endMinute);

            if (endTimeNum <= startTimeNum) {
                alert('Waktu selesai harus setelah waktu mulai!');
                return;
            }

            // Validasi rentang waktu (07:00 - 21:00)
            if (startTimeNum < 7 * 60 || endTimeNum > 21 * 60) {
                alert('Booking hanya bisa dilakukan antara jam 07:00 - 21:00!');
                return;
            }

            // Buat booking baru
            const newBooking = {
                id: sampleBookings.length + 1,
                date: date,
                startTime: `${startHour}:${startMinute}`,
                endTime: `${endHour}:${endMinute}`,
                booker: booker,
                purpose: purpose,
                status: 'Confirmed',
                color: 'rgba(233, 30, 99, 0.15)',
                borderColor: 'rgba(233, 30, 99, 0.5)'
            };

            // Tambahkan ke data contoh
            sampleBookings.push(newBooking);

            // Render booking baru
            renderBooking(newBooking);

            // Tutup modal
            addModal.style.display = 'none';

            alert('Booking berhasil ditambahkan!');
        });
    }

    // Fungsi untuk mengupdate opsi tanggal di modal add booking
    function updateBookingDateOptions() {
        const dateSelect = document.getElementById('booking-date');
        dateSelect.innerHTML = '';

        // Hitung tanggal Senin-Jumat
        const mondayDate = new Date(currentDate);
        const dayOfWeek = mondayDate.getDay();
        const diff = dayOfWeek === 0 ? 1 : (dayOfWeek === 6 ? 2 : 1 - dayOfWeek);
        mondayDate.setDate(mondayDate.getDate() + diff);

        for (let i = 0; i < 5; i++) {
            const date = new Date(mondayDate);
            date.setDate(date.getDate() + i);

            const option = document.createElement('option');
            option.value = formatDate(date);
            option.textContent = `${getDayName(date.getDay())}, ${date.getDate()} ${getMonthName(date.getMonth())} ${date.getFullYear()}`;

            dateSelect.appendChild(option);
        }
    }

    // Fungsi untuk generate opsi jam (07:00 - 21:00)
    function generateHourOptions() {
        const startHourSelect = document.getElementById('start-hour');
        const endHourSelect = document.getElementById('end-hour');

        startHourSelect.innerHTML = '';
        endHourSelect.innerHTML = '';

        // Generate opsi dari jam 07:00 sampai 21:00
        for (let i = 7; i <= 21; i++) {
            const hourStr = String(i).padStart(2, '0');

            const option1 = document.createElement('option');
            option1.value = hourStr;
            option1.textContent = hourStr + ':00';
            startHourSelect.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = hourStr;
            option2.textContent = hourStr + ':00';
            endHourSelect.appendChild(option2);
        }

        // Set nilai default
        startHourSelect.value = '08';
        endHourSelect.value = '09';
    }
</script>
</body>

</html>