<?php
// ==================== api/anggota.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'list':
        getAnggotaList($koneksi);
        break;
    case 'detail':
        getAnggotaDetail($koneksi, $_GET['id']);
        break;
    case 'add':
        addAnggota($koneksi);
        break;
    case 'update':
        updateAnggota($koneksi);
        break;
    case 'delete':
        deleteAnggota($koneksi);
        break;
}

function getAnggotaList($koneksi) {
    $query = "SELECT * FROM anggota WHERE is_active = TRUE ORDER BY urutan ASC, nama ASC";
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

function getAnggotaDetail($koneksi, $id) {
    $id = pg_escape_string($koneksi, $id);
    $query = "SELECT * FROM anggota WHERE id_anggota = $id";
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

function addAnggota($koneksi) {
    $nama = pg_escape_string($koneksi, $_POST['nama']);
    $jabatan = pg_escape_string($koneksi, $_POST['jabatan']);
    $email = isset($_POST['email']) ? pg_escape_string($koneksi, $_POST['email']) : '';
    $urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
    
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = uploadFoto($_FILES['foto']);
        if (!$foto) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal upload foto'
            ]);
            return;
        }
    }
    
    $fotoValue = $foto ? "'$foto'" : "NULL";
    $emailValue = $email ? "'$email'" : "NULL";
    
    $query = "INSERT INTO anggota (nama, jabatan, email, foto, urutan) 
              VALUES ('$nama', '$jabatan', $emailValue, $fotoValue, $urutan)";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Anggota berhasil ditambahkan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah anggota: ' . pg_last_error($koneksi)
        ]);
    }
}

function updateAnggota($koneksi) {
    $id = intval($_POST['id']);
    $nama = pg_escape_string($koneksi, $_POST['nama']);
    $jabatan = pg_escape_string($koneksi, $_POST['jabatan']);
    $email = isset($_POST['email']) ? pg_escape_string($koneksi, $_POST['email']) : '';
    $urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
    
    // Get old photo
    $query = "SELECT foto FROM anggota WHERE id_anggota = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    $foto = $oldData['foto'];
    
    // Upload new photo if exists
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Delete old photo
        if ($foto && file_exists('../' . $foto)) {
            unlink('../' . $foto);
        }
        $foto = uploadFoto($_FILES['foto']);
    }
    
    $fotoValue = $foto ? "'$foto'" : "NULL";
    $emailValue = $email ? "'$email'" : "NULL";
    
    $query = "UPDATE anggota 
              SET nama = '$nama', jabatan = '$jabatan', email = $emailValue, 
                  foto = $fotoValue, urutan = $urutan, updated_at = NOW()
              WHERE id_anggota = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Anggota berhasil diupdate'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update anggota: ' . pg_last_error($koneksi)
        ]);
    }
}

function deleteAnggota($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    // Get photo path
    $query = "SELECT foto FROM anggota WHERE id_anggota = $id";
    $result = pg_query($koneksi, $query);
    $oldData = pg_fetch_assoc($result);
    
    // Soft delete
    $query = "UPDATE anggota SET is_active = FALSE WHERE id_anggota = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        // Optional: delete photo file
        if ($oldData['foto'] && file_exists('../' . $oldData['foto'])) {
            unlink('../' . $oldData['foto']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Anggota berhasil dihapus'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus anggota: ' . pg_last_error($koneksi)
        ]);
    }
}

function uploadFoto($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $uploadDir = '../uploads/anggota/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'anggota_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/anggota/' . $filename;
    } else {
        return false;
    }
}
?>