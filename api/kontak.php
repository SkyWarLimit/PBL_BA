<?php 
// ===== FILE: api/kontak.php =====
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
        $query = "SELECT * FROM kontak ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'read':
        $query = "UPDATE kontak SET is_read = true WHERE id_kontak = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_kontak']]);
        jsonResponse(true, 'Pesan ditandai sudah dibaca');
        break;
        
    case 'delete':
        $query = "DELETE FROM kontak WHERE id_kontak = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_kontak']]);
        jsonResponse(true, 'Pesan berhasil dihapus');
        break;
}
?>