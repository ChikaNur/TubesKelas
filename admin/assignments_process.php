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
            $mk_id = (int)$_POST['mk_id'];
            $judul = sanitize_input($_POST['judul']);
            $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
            $deadline = $_POST['deadline'];
            $max_score = (int)$_POST['max_score'];
            
            if (empty($mk_id) || empty($judul) || empty($deadline)) {
                throw new Exception('Course, judul, dan deadline wajib diisi');
            }
            
            $stmt = $db->prepare("INSERT INTO tugas (mk_id, judul, deskripsi, deadline, max_score, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$mk_id, $judul, $deskripsi, $deadline, $max_score]);
            
            $_SESSION['success_message'] = 'Assignment berhasil ditambahkan';
            break;
            
        case 'edit':
            $tugas_id = (int)$_POST['tugas_id'];
            $mk_id = (int)$_POST['mk_id'];
            $judul = sanitize_input($_POST['judul']);
            $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
            $deadline = $_POST['deadline'];
            $max_score = (int)$_POST['max_score'];
            
            if (empty($mk_id) || empty($judul) || empty($deadline)) {
                throw new Exception('Course, judul, dan deadline wajib diisi');
            }
            
            $stmt = $db->prepare("UPDATE tugas SET mk_id = ?, judul = ?, deskripsi = ?, deadline = ?, max_score = ? WHERE tugas_id = ?");
            $stmt->execute([$mk_id, $judul, $deskripsi, $deadline, $max_score, $tugas_id]);
            
            $_SESSION['success_message'] = 'Assignment berhasil diupdate';
            break;
            
        case 'delete':
            $tugas_id = (int)$_GET['tugas_id'];
            
            // Delete associated submissions first
            $stmt = $db->prepare("DELETE FROM submissions WHERE tugas_id = ?");
            $stmt->execute([$tugas_id]);
            
            // Then delete the assignment
            $stmt = $db->prepare("DELETE FROM tugas WHERE tugas_id = ?");
            $stmt->execute([$tugas_id]);
            
            $_SESSION['success_message'] = 'Assignment berhasil dihapus';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

redirect('assignments.php');
?>
