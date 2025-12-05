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

    // ==================================================================
    // === GET DATA (READ PENDING SUBMISSIONS) ===
    // ==================================================================
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $sql = "SELECT * FROM news_submissions WHERE status = 'pending' ORDER BY tanggal_upload DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // MODIFIKASI: Memaksa path foto mengarah ke admin/uploads/newsInput/
        foreach ($data as &$row) {
            if (!empty($row['foto_path'])) {
                // Ambil hanya nama filenya saja (misal: foto.jpg) untuk menghindari path lama yang salah
                $fileName = basename($row['foto_path']);
                
                // Set path tampilan yang benar untuk admin panel
                // Lokasi fisik: admin/uploads/newsInput/
                // Dari admin/index.php aksesnya menjadi: ../admin/uploads/newsInput/
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

            // === 1. TENTUKAN SUMBER FILE (News Service Folder) ===
            // Kita ambil nama filenya saja dari database
            $fileName = basename($submission['foto_path']);

            // Root Path Server
            $rootPath = __DIR__ . '/../../'; 
            
            // Source: admin/uploads/newsInput/ (Sesuai request Anda)
            $sourceFile = $rootPath . 'admin/uploads/newsInput/' . $fileName;
            
            // Target: admin/uploads/berita/ (Folder tujuan berita resmi)
            $targetDir = $rootPath . 'admin/uploads/berita/';
            $targetFile = $targetDir . $fileName;

            // === 2. PINDAHKAN FILE ===
            // Pastikan folder tujuan ada
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            // Cek apakah file sumber ada di newsInput, lalu pindahkan
            if (file_exists($sourceFile)) {
                rename($sourceFile, $targetFile);
            }

            // === 3. SIMPAN DATA KE TABEL ARTIKEL ===
            // Path baru yang akan disimpan di database (untuk tabel artikel)
            $newDbPath = 'admin/uploads/berita/' . $fileName;

            // Generate Ringkasan
            $clean_konten = strip_tags($submission['deskripsi']);
            $ringkasan = substr($clean_konten, 0, 150) . (strlen($clean_konten) > 150 ? '...' : '');

            // Insert Query (Tanpa kolom uploaded_by dan is_published yang bikin error)
            $sqlInsert = "INSERT INTO artikel (
                            judul, konten, ringkasan, file_path, kategori, 
                            tanggal_upload, id_user, updated_at
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([
                $submission['judul'],
                $submission['deskripsi'], 
                $ringkasan,
                $newDbPath, // Path baru (admin/uploads/berita/...)
                $submission['kategori'],
                $submission['tanggal_upload'],
                $submission['id_user']
            ]);

            // === 4. UPDATE STATUS SUBMISSION ===
            $sqlUpdate = $db->prepare("UPDATE news_submissions SET status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id_submission = ?");
            $sqlUpdate->execute([$id]);

            $db->commit();
            $response = ['success' => true, 'message' => 'Berita disetujui. Foto berhasil dipindahkan ke folder berita.'];

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