<?php
// File: admin/api/berita.php
require_once '../../admin/config/database.php';
checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id']; // Ambil ID user yang sedang login

// --- HELPER FUNCTION UNTUK LOG ---
function logActivity($db, $userId, $activity, $desc) {
    try {
        $ip = getClientIP(); // Dari database.php
        $sql = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address, waktu) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $activity, $desc, $ip]);
    } catch (Exception $e) {
        // Silent fail: Jangan hentikan proses utama jika log gagal
    }
}

// === GET DATA ===
if ($method == 'GET') {
    try {
        if (isset($_GET['id'])) {
            $query = "SELECT a.*, u.nama as nama_pengupload, u.role as role_pengupload 
                      FROM artikel a 
                      LEFT JOIN users u ON a.id_user = u.id_user 
                      WHERE a.id_artikel = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            $query = "SELECT a.*, u.nama as nama_pengupload, u.role as role_pengupload 
                      FROM artikel a 
                      LEFT JOIN users u ON a.id_user = u.id_user 
                      ORDER BY a.tanggal_upload DESC";
            $stmt = $db->query($query);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 

// === POST DATA ===
elseif ($method == 'POST') {
    try {
        // 1. DELETE
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $id_artikel = $_POST['id'];
            
            // Ambil judul dulu untuk log
            $stmtInfo = $db->prepare("SELECT judul, file_path FROM artikel WHERE id_artikel = ?");
            $stmtInfo->execute([$id_artikel]);
            $info = $stmtInfo->fetch();
            
            if ($info) {
                // Hapus file fisik
                if ($info['file_path']) deleteFile($info['file_path']);
                
                // Hapus data
                $db->prepare("DELETE FROM artikel WHERE id_artikel = ?")->execute([$id_artikel]);
                
                // CATAT LOG
                logActivity($db, $user_id, 'DELETE', "Menghapus berita: " . $info['judul']);
                
                echo json_encode(['success' => true, 'message' => 'Berita dihapus']);
            } else {
                throw new Exception("Data tidak ditemukan");
            }
            exit;
        }

        // --- INPUT DATA ---
        $judul = $_POST['judul'] ?? '';
        $kategori = $_POST['kategori'] ?? 'News Latest';
        $konten = $_POST['deskripsi'] ?? '';
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $id_user = $_SESSION['user_id']; 

        if (empty($judul) || empty($konten)) throw new Exception("Data tidak lengkap!");

        $foto_query = "";
        $params = [$judul, $kategori, $konten, $tanggal, $id_user];

        // Handle File Upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $up = uploadFile($_FILES['foto'], 'berita');
            if (!$up['success']) throw new Exception($up['message']);
            
            if (isset($_POST['id_artikel']) && !empty($_POST['id_artikel'])) {
                $old = $db->prepare("SELECT file_path FROM artikel WHERE id_artikel = ?");
                $old->execute([$_POST['id_artikel']]);
                $oldRow = $old->fetch();
                if ($oldRow && $oldRow['file_path']) deleteFile($oldRow['file_path']);
            }
            $foto_path = $up['path'];
        } else {
            $foto_path = null;
        }

        // 2. UPDATE
        if (isset($_POST['id_artikel']) && !empty($_POST['id_artikel'])) {
            $id = $_POST['id_artikel'];
            
            if ($foto_path) {
                $foto_query = ", file_path = ?";
                $params[] = $foto_path;
            }
            $params[] = $id; 

            $sql = "UPDATE artikel SET judul=?, kategori=?, konten=?, tanggal_upload=?, id_user=? $foto_query WHERE id_artikel=?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // CATAT LOG
            logActivity($db, $user_id, 'UPDATE', "Mengubah berita: $judul");
            
            echo json_encode(['success' => true, 'message' => 'Berita diperbarui!']);
        } 
        
        // 3. INSERT
        else {
            if (!$foto_path) throw new Exception("Foto wajib diupload!");
            
            $sql = "INSERT INTO artikel (judul, kategori, konten, tanggal_upload, id_user, file_path, is_published) 
                    VALUES (?, ?, ?, ?, ?, ?, true)";
            $stmt = $db->prepare($sql);
            $params[] = $foto_path; 
            $stmt->execute($params);
            
            // CATAT LOG
            logActivity($db, $user_id, 'INSERT', "Menambahkan berita baru: $judul");
            
            echo json_encode(['success' => true, 'message' => 'Berita ditambahkan!']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>