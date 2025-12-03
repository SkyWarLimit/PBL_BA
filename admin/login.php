<?php
// ===== FILE: login.php (Revisi Text Normal) =====
// Lokasi: PBL_BA/admin/login.php

session_start();
require_once __DIR__ . '/config/database.php'; 

// Cek Login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: index.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email AND is_active = true";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; 
            
            try {
                $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id_user = ?")->execute([$user['id_user']]);
                
                $log_query = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address) VALUES (?, 'LOGIN', ?, ?)";
                $deskripsi = "User role " . $user['role'] . " berhasil login";
                $ip = function_exists('getClientIP') ? getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
                $db->prepare($log_query)->execute([$user['id_user'], $deskripsi, $ip]);
            } catch (PDOException $e) {}
            
            if ($user['role'] == 'admin') {
                header('Location: index.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $error = 'Email atau password salah';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laboratorium Business Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffff 0%, #e6f2ff 50%, #2B95FD 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #2B95FD;
            padding: 30px;
            text-align: center;
            color: white;
        }
        .login-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
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
        .btn-login {
            background: #2B95FD;
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
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
                <h4 class="mb-0">Login System</h4>
                <p class="mb-0 small">Laboratorium Business Analytics</p>
            </div>
            <div class="login-body">
                
                <?php if (isset($_GET['pesan'])): ?>
                    <?php if ($_GET['pesan'] == 'logout'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Anda berhasil logout.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['pesan'] == 'registered'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Registrasi berhasil! Silakan login.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" required placeholder="email@contoh.com">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                    
                    <div class="text-center mt-3">
                        <div class="mb-2">
                            <small class="text-muted">Belum punya akun? <a href="register.php" class="text-decoration-none text-primary-custom">Daftar disini</a></small>
                        </div>
                        <small class="text-muted">Kembali ke <a href="../index.php" class="text-decoration-none text-primary-custom">Beranda Utama</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>