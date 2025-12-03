<?php
// File: admin/api/settings.php
require_once '../../admin/config/database.php';
checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    try {
        $stmt = $db->query("SELECT * FROM settings");
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach($rawData as $row) $settings[$row['key']] = $row;
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 
elseif ($method == 'POST') {
    try {
        $allowed_keys = ['nama_lab', 'email', 'no_telp', 'alamat', 'visi', 'misi', 'logo', 'maskot'];
        
        foreach ($allowed_keys as $key) {
            // 1. Update Teks
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                // Tentukan kategori berdasarkan key
                $kategori = in_array($key, ['visi', 'misi']) ? 'profil' : (in_array($key, ['logo', 'maskot']) ? 'identitas' : 'kontak');
                
                $sql = "INSERT INTO settings (key, value, kategori) VALUES (:key, :val, :cat) 
                        ON CONFLICT (key) DO UPDATE SET value = :val";
                $stmt = $db->prepare($sql);
                $stmt->execute([':key' => $key, ':val' => $value, ':cat' => $kategori]);
            }

            // 2. Update File (Gambar)
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
                
                // === LOGIKA PEMISAH FOLDER ===
                $folder_tujuan = 'profil'; // Default
                if ($key === 'logo') {
                    $folder_tujuan = 'logo';   // Masuk uploads/logo
                } elseif ($key === 'maskot') {
                    $folder_tujuan = 'maskot'; // Masuk uploads/maskot
                }

                $up = uploadFile($_FILES[$key], $folder_tujuan); 
                
                if ($up['success']) {
                    $sql = "UPDATE settings SET file_path = :path, updated_at = CURRENT_TIMESTAMP WHERE key = :key";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':path' => $up['path'], ':key' => $key]);
                }
            }
        }
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>