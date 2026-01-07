<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();

// Get all users
$searchQuery = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

$sql = "SELECT user_id, nama, nim, email, role, created_at FROM users WHERE 1=1";
$params = [];

if ($searchQuery) {
    $sql .= " AND (nama LIKE ? OR email LIKE ? OR nim LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($roleFilter && in_array($roleFilter, ['mahasiswa', 'admin'])) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics
$statsStmt = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'mahasiswa' THEN 1 ELSE 0 END) as students,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
    FROM users
");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - EduLearn Admin</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/adminindex.css">
    <style>
        .admin-search-filter { display: flex; gap: 15px; margin: 20px 0; }
        .search-box { flex: 1; }
        .search-box input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .filter-options { display: flex; gap: 10px; }
        .admin-table-container { background: white; border-radius: 10px; padding: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; }
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .action-buttons { display: flex; gap: 8px; }
        .btn-icon { padding: 6px 10px; border: none; border-radius: 5px; cursor: pointer; background: #f8f9fa; }
        .btn-icon:hover { background: #e9ecef; }
        .btn-edit { color: #007bff; }
        .btn-delete { color: #dc3545; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .stats-overview { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }
        .stat-box { background: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #3498db; }
        .btn-outline { background: white; border: 2px solid #3498db; color: #3498db; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-outline:hover { background: #3498db; color: white; }
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
            <li class="active"><a href="users.php">Manage Users</a></li>
            <li><a href="courses.php">Manage Courses</a></li>
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
                <h1>Manage Users</h1>
                <p>Manage all users and their permissions</p>
            </div>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="showAddUserModal()">+ Add New User</button>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-box">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $stats['students'] ?></div>
                <div>Students</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $stats['admins'] ?></div>
                <div>Admins</div>
            </div>
        </div>

        <div class="admin-search-filter">
            <div class="search-box">
                <form action="" method="GET">
                    <input type="text" name="search" placeholder="Search users by name, email, or NIM..." value="<?= escape_html($searchQuery) ?>">
                </form>
            </div>
            <div class="filter-options">
                <select class="form-control" onchange="window.location.href='?role='+this.value">
                    <option value="">All Roles</option>
                    <option value="mahasiswa" <?= $roleFilter === 'mahasiswa' ? 'selected' : '' ?>>Student</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= $user['user_id'] ?></td>
                        <td>
                            <strong><?= escape_html($user['nama']) ?></strong><br>
                            <?php if ($user['nim']): ?>
                            <small>NIM: <?= escape_html($user['nim']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= escape_html($user['email']) ?></td>
                        <td><span class="status-badge status-active"><?= ucfirst($user['role']) ?></span></td>
                        <td><?= format_date($user['created_at']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-edit" onclick="editUser(<?= $user['user_id'] ?>, '<?= escape_html($user['nama']) ?>', '<?= escape_html($user['email']) ?>', '<?= escape_html($user['nim'] ?? '') ?>', '<?= $user['role'] ?>')" title="Edit">✏️</button>
                                <?php if ($user['user_id'] != get_user_info('user_id')): ?>
                                <button class="btn-icon btn-delete" onclick="deleteUser(<?= $user['user_id'] ?>, '<?= escape_html($user['nama']) ?>')" title="Delete">🗑️</button>
                                <?php endif; ?>
            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Add New User</h2>
        <form action="users_process.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>NIM (for students)</label>
                <input type="text" name="nim" class="form-control">
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" class="form-control" required>
                    <option value="mahasiswa">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex:1">Add User</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()" style="flex:1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit User</h2>
        <form action="users_process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>NIM (for students)</label>
                <input type="text" name="nim" id="edit_nim" class="form-control">
            </div>
            <div class="form-group">
                <label>Role *</label>
                <select name="role" id="edit_role" class="form-control" required>
                    <option value="mahasiswa">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex:1">Update User</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()" style="flex:1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('addUserModal').style.display = 'none';
    document.getElementById('editUserModal').style.display = 'none';
}
function deleteUser(id, name) {
    if (confirm('Hapus user "' + name + '"?')) {
        window.location.href = 'users_process.php?action=delete&user_id=' + id;
    }
}
function editUser(userId, nama, email, nim, role) {
    document.getElementById('editUserModal').style.display = 'flex';
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_nim').value = nim || '';
    document.getElementById('edit_role').value = role;
}

</script>
</body>
</html>
```
