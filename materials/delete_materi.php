<?php
/**
 * Delete Course Material
 * Removes material from database (admin only)
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'Unauthorized';
    redirect('../admincourses.php');
}

$materi_id = (int)($_GET['id'] ?? 0);

if ($materi_id === 0) {
    $_SESSION['error_message'] = 'Material ID required';
    redirect('../admincourses.php');
}

try {
    $db = getDB();
    
    // Delete material
    $stmt = $db->prepare("DELETE FROM materi WHERE materi_id = ?");
    $stmt->execute([$materi_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Materi berhasil dihapus';
    } else {
        $_SESSION['error_message'] = 'Materi tidak ditemukan';
    }
    
} catch (PDOException $e) {
    error_log("Delete Material Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan sistem';
}

// Redirect back
$referer = $_SERVER['HTTP_REFERER'] ?? '../admincourses.php';
redirect($referer);
