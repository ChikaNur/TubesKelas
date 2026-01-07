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

// Get weekly schedule
$stmt = $db->prepare("
    SELECT j.*, mk.nama_mk, mk.kode_mk, d.nama_dosen
    FROM jadwal j
    INNER JOIN enrollments e ON j.mk_id = e.mk_id
    INNER JOIN mata_kuliah mk ON j.mk_id = mk.mk_id
    LEFT JOIN dosen d ON mk.dosen_id = d.dosen_id
    WHERE e.user_id = ?
    ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), j.jam_mulai
");
$stmt->execute([$user_id]);
$schedules = $stmt->fetchAll();

// Group by time slots and days
$time_slots = ['08:00', '10:00', '13:00', '15:00'];
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$grid = [];

foreach ($time_slots as $slot) {
    foreach ($days as $day) {
        $grid[$slot][$day] = null;
    }
}

foreach ($schedules as $schedule) {
    $time = substr($schedule['jam_mulai'], 0, 5);
    $grid[$time][$schedule['hari']] = $schedule;
}

$current_day = ['Sunday' => null, 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
                 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 
                 'Saturday' => null][date('l')];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable - EduLearn</title>
    <link rel="stylesheet" href="../assets/css/student-style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>EduLearn</h1>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li class="active"><a href="timetable.php">Timetable</a></li>
            <li><a href="assignments.php">Assignments</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="timetable-header">
            <h1>Weekly Timetable</h1>
            <p>Jadwal perkuliahan semester berjalan</p>
        </div>

        <div class="timetable-container">
            <div class="timetable-grid">
                <div class="timetable-header-cell">Waktu</div>
                <?php foreach ($days as $day): ?>
                    <div class="timetable-header-cell <?= $day === $current_day ? 'current-day' : '' ?>">
                        <?= $day ?>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($time_slots as $slot): ?>
                    <div class="time-slot">
                        <?= $slot ?><br>
                        <span><?= $slot ?> - <?= date('H:i', strtotime($slot) + 6000) ?></span>
                    </div>
                    <?php foreach ($days as $day): ?>
                        <?php if ($grid[$slot][$day]): ?>
                            <?php $class = $grid[$slot][$day]; ?>
                            <div class="class-card">
                                <div class="class-content">
                                    <h4><?= escape_html($class['nama_mk']) ?></h4>
                                    <div class="class-meta">
                                        <span>Ruang: <?= escape_html($class['ruangan']) ?></span>
                                        <span class="class-room">Kelas</span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="class-card empty"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($current_day && !empty(array_filter($schedules, fn($s) => $s['hari'] === $current_day))): ?>
        <div class="today-schedule">
            <h3>Jadwal Hari Ini (<?= $current_day ?>)</h3>
            <div class="today-classes">
                <?php foreach (array_filter($schedules, fn($s) => $s['hari'] === $current_day) as $class): ?>
                <div class="today-class">
                    <div class="class-time"><?= substr($class['jam_mulai'], 0, 5) ?> - <?= substr($class['jam_selesai'], 0, 5) ?></div>
                    <div class="class-details">
                        <h4><?= escape_html($class['nama_mk']) ?></h4>
                        <p>Ruang: <?= escape_html($class['ruangan']) ?> | Dosen: <?= escape_html($class['nama_dosen']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
