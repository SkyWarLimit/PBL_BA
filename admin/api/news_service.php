<?php
// File: admin/api/news_service.php

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $sql = "SELECT * FROM news_submissions WHERE status = 'pending' ORDER BY tanggal_upload DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tambahkan prefix ../ agar tampil di admin
        foreach ($data as &$row) {
            if (!empty($row['foto_path']) && strpos($row['foto_path'], '../') === false) {
                $row['foto_path'] = '../' . $row['foto_path']; 
            }
        }
        $response = ['success' => true, 'data' => $data];
    }

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

            // === PROSES PINDAH FILE ===
            // Path asal (di DB): admin/uploads/newsInput/file.jpg
            $oldDbPath = $submission['foto_path']; 
            
            // Bersihkan path jika ada '../' di database (untuk keamanan move)
            $cleanOldPath = str_replace('../', '', $oldDbPath);
            
            $fileName = basename($cleanOldPath);

            // Path Fisik di Server
            // __DIR__ = admin/api/
            // Root = admin/api/../../
            $rootPath = __DIR__ . '/../../'; 
            
            $sourceFile = $rootPath . $cleanOldPath;
            $targetDir = $rootPath . 'admin/uploads/berita/';
            $targetFile = $targetDir . $fileName;

            // Buat folder jika belum ada
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            // Pindahkan file
            if (file_exists($sourceFile)) {
                rename($sourceFile, $targetFile);
            }

            // Path Baru untuk disimpan di Tabel Artikel
            $newDbPath = 'admin/uploads/berita/' . $fileName;

            // Masukkan ke tabel ARTIKEL
            $sqlInsert = "INSERT INTO artikel (
                            judul, konten, file_path, kategori, is_published, 
                            tanggal_upload, uploaded_by, id_user, updated_at
                          ) VALUES (?, ?, ?, ?, true, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([
                $submission['judul'],
                $submission['deskripsi'], 
                $newDbPath, // Path baru
                $submission['kategori'],
                $submission['tanggal_upload'],
                $submission['id_user'], // ID User (Angka)
                $submission['id_user']  // ID User (Angka)
            ]);

            // Update status submission
            $sqlUpdate = $db->prepare("UPDATE news_submissions SET status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id_submission = ?");
            $sqlUpdate->execute([$id]);

            $db->commit();
            $response = ['success' => true, 'message' => 'Berita disetujui. Foto dipindahkan.'];

        } elseif ($action === 'reject') {
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