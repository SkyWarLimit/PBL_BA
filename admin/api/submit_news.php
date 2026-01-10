<?php
// File: admin/api/submit_news.php

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];

try {
    // 1. KONEKSI DATABASE
    $path1 = __DIR__ . '/../config/database.php';
    $path2 = __DIR__ . '/../../admin/config/database.php';

    if (file_exists($path1)) require_once $path1;
    elseif (file_exists($path2)) require_once $path2;
    else throw new Exception("File database.php tidak ditemukan.");

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Anda harus login terlebih dahulu.');
    }

    $db = (new Database())->getConnection();

    // 2. AMBIL DATA FORM
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    // PERBAIKAN: Ambil kategori dari dropdown user
    $kategori = !empty($_POST['kategori']) ? $_POST['kategori'] : 'User Submission';
    
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $id_user = $_SESSION['user_id'];
    $uploaded_by = $_SESSION['nama'] ?? 'User'; 

    // 3. UPLOAD FOTO
    $dbFilePath = ''; 
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        // Simpan sementara di folder newsInput
        // Path fisik: project_root/admin/uploads/newsInput/
        $uploadDir = __DIR__ . '/../uploads/newsInput/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) throw new Exception("Gagal membuat folder upload.");
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $newFileName = 'sub_' . time() . '_' . uniqid() . '.' . $extension;
        $targetFile = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            // Path yang disimpan di database (Relatif agar mudah dipanggil)
            $dbFilePath = 'admin/uploads/newsInput/' . $newFileName; 
        } else {
            throw new Exception("Gagal memindahkan file gambar.");
        }
    } else {
        throw new Exception("Wajib menyertakan foto.");
    }

    // 4. INSERT DATABASE (TABEL SUBMISSIONS)
    $sql = "INSERT INTO news_submissions 
            (id_user, judul, deskripsi, kategori, foto_path, tanggal_upload, uploaded_by, status, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)";

    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $id_user,
        $judul,
        $deskripsi,
        $kategori, // Masukkan Kategori Pilihan User
        $dbFilePath,
        $tanggal,
        $uploaded_by // Di tabel submission boleh simpan nama string
    ]);

    if ($result) {
        $response = ['success' => true, 'message' => 'Berita berhasil dikirim! Menunggu persetujuan admin.'];
    } else {
        throw new Exception("Gagal menyimpan data ke database.");
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

ob_clean();
echo json_encode($response);
exit;
?>