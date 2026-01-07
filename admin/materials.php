<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get filters
$courseFilter = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$searchQuery = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$sql = "SELECT m.*, mk.nama_mk, mk.kode_mk
    FROM materi m
    INNER JOIN mata_kuliah mk ON m.mk_id = mk.mk_id
    WHERE 1=1";
$params = [];

if ($courseFilter > 0) {
    $sql .= " AND m.mk_id = ?";
    $params[] = $courseFilter;
}

if ($searchQuery) {
    $sql .= " AND m.judul LIKE ?";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY m.uploaded_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// Get courses for dropdown
$coursesStmt = $db->query("SELECT mk_id, nama_mk, kode_mk FROM mata_kuliah WHERE status = 'aktif' ORDER BY nama_mk");
$courses = $coursesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials - EduLearn Admin</title>
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
            <li class="active"><a href="materials.php">Course Materials</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="admin-header">
            <div class="admin-title">
                <h1>📚 Course Materials</h1>
                <p>Manage learning materials for all courses</p>
            </div>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="showAddModal()">+ Add New Material</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="admin-search-filter">
            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search materials by title..." value="<?= escape_html($searchQuery) ?>" class="admin-search-input">
                </form>
            </div>
            <div class="filter-options">
                <select class="admin-filter-select" onchange="window.location.href='?course='+this.value+'&search=<?= urlencode($searchQuery) ?>'">
                    <option value="">📚 All Courses</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['mk_id'] ?>" <?= $courseFilter == $course['mk_id'] ? 'selected' : '' ?>>
                        <?= escape_html($course['nama_mk']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Material</th>
                        <th>Course</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">No materials found</td></tr>
                    <?php else: ?>
                        <?php foreach ($materials as $material): ?>
                        <tr>
                            <td>#<?= $material['materi_id'] ?></td>
                            <td>
                                <strong><?= escape_html($material['judul']) ?></strong>
                                <?php if ($material['deskripsi']): ?>
                                <br><small><?= escape_html(substr($material['deskripsi'], 0, 60)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= escape_html($material['nama_mk']) ?></td>
                            <td>📄 <?= escape_html($material['nama_file']) ?></td>
                            <td><?= format_file_size($material['ukuran_file']) ?></td>
                            <td><?= format_date($material['uploaded_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick='editMaterial(<?= json_encode($material) ?>)' title="Edit">✏️</button>
                                    <button class="btn-icon" onclick="deleteMaterial(<?= $material['materi_id'] ?>, '<?= escape_html($material['judul']) ?>')" style="color:#dc3545" title="Delete">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit Material Modal -->
<div id="materialModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:10px; width:90%; max-width:600px;">
        <span class="close" onclick="closeModal()" style="float:right; font-size:28px; cursor:pointer;">&times;</span>
        <h2 id="modalTitle">Add New Material</h2>
        <form action="materials_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="materi_id" id="materi_id">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label>Course *</label>
                <select name="mk_id" id="mk_id" class="form-control" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['mk_id'] ?>"><?= escape_html($course['nama_mk']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label>Title *</label>
                <input type="text" name="judul" id="judul" class="form-control" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label>Description</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;"></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label>Upload File * (PDF, DOC, PPT, etc.)</label>
                <input type="file" name="file" id="file" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                <small style="color:#64748b;">Max file size: 10MB</small>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="flex:1; padding:12px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer;">Save Material</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()" style="flex:1; padding:12px; background:#e2e8f0; color:#475569; border:none; border-radius:5px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('materialModal').style.display = 'flex';
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add New Material';
    document.querySelector('form').reset();
}

function closeModal() {
    document.getElementById('materialModal').style.display = 'none';
}

function editMaterial(material) {
    document.getElementById('materialModal').style.display = 'flex';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('modalTitle').textContent = 'Edit Material';
    document.getElementById('materi_id').value = material.materi_id;
    document.getElementById('mk_id').value = material.mk_id;
    document.getElementById('judul').value = material.judul;
    document.getElementById('deskripsi').value = material.deskripsi || '';
    document.getElementById('file').required = false;
}

function deleteMaterial(id, title) {
    if (confirm('Delete material "' + title + '"?')) {
        window.location.href = 'materials_process.php?action=delete&materi_id=' + id;
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('materialModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>
