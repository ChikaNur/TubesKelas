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
                <div style="padding: 15px; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px;">
                    <h4 style="margin-bottom: 10px;">Change Password</h4>
                    <p style="color: #7f8c8d; margin-bottom: 15px;">Update your admin password for better security</p>
                    <button class="btn btn-primary" onclick="alert('Password change feature coming soon!')">Change Password</button>
                </div>
                
                <div style="padding: 15px; border: 1px solid #dee2e6; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px;">Two-Factor Authentication</h4>
                    <p style="color: #7f8c8d; margin-bottom: 15px;">Add an extra layer of security to your account</p>
                    <button class="btn btn-secondary" onclick="alert('2FA feature coming soon!')">Enable 2FA</button>
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
</body>
</html>
