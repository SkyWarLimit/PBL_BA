<?php
// File: admin/config/database.php

// 1. Matikan display error HTML agar tidak merusak JSON pada API
ini_set('display_errors', 0);
error_reporting(E_ALL);

class Database {
    // Setting Database PostgreSQL Anda
    private $host = "localhost";
    private $db_name = "lab_ba";
    private $username = "postgres";
    private $password = "";
    private $port = "5432";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // =================================================================
            // === [MODIFIKASI: INJEKSI SESSION UNTUK TRIGGER LOG] ===
            // =================================================================
            // Bagian ini mengirimkan ID User & IP ke PostgreSQL agar Trigger bisa membacanya
            
            if (session_status() == PHP_SESSION_NONE) session_start();

            if (isset($_SESSION['user_id'])) {
                $uid = (int)$_SESSION['user_id']; 
                $ip  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

                // Set variabel sesi database (app.current_user_id & app.current_ip)
                $this->conn->exec("SET app.current_user_id = '$uid'");
                $this->conn->exec("SET app.current_ip = '$ip'");
            }
            // =================================================================

        } catch(PDOException $e) {
            // Jika request adalah API (mengandung /api/), kirim respon JSON
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Koneksi Database Gagal: " . $e->getMessage()]);
                exit;
            }
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
        return $this->conn;
    }
}

// ===== INISIALISASI VARIABEL GLOBAL =====
// Memastikan variabel $pdo dan $conn tersedia untuk semua file API
$database = new Database();
$pdo = $database->getConnection();
$conn = $pdo; // Alias $conn ditambahkan agar API lama tetap jalan
// ========================================


// ===== HELPER FUNCTIONS (TIDAK BERUBAH) =====

// Wrapper agar kode lama yang memanggil getDBConnection() tetap jalan
function getDBConnection() {
    global $pdo;
    return $pdo;
}

function jsonResponse($success, $message = '', $data = null) {
    // Bersihkan buffer output agar JSON valid
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Header anti-cache
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate"); 
    header("Pragma: no-cache"); 

    // Cek sesi user
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        // Jika request AJAX/API, kirim JSON error, bukan redirect HTML
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
            (strpos($_SERVER['REQUEST_URI'], '/api/') !== false)) {
            jsonResponse(false, 'Unauthorized: Silahkan login kembali.');
        }
        // Jika akses langsung browser, redirect
        // Asumsi path login ada di satu level di atas folder admin config
        header('Location: ../login.php'); 
        exit;
    }
}

function uploadFile($file, $folder_name, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']) {
    // Target: admin/uploads/{folder_name}/
    // Pastikan path ini sesuai struktur folder Anda
    $target_dir = __DIR__ . "/../../uploads/" . $folder_name . "/";
    
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan (hanya jpg, jpeg, png, gif)'];
    }
    
    // Generate nama file unik
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Return path relative untuk disimpan di database
        return ['success' => true, 'path' => 'uploads/' . $folder_name . '/' . $new_filename];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan file upload'];
    }
}

function deleteFile($file_path) {
    if (empty($file_path)) return false;
    // Hapus file fisik
    // Path fisik: admin/config/../../ + path database
    $full_path = __DIR__ . "/../../" . $file_path;
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    return false;
}

function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['FORWARDED_FOR']))
        $ipaddress = $_SERVER['FORWARDED_FOR'];
    else if(isset($_SERVER['FORWARDED']))
        $ipaddress = $_SERVER['FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
?>