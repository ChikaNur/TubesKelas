<?php
/**
 * Submit Assignment Handler
 * Handles file upload for assignment submissions
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || is_admin()) {
    redirect('../login.php');
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tugas_id = (int)$_POST['tugas_id'];
    $user_id = get_user_info('user_id');
    
    // Validate input
    if (empty($tugas_id)) {
        $_SESSION['error_message'] = 'Tugas tidak valid';
        redirect('assignments.php');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = 'File tidak ditemukan atau terjadi kesalahan upload';
        redirect('assignments.php');
    }
    
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];
    
    // Validate file size (max 5MB)
    if ($file_size > 5242880) {
        $_SESSION['error_message'] = 'Ukuran file maksimal 5MB';
        redirect('assignments.php');
    }
    
    // Validate file type
    $allowed_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-zip-compressed'
    ];
    
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];
    
    if (!in_array($ext, $allowed_ext)) {
        $_SESSION['error_message'] = 'Tipe file tidak diizinkan. Gunakan PDF, DOC, DOCX, ZIP, atau RAR';
        redirect('assignments.php');
    }
    
    try {
        $db = getDB();
        
        // Verify assignment exists and user is enrolled
        $stmt = $db->prepare("
            SELECT t.tugas_id, t.deadline
            FROM tugas t
            INNER JOIN enrollments e ON t.mk_id = e.mk_id
            WHERE t.tugas_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$tugas_id, $user_id]);
        $assignment = $stmt->fetch();
        
        if (!$assignment) {
            $_SESSION['error_message'] = 'Tugas tidak ditemukan atau Anda tidak terdaftar di mata kuliah ini';
            redirect('assignments.php');
        }
        
        // Check if already submitted
        $stmt = $db->prepare("SELECT submission_id FROM submissions WHERE tugas_id = ? AND user_id = ?");
        $stmt->execute([$tugas_id, $user_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Anda sudah mengumpulkan tugas ini';
            redirect('assignments.php');
        }
        
        // Read file content
        $file_content = file_get_contents($file_tmp);
        
        // Determine if late
        $is_late = strtotime($assignment['deadline']) < time() ? 1 : 0;
        
        // Insert submission
        $stmt = $db->prepare("
            INSERT INTO submissions (tugas_id, user_id, file, nama_file, ukuran_file, tipe_file, is_late)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $tugas_id,
            $user_id,
            $file_content,
            $file_name,
            $file_size,
            $file_type,
            $is_late
        ]);
        
        if ($is_late) {
            $_SESSION['warning_message'] = 'Tugas berhasil dikumpulkan, tetapi terlambat';
        } else {
            $_SESSION['success_message'] = 'Tugas berhasil dikumpulkan';
        }
        
    } catch (PDOException $e) {
        error_log("Submit Assignment Error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    }
}

redirect('assignments.php');
