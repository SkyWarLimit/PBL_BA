<?php
// ===== FILE: login.php =====
// WARNING: Password tanpa hash - HANYA UNTUK DEVELOPMENT!
// JANGAN gunakan di production server!

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    header('Location: admin/index.php');
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email AND role = 'admin' AND is_active = true";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        // PERBANDINGAN PLAINTEXT - TIDAK AMAN!
        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            try {
                // Update last login
                $update_query = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id_user = :id";
                $update_stmt = $db->prepare($update_query);
                $user_id = $user['id_user'];
                $update_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $update_stmt->execute();
                
                // Log login
                $log_query = "INSERT INTO log (id_user, aktivitas, deskripsi, ip_address) VALUES (:id, 'LOGIN', 'User berhasil login', :ip)";
                $log_stmt = $db->prepare($log_query);
                $ip = getClientIP();
                $log_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $log_stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
                $log_stmt->execute();
            } catch (PDOException $e) {
                // Silent fail - login tetap berhasil meskipun logging gagal
                error_log("Login log error: " . $e->getMessage());
            }
            
            header('Location: admin/index.php');
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
    <title>Login - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .login-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .dev-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <div class="login-header">
                <i class="fas fa-flask fa-3x mb-3"></i>
                <h4 class="mb-0">Admin Panel Login</h4>
                <p class="mb-0 small">Laboratorium Management System</p>
            </div>
            <div class="login-body">
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
                            <input type="email" class="form-control" name="email" required placeholder="admin@lab.com" value="admin@lab.com">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="admin123" value="admin123">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>