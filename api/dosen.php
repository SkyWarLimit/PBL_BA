<?php 
// ===== FILE: api/dosen.php =====
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    jsonResponse(false, 'Unauthorized');
}

$database = new Database();
$db = $database->getConnection();
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $query = "SELECT d.*, u.nama, u.email FROM dosen d LEFT JOIN users u ON d.id_user = u.id_user ORDER BY u.nama";
    $stmt = $db->prepare($query);
    $stmt->execute();
    jsonResponse(true, '', $stmt->fetchAll());
}
?>
?>