<?php
// File: admin/api/galeri.php
require_once '../../admin/config/database.php';

// Pastikan session dimulai sebelum mengambil data session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

checkAuth(); // Pastikan user sudah login
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// === GET DATA ===
if ($method == 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Ambil 1 data dari view_galeri untuk Edit
            $stmt = $db->prepare("SELECT * FROM view_galeri WHERE id_galeri = ?");
            $stmt->execute([$_GET['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            // Ambil semua data dari view_galeri untuk Tabel
            // View ini harusnya sudah men-join tabel users untuk dapat nama & role
            $stmt = $db->query("SELECT * FROM view_galeri ORDER BY id_galeri DESC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 

// === POST DATA (INSERT / UPDATE / DELETE) ===
elseif ($method == 'POST') {
    try {
        // 1. DELETE
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $stmt = $db->prepare("SELECT file_path FROM galeri WHERE id_galeri = ?");
            $stmt->execute([$_POST['id']]);
            $row = $stmt->fetch();
            
            if ($row && $row['file_path']) {
                deleteFile($row['file_path']);
            }

            $db->prepare("DELETE FROM galeri WHERE id_galeri = ?")->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Galeri berhasil dihapus']);
            exit;
        }

        // --- INPUT DATA ---
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $kategori = $_POST['kategori'] ?? 'Kategori 1';
        
        // [REVISI PENTING] Ambil ID User dari Session
        // Jika session kosong (aneh jika sudah checkAuth), fallback ke NULL
        $uploaded_by = $_SESSION['user_id'] ?? null; 

        if (empty($judul)) throw new Exception("Judul foto wajib diisi!");

        // 2. UPDATE
        if (isset($_POST['id_galeri']) && !empty($_POST['id_galeri'])) {
            $id = $_POST['id_galeri'];
            
            // Note: Kita biasanya TIDAK mengupdate uploader saat edit, 
            // biarkan tetap user aslinya. Jadi $uploaded_by tidak dimasukkan ke query update
            // kecuali Anda ingin mengubah pemiliknya menjadi pengedit.
            // Di sini saya asumsikan pemilik tidak berubah saat edit.
            
            $params = [$judul, $deskripsi, $kategori];
            $foto_query = "";

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $up = uploadFile($_FILES['foto'], 'galeri'); 
                if (!$up['success']) throw new Exception($up['message']);
                
                $old = $db->prepare("SELECT file_path FROM galeri WHERE id_galeri = ?");
                $old->execute([$id]);
                $row = $old->fetch();
                if ($row && $row['file_path']) deleteFile($row['file_path']);

                $foto_query = ", file_path = ?";
                $params[] = $up['path'];
            }
            $params[] = $id; 

            // Update tanpa mengubah uploaded_by
            $sql = "UPDATE galeri SET judul=?, deskripsi=?, kategori=? $foto_query WHERE id_galeri=?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute($params)) {
                echo json_encode(['success' => true, 'message' => 'Galeri berhasil diperbarui!']);
            } else {
                throw new Exception('Gagal update database.');
            }
        } 
        
        // 3. INSERT
        else {
            $foto_path = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $up = uploadFile($_FILES['foto'], 'galeri');
                if ($up['success']) $foto_path = $up['path'];
                else throw new Exception($up['message']);
            } else {
                throw new Exception("Wajib upload foto!");
            }

            // Insert dengan uploaded_by dari session
            $sql = "INSERT INTO galeri (judul, deskripsi, kategori, file_path, uploaded_by, is_active, tanggal_upload) 
                    VALUES (?, ?, ?, ?, ?, true, CURRENT_DATE)";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$judul, $deskripsi, $kategori, $foto_path, $uploaded_by])) {
                echo json_encode(['success' => true, 'message' => 'Foto berhasil ditambahkan!']);
            } else {
                throw new Exception('Gagal insert database.');
            }
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>