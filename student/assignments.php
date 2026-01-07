<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

if (is_admin()) {
    redirect('../admin/dashboard.php');
}

$db = getDB();
$user_id = get_user_info('user_id');

// Get all assignments from enrolled courses
$stmt = $db->prepare("
    SELECT 
        t.*,
        mk.nama_mk,
        mk.kode_mk,
        s.submission_id,
        s.submitted_at,
        s.score,
        DATEDIFF(t.deadline, NOW()) as days_left,
        CASE 
            WHEN s.submission_id IS NOT NULL THEN 'uploaded'
            WHEN t.deadline < NOW() THEN 'overdue'
            ELSE 'pending'
        END as status
    FROM tugas t
    INNER JOIN enrollments e ON t.mk_id = e.mk_id
    INNER JOIN mata_kuliah mk ON t.mk_id = mk.mk_id
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
    WHERE e.user_id = ?
    ORDER BY 
        CASE 
            WHEN t.deadline < NOW() AND s.submission_id IS NULL THEN 1
            WHEN s.submission_id IS NULL THEN 2
            ELSE 3
        END,
        t.deadline ASC
");
$stmt->execute([$user_id, $user_id]);
$assignments = $stmt->fetchAll();

$total_assignments = count($assignments);
$uploaded = count(array_filter($assignments, fn($a) => $a['status'] === 'uploaded'));
$pending = count(array_filter($assignments, fn($a) => $a['status'] === 'pending'));
$overdue = count(array_filter($assignments, fn($a) => $a['status'] === 'overdue'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - EduLearn</title>
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li class="active"><a href="assignments.php">Assignments</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="assignments-header">
            <h1>Assignments <span class="assignment-count"><?= $total_assignments ?> tugas</span></h1>
            <p>Daftar tugas seluruh mata kuliah</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?= $total_assignments ?></div>
                <div class="stat-label">Total Tugas</div>
            </div>
            <div class="stat-card pending-card">
                <div class="stat-number"><?= $pending ?></div>
                <div class="stat-label">Belum Dikumpulkan</div>
            </div>
            <div class="stat-card success-card">
                <div class="stat-number"><?= $uploaded ?></div>
                <div class="stat-label">Sudah Dikumpulkan</div>
            </div>
            <div class="stat-card warning-card">
                <div class="stat-number"><?= $overdue ?></div>
                <div class="stat-label">Terlambat</div>
            </div>
        </div>

        <div class="assignment-list">
            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <h3>Belum Ada Tugas</h3>
                    <p>Tidak ada tugas yang tersedia saat ini</p>
                </div>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                <a href="course_detail.php?id=<?= $assignment['mk_id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                    <div class="assignment-item" style="cursor: pointer;">
                        <h3 class="course-title">
                            <span class="course-icon" style="background: <?= $assignment['color'] ?>">
                                <?= substr($assignment['kode_mk'], 0, 2) ?>
                            </span>
                            <?= escape_html($assignment['nama_mk']) ?>
                        </h3>
                        
                        <div class="assignment-title"><?= escape_html($assignment['judul']) ?></div>
                        
                        <?php if ($assignment['deskripsi']): ?>
                        <div class="assignment-desc"><?= escape_html($assignment['deskripsi']) ?></div>
                        <?php endif; ?>
                        
                        <div class="deadline">
                            ⏰ Deadline: <?= format_date($assignment['deadline']) ?>
                            <?php 
                            $days_left = ceil((strtotime($assignment['deadline']) - time()) / 86400);
                            if ($assignment['status'] === 'pending' && $days_left <= 3 && $days_left > 0): 
                            ?>
                                <span style="color: #e74c3c;">(<?= $days_left ?> hari lagi!)</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="upload-section" onclick="event.stopPropagation();">
                            <?php if ($assignment['submission_id']): ?>
                                <div class="status-badge uploaded">✓ Telah diunggah</div>
                                <div class="submission-info">
                                    <small>Dikumpulkan: <?= format_date($assignment['submitted_at']) ?></small>
                                    <?php if ($assignment['score']): ?>
                                        <strong style="color: #27ae60;">Nilai: <?= $assignment['score'] ?></strong>
                                    <?php else: ?>
                                        <span class="waiting-grade">Menunggu penilaian</span>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (strtotime($assignment['deadline']) < time()): ?>
                                <div class="status-badge overdue">⚠️ Terlambat</div>
                                <button class="upload-btn late" onclick="openUploadModal(<?= $assignment['tugas_id'] ?>, '<?= escape_html($assignment['judul']) ?>')">
                                    Unggah Sekarang (Terlambat)
                                </button>
                            <?php else: ?>
                                <div class="status-badge pending">⏳ Belum diunggah</div>
                                <button class="upload-btn" onclick="openUploadModal(<?= $assignment['tugas_id'] ?>, '<?= escape_html($assignment['judul']) ?>')">
                                    📤 Unggah Tugas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeUploadModal()">&times;</span>
        <h2 id="modalTitle">Upload Tugas</h2>
        <form action="submit_assignment.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="tugas_id" id="tugas_id">
            <div class="form-group">
                <label for="file">Pilih File:</label>
                <input type="file" name="file" id="file" required accept=".pdf,.doc,.docx,.zip,.rar">
                <small>Format: PDF, DOC, DOCX, ZIP, RAR (Max 5MB)</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload</button>
                <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal(tugasId, judul) {
    document.getElementById('uploadModal').style.display = 'flex';
    document.getElementById('tugas_id').value = tugasId;
    document.getElementById('modalTitle').textContent = 'Upload: ' + judul;
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.getElementById('uploadForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('uploadModal');
    if (event.target == modal) {
        closeUploadModal();
    }
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}
.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
}
.close {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.form-group {
    margin: 20px 0;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}
.form-group input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 2px dashed #ddd;
    border-radius: 5px;
}
.form-group small {
    color: #666;
    display: block;
    margin-top: 5px;
}
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    flex: 1;
}
.btn-primary {
    background: #3498db;
    color: white;
}
.btn-secondary {
    background: #95a5a6;
    color: white;
}
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}
.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #2c3e50;
}
.pending-card .stat-number { color: #f39c12; }
.success-card .stat-number { color: #27ae60; }
.warning-card .stat-number { color: #e74c3c; }
.urgent {
    color: #e74c3c;
    font-weight: bold;
}
.submission-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.score {
    color: #27ae60;
    font-size: 18px;
}
.waiting-grade {
    color: #f39c12;
    font-style: italic;
}
.upload-btn.late {
    background: #e74c3c;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 10px;
}
</style>
</body>
</html>
