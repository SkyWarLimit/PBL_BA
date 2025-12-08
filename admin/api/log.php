<?php 
// ===== FILE: api/log.php =====
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
        $query = "SELECT l.*, u.nama FROM log l LEFT JOIN users u ON l.id_user = u.id_user ORDER BY l.waktu DESC LIMIT 100";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'recent':
        $limit = $_GET['limit'] ?? 10;
        $query = "SELECT l.*, u.nama FROM log l LEFT JOIN users u ON l.id_user = u.id_user ORDER BY l.waktu DESC LIMIT :limit";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
}
?>