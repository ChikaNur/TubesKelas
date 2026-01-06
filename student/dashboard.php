<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Redirect admin to admin dashboard
if (is_admin()) {
    redirect('../admin/dashboard.php');
}

$db = getDB();
$user_id = get_user_info('user_id');
$user_name = get_user_info('nama');

// Get enrolled courses count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_courses = $stmt->fetch()['total'];

// Get pending assignments count
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM tugas t
    INNER JOIN enrollments e ON t.mk_id = e.mk_id
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
    WHERE e.user_id = ? AND s.submission_id IS NULL AND t.deadline > NOW()
");
$stmt->execute([$user_id, $user_id]);
$pending_assignments = $stmt->fetch()['total'];

// Get upcoming deadlines (next 7 days)
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM tugas t
    INNER JOIN enrollments e ON t.mk_id = e.mk_id
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
    WHERE e.user_id = ? AND s.submission_id IS NULL 
    AND t.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
");
$stmt->execute([$user_id, $user_id]);
$upcoming_deadlines = $stmt->fetch()['total'];

// Calculate average progress
$stmt = $db->prepare("SELECT AVG(progress) as avg_progress FROM enrollments WHERE user_id = ?");
$stmt->execute([$user_id]);
$avg_progress = round($stmt->fetch()['avg_progress'] ?? 0, 0);

// Get enrolled courses with progress for chart
$stmt = $db->prepare("
    SELECT mk.nama_mk, mk.kode_mk, e.progress
    FROM enrollments e
    INNER JOIN mata_kuliah mk ON e.mk_id = mk.mk_id
    WHERE e.user_id = ?
    ORDER BY e.progress DESC
    LIMIT 4
");
$stmt->execute([$user_id]);
$courses_progress = $stmt->fetchAll();

// Get recent assignments close to deadline
$stmt = $db->prepare("
    SELECT t.judul, t.deadline, mk.nama_mk, mk.kode_mk
    FROM tugas t
    INNER JOIN enrollments e ON t.mk_id = e.mk_id
    INNER JOIN mata_kuliah mk ON t.mk_id = mk.mk_id
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
    WHERE e.user_id = ? AND s.submission_id IS NULL
    AND t.deadline > NOW()
    ORDER BY t.deadline ASC
    LIMIT 3
");
$stmt->execute([$user_id, $user_id]);
$upcoming_tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>EduLearn</h1>
        </div>
        <ul class="sidebar-nav">
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

   <!-- Main Content -->
    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p>Ringkasan aktivitas pembelajaran</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card courses">
                <div class="stat-icon">
                    📚
                </div>
                <div class="stat-content">
                    <h3>TOTAL COURSES</h3>
                    <div class="stat-value"><?= $total_courses ?></div>
                    <div class="stat-change positive">Semester ini</div>
                </div>
            </div>

            <div class="stat-card assignments">
                <div class="stat-icon">
                    📝
                </div>
                <div class="stat-content">
                    <h3>PENDING ASSIGNMENTS</h3>
                    <div class="stat-value"><?= $pending_assignments ?></div>
                    <div class="stat-change <?= $upcoming_deadlines > 0 ? 'negative' : 'positive' ?>">
                        <?= $upcoming_deadlines ?> deadline terdekat
                    </div>
                </div>
            </div>

            <div class="stat-card deadlines">
                <div class="stat-icon">
                    ⏰
                </div>
                <div class="stat-content">
                    <h3>UPCOMING DEADLINES</h3>
                    <div class="stat-value"><?= $upcoming_deadlines ?></div>
                    <div class="stat-change positive">7 hari kedepan</div>
                </div>
            </div>

            <div class="stat-card progress">
                <div class="stat-icon">
                    📈
                </div>
                <div class="stat-content">
                    <h3>LEARNING PROGRESS</h3>
                    <div class="stat-value"><?= $avg_progress ?>%</div>
                    <div class="stat-change positive">Rata-rata</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Learning Progress Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Learning Progress</h3>
                    <div class="chart-legend">
                        <?php foreach ($courses_progress as $index => $course): ?>
                        <div class="legend-item">
                            <span class="legend-color" style="background: <?= ['#3498db', '#2ecc71', '#f39c12', '#e74c3c'][$index] ?? '#95a5a6' ?>"></span>
                            <span><?= escape_html($course['kode_mk']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="chart-visual">
                    <!-- Y-axis -->
                    <div class="chart-y-axis">
                        <div class="y-axis-label">100</div>
                        <div class="y-axis-label">80</div>
                        <div class="y-axis-label">60</div>
                        <div class="y-axis-label">40</div>
                        <div class="y-axis-label">20</div>
                        <div class="y-axis-label">0</div>
                    </div>

                    <!-- Horizontal Grid Lines -->
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>
                    <div class="grid-line"></div>

                    <!-- Chart Bars -->
                    <div class="chart-bars">
                        <?php 
                        $colors = ['#3498db', '#2ecc71', '#f39c12', '#e74c3c'];
                        foreach ($courses_progress as $index => $course): 
                        ?>
                        <div class="chart-bar">
                            <div class="bar-container">
                                <div class="bar-fill" style="height: <?= $course['progress'] ?>%; background: <?= $colors[$index] ?? '#95a5a6' ?>"></div>
                                <div class="bar-tooltip"><?= $course['progress'] ?>% Progress</div>
                            </div>
                            <div class="bar-label"><?= escape_html($course['kode_mk']) ?></div>
                            <div class="bar-value"><?= $course['progress'] ?>%</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Activity and Notifications -->
            <div class="activity-container">
                <!-- Upcoming Deadlines -->
                <div class="notifications-card">
                    <h3>Deadline Terdekat</h3>
                    <ul class="notification-list">
                        <?php if (empty($upcoming_tasks)): ?>
                            <li class="notification-item">
                                <div class="notification-content">
                                    <div class="notification-text">Tidak ada tugas yang mendesak</div>
                                    <div class="notification-time">Selamat! 🎉</div>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php foreach ($upcoming_tasks as $task): ?>
                            <li class="notification-item">
                                <div class="notification-badge <?= (strtotime($task['deadline']) - time() < 86400) ? 'urgent' : 'info' ?>"></div>
                                <div class="notification-content">
                                    <div class="notification-text">
                                        <strong><?= escape_html($task['nama_mk']) ?></strong><br>
                                        <?= escape_html($task['judul']) ?>
                                    </div>
                                    <div class="notification-time"><?= time_difference($task['deadline']) ?></div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
