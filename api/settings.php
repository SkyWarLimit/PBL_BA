<?php
// ==================== api/settings.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method == 'GET' && $action == 'get') {
    $query = "SELECT key, value FROM settings";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $settings = [];
        while ($row = pg_fetch_assoc($result)) {
            $settings[$row['key']] = $row['value'];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
} elseif ($method == 'POST' && $action == 'update_logo') {
    if (!isset($_FILES['logo'])) {
        echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
        exit;
    }
    
    $file = $_FILES['logo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
        exit;
    }
    
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old logo
        $query = "SELECT value FROM settings WHERE key = 'logo'";
        $result = pg_query($koneksi, $query);
        $oldLogo = pg_fetch_assoc($result);
        if ($oldLogo && file_exists('../' . $oldLogo['value'])) {
            unlink('../' . $oldLogo['value']);
        }
        
        // Update database
        $relativePath = 'uploads/' . $filename;
        $query = "INSERT INTO settings (key, value, description) 
                  VALUES ('logo', '$relativePath', 'Logo website') 
                  ON CONFLICT (key) DO UPDATE SET value = '$relativePath', updated_at = NOW()";
        
        $result = pg_query($koneksi, $query);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Logo berhasil diupdate',
                'path' => $relativePath
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error database: ' . pg_last_error($koneksi)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
    }
} elseif ($method == 'POST' && $action == 'update_info') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    pg_query($koneksi, "BEGIN");
    
    $settings = [
        'nama_lab' => pg_escape_string($koneksi, $data['nama_lab']),
        'alamat' => pg_escape_string($koneksi, $data['alamat']),
        'email' => pg_escape_string($koneksi, $data['email']),
        'telp' => pg_escape_string($koneksi, $data['telp'])
    ];
    
    $success = true;
    foreach ($settings as $key => $value) {
        $query = "INSERT INTO settings (key, value) 
                  VALUES ('$key', '$value') 
                  ON CONFLICT (key) DO UPDATE SET value = '$value', updated_at = NOW()";
        
        $result = pg_query($koneksi, $query);
        if (!$result) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        pg_query($koneksi, "COMMIT");
        echo json_encode([
            'success' => true,
            'message' => 'Informasi berhasil diupdate'
        ]);
    } else {
        pg_query($koneksi, "ROLLBACK");
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update informasi: ' . pg_last_error($koneksi)
        ]);
    }
}
?>