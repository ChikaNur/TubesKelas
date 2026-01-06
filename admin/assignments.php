<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get all assignments with submission statistics
$stmt = $db->query("
    SELECT 
        t.*,
        mk.nama_mk,
        mk.kode_mk,
        (SELECT COUNT(*) FROM enrollments WHERE mk_id = t.mk_id) as total_students,
        (SELECT COUNT(*) FROM submissions WHERE tugas_id = t.tugas_id) as total_submissions
    FROM tugas t
    INNER JOIN mata_kuliah mk ON t.mk_id = mk.mk_id
    ORDER BY t.deadline DESC
");
$assignments = $stmt->fetchAll();

// Get courses for dropdown
$coursesStmt = $db->query("SELECT mk_id, nama_mk, kode_mk FROM mata_kuliah WHERE status = 'aktif' ORDER BY nama_mk");
$courses = $coursesStmt->fetchAll();

// Statistics
$total_assignments = count($assignments);
$total_submissions = array_sum(array_column($assignments, 'total_submissions'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - EduLearn Admin</title>
    <link rel="stylesheet" href="../assets/css/adminindex.css">
    <style>
        .admin-table-container { background: white; border-radius: 10px; padding: 20px; margin-top: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-icon { padding: 6px 10px; border: none; border-radius: 5px; cursor: pointer; background: #f8f9fa; font-size: 14px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; }
        .stats-overview { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }
        .stat-box { background: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #3498db; }
        .deadline-badge { padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .deadline-upcoming { background: #fff3cd; color: #856404; }
        .deadline-past { background: #f8d7da; color: #721c24; }
        .deadline-future { background: #d1ecf1; color: #0c5460; }
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
        
        <div class="admin-header">
            <div class="admin-title">
                <h1>Manage Assignments</h1>
                <p>Create and manage assignments for all courses</p>
            </div>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="showAddModal()">+ Add New Assignment</button>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-box">
                <div class="stat-number"><?= $total_assignments ?></div>
                <div>Total Assignments</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $total_submissions ?></div>
                <div>Total Submissions</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= count($courses) ?></div>
                <div>Active Courses</div>
            </div>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Assignment</th>
                        <th>Course</th>
                        <th>Deadline</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <?php 
                        $deadline = strtotime($assignment['deadline']);
                        $days_diff = floor(($deadline - time()) / 86400);
                        if ($days_diff < 0) {
                            $badge_class = 'deadline-past';
                            $badge_text = 'Passed';
                        } elseif ($days_diff <= 3) {
                            $badge_class = 'deadline-upcoming';
                            $badge_text = $days_diff . ' days';
                        } else {
                            $badge_class = 'deadline-future';
                            $badge_text = format_date($assignment['deadline']);
                        }
                        ?>
                    <tr>
                        <td>#<?= $assignment['tugas_id'] ?></td>
                        <td>
                            <strong><?= escape_html($assignment['judul']) ?></strong>
                            <?php if ($assignment['deskripsi']): ?>
                            <br><small><?= escape_html(substr($assignment['deskripsi'], 0, 60)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= escape_html($assignment['nama_mk']) ?></td>
                        <td><span class="deadline-badge <?= $badge_class ?>"><?= $badge_text ?></span></td>
                        <td><?= $assignment['total_submissions'] ?> / <?= $assignment['total_students'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" onclick="viewSubmissions(<?= $assignment['tugas_id'] ?>)" title="View Submissions">📝</button>
                                <button class="btn-icon" onclick="editAssignment(<?= $assignment['tugas_id'] ?>)" title="Edit">✏️</button>
                                <button class="btn-icon" onclick="deleteAssignment(<?= $assignment['tugas_id'] ?>, '<?= escape_html($assignment['judul']) ?>')" style="color:#dc3545" title="Delete">🗑️</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Assignment Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add New Assignment</h2>
        <form action="assignments_process.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Course *</label>
                <select name="mk_id" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['mk_id'] ?>"><?= escape_html($course['nama_mk']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Deadline *</label>
                <input type="datetime-local" name="deadline" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Max Score</label>
                <input type="number" name="max_score" class="form-control" value="100">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex:1">Create Assignment</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()" style="flex:1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeModal() { document.getElementById('addModal').style.display = 'none'; }
function deleteAssignment(id, title) {
    if (confirm('Delete assignment "' + title + '"?')) {
        window.location.href = 'assignments_process.php?action=delete&tugas_id=' + id;
    }
}
function viewSubmissions(id) {
    window.location.href = 'view_submissions.php?tugas_id=' + id;
}
</script>
</body>
</html>
