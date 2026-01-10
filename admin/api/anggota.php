<?php
// admin/api/anggota.php

// 1. Matikan error HTML agar JSON aman
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // 2. Load Database (Tanpa mengubah file aslinya)
    $possiblePaths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php'
    ];
    $dbPath = null;
    foreach ($possiblePaths as $path) { if (file_exists($path)) { $dbPath = $path; break; }}
    
    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    // Start Session Manual
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Auth Check
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized access.");
    }

    // Adaptasi Koneksi: Gunakan variabel $pdo dari database.php
    if (isset($pdo) && $pdo) { 
        $conn = $pdo; 
    } elseif (class_exists('Database')) { 
        $db = new Database(); 
        $conn = $db->getConnection(); 
    } else { 
        throw new Exception("Koneksi database gagal."); 
    }

    // [PENTING] Injeksi ID User untuk Trigger Log
    $currentUid = (int)$_SESSION['user_id'];
    $currentIp  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $conn->exec("SET app.current_user_id = '$currentUid'");
    $conn->exec("SET app.current_ip = '$currentIp'");

    $method = $_SERVER['REQUEST_METHOD'];

    // === GET DATA ===
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        // List User untuk Dropdown Search
        if ($action === 'get_users') {
            // Mengambil semua user agar pencarian di frontend bekerja
            $stmt = $conn->query("SELECT id_user, nama, email, role FROM users ORDER BY nama ASC");
            jsonOutput(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } 
        
        // Detail Anggota (Edit)
        else if ($action === 'detail' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT d.*, u.nama, u.email FROM dosen d JOIN users u ON d.id_user = u.id_user WHERE d.id_dosen = :id");
            $stmt->execute([':id' => $id]);
            $dosen = $stmt->fetch(PDO::FETCH_ASSOC);

            if($dosen) {
                // Pendidikan
                $stmt = $conn->prepare("SELECT pendidikan FROM pendidikan_terakhir WHERE id_dosen = :id LIMIT 1");
                $stmt->execute([':id' => $id]); 
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                $dosen['pendidikan_terakhir'] = $res ? $res['pendidikan'] : '';

                // Keahlian
                $stmt = $conn->prepare("SELECT nama_bidang FROM bidang_keahlian WHERE id_dosen = :id");
                $stmt->execute([':id' => $id]); 
                $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dosen['bidang_keahlian'] = implode(', ', array_column($skills, 'nama_bidang'));

                // Links
                $stmt = $conn->prepare("SELECT platform, link_url FROM link_akademik_dosen WHERE id_dosen = :id");
                $stmt->execute([':id' => $id]); 
                $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dosen['links_dynamic'] = array_map(function($l){ return ['platform'=>$l['platform'], 'url'=>$l['link_url']]; }, $links);
            }
            jsonOutput(['success' => true, 'data' => $dosen]);
        }
        
        // List Table Anggota
        else {
            $sql = "SELECT d.id_dosen, d.nidn, d.foto, u.nama,
                    (SELECT pendidikan FROM pendidikan_terakhir WHERE id_dosen = d.id_dosen LIMIT 1) as pendidikan,
                    (SELECT STRING_AGG(nama_bidang, ', ') FROM bidang_keahlian WHERE id_dosen = d.id_dosen) as keahlian,
                    (SELECT STRING_AGG(CONCAT(platform, '::', link_url), '||') FROM link_akademik_dosen WHERE id_dosen = d.id_dosen) as links_raw
                    FROM dosen d JOIN users u ON d.id_user = u.id_user ORDER BY u.nama ASC";
            
            $stmt = $conn->prepare($sql); 
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [];
            foreach($result as $row) {
                $linksMap = [];
                if($row['links_raw']) {
                    foreach(explode('||', $row['links_raw']) as $r) {
                        $parts = explode('::', $r);
                        if(count($parts) == 2) $linksMap[$parts[0]] = $parts[1];
                    }
                }
                $row['links_map'] = $linksMap; 
                unset($row['links_raw']);
                $data[] = $row;
            }
            jsonOutput(['success' => true, 'data' => $data]);
        }
    }

    // === POST DATA ===
    if ($method === 'POST') {
        $action = $_POST['action'] ?? 'save';

        // 1. DELETE
        if ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Ambil foto lama
            $stmt = $conn->prepare("SELECT foto FROM dosen WHERE id_dosen = :id");
            $stmt->execute([':id' => $id]);
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Hapus file fisik
            if ($oldData && !empty($oldData['foto'])) {
                $filePath = __DIR__ . '/../' . $oldData['foto']; 
                if (file_exists($filePath)) unlink($filePath);
            }

            // Hapus Data (Trigger DB aktif otomatis)
            $conn->prepare("DELETE FROM bidang_keahlian WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM dosen WHERE id_dosen = :id")->execute([':id' => $id]);

            jsonOutput(['success' => true, 'message' => 'Data berhasil dihapus']);
        } 
        
        // 2. SAVE / UPDATE
        else {
            $id_dosen = !empty($_POST['id_dosen']) ? (int)$_POST['id_dosen'] : null;
            $id_user = $_POST['id_user'];
            $nidn = $_POST['nidn'];
            
            // [FIX ERROR STRING TRUNCATED] 
            // Potong teks jika lebih dari 250 karakter (karena DB varchar(255))
            $pendidikan = substr($_POST['pendidikan_terakhir'], 0, 250); 
            $keahlian_raw = $_POST['bidang_keahlian'];

            if(empty($id_user)) throw new Exception("User wajib dipilih dari daftar.");

            // Upload Foto
            $fotoPath = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if(!in_array($ext, $allowed)) throw new Exception("Format file tidak diizinkan.");

                $fileName = 'anggota_' . time() . '_' . uniqid() . '.' . $ext;
                $targetDir = __DIR__ . '/../uploads/anggota/'; 
                
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . $fileName)) {
                    $fotoPath = 'uploads/anggota/' . $fileName;
                }
            }

            $conn->beginTransaction();
            try {
                if ($id_dosen) {
                    // UPDATE
                    if ($fotoPath) {
                        $stmt = $conn->prepare("SELECT foto FROM dosen WHERE id_dosen = :id");
                        $stmt->execute([':id' => $id_dosen]);
                        $old = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($old && $old['foto'] && file_exists(__DIR__.'/../'.$old['foto'])) {
                            unlink(__DIR__.'/../'.$old['foto']);
                        }
                        
                        $sql = "UPDATE dosen SET id_user=:u, nidn=:n, foto=:f, updated_at=NOW() WHERE id_dosen=:id";
                        $conn->prepare($sql)->execute([':u'=>$id_user, ':n'=>$nidn, ':f'=>$fotoPath, ':id'=>$id_dosen]);
                    } else {
                        $sql = "UPDATE dosen SET id_user=:u, nidn=:n, updated_at=NOW() WHERE id_dosen=:id";
                        $conn->prepare($sql)->execute([':u'=>$id_user, ':n'=>$nidn, ':id'=>$id_dosen]);
                    }
                    
                    // Reset Child
                    $conn->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen=:id")->execute([':id'=>$id_dosen]);
                    $conn->prepare("DELETE FROM bidang_keahlian WHERE id_dosen=:id")->execute([':id'=>$id_dosen]);
                    $conn->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen=:id")->execute([':id'=>$id_dosen]);

                } else {
                    // INSERT
                    $stmt = $conn->prepare("SELECT id_dosen FROM dosen WHERE id_user = :u");
                    $stmt->execute([':u' => $id_user]);
                    if($stmt->rowCount() > 0) throw new Exception("User ini sudah terdaftar sebagai Anggota.");

                    // [FIX FOREIGN KEY ERROR] Gunakan RETURNING id_dosen
                    $sql = "INSERT INTO dosen (id_user, nidn, foto, created_at) VALUES (:u, :n, :f, NOW()) RETURNING id_dosen";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':u'=>$id_user, ':n'=>$nidn, ':f'=>$fotoPath]);
                    $id_dosen = $stmt->fetchColumn(); // Ambil ID yang baru dibuat
                }

                // Insert Relasi (Pastikan ID Dosen Valid)
                if(!$id_dosen) throw new Exception("Gagal membuat data dosen induk.");

                if($pendidikan) {
                    $conn->prepare("INSERT INTO pendidikan_terakhir (id_dosen, pendidikan, created_at) VALUES (:id, :p, NOW())")
                         ->execute([':id'=>$id_dosen, ':p'=>$pendidikan]);
                }

                if($keahlian_raw) {
                    foreach(explode(',', $keahlian_raw) as $bid) { 
                        if($b=trim($bid)) {
                            // Potong per item keahlian juga
                            $b_safe = substr($b, 0, 250);
                            $conn->prepare("INSERT INTO bidang_keahlian (id_dosen, nama_bidang, created_at) VALUES (:id, :b, NOW())")
                                 ->execute([':id'=>$id_dosen, ':b'=>$b_safe]);
                        }
                    }
                }

                if(isset($_POST['link_platform']) && isset($_POST['link_url'])) {
                    $p=$_POST['link_platform']; $u=$_POST['link_url'];
                    for($i=0; $i<count($p); $i++){ 
                        if(!empty($p[$i]) && !empty($u[$i])) {
                            $conn->prepare("INSERT INTO link_akademik_dosen (id_dosen, platform, link_url, created_at) VALUES (:id, :p, :u, NOW())")
                                 ->execute([':id'=>$id_dosen, ':p'=>$p[$i], ':u'=>$u[$i]]); 
                        }
                    }
                }

                $conn->commit();
                jsonOutput(['success' => true, 'message' => 'Data berhasil disimpan']);
            } catch (Exception $e) { 
                $conn->rollBack(); 
                throw new Exception("Database Error: " . $e->getMessage()); 
            }
        }
    }
} catch (Exception $e) { 
    ob_end_clean(); 
    echo json_encode(['success' => false, 'message' => $e->getMessage()]); 
    exit; 
}

function jsonOutput($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}
?>