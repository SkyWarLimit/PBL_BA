<?php
// === ANTI-CRASH & DEBUGGING ===
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'SYSTEM ERROR: ' . $error['message'] . ' on line ' . $error['line']]);
        exit;
    }
});

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json; charset=utf-8');

function sendJson($success, $message, $data = [])
{
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    // 1. Load Database
    $possiblePaths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php'
    ];
    $dbPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $dbPath = $path;
            break;
        }
    }

    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    // Adaptasi Koneksi
    if (isset($pdo) && $pdo) {
        $conn = $pdo;
    } elseif (class_exists('Database')) {
        $db = new Database();
        $conn = $db->getConnection();
    } else {
        throw new Exception("Koneksi database gagal.");
    }

    // =================================================================
    // [PENTING] INJEKSI SESSION UNTUK TRIGGER LOG DATABASE
    // Kita kirim ID user ke DB agar Trigger 'trg_log_fasilitas' tahu siapa pelakunya.
    // Kita SUDAH MENGHAPUS catatLog() agar tidak double.
    // =================================================================
    $currentUid = (int)($_SESSION['user_id'] ?? 0);
    $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $conn->exec("SET app.current_user_id = '$currentUid'");
    $conn->exec("SET app.current_ip = '$currentIp'");
    // =================================================================

    // 3. Proses Request
    $method = $_SERVER['REQUEST_METHOD'];
    $userId = $_SESSION['user_id'] ?? 1;

    // --- GET DATA ---
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM lab_facilities WHERE id_facility = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            sendJson(true, 'Data fetched', $data ? $data : []);
        } else {
            // Join ke users jika ada relasi
            $sql = "SELECT f.*, u.nama as uploader_name 
                    FROM lab_facilities f
                    LEFT JOIN users u ON f.id_user = u.id_user 
                    ORDER BY f.created_at DESC";
            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(true, 'List fetched', $data);
        }
    }

    // --- POST DATA ---
    if ($method === 'POST') {
        $action = $_POST['action'] ?? 'save';

        // DELETE (Trigger 'trg_log_fasilitas' DELETE otomatis jalan)
        if ($action === 'delete') {
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID kosong.");

            // Hapus DB
            // KITA HAPUS KODE catatLog() DARI SINI
            $stmt = $conn->prepare("DELETE FROM lab_facilities WHERE id_facility = ?");
            $stmt->execute([$id]);

            sendJson(true, 'Berhasil dihapus.');
        }

        // SAVE / UPDATE (Trigger INSERT/UPDATE otomatis jalan)
        $nama = trim($_POST['nama_fasilitas'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi bi-box-seam');
        $id   = $_POST['id_facility'] ?? '';

        if (empty($nama)) throw new Exception("Nama fasilitas wajib diisi.");

        if (!empty($id)) {
            // UPDATE
            $sql = "UPDATE lab_facilities 
                    SET nama_fasilitas = ?, icon = ?, id_user = ?, updated_at = NOW() 
                    WHERE id_facility = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nama, $icon, $userId, $id]);

            // KITA HAPUS KODE catatLog() DARI SINI
            sendJson(true, 'Data berhasil diperbarui.');
        } else {
            // INSERT
            $sql = "INSERT INTO lab_facilities (nama_fasilitas, icon, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nama, $icon]);

            // KITA HAPUS KODE catatLog() DARI SINI
            sendJson(true, 'Data berhasil ditambahkan.');
        }
    }
} catch (Exception $e) {
    sendJson(false, 'Error: ' . $e->getMessage());
}
