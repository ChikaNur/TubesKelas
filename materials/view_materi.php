<?php
/**
 * View Course Material or Submission File  (BLOB)
 * Retrieves and displays file from database
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(403);
    die('Unauthorized');
}

$db = getDB();
$type = $_GET['type'] ?? 'material';  // 'material' or 'submission'
$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    http_response_code(400);
    die('ID required');
}

try {
    if ($type === 'submission') {
        // Handle submission download
        $stmt = $db->prepare("
            SELECT s.file, s.nama_file, s.ukuran_file, s.tipe_file, s.user_id, t.mk_id
            FROM submissions s
            INNER JOIN tugas t ON s.tugas_id = t.tugas_id
            WHERE s.submission_id = ?
        ");
        $stmt->execute([$id]);
        $submission = $stmt->fetch();
        
        if (!$submission) {
            http_response_code(404);
            die('Submission not found');
        }
        
        // Check permissions: admin can view all, students can only view their own
        if (!is_admin()) {
            $user_id = get_user_info('user_id');
            if ($submission['user_id'] != $user_id) {
                http_response_code(403);
                die('Access denied');
            }
        }
        
        // Set headers and output file
        header('Content-Type: ' . $submission['tipe_file']);
        header('Content-Disposition: attachment; filename="' . $submission['nama_file'] . '"');
        header('Content-Length: ' . $submission['ukuran_file']);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        echo $submission['file'];
        exit;
        
    } else {
        // Handle material download (original code)
        $stmt = $db->prepare("
            SELECT m.*, mk.nama_mk
            FROM materi m
            INNER JOIN mata_kuliah mk ON m.mk_id = mk.mk_id
            WHERE m.materi_id = ?
        ");
        $stmt->execute([$id]);
        $material = $stmt->fetch();
        
        if (!$material) {
            http_response_code(404);
            die('Material not found');
        }
        
        // If not admin, check if user is enrolled in the course
        if (!is_admin()) {
            $user_id = get_user_info('user_id');
            $stmt = $db->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND mk_id = ?");
            $stmt->execute([$user_id, $material['mk_id']]);
            
            if (!$stmt->fetch()) {
                http_response_code(403);
                die('You are not enrolled in this course');
            }
        }
        
        // Set appropriate headers to display the file
        header('Content-Type: ' . $material['tipe_file']);
        header('Content-Disposition: inline; filename="' . $material['nama_file'] . '"');
        header('Content-Length: ' . $material['ukuran_file']);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output the file
        echo $material['file'];
        exit;
    }
    
} catch (PDOException $e) {
    error_log("View File Error: " . $e->getMessage());
    http_response_code(500);
    die('Database error');
}

