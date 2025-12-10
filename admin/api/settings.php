<?php
// File: admin/api/settings.php
require_once '../../admin/config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

checkAuth();
// Matikan error display HTML agar JSON tidak rusak
ini_set('display_errors', 0);
error_reporting(E_ALL); 
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// --- Helper Function: Upload File ---
// Parameter $key ditambahkan untuk menentukan subfolder (logo/maskot)
function uploadSettingFile($file, $key) {
    // Tentukan subfolder berdasarkan key
    $subfolder = ($key === 'logo') ? 'logo' : (($key === 'maskot') ? 'maskot' : 'general');
    
    // Target Path: ../../admin/uploads/{subfolder}/
    $targetDir = "../../admin/uploads/" . $subfolder . "/";
    
    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            return ['success' => false, 'message' => "Gagal membuat folder: $subfolder"];
        }
    }

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    
    if (!in_array($extension, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak didukung (hanya jpg, png, gif, svg).'];
    }

    // Nama file unik
    $fileName = $key . '_' . time() . '_' . rand(100,999) . '.' . $extension;
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        // Return path relative untuk disimpan di database (uploads/...)
        // Sesuaikan dengan struktur folder admin Anda
        return ['success' => true, 'path' => "uploads/" . $subfolder . "/" . $fileName];
    }
    
    return ['success' => false, 'message' => 'Gagal memindahkan file ke server.'];
}

// ==================================================================
// === GET SETTINGS ===
// ==================================================================
if ($method == 'GET') {
    try {
        $stmt = $db->query('SELECT * FROM settings');
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        
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
        if (isset($_POST['key']) && in_array($_POST['key'], $allowed_keys)) {
            $key = $_POST['key'];
            
            // 1. Update Teks Deskripsi (jika ada input 'value')
            if (isset($_POST['value'])) {
                $val = $_POST['value'];
                
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

            // 2. Update File Gambar (Logo / Maskot)
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] == 0) {
                // Upload file baru ke folder spesifik
                $uploadResult = uploadSettingFile($_FILES[$key], $key);
                
                if ($uploadResult['success']) {
                    $newPath = $uploadResult['path'];

                    // A. Ambil path file lama dari database
                    $stmtGetOld = $db->prepare('SELECT file_path FROM settings WHERE "key" = ?');
                    $stmtGetOld->execute([$key]);
                    $oldData = $stmtGetOld->fetch(PDO::FETCH_ASSOC);

                    // B. Hapus file lama jika ada secara fisik
                    if ($oldData && !empty($oldData['file_path'])) {
                        // Path fisik relatif dari file api/settings.php ke admin/
                        $oldFilePhysicalPath = "../../admin/" . $oldData['file_path'];
                        
                        if (file_exists($oldFilePhysicalPath)) {
                            unlink($oldFilePhysicalPath);
                        }
                    }

                    // C. Update database dengan path baru
                    $stmtFile = $db->prepare('UPDATE settings SET file_path = ?, updated_at = CURRENT_TIMESTAMP WHERE "key" = ?');
                    $stmtFile->execute([$newPath, $key]);

                } else {
                    throw new Exception($uploadResult['message']);
                }
            }
        }
        
        // --- SKENARIO 2: Bulk Update (Identitas/Kontak) ---
        else {
            foreach ($allowed_keys as $key) {
                if ($key == 'key') continue;

                if (isset($_POST[$key])) {
                    $val = $_POST[$key];
                    
                    $check = $db->prepare('SELECT 1 FROM settings WHERE "key" = ?');
                    $check->execute([$key]);
                    
                    if ($check->rowCount() > 0) {
                        $stmt = $db->prepare('UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE "key" = ?');
                        $stmt->execute([$val, $key]);
                    } else {
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
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>