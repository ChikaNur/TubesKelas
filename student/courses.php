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

// Get enrolled courses with statistics
$stmt = $db->prepare("
    SELECT 
        mk.*,
        d.nama_dosen,
        e.progress,
        e.enrolled_at,
        (SELECT COUNT(*) FROM tugas WHERE mk_id = mk.mk_id) as total_tugas,
        (SELECT COUNT(*) FROM tugas t 
         LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
         WHERE t.mk_id = mk.mk_id AND s.submission_id IS NULL AND t.deadline > NOW()) as pending_tugas,
        (SELECT COUNT(*) FROM materi WHERE mk_id = mk.mk_id) as total_materi
    FROM enrollments e
    INNER JOIN mata_kuliah mk ON e.mk_id = mk.mk_id
    LEFT JOIN dosen d ON mk.dosen_id = d.dosen_id
    WHERE e.user_id = ?
    ORDER BY e.progress DESC, mk.nama_mk
");
$stmt->execute([$user_id, $user_id]);
$courses = $stmt->fetchAll();

// Get statistics
$total_courses = count($courses);
$active_courses = count(array_filter($courses, fn($c) => $c['status'] === 'aktif'));
$completed_courses = count(array_filter($courses, fn($c) => $c['progress'] >= 100));
$total_pending = array_sum(array_column($courses, 'pending_tugas'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/couses.css">
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
            <li class="active"><a href="courses.php">Courses</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <!-- Courses Header -->
        <div class="courses-header">
            <h1>My Courses</h1>
            <p>Kelola dan pantau perkembangan mata kuliah</p>
        </div>

        <!-- Course Stats -->
        <div class="course-stats">
            <h3>Ringkasan Perkuliahan</h3>
            <div class="stats-grid">
                <div class="stat-item all">
                    <div class="stat-number"><?= $total_courses ?></div>
                    <div class="stat-label">Total Mata Kuliah</div>
                </div>
                <div class="stat-item active">
                    <div class="stat-number"><?= $active_courses ?></div>
                    <div class="stat-label">Sedang Berjalan</div>
                </div>
                <div class="stat-item completed">
                    <div class="stat-number"><?= $completed_courses ?></div>
                    <div class="stat-label">Telah Selesai</div>
                </div>
                <div class="stat-item upcoming">
                    <div class="stat-number"><?= $total_pending ?></div>
                    <div class="stat-label">Tugas Mendatang</div>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="courses-grid">
            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <h3>Belum Ada Mata Kuliah</h3>
                    <p>Anda belum terdaftar di mata kuliah manapun</p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-thumbnail">
                        <div class="course-thumbnail-icon">
                            <?php 
                            // Get first letter of course code for thumbnail
                            $firstLetter = substr($course['kode_mk'], 0, 1);
                            echo strtoupper($firstLetter);
                            ?>
                        </div>
                    </div>
                    <div class="course-header <?= strtolower($course['kode_mk']) ?>">
                        <div class="course-code"><?= escape_html($course['kode_mk']) ?></div>
                        <div class="course-status"><?= ucfirst($course['status']) ?></div>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?= escape_html($course['nama_mk']) ?></h3>
                        <div class="course-info">
                            <div class="course-info-item dosen">
                                Dosen: <?= escape_html($course['nama_dosen'] ?? 'Belum ditentukan') ?>
                            </div>
                            <div class="course-info-item semester">
                                Semester: <?= $course['semester'] ?>
                            </div>
                            <div class="course-info-item credits">
                                SKS: <?= $course['sks'] ?>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-label">Progress Pembelajaran</span>
                                <span class="progress-value <?= $course['progress'] >= 70 ? 'high' : ($course['progress'] >= 40 ? 'medium' : 'low') ?>">
                                    <?= round($course['progress']) ?>%
                                </span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill <?= $course['progress'] >= 70 ? 'high' : ($course['progress'] >= 40 ? 'medium' : 'low') ?>" 
                                     style="width: <?= min($course['progress'], 100) ?>%"></div>
                            </div>
                        </div>

                        <div class="course-meta">
                            <span>📚 <?= $course['total_materi'] ?> Materi</span>
                            <span>📝 <?= $course['pending_tugas'] ?> Tugas Pending</span>
                        </div>
                        
                        <div class="course-actions">
                            <a href="course_detail.php?id=<?= $course['mk_id'] ?>" class="action-btn primary">
                                📖 View Course
                            </a>
                            <a href="timetable.php" class="action-btn secondary">
                                📅 Jadwal
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
