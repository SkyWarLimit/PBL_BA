<?php 
// ===== FILE: api/settings.php =====
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    jsonResponse(false, 'Unauthorized');
}

$database = new Database();
$db = $database->getConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        $query = "SELECT * FROM settings ORDER BY kategori, key";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'update':
        $db->beginTransaction();
        try {
            foreach ($_POST as $key => $value) {
                if ($key === 'logo' && isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $query = "SELECT value FROM settings WHERE key = 'logo'";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $old_logo = $stmt->fetch();
                    
                    if ($old_logo && $old_logo['value']) deleteFile($old_logo['value']);
                    
                    $upload = uploadFile($_FILES['logo'], 'logo');
                    if ($upload['success']) {
                        $value = $upload['path'];
                        $query = "UPDATE settings SET value = :value WHERE key = 'logo'";
                        $stmt = $db->prepare($query);
                        $stmt->execute([':value' => $value]);
                    }
                } else {
                    $query = "UPDATE settings SET value = :value WHERE key = :key";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':value' => $value, ':key' => $key]);
                }
            }
            $db->commit();
            jsonResponse(true, 'Pengaturan berhasil diupdate');
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(false, $e->getMessage());
        }
        break;
}

?>