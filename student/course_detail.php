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
$mk_id = (int)($_GET['id'] ?? 0);

if ($mk_id === 0) {
    redirect('courses.php');
}

// Get course details
$stmt = $db->prepare("
    SELECT mk.*, d.nama_dosen, e.progress
    FROM mata_kuliah mk
    LEFT JOIN dosen d ON mk.dosen_id = d.dosen_id
    INNER JOIN enrollments e ON mk.mk_id = e.mk_id
    WHERE mk.mk_id = ? AND e.user_id = ?
");
$stmt->execute([$mk_id, $user_id]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['error_message'] = 'Anda tidak terdaftar di mata kuliah ini';
    redirect('courses.php');
}

// Get course materials
$stmt = $db->prepare("
    SELECT materi_id, judul, deskripsi, tipe_file, nama_file, ukuran_file, uploaded_at
    FROM materi
    WHERE mk_id = ?
    ORDER BY uploaded_at DESC
");
$stmt->execute([$mk_id]);
$materials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($course['nama_mk']) ?> - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/couses.css">
    <style>
        .course-detail-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; border-radius: 10px; margin-bottom: 30px; }
        .materials-list { background: white; border-radius: 10px; padding: 20px; }
        .material-item { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .material-item:hover { background: #f8f9fa; }
        .material-info h3 { margin: 0 0 5px 0; }
        .material-meta { color: #666; font-size: 14px; }
        .download-btn { background: #3498db; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .download-btn:hover { background: #2980b9; }
        .empty-state { text-align: center; padding: 60px 20px; }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header"><h1>EduLearn</h1></div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li class="active"><a href="courses.php">Courses</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li><a href="assignments.php">Assignment</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="course-detail-header">
            <h1><?= escape_html($course['nama_mk']) ?></h1>
            <p>Kode: <?= escape_html($course['kode_mk']) ?> | Dosen: <?= escape_html($course['nama_dosen']) ?> | SKS: <?= $course['sks'] ?></p>
            <div style="margin-top: 20px;">
                <strong>Progress: <?= round($course['progress']) ?>%</strong>
                <div style="background: rgba(255,255,255,0.3); height: 10px; border-radius: 5px; margin-top: 10px;">
                    <div style="background: white; height: 100%; width: <?= min($course['progress'], 100) ?>%; border-radius: 5px;"></div>
                </div>
            </div>
        </div>

        <?php if ($course['deskripsi']): ?>
        <div class="materials-list">
            <h3>Deskripsi Mata Kuliah</h3>
            <p><?= escape_html($course['deskripsi']) ?></p>
        </div>
        <br>
        <?php endif; ?>

        <div class="materials-list">
            <h3>📚 Materi Pembelajaran (<?= count($materials) ?>)</h3>
            
            <?php if (empty($materials)): ?>
                <div class="empty-state">
                    <h4>Belum Ada Materi</h4>
                    <p>Materi untuk mata kuliah ini akan segera ditambahkan</p>
                </div>
            <?php else: ?>
                <?php foreach ($materials as $material): ?>
                <div class="material-item">
                    <div class="material-info">
                        <h3><?= escape_html($material['judul']) ?></h3>
                        <?php if ($material['deskripsi']): ?>
                        <p style="margin: 5px 0; color: #555;"><?= escape_html($material['deskripsi']) ?></p>
                        <?php endif; ?>
                        <div class="material-meta">
                            📄 <?= escape_html($material['nama_file']) ?> 
                            (<?= format_file_size($material['ukuran_file']) ?>) 
                            | Diupload: <?= format_date($material['uploaded_at']) ?>
                        </div>
                    </div>
                    <div>
                        <a href="../materials/view_materi.php?id=<?= $material['materi_id'] ?>" 
                           class="download-btn" 
                           target="_blank">
                            📥 View/Download
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="courses.php" style="background: #95a5a6; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block;">
                ← Kembali ke Daftar Courses
            </a>
        </div>
    </main>
</div>
</body>
</html>
