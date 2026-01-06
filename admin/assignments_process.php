<?php
/**
 * Assignments CRUD Process Handler (Admin)
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
            $mk_id = (int)$_POST['mk_id'];
            $judul = sanitize_input($_POST['judul']);
            $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
            $deadline = $_POST['deadline'];
            $max_score = (int)($_POST['max_score'] ?? 100);
            
            $stmt = $db->prepare("
                INSERT INTO tugas (mk_id, judul, deskripsi, deadline, max_score)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$mk_id, $judul, $deskripsi, $deadline, $max_score]);
            
            $_SESSION['success_message'] = "Assignment berhasil ditambahkan";
            break;
            
        case 'delete':
            $tugas_id = (int)$_GET['tugas_id'];
            
            // Check if there are submissions
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions WHERE tugas_id = ?");
            $stmt->execute([$tugas_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                $_SESSION['error_message'] = "Tidak dapat menghapus assignment yang sudah memiliki submission";
                redirect('assignments.php');
            }
            
            $stmt = $db->prepare("DELETE FROM tugas WHERE tugas_id = ?");
            $stmt->execute([$tugas_id]);
            
            $_SESSION['success_message'] = "Assignment berhasil dihapus";
            break;
            
        default:
            $_SESSION['error_message'] = "Aksi tidak valid";
            break;
    }
} catch (PDOException $e) {
    error_log("Assignment CRUD Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan sistem";
}

redirect('assignments.php');
