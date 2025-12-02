<?php
// File: logout.php
// Logout handler dengan error handling

session_start();

require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Log logout activity
        $query = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address) 
                  VALUES (:id, 'LOGOUT', 'User berhasil logout', :ip)";
        $stmt = $db->prepare($query);
        
        $user_id = $_SESSION['user_id'];
        $ip = getClientIP();
        
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->execute();
        
    } catch (PDOException $e) {
        // Silent fail - logout tetap berjalan meskipun logging gagal
        error_log("Logout log error: " . $e->getMessage());
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
?>