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
    SELECT t.judul, t.deadline, mk.nama_mk, mk.kode_mk, mk.mk_id
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
    <link rel="stylesheet" href="../assets/css/student-style.css">
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
                    <div class="chart-legend" id="chartLegend">
                        <?php foreach ($courses_progress as $index => $course): ?>
                        <div class="legend-item">
                            <span class="legend-color" style="background: <?= ['#3498db', '#2ecc71', '#f39c12', '#e74c3c'][$index] ?? '#95a5a6' ?>"></span>
                            <span><?= escape_html($course['kode_mk']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="chart-visual">
                    <canvas id="progressChart" style="max-height: 300px;"></canvas>
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
                            <a href="course_detail.php?id=<?= $task['mk_id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                                <li class="notification-item" style="cursor: pointer; transition: all 0.3s;">
                                    <div class="notification-badge <?= (strtotime($task['deadline']) - time() < 86400) ? 'urgent' : 'info' ?>"></div>
                                    <div class="notification-content">
                                        <div class="notification-text">
                                            <strong><?= escape_html($task['nama_mk']) ?></strong><br>
                                            <?= escape_html($task['judul']) ?>
                                        </div>
                                        <div class="notification-time"><?= time_difference($task['deadline']) ?></div>
                                    </div>
                                </li>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Prepare data from PHP
const courseData = <?= json_encode(array_map(function($c) {
    return [
        'label' => $c['kode_mk'],
        'progress' => (int)$c['progress']
    ];
}, $courses_progress)) ?>;

// Chart colors
const colors = ['#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c'];

// Create the chart
const ctx = document.getElementById('progressChart');
const progressChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: courseData.map(c => c.label),
        datasets: [{
            label: 'Progress (%)',
            data: courseData.map(c => c.progress),
            backgroundColor: colors.slice(0, courseData.length).map(color => color + '99'), // Add transparency
            borderColor: colors.slice(0, courseData.length),
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#2c3e50',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                borderRadius: 8,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + '% Complete';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    },
                    color: '#64748b',
                    font: {
                        size: 12
                    }
                },
                grid: {
                    color: '#f1f5f9',
                    drawBorder: false
                }
            },
            x: {
                ticks: {
                    color: '#2c3e50',
                    font: {
                        size: 13,
                        weight: '600'
                    }
                },
                grid: {
                    display: false
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeInOutQuart'
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Add hover effect on chart
ctx.style.cursor = 'pointer';
ctx.addEventListener('mousemove', (e) => {
    const points = progressChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
    if (points.length) {
        e.target.style.cursor = 'pointer';
    } else {
        e.target.style.cursor = 'default';
    }
});
</script>
</body>
</html>
