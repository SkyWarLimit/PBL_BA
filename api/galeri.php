<?php
// ==================== api/galeri.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'list':
        getGaleriList($koneksi);
        break;
    case 'detail':
        getGaleriDetail($koneksi, $_GET['id']);
        break;
    case 'add':
        addGaleri($koneksi);
        break;
    case 'update':
        updateGaleri($koneksi);
        break;
    case 'delete':
        deleteGaleri($koneksi);
        break;
}

function getGaleriList($koneksi) {
    $kategori = isset($_GET['kategori']) ? pg_escape_string($koneksi, $_GET['kategori']) : '';
    $search = isset($_GET['search']) ? pg_escape_string($koneksi, $_GET['search']) : '';
    
    $query = "SELECT 
                g.*,
                u.nama as uploaded_by_name
              FROM galeri g
              LEFT JOIN users u ON g.uploaded_by = u.id_users
              WHERE g.is_active = TRUE";
    
    if ($kategori) {
        $query .= " AND g.kategori = '$kategori'";
    }
    if ($search) {
        $query .= " AND g.judul ILIKE '%$search%'";
    }
    
    $query .= " ORDER BY g.tanggal_upload DESC";
    
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

function getGaleriDetail($koneksi, $id) {
    $id = pg_escape_string($koneksi, $id);
    
    $query = "SELECT 
                g.*,
                u.nama as uploaded_by_name
              FROM galeri g
              LEFT JOIN users u ON g.uploaded_by = u.id_users
              WHERE g.id_galeri = $id";
    
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

function addGaleri($koneksi) {
    $judul = pg_escape_string($koneksi, $_POST['judul']);
    $deskripsi = isset($_POST['deskripsi']) ? pg_escape_string($koneksi, $_POST['deskripsi']) : '';
    $kategori = isset($_POST['kategori']) ? pg_escape_string($koneksi, $_POST['kategori']) : '';
    $uploaded_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Upload foto
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] != 0) {
        echo json_encode([
            'success' => false,
            'message' => 'File foto harus diupload'
        ]);
        return;
    }
    
    $file_path = uploadFotoGaleri($_FILES['foto']);
    if (!$file_path) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal upload foto'
        ]);
        return;
    }
    
    $deskripsiValue = $deskripsi ? "'$deskripsi'" : "NULL";
    $kategoriValue = $kategori ? "'$kategori'" : "NULL";
    $uploadedByValue = $uploaded_by ? $uploaded_by : "NULL";
    
    $query = "INSERT INTO galeri (judul, deskripsi, file_path, kategori, uploaded_by) 
              VALUES ('$judul', $deskripsiValue, '$file_path', $kategoriValue, $uploadedByValue)";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil ditambahkan'
        ]);
    } else {
        // Delete uploaded file if query failed
        if (file_exists('../' . $file_path)) {
            unlink('../' . $file_path);
        }
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah foto: ' . pg_last_error($koneksi)
        ]);
    }
}

function updateGaleri($koneksi) {
    $id = intval($_POST['id']);
    $judul = pg_escape_string($koneksi, $_POST['judul']);
    $deskripsi = isset($_POST['deskripsi']) ? pg_escape_string($koneksi, $_POST['deskripsi']) : '';
    $kategori = isset($_POST['kategori']) ? pg_escape_string($koneksi, $_POST['kategori']) : '';
    
    // Get old file path
    $query = "SELECT file_path FROM galeri WHERE id_galeri = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    $file_path = $oldData['file_path'];
    
    // Upload new photo if exists
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Delete old photo
        if ($file_path && file_exists('../' . $file_path)) {
            unlink('../' . $file_path);
        }
        $file_path = uploadFotoGaleri($_FILES['foto']);
        if (!$file_path) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal upload foto'
            ]);
            return;
        }
    }
    
    $deskripsiValue = $deskripsi ? "'$deskripsi'" : "NULL";
    $kategoriValue = $kategori ? "'$kategori'" : "NULL";
    
    $query = "UPDATE galeri 
              SET judul = '$judul', 
                  deskripsi = $deskripsiValue, 
                  file_path = '$file_path',
                  kategori = $kategoriValue,
                  updated_at = NOW()
              WHERE id_galeri = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil diupdate'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update foto: ' . pg_last_error($koneksi)
        ]);
    }
}

function deleteGaleri($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    // Get file path
    $query = "SELECT file_path FROM galeri WHERE id_galeri = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    
    // Soft delete
    $query = "UPDATE galeri SET is_active = FALSE WHERE id_galeri = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        // Optional: delete file
        if ($oldData['file_path'] && file_exists('../' . $oldData['file_path'])) {
            unlink('../' . $oldData['file_path']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil dihapus'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus foto: ' . pg_last_error($koneksi)
        ]);
    }
}

function uploadFotoGaleri($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $uploadDir = '../uploads/galeri/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'galeri_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/galeri/' . $filename;
    } else {
        return false;
    }
}
?>