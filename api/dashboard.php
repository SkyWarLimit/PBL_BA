<?php 
// ===== FILE: api/dashboard.php =====
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    jsonResponse(false, 'Unauthorized');
}

$database = new Database();
$db = $database->getConnection();
$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    $query = "SELECT * FROM get_dashboard_stats()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch();
    jsonResponse(true, '', $stats);
}
?>