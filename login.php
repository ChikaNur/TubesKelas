<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

// Check for logout message
$logout_message = isset($_GET['logout']) ? 'Anda telah keluar dari sistem' : '';

// Check for error message from login process
$error_message = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduLearn</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        
        <div class="auth-left-panel">
            <div class="logo">
                <h1>EduLearn</h1>
                <p>Platform Pembelajaran Digital Terpadu</p>
            </div>
            
            <div class="auth-features">
                <div class="feature-item">
                    <div class="feature-icon">📚</div>
                    <div class="feature-text">
                        <h4>7+ Mata Kuliah</h4>
                        <p>Akses semua materi pembelajaran</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">📝</div>
                    <div class="feature-text">
                        <h4>Tugas Terkelola</h4>
                        <p>Pantau deadline dan submit tugas</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">📅</div>
                    <div class="feature-text">
                        <h4>Jadwal Pintar</h4>
                        <p>Atur jadwal perkuliahan dengan mudah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="auth-right-panel">
            <div class="auth-header">
                <h2>Login EduLearn</h2>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <?php if ($logout_message): ?>
                <div class="alert alert-success" style="padding: 10px; margin-bottom: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
                    <?= escape_html($logout_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" style="padding: 10px; margin-bottom: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
                    <?= escape_html($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- FORM dengan action ke auth/login_process.php -->
            <form action="auth/login_process.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="masukan@email.ac.id" required>
                    <div class="form-control-icon">📧</div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">👁️</button>
                </div>

                <div class="login-options">
                    <div class="form-check">
                        <input type="checkbox" id="remember" class="form-check-input" name="remember">
                        <label for="remember" class="form-check-label">Ingat saya</label>
                    </div>
                    <a href="#" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    🔑 Login
                </button>

                <div class="auth-footer">
                    <p>Belum punya akun? 
                        <a href="signup.php">Daftar sekarang</a>
                    </p>
                </div>

                <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-radius: 5px; font-size: 14px;">
                    <strong>Demo Credentials:</strong><br>
                    Admin: admin@edulearn.ac.id / password<br>
                    Student: bayu@student.telkomuniversity.ac.id / password
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hanya fungsi toggle password saja
        function togglePassword(button) {
            const input = button.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            button.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        }
    </script>
</body>
</html>
