<?php
// ===== FILE: login.php (Animated) =====
session_start();
require_once __DIR__ . '/config/database.php'; 

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') header('Location: index.php');
    else header('Location: ../index.php');
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
            
            if ($user['role'] == 'admin') header('Location: index.php');
            else header('Location: ../index.php');
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
    <title>Login - Lab Business Analytics</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #e6f2ff 50%, #2B95FD 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden; /* Mencegah scrollbar saat animasi */
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 550px;
            display: flex;
            opacity: 0; /* Hidden awal untuk animasi fade in container */
            animation: fadeInContainer 0.8s ease-out forwards;
        }

        /* ANIMASI */
        @keyframes fadeInContainer { to { opacity: 1; } }
        @keyframes slideInLeft { from { transform: translateX(-50px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideInRight { from { transform: translateX(50px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .login-left {
            width: 50%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #ffffff;
            /* Animasi masuk dari Kiri */
            animation: slideInLeft 0.8s 0.2s ease-out forwards;
            opacity: 0; 
        }

        .login-right {
            width: 50%;
            background: linear-gradient(135deg, #2B95FD 0%, #0d6efd 100%);
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            /* Animasi masuk dari Kanan */
            animation: slideInRight 0.8s 0.2s ease-out forwards;
            opacity: 0;
        }
        
        .login-right::before { content: ''; position: absolute; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; top: -50px; right: -50px; }
        .login-right::after { content: ''; position: absolute; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; bottom: 30px; left: -30px; }

        .login-logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 25px;
            z-index: 2;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .welcome-title { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; z-index: 2; }
        .welcome-text { font-size: 0.95rem; opacity: 0.9; z-index: 2; }
        .form-title { font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 5px; }
        .form-subtitle { color: #888; font-size: 0.9rem; margin-bottom: 30px; }
        .form-control { padding: 12px 15px; border-radius: 8px; border: 1px solid #ddd; background-color: #f9f9f9; }
        .form-control:focus { background-color: #fff; border-color: #2B95FD; box-shadow: 0 0 0 4px rgba(43, 149, 253, 0.1); }
        .btn-login { background: #2B95FD; border: none; padding: 12px; font-weight: 600; border-radius: 8px; width: 100%; color: white; margin-top: 10px; transition: all 0.3s; }
        .btn-login:hover { background: #1a84e6; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(43, 149, 253, 0.3); }
        .auth-links { margin-top: 25px; font-size: 0.9rem; }
        .text-primary-custom { color: #2B95FD; font-weight: 600; text-decoration: none; }
        .text-primary-custom:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .login-container { flex-direction: column-reverse; min-height: auto; max-width: 400px; }
            .login-left, .login-right { width: 100%; padding: 30px; }
            .login-right { padding: 40px 20px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-left">
            <h2 class="form-title">Halo, Selamat Datang!</h2>
            <p class="form-subtitle">Silakan masukkan detail akun Anda</p>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'registered'): ?>
                <div class="alert alert-success py-2 small"><i class="fas fa-check me-1"></i>Registrasi berhasil! Silakan login.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-circle me-1"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0" name="email" required placeholder="user@contoh.com">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" name="password" required placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">Masuk Sekarang <i class="fas fa-arrow-right ms-2"></i></button>
            </form>

            <div class="auth-links">
                <div class="mb-2">
                    <span class="text-muted">Belum punya akun?</span> <a href="register.php" class="text-primary-custom">Daftar disini</a>
                </div>
                <div><a href="../index.php" class="text-muted text-decoration-none small"><i class="fas fa-chevron-left me-1"></i> Kembali ke Beranda</a></div>
            </div>
        </div>

        <div class="login-right">
            <img src="../assets/images/logo.png" alt="Logo Lab" class="login-logo">
            <h3 class="welcome-title">Lab Business Analytics</h3>
            <p class="welcome-text">Transforming Data into Decisions.<br>Kelola data dan aktivitas laboratorium Anda dengan mudah dan aman.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>