<?php
// File: admin/config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "lab_ba";
    private $username = "postgres";
    private $password = "Anasaurizky0705";
    private $port = "5432";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
        return $this->conn;
    }
}

function jsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate"); 
    header("Pragma: no-cache"); 

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php'); 
        exit;
    }
}

// ===== FUNGSI UPLOAD (DIPERBAIKI UNTUK SUB-FOLDER) =====
function uploadFile($file, $folder_name, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']) {
    // Target: admin/uploads/{folder_name}/
    $target_dir = __DIR__ . "/../uploads/" . $folder_name . "/";
    
    // Buat folder secara rekursif jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Path Database: uploads/{folder_name}/namafile.jpg
        return ['success' => true, 'path' => 'uploads/' . $folder_name . '/' . $new_filename];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

function deleteFile($file_path) {
    $full_path = __DIR__ . "/../" . $file_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}
?>