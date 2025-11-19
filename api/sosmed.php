<?php
// ==================== api/sosmed.php ====================
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'list':
        getSosmedList($koneksi);
        break;
    case 'detail':
        getSosmedDetail($koneksi, $_GET['id']);
        break;
    case 'add':
        addSosmed($koneksi);
        break;
    case 'update':
        updateSosmed($koneksi);
        break;
    case 'delete':
        deleteSosmed($koneksi);
        break;
}

function getSosmedList($koneksi) {
    $query = "SELECT * FROM sosmed_lab WHERE is_active = TRUE ORDER BY id_sosmed ASC";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_all($result);
        echo json_encode([
            'success' => true,
            'data' => $data ? $data : []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
}

function getSosmedDetail($koneksi, $id) {
    $id = pg_escape_string($koneksi, $id);
    $query = "SELECT * FROM sosmed_lab WHERE id_sosmed = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . pg_last_error($koneksi)
        ]);
    }
}

function addSosmed($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $platform = pg_escape_string($koneksi, $data['platform']);
    $username = isset($data['username']) ? pg_escape_string($koneksi, $data['username']) : '';
    $link_url = pg_escape_string($koneksi, $data['link_url']);
    
    // Get icon based on platform
    $icons = [
        'instagram' => 'bi-instagram',
        'facebook' => 'bi-facebook',
        'twitter' => 'bi-twitter-x',
        'youtube' => 'bi-youtube',
        'linkedin' => 'bi-linkedin',
        'tiktok' => 'bi-tiktok'
    ];
    $icon = isset($icons[$platform]) ? $icons[$platform] : 'bi-link';
    
    $usernameValue = $username ? "'$username'" : "NULL";
    
    $query = "INSERT INTO sosmed_lab (platform, username, link_url, icon) 
              VALUES ('$platform', $usernameValue, '$link_url', '$icon')";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Sosial media berhasil ditambahkan'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah sosial media: ' . pg_last_error($koneksi)
        ]);
    }
}

function updateSosmed($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $id = intval($data['id']);
    $platform = pg_escape_string($koneksi, $data['platform']);
    $username = isset($data['username']) ? pg_escape_string($koneksi, $data['username']) : '';
    $link_url = pg_escape_string($koneksi, $data['link_url']);
    
    // Get icon based on platform
    $icons = [
        'instagram' => 'bi-instagram',
        'facebook' => 'bi-facebook',
        'twitter' => 'bi-twitter-x',
        'youtube' => 'bi-youtube',
        'linkedin' => 'bi-linkedin',
        'tiktok' => 'bi-tiktok'
    ];
    $icon = isset($icons[$platform]) ? $icons[$platform] : 'bi-link';
    
    $usernameValue = $username ? "'$username'" : "NULL";
    
    $query = "UPDATE sosmed_lab 
              SET platform = '$platform', username = $usernameValue, 
                  link_url = '$link_url', icon = '$icon'
              WHERE id_sosmed = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Sosial media berhasil diupdate'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update sosial media: ' . pg_last_error($koneksi)
        ]);
    }
}

function deleteSosmed($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = intval($data['id']);
    
    // Soft delete
    $query = "UPDATE sosmed_lab SET is_active = FALSE WHERE id_sosmed = $id";
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Sosial media berhasil dihapus'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus sosial media: ' . pg_last_error($koneksi)
        ]);
    }
}
?>