<?php
// admin/api/news_service.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];

try {
    $path1 = __DIR__ . '/../config/database.php';
    $path2 = __DIR__ . '/../../admin/config/database.php';

    if (file_exists($path1)) require_once $path1;
    elseif (file_exists($path2)) require_once $path2;
    else throw new Exception("File database.php tidak ditemukan.");

    if (!isset($_SESSION['user_id'])) throw new Exception('Sesi habis.');

    $db = (new Database())->getConnection();

    // =================================================================
    // [PENTING] INJEKSI SESSION UNTUK TRIGGER LOG DATABASE
    // =================================================================
    $currentUid = (int)$_SESSION['user_id'];
    $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    $db->exec("SET app.current_user_id = '$currentUid'");
    $db->exec("SET app.current_ip = '$currentIp'");
    // =================================================================

    // ==================================================================
    // === GET DATA (READ PENDING SUBMISSIONS) ===
    // ==================================================================
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $sql = "SELECT * FROM news_submissions WHERE status = 'pending' ORDER BY tanggal_upload DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Perbaiki path foto agar tampil di frontend admin
        foreach ($data as &$row) {
            if (!empty($row['foto_path'])) {
                $fileName = basename($row['foto_path']);
                // Path relatif dari admin/index.php ke uploads/newsInput/
                $row['foto_path'] = '../admin/uploads/newsInput/' . $fileName; 
            }
        }
        $response = ['success' => true, 'data' => $data];
    }

    // ==================================================================
    // === POST DATA (APPROVE / REJECT) ===
    // ==================================================================
    elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        $id = $_POST['id'] ?? 0;

        if (empty($id)) throw new Exception("ID tidak valid.");

        $stmt = $db->prepare("SELECT * FROM news_submissions WHERE id_submission = ?");
        $stmt->execute([$id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) throw new Exception("Data tidak ditemukan.");

        if ($action === 'approve') {
            $db->beginTransaction();

            // 1. PINDAHKAN FOTO
            $fileName = basename($submission['foto_path']);
            $rootPath = __DIR__ . '/../../'; 
            
            // Asal: admin/uploads/newsInput/
            $sourceFile = $rootPath . 'admin/uploads/newsInput/' . $fileName;
            
            // Tujuan: admin/uploads/berita/
            $targetDir = $rootPath . 'admin/uploads/berita/';
            $targetFile = $targetDir . $fileName;

            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            if (file_exists($sourceFile)) {
                rename($sourceFile, $targetFile);
            }

            // Path baru untuk DB
            $newDbPath = 'admin/uploads/berita/' . $fileName;

            // Generate Ringkasan
            $clean_konten = strip_tags($submission['deskripsi']);
            $ringkasan = substr($clean_konten, 0, 150) . (strlen($clean_konten) > 150 ? '...' : '');

            // 2. INSERT KE ARTIKEL
            // (Trigger 'artikel_insert_log' akan otomatis mencatat ini)
            $sqlInsert = "INSERT INTO artikel (
                            judul, konten, ringkasan, file_path, kategori, 
                            tanggal_upload, id_user, updated_at
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([
                $submission['judul'],
                $submission['deskripsi'], 
                $ringkasan,
                $newDbPath, 
                $submission['kategori'],
                $submission['tanggal_upload'],
                $submission['id_user']
            ]);

            // 3. UPDATE STATUS SUBMISSION
            // (Trigger 'trg_log_news' akan otomatis mencatat ini)
            $sqlUpdate = $db->prepare("UPDATE news_submissions SET status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id_submission = ?");
            $sqlUpdate->execute([$id]);

            $db->commit();
            $response = ['success' => true, 'message' => 'Berita disetujui.'];

        } elseif ($action === 'reject') {
            // (Trigger 'trg_log_news' akan otomatis mencatat ini)
            $stmt = $db->prepare("UPDATE news_submissions SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id_submission = ?");
            $stmt->execute([$id]);
            $response = ['success' => true, 'message' => 'Pengajuan ditolak.'];
        }
    }

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    $response = ['success' => false, 'message' => 'Gagal: ' . $e->getMessage()];
}

ob_clean();
echo json_encode($response);
exit;
?>