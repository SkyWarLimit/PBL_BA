// ==================== js/admin.js - FULL FINAL VERSION ====================
const API_URL = 'api/';

// ==================== TAB SWITCHING & INITIAL LOAD ====================
document.addEventListener('DOMContentLoaded', function () {
    // Tab navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const tab = this.dataset.tab;

            if (tab === 'logout') {
                if (confirm('Yakin ingin logout?')) window.location.href = 'logout.php';
                return;
            }

            // Update active tab
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Show selected tab
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('d-none'));
            document.getElementById(tab + 'Tab').classList.remove('d-none');

            // Load data sesuai tab
            if (tab === 'beranda') loadDashboard();
            if (tab === 'peminjaman') loadPeminjaman();
            if (tab === 'kontak') loadKontak();
            if (tab === 'pengaturan') loadSettings();
            if (tab === 'anggota') loadAnggota();
            if (tab === 'sosmed') loadSosmed();
            if (tab === 'galeri') loadGaleri();
            if (tab === 'artikel') loadArtikel();
        });
    });

    // Support ?tab= dari URL (misal: admin.php?tab=peminjaman)
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && document.querySelector(`.nav-link[data-tab="${tab}"]`)) {
        document.querySelector(`.nav-link[data-tab="${tab}"]`).click();
    } else {
        loadDashboard(); // Default load dashboard
    }
});

// ==================== DASHBOARD ====================
function loadDashboard() {
    fetch(API_URL + 'peminjaman.php?action=stats')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statPending').textContent = data.data.pending;
                document.getElementById('statPembatalan').textContent = data.data.pembatalan;
                document.getElementById('statKontak').textContent = data.data.kontak;
                document.getElementById('statApproved').textContent = data.data.approved;

                const totalNotif = parseInt(data.data.pending) + parseInt(data.data.pembatalan);
                const totalBadge = document.getElementById('totalNotif');
                const kontakBadge = document.getElementById('kontakNotif');

                totalBadge.textContent = totalNotif > 99 ? '99+' : totalNotif;
                kontakBadge.textContent = data.data.kontak;

                totalBadge.classList.toggle('d-none', totalNotif === 0);
                kontakBadge.classList.toggle('d-none', data.data.kontak == 0);
            }
        });

    loadRecentActivities();
}

// ==================== RECENT ACTIVITIES ====================
function loadRecentActivities() {
    const tbody = document.getElementById('recentActivities');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Memuat...</td></tr>';

    Promise.all([
        fetch(API_URL + 'peminjaman.php?action=list&status=pending').then(r => r.json()),
        fetch(API_URL + 'peminjaman.php?action=list').then(r => r.json()).then(d => ({
            success: true,
            data: d.data.filter(p => ['t', true, 'true', 1, '1'].includes(p.request_pembatalan))
        })),
        fetch(API_URL + 'kontak.php?action=list').then(r => r.json()).then(d => ({
            success: true,
            data: d.data.filter(k => !['t', true, 'true', 1, '1'].includes(k.is_read))
        }))
    ]).then(([pending, batalReq, kontak]) => {
        let activities = [];

        // Peminjaman Baru
        pending.data?.forEach(p => {
            activities.push({
                type: 'Peminjaman Baru',
                badge: 'bg-warning',
                nama: p.peminjam,
                detail: `${p.tanggal_peminjaman} | ${p.waktu || '-'}`,
                tanggal: p.created_at || p.tanggal_peminjaman,
                tombol: `<button class="btn btn-sm btn-primary" onclick="viewDetail(${p.id_peminjaman})">
                            <i class="bi bi-eye"></i> Lihat
                         </button>`
            });
        });

        // Request Pembatalan
        batalReq.data?.forEach(p => {
            activities.push({
                type: 'Request Pembatalan',
                badge: 'bg-danger',
                nama: p.peminjam,
                detail: `Request batal: ${p.tanggal_peminjaman}`,
                tanggal: p.updated_at || p.created_at,
                tombol: `<button class="btn btn-sm btn-primary me-1" onclick="viewDetail(${p.id_peminjaman})">
                            <i class="bi bi-eye"></i> Lihat
                         </button>
                         <button class="btn btn-sm btn-success btn-xs" onclick="cancelBooking(${p.id_peminjaman})">
                            <i class="bi bi-check"></i>
                         </button>
                         <button class="btn btn-sm btn-secondary btn-xs" onclick="rejectCancel(${p.id_peminjaman})">
                            <i class="bi bi-x"></i>
                         </button>`
            });
        });

        // Pesan Kontak Baru
        kontak.data?.forEach(k => {
            activities.push({
                type: 'Pesan Baru',
                badge: 'bg-info',
                nama: k.nama,
                detail: k.subjek || '(tanpa subjek)',
                tanggal: k.created_at,
                tombol: `<button class="btn btn-sm btn-primary" onclick="viewKontak(${k.id_kontak})">
                            <i class="bi bi-eye"></i> Lihat
                         </button>`
            });
        });

        // Urutkan dari terbaru
        activities.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));

        tbody.innerHTML = activities.length === 0 
            ? '<tr><td colspan="5" class="text-center text-muted">Tidak ada aktivitas terbaru</td></tr>'
            : activities.map(a => `
                <tr>
                    <td><span class="badge ${a.badge}">${a.type}</span></td>
                    <td><strong>${a.nama}</strong></td>
                    <td>${a.detail}</td>
                    <td>${new Date(a.tanggal).toLocaleString('id-ID')}</td>
                    <td>${a.tombol}</td>
                </tr>
            `).join('');
    }).catch(err => {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Gagal memuat aktivitas</td></tr>';
        console.error(err);
    });
}

// ==================== PEMINJAMAN ====================
function loadPeminjaman() {
    const status = document.getElementById('filterStatus')?.value || '';
    const tanggal = document.getElementById('filterTanggal')?.value || '';
    const search = document.getElementById('searchPeminjam')?.value || '';

    let url = API_URL + 'peminjaman.php?action=list';
    if (status) url += '&status=' + status;
    if (tanggal) url += '&tanggal=' + tanggal;
    if (search) url += '&search=' + search;

    const list = document.getElementById('peminjamanList');
    list.innerHTML = '<tr><td colspan="7" class="text-center">Memuat...</td></tr>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            list.innerHTML = '';

            if (!data.success || data.data.length === 0) {
                list.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data</td></tr>';
                return;
            }

            data.data.forEach(p => {
                // FIX: Convert PostgreSQL boolean properly
                const isRequestBatal = p.request_pembatalan === 't' || p.request_pembatalan === true || p.request_pembatalan === 'true';
                
                let statusText = '';
                let badgeClass = 'bg-secondary';

                // Prioritas: Jika ada request pembatalan, tampilkan itu dulu
                if (isRequestBatal) {
                    statusText = 'Request Pembatalan';
                    badgeClass = 'bg-danger';
                } else {
                    switch (p.status) {
                        case 'pending': 
                            statusText = 'Pending'; 
                            badgeClass = 'bg-warning text-dark'; 
                            break;
                        case 'approved': 
                            statusText = 'Approved'; 
                            badgeClass = 'bg-success'; 
                            break;
                        case 'rejected': 
                            statusText = 'Rejected'; 
                            badgeClass = 'bg-danger'; 
                            break;
                        case 'cancelled': 
                            statusText = 'Cancelled'; 
                            badgeClass = 'bg-dark'; 
                            break;
                        default: 
                            statusText = p.status;
                    }
                }

                let actionButtons = `<button class="btn btn-sm btn-info action-btn" onclick="viewDetail(${p.id_peminjaman})"><i class="bi bi-eye"></i></button>`;

                // Tombol untuk status pending (belum ada request batal)
                if (p.status === 'pending' && !isRequestBatal) {
                    actionButtons += `
                        <button class="btn btn-sm btn-success action-btn" onclick="approve(${p.id_peminjaman})">
                            <i class="bi bi-check"></i> Setujui
                        </button>
                        <button class="btn btn-sm btn-danger action-btn" onclick="reject(${p.id_peminjaman})">
                            <i class="bi bi-x"></i> Tolak
                        </button>
                    `;
                }

                // Tombol khusus untuk request pembatalan
                if (isRequestBatal) {
                    actionButtons += `
                        <button class="btn btn-sm btn-danger action-btn" onclick="cancelBooking(${p.id_peminjaman})">
                            <i class="bi bi-check"></i> Batalkan
                        </button>
                        <button class="btn btn-sm btn-secondary action-btn" onclick="rejectCancel(${p.id_peminjaman})">
                            <i class="bi bi-x"></i> Tolak Batal
                        </button>
                    `;
                }

                list.innerHTML += `
                    <tr>
                        <td>#${p.id_peminjaman}</td>
                        <td>${p.peminjam}<br><small class="text-muted">${p.email || '-'}</small></td>
                        <td>${p.tanggal_peminjaman}</td>
                        <td>${p.waktu || '-'}</td>
                        <td><span class="badge ${badgeClass}">${statusText}</span></td>
                        <td>${p.tujuan}</td>
                        <td>${actionButtons}</td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            list.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
            console.error(err);
        });
}

// ==================== KONTAK ====================
function loadKontak() {
    fetch(API_URL + 'kontak.php?action=list')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('kontakList');
            list.innerHTML = '';

            if (!data.success || data.data.length === 0) {
                list.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Tidak ada pesan</td></tr>';
                return;
            }

            data.data.forEach(k => {
                const isRead = ['t', true, 'true', 1, '1'].includes(k.is_read);
                
                // Pilih ikon amplop sesuai status baca
                const iconClass = isRead 
                    ? 'bi-envelope-open text-success'   // Sudah dibaca → amplop terbuka
                    : 'bi-envelope-fill text-danger';   // Belum dibaca → amplop tertutup + merah

                const statusBadge = isRead
                    ? '<span class="badge bg-success">Sudah Dibaca</span>'
                    : '<span class="badge bg-danger">Belum Dibaca</span>';

                list.innerHTML += `
                    <tr>
                        <td>${statusBadge}</td>
                        <td><strong>${k.nama}</strong></td>
                        <td>${k.email}</td>
                        <td>${k.subjek || '<em class="text-muted">(tanpa subjek)</em>'}</td>
                        <td>${new Date(k.created_at).toLocaleDateString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewKontak(${k.id_kontak})" title="${isRead ? 'Lihat kembali' : 'Baca pesan'}">
                                <i class="bi ${iconClass}"></i> Lihat
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            list.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat pesan</td></tr>';
            console.error(err);
        });
}

// ==================== PENGATURAN ====================
function loadSettings() {
    fetch(API_URL + 'settings.php?action=get')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('logoPreview').src = data.data.logo || 'uploads/logo.png';
                document.getElementById('namaLab').value = data.data.nama_lab || '';
                document.getElementById('alamat').value = data.data.alamat || '';
                document.getElementById('emailLab').value = data.data.email || '';
                document.getElementById('telpLab').value = data.data.telp || '';
            }
        })
        .catch(err => console.error('Error:', err));
}

// ==================== ANGGOTA ====================
function loadAnggota() {
    fetch(API_URL + 'anggota.php?action=list')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('anggotaList');
            list.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(a => {
                    list.innerHTML += `
                        <div class="col-md-6 col-lg-4">
                            <div class="anggota-card">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="${a.foto || 'https://via.placeholder.com/100'}" class="anggota-foto me-3" alt="${a.nama}">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${a.nama}</h6>
                                        <p class="text-muted mb-0 small">${a.jabatan}</p>
                                        ${a.email ? `<p class="text-muted mb-0 small">${a.email}</p>` : ''}
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning flex-grow-1" onclick="editAnggota(${a.id_anggota})">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteAnggota(${a.id_anggota})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                list.innerHTML = '<div class="col-12"><div class="alert alert-info">Belum ada anggota</div></div>';
            }
        })
        .catch(err => console.error('Error:', err));
}

// ==================== SOSMED ====================
function loadSosmed() {
    fetch(API_URL + 'sosmed.php?action=list')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('sosmedList');
            list.innerHTML = '';

            if (data.success && data.data.length > 0) {
                const icons = {
                    instagram: 'bi-instagram',
                    facebook: 'bi-facebook',
                    twitter: 'bi-twitter-x',
                    youtube: 'bi-youtube',
                    linkedin: 'bi-linkedin',
                    tiktok: 'bi-tiktok'
                };

                data.data.forEach(s => {
                    list.innerHTML += `
                        <div class="sosmed-item">
                            <i class="bi ${icons[s.platform]} sosmed-icon"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${s.platform.charAt(0).toUpperCase() + s.platform.slice(1)}</h6>
                                <p class="mb-0 small text-muted">${s.username || '-'}</p>
                                <a href="${s.link_url}" target="_blank" class="small">${s.link_url}</a>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-warning me-2" onclick="editSosmed(${s.id_sosmed})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSosmed(${s.id_sosmed})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                list.innerHTML = '<div class="alert alert-info">Belum ada sosial media</div>';
            }
        })
        .catch(err => console.error('Error:', err));
}

// ==================== GALERI ====================
function loadGaleri() {
    const kategori = document.getElementById('filterKategoriGaleri').value;
    const search = document.getElementById('searchGaleri').value;

    let url = API_URL + 'galeri.php?action=list';
    if (kategori) url += '&kategori=' + kategori;
    if (search) url += '&search=' + search;

    const grid = document.getElementById('galeriGrid');
    grid.innerHTML = '<div class="col-12"><p class="text-center">Memuat...</p></div>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            grid.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(g => {
                    grid.innerHTML += `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="${g.file_path}" class="card-img-top" alt="${g.judul}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">${g.judul}</h6>
                                    <p class="card-text small text-muted">${g.deskripsi || '-'}</p>
                                    <span class="badge bg-info">${g.kategori || 'Tanpa Kategori'}</span>
                                    <p class="card-text small text-muted mt-2">
                                        <i class="bi bi-calendar"></i> ${new Date(g.tanggal_upload).toLocaleDateString('id-ID')}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning flex-grow-1" onclick="editGaleri(${g.id_galeri})">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteGaleri(${g.id_galeri})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info">Belum ada foto</div></div>';
            }
        })
        .catch(err => {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Gagal memuat data</div></div>';
            console.error('Error:', err);
        });
}

// ==================== ARTIKEL ====================
function loadArtikel() {
    const kategori = document.getElementById('filterKategoriArtikel').value;
    const search = document.getElementById('searchArtikel').value;

    let url = API_URL + 'artikel.php?action=list';
    if (kategori) url += '&kategori=' + kategori;
    if (search) url += '&search=' + search;

    const list = document.getElementById('artikelList');
    list.innerHTML = '<tr><td colspan="6" class="text-center">Memuat...</td></tr>';

    fetch(url)
        .then(r => r.json())
        .then(data => {
            list.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(a => {
                    const statusBadge = a.is_published == 't' || a.is_published == true 
                        ? '<span class="badge bg-success">Published</span>' 
                        : '<span class="badge bg-secondary">Draft</span>';

                    list.innerHTML += `
                        <tr>
                            <td>
                                <strong>${a.judul}</strong>
                                ${a.ringkasan ? '<br><small class="text-muted">' + a.ringkasan.substring(0, 100) + '...</small>' : ''}
                            </td>
                            <td><span class="badge bg-info">${a.kategori || '-'}</span></td>
                            <td>${statusBadge}</td>
                            <td><i class="bi bi-eye"></i> ${a.views || 0}</td>
                            <td>${new Date(a.tanggal_upload).toLocaleDateString('id-ID')}</td>
                            <td>
                                <button class="btn btn-sm btn-info action-btn" onclick="viewArtikel(${a.id_artikel})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning action-btn" onclick="editArtikel(${a.id_artikel})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-${a.is_published == 't' || a.is_published == true ? 'secondary' : 'success'} action-btn" 
                                        onclick="togglePublishArtikel(${a.id_artikel})">
                                    <i class="bi bi-${a.is_published == 't' || a.is_published == true ? 'eye-slash' : 'check'}"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteArtikel(${a.id_artikel})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                list.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada artikel</td></tr>';
            }
        })
        .catch(err => {
            list.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>';
            console.error('Error:', err);
        });
}

// ==================== PREVIEW FUNCTIONS ====================
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('logoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAnggotaFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('anggotaFotoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGaleriFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('galeriFotoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// ==================== FORM SUBMISSIONS ====================
document.getElementById('formLogo').addEventListener('submit', function (e) {
    e.preventDefault();
    const file = document.getElementById('logoFile').files[0];
    if (!file) return alert('Pilih file logo!');
    const formData = new FormData();
    formData.append('logo', file);
    fetch(API_URL + 'settings.php?action=update_logo', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? 'Logo berhasil diupdate!' : 'Gagal: ' + data.message);
            if (data.success) loadSettings();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

document.getElementById('formInfoLab').addEventListener('submit', function (e) {
    e.preventDefault();
    const data = {
        nama_lab: document.getElementById('namaLab').value,
        alamat: document.getElementById('alamat').value,
        email: document.getElementById('emailLab').value,
        telp: document.getElementById('telpLab').value
    };
    fetch(API_URL + 'settings.php?action=update_info', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(data => alert(data.success ? 'Informasi diupdate!' : 'Gagal: ' + data.message))
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

document.getElementById('formAnggota').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData();
    const id = document.getElementById('anggotaId').value;
    const file = document.getElementById('anggotaFoto').files[0];
    if (id) formData.append('id', id);
    if (file) formData.append('foto', file);
    formData.append('nama', document.getElementById('anggotaNama').value);
    formData.append('jabatan', document.getElementById('anggotaJabatan').value);
    formData.append('email', document.getElementById('anggotaEmail').value);
    formData.append('urutan', document.getElementById('anggotaUrutan').value);
    const action = id ? 'update' : 'add';
    fetch(API_URL + `anggota.php?action=${action}`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? data.message : 'Gagal: ' + data.message);
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('anggotaModal')).hide();
                loadAnggota();
            }
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

document.getElementById('formSosmed').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('sosmedId').value;
    const data = {
        id: id || null,
        platform: document.getElementById('sosmedPlatform').value,
        username: document.getElementById('sosmedUsername').value,
        link_url: document.getElementById('sosmedUrl').value
    };
    const action = id ? 'update' : 'add';
    fetch(API_URL + `sosmed.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? data.message : 'Gagal: ' + data.message);
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('sosmedModal')).hide();
                loadSosmed();
            }
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

document.getElementById('formGaleri').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData();
    const id = document.getElementById('galeriId').value;
    const file = document.getElementById('galeriFoto').files[0];
    if (id) formData.append('id', id);
    if (file) formData.append('foto', file);
    formData.append('judul', document.getElementById('galeriJudul').value);
    formData.append('deskripsi', document.getElementById('galeriDeskripsi').value);
    formData.append('kategori', document.getElementById('galeriKategori').value);
    const action = id ? 'update' : 'add';
    fetch(API_URL + `galeri.php?action=${action}`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? data.message : 'Gagal: ' + data.message);
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('galeriModal')).hide();
                loadGaleri();
            }
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

document.getElementById('formArtikel').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData();
    const id = document.getElementById('artikelId').value;
    const file = document.getElementById('artikelFile').files[0];
    if (id) formData.append('id', id);
    if (file) formData.append('file', file);
    formData.append('judul', document.getElementById('artikelJudul').value);
    formData.append('ringkasan', document.getElementById('artikelRingkasan').value);
    formData.append('konten', document.getElementById('artikelKonten').value);
    formData.append('kategori', document.getElementById('artikelKategori').value);
    formData.append('is_published', document.getElementById('artikelPublish').checked);
    const action = id ? 'update' : 'add';
    fetch(API_URL + `artikel.php?action=${action}`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? data.message : 'Gagal: ' + data.message);
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('artikelModal')).hide();
                loadArtikel();
            }
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
});

// ==================== SHOW MODALS ====================
function showAddAnggota() {
    document.getElementById('anggotaModalTitle').textContent = 'Tambah Anggota';
    document.getElementById('formAnggota').reset();
    document.getElementById('anggotaId').value = '';
    document.getElementById('anggotaFotoPreview').src = 'https://via.placeholder.com/150';
    new bootstrap.Modal(document.getElementById('anggotaModal')).show();
}

function showAddSosmed() {
    document.getElementById('formSosmed').reset();
    document.getElementById('sosmedId').value = '';
    new bootstrap.Modal(document.getElementById('sosmedModal')).show();
}

function showAddGaleri() {
    document.getElementById('galeriModalTitle').textContent = 'Tambah Foto';
    document.getElementById('formGaleri').reset();
    document.getElementById('galeriId').value = '';
    document.getElementById('galeriFotoPreview').src = 'https://via.placeholder.com/400x300?text=Preview+Foto';
    document.getElementById('galeriFoto').required = true;
    new bootstrap.Modal(document.getElementById('galeriModal')).show();
}

function showAddArtikel() {
    document.getElementById('artikelModalTitle').textContent = 'Tambah Artikel';
    document.getElementById('formArtikel').reset();
    document.getElementById('artikelId').value = '';
    document.getElementById('artikelPublish').checked = true;
    new bootstrap.Modal(document.getElementById('artikelModal')).show();
}

// ==================== EDIT FUNCTIONS ====================
function editAnggota(id) {
    fetch(API_URL + `anggota.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const a = data.data;
                document.getElementById('anggotaModalTitle').textContent = 'Edit Anggota';
                document.getElementById('anggotaId').value = a.id_anggota;
                document.getElementById('anggotaNama').value = a.nama;
                document.getElementById('anggotaJabatan').value = a.jabatan;
                document.getElementById('anggotaEmail').value = a.email || '';
                document.getElementById('anggotaUrutan').value = a.urutan || 0;
                document.getElementById('anggotaFotoPreview').src = a.foto || 'https://via.placeholder.com/150';
                new bootstrap.Modal(document.getElementById('anggotaModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

function editSosmed(id) {
    fetch(API_URL + `sosmed.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const s = data.data;
                document.getElementById('sosmedId').value = s.id_sosmed;
                document.getElementById('sosmedPlatform').value = s.platform;
                document.getElementById('sosmedUsername').value = s.username || '';
                document.getElementById('sosmedUrl').value = s.link_url;
                new bootstrap.Modal(document.getElementById('sosmedModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

function editGaleri(id) {
    fetch(API_URL + `galeri.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const g = data.data;
                document.getElementById('galeriModalTitle').textContent = 'Edit Foto';
                document.getElementById('galeriId').value = g.id_galeri;
                document.getElementById('galeriJudul').value = g.judul;
                document.getElementById('galeriDeskripsi').value = g.deskripsi || '';
                document.getElementById('galeriKategori').value = g.kategori || '';
                document.getElementById('galeriFotoPreview').src = g.file_path;
                document.getElementById('galeriFoto').required = false;
                new bootstrap.Modal(document.getElementById('galeriModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

function editArtikel(id) {
    fetch(API_URL + `artikel.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const a = data.data;
                document.getElementById('artikelModalTitle').textContent = 'Edit Artikel';
                document.getElementById('artikelId').value = a.id_artikel;
                document.getElementById('artikelJudul').value = a.judul;
                document.getElementById('artikelRingkasan').value = a.ringkasan || '';
                document.getElementById('artikelKonten').value = a.konten;
                document.getElementById('artikelKategori').value = a.kategori || '';
                document.getElementById('artikelPublish').checked = a.is_published == 't' || a.is_published == true;
                new bootstrap.Modal(document.getElementById('artikelModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

// ==================== DELETE FUNCTIONS ====================
function deleteAnggota(id) {
    if (!confirm('Yakin menghapus anggota?')) return;
    fetch(API_URL + 'anggota.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? 'Berhasil dihapus!' : 'Gagal: ' + data.message);
            if (data.success) loadAnggota();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
}

function deleteSosmed(id) {
    if (!confirm('Yakin menghapus sosmed?')) return;
    fetch(API_URL + 'sosmed.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? 'Berhasil dihapus!' : 'Gagal: ' + data.message);
            if (data.success) loadSosmed();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
}

function deleteGaleri(id) {
    if (!confirm('Yakin menghapus foto?')) return;
    fetch(API_URL + 'galeri.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? 'Berhasil dihapus!' : 'Gagal: ' + data.message);
            if (data.success) loadGaleri();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
}

function deleteArtikel(id) {
    if (!confirm('Yakin menghapus artikel?')) return;
    fetch(API_URL + 'artikel.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? 'Berhasil dihapus!' : 'Gagal: ' + data.message);
            if (data.success) loadArtikel();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
}

// ==================== VIEW FUNCTIONS ====================
function viewDetail(id) {
    // Implementasi viewDetail peminjaman (dari truncated code)
    fetch(API_URL + `peminjaman.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const p = data.data;
                document.getElementById('modalContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Peminjam:</strong> ${p.peminjam}</p>
                            <p><strong>Email:</strong> ${p.email}</p>
                            <p><strong>Tanggal:</strong> ${p.tanggal_peminjaman}</p>
                            <p><strong>Waktu:</strong> ${p.waktu || '-'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> ${p.status}</p>
                            <p><strong>Tujuan:</strong> ${p.tujuan}</p>
                        </div>
                        ${p.alasan_pembatalan ? `<div class="col-12 mt-3"><p><strong>Alasan Pembatalan:</strong> ${p.alasan_pembatalan}</p></div>` : ''}
                        ${p.catatan_admin ? `<div class="col-12 mt-3"><p><strong>Catatan Admin:</strong> ${p.catatan_admin}</p></div>` : ''}
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

function viewKontak(id) {
    fetch(API_URL + `kontak.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const k = data.data;
                document.getElementById('modalContent').innerHTML = `
                    <div class="mb-3">
                        <p><strong>Dari:</strong> ${k.nama} (${k.email})</p>
                        <p><strong>Subjek:</strong> ${k.subjek || '-'}</p>
                        <p><strong>Tanggal:</strong> ${new Date(k.created_at).toLocaleString('id-ID')}</p>
                    </div>
                    <div class="mb-3">
                        <p><strong>Pesan:</strong></p>
                        <p>${k.pesan}</p>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('detailModal')).show();
                loadKontak();
                loadDashboard();
            }
        })
        .catch(err => console.error('Error:', err));
}

function viewArtikel(id) {
    fetch(API_URL + `artikel.php?action=detail&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const a = data.data;
                document.getElementById('modalContent').innerHTML = `
                    <h4>${a.judul}</h4>
                    <p class="text-muted small">
                        <i class="bi bi-calendar"></i> ${new Date(a.tanggal_upload).toLocaleDateString('id-ID')} | 
                        <i class="bi bi-person"></i> ${a.author_name || 'Admin'} |
                        <i class="bi bi-eye"></i> ${a.views || 0} views
                    </p>
                    ${a.ringkasan ? '<p class="lead">' + a.ringkasan + '</p>' : ''}
                    <hr>
                    <div style="white-space: pre-wrap;">${a.konten}</div>
                `;
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            }
        })
        .catch(err => console.error('Error:', err));
}

// ==================== TOGGLE PUBLISH ARTIKEL ====================
function togglePublishArtikel(id) {
    fetch(API_URL + 'artikel.php?action=toggle_publish', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? data.message : 'Gagal: ' + data.message);
            if (data.success) loadArtikel();
        })
        .catch(err => {
            alert('Error!');
            console.error(err);
        });
}

// ==================== AKSI PEMINJAMAN ====================
function approve(id) {
    showConfirmModal('Setujui Peminjaman', 'Yakin menyetujui?', () => {
        const catatan = document.getElementById('catatanAdmin').value;
        fetch(API_URL + 'peminjaman.php?action=approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, catatan })
        })
            .then(r => r.json())
            .then(data => {
                showToast(data.success ? 'Berhasil' : 'Gagal', data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    loadDashboard();
                    loadPeminjaman();
                }
            })
            .catch(err => {
                showToast('Error', 'Terjadi kesalahan sistem', 'danger');
                console.error(err);
            });
    }, true);
}

function reject(id) {
    showConfirmModal('Tolak Peminjaman', 'Yakin menolak?', () => {
        const catatan = document.getElementById('catatanAdmin').value;
        fetch(API_URL + 'peminjaman.php?action=reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, catatan })
        })
            .then(r => r.json())
            .then(data => {
                showToast(data.success ? 'Berhasil' : 'Gagal', data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    loadDashboard();
                    loadPeminjaman();
                }
            })
            .catch(err => {
                showToast('Error', 'Terjadi kesalahan sistem', 'danger');
                console.error(err);
            });
    }, true);
}

function cancelBooking(id) {
    showConfirmModal('Batalkan Peminjaman', 'Yakin membatalkan?', () => {
        fetch(API_URL + 'peminjaman.php?action=cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
            .then(r => r.json())
            .then(data => {
                showToast(data.success ? 'Berhasil' : 'Gagal', data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    loadDashboard();
                    loadPeminjaman();
                }
            })
            .catch(err => {
                showToast('Error', 'Terjadi kesalahan sistem', 'danger');
                console.error(err);
            });
    });
}

function rejectCancel(id) {
    showConfirmModal('Tolak Pembatalan', 'Yakin tolak request batal?', () => {
        fetch(API_URL + 'peminjaman.php?action=reject_cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
            .then(r => r.json())
            .then(data => {
                showToast(data.success ? 'Berhasil' : 'Gagal', data.message, data.success ? 'success' : 'danger');
                if (data.success) {
                    loadDashboard();
                    loadPeminjaman();
                }
            })
            .catch(err => {
                showToast('Error', 'Terjadi kesalahan sistem', 'danger');
                console.error(err);
            });
    });
}

// ==================== MODAL & TOAST ====================
function showConfirmModal(title, message, callback, showCatatan = false) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('catatanAdmin').value = '';

    const catatanDiv = document.getElementById('catatanAdminDiv');
    catatanDiv.classList.toggle('d-none', !showCatatan);

    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.onclick = () => {
        callback();
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    };

    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function showToast(title, message, type) {
    // Bisa ganti dengan Bootstrap Toast kalau mau lebih fancy
    alert(`${title}: ${message}`);
}

// ==================== AUTO REFRESH ====================
setInterval(() => {
    const activeTab = document.querySelector('.nav-link.active')?.dataset.tab;
    if (activeTab === 'beranda') loadDashboard();
}, 30000);