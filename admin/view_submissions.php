<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$tugas_id = (int)($_GET['tugas_id'] ?? 0);

if ($tugas_id === 0) {
    redirect('assignments.php');
}

// Get assignment details
$stmt = $db->prepare("
    SELECT t.*, mk.nama_mk, mk.kode_mk,
           (SELECT COUNT(*) FROM enrollments WHERE mk_id = t.mk_id) as total_students
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.mk_id = mk.mk_id
    WHERE t.tugas_id = ?
");
$stmt->execute([$tugas_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    $_SESSION['error_message'] = 'Assignment tidak ditemukan';
    redirect('assignments.php');
}

// Get all submissions
$stmt = $db->prepare("
    SELECT s.*, u.nama, u.nim
    FROM submissions s
    INNER JOIN users u ON s.user_id = u.user_id
    WHERE s.tugas_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$tugas_id]);
$submissions = $stmt->fetchAll();

$graded = count(array_filter($submissions, fn($s) => $s['score'] !== null));
$late = count(array_filter($submissions, fn($s) => $s['is_late'] == 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions - <?= escape_html($assignment['judul']) ?></title>
    <link rel="stylesheet" href="../assets/css/adminindex.css">
    <style>
        .submission-header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .stats-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; color: #3498db; }
        .admin-table-container { background: white; border-radius: 10px; padding: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; }
        .badge { padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .btn-sm { padding: 5px 10px; font-size: 12px; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; border: none; }
        .btn-secondary { background: #95a5a6; color: white; border: none; }
    </style>
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
            <li class="active"><a href="assignments.php">Assignments</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="submission-header">
            <h1>📝 <?= escape_html($assignment['judul']) ?></h1>
            <p><strong>Course:</strong> <?= escape_html($assignment['nama_mk']) ?> (<?= escape_html($assignment['kode_mk']) ?>)</p>
            <p><strong>Deadline:</strong> <?= format_date($assignment['deadline']) ?> | <strong>Max Score:</strong> <?= $assignment['max_score'] ?></p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?= count($submissions) ?></div>
                <div>Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $assignment['total_students'] ?></div>
                <div>Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $graded ?></div>
                <div>Graded</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #e74c3c;"><?= $late ?></div>
                <div>Late Submissions</div>
            </div>
        </div>

        <div class="admin-table-container">
            <h3>Student Submissions</h3>
            <?php if (empty($submissions)): ?>
                <p style="text-align: center; padding: 40px; color: #999;">Belum ada submission</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>NIM</th>
                        <th>File</th>
                        <th>Submitted At</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td><strong><?= escape_html($sub['nama']) ?></strong></td>
                        <td><?= escape_html($sub['nim']) ?></td>
                        <td><?= escape_html($sub['nama_file']) ?><br><small>(<?= format_file_size($sub['ukuran_file']) ?>)</small></td>
                        <td><?= format_date($sub['submitted_at']) ?></td>
                        <td>
                            <?php if ($sub['is_late']): ?>
                                <span class="badge badge-danger">Late</span>
                            <?php else: ?>
                                <span class="badge badge-success">On Time</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($sub['score'] !== null): ?>
                                <strong style="color: #27ae60;"><?= $sub['score'] ?>/<?= $assignment['max_score'] ?></strong>
                            <?php else: ?>
                                <span style="color: #95a5a6;">Not graded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-sm btn-primary" onclick="window.open('../materials/view_materi.php?type=submission&id=<?= $sub['submission_id'] ?>', '_blank')">
                                📥 Download
                            </button>
                            <button class="btn-sm btn-secondary" onclick="gradeSubmission(<?= $sub['submission_id'] ?>, '<?= escape_html($sub['nama']) ?>', <?= $sub['score'] ?? 0 ?>)">
                                ✏️ Grade
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="assignments.php" style="background: #95a5a6; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block;">
                ← Back to Assignments
            </a>
        </div>
    </main>
</div>

<script>
function gradeSubmission(id, name, currentScore) {
    const score = prompt(`Enter score for ${name} (Current: ${currentScore || 'Not graded'}):`, currentScore || '');
    if (score !== null && score !== '') {
        window.location.href = 'grade_submission.php?submission_id=' + id + '&score=' + score;
    }
}
</script>
</body>
</html>
