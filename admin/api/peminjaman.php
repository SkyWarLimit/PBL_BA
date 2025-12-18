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

    // ==================================================================
    // === GET: READ DATA (ADMIN MELIHAT SEMUA DATA) ===
    // ==================================================================
    if ($method === 'GET') {
        // Query dimodifikasi: Filter WHERE dihapus agar Admin bisa melihat semua status
        $sql = "SELECT p.*, dp.waktu_mulai AS check_in, dp.waktu_selesai AS check_out, 
                       u.nama AS nama_akun, u.email AS email_akun
                FROM peminjaman p
                JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                JOIN users u ON p.id_user = u.id_user
                ORDER BY p.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendJson(true, "Data loaded", $data);
    }

    // ==================================================================
    // === POST: CREATE / UPDATE / DELETE / REQUEST_CANCEL ===
    // ==================================================================
    if ($method === 'POST') {
        // Pengecekan Auth DITERAPKAN DI SINI untuk operasi POST
        if (!$userId) { 
            throw new Exception("Unauthorized: Silakan login terlebih dahulu.");
        }
        
        $action = $_POST['action'] ?? 'create';

        // --- CREATE BOOKING (USER) ---
        if ($action === 'create') {
            if (empty($_POST['tujuan']) || empty($_POST['check_in'])) {
                throw new Exception("Data tidak lengkap.");
            }

            $tujuan = $_POST['tujuan'];
            $kategori = $_POST['kategori_pemohon'];
            $nomorId = $_POST['nomor_identitas'];
            $instansi = $_POST['asal_instansi'];
            $hp = $_POST['no_hp'];
            $checkIn = $_POST['check_in'];
            $checkOut = $_POST['check_out'];
            $tanggalOnly = date('Y-m-d', strtotime($checkIn));

            $conn->beginTransaction();
            try {
                // Insert Peminjaman
                $sqlMain = "INSERT INTO peminjaman (
                                id_user, tujuan, kategori_pemohon, nomor_identitas, 
                                asal_instansi, no_hp, status, tanggal_peminjaman, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())";
                
                // Cek Driver untuk Last Insert ID (PostgreSQL vs MySQL)
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
        if ($action === 'update_status') {
             // Opsional: Validasi role admin bisa ditambahkan di sini
            
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
        if ($action === 'delete') {
             // Opsional: Validasi role admin bisa ditambahkan di sini

            $id = $_POST['id'];
            
            // Hapus detail dulu
            $conn->prepare("DELETE FROM detail_peminjaman WHERE id_peminjaman = ?")->execute([$id]);
            
            // Hapus induk
            if ($conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = ?")->execute([$id])) {
                sendJson(true, "Data dihapus");
            } else {
                throw new Exception("Gagal menghapus data");
            }
        }

        // --- REQUEST CANCEL (USER) ---
        // (BAGIAN INI YANG DITAMBAHKAN UNTUK MEMPERBAIKI ERROR)
        if ($action === 'request_cancel') {
            $id = $_POST['id_peminjaman'] ?? null;
            $alasan = $_POST['alasan_pembatalan'] ?? '';

            if (!$id) throw new Exception("ID Peminjaman tidak ditemukan.");
            if (empty($alasan)) throw new Exception("Alasan pembatalan wajib diisi.");

            // Validasi: Pastikan peminjaman ini milik user yang sedang login
            // dan statusnya masih memungkinkan untuk dibatalkan
            $checkSql = "SELECT status FROM peminjaman WHERE id_peminjaman = ? AND id_user = ?";
            $stmtCheck = $conn->prepare($checkSql);
            $stmtCheck->execute([$id, $userId]);
            $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$exists) {
                throw new Exception("Data booking tidak ditemukan atau bukan milik Anda.");
            }

            if ($exists['status'] === 'Rejected' || $exists['status'] === 'Cancelled' || $exists['status'] === 'Completed') {
                throw new Exception("Booking dengan status ini tidak dapat diajukan pembatalan.");
            }

            // Lakukan Update: Set request_pembatalan = true (atau 1)
            $updateSql = "UPDATE peminjaman 
                          SET request_pembatalan = ?, 
                              alasan_pembatalan = ?, 
                              updated_at = NOW() 
                          WHERE id_peminjaman = ?";
            
            $stmtUpdate = $conn->prepare($updateSql);
            
            // Parameter pertama: true (integer 1 agar kompatibel boolean MySQL/Postgres via PDO)
            if ($stmtUpdate->execute([1, $alasan, $id])) {
                sendJson(true, "Permintaan pembatalan berhasil dikirim. Menunggu persetujuan Admin.");
            } else {
                throw new Exception("Gagal mengajukan pembatalan.");
            }
        }
    }

} catch (Exception $e) {
    sendJson(false, $e->getMessage());
}
?>