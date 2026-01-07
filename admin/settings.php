<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$user_info = get_user_info();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EduLearn Admin</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/adminindex.css">
</head>
<body>
<div class="app-container">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <h1>EduLearn</h1>
            <p style="font-size: 12px; opacity: 0.8;">Admin Panel</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="courses.php">Manage Courses</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li class="active"><a href="settings.php">Settings</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="admin-header">
            <div class="admin-title">
                <h1>⚙️ System Settings</h1>
                <p>Manage application configuration and preferences</p>
            </div>
        </div>

        <!-- Profile Settings -->
        <div class="admin-table-container" style="margin-bottom: 20px;">
            <h3>👤 Admin Profile</h3>
            <form style="max-width: 600px;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Full Name</label>
                    <input type="text" class="form-control" value="<?= escape_html($user_info['nama']) ?>" readonly>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email</label>
                    <input type="email" class="form-control" value="<?= escape_html($user_info['email']) ?>" readonly>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Role</label>
                    <input type="text" class="form-control" value="<?= ucfirst($user_info['role']) ?>" readonly>
                </div>
                <p style="color: #7f8c8d; font-size: 14px; margin-top: 10px;">
                    ℹ️ Profile editing feature is currently under development
                </p>
            </form>
        </div>

        <!-- System Configuration -->
        <div class="admin-table-container" style="margin-bottom: 20px;">
            <h3>🔧 System Configuration</h3>
            <div style="max-width: 600px;">
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Database:</strong> edulearn<br>
                    <strong>PHP Version:</strong> <?= phpversion() ?><br>
                    <strong>Upload Max Size:</strong> <?= ini_get('upload_max_filesize') ?><br>
                    <strong>Post Max Size:</strong> <?= ini_get('post_max_size') ?>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="admin-table-container" style="margin-bottom: 20px;">
            <h3>🔒 Security Settings</h3>
            <div style="max-width: 600px;">
                <div style="padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <h4 style="margin-bottom: 15px;">Change Password</h4>
                    <form method="POST" action="change_admin_password.php" onsubmit="return confirmPasswordChange()">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="old_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Password Lama</label>
                            <input type="password" id="old_password" name="old_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="new_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                            <small style="color: #64748b;">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">🔒 Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Application Settings -->
        <div class="admin-table-container">
            <h3>📱 Application Settings</h3>
            <div style="max-width: 600px;">
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" style="margin-right: 10px;" disabled>
                        <span>Enable email notifications</span>
                    </label>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" style="margin-right: 10px;" disabled>
                        <span>Auto-backup database daily</span>
                    </label>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" style="margin-right: 10px;" disabled>
                        <span>Maintenance mode</span>
                    </label>
                </div>
                <p style="color: #7f8c8d; font-size: 14px; margin-top: 15px;">
                    ℹ️ These features are currently under development
                </p>
            </div>
        </div>
    </main>
</div>

<script>
function confirmPasswordChange() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('Password baru dan konfirmasi tidak cocok!');
        return false;
    }
    
    if (newPassword.length < 6) {
        alert('Password baru minimal 6 karakter!');
        return false;
    }
    
    return confirm('Apakah Anda yakin ingin mengubah password?');
}
</script>
</body>
</html>
