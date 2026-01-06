<?php
/**
 * Users CRUD Process Handler (Admin)
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            $nama = sanitize_input($_POST['nama']);
            $email = sanitize_input($_POST['email']);
            $nim = !empty($_POST['nim']) ? sanitize_input($_POST['nim']) : null;
            $role = $_POST['role'];
            $password = $_POST['password'];
            
            // Check duplicate email
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "Email sudah digunakan";
                redirect('users.php');
            }
            
            // Check duplicate NIM
            if ($nim) {
                $stmt = $db->prepare("SELECT user_id FROM users WHERE nim = ?");
                $stmt->execute([$nim]);
                if ($stmt->fetch()) {
                    $_SESSION['error_message'] = "NIM sudah digunakan";
                    redirect('users.php');
                }
            }
            
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("INSERT INTO users (nama, email, nim, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $nim, $hashedPassword, $role]);
            
            $_SESSION['success_message'] = "User berhasil ditambahkan";
            break;
            
        case 'delete':
            $user_id = (int)$_GET['user_id'];
            
            // Can't delete yourself
            if ($user_id == get_user_info('user_id')) {
                $_SESSION['error_message'] = "Tidak dapat menghapus user sendiri";
                redirect('users.php');
            }
            
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['success_message'] = "User berhasil dihapus";
            break;
            
        default:
            $_SESSION['error_message'] = "Aksi tidak valid";
            break;
    }
} catch (PDOException $e) {
    error_log("User CRUD Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan sistem";
}

redirect('users.php');
