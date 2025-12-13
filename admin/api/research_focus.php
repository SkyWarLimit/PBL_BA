<?php
// 1. Matikan output error ke layar agar JSON tidak rusak
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. Buffer Output (PENTING: Mencegah error "Unexpected token <")
ob_start();

header('Content-Type: application/json');
session_start();

// Fungsi helper kirim JSON
function sendJson($success, $message, $data = []) {
    ob_clean(); // Hapus output sampah sebelum kirim
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // 3. LOAD DATABASE (Path Absolut)
    $dbPath = __DIR__ . '/../config/database.php';

    if (!file_exists($dbPath)) {
        throw new Exception("File database.php tidak ditemukan di: " . $dbPath);
    }

    // Include file database
    require_once $dbPath;

    // 4. DETEKSI KONEKSI OTOMATIS (MODIFIKASI UTAMA DISINI)
    // Kita cari variabel koneksi yang mungkin digunakan
    if (function_exists('getDBConnection')) {
        $conn = getDBConnection();
    } elseif (isset($conn)) {
        // Variabel umum: $conn
        // Hapus pengecekan 'instanceof PDO' agar support semua driver (PDO/MySQLi/PgSQL)
    } elseif (isset($pdo)) {
        $conn = $pdo;
    } elseif (isset($db)) {
        $conn = $db;
    } elseif (isset($mysqli)) {
        $conn = $mysqli;
    } else {
        // Debugging: Jika masih gagal, beri tahu variabel apa yang tersedia
        // Filter variabel sistem PHP agar tidak bingung
        $vars = array_keys(get_defined_vars());
        $userVars = array_diff($vars, ['GLOBALS', '_ENV', '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', 'dbPath']);
        $varList = implode(', ', $userVars);
        
        throw new Exception("Gagal Koneksi! Tidak ditemukan variabel database ($conn, $pdo, $db). Variabel yang ada di file: " . ($varList ?: 'Tidak ada'));
    }

    // Pastikan $conn benar-benar ada isinya
    if (empty($conn)) {
        throw new Exception("Variabel koneksi database terdeteksi NULL/Kosong.");
    }

    // 5. CEK AUTH
    if (!isset($_SESSION['user_id'])) {
        // Jika session belum ada, kirim error JSON (bukan redirect) agar frontend bisa handle
        throw new Exception("Sesi habis atau belum login.");
    }
    $userId = $_SESSION['user_id'];

    $method = $_SERVER['REQUEST_METHOD'];

    // --- GET DATA ---
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM research_focus WHERE id_research = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) throw new Exception("Data tidak ditemukan");
            
            sendJson(true, "Data ditemukan", $data);
        } else {
            $stmt = $conn->query("SELECT * FROM research_focus ORDER BY urutan ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJson(true, "Data dimuat", $data);
        }
    }

    // --- POST DATA ---
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        // HAPUS
        if ($action === 'delete') {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception("ID tidak valid");

            // Hapus file fisik
            $stmt = $conn->prepare("SELECT file_path FROM research_focus WHERE id_research = ?");
            $stmt->execute([$id]);
            $oldFile = $stmt->fetchColumn();
            
            if ($oldFile && file_exists(__DIR__ . '/../' . $oldFile)) {
                unlink(__DIR__ . '/../' . $oldFile);
            }

            $stmt = $conn->prepare("DELETE FROM research_focus WHERE id_research = ?");
            if ($stmt->execute([$id])) {
                sendJson(true, "Data berhasil dihapus");
            } else {
                throw new Exception("Gagal menghapus data");
            }
        }

        // SIMPAN / EDIT
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $urutan = $_POST['urutan'] ?? 0;
        $id_research = $_POST['id_research'] ?? '';

        // Upload File
        $filePath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/research/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($ext, $allowed)) throw new Exception("Format file harus JPG, PNG, atau WEBP");

            $fileName = 'res_' . time() . '_' . rand(100,999) . '.' . $ext;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                $filePath = 'uploads/research/' . $fileName;
            } else {
                throw new Exception("Gagal upload file");
            }
        }

        if (!empty($id_research)) {
            // UPDATE
            $chk = $conn->prepare("SELECT id_research FROM research_focus WHERE urutan = ? AND id_research != ?");
            $chk->execute([$urutan, $id_research]);
            if ($chk->rowCount() > 0) throw new Exception("Urutan $urutan sudah dipakai");

            $sql = "UPDATE research_focus SET judul=?, deskripsi=?, urutan=?, updated_at=NOW()";
            $params = [$judul, $deskripsi, $urutan];

            if ($filePath) {
                $qOld = $conn->prepare("SELECT file_path FROM research_focus WHERE id_research = ?");
                $qOld->execute([$id_research]);
                $oldP = $qOld->fetchColumn();
                if ($oldP && file_exists(__DIR__ . '/../' . $oldP)) unlink(__DIR__ . '/../' . $oldP);

                $sql .= ", file_path=?";
                $params[] = $filePath;
            }
            $sql .= " WHERE id_research=?";
            $params[] = $id_research;

            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) sendJson(true, "Data berhasil diupdate");
            else throw new Exception("Gagal update database");

        } else {
            // INSERT
            $chk = $conn->prepare("SELECT id_research FROM research_focus WHERE urutan = ?");
            $chk->execute([$urutan]);
            if ($chk->rowCount() > 0) throw new Exception("Urutan $urutan sudah dipakai");

            $sql = "INSERT INTO research_focus (judul, deskripsi, urutan, file_path, id_user, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$judul, $deskripsi, $urutan, $filePath, $userId])) {
                sendJson(true, "Data berhasil disimpan");
            } else {
                throw new Exception("Gagal insert database");
            }
        }
    }

} catch (Exception $e) {
    sendJson(false, $e->getMessage());
}
?>