<?php
// ===== FILE: register.php (Swapped Layout + Animated) =====
session_start();
require_once __DIR__ . '/config/database.php'; 

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

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
            $insert_query = "INSERT INTO users (nama, email, password, role, is_active) VALUES (:nama, :email, :password, 'mahasiswa', true)";
            try {
                $stmt = $db->prepare($insert_query);
                $stmt->execute([':nama' => $nama, ':email' => $email, ':password' => $password]);
                header('Location: login.php?pesan=registered');
                exit;
            } catch (PDOException $e) { $error = 'Terjadi kesalahan sistem.'; }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lab Business Analytics</title>
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
            overflow-x: hidden;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
            opacity: 0;
            animation: fadeInContainer 0.8s ease-out forwards;
        }

        @keyframes fadeInContainer { to { opacity: 1; } }
        /* Slide In Animations */
        @keyframes slideInLeft { from { transform: translateX(-50px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideInRight { from { transform: translateX(50px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- BAGIAN KIRI (LOGO - REGISTER) --- */
        /* Layout ditukar: Class ini untuk Logo/Biru */
        .register-left {
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
            /* Animasi dari Kiri */
            animation: slideInLeft 0.8s 0.2s ease-out forwards;
            opacity: 0;
        }
        .register-left::before { content: ''; position: absolute; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; top: -50px; right: -50px; }
        .register-left::after { content: ''; position: absolute; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; bottom: 30px; left: -30px; }

        /* --- BAGIAN KANAN (FORM - REGISTER) --- */
        .register-right {
            width: 50%;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #ffffff;
            /* Animasi dari Kanan */
            animation: slideInRight 0.8s 0.2s ease-out forwards;
            opacity: 0;
        }

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
        .form-subtitle { color: #888; font-size: 0.9rem; margin-bottom: 25px; }
        .form-control { padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background-color: #f9f9f9; font-size: 0.9rem; }
        .form-control:focus { background-color: #fff; border-color: #2B95FD; box-shadow: 0 0 0 3px rgba(43, 149, 253, 0.1); }
        .btn-login { background: #2B95FD; border: none; padding: 12px; font-weight: 600; border-radius: 8px; width: 100%; color: white; margin-top: 15px; transition: all 0.3s; }
        .btn-login:hover { background: #1a84e6; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(43, 149, 253, 0.3); }
        .auth-links { margin-top: 20px; font-size: 0.9rem; text-align: center; }
        .text-primary-custom { color: #2B95FD; font-weight: 600; text-decoration: none; }
        .text-primary-custom:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            /* Mobile: Logo di atas, Form di bawah */
            .login-container { flex-direction: column; min-height: auto; max-width: 400px; }
            .register-left, .register-right { width: 100%; padding: 30px; }
            .register-left { padding: 40px 20px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="register-left">
            <img src="../assets/images/logo.png" alt="Logo Lab" class="login-logo">
            <h3 class="welcome-title">Join Us Now!</h3>
            <p class="welcome-text">Bergabunglah dengan komunitas Laboratorium Business Analytics dan mulai kelola data Anda.</p>
        </div>

        <div class="register-right">
            <h2 class="form-title">Buat Akun Baru</h2>
            <p class="form-subtitle">Lengkapi data diri Anda untuk bergabung</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-circle me-1"></i> <?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" name="nama" required placeholder="Nama Lengkap Anda" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 ps-0" name="email" required placeholder="user@contoh.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" name="password" required placeholder="Buat password">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" name="confirm_password" required placeholder="Ulangi password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>
            </form>

            <div class="auth-links">
                <span class="text-muted">Sudah punya akun?</span> 
                <a href="login.php" class="text-primary-custom ms-1">Login disini</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>