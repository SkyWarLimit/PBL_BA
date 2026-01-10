<?php
// File: admin/api/roadmap.php

// 1. Mencegah output sampah
ob_start();

// 2. Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Include Config
require_once '../config/database.php';

// 4. Koneksi Database
$database = new Database();
$pdo = $database->getConnection();

// 5. Header JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

$response = [];

// --- Helper Functions ---
function sendJson($success, $message, $data = []) {
    global $response;
    $response['success'] = $success;
    $response['message'] = $message;
    $response['data'] = $data;
    ob_clean(); 
    echo json_encode($response);
    exit;
}

// --- Main Logic ---
try {
    if (!$pdo) throw new Exception("Koneksi database gagal.");

    // =================================================================
    // [PENTING] INJEKSI SESSION UNTUK TRIGGER LOG DATABASE
    // Mengirim ID User ke PostgreSQL agar Trigger 'trg_log_roadmap' tahu siapa pelakunya.
    // Kode catatLog() SUDAH DIHAPUS agar tidak double log.
    // =================================================================
    $currentUid = (int)($_SESSION['user_id'] ?? 0);
    $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    $pdo->exec("SET app.current_user_id = '$currentUid'");
    $pdo->exec("SET app.current_ip = '$currentIp'");
    // =================================================================

    $method = $_SERVER['REQUEST_METHOD'];

    // === GET ===
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM roadmap WHERE id_roadmap = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $data ? sendJson(true, 'Ditemukan', $data) : sendJson(false, 'Tidak ditemukan');
        } else {
            $stmt = $pdo->query("SELECT * FROM roadmap ORDER BY urutan ASC");
            sendJson(true, 'List roadmap', $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    }

    // === POST ===
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        // --- HAPUS ---
        // (Trigger 'trg_log_roadmap' - DELETE otomatis jalan)
        if ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM roadmap WHERE id_roadmap = ?");
                $stmt->execute([$id]);
                
                sendJson(true, 'Data berhasil dihapus');
            }
            sendJson(false, 'ID tidak valid');
        }

        // --- SIMPAN (INSERT / UPDATE) ---
        $tahun = $_POST['tahun'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $urutan = $_POST['urutan'] ?? 0;
        $id_roadmap = $_POST['id_roadmap'] ?? ''; 

        if (empty($tahun) || empty($judul)) {
            sendJson(false, 'Tahun dan Judul wajib diisi');
        }

        // Validasi Urutan Unik
        $sqlCheck = "SELECT COUNT(*) FROM roadmap WHERE urutan = ? AND id_roadmap != ?";
        $checkId = !empty($id_roadmap) ? $id_roadmap : 0;
        
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$urutan, $checkId]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            sendJson(false, "Urutan '$urutan' sudah digunakan. Silakan pilih nomor urut lain.");
        }

        if (!empty($id_roadmap)) {
            // UPDATE (Trigger 'trg_log_roadmap' - UPDATE otomatis jalan)
            $sql = "UPDATE roadmap SET tahun = ?, judul = ?, deskripsi = ?, urutan = ? WHERE id_roadmap = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tahun, $judul, $deskripsi, $urutan, $id_roadmap]);
            
            sendJson(true, 'Data berhasil diperbarui');
        } else {
            // INSERT (Trigger 'trg_log_roadmap' - INSERT otomatis jalan)
            $sql = "INSERT INTO roadmap (tahun, judul, deskripsi, urutan) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tahun, $judul, $deskripsi, $urutan]);
            
            sendJson(true, 'Data berhasil ditambahkan');
        }
    }

} catch (Exception $e) {
    sendJson(false, 'Server Error: ' . $e->getMessage());
}
?>