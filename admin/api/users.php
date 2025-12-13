<?php
// admin/api/users.php

// 1. Matikan error display
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // 2. Load Database
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

    // =================================================================
    // [PENTING] INJEKSI SESSION UNTUK TRIGGER LOG DATABASE
    // Kita kirim ID user ke DB, agar Trigger 'trg_log_users' tahu siapa pelakunya.
    // Kita SUDAH MENGHAPUS recordLog() agar tidak double.
    // =================================================================
    $currentUid = (int)$_SESSION['user_id'];
    $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    // Set variabel sesi PostgreSQL
    $conn->exec("SET app.current_user_id = '$currentUid'");
    $conn->exec("SET app.current_ip = '$currentIp'");
    // =================================================================

    $method = $_SERVER['REQUEST_METHOD'];

    // === GET: LIST USERS ===
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT id_user, nama, email, role, is_active, last_login FROM users ORDER BY nama ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        ob_clean();
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // === POST: UPDATE ROLE / DELETE ===
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        // UPDATE ROLE & STATUS
        // (Trigger 'trg_log_users' - UPDATE otomatis jalan)
        if ($action === 'update_role') {
            $id = (int)$_POST['id_user'];
            $role = $_POST['role']; 
            $is_active = isset($_POST['is_active']) ? 1 : 0; 

            // Validasi: Jangan biarkan admin men-downgrade diri sendiri
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id && $role != 'admin') {
                throw new Exception("Anda tidak dapat mengubah role akun sendiri menjadi bukan Admin.");
            }

            $sql = "UPDATE users SET role = :r, is_active = :a, updated_at = NOW() WHERE id_user = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':r' => $role, ':a' => $is_active, ':id' => $id]);

            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Role dan Status user berhasil diperbarui.']);
            exit;
        }

        // DELETE USER
        // (Trigger 'trg_log_users' - DELETE otomatis jalan)
        if ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
                throw new Exception("Tidak dapat menghapus akun yang sedang digunakan.");
            }

            // Hapus User
            $conn->prepare("DELETE FROM users WHERE id_user = :id")->execute([':id' => $id]);
            
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'User berhasil dihapus.']);
            exit;
        }
    }

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>