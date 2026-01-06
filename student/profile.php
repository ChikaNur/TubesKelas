<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('../login.php');
}

if (is_admin()) {
    redirect('../admin/dashboard.php');
}

$db = getDB();
$user_id = get_user_info('user_id');
$user_info = get_user_info();

// Get user's courses and assignments count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_courses = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM tugas t 
    INNER JOIN enrollments e ON t.mk_id = e.mk_id 
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = e.user_id
    WHERE e.user_id = ? AND s.submission_id IS NULL");
$stmt->execute([$user_id]);
$pending_assignments = $stmt->fetch()['total'];

// Assuming semester 4 for now - should be from user profile
$current_semester = 4;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>EduLearn</h1>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li class="active"><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Kelola informasi akun dan akademik</p>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user_info['nama'], 0, 2)) ?>                </div>
                <div class="profile-info">
                    <h2><?= escape_html($user_info['nama']) ?></h2>
                    <p class="email"><?= escape_html($user_info['email']) ?></p>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?= $total_courses ?></div>
                            <div class="stat-label">Courses</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $current_semester ?></div>
                            <div class="stat-label">Semester</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $pending_assignments ?></div>
                            <div class="stat-label">Assignments</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-main">
                <div class="profile-card">
                    <h3>Overview</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Nama Lengkap</div>
                            <div class="info-value"><?= escape_html($user_info['nama']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= escape_html($user_info['email']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">NIM</div>
                            <div class="info-value"><?= escape_html($user_info['nim']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Role</div>
                            <div class="info-value"><?= ucfirst($user_info['role']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <h3>Academic Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">NIM</div>
                            <div class="info-value"><?= escape_html($user_info['nim']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Program Studi</div>
                            <div class="info-value">Informatika</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value"><?= $current_semester ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">Aktif</div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <h3>Security</h3>
                    <div class="security-note">
                        <h4>⚠️ Fitur Sedang Dikembangkan</h4>
                        <p>Fitur keamanan akan dikembangkan pada tahap selanjutnya. Terima kasih atas pengertiannya.</p>
                    </div>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-outline" disabled>Ganti Password</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.btn-outline {
    background: white;
    border: 2px solid #3498db;
    color: #3498db;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}
.btn-outline:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.security-note {
    background: #fff3cd;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #f39c12;
}
</style>
</body>
</html>
