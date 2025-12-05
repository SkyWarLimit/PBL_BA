<?php
// File: admin/api/berita.php
require_once '../../admin/config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'] ?? 0;

// --- HELPER FUNCTION UNTUK LOG ---
function logActivity($db, $userId, $activity, $desc) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sql = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address, waktu) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $activity, $desc, $ip]);
    } catch (Exception $e) { }
}

// ==================================================================
// === GET DATA (MENGGUNAKAN SQL VIEW) ===
// ==================================================================
if ($method == 'GET') {
    try {
        // 1. GET SINGLE DATA (Untuk Edit)
        if (isset($_GET['id'])) {
            $query = "SELECT * FROM view_artikel WHERE id_artikel = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            }
        } 
        // 2. GET ALL DATA (Untuk Table View UI)
        else {
            $query = "SELECT * FROM view_artikel ORDER BY tanggal_upload DESC";
            $stmt = $db->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $result]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} 

// ==================================================================
// === POST DATA (TETAP KE TABEL ASLI 'artikel') ===
// ==================================================================
elseif ($method == 'POST') {
    try {
        // 1. DELETE ACTION
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $id_artikel = $_POST['id'];
            
            $stmtInfo = $db->prepare("SELECT judul, file_path FROM artikel WHERE id_artikel = ?");
            $stmtInfo->execute([$id_artikel]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
            
            if ($info) {
                if (!empty($info['file_path']) && file_exists('../../' . $info['file_path'])) {
                     unlink('../../' . $info['file_path']);
                }
                
                $delStmt = $db->prepare("DELETE FROM artikel WHERE id_artikel = ?");
                $delStmt->execute([$id_artikel]);
                
                logActivity($db, $user_id, 'DELETE', "Menghapus artikel: " . $info['judul']);
                echo json_encode(['success' => true, 'message' => 'Artikel berhasil dihapus']);
            } else {
                throw new Exception("Data artikel tidak ditemukan");
            }
            exit;
        }

        // --- PREPARE INPUT DATA ---
        $judul      = $_POST['judul'] ?? '';
        $kategori   = $_POST['kategori'] ?? 'News Latest';
        $konten     = $_POST['deskripsi'] ?? ''; 
        $tanggal    = $_POST['tanggal'] ?? date('Y-m-d H:i:s');
        
        $clean_konten = strip_tags($konten);
        $ringkasan    = substr($clean_konten, 0, 150) . (strlen($clean_konten) > 150 ? '...' : '');

        if (empty($judul) || empty($konten)) throw new Exception("Judul dan Konten tidak boleh kosong!");

        // Upload Logic
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // REVISI: Mengubah target direktori penyimpanan ke admin/uploads/berita/
            // Menggunakan ../../ untuk kembali ke root, lalu masuk ke admin/uploads/berita/
            $targetDir = "../../admin/uploads/berita/";
            
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES["foto"]["name"]);
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) {
                // Path yang disimpan di database
                $foto_path = "admin/uploads/berita/" . $fileName;
            }

            if (!empty($_POST['id_artikel'])) {
                $oldQuery = $db->prepare("SELECT file_path FROM artikel WHERE id_artikel = ?");
                $oldQuery->execute([$_POST['id_artikel']]);
                $oldData = $oldQuery->fetch(PDO::FETCH_ASSOC);
                if ($oldData && !empty($oldData['file_path']) && file_exists('../../' . $oldData['file_path'])) {
                    unlink('../../' . $oldData['file_path']);
                }
            }
        }

        // 2. UPDATE ACTION
        if (isset($_POST['id_artikel']) && !empty($_POST['id_artikel'])) {
            $id = $_POST['id_artikel'];
            
            // Kolom 'is_published' dan 'views' dihapus dari query ini
            $sql = "UPDATE artikel SET judul=?, kategori=?, konten=?, ringkasan=?, tanggal_upload=?, updated_at=CURRENT_TIMESTAMP, id_user=?";
            $params = [$judul, $kategori, $konten, $ringkasan, $tanggal, $user_id];

            if ($foto_path) {
                $sql .= ", file_path = ?";
                $params[] = $foto_path;
            }
            $sql .= " WHERE id_artikel = ?";
            $params[] = $id;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            logActivity($db, $user_id, 'UPDATE', "Mengubah artikel: $judul");
            echo json_encode(['success' => true, 'message' => 'Artikel berhasil diperbarui!']);
        } 
        
        // 3. INSERT ACTION
        else {
            if (!$foto_path) throw new Exception("Foto wajib diupload!");
            
            // REVISI PENTING: Menghapus 'uploaded_by' dari INSERT untuk menghindari error tipe data Integer vs String.
            // Database sudah menyimpan relasi user melalui 'id_user'.
            
            $sql = "INSERT INTO artikel (judul, konten, ringkasan, file_path, kategori, tanggal_upload, updated_at, id_user) 
                    VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?)";
            
            $stmt = $db->prepare($sql);
            
            // Parameter disesuaikan: Menghapus uploaded_by dari array
            $stmt->execute([$judul, $konten, $ringkasan, $foto_path, $kategori, $tanggal, $user_id]);
            
            logActivity($db, $user_id, 'INSERT', "Menambahkan artikel baru: $judul");
            echo json_encode(['success' => true, 'message' => 'Artikel berhasil ditambahkan!']);
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>