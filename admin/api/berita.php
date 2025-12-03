<?php
// File: admin/api/berita.php
require_once '../../admin/config/database.php';
checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // ... (GET logic sama seperti sebelumnya)
    $stmt = $db->query("SELECT * FROM artikel ORDER BY tanggal_upload DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} 
elseif ($method == 'POST') {
    try {
        // DELETE LOGIC (Sama seperti sebelumnya)
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $stmt = $db->prepare("SELECT file_path FROM artikel WHERE id_artikel = ?");
            $stmt->execute([$_POST['id']]);
            $row = $stmt->fetch();
            if ($row && $row['file_path']) deleteFile($row['file_path']);
            $db->prepare("DELETE FROM artikel WHERE id_artikel = ?")->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Berita dihapus']);
            exit;
        }

        // INSERT / UPDATE LOGIC
        $judul = $_POST['judul'] ?? '';
        $kategori = $_POST['kategori'] ?? 'News Latest';
        $konten = $_POST['deskripsi'] ?? ''; 
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $nama_upload = $_POST['nama_pengupload'] ?? 'Admin';
        $role_upload = $_POST['role_pengupload'] ?? 'admin';

        $foto_path = '';
        $foto_query = "";
        $params = [$judul, $kategori, $konten, $tanggal, $nama_upload, $role_upload];

        // === UPLOAD FOTO BERITA ===
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // Pastikan folder tujuan adalah 'berita'
            $up = uploadFile($_FILES['foto'], 'berita'); 
            
            if ($up['success']) {
                $foto_path = $up['path'];
                $foto_query = ", file_path = ?"; // Untuk update
            } else {
                throw new Exception($up['message']);
            }
        }

        if (isset($_POST['id_artikel']) && !empty($_POST['id_artikel'])) {
            // UPDATE
            if ($foto_path) $params[] = $foto_path;
            $params[] = $_POST['id_artikel'];
            
            // Logic hapus foto lama jika ada update foto baru... (seperti kode sebelumnya)

            $sql = "UPDATE artikel SET judul=?, kategori=?, konten=?, tanggal_upload=?, nama_pengupload=?, role_pengupload=? $foto_query WHERE id_artikel=?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Berita diperbarui']);
        } else {
            // INSERT
            $sql = "INSERT INTO artikel (judul, kategori, konten, file_path, tanggal_upload, nama_pengupload, role_pengupload, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, true)";
            // Masukkan path foto ke parameter ke-4
            $insertParams = [$judul, $kategori, $konten, $foto_path, $tanggal, $nama_upload, $role_upload];
            $db->prepare($sql)->execute($insertParams);
            echo json_encode(['success' => true, 'message' => 'Berita ditambah']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>