<?php
// admin/api/peminjaman.php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
session_start();

function sendJson($success, $message, $data = []) {
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
    foreach ($possiblePaths as $path) { if (file_exists($path)) { $dbPath = $path; break; }}
    
    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    // Adaptasi Koneksi
    if (isset($pdo) && $pdo) { $conn = $pdo; } 
    elseif (class_exists('Database')) { $db = new Database(); $conn = $db->getConnection(); } 
    else { throw new Exception("Koneksi database gagal."); }

    if (!$conn) throw new Exception("Gagal terhubung ke database.");

    // Mendefinisikan userId, mungkin null jika belum login.
    $userId = $_SESSION['user_id'] ?? null;
    
    $method = $_SERVER['REQUEST_METHOD'];

    // === GET: READ DATA (DIPERBOLEHKAN UNTUK PUBLIK/NON-LOGIN) ===
    if ($method === 'GET') {
        // Ambil data (Filter hanya status yang sudah diizinkan untuk ditampilkan ke publik)
        $sql = "SELECT p.*, dp.waktu_mulai AS check_in, dp.waktu_selesai AS check_out, 
                         u.nama AS nama_akun, u.email AS email_akun
                  FROM peminjaman p
                  JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                  JOIN users u ON p.id_user = u.id_user
                  -- HANYA TAMPILKAN BOOKING YANG SUDAH DISETUJUI DI TABLE PUBLIK
                  WHERE p.status IN ('Approved', 'Confirmed') 
                  ORDER BY p.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendJson(true, "Data loaded", $data);
    }

    // === POST: CREATE / UPDATE / DELETE (MEMERLUKAN LOGIN) ===
    if ($method === 'POST') {
        // Pengecekan Auth DITERAPKAN DI SINI untuk operasi POST
        if (!$userId) { 
            // Tambahkan pengecualian yang lebih jelas jika mencoba POST tanpa login
            throw new Exception("Unauthorized: Silakan login terlebih dahulu untuk melakukan Pemesanan Lab.");
        }
        
        $action = $_POST['action'] ?? 'create';

        // --- CREATE BOOKING (USER) ---
        // Trigger peminjaman_insert_log akan otomatis aktif
        if ($action === 'create') {
            if (empty($_POST['tujuan']) || empty($_POST['check_in'])) {
                throw new Exception("Data tidak lengkap.");
            }

            $tujuan = $_POST['tujuan'];
            $kategori = $_POST['kategori_pemohon'];
            $nomorId = $_POST['nomor_identitas'];
            $instansi = $_POST['asal_instansi'];
            $hp = $_POST['no_handphone'];
            $checkIn = $_POST['check_in'];
            $checkOut = $_POST['check_out'];
            $tanggalOnly = date('Y-m-d', strtotime($checkIn));

            $conn->beginTransaction();
            try {
                // Insert Peminjaman (Trigger insert log jalan di sini)
                $sqlMain = "INSERT INTO peminjaman (
                                id_user, tujuan, kategori_pemohon, nomor_identitas, 
                                asal_instansi, no_handphone, status, tanggal_peminjaman, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())";
                
                // PostgreSQL: Gunakan RETURNING id_peminjaman untuk dapat ID
                if ($conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                    $sqlMain .= " RETURNING id_peminjaman";
                    $stmtMain = $conn->prepare($sqlMain);
                    $stmtMain->execute([$userId, $tujuan, $kategori, $nomorId, $instansi, $hp, $tanggalOnly]);
                    $lastId = $stmtMain->fetchColumn();
                } else {
                    // MySQL Fallback
                    $stmtMain = $conn->prepare($sqlMain);
                    $stmtMain->execute([$userId, $tujuan, $kategori, $nomorId, $instansi, $hp, $tanggalOnly]);
                    $lastId = $conn->lastInsertId();
                }

                if (!$lastId) throw new Exception("Gagal mendapatkan ID Peminjaman.");

                // Insert Detail
                $stmtDetail = $conn->prepare("INSERT INTO detail_peminjaman (id_peminjaman, waktu_mulai, waktu_selesai) VALUES (?, ?, ?)");
                $stmtDetail->execute([$lastId, $checkIn, $checkOut]);

                $conn->commit();
                sendJson(true, "Booking berhasil disimpan! Menunggu konfirmasi Admin.");

            } catch (Exception $ex) {
                $conn->rollBack();
                throw new Exception("Database Error: " . $ex->getMessage());
            }
        }

        // --- UPDATE STATUS (ADMIN) ---
        // Trigger peminjaman_update_log akan otomatis aktif
        if ($action === 'update_status') {
             // Opsional: Cek role admin di sini jika Anda memilikinya
             // if ($_SESSION['role'] !== 'admin') throw new Exception("Akses ditolak.");

            $id = $_POST['id'];
            $status = $_POST['status'];
            $alasan = $_POST['alasan_batal'] ?? null;

            $sql = "UPDATE peminjaman SET status = ?, alasan_batal = ?, updated_at = NOW() WHERE id_peminjaman = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$status, $alasan, $id])) {
                sendJson(true, "Status berhasil diubah");
            } else {
                throw new Exception("Gagal update status");
            }
        }

        // --- DELETE (ADMIN) ---
        // Trigger peminjaman_delete_log akan otomatis aktif
        if ($action === 'delete') {
             // Opsional: Cek role admin di sini jika Anda memilikinya
             // if ($_SESSION['role'] !== 'admin') throw new Exception("Akses ditolak.");
             
            $id = $_POST['id'];
            
            // Hapus detail dulu (manual jika foreign key tdk cascade)
            $conn->prepare("DELETE FROM detail_peminjaman WHERE id_peminjaman = ?")->execute([$id]);
            
            // Hapus induk (Trigger delete log jalan di sini)
            if ($conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = ?")->execute([$id])) {
                sendJson(true, "Data dihapus");
            } else {
                throw new Exception("Gagal menghapus data");
            }
        }
    }

} catch (Exception $e) {
    // Jika GET gagal karena database, atau POST gagal karena auth/logic
    sendJson(false, $e->getMessage());
}
?>