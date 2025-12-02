// ===== ARTIKEL FUNCTIONS =====
function loadArtikel() {
    const content = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Artikel</h2>
            <button class="btn btn-primary" onclick="showArtikelModal()">
                <i class="fas fa-plus me-2"></i>Tambah Artikel
            </button>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="artikel-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadArtikelData();
}

function loadArtikelData() {
    fetch('../api/artikel.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('artikel-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.file_path ? `<img src="../${item.file_path}" style="width:80px;height:60px;object-fit:cover;">` : '-'}</td>
                        <td>${item.judul}</td>
                        <td>${item.kategori || '-'}</td>
                        <td>${item.author_name || '-'}</td>
                        <td><span class="badge bg-${item.is_published ? 'success' : 'warning'}">${item.is_published ? 'Published' : 'Draft'}</span></td>
                        <td>${item.views}</td>
                        <td>${new Date(item.tanggal_upload).toLocaleDateString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editArtikel(${item.id_artikel})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteArtikel(${item.id_artikel})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function showArtikelModal(id = null) {
    const modal = `
        <div class="modal fade" id="artikelModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Artikel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="artikelForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="id_artikel" value="${id || ''}">
                            <div class="mb-3">
                                <label class="form-label">Judul *</label>
                                <input type="text" class="form-control" name="judul" id="a_judul" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ringkasan</label>
                                <textarea class="form-control" name="ringkasan" id="a_ringkasan" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konten *</label>
                                <textarea class="form-control" name="konten" id="a_konten" rows="8" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <input type="text" class="form-control" name="kategori" id="a_kategori">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gambar Cover</label>
                                <input type="file" class="form-control" name="foto" accept="image/*" onchange="previewImage(this, 'artikel-preview')">
                                <img id="artikel-preview" class="img-preview" style="display:none;">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="a_published" value="1">
                                    <label class="form-check-label">Publish Artikel</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modal-container').innerHTML = modal;
    const modalEl = new bootstrap.Modal(document.getElementById('artikelModal'));
    modalEl.show();
    
    if (id) {
        fetch(`../api/artikel.php?action=get&id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('a_judul').value = data.data.judul;
                    document.getElementById('a_ringkasan').value = data.data.ringkasan || '';
                    document.getElementById('a_konten').value = data.data.konten;
                    document.getElementById('a_kategori').value = data.data.kategori || '';
                    document.getElementById('a_published').checked = data.data.is_published;
                    if (data.data.file_path) {
                        document.getElementById('artikel-preview').src = '../' + data.data.file_path;
                        document.getElementById('artikel-preview').style.display = 'block';
                    }
                }
            });
    }
    
    document.getElementById('artikelForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = id ? 'update' : 'add';
        
        fetch(`../api/artikel.php?action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                modalEl.hide();
                loadArtikelData();
            } else {
                alert('Error: ' + data.message);
            }
        });
    };
}

function editArtikel(id) { showArtikelModal(id); }

function deleteArtikel(id) {
    if (!confirm('Yakin ingin menghapus artikel ini?')) return;
    const formData = new FormData();
    formData.append('id_artikel', id);
    fetch('../api/artikel.php?action=delete', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadArtikelData(); });
}

// ===== PENELITIAN FUNCTIONS =====
function loadPenelitian() {
    const content = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Penelitian</h2>
            <button class="btn btn-primary" onclick="showPenelitianModal()">
                <i class="fas fa-plus me-2"></i>Tambah Penelitian
            </button>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Dosen</th>
                                <th>Kategori</th>
                                <th>Tahun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="penelitian-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadPenelitianData();
}

function loadPenelitianData() {
    fetch('../api/penelitian.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('penelitian-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.judul}</td>
                        <td>${item.nama_dosen || '-'}</td>
                        <td>${item.kategori || '-'}</td>
                        <td>${item.tahun}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewPenelitian(${item.id_penelitian})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-warning" onclick="editPenelitian(${item.id_penelitian})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deletePenelitian(${item.id_penelitian})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function showPenelitianModal(id = null) {
    // Load dosen list first
    fetch('../api/dosen.php?action=list')
        .then(r => r.json())
        .then(dosenData => {
            const dosenOptions = dosenData.data.map(d => 
                `<option value="${d.id_dosen}">${d.nama} (${d.nidn})</option>`
            ).join('');
            
            const modal = `
                <div class="modal fade" id="penelitianModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Penelitian</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="penelitianForm">
                                <div class="modal-body">
                                    <input type="hidden" name="id_penelitian" value="${id || ''}">
                                    <div class="mb-3">
                                        <label class="form-label">Judul *</label>
                                        <input type="text" class="form-control" name="judul" id="p_judul" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi Penelitian *</label>
                                        <textarea class="form-control" name="penelitian" id="p_penelitian" rows="6" required></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tahun *</label>
                                            <input type="number" class="form-control" name="tahun" id="p_tahun" required min="1900" max="2100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Kategori</label>
                                            <input type="text" class="form-control" name="kategori" id="p_kategori">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dosen</label>
                                        <select class="form-select" name="id_dosen" id="p_dosen">
                                            <option value="">-- Pilih Dosen --</option>
                                            ${dosenOptions}
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-container').innerHTML = modal;
            const modalEl = new bootstrap.Modal(document.getElementById('penelitianModal'));
            modalEl.show();
            
            if (id) {
                fetch(`../api/penelitian.php?action=get&id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('p_judul').value = data.data.judul;
                            document.getElementById('p_penelitian').value = data.data.penelitian;
                            document.getElementById('p_tahun').value = data.data.tahun;
                            document.getElementById('p_kategori').value = data.data.kategori || '';
                            document.getElementById('p_dosen').value = data.data.id_dosen || '';
                        }
                    });
            }
            
            document.getElementById('penelitianForm').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const action = id ? 'update' : 'add';
                
                fetch(`../api/penelitian.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modalEl.hide();
                        loadPenelitianData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            };
        });
}

function viewPenelitian(id) {
    fetch(`../api/penelitian.php?action=get&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Judul: ' + data.data.judul + '\n\nDeskripsi:\n' + data.data.penelitian);
            }
        });
}

function editPenelitian(id) { showPenelitianModal(id); }

function deletePenelitian(id) {
    if (!confirm('Yakin ingin menghapus penelitian ini?')) return;
    const formData = new FormData();
    formData.append('id_penelitian', id);
    fetch('../api/penelitian.php?action=delete', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadPenelitianData(); });
}

// ===== PENGABDIAN FUNCTIONS =====
function loadPengabdian() {
    const content = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Pengabdian</h2>
            <button class="btn btn-primary" onclick="showPengabdianModal()">
                <i class="fas fa-plus me-2"></i>Tambah Pengabdian
            </button>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Dosen</th>
                                <th>Tahun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pengabdian-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadPengabdianData();
}

function loadPengabdianData() {
    fetch('../api/pengabdian.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('pengabdian-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.judul}</td>
                        <td>${item.nama_dosen || '-'}</td>
                        <td>${item.tahun}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editPengabdian(${item.id_pengabdian})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deletePengabdian(${item.id_pengabdian})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function showPengabdianModal(id = null) {
    fetch('../api/dosen.php?action=list')
        .then(r => r.json())
        .then(dosenData => {
            const dosenOptions = dosenData.data.map(d => 
                `<option value="${d.id_dosen}">${d.nama} (${d.nidn})</option>`
            ).join('');
            
            const modal = `
                <div class="modal fade" id="pengabdianModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Pengabdian</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="pengabdianForm">
                                <div class="modal-body">
                                    <input type="hidden" name="id_pengabdian" value="${id || ''}">
                                    <div class="mb-3">
                                        <label class="form-label">Judul *</label>
                                        <input type="text" class="form-control" name="judul" id="pg_judul" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tahun *</label>
                                        <input type="number" class="form-control" name="tahun" id="pg_tahun" required min="1900" max="2100">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dosen</label>
                                        <select class="form-select" name="id_dosen" id="pg_dosen">
                                            <option value="">-- Pilih Dosen --</option>
                                            ${dosenOptions}
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-container').innerHTML = modal;
            const modalEl = new bootstrap.Modal(document.getElementById('pengabdianModal'));
            modalEl.show();
            
            if (id) {
                fetch(`../api/pengabdian.php?action=get&id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('pg_judul').value = data.data.judul;
                            document.getElementById('pg_tahun').value = data.data.tahun;
                            document.getElementById('pg_dosen').value = data.data.id_dosen || '';
                        }
                    });
            }
            
            document.getElementById('pengabdianForm').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const action = id ? 'update' : 'add';
                
                fetch(`../api/pengabdian.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modalEl.hide();
                        loadPengabdianData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            };
        });
}

function editPengabdian(id) { showPengabdianModal(id); }

function deletePengabdian(id) {
    if (!confirm('Yakin ingin menghapus pengabdian ini?')) return;
    const formData = new FormData();
    formData.append('id_pengabdian', id);
    fetch('../api/pengabdian.php?action=delete', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadPengabdianData(); });
}

// ===== ANGGOTA FUNCTIONS =====
function loadAnggota() {
    const content = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Anggota</h2>
            <button class="btn btn-primary" onclick="showAnggotaModal()">
                <i class="fas fa-plus me-2"></i>Tambah Anggota
            </button>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>NIDN</th>
                                <th>Jabatan</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="anggota-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadAnggotaData();
}

function loadAnggotaData() {
    fetch('../api/anggota.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('anggota-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.foto ? `<img src="../${item.foto}" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">` : '-'}</td>
                        <td>${item.nama || '-'}</td>
                        <td>${item.nidn || '-'}</td>
                        <td>${item.jabatan}</td>
                        <td>${item.urutan}</td>
                        <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editAnggota(${item.id_anggota})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteAnggota(${item.id_anggota})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function showAnggotaModal(id = null) {
    fetch('../api/dosen.php?action=list')
        .then(r => r.json())
        .then(dosenData => {
            const dosenOptions = dosenData.data.map(d => 
                `<option value="${d.id_dosen}">${d.nama} - ${d.nidn}</option>`
            ).join('');
            
            const modal = `
                <div class="modal fade" id="anggotaModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Anggota</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="anggotaForm" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="id_anggota" value="${id || ''}">
                                    <div class="mb-3">
                                        <label class="form-label">Dosen *</label>
                                        <select class="form-select" name="id_dosen" id="ang_dosen" required>
                                            <option value="">-- Pilih Dosen --</option>
                                            ${dosenOptions}
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Jabatan *</label>
                                        <input type="text" class="form-control" name="jabatan" id="ang_jabatan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="deskripsi" id="ang_deskripsi" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Urutan</label>
                                        <input type="number" class="form-control" name="urutan" id="ang_urutan" min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Foto</label>
                                        <input type="file" class="form-control" name="foto" accept="image/*" onchange="previewImage(this, 'anggota-preview')">
                                        <img id="anggota-preview" class="img-preview" style="display:none;">
                                    </div>
                                    ${id ? '<div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="ang_active" value="1"><label class="form-check-label">Aktif</label></div></div>' : ''}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modal-container').innerHTML = modal;
            const modalEl = new bootstrap.Modal(document.getElementById('anggotaModal'));
            modalEl.show();
            
            if (id) {
                fetch(`../api/anggota.php?action=get&id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('ang_dosen').value = data.data.id_dosen;
                            document.getElementById('ang_jabatan').value = data.data.jabatan;
                            document.getElementById('ang_deskripsi').value = data.data.deskripsi || '';
                            document.getElementById('ang_urutan').value = data.data.urutan;
                            document.getElementById('ang_active').checked = data.data.is_active;
                            if (data.data.foto) {
                                document.getElementById('anggota-preview').src = '../' + data.data.foto;
                                document.getElementById('anggota-preview').style.display = 'block';
                            }
                        }
                    });
            }
            
            document.getElementById('anggotaForm').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const action = id ? 'update' : 'add';
                
                fetch(`../api/anggota.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modalEl.hide();
                        loadAnggotaData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            };
        });
}

function editAnggota(id) { showAnggotaModal(id); }

function deleteAnggota(id) {
    if (!confirm('Yakin ingin menghapus anggota ini?')) return;
    const formData = new FormData();
    formData.append('id_anggota', id);
    fetch('../api/anggota.php?action=delete', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadAnggotaData(); });
}

// ===== PEMINJAMAN FUNCTIONS =====
function loadPeminjaman() {
    const content = `
        <h2 class="mb-4">Kelola Peminjaman</h2>
        <ul class="nav nav-tabs mb-3" id="peminjamanTab">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#approved">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#pembatalan">Request Pembatalan</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="pending">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Peminjaman</th>
                                        <th>Waktu Mulai</th>
                                        <th>Waktu Selesai</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pending-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="approved">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Peminjaman</th>
                                        <th>Waktu Mulai</th>
                                        <th>Waktu Selesai</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="approved-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pembatalan">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Peminjaman</th>
                                        <th>Alasan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pembatalan-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadPeminjamanData();
}

function loadPeminjamanData() {
    fetch('../api/peminjaman.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const pending = data.data.filter(p => p.status === 'pending');
                const approved = data.data.filter(p => p.status === 'approved');
                const pembatalan = data.data.filter(p => p.request_pembatalan && p.status !== 'cancelled');
                
                document.getElementById('pending-table').innerHTML = pending.map(item => `
                    <tr>
                        <td>${item.id_peminjaman}</td>
                        <td>${item.nama_peminjam}</td>
                        <td>${new Date(item.tanggal_peminjaman).toLocaleDateString('id-ID')}</td>
                        <td>${item.waktu_mulai ? new Date(item.waktu_mulai).toLocaleString('id-ID') : '-'}</td>
                        <td>${item.waktu_selesai ? new Date(item.waktu_selesai).toLocaleString('id-ID') : '-'}</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="approvePeminjaman(${item.id_peminjaman})">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectPeminjaman(${item.id_peminjaman})">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </td>
                    </tr>
                `).join('');
                
                document.getElementById('approved-table').innerHTML = approved.map(item => `
                    <tr>
                        <td>${item.id_peminjaman}</td>
                        <td>${item.nama_peminjam}</td>
                        <td>${new Date(item.tanggal_peminjaman).toLocaleDateString('id-ID')}</td>
                        <td>${item.waktu_mulai ? new Date(item.waktu_mulai).toLocaleString('id-ID') : '-'}</td>
                        <td>${item.waktu_selesai ? new Date(item.waktu_selesai).toLocaleString('id-ID') : '-'}</td>
                        <td><span class="badge bg-success">Approved</span></td>
                        <td>${item.catatan_admin || '-'}</td>
                    </tr>
                `).join('');
                
                document.getElementById('pembatalan-table').innerHTML = pembatalan.map(item => `
                    <tr>
                        <td>${item.id_peminjaman}</td>
                        <td>${item.nama_peminjam}</td>
                        <td>${new Date(item.tanggal_peminjaman).toLocaleDateString('id-ID')}</td>
                        <td>${item.alasan_pembatalan || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="cancelPeminjaman(${item.id_peminjaman})">
                                <i class="fas fa-check"></i> Setujui Pembatalan
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function approvePeminjaman(id) {
    const catatan = prompt('Catatan (opsional):');
    const formData = new FormData();
    formData.append('id_peminjaman', id);
    formData.append('catatan', catatan || '');
    
    fetch('../api/peminjaman.php?action=approve', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadPeminjamanData(); });
}

function rejectPeminjaman(id) {
    const catatan = prompt('Alasan penolakan:');
    if (!catatan) return;
    
    const formData = new FormData();
    formData.append('id_peminjaman', id);
    formData.append('catatan', catatan);
    
    fetch('../api/peminjaman.php?action=reject', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadPeminjamanData(); });
}

function cancelPeminjaman(id) {
    if (!confirm('Setujui pembatalan peminjaman ini?')) return;
    
    const formData = new FormData();
    formData.append('id_peminjaman', id);
    
    fetch('../api/peminjaman.php?action=cancel', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadPeminjamanData(); });
}

// ===== KONTAK FUNCTIONS =====
function loadKontak() {
    const content = `
        <h2 class="mb-4">Pesan Kontak</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Subjek</th>
                                <th>Pesan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kontak-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadKontakData();
}

function loadKontakData() {
    fetch('../api/kontak.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('kontak-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr class="${!item.is_read ? 'table-info' : ''}">
                        <td>
                            ${item.is_read ? 
                                '<span class="badge bg-secondary">Dibaca</span>' : 
                                '<span class="badge bg-primary">Baru</span>'}
                        </td>
                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                        <td>${item.nama}</td>
                        <td>${item.email}</td>
                        <td>${item.subjek || '-'}</td>
                        <td>${item.pesan.substring(0, 50)}...</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewKontak(${item.id_kontak}, '${item.nama}', '${item.email}', '${item.subjek}', \`${item.pesan}\`)">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${!item.is_read ? 
                                `<button class="btn btn-sm btn-success" onclick="markAsRead(${item.id_kontak})">
                                    <i class="fas fa-check"></i>
                                </button>` : ''}
                            <button class="btn btn-sm btn-danger" onclick="deleteKontak(${item.id_kontak})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function viewKontak(id, nama, email, subjek, pesan) {
    const modal = `
        <div class="modal fade" id="kontakModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pesan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Nama:</strong> ${nama}</p>
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Subjek:</strong> ${subjek || '-'}</p>
                        <hr>
                        <p><strong>Pesan:</strong></p>
                        <p>${pesan}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modal-container').innerHTML = modal;
    const modalEl = new bootstrap.Modal(document.getElementById('kontakModal'));
    modalEl.show();
    
    markAsRead(id);
}

function markAsRead(id) {
    const formData = new FormData();
    formData.append('id_kontak', id);
    
    fetch('../api/kontak.php?action=read', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) loadKontakData(); });
}

function deleteKontak(id) {
    if (!confirm('Yakin ingin menghapus pesan ini?')) return;
    const formData = new FormData();
    formData.append('id_kontak', id);
    
    fetch('../api/kontak.php?action=delete', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { alert(data.message); if (data.success) loadKontakData(); });
}

// ===== PENGATURAN WEB FUNCTIONS =====
function loadPengaturan() {
    const content = `
        <h2 class="mb-4">Pengaturan Website</h2>
        <div class="card">
            <div class="card-body">
                <form id="settingsForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lab</label>
                            <input type="text" class="form-control" name="nama_lab" id="s_nama_lab">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="s_email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="no_telp" id="s_no_telp">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat</label>
                            <input type="text" class="form-control" name="alamat" id="s_alamat">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Visi</label>
                        <textarea class="form-control" name="visi" id="s_visi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Misi</label>
                        <textarea class="form-control" name="misi" id="s_misi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Roadmap</label>
                        <textarea class="form-control" name="roadmap" id="s_roadmap" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*" onchange="previewImage(this, 'logo-preview')">
                        <img id="logo-preview" class="img-preview" style="display:none;">
                        <small class="text-muted">Logo saat ini:</small>
                        <div id="current-logo"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadSettingsData();
}

function loadSettingsData() {
    fetch('../api/settings.php?action=get')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const settings = {};
                data.data.forEach(item => {
                    settings[item.key] = item.value;
                });
                
                document.getElementById('s_nama_lab').value = settings.nama_lab || '';
                document.getElementById('s_email').value = settings.email || '';
                document.getElementById('s_no_telp').value = settings.no_telp || '';
                document.getElementById('s_alamat').value = settings.alamat || '';
                document.getElementById('s_visi').value = settings.visi || '';
                document.getElementById('s_misi').value = settings.misi || '';
                document.getElementById('s_roadmap').value = settings.roadmap || '';
                
                if (settings.logo) {
                    document.getElementById('current-logo').innerHTML = 
                        `<img src="../${settings.logo}" style="max-width:200px;margin-top:10px;">`;
                }
            }
        });
    
    document.getElementById('settingsForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../api/settings.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) loadSettingsData();
        });
    };
}

// ===== SOSIAL MEDIA FUNCTIONS =====
function loadSosmed() {
    const content = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Sosial Media</h2>
            <button class="btn btn-primary" onclick="showSosmedModal()">
                <i class="fas fa-plus me-2"></i>Tambah Sosial Media
            </button>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Platform</th>
                                <th>Link URL</th>
                                <th>Icon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sosmed-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadSosmedData();
}

function loadSosmedData() {
    fetch('../api/sosmed.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('sosmed-table');
                tbody.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.platform}</td>
                        <td><a href="${item.link_url}" target="_blank">${item.link_url}</a></td>
                        <td><i class="${item.icon}"></i> ${item.icon}</td>
                        <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editSosmed(${item.id_sosmed})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSosmed(${item.id_sosmed})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            }
        });
}

function showSosmedModal(id = null) {
    const modal = `
        <div class="modal fade" id="sosmedModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${id ? 'Edit' : 'Tambah'} Sosial Media</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="sosmedForm">
                        <div class="modal-body">
                            <input type="hidden" name="id_sosmed" value="${id || ''}">
                            <div class="mb-3">
                                <label class="form-label">Platform *</label>
                                <input type="text" class="form-control" name="platform" id="sm_platform" required placeholder="e.g., Facebook, Instagram, Twitter">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link URL *</label>
                                <input type="url" class="form-control" name="link_url" id="sm_link" required placeholder="https://...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <input type="text" class="form-control" name="icon" id="sm_icon" placeholder="fab fa-facebook">
                                <small class="text-muted">Contoh: fab fa-facebook, fab fa-instagram, fab fa-twitter</small>
                            </div>
                            ${id ? '<div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="sm_active" value="1"><label class="form-check-label">Aktif</label></div></div>' : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modal-container').innerHTML = modal;
    const modalEl = new bootstrap.Modal(document.getElementById('sosmedModal'));
    modalEl.show();
    
    if (id) {
        fetch(`../api/sosmed.php?action=list`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const sosmed = data.data.find(item => item.id_sosmed == id);
                    if (sosmed) {
                        document.getElementById('sm_platform').value = sosmed.platform;
                        document.getElementById('sm_link').value = sosmed.link_url;
                        document.getElementById('sm_icon').value = sosmed.icon || '';
                        if (document.getElementById('sm_active')) {
                            document.getElementById('sm_active').checked = sosmed.is_active;
                        }
                    }
                }
            });
    }
    
    document.getElementById('sosmedForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = id ? 'update' : 'add';
        
        fetch(`../api/sosmed.php?action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                modalEl.hide();
                loadSosmedData();
            } else {
                alert('Error: ' + data.message);
            }
        });
    };
}

function editSosmed(id) {
    showSosmedModal(id);
}

function deleteSosmed(id) {
    if (!confirm('Yakin ingin menghapus sosial media ini?')) return;
    
    const formData = new FormData();
    formData.append('id_sosmed', id);
    
    fetch('../api/sosmed.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) loadSosmedData();
    });
}

// ===== LOG AKTIVITAS FUNCTIONS =====
function loadLog() {
    const content = `
        <h2 class="mb-4">Log Aktivitas</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aktivitas</th>
                                <th>Deskripsi</th>
                                <th>Table</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody id="log-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('content-area').innerHTML = content;
    loadLogData();
}

function loadLogData() {
    fetch('../api/log.php?action=list')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('log-table');
                tbody.innerHTML = data.data.map(item => {
                    let badgeClass = 'bg-secondary';
                    if (item.aktivitas === 'INSERT') badgeClass = 'bg-success';
                    else if (item.aktivitas === 'UPDATE') badgeClass = 'bg-warning';
                    else if (item.aktivitas === 'DELETE') badgeClass = 'bg-danger';
                    
                    return `
                        <tr>
                            <td>${new Date(item.waktu).toLocaleString('id-ID')}</td>
                            <td>${item.nama || 'System'}</td>
                            <td><span class="badge ${badgeClass}">${item.aktivitas}</span></td>
                            <td>${item.deskripsi}</td>
                            <td>${item.related_table || '-'}</td>
                            <td>${item.ip_address || '-'}</td>
                        </tr>
                    `;
                }).join('');
            }
        });
}

// Helper function untuk preview image (dipanggil dari berbagai modal)
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}