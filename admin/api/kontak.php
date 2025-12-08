<?php
// File: admin/api/kontak.php
require_once '../../admin/config/database.php';

// Pastikan session dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

checkAuth();
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // === GET DATA (AMBIL PESAN) ===
    if ($method == 'GET') {
        // Ambil semua pesan urut dari yang terbaru
        $stmt = $db->query("SELECT * FROM kontak ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    
    // === POST DATA (UPDATE STATUS / DELETE) ===
    elseif ($method == 'POST') {
        
        // 1. TANDAI SUDAH DIBACA
        if (isset($_POST['action']) && $_POST['action'] == 'mark_read') {
            $id = $_POST['id'];
            $stmt = $db->prepare("UPDATE kontak SET is_read = 1 WHERE id_kontak = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Pesan ditandai sudah dibaca']);
            } else {
                throw new Exception('Gagal update status.');
            }
        }

        // 2. HAPUS PESAN
        elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM kontak WHERE id_kontak = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus']);
            } else {
                throw new Exception('Gagal menghapus pesan.');
            }
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>