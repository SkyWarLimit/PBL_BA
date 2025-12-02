<?php 
// ===== FILE: api/sosmed.php =====
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
        $query = "SELECT * FROM sosmed_lab ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'add':
        $query = "INSERT INTO sosmed_lab (platform, link_url, icon, is_active) VALUES (:platform, :link_url, :icon, :is_active)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':platform' => $_POST['platform'],
            ':link_url' => $_POST['link_url'],
            ':icon' => $_POST['icon'] ?? '',
            ':is_active' => true
        ]);
        jsonResponse(true, 'Sosial media berhasil ditambahkan');
        break;
        
    case 'update':
        $query = "UPDATE sosmed_lab SET platform = :platform, link_url = :link_url, icon = :icon, is_active = :is_active WHERE id_sosmed = :id";
        $stmt = $db->prepare($query);
        $is_active = isset($_POST['is_active']) && ($_POST['is_active'] === 'true' || $_POST['is_active'] === '1');
        $stmt->execute([
            ':id' => $_POST['id_sosmed'],
            ':platform' => $_POST['platform'],
            ':link_url' => $_POST['link_url'],
            ':icon' => $_POST['icon'] ?? '',
            ':is_active' => $is_active
        ]);
        jsonResponse(true, 'Sosial media berhasil diupdate');
        break;
        
    case 'delete':
        $query = "DELETE FROM sosmed_lab WHERE id_sosmed = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_sosmed']]);
        jsonResponse(true, 'Sosial media berhasil dihapus');
        break;
}
?>