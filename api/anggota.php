<?php 
// ===== FILE: api/anggota.php =====
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
        $query = "SELECT a.*, u.nama, u.email, d.nidn FROM anggota a 
                  LEFT JOIN dosen d ON a.id_dosen = d.id_dosen 
                  LEFT JOIN users u ON d.id_user = u.id_user 
                  ORDER BY a.urutan, a.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        jsonResponse(true, '', $stmt->fetchAll());
        break;
        
    case 'add':
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = uploadFile($_FILES['foto'], 'anggota');
            if (!$upload['success']) jsonResponse(false, $upload['message']);
            $foto = $upload['path'];
        }
        
        $query = "SELECT add_anggota(:id_dosen, :jabatan, :deskripsi, :foto, :urutan)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':id_dosen' => $_POST['id_dosen'],
            ':jabatan' => $_POST['jabatan'],
            ':deskripsi' => $_POST['deskripsi'] ?? '',
            ':foto' => $foto,
            ':urutan' => $_POST['urutan'] ?? null
        ]);
        jsonResponse(true, 'Anggota berhasil ditambahkan');
        break;
        
    case 'update':
        $query_old = "SELECT foto FROM anggota WHERE id_anggota = :id";
        $stmt_old = $db->prepare($query_old);
        $stmt_old->execute([':id' => $_POST['id_anggota']]);
        $old_data = $stmt_old->fetch();
        
        $foto = $old_data['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = uploadFile($_FILES['foto'], 'anggota');
            if ($upload['success']) {
                if ($old_data['foto']) deleteFile($old_data['foto']);
                $foto = $upload['path'];
            }
        }
        
        $query = "SELECT update_anggota(:id, :jabatan, :deskripsi, :foto, :urutan, :is_active)";
        $stmt = $db->prepare($query);
        $is_active = isset($_POST['is_active']) && ($_POST['is_active'] === 'true' || $_POST['is_active'] === '1');
        $stmt->execute([
            ':id' => $_POST['id_anggota'],
            ':jabatan' => $_POST['jabatan'],
            ':deskripsi' => $_POST['deskripsi'] ?? '',
            ':foto' => $foto,
            ':urutan' => $_POST['urutan'] ?? 0,
            ':is_active' => $is_active
        ]);
        jsonResponse(true, 'Anggota berhasil diupdate');
        break;
        
    case 'delete':
        $query_old = "SELECT foto FROM anggota WHERE id_anggota = :id";
        $stmt_old = $db->prepare($query_old);
        $stmt_old->execute([':id' => $_POST['id_anggota']]);
        $old_data = $stmt_old->fetch();
        
        if ($old_data && $old_data['foto']) deleteFile($old_data['foto']);
        
        $query = "SELECT delete_anggota(:id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['id_anggota']]);
        jsonResponse(true, 'Anggota berhasil dihapus');
        break;
        
    case 'get':
        $query = "SELECT a.*, u.nama, u.email, d.nidn FROM anggota a 
                  LEFT JOIN dosen d ON a.id_dosen = d.id_dosen 
                  LEFT JOIN users u ON d.id_user = u.id_user 
                  WHERE a.id_anggota = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_GET['id']]);
        jsonResponse(true, '', $stmt->fetch());
        break;
}
?>