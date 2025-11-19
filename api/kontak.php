<?php
// ==================== api/kontak.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method == 'GET' && $action == 'list') {
    $query = "SELECT * FROM kontak ORDER BY is_read ASC, created_at DESC";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_all($result);
        echo json_encode([
            'success' => true,
            'data' => $data ? $data : []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
} elseif ($method == 'GET' && $action == 'detail') {
    $id = pg_escape_string($koneksi, $_GET['id']);
    
    // Mark as read
    $updateQuery = "UPDATE kontak SET is_read = TRUE WHERE id_kontak = $id";
    pg_query($koneksi, $updateQuery);
    
    // Get detail
    $query = "SELECT * FROM kontak WHERE id_kontak = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
}
?>