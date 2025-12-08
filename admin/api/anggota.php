<?php
// api/anggota.php - Manajemen CRUD Dosen/Anggota

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya admin yang bisa mengakses API ini
if (!function_exists('checkAuth')) {
    session_start();
    function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }
}
checkAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = (new Database())->getConnection();

// Pastikan fungsi uploadFile dan deleteFile ada
if (!function_exists('uploadFile')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Fungsi uploadFile tidak ditemukan di database.php']);
    exit;
}
if (!function_exists('deleteFile')) {
    function deleteFile($path) { return true; } 
}


// --- FUNGSI HELPER ---

/**
 * Mendapatkan semua data anggota dengan relasi penuh.
 * Mengambil Pendidikan dan Keahlian dari tabel relasional.
 * @param PDO $db Objek database
 * @return array
 */
function getAnggotaData($db) {
    try {
        // 1. Query utama untuk data dosen (JOIN ke users untuk mendapatkan nama)
        $sql_dosen = "SELECT d.id_dosen, d.id_user, d.nidn, d.foto, d.created_at, d.updated_at,
                     u.nama AS nama_anggota
                     FROM dosen d
                     JOIN users u ON d.id_user = u.id_user 
                     ORDER BY d.id_dosen DESC";
        $dosen_stmt = $db->query($sql_dosen);
        $dosen_data = $dosen_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Ambil semua data relasional dalam satu query untuk efisiensi
        $sql_links = "SELECT id_dosen, platform, link_url FROM link_akademik_dosen";
        $links_data = $db->query($sql_links)->fetchAll(PDO::FETCH_ASSOC);

        $sql_pendidikan = "SELECT id_dosen, pendidikan FROM pendidikan_terakhir";
        $pendidikan_data = $db->query($sql_pendidikan)->fetchAll(PDO::FETCH_ASSOC);
        
        $sql_keahlian = "SELECT id_dosen, nama_bidang FROM bidang_keahlian";
        $keahlian_data = $db->query($sql_keahlian)->fetchAll(PDO::FETCH_ASSOC);

        // 3. Mapping semua data relasional ke dosen
        $dosen_with_relations = [];
        foreach ($dosen_data as $dosen) {
            $id = $dosen['id_dosen'];
            
            // Filter dan map data relasional
            $dosen['links'] = array_filter($links_data, fn($link) => $link['id_dosen'] == $id);
            
            $pendidikan = array_filter($pendidikan_data, fn($p) => $p['id_dosen'] == $id);
            $dosen['pendidikan_terakhir'] = array_column($pendidikan, 'pendidikan'); 
            
            $keahlian = array_filter($keahlian_data, fn($k) => $k['id_dosen'] == $id);
            $dosen['bidang_keahlian'] = array_column($keahlian, 'nama_bidang');
            
            $dosen_with_relations[] = $dosen;
        }
        
        return $dosen_with_relations;
    } catch (PDOException $e) {
        error_log("Error fetching anggota data: " . $e->getMessage());
        return [];
    }
}

/**
 * Menyimpan data dosen/anggota baru atau mengupdate yang sudah ada ke multi-tabel.
 * @param array $data Data form
 * @param array $file Array $_FILES
 * @param PDO $db Objek database
 * @return array Hasil operasi
 */
function saveAnggota($data, $file, $db) {
    $is_update = !empty($data['id_dosen']);
    $id_dosen = $data['id_dosen'] ?? null;
    $id_user_input = $data['id_user_terpilih'] ?? null; 
    
    // Ambil data dari form
    $nidn = $data['nidn'] ?? '';
    $pendidikan_str = $data['pendidikan_terakhir'] ?? ''; 
    $keahlian_str = $data['bidang_keahlian'] ?? '';
    
    // Validasi input
    if (empty($id_user_input) || empty($nidn) || empty($pendidikan_str) || empty($keahlian_str)) {
         return ['success' => false, 'message' => 'Semua kolom wajib diisi. (Pastikan User sudah dipilih dari pencarian)'];
    }

    $db->beginTransaction();
    try {
        $foto_path = $data['old_foto'] ?? null; 

        // 1. Proses Upload Foto Baru
        if (isset($file['foto']) && $file['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadFile($file['foto'], 'anggota');
            if (!$upload_result['success']) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Gagal Upload Foto: ' . $upload_result['message']];
            }
            $foto_path = $upload_result['path'];
            if ($is_update && !empty($data['old_foto']) && $data['old_foto'] !== $foto_path) {
                deleteFile($data['old_foto']);
            }
        }
        
        // 2. Insert/Update Data Dosen (Tabel 'dosen') - Hapus kolom relasional
        if ($is_update) {
            $sql_dosen = "UPDATE dosen SET 
                          nidn = :nidn, updated_at = NOW()" . 
                          (!empty($foto_path) ? ", foto = :foto" : "") . 
                          " WHERE id_dosen = :id_dosen";
            $stmt = $db->prepare($sql_dosen);
            $stmt->bindParam(':id_dosen', $id_dosen, PDO::PARAM_INT);
        } else {
            // INSERT: Menyertakan id_user yang dipilih
            $sql_dosen = "INSERT INTO dosen (id_user, nidn, foto, created_at, updated_at) 
                          VALUES (:id_user, :nidn, :foto, NOW(), NOW())";
            $stmt = $db->prepare($sql_dosen);
            $stmt->bindParam(':id_user', $id_user_input, PDO::PARAM_INT);
        }

        $stmt->bindParam(':nidn', $nidn);
        if (!empty($foto_path)) {
            $stmt->bindParam(':foto', $foto_path);
        }
        $stmt->execute(); 

        if (!$is_update) {
            $id_dosen = $db->lastInsertId();
        }

        // --- 3. Update Pendidikan Terakhir (Tabel Relasional) ---
        $db->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen = ?")->execute([$id_dosen]);
        // Pisahkan input berdasarkan koma atau baris baru
        $pendidikan_list = array_map('trim', array_filter(explode(',', str_replace(["\r\n", "\r", "\n"], ',', $pendidikan_str))));
        
        $sql_pendidikan = "INSERT INTO pendidikan_terakhir (id_dosen, pendidikan, created_at) VALUES (?, ?, NOW())";
        $stmt_pendidikan = $db->prepare($sql_pendidikan);

        foreach ($pendidikan_list as $pendidikan) {
            if (!empty($pendidikan)) {
                $stmt_pendidikan->execute([$id_dosen, $pendidikan]);
            }
        }

        // --- 4. Update Bidang Keahlian (Tabel Relasional) ---
        $db->prepare("DELETE FROM bidang_keahlian WHERE id_dosen = ?")->execute([$id_dosen]);
        // Pisahkan input berdasarkan koma atau baris baru
        $keahlian_list = array_map('trim', array_filter(explode(',', str_replace(["\r\n", "\r", "\n"], ',', $keahlian_str))));
        
        $sql_keahlian = "INSERT INTO bidang_keahlian (id_dosen, nama_bidang, created_at) VALUES (?, ?, NOW())";
        $stmt_keahlian = $db->prepare($sql_keahlian);

        foreach ($keahlian_list as $bidang) {
            if (!empty($bidang)) {
                $stmt_keahlian->execute([$id_dosen, $bidang]);
            }
        }

        // --- 5. Update Link Akademik (SAMA) ---
        $db->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen = ?")->execute([$id_dosen]);
        if (!empty($data['link_platform'])) {
            $platforms = $data['link_platform'];
            $urls = $data['link_url'];
            if (is_array($platforms) && is_array($urls) && count($platforms) === count($urls)) {
                $sql_link = "INSERT INTO link_akademik_dosen (id_dosen, platform, link_url, created_at, updated_at) 
                             VALUES (?, ?, ?, NOW(), NOW())";
                $link_stmt = $db->prepare($sql_link);
                foreach ($platforms as $index => $platform) {
                    $url = $urls[$index];
                    if (!empty($platform) && !empty($url)) {
                        $link_stmt->execute([$id_dosen, $platform, $url]);
                    }
                }
            }
        }

        $db->commit();
        return ['success' => true, 'message' => $is_update ? 'Data anggota berhasil diperbarui.' : 'Anggota baru berhasil ditambahkan.', 'data' => ['id_dosen' => $id_dosen]];

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error saveAnggota: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error database saat menyimpan data: ' . $e->getMessage()];
    }
}


// --- LOGIKA UTAMA API ---

if ($method === 'GET') {
    
    // ENDPOINT PENCARIAN USER
    if (isset($_GET['action']) && $_GET['action'] === 'search_user' && isset($_GET['q'])) {
        $search_term = '%' . $_GET['q'] . '%';
        // Cari user di tabel users.
        $stmt = $db->prepare("SELECT id_user, nama, email FROM users WHERE nama LIKE ? OR email LIKE ? LIMIT 10");
        $stmt->execute([$search_term, $search_term]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);
        exit;
    }
    
    if (isset($_GET['id'])) {
        // READ detail anggota
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        
        // Ambil data dasar dosen (JOIN ke users untuk nama)
        $dosen_stmt = $db->prepare("SELECT d.*, u.nama AS nama_anggota 
                                   FROM dosen d 
                                   JOIN users u ON d.id_user = u.id_user
                                   WHERE d.id_dosen = ?");
        $dosen_stmt->execute([$id]);
        $dosen_data = $dosen_stmt->fetch(PDO::FETCH_ASSOC);

        if ($dosen_data) {
            // 1. Ambil Data Link Akademik
            $links_stmt = $db->prepare("SELECT platform, link_url FROM link_akademik_dosen WHERE id_dosen = ?");
            $links_stmt->execute([$id]);
            $dosen_data['links'] = $links_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 2. Ambil Data Pendidikan Terakhir
            $pendidikan_stmt = $db->prepare("SELECT pendidikan FROM pendidikan_terakhir WHERE id_dosen = ?");
            $pendidikan_stmt->execute([$id]);
            // Gabungkan array pendidikan menjadi satu string yang dipisahkan koma untuk ditampilkan di textarea
            $dosen_data['pendidikan_terakhir'] = implode(', ', array_column($pendidikan_stmt->fetchAll(PDO::FETCH_ASSOC), 'pendidikan'));
            
            // 3. Ambil Data Bidang Keahlian
            $keahlian_stmt = $db->prepare("SELECT nama_bidang FROM bidang_keahlian WHERE id_dosen = ?");
            $keahlian_stmt->execute([$id]);
            // Gabungkan array bidang menjadi satu string yang dipisahkan koma untuk ditampilkan di textarea
            $dosen_data['bidang_keahlian'] = implode(', ', array_column($keahlian_stmt->fetchAll(PDO::FETCH_ASSOC), 'nama_bidang'));

            $dosen_data['id_user_terpilih'] = $dosen_data['id_user']; 

            echo json_encode(['success' => true, 'data' => $dosen_data]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan.']);
        }

    } else {
        // READ semua anggota (list)
        echo json_encode(['success' => true, 'data' => getAnggotaData($db)]);
    }

} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $result = saveAnggota($_POST, $_FILES, $db);
        echo json_encode($result);

    } elseif ($action === 'delete' && isset($_POST['id'])) {
        // DELETE (Menghapus dari semua tabel relasional)
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        
        $db->beginTransaction();
        try {
            $stmt_foto = $db->prepare("SELECT foto FROM dosen WHERE id_dosen = ?");
            $stmt_foto->execute([$id]);
            $foto_path = $stmt_foto->fetchColumn();

            // Hapus dari tabel relasional
            $db->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen = ?")->execute([$id]);
            $db->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen = ?")->execute([$id]);
            $db->prepare("DELETE FROM bidang_keahlian WHERE id_dosen = ?")->execute([$id]);
            
            // Hapus data dosen
            $stmt_del = $db->prepare("DELETE FROM dosen WHERE id_dosen = ?");
            $stmt_del->execute([$id]);

            $db->commit();

            if ($foto_path) deleteFile($foto_path);

            echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus.']);

        } catch (PDOException $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus anggota: ' . $e->getMessage()]);
        }

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>