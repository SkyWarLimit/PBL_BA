<?php
// File: admin/api/public_kontak.php
require_once '../../admin/config/database.php';

// Header agar bisa diakses dari frontend (CORS)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Ambil data dari Form
        $nama = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? ''; 
        $pesan = $_POST['message'] ?? '';

        // Validasi Sederhana
        if (empty($nama) || empty($email) || empty($pesan)) {
            throw new Exception("Nama, Email, dan Pesan wajib diisi!");
        }

        // --- PERBAIKAN DI SINI ---
        // PostgreSQL membutuhkan keyword 'false' atau string 'f' untuk boolean, bukan angka 0.
        // Kita gunakan 'false' secara eksplisit di dalam query.
        
        $sql = "INSERT INTO kontak (nama, email, subjek, pesan, is_read, created_at) 
                VALUES (?, ?, ?, ?, false, CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($sql);
        
        // Format subjek: "Telp: 08123..."
        $subjek_isi = !empty($phone) ? "Telp: " . $phone : "Pertanyaan Umum";

        if ($stmt->execute([$nama, $email, $subjek_isi, $pesan])) {
            echo json_encode(['success' => true, 'message' => 'Pesan Anda telah terkirim!']);
        } else {
            throw new Exception("Gagal menyimpan pesan ke database.");
        }

    } catch (Exception $e) {
        // Log error jika perlu untuk debugging server
        // error_log($e->getMessage()); 
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>