<?php
// File: api/galeri.php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    jsonResponse(false, 'Unauthorized');
}

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $query = "SELECT g.*, u.nama as uploaded_by_name 
                     FROM galeri g 
                     LEFT JOIN users u ON g.uploaded_by = u.id_user 
                     ORDER BY g.tanggal_upload DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $galeri = $stmt->fetchAll();
            jsonResponse(true, '', $galeri);
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'add':
        try {
            $judul = $_POST['judul'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            
            if (empty($judul)) {
                jsonResponse(false, 'Judul harus diisi');
            }
            
            $file_path = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadFile($_FILES['foto'], 'galeri');
                if (!$upload['success']) {
                    jsonResponse(false, $upload['message']);
                }
                $file_path = $upload['path'];
            } else {
                jsonResponse(false, 'Foto harus diupload');
            }
            
            $query = "SELECT add_galeri(:judul, :deskripsi, :file_path, :kategori, :uploaded_by)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
            $stmt->execute();
            
            // Manual log untuk tracking user yang benar
            $log_query = "INSERT INTO log (id_user, aktivitas, deskripsi, related_table) 
                         VALUES (:user_id, 'INSERT', 'Menambahkan galeri baru: ' || :judul, 'galeri')";
            $log_stmt = $db->prepare($log_query);
            $log_stmt->bindParam(':user_id', $_SESSION['user_id']);
            $log_stmt->bindParam(':judul', $judul);
            $log_stmt->execute();
            
            jsonResponse(true, 'Galeri berhasil ditambahkan');
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'update':
        try {
            $id = $_POST['id_galeri'] ?? 0;
            $judul = $_POST['judul'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            $is_active = $_POST['is_active'] ?? 'true';
            $is_active = ($is_active === 'true' || $is_active === '1');
            
            if (empty($judul)) {
                jsonResponse(false, 'Judul harus diisi');
            }
            
            // Get old file path
            $query = "SELECT file_path FROM galeri WHERE id_galeri = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $old_data = $stmt->fetch();
            
            $file_path = $old_data['file_path'];
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadFile($_FILES['foto'], 'galeri');
                if ($upload['success']) {
                    // Delete old file
                    deleteFile($old_data['file_path']);
                    $file_path = $upload['path'];
                }
            }
            
            $query = "SELECT update_galeri(:id, :judul, :deskripsi, :file_path, :kategori, :is_active)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);
            $stmt->execute();
            
            jsonResponse(true, 'Galeri berhasil diupdate');
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id_galeri'] ?? 0;
            
            // Get file path
            $query = "SELECT file_path FROM galeri WHERE id_galeri = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch();
            
            if ($data) {
                // Delete file
                deleteFile($data['file_path']);
                
                // Delete from database
                $query = "SELECT delete_galeri(:id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                jsonResponse(true, 'Galeri berhasil dihapus');
            } else {
                jsonResponse(false, 'Galeri tidak ditemukan');
            }
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'get':
        try {
            $id = $_GET['id'] ?? 0;
            $query = "SELECT * FROM galeri WHERE id_galeri = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $galeri = $stmt->fetch();
            
            if ($galeri) {
                jsonResponse(true, '', $galeri);
            } else {
                jsonResponse(false, 'Galeri tidak ditemukan');
            }
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Invalid action');
}
?>