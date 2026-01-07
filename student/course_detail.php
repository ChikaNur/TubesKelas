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

// Get assignments for this course
$stmt = $db->prepare("
    SELECT 
        t.*,
        s.submission_id,
        s.submitted_at,
        s.score,
        s.feedback,
        DATEDIFF(t.deadline, NOW()) as days_left,
        CASE 
            WHEN s.submission_id IS NOT NULL THEN 'uploaded'
            WHEN t.deadline < NOW() THEN 'overdue'
            ELSE 'pending'
        END as status
    FROM tugas t
    LEFT JOIN submissions s ON t.tugas_id = s.tugas_id AND s.user_id = ?
    WHERE t.mk_id = ?
    ORDER BY 
        CASE 
            WHEN t.deadline < NOW() AND s.submission_id IS NULL THEN 1
            WHEN s.submission_id IS NULL THEN 2
            ELSE 3
        END,
        t.deadline ASC
");
$stmt->execute([$user_id, $mk_id]);
$assignments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($course['nama_mk']) ?> - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/student-style.css">
    <style>
        .course-detail-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 40px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }
        .course-detail-header h1 { margin: 0 0 10px 0; font-size: 32px; }
        .course-detail-header p { opacity: 0.95; margin: 0; }
        
        .materials-list { 
            background: white; 
            border-radius: 16px; 
            padding: 28px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }
        .materials-list h3 { 
            margin: 0 0 20px 0; 
            color: #2c3e50; 
            font-size: 20px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid #f1f5f9;
        }
        
        .material-item { 
            padding: 20px; 
            border-bottom: 1px solid #f1f5f9; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .material-item:last-child { border-bottom: none; margin-bottom: 0; }
        .material-item:hover { 
            background: #f8fafc; 
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .assignment-item { padding: 24px !important; }
        .assignment-item.pending { border-left: 4px solid #f39c12; }
        .assignment-item.uploaded { border-left: 4px solid #16a34a; }
        .assignment-item.overdue { border-left: 4px solid #dc2626; }
        
        .material-info h3 { margin: 0 0 8px 0; font-size: 18px; color: #2c3e50; }
        .material-meta { color: #64748b; font-size: 14px; margin-top: 8px; }
        
        .download-btn { 
            background: #3498db; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .download-btn:hover { 
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .empty-state { 
            text-align: center; 
            padding: 60px 20px;
            color: #64748b;
        }
        .empty-state h4 { color: #2c3e50; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="container">
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

        <!-- Assignments Section -->
        <div class="materials-list" style="margin-top: 30px;">
            <h3>📝 Tugas (<?= count($assignments) ?>)</h3>
            
            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <h4>Belum Ada Tugas</h4>
                    <p>Tidak ada tugas untuk mata kuliah ini saat ini</p>
                </div>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                <div class="material-item assignment-item <?= $assignment['status'] ?>" style="flex-direction: column; align-items: flex-start;">
                    <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-start;">
                        <div class="material-info" style="flex: 1;">
                            <h3 style="color: #2c3e50;"><?= escape_html($assignment['judul']) ?></h3>
                            <?php if ($assignment['deskripsi']): ?>
                            <p style="margin: 5px 0; color: #555;"><?= escape_html($assignment['deskripsi']) ?></p>
                            <?php endif; ?>
                            <div class="material-meta">
                                <strong style="color: <?= $assignment['status'] === 'overdue' ? '#e74c3c' : '#f39c12' ?>;">
                                    ⏰ Deadline: <?= format_date($assignment['deadline']) ?>
                                </strong>
                                <?php if ($assignment['status'] === 'pending' && $assignment['days_left'] <= 3): ?>
                                    <span style="color: #e74c3c; margin-left: 10px;">(<?= $assignment['days_left'] ?> hari lagi!)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                            <?php if ($assignment['status'] === 'uploaded'): ?>
                                <span class="status-badge uploaded" style="padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">
                                    ✓ Telah diunggah
                                </span>
                            <?php elseif ($assignment['status'] === 'overdue'): ?>
                                <span class="status-badge overdue" style="padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                    ⚠️ Terlambat
                                </span>
                                <button class="upload-btn" style="background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                    📤 Unggah (Terlambat)
                                </button>
                            <?php else: ?>
                                <span class="status-badge pending" style="padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                                    ⏳ Belum diunggah
                                </span>
                                <a href="../student/assignments.php" class="download-btn" style="background: #3498db;">
                                    📤 Unggah Tugas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($assignment['status'] === 'uploaded'): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; width: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <small style="color: #64748b;">Dikumpulkan: <?= format_date($assignment['submitted_at']) ?></small>
                            <?php if ($assignment['score']): ?>
                                <strong style="color: #16a34a; font-size: 18px;">Nilai: <?= $assignment['score'] ?></strong>
                            <?php else: ?>
                                <span style="color: #f39c12; font-style: italic;">Menunggu penilaian</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($assignment['feedback']): ?>
                        <div style="margin-top: 10px; padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3498db;">
                            <strong style="color: #2c3e50;">Feedback dari dosen:</strong>
                            <p style="margin: 5px 0 0 0; color: #475569;"><?= escape_html($assignment['feedback']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
