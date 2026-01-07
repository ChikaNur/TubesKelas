<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $nama = sanitize_input($_POST['nama']);
            $email = sanitize_input($_POST['email']);
            $nim = sanitize_input($_POST['nim'] ?? '');
            $role = $_POST['role'];
            $password = $_POST['password'];
            
            // Validate
            if (empty($nama) || empty($email) || empty($role) || empty($password)) {
                throw new Exception('Semua field wajib harus diisi');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter');
            }
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email sudah terdaftar');
            }
            
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (nama, email, nim, role, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nama, $email, $nim, $role, $hashed_password]);
            
            $_SESSION['success_message'] = 'User berhasil ditambahkan';
            break;
            
        case 'edit':
            $user_id = (int)$_POST['user_id'];
            $nama = sanitize_input($_POST['nama']);
            $email = sanitize_input($_POST['email']);
            $nim = sanitize_input($_POST['nim'] ?? '');
            $role = $_POST['role'];
            
            if (empty($nama) || empty($email) || empty($role)) {
                throw new Exception('Nama, email, dan role wajib diisi');
            }
            
            // Check if email is taken by another user
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                throw new Exception('Email sudah digunakan user lain');
            }
            
            $stmt = $db->prepare("UPDATE users SET nama = ?, email = ?, nim = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$nama, $email, $nim, $role, $user_id]);
            
            $_SESSION['success_message'] = 'User berhasil diupdate';
            break;
            
        case 'delete':
            $user_id = (int)$_GET['user_id'];
            
            // Prevent deleting self
            if ($user_id == get_user_info('user_id')) {
                throw new Exception('Tidak bisa menghapus akun sendiri');
            }
            
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['success_message'] = 'User berhasil dihapus';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

redirect('users.php');
?>
