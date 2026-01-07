<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get filter parameters
$sortOrder = isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc']) ? $_GET['sort'] : 'desc';
$monthFilter = isset($_GET['month']) ? sanitize_input($_GET['month']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';

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

// Recent activity with filters
$activitySql = "
    SELECT u.nama, mk.nama_mk, e.enrolled_at
    FROM enrollments e
    INNER JOIN users u ON e.user_id = u.user_id
    INNER JOIN mata_kuliah mk ON e.mk_id = mk.mk_id
    WHERE 1=1
";
$activityParams = [];

if ($monthFilter) {
    $activitySql .= " AND DATE_FORMAT(e.enrolled_at, '%Y-%m') = ?";
    $activityParams[] = $monthFilter;
}

if ($dateFrom) {
    $activitySql .= " AND e.enrolled_at >= ?";
    $activityParams[] = $dateFrom . ' 00:00:00';
}

if ($dateTo) {
    $activitySql .= " AND e.enrolled_at <= ?";
    $activityParams[] = $dateTo . ' 23:59:59';
}

$activitySql .= " ORDER BY e.enrolled_at " . strtoupper($sortOrder) . " LIMIT 10";

$stmt = $db->prepare($activitySql);
$stmt->execute($activityParams);
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
        
        <!-- Activity Filters -->
        <div class="admin-search-filter" style="margin: 30px 0 20px 0;">
            <div style="flex: 1;">
                <h3 style="margin: 0; color: var(--text-primary);">📋 Recent Activity</h3>
            </div>
            <div class="filter-options">
                <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="">📅 All Months</option>
                    <?php
                    $months = [
                        '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                        '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                    ];
                    $currentYear = date('Y');
                    for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++) {
                        foreach ($months as $m => $monthName) {
                            $value = "$y-$m";
                            $selected = $monthFilter === $value ? 'selected' : '';
                            echo "<option value='$value' $selected>$monthName $y</option>";
                        }
                    }
                    ?>
                </select>
                
                <input type="date" name="date_from" value="<?= escape_html($dateFrom) ?>" 
                       class="admin-date-input" placeholder="From Date" 
                       onchange="document.getElementById('filterForm').submit()">
                       
                <input type="date" name="date_to" value="<?= escape_html($dateTo) ?>" 
                       class="admin-date-input" placeholder="To Date" 
                       onchange="document.getElementById('filterForm').submit()">
                
                <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>⬇️ Newest First</option>
                    <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>⬆️ Oldest First</option>
                </select>
            </div>
        </div>
        
        <!-- Hidden form for filter submission -->
        <form id="filterForm" method="GET" style="display: none;">
            <input type="hidden" name="month" value="<?= escape_html($monthFilter) ?>">
            <input type="hidden" name="date_from" value="<?= escape_html($dateFrom) ?>">
            <input type="hidden" name="date_to" value="<?= escape_html($dateTo) ?>">
            <input type="hidden" name="sort" value="<?= $sortOrder ?>">
        </form>
        
        <script>
        document.querySelectorAll('.admin-filter-select, .admin-date-input').forEach(el => {
            el.addEventListener('change', function() {
                const form = document.getElementById('filterForm');
                const name = this.name;
                let input = form.querySelector(`input[name="${name}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    form.appendChild(input);
                }
                input.value = this.value;
            });
        });
        </script>

        <!-- Recent Activity Table -->
        <div class="admin-table-container">
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
    </main>
</div>
</body>
</html>
