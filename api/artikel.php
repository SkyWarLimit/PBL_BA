<?php
// File: api/artikel.php
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
            $query = "SELECT a.*, u.nama as author_name, u2.nama as uploaded_by_name 
                     FROM artikel a 
                     LEFT JOIN users u ON a.author_id = u.id_user 
                     LEFT JOIN users u2 ON a.uploaded_by = u2.id_user 
                     ORDER BY a.tanggal_upload DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $artikel = $stmt->fetchAll();
            jsonResponse(true, '', $artikel);
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'add':
        try {
            $judul = $_POST['judul'] ?? '';
            $konten = $_POST['konten'] ?? '';
            $ringkasan = $_POST['ringkasan'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            $author_id = $_POST['author_id'] ?? $_SESSION['user_id'];
            $is_published = isset($_POST['is_published']) ? ($_POST['is_published'] === 'true' || $_POST['is_published'] === '1') : false;
            
            if (empty($judul) || empty($konten)) {
                jsonResponse(false, 'Judul dan konten harus diisi');
            }
            
            $file_path = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadFile($_FILES['foto'], 'artikel');
                if ($upload['success']) {
                    $file_path = $upload['path'];
                }
            }
            
            $query = "SELECT add_artikel(:judul, :konten, :ringkasan, :file_path, :kategori, :author_id, :uploaded_by, :is_published)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':konten', $konten);
            $stmt->bindParam(':ringkasan', $ringkasan);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':author_id', $author_id);
            $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
            $stmt->bindParam(':is_published', $is_published, PDO::PARAM_BOOL);
            $stmt->execute();
            
            jsonResponse(true, 'Artikel berhasil ditambahkan');
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'update':
        try {
            $id = $_POST['id_artikel'] ?? 0;
            $judul = $_POST['judul'] ?? '';
            $konten = $_POST['konten'] ?? '';
            $ringkasan = $_POST['ringkasan'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            $is_published = isset($_POST['is_published']) ? ($_POST['is_published'] === 'true' || $_POST['is_published'] === '1') : false;
            
            if (empty($judul) || empty($konten)) {
                jsonResponse(false, 'Judul dan konten harus diisi');
            }
            
            // Get old file path
            $query = "SELECT file_path FROM artikel WHERE id_artikel = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $old_data = $stmt->fetch();
            
            $file_path = $old_data['file_path'];
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadFile($_FILES['foto'], 'artikel');
                if ($upload['success']) {
                    if ($old_data['file_path']) {
                        deleteFile($old_data['file_path']);
                    }
                    $file_path = $upload['path'];
                }
            }
            
            $query = "SELECT update_artikel(:id, :judul, :konten, :ringkasan, :file_path, :kategori, :is_published)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':konten', $konten);
            $stmt->bindParam(':ringkasan', $ringkasan);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':is_published', $is_published, PDO::PARAM_BOOL);
            $stmt->execute();
            
            jsonResponse(true, 'Artikel berhasil diupdate');
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id_artikel'] ?? 0;
            
            // Get file path
            $query = "SELECT file_path FROM artikel WHERE id_artikel = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch();
            
            if ($data) {
                if ($data['file_path']) {
                    deleteFile($data['file_path']);
                }
                
                $query = "SELECT delete_artikel(:id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                jsonResponse(true, 'Artikel berhasil dihapus');
            } else {
                jsonResponse(false, 'Artikel tidak ditemukan');
            }
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    case 'get':
        try {
            $id = $_GET['id'] ?? 0;
            $query = "SELECT * FROM artikel WHERE id_artikel = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $artikel = $stmt->fetch();
            
            if ($artikel) {
                jsonResponse(true, '', $artikel);
            } else {
                jsonResponse(false, 'Artikel tidak ditemukan');
            }
        } catch (PDOException $e) {
            jsonResponse(false, $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, 'Invalid action');
}
?>