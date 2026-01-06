<?php
/**
 * View Course Material (BLOB)
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

// Get material ID
$materi_id = (int)($_GET['id'] ?? 0);

if ($materi_id === 0) {
    http_response_code(400);
    die('Material ID required');
}

try {
    $db = getDB();
    
    // Get material from database
    $stmt = $db->prepare("
        SELECT m.*, mk.nama_mk
        FROM materi m
        INNER JOIN mata_kuliah mk ON m.mk_id = mk.mk_id
        WHERE m.materi_id = ?
    ");
    $stmt->execute([$materi_id]);
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
    
} catch (PDOException $e) {
    error_log("View Material Error: " . $e->getMessage());
    http_response_code(500);
    die('Database error');
}
