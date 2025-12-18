<?php
// admin/api/anggota.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

try {
    // === 1. KONEKSI DATABASE ===
    $possiblePaths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/admin/config/database.php'
    ];
    $dbPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $dbPath = $path;
            break;
        }
    }

    if (!$dbPath) throw new Exception("Config database tidak ditemukan.");
    require_once $dbPath;

    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception("Unauthorized access.");

    if (isset($pdo) && $pdo) {
        $conn = $pdo;
    } elseif (class_exists('Database')) {
        $db = new Database();
        $conn = $db->getConnection();
    } else {
        throw new Exception("Koneksi database gagal.");
    }

    // Set Session Variables untuk Log (Opsional)
    try {
        $uid = (int)$_SESSION['user_id'];
        $conn->exec("SET app.current_user_id = '$uid'");
    } catch (Exception $e) {}

    $method = $_SERVER['REQUEST_METHOD'];

    // === 2. GET DATA ===
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'get_users') {
            $stmt = $conn->query("SELECT id_user, nama, email, role FROM users ORDER BY nama ASC");
            jsonOutput(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }

        else if ($action === 'detail' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT d.*, u.nama, u.email FROM dosen d JOIN users u ON d.id_user = u.id_user WHERE d.id_dosen = :id");
            $stmt->execute([':id' => $id]);
            $dosen = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dosen) {
                // Ambil data PENDIDIKAN dari tabel anak
                $stmt = $conn->prepare("SELECT pendidikan FROM pendidikan_terakhir WHERE id_dosen = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                $dosen['pendidikan_terakhir'] = $res ? $res['pendidikan'] : '';

                // Ambil data KEAHLIAN dari tabel anak
                $stmt = $conn->prepare("SELECT nama_bidang FROM bidang_keahlian WHERE id_dosen = :id");
                $stmt->execute([':id' => $id]);
                $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dosen['bidang_keahlian'] = !empty($skills) ? implode(', ', array_column($skills, 'nama_bidang')) : '';

                // Links
                $stmt = $conn->prepare("SELECT platform, link_url FROM link_akademik_dosen WHERE id_dosen = :id");
                $stmt->execute([':id' => $id]);
                $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dosen['links_dynamic'] = array_map(function ($l) {
                    return ['platform' => $l['platform'], 'url' => $l['link_url']];
                }, $links);
            }
            jsonOutput(['success' => true, 'data' => $dosen]);
        }

        else {
            // === [PERBAIKAN QUERY UTAMA] ===
            // Hapus COALESCE penyebab error. Kita ambil langsung dari tabel anak via Subquery.
            // PostgreSQL akan otomatis mengisi NULL jika data anak tidak ada.
            
            $sql = "SELECT d.id_dosen, d.nidn, d.foto, u.nama,
                    (SELECT pendidikan FROM pendidikan_terakhir WHERE id_dosen = d.id_dosen LIMIT 1) as pendidikan,
                    (SELECT STRING_AGG(nama_bidang, ', ') FROM bidang_keahlian WHERE id_dosen = d.id_dosen) as keahlian,
                    (SELECT STRING_AGG(CONCAT(platform, '::', link_url), '||') FROM link_akademik_dosen WHERE id_dosen = d.id_dosen) as links_raw
                    FROM dosen d JOIN users u ON d.id_user = u.id_user ORDER BY u.nama ASC";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($result as $row) {
                // Parsing Links
                $linksMap = [];
                if ($row['links_raw']) {
                    foreach (explode('||', $row['links_raw']) as $r) {
                        $parts = explode('::', $r);
                        if (count($parts) == 2) $linksMap[$parts[0]] = $parts[1];
                    }
                }
                $row['links_map'] = $linksMap;
                unset($row['links_raw']);
                $data[] = $row;
            }
            jsonOutput(['success' => true, 'data' => $data]);
        }
    }

    // === 3. POST DATA (SAVE/UPDATE) ===
    if ($method === 'POST') {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Hapus File Foto
            $stmt = $conn->prepare("SELECT foto FROM dosen WHERE id_dosen = :id");
            $stmt->execute([':id' => $id]);
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($oldData && !empty($oldData['foto'])) {
                $path = __DIR__ . '/../' . $oldData['foto'];
                if (file_exists($path)) unlink($path);
            }

            // Hapus Data Berjenjang
            $conn->prepare("DELETE FROM bidang_keahlian WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen = :id")->execute([':id' => $id]);
            $conn->prepare("DELETE FROM anggota WHERE id_dosen = :id")->execute([':id' => $id]); // Hapus anggota juga
            $conn->prepare("DELETE FROM dosen WHERE id_dosen = :id")->execute([':id' => $id]);

            jsonOutput(['success' => true, 'message' => 'Data berhasil dihapus']);
        }

        else {
            // === SAVE / UPDATE LOGIC ===
            $id_dosen = !empty($_POST['id_dosen']) ? (int)$_POST['id_dosen'] : null;
            $id_user = $_POST['id_user'];
            $nidn = $_POST['nidn'];
            
            $pendidikan_text = substr($_POST['pendidikan_terakhir'], 0, 250);
            $keahlian_raw = $_POST['bidang_keahlian'];

            // Upload Foto
            $fotoPath = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $fileName = 'anggota_' . time() . '_' . uniqid() . '.' . $ext;
                    $targetDir = __DIR__ . '/../uploads/anggota/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . $fileName)) {
                        $fotoPath = 'uploads/anggota/' . $fileName;
                    }
                }
            }

            $conn->beginTransaction();
            try {
                if ($id_dosen) {
                    // === UPDATE ===
                    // PERBAIKAN: JANGAN update kolom 'pendidikan_terakhir' di tabel dosen (karena Integer)
                    if ($fotoPath) {
                        // Hapus foto lama
                        $old = $conn->query("SELECT foto FROM dosen WHERE id_dosen=$id_dosen")->fetch();
                        if ($old && $old['foto'] && file_exists(__DIR__.'/../'.$old['foto'])) unlink(__DIR__.'/../'.$old['foto']);

                        $sql = "UPDATE dosen SET id_user=:u, nidn=:n, foto=:f, updated_at=NOW() WHERE id_dosen=:id";
                        $conn->prepare($sql)->execute([':u'=>$id_user, ':n'=>$nidn, ':f'=>$fotoPath, ':id'=>$id_dosen]);
                    } else {
                        $sql = "UPDATE dosen SET id_user=:u, nidn=:n, updated_at=NOW() WHERE id_dosen=:id";
                        $conn->prepare($sql)->execute([':u'=>$id_user, ':n'=>$nidn, ':id'=>$id_dosen]);
                    }

                    // Reset Child Tables untuk di-insert ulang
                    $conn->prepare("DELETE FROM pendidikan_terakhir WHERE id_dosen=:id")->execute([':id' => $id_dosen]);
                    $conn->prepare("DELETE FROM bidang_keahlian WHERE id_dosen=:id")->execute([':id' => $id_dosen]);
                    $conn->prepare("DELETE FROM link_akademik_dosen WHERE id_dosen=:id")->execute([':id' => $id_dosen]);

                } else {
                    // === INSERT BARU ===
                    // Cek duplikasi
                    $stmt = $conn->prepare("SELECT id_dosen FROM dosen WHERE id_user = :u");
                    $stmt->execute([':u' => $id_user]);
                    if ($stmt->rowCount() > 0) throw new Exception("User ini sudah terdaftar.");

                    // INSERT ke Dosen (Tanpa kolom pendidikan_terakhir/bidang_keahlian yang Integer)
                    $sql = "INSERT INTO dosen (id_user, nidn, foto, created_at) VALUES (:u, :n, :f, NOW()) RETURNING id_dosen";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':u' => $id_user, ':n' => $nidn, ':f' => $fotoPath]);
                    $id_dosen = $stmt->fetchColumn();
                }

                // === INSERT TABEL ANGGOTA (Penting!) ===
                // Pastikan data masuk ke tabel anggota agar tampil di halaman anggota
                $cek = $conn->prepare("SELECT id_anggota FROM anggota WHERE id_dosen = :id");
                $cek->execute([':id' => $id_dosen]);
                if ($cek->rowCount() == 0) {
                    $conn->prepare("INSERT INTO anggota (id_dosen, jabatan, is_active, created_at) VALUES (:id, 'Anggota', true, NOW())")
                         ->execute([':id' => $id_dosen]);
                }

                // === INSERT CHILD DATA ===
                // 1. Pendidikan
                if (!empty($pendidikan_text)) {
                    $conn->prepare("INSERT INTO pendidikan_terakhir (id_dosen, pendidikan, created_at) VALUES (:id, :p, NOW())")
                        ->execute([':id' => $id_dosen, ':p' => $pendidikan_text]);
                }

                // 2. Keahlian
                if ($keahlian_raw) {
                    foreach (explode(',', $keahlian_raw) as $bid) {
                        if ($b = trim($bid)) {
                            $conn->prepare("INSERT INTO bidang_keahlian (id_dosen, nama_bidang, created_at) VALUES (:id, :b, NOW())")
                                ->execute([':id' => $id_dosen, ':b' => substr($b, 0, 250)]);
                        }
                    }
                }

                // 3. Link
                if (isset($_POST['link_platform']) && isset($_POST['link_url'])) {
                    $p = $_POST['link_platform'];
                    $u = $_POST['link_url'];
                    for ($i=0; $i<count($p); $i++) {
                        if (!empty($p[$i]) && !empty($u[$i])) {
                            $conn->prepare("INSERT INTO link_akademik_dosen (id_dosen, platform, link_url, created_at) VALUES (:id, :p, :u, NOW())")
                                ->execute([':id' => $id_dosen, ':p' => $p[$i], ':u' => $u[$i]]);
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