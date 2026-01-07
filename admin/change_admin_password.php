<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$user_id = get_user_info('user_id');

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = 'Semua field harus diisi';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = 'Password baru dan konfirmasi tidak cocok';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error_message'] = 'Password baru minimal 6 karakter';
    } else {
        // Get current password from database
        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        // Verify old password
        if (password_verify($old_password, $user['password'])) {
            // Update to new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $_SESSION['success_message'] = 'Password berhasil diubah!';
            } else {
                $_SESSION['error_message'] = 'Gagal mengubah password';
            }
        } else {
            $_SESSION['error_message'] = 'Password lama tidak sesuai';
        }
    }
    
    redirect('settings.php');
}
?>
