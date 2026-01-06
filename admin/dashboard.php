<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'");
$total_users = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM mata_kuliah WHERE status = 'aktif'");
$total_courses = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM tugas WHERE deadline > NOW()");
$total_assignments = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM enrollments");
$total_enrollments = $stmt->fetch()['total'];

// Get recent enrollments
$stmt = $db->query("
    SELECT u.nama, mk.nama_mk, e.enrolled_at
    FROM enrollments e
    INNER JOIN users u ON e.user_id = u.user_id
    INNER JOIN mata_kuliah mk ON e.mk_id = mk.mk_id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
$recent_enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/adminindex.css">
</head>
<body>
    <div class="app-container">
        <!-- Admin Sidebar -->
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <h1>EduLearn</h1>
                <p style="font-size: 12px; opacity: 0.8;">Admin Panel</p>
            </div>
            <ul class="sidebar-nav">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="courses.php">Manage Courses</a></li>
                <li><a href="assignments.php">Assignments</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="admin-header">
                <div class="admin-title">
                    <h1>Dashboard</h1>
                    <p>Selamat datang, <?= escape_html(get_user_info('nama')) ?></p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Total Mahasiswa</h3>
                        <div class="stat-value"><?= $total_users ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3>Mata Kuliah Aktif</h3>
                        <div class="stat-value"><?= $total_courses ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <h3>Tugas Aktif</h3>
                        <div class="stat-value"><?= $total_assignments ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>Total Enrollments</h3>
                        <div class="stat-value"><?= $total_enrollments ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="admin-table-container">
                <h3>Aktivitas Terbaru</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Mata Kuliah</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_enrollments as $enrollment): ?>
                        <tr>
                            <td><?= escape_html($enrollment['nama']) ?></td>
                            <td><?= escape_html($enrollment['nama_mk']) ?></td>
                            <td><?= format_date($enrollment['enrolled_at'], true) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
