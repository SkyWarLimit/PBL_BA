<?php
// admin/api/kontak.php

// 1. Matikan error display agar JSON tidak rusak
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // 2. Load Database (Metode Robust)
    $possiblePaths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php'
    ];
    $dbPath = null;
    foreach ($possiblePaths as $path) { if (file_exists($path)) { $dbPath = $path; break; }}
    
    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    // Start Session
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Auth Check
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized access.");
    }

    // Adaptasi Koneksi
    if (isset($pdo) && $pdo) { $conn = $pdo; } 
    elseif (class_exists('Database')) { $db = new Database(); $conn = $db->getConnection(); } 
    else { throw new Exception("Koneksi database gagal."); }

    $method = $_SERVER['REQUEST_METHOD'];

    // === GET DATA (AMBIL PESAN) ===
    if ($method === 'GET') {
        // Ambil semua pesan urut dari yang terbaru
        $stmt = $conn->query("SELECT * FROM kontak ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonOutput(['success' => true, 'data' => $data]);
    } 
    
    // === POST DATA (UPDATE STATUS / DELETE) ===
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        // -------------------------------------------------------------
        // 1. TANDAI SUDAH DIBACA (Trigger kontak_update_log otomatis jalan)
        // -------------------------------------------------------------
        if ($action === 'mark_read') {
            $id = (int)$_POST['id'];
            
            $stmt = $conn->prepare("UPDATE kontak SET is_read = 1 WHERE id_kontak = ?");
            
            if ($stmt->execute([$id])) {
                jsonOutput(['success' => true, 'message' => 'Pesan ditandai sudah dibaca']);
            } else {
                throw new Exception('Gagal update status.');
            }
        }

        // -------------------------------------------------------------
        // 2. HAPUS PESAN (Trigger kontak_delete_log otomatis jalan)
        // -------------------------------------------------------------
        elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            $stmt = $conn->prepare("DELETE FROM kontak WHERE id_kontak = ?");
            
            if ($stmt->execute([$id])) {
                jsonOutput(['success' => true, 'message' => 'Pesan berhasil dihapus']);
            } else {
                throw new Exception('Gagal menghapus pesan.');
            }
        }
    }

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

function jsonOutput($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}
?>