<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get statistics for reports
$stats = [];

// Total users by role
$stmt = $db->query("
    SELECT role, COUNT(*) as count 
    FROM users 
    GROUP BY role
");
$stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Courses by status
$stmt = $db->query("
    SELECT status, COUNT(*) as count 
    FROM mata_kuliah 
    GROUP BY status
");
$stats['courses_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Assignment completion rate
$stmt = $db->query("
    SELECT 
        COUNT(DISTINCT t.tugas_id) as total_assignments,
        COUNT(DISTINCT s.submission_id) as total_submissions,
        COUNT(DISTINCT s.user_id) as students_submitted
    FROM tugas t
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id
");
$stats['assignments'] = $stmt->fetch();

// Enrollment statistics
$stmt = $db->query("
    SELECT 
        COUNT(*) as total_enrollments,
        COUNT(DISTINCT user_id) as unique_students,
        COUNT(DISTINCT mk_id) as courses_with_students
    FROM enrollments
");
$stats['enrollments'] = $stmt->fetch();

// Recent activity (last 10 enrollments)
$stmt = $db->query("
    SELECT u.nama, mk.nama_mk, e.enrolled_at
    FROM enrollments e
    INNER JOIN users u ON e.user_id = u.user_id
    INNER JOIN mata_kuliah mk ON e.mk_id = mk.mk_id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
");
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - EduLearn Admin</title>
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
            <li class="active"><a href="reports.php">Reports</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="admin-header">
            <div class="admin-title">
                <h1>📊 System Reports</h1>
                <p>Overview of system statistics and activity</p>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <div class="stat-value"><?= array_sum($stats['users_by_role']) ?></div>
                    <small><?= $stats['users_by_role']['mahasiswa'] ?? 0 ?> Students, <?= $stats['users_by_role']['admin'] ?? 0 ?> Admins</small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3>Active Courses</h3>
                    <div class="stat-value"><?= $stats['courses_by_status']['aktif'] ?? 0 ?></div>
                    <small><?= $stats['courses_by_status']['draft'] ?? 0 ?> Draft, <?= $stats['courses_by_status']['arsip'] ?? 0 ?> Archived</small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-content">
                    <h3>Assignments</h3>
                    <div class="stat-value"><?= $stats['assignments']['total_assignments'] ?></div>
                    <small><?= $stats['assignments']['total_submissions'] ?> Submissions</small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-content">
                    <h3>Enrollments</h3>
                    <div class="stat-value"><?= $stats['enrollments']['total_enrollments'] ?></div>
                    <small><?= $stats['enrollments']['unique_students'] ?> Unique Students</small>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="admin-table-container">
            <h3>📋 Recent Activity</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Enrolled At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity as $activity): ?>
                    <tr>
                        <td><?= escape_html($activity['nama']) ?></td>
                        <td><?= escape_html($activity['nama_mk']) ?></td>
                        <td><?= format_date($activity['enrolled_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Export Options -->
        <div class="admin-table-container">
            <h3>📥 Export Data</h3>
            <p style="color: #7f8c8d; margin-bottom: 20px;">Export system data to CSV for further analysis</p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="alert('Export Users - Feature coming soon!')">Export Users Data</button>
                <button class="btn btn-primary" onclick="alert('Export Courses - Feature coming soon!')">Export Courses Data</button>
                <button class="btn btn-primary" onclick="alert('Export Enrollments - Feature coming soon!')">Export Enrollments</button>
                <button class="btn btn-primary" onclick="alert('Export Submissions - Feature coming soon!')">Export Submissions</button>
            </div>
        </div>
    </main>
</div>
</body>
</html>
