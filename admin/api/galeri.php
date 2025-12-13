<?php
// admin/api/galeri.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // 1. Load Database
    $possiblePaths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php'
    ];
    $dbPath = null;
    foreach ($possiblePaths as $path) { if (file_exists($path)) { $dbPath = $path; break; }}
    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    // Start Session Manual jika belum ada
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Auth Check
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized access.");
    }

    if (isset($pdo) && $pdo) { $conn = $pdo; } 
    elseif (class_exists('Database')) { $db = new Database(); $conn = $db->getConnection(); } 
    else { throw new Exception("Koneksi database gagal."); }

    // =========================================================================
    // [PENTING] KONEKSI KE TRIGGER
    // =========================================================================
    // Kita set variabel sesi database agar Trigger (log_insert_trigger, dll)
    // bisa membaca siapa user yang sedang login lewat current_setting().
    // Asumsi ini menggunakan PostgreSQL berdasarkan sintaks gambar.
    // =========================================================================
    try {
        $uid = (int)$_SESSION['user_id'];
        // Mengirim ID user ke config session database
        $conn->exec("SET app.current_user_id = '$uid'");
    } catch (Exception $e) {
        // Abaikan error jika database tidak support setting ini, 
        // tapi log trigger mungkin akan mencatat user sebagai 'unknown'
    }
    // =========================================================================

    $method = $_SERVER['REQUEST_METHOD'];

    // === GET DATA ===
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM view_galeri WHERE id_galeri = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonOutput(['success' => true, 'data' => $data]);
        } else {
            $stmt = $conn->query("SELECT * FROM view_galeri ORDER BY id_galeri DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonOutput(['success' => true, 'data' => $data]);
        }
    }

    // === POST DATA ===
    if ($method === 'POST') {
        
        // -------------------------------------------------------------
        // DELETE ACTION 
        // (Saat baris ini jalan, Trigger 'galeri_delete_log' OTOMATIS jalan di DB)
        // -------------------------------------------------------------
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            
            // 1. Ambil path file
            $stmt = $conn->prepare("SELECT file_path FROM galeri WHERE id_galeri = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 2. Hapus file fisik
            if ($row && !empty($row['file_path'])) {
                $physicalPath = __DIR__ . '/../' . $row['file_path'];
                if (file_exists($physicalPath)) {
                    unlink($physicalPath);
                }
            }

            // 3. Hapus DB
            $conn->prepare("DELETE FROM galeri WHERE id_galeri = ?")->execute([$id]);
            
            jsonOutput(['success' => true, 'message' => 'Galeri berhasil dihapus']);
        }

        // -------------------------------------------------------------
        // SAVE / UPDATE ACTION 
        // (Saat INSERT/UPDATE jalan, Trigger terkait OTOMATIS jalan di DB)
        // -------------------------------------------------------------
        else {
            $id_galeri = !empty($_POST['id_galeri']) ? (int)$_POST['id_galeri'] : null;
            $judul = $_POST['judul'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $kategori = $_POST['kategori'] ?? 'Kategori 1';
            $uploaded_by = $_SESSION['user_id']; 

            if (empty($judul)) throw new Exception("Judul foto wajib diisi!");

            // Upload Foto
            $filePath = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed)) throw new Exception("Format file tidak diizinkan.");

                $fileName = 'galeri_' . time() . '_' . uniqid() . '.' . $ext;
                $targetDir = __DIR__ . '/../uploads/galeri/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . $fileName)) {
                    $filePath = 'uploads/galeri/' . $fileName;
                } else {
                    throw new Exception("Gagal upload file.");
                }
            }

            $conn->beginTransaction();
            try {
                if ($id_galeri) {
                    // UPDATE: Trigger 'galeri_update_log' akan menyala otomatis
                    if ($filePath) {
                        $old = $conn->prepare("SELECT file_path FROM galeri WHERE id_galeri=?");
                        $old->execute([$id_galeri]);
                        $rowOld = $old->fetch(PDO::FETCH_ASSOC);
                        if ($rowOld && $rowOld['file_path'] && file_exists(__DIR__.'/../'.$rowOld['file_path'])) {
                            unlink(__DIR__.'/../'.$rowOld['file_path']);
                        }

                        $sql = "UPDATE galeri SET judul=?, deskripsi=?, kategori=?, file_path=? WHERE id_galeri=?";
                        $conn->prepare($sql)->execute([$judul, $deskripsi, $kategori, $filePath, $id_galeri]);
                    } else {
                        $sql = "UPDATE galeri SET judul=?, deskripsi=?, kategori=? WHERE id_galeri=?";
                        $conn->prepare($sql)->execute([$judul, $deskripsi, $kategori, $id_galeri]);
                    }
                    $msg = 'Galeri diperbarui';
                } else {
                    // INSERT: Trigger 'galeri_insert_log' akan menyala otomatis
                    if (empty($filePath)) throw new Exception("Foto wajib diupload!");
                    
                    $sql = "INSERT INTO galeri (judul, deskripsi, kategori, file_path, uploaded_by, is_active, tanggal_upload) 
                            VALUES (?, ?, ?, ?, ?, true, CURRENT_DATE)";
                    $conn->prepare($sql)->execute([$judul, $deskripsi, $kategori, $filePath, $uploaded_by]);
                    $msg = 'Foto ditambahkan';
                }

                $conn->commit();
                jsonOutput(['success' => true, 'message' => $msg]);

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
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