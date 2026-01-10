<?php
// admin/api/berita.php

// 1. Matikan error display agar JSON aman
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // 2. Load Database (Metode Aman)
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
    
    // Adaptasi Koneksi
    if (isset($pdo) && $pdo) { $conn = $pdo; } 
    elseif (class_exists('Database')) { $db = new Database(); $conn = $db->getConnection(); } 
    else { throw new Exception("Koneksi database gagal."); }

    $method = $_SERVER['REQUEST_METHOD'];
    
    // =================================================================
    // MODIFIKASI DIMULAI DI SINI
    // =================================================================

    // Cek Login HANYA jika metodenya BUKAN GET (yaitu POST, yang digunakan untuk Insert/Update/Delete)
    if ($method !== 'GET') {
        if (!isset($_SESSION['user_id'])) {
            // Jika POST tanpa login, tolak akses
            throw new Exception("Unauthorized access. Anda harus login untuk mengubah data.");
        }

        // Jika user login, siapkan variabel session untuk log/trigger
        $currentUid = (int)$_SESSION['user_id'];
        $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $conn->exec("SET app.current_user_id = '$currentUid'");
        $conn->exec("SET app.current_ip = '$currentIp'");
        
        $user_id = $_SESSION['user_id']; // Definisikan user_id hanya jika login
    } else {
        // Jika metode GET (publik), set user_id default atau ambil dari session jika ada
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    // =================================================================
    // MODIFIKASI SELESAI
    // =================================================================

    // === GET DATA === (Sekarang bisa diakses publik karena pengecekan sudah dipindahkan)
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Ambil 1 data untuk Edit
            $stmt = $conn->prepare("SELECT * FROM view_artikel WHERE id_artikel = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonOutput(['success' => true, 'data' => $data]);
        } else {
            // Ambil semua data untuk Tabel
            $stmt = $conn->query("SELECT * FROM view_artikel ORDER BY tanggal_upload DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonOutput(['success' => true, 'data' => $data]);
        }
    }

    // === POST DATA === (Tetap memerlukan login karena pengecekan sudah dilakukan di atas)
    if ($method === 'POST') {
        $action = $_POST['action'] ?? 'save';

        // 1. DELETE ACTION
        if ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Ambil path file lama
            $stmt = $conn->prepare("SELECT file_path FROM artikel WHERE id_artikel = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Hapus file fisik
            if ($row && !empty($row['file_path'])) {
                $physicalPath = __DIR__ . '/../' . $row['file_path'];
                if (file_exists($physicalPath)) {
                    unlink($physicalPath);
                }
            }

            // Hapus DB (Trigger 'artikel_delete_log' akan berjalan otomatis di sini)
            $conn->prepare("DELETE FROM artikel WHERE id_artikel = ?")->execute([$id]);
            
            jsonOutput(['success' => true, 'message' => 'Artikel berhasil dihapus']);
        }

        // 2. SAVE / UPDATE ACTION
        else {
            $id_artikel = !empty($_POST['id_artikel']) ? (int)$_POST['id_artikel'] : null;
            $judul = $_POST['judul'] ?? '';
            $kategori = $_POST['kategori'] ?? 'News Latest';
            $konten = $_POST['deskripsi'] ?? '';
            $tanggal = $_POST['tanggal'] ?? date('Y-m-d H:i:s');
            
            // Buat Ringkasan Otomatis
            $clean_konten = strip_tags($konten);
            $ringkasan = substr($clean_konten, 0, 150) . (strlen($clean_konten) > 150 ? '...' : '');

            if (empty($judul) || empty($konten)) throw new Exception("Judul dan Konten wajib diisi!");

            // Upload Logic
            $filePath = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed)) throw new Exception("Format file tidak diizinkan.");

                $fileName = 'berita_' . time() . '_' . uniqid() . '.' . $ext;
                // Target: admin/uploads/berita/
                $targetDir = __DIR__ . '/../uploads/berita/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . $fileName)) {
                    $filePath = 'uploads/berita/' . $fileName;
                } else {
                    throw new Exception("Gagal upload file.");
                }
            }

            $conn->beginTransaction();
            try {
                if ($id_artikel) {
                    // UPDATE (Trigger 'artikel_update_log' akan berjalan otomatis)
                    if ($filePath) {
                        // Hapus foto lama
                        $old = $conn->prepare("SELECT file_path FROM artikel WHERE id_artikel=?");
                        $old->execute([$id_artikel]);
                        $rowOld = $old->fetch(PDO::FETCH_ASSOC);
                        if ($rowOld && $rowOld['file_path'] && file_exists(__DIR__.'/../'.$rowOld['file_path'])) {
                            unlink(__DIR__.'/../'.$rowOld['file_path']);
                        }

                        $sql = "UPDATE artikel SET judul=?, kategori=?, konten=?, ringkasan=?, tanggal_upload=?, file_path=?, updated_at=CURRENT_TIMESTAMP, id_user=? WHERE id_artikel=?";
                        $conn->prepare($sql)->execute([$judul, $kategori, $konten, $ringkasan, $tanggal, $filePath, $user_id, $id_artikel]);
                    } else {
                        $sql = "UPDATE artikel SET judul=?, kategori=?, konten=?, ringkasan=?, tanggal_upload=?, updated_at=CURRENT_TIMESTAMP, id_user=? WHERE id_artikel=?";
                        $conn->prepare($sql)->execute([$judul, $kategori, $konten, $ringkasan, $tanggal, $user_id, $id_artikel]);
                    }
                    $msg = 'Artikel diperbarui';
                    
                } else {
                    // INSERT (Trigger 'artikel_insert_log' akan berjalan otomatis)
                    if (!$filePath) throw new Exception("Foto wajib diupload!");
                    
                    $sql = "INSERT INTO artikel (judul, konten, ringkasan, file_path, kategori, tanggal_upload, updated_at, id_user) 
                            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?)";
                    $conn->prepare($sql)->execute([$judul, $konten, $ringkasan, $filePath, $kategori, $tanggal, $user_id]);
                    $msg = 'Artikel ditambahkan';
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