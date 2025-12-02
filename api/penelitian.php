<?php
// ===== FILE: api/penelitian.php =====
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
        $query = "SELECT p.*, d.nidn, u.nama as nama_dosen FROM penelitian p 
                  LEFT JOIN dosen d ON p.id_dosen = d.id_dosen 
                  LEFT JOIN users u ON d.id_user = u.id_user 
                  ORDER BY p.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'add':
        $query = "SELECT add_penelitian(:judul, :penelitian, :tahun, :id_dosen, :kategori)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':judul' => $_POST['judul'],
            ':penelitian' => $_POST['penelitian'],
            ':tahun' => $_POST['tahun'],
            ':id_dosen' => $_POST['id_dosen'] ?? null,
            ':kategori' => $_POST['kategori'] ?? null
        ]);
        jsonResponse(true, 'Penelitian berhasil ditambahkan');
        break;
        
    case 'update':
        $query = "SELECT update_penelitian(:id, :judul, :penelitian, :tahun, :id_dosen, :kategori)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':id' => $_POST['id_penelitian'],
            ':judul' => $_POST['judul'],
            ':penelitian' => $_POST['penelitian'],
            ':tahun' => $_POST['tahun'],
            ':id_dosen' => $_POST['id_dosen'] ?? null,
            ':kategori' => $_POST['kategori'] ?? null
        ]);
        jsonResponse(true, 'Penelitian berhasil diupdate');
        break;
        
    case 'delete':
        $query = "SELECT delete_penelitian(:id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_penelitian']]);
        jsonResponse(true, 'Penelitian berhasil dihapus');
        break;
        
    case 'get':
        $query = "SELECT * FROM penelitian WHERE id_penelitian = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_GET['id']]);
        jsonResponse(true, '', $stmt->fetch());
        break;
}
