<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get filters
$searchQuery = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$semesterFilter = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sksFilter = isset($_GET['sks']) ? (int)$_GET['sks'] : 0;
$dosenFilter = isset($_GET['dosen']) ? (int)$_GET['dosen'] : 0;

// Build query
$sql = "SELECT mk.*, d.nama_dosen,
           (SELECT COUNT(*) FROM enrollments WHERE mk_id = mk.mk_id) as student_count
    FROM mata_kuliah mk
    LEFT JOIN dosen d ON mk.dosen_id = d.dosen_id
    WHERE 1=1";
$params = [];

if ($searchQuery) {
    $sql .= " AND (mk.nama_mk LIKE ? OR mk.kode_mk LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($semesterFilter > 0) {
    $sql .= " AND mk.semester = ?";
    $params[] = $semesterFilter;
}

if ($statusFilter && in_array($statusFilter, ['aktif', 'draft', 'arsip'])) {
    $sql .= " AND mk.status = ?";
    $params[] = $statusFilter;
}

if ($sksFilter > 0) {
    $sql .= " AND mk.sks = ?";
    $params[] = $sksFilter;
}

if ($dosenFilter > 0) {
    $sql .= " AND mk.dosen_id = ?";
    $params[] = $dosenFilter;
}

$sql .= " ORDER BY mk.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get all instructors for the add/edit form
$stmt = $db->query("SELECT * FROM dosen ORDER BY nama_dosen");
$instructors = $stmt->fetchAll();

// Check for success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - EduLearn Admin</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/adminindex.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: #000; }
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Admin Sidebar -->
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <h1>EduLearn</h1>
                <p style="font-size: 12px; opacity: 0.8;">Admin Panel</p>
            </div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li class="active"><a href="courses.php">Manage Courses</a></li>
                <li><a href="assignments.php">Assignments</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
        <?php include '../includes/messages.php'; ?>
        
        <div class="admin-header">
                <div class="admin-title">
                    <h1>Manage Courses</h1>
                    <p>Create and manage all courses in the system</p>
                </div>
                <div class="admin-actions">
                    <button class="btn btn-primary" onclick="openModal('add')">+ Add New Course</button>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= escape_html($success_message) ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= escape_html($error_message) ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="admin-search-filter">
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Search by course name or code..." value="<?= escape_html($searchQuery) ?>" class="admin-search-input">
                    </form>
                </div>
                <div class="filter-options">
                    <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                        <option value="">📅 All Semesters</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?= $i ?>" <?= $semesterFilter == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    
                    <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                        <option value="">💼 All Status</option>
                        <option value="aktif" <?= $statusFilter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="arsip" <?= $statusFilter === 'arsip' ? 'selected' : '' ?>>Arsip</option>
                    </select>
                    
                    <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                        <option value="">📊 All SKS</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?= $i ?>" <?= $sksFilter == $i ? 'selected' : '' ?>><?= $i ?> SKS</option>
                        <?php endfor; ?>
                    </select>
                    
                    <select class="admin-filter-select" onchange="this.form.submit()" form="filterForm">
                        <option value="">👨‍🏫 All Lecturers</option>
                        <?php foreach ($instructors as $instructor): ?>
                        <option value="<?= $instructor['dosen_id'] ?>" <?= $dosenFilter == $instructor['dosen_id'] ? 'selected' : '' ?>>
                            <?= escape_html($instructor['nama_dosen']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Hidden form for filter submission -->
            <form id="filterForm" method="GET" style="display: none;">
                <input type="hidden" name="search" value="<?= escape_html($searchQuery) ?>">
                <input type="hidden" name="semester" value="<?= $semesterFilter ?>">
                <input type="hidden" name="status" value="<?= escape_html($statusFilter) ?>">
                <input type="hidden" name="sks" value="<?= $sksFilter ?>">
                <input type="hidden" name="dosen" value="<?= $dosenFilter ?>">
            </form>
            
            <script>
            // Update hidden form fields when filters change
            document.querySelectorAll('.admin-filter-select').forEach(select => {
                select.addEventListener('change', function() {
                    const formId = this.getAttribute('form');
                    const form = document.getElementById(formId);
                    const name = this.name || this.previousElementSibling?.name || 
                               (this.options[0].textContent.includes('Semester') ? 'semester' :
                                this.options[0].textContent.includes('Status') ? 'status' :
                                this.options[0].textContent.includes('SKS') ? 'sks' : 'dosen');
                    
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

            <!-- Courses Table -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>SKS</th>
                            <th>Semester</th>
                            <th>Mahasiswa</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><strong><?= escape_html($course['kode_mk']) ?></strong></td>
                            <td>
                                <strong><?= escape_html($course['nama_mk']) ?></strong><br>
                                <small><?= escape_html($course['deskripsi'] ? substr($course['deskripsi'], 0, 50) . '...' : '-') ?></small>
                            </td>
                            <td><?= escape_html($course['nama_dosen'] ?? '-') ?></td>
                            <td><?= $course['sks'] ?></td>
                            <td>Semester <?= $course['semester'] ?></td>
                            <td><?= $course['student_count'] ?></td>
                            <td>
                                <span class="status-badge status-<?= $course['status'] ?>">
                                    <?= ucfirst($course['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" onclick='editCourse(<?= json_encode($course) ?>)'>✏️</button>
                                    <button class="btn-icon btn-delete" onclick="deleteCourse(<?= $course['mk_id'] ?>, '<?= escape_html($course['nama_mk']) ?>')">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Course Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Course</h2>
            <form action="courses_process.php" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="mk_id" id="mk_id">
                
                <div class="form-row">
                    <label for="kode_mk">Kode Mata Kuliah</label>
                    <input type="text" name="kode_mk" id="kode_mk" required>
                </div>
                
                <div class="form-row">
                    <label for="nama_mk">Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" id="nama_mk" required>
                </div>
                
                <div class="form-row">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <label for="sks">SKS</label>
                    <input type="number" name="sks" id="sks" min="1" max="6" required>
                </div>
                
                <div class="form-row">
                    <label for="semester">Semester</label>
                    <input type="number" name="semester" id="semester" min="1" max="8" required>
                </div>
                
                <div class="form-row">
                    <label for="dosen_id">Dosen Pengampu</label>
                    <select name="dosen_id" id="dosen_id">
                        <option value="">- Pilih Dosen -</option>
                        <?php foreach ($instructors as $instructor): ?>
                        <option value="<?= $instructor['dosen_id'] ?>"><?= escape_html($instructor['nama_dosen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="status">Status</label>
                    <select name="status" id="status" required>
                        <option value="aktif">Aktif</option>
                        <option value="draft">Draft</option>
                        <option value="arsip">Arsip</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <button type="submit" class="btn btn-primary">Save Course</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action) {
            document.getElementById('courseModal').style.display = 'block';
            document.getElementById('formAction').value = action;
            
            if (action === 'add') {
                document.getElementById('modalTitle').textContent = 'Add New Course';
                document.getElementById('courseForm').reset();
            }
        }
        
        function closeModal() {
            document.getElementById('courseModal').style.display = 'none';
        }
        
        function editCourse(course) {
            openModal('edit');
            document.getElementById('modalTitle').textContent = 'Edit Course';
            document.getElementById('mk_id').value = course.mk_id;
            document.getElementById('kode_mk').value = course.kode_mk;
            document.getElementById('nama_mk').value = course.nama_mk;
            document.getElementById('deskripsi').value = course.deskripsi || '';
            document.getElementById('sks').value = course.sks;
            document.getElementById('semester').value = course.semester;
            document.getElementById('dosen_id').value = course.dosen_id || '';
            document.getElementById('status').value = course.status;
        }
        
        function deleteCourse(id, name) {
            if (confirm('Apakah Anda yakin ingin menghapus mata kuliah "' + name + '"?')) {
                window.location.href = 'courses_process.php?action=delete&mk_id=' + id;
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('courseModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
