<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('student/dashboard.php');
}

// Check for error message from registration process
$error_message = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EduLearn</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <!-- Left Panel -->
        <div class="auth-left-panel">
            <div class="logo">
                <h1>EduLearn</h1>
                <p>Bergabung dengan komunitas pembelajaran</p>
            </div>
            
            <div class="auth-features">
                <div class="feature-item">
                    <div class="feature-icon">🎓</div>
                    <div class="feature-text">
                        <h4>Mahasiswa Terdaftar</h4>
                        <p>5000+ mahasiswa aktif</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">⭐</div>
                    <div class="feature-text">
                        <h4>Rating Tertinggi</h4>
                        <p>4.8/5 dari 1000+ review</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <div class="feature-text">
                        <h4>Akses Dimana Saja</h4>
                        <p>Desktop, tablet, dan mobile</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="auth-right-panel">
            <div class="auth-header">
                <h2>Daftar Akun</h2>
                <p>Buat akun baru untuk memulai perjalanan belajar</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" style="padding: 10px; margin-bottom: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
                    <?= escape_html($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- FORM dengan action ke auth/register_process.php -->
            <form action="auth/register_process.php" method="POST" class="auth-form" onsubmit="return validateForm()">
                <div class="register-grid">
                    <div class="form-group">
                        <label for="fullName" class="form-label">Nama Lengkap</label>
                        <input type="text" id="fullName" name="fullName" class="form-control" placeholder="Bayu Muda Herlambang" required>
                        <div class="form-control-icon">👤</div>
                    </div>

                    <div class="form-group">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" id="nim" name="nim" class="form-control" placeholder="707082400072" required>
                        <div class="form-control-icon">🎓</div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="student@email.ac.id" required>
                        <div class="form-control-icon">📧</div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">👁️</button>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="terms" class="form-check-input" name="terms" required>
                    <label for="terms" class="form-check-label">
                        Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    📝 Daftar
                </button>

                <div class="auth-footer">
                    <p>Sudah punya akun? 
                        <a href="login.php">Login disini</a>
                    </p>
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
        
        // Validasi password match
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Password dan konfirmasi password tidak cocok');
                return false;
            }
            
            if (password.length < 6) {
                alert('Password minimal 6 karakter');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>
