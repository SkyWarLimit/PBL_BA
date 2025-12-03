<?php
// File: admin/api/galeri.php
require_once '../../admin/config/database.php';
checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // ... (GET logic sama)
    $stmt = $db->query("SELECT * FROM galeri ORDER BY id_galeri DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} 
elseif ($method == 'POST') {
    try {
        // DELETE LOGIC (Sama)
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $stmt = $db->prepare("SELECT file_path FROM galeri WHERE id_galeri = ?");
            $stmt->execute([$_POST['id']]);
            $row = $stmt->fetch();
            if ($row && $row['file_path']) deleteFile($row['file_path']);
            $db->prepare("DELETE FROM galeri WHERE id_galeri = ?")->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Hapus berhasil']);
            exit;
        }

        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $kategori = $_POST['kategori'] ?? 'Kategori 1';
        $uploaded_by = $_POST['uploaded_by'] ?? null;

        $foto_path = '';
        $foto_query = "";
        $params = [$judul, $deskripsi, $kategori, $uploaded_by];

        // === UPLOAD FOTO GALERI ===
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            // Pastikan folder tujuan adalah 'galeri'
            $up = uploadFile($_FILES['foto'], 'galeri');
            
            if ($up['success']) {
                $foto_path = $up['path'];
                $foto_query = ", file_path = ?";
            } else {
                throw new Exception($up['message']);
            }
        }

        if (isset($_POST['id_galeri']) && !empty($_POST['id_galeri'])) {
            // UPDATE
            if ($foto_path) $params[] = $foto_path;
            $params[] = $_POST['id_galeri'];
            $sql = "UPDATE galeri SET judul=?, deskripsi=?, kategori=?, uploaded_by=? $foto_query WHERE id_galeri=?";
            $db->prepare($sql)->execute($params);
            echo json_encode(['success' => true, 'message' => 'Galeri diperbarui']);
        } else {
            // INSERT
            $sql = "INSERT INTO galeri (judul, deskripsi, kategori, file_path, uploaded_by, is_active, tanggal_upload) VALUES (?, ?, ?, ?, ?, true, CURRENT_DATE)";
            $db->prepare($sql)->execute([$judul, $deskripsi, $kategori, $foto_path, $uploaded_by]);
            echo json_encode(['success' => true, 'message' => 'Galeri ditambah']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>