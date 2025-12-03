<?php
// ===== FILE: register.php (Revisi Text Normal) =====
// Lokasi: PBL_BA/admin/register.php

session_start();
require_once __DIR__ . '/config/database.php'; 

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Semua kolom wajib diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sesuai';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $check_stmt = $db->prepare("SELECT id_user FROM users WHERE email = :email");
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error = 'Email sudah terdaftar, silakan login.';
        } else {
            // INSERT Member Baru
            $insert_query = "INSERT INTO users (nama, email, password, role, is_active) VALUES (:nama, :email, :password, 'member', true)";
            try {
                $stmt = $db->prepare($insert_query);
                $stmt->execute([':nama' => $nama, ':email' => $email, ':password' => $password]);
                header('Location: login.php?pesan=registered');
                exit;
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan sistem.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Laboratorium Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffff 0%, #e6f2ff 50%, #2B95FD 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: #2B95FD;
            padding: 25px;
            text-align: center;
            color: white;
        }
        .login-logo {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .login-body { padding: 30px; }
        .form-control:focus {
            border-color: #2B95FD;
            box-shadow: 0 0 0 0.2rem rgba(43, 149, 253, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            color: #2B95FD;
        }
        .btn-primary-custom {
            background: #2B95FD;
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background: #1a84e6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43, 149, 253, 0.3);
        }
        .text-primary-custom { color: #2B95FD; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <div class="login-header">
                <img src="../assets/images/logo.png" alt="Logo" class="login-logo">
                <h4 class="mb-0">Daftar Akun Baru</h4>
                <p class="mb-0 small">Bergabung dengan Laboratorium Business Analytics</p>
            </div>
            <div class="login-body">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap (Username)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="nama" required placeholder="Nama Lengkap" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" required placeholder="email@contoh.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="Buat password">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <input type="password" class="form-control" name="confirm_password" required placeholder="Ulangi password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">Sudah punya akun? <a href="login.php" class="text-decoration-none text-primary-custom">Login disini</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>