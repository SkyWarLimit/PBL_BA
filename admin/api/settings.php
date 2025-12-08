<?php
// File: admin/api/settings.php
require_once '../../admin/config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// --- Helper Function: Upload File ---
function uploadSettingFile($file, $subfolder) {
    // Target Path: ../../admin/uploads/{subfolder}/
    // Folder fisik server
    $targetDir = "../../admin/uploads/" . $subfolder . "/";
    
    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    
    if (!in_array($extension, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak didukung (hanya jpg, png, gif, svg).'];
    }

    // Nama file unik dengan timestamp
    $fileName = $subfolder . '_' . time() . '_' . rand(100,999) . '.' . $extension;
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        // Return path relative untuk disimpan di database (admin/uploads/...)
        return ['success' => true, 'path' => "admin/uploads/" . $subfolder . "/" . $fileName];
    }
    
    return ['success' => false, 'message' => 'Gagal memindahkan file ke server.'];
}

// ==================================================================
// === GET SETTINGS ===
// ==================================================================
if ($method == 'GET') {
    try {
        // Menggunakan "key" (double quotes) untuk kompatibilitas PostgreSQL
        $stmt = $db->query('SELECT * FROM settings');
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        
        // Reformat array agar index-nya adalah nama key (logo, maskot, dll)
        foreach($rawData as $row) {
            $settings[$row['key']] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} 

// ==================================================================
// === POST SETTINGS (UPDATE) ===
// ==================================================================
elseif ($method == 'POST') {
    try {
        $db->beginTransaction();

        $allowed_keys = ['nama_lab', 'email', 'no_telp', 'alamat', 'visi', 'misi', 'logo', 'maskot'];
        
        // --- SKENARIO 1: Update Single Item (Logo/Maskot via openSetForm) ---
        // Frontend mengirim: name="key" value="logo", name="value" value="Deskripsi...", name="logo" (file)
        if (isset($_POST['key']) && in_array($_POST['key'], $allowed_keys)) {
            $key = $_POST['key'];
            
            // 1. Update Teks Deskripsi (jika ada input 'value')
            if (isset($_POST['value'])) {
                $val = $_POST['value'];
                
                // Cek apakah row sudah ada (Gunakan "key" dengan tanda kutip dua)
                $check = $db->prepare('SELECT 1 FROM settings WHERE "key" = ?');
                $check->execute([$key]);
                
                if ($check->rowCount() > 0) {
                    $stmt = $db->prepare('UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE "key" = ?');
                    $stmt->execute([$val, $key]);
                } else {
                    $cat = in_array($key, ['logo', 'maskot']) ? 'identitas' : 'umum';
                    $stmt = $db->prepare('INSERT INTO settings ("key", value, kategori, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
                    $stmt->execute([$key, $val, $cat]);
                }
            }

            // 2. Update File Gambar (jika ada file yang diupload dengan name sesuai key)
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
                $subfolder = 'identitas'; // Simpan logo & maskot di folder identitas
                
                $uploadResult = uploadSettingFile($_FILES[$key], $subfolder);
                
                if ($uploadResult['success']) {
                    // Update kolom file_path di database
                    $stmtFile = $db->prepare('UPDATE settings SET file_path = ?, updated_at = CURRENT_TIMESTAMP WHERE "key" = ?');
                    $stmtFile->execute([$uploadResult['path'], $key]);
                } else {
                    throw new Exception($uploadResult['message']);
                }
            }
        }
        
        // --- SKENARIO 2: Bulk Update (Form Identitas/Kontak biasa) ---
        // Frontend mengirim: name="nama_lab", name="email", dst.
        else {
            foreach ($allowed_keys as $key) {
                // Lewati jika ini adalah parameter kontrol
                if ($key == 'key') continue;

                if (isset($_POST[$key])) {
                    $val = $_POST[$key];
                    
                    // Cek existensi data
                    $check = $db->prepare('SELECT 1 FROM settings WHERE "key" = ?');
                    $check->execute([$key]);
                    
                    if ($check->rowCount() > 0) {
                        $stmt = $db->prepare('UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE "key" = ?');
                        $stmt->execute([$val, $key]);
                    } else {
                        // Insert data baru jika belum ada
                        $cat = 'umum';
                        if (in_array($key, ['visi', 'misi'])) $cat = 'profil';
                        
                        $stmt = $db->prepare('INSERT INTO settings ("key", value, kategori, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)');
                        $stmt->execute([$key, $val, $cat]);
                    }
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan!']);

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>