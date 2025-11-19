<?php
// ==================== api/artikel.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'list':
        getArtikelList($koneksi);
        break;
    case 'detail':
        getArtikelDetail($koneksi, $_GET['id']);
        break;
    case 'add':
        addArtikel($koneksi);
        break;
    case 'update':
        updateArtikel($koneksi);
        break;
    case 'delete':
        deleteArtikel($koneksi);
        break;
    case 'toggle_publish':
        togglePublish($koneksi);
        break;
}

function getArtikelList($koneksi) {
    $kategori = isset($_GET['kategori']) ? pg_escape_string($koneksi, $_GET['kategori']) : '';
    $search = isset($_GET['search']) ? pg_escape_string($koneksi, $_GET['search']) : '';
    $published_only = isset($_GET['published_only']) ? true : false;
    
    $query = "SELECT 
                a.*,
                u.nama as author_name
              FROM artikel a
              LEFT JOIN users u ON a.author_id = u.id_users
              WHERE 1=1";
    
    if ($published_only) {
        $query .= " AND a.is_published = TRUE";
    }
    if ($kategori) {
        $query .= " AND a.kategori = '$kategori'";
    }
    if ($search) {
        $query .= " AND (a.judul ILIKE '%$search%' OR a.konten ILIKE '%$search%')";
    }
    
    $query .= " ORDER BY a.tanggal_upload DESC";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_all($result);
        echo json_encode([
            'success' => true,
            'data' => $data ? $data : []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
}

function getArtikelDetail($koneksi, $id) {
    $id = pg_escape_string($koneksi, $id);
    
    // Increment views
    pg_query($koneksi, "UPDATE artikel SET views = views + 1 WHERE id_artikel = $id");
    
    $query = "SELECT 
                a.*,
                u.nama as author_name
              FROM artikel a
              LEFT JOIN users u ON a.author_id = u.id_users
              WHERE a.id_artikel = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
}

function addArtikel($koneksi) {
    $judul = pg_escape_string($koneksi, $_POST['judul']);
    $konten = pg_escape_string($koneksi, $_POST['konten']);
    $ringkasan = isset($_POST['ringkasan']) ? pg_escape_string($koneksi, $_POST['ringkasan']) : '';
    $kategori = isset($_POST['kategori']) ? pg_escape_string($koneksi, $_POST['kategori']) : '';
    $is_published = isset($_POST['is_published']) && $_POST['is_published'] == 'true' ? 'TRUE' : 'FALSE';
    $author_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Upload file (optional)
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_path = uploadFileArtikel($_FILES['file']);
    }
    
    $ringkasanValue = $ringkasan ? "'$ringkasan'" : "NULL";
    $kategoriValue = $kategori ? "'$kategori'" : "NULL";
    $filePathValue = $file_path ? "'$file_path'" : "NULL";
    $authorValue = $author_id ? $author_id : "NULL";
    
    $query = "INSERT INTO artikel (judul, konten, ringkasan, file_path, kategori, is_published, author_id) 
              VALUES ('$judul', '$konten', $ringkasanValue, $filePathValue, $kategoriValue, $is_published, $authorValue)";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Artikel berhasil ditambahkan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah artikel: ' . pg_last_error($koneksi)
        ]);
    }
}

function updateArtikel($koneksi) {
    $id = intval($_POST['id']);
    $judul = pg_escape_string($koneksi, $_POST['judul']);
    $konten = pg_escape_string($koneksi, $_POST['konten']);
    $ringkasan = isset($_POST['ringkasan']) ? pg_escape_string($koneksi, $_POST['ringkasan']) : '';
    $kategori = isset($_POST['kategori']) ? pg_escape_string($koneksi, $_POST['kategori']) : '';
    $is_published = isset($_POST['is_published']) && $_POST['is_published'] == 'true' ? 'TRUE' : 'FALSE';
    
    // Get old file path
    $query = "SELECT file_path FROM artikel WHERE id_artikel = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    $file_path = $oldData['file_path'];
    
    // Upload new file if exists
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // Delete old file
        if ($file_path && file_exists('../' . $file_path)) {
            unlink('../' . $file_path);
        }
        $file_path = uploadFileArtikel($_FILES['file']);
    }
    
    $ringkasanValue = $ringkasan ? "'$ringkasan'" : "NULL";
    $kategoriValue = $kategori ? "'$kategori'" : "NULL";
    $filePathValue = $file_path ? "'$file_path'" : "NULL";
    
    $query = "UPDATE artikel 
              SET judul = '$judul', 
                  konten = '$konten',
                  ringkasan = $ringkasanValue,
                  file_path = $filePathValue,
                  kategori = $kategoriValue,
                  is_published = $is_published,
                  updated_at = NOW()
              WHERE id_artikel = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Artikel berhasil diupdate'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update artikel: ' . pg_last_error($koneksi)
        ]);
    }
}

function deleteArtikel($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    // Get file path
    $query = "SELECT file_path FROM artikel WHERE id_artikel = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    
    // Hard delete
    $query = "DELETE FROM artikel WHERE id_artikel = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        // Delete file
        if ($oldData['file_path'] && file_exists('../' . $oldData['file_path'])) {
            unlink('../' . $oldData['file_path']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Artikel berhasil dihapus'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus artikel: ' . pg_last_error($koneksi)
        ]);
    }
}

function togglePublish($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    $query = "UPDATE artikel 
              SET is_published = NOT is_published,
                  updated_at = NOW()
              WHERE id_artikel = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Status publikasi berhasil diubah'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengubah status: ' . pg_last_error($koneksi)
        ]);
    }
}

function uploadFileArtikel($file) {
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/jpg', 'image/webp',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // .docx
    ];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $uploadDir = '../uploads/artikel/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'artikel_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/artikel/' . $filename;
    } else {
        return false;
    }
}
?>