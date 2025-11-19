<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($method) {
    case 'GET':
        if ($action == 'list') {
            getPeminjaman($koneksi);
        } elseif ($action == 'detail') {
            getDetailPeminjaman($koneksi, $_GET['id']);
        } elseif ($action == 'stats') {
            getStats($koneksi);
        }
        break;
    
    case 'POST':
        if ($action == 'approve') {
            approvePeminjaman($koneksi);
        } elseif ($action == 'reject') {
            rejectPeminjaman($koneksi);
        } elseif ($action == 'cancel') {
            cancelPeminjaman($koneksi);
        } elseif ($action == 'reject_cancel') {
            rejectCancelPeminjaman($koneksi);
        }
        break;
}

function getPeminjaman($koneksi) {
    $status = isset($_GET['status']) ? pg_escape_string($koneksi, $_GET['status']) : '';
    $tanggal = isset($_GET['tanggal']) ? pg_escape_string($koneksi, $_GET['tanggal']) : '';
    $search = isset($_GET['search']) ? pg_escape_string($koneksi, $_GET['search']) : '';
    
    $query = "SELECT 
                p.id_peminjaman,
                p.tanggal_peminjaman,
                p.status,
                p.request_pembatalan,
                p.alasan_pembatalan,
                p.catatan_admin,
                pm.nama as peminjam,
                pm.email,
                pm.instansi,
                pm.tujuan,
                STRING_AGG(
                    CONCAT(TO_CHAR(dp.waktu_mulai, 'HH24:MI'), ' - ', TO_CHAR(dp.waktu_selesai, 'HH24:MI')),
                    ', '
                ) as waktu
              FROM peminjaman p
              JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
              LEFT JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
              WHERE 1=1";
    
    if ($status) {
        $query .= " AND p.status = '$status'";
    }
    if ($tanggal) {
        $query .= " AND p.tanggal_peminjaman = '$tanggal'";
    }
    if ($search) {
        $query .= " AND pm.nama ILIKE '%$search%'";
    }
    
    $query .= " GROUP BY p.id_peminjaman, p.request_pembatalan, p.alasan_pembatalan, p.catatan_admin, pm.id_peminjam 
                ORDER BY p.created_at DESC";
    
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

function getDetailPeminjaman($koneksi, $id) {
    $id = pg_escape_string($koneksi, $id);
    
    $query = "SELECT 
                p.*,
                pm.nama,
                pm.email,
                pm.status_peminjam,
                pm.instansi,
                pm.departemen,
                pm.no_hp,
                pm.tujuan
              FROM peminjaman p
              JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
              WHERE p.id_peminjaman = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        $data = pg_fetch_assoc($result);
        
        // Get detail waktu
        $query2 = "SELECT waktu_mulai, waktu_selesai FROM detail_peminjaman WHERE id_peminjaman = $id";
        $result2 = pg_query($koneksi, $query2);
        $detail_waktu = pg_fetch_all($result2);
        
        $data['detail_waktu'] = json_encode($detail_waktu ? $detail_waktu : []);
        
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

function getStats($koneksi) {
    $stats = [];
    
    $result = pg_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'");
    $stats['pending'] = pg_fetch_assoc($result)['total'];
    
    $result = pg_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE request_pembatalan = TRUE");
    $stats['pembatalan'] = pg_fetch_assoc($result)['total'];
    
    $result = pg_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'approved'");
    $stats['approved'] = pg_fetch_assoc($result)['total'];
    
    $result = pg_query($koneksi, "SELECT COUNT(*) as total FROM kontak WHERE is_read = FALSE");
    $stats['kontak'] = pg_fetch_assoc($result)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function approvePeminjaman($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = pg_escape_string($koneksi, $data['id']);
    $catatan = isset($data['catatan']) ? pg_escape_string($koneksi, $data['catatan']) : null;
    
    pg_query($koneksi, "BEGIN");
    
    $query = "UPDATE peminjaman 
              SET status = 'approved', 
                  catatan_admin = " . ($catatan ? "'$catatan'" : "NULL") . ",
                  updated_at = NOW()
              WHERE id_peminjaman = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        if (isset($_SESSION['user_id'])) {
            logActivity($koneksi, $_SESSION['user_id'], 'approve_peminjaman', "Menyetujui peminjaman #$id");
        }
        
        pg_query($koneksi, "COMMIT");
        
        echo json_encode([
            'success' => true,
            'message' => 'Peminjaman berhasil disetujui'
        ]);
    } else {
        pg_query($koneksi, "ROLLBACK");
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyetujui peminjaman: ' . pg_last_error($koneksi)
        ]);
    }
}

function rejectPeminjaman($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = pg_escape_string($koneksi, $data['id']);
    $catatan = isset($data['catatan']) ? pg_escape_string($koneksi, $data['catatan']) : null;
    
    pg_query($koneksi, "BEGIN");
    
    $query = "UPDATE peminjaman 
              SET status = 'rejected', 
                  catatan_admin = " . ($catatan ? "'$catatan'" : "NULL") . ",
                  updated_at = NOW()
              WHERE id_peminjaman = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        if (isset($_SESSION['user_id'])) {
            logActivity($koneksi, $_SESSION['user_id'], 'reject_peminjaman', "Menolak peminjaman #$id");
        }
        
        pg_query($koneksi, "COMMIT");
        
        echo json_encode([
            'success' => true,
            'message' => 'Peminjaman berhasil ditolak'
        ]);
    } else {
        pg_query($koneksi, "ROLLBACK");
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menolak peminjaman: ' . pg_last_error($koneksi)
        ]);
    }
}

function cancelPeminjaman($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = pg_escape_string($koneksi, $data['id']);
    
    pg_query($koneksi, "BEGIN");
    
    $query = "UPDATE peminjaman 
              SET status = 'cancelled',
                  request_pembatalan = FALSE,
                  updated_at = NOW()
              WHERE id_peminjaman = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        if (isset($_SESSION['user_id'])) {
            logActivity($koneksi, $_SESSION['user_id'], 'cancel_peminjaman', "Membatalkan peminjaman #$id");
        }
        
        pg_query($koneksi, "COMMIT");
        
        echo json_encode([
            'success' => true,
            'message' => 'Peminjaman berhasil dibatalkan'
        ]);
    } else {
        pg_query($koneksi, "ROLLBACK");
        echo json_encode([
            'success' => false,
            'message' => 'Gagal membatalkan peminjaman: ' . pg_last_error($koneksi)
        ]);
    }
}

function rejectCancelPeminjaman($koneksi) {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = pg_escape_string($koneksi, $data['id']);
    
    $query = "UPDATE peminjaman 
              SET request_pembatalan = FALSE,
                  alasan_pembatalan = NULL,
                  tanggal_request_pembatalan = NULL,
                  updated_at = NOW()
              WHERE id_peminjaman = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        if (isset($_SESSION['user_id'])) {
            logActivity($koneksi, $_SESSION['user_id'], 'reject_cancel', "Menolak pembatalan peminjaman #$id");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Request pembatalan ditolak'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menolak pembatalan: ' . pg_last_error($koneksi)
        ]);
    }
}

function logActivity($koneksi, $userId, $aktivitas, $deskripsi) {
    $ip = pg_escape_string($koneksi, $_SERVER['REMOTE_ADDR']);
    $aktivitas = pg_escape_string($koneksi, $aktivitas);
    $deskripsi = pg_escape_string($koneksi, $deskripsi);
    
    $query = "INSERT INTO log (id_users, aktivitas, deskripsi, ip_address) 
              VALUES ($userId, '$aktivitas', '$deskripsi', '$ip')";
    
    pg_query($koneksi, $query);
}
?>