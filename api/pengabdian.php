<?php 
// ===== FILE: api/pengabdian.php =====
// Similar structure to penelitian.php

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
        $query = "SELECT p.*, d.nidn, u.nama as nama_dosen FROM pengabdian p 
                  LEFT JOIN dosen d ON p.id_dosen = d.id_dosen 
                  LEFT JOIN users u ON d.id_user = u.id_user 
                  ORDER BY p.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'add':
        $query = "SELECT add_pengabdian(:judul, :tahun, :id_dosen)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':judul' => $_POST['judul'],
            ':tahun' => $_POST['tahun'],
            ':id_dosen' => $_POST['id_dosen'] ?? null
        ]);
        jsonResponse(true, 'Pengabdian berhasil ditambahkan');
        break;
        
    case 'update':
        $query = "SELECT update_pengabdian(:id, :judul, :tahun, :id_dosen)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':id' => $_POST['id_pengabdian'],
            ':judul' => $_POST['judul'],
            ':tahun' => $_POST['tahun'],
            ':id_dosen' => $_POST['id_dosen'] ?? null
        ]);
        jsonResponse(true, 'Pengabdian berhasil diupdate');
        break;
        
    case 'delete':
        $query = "SELECT delete_pengabdian(:id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_pengabdian']]);
        jsonResponse(true, 'Pengabdian berhasil dihapus');
        break;
        
    case 'get':
        $query = "SELECT * FROM pengabdian WHERE id_pengabdian = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_GET['id']]);
        jsonResponse(true, '', $stmt->fetch());
        break;
}
?>