<?php
// File: logout.php
// Lokasi: PBL_BA/admin/logout.php

session_start();

// 1. REVISI: Gunakan __DIR__ agar path absolut dan aman
require_once __DIR__ . '/config/database.php';

// Cek apakah user memang sedang login sebelum mencatat log
if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Log logout activity
        $query = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address) 
                  VALUES (:id, 'LOGOUT', 'User berhasil logout', :ip)";
        $stmt = $db->prepare($query);
        
        $user_id = $_SESSION['user_id'];
        
        // 2. REVISI: Cek apakah fungsi getClientIP tersedia untuk mencegah Fatal Error
        if (function_exists('getClientIP')) {
            $ip = getClientIP();
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Fallback aman
        }
        
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->execute();
        
    } catch (PDOException $e) {
        // Silent fail - logout tetap berjalan meskipun logging gagal
        // Opsional: Catat error ke file log server
        error_log("Logout log error: " . $e->getMessage());
    }
}

// Destroy session (Hapus semua data sesi)
$_SESSION = array(); // Kosongkan variabel session
session_unset();     // Unset variabel
session_destroy();   // Hancurkan session

// 3. REVISI: Redirect dengan parameter pesan
// Arahkan kembali ke file login di folder yang sama (admin/login.php)
header('Location: login.php?pesan=logout');
exit;
?>