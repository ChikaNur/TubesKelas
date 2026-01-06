<?php
/**
 * Grade Submission Handler
 * Updates score for a student submission
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$submission_id = (int)($_GET['submission_id'] ?? 0);
$score = (int)($_GET['score'] ?? 0);

if ($submission_id === 0) {
    $_SESSION['error_message'] = 'Invalid submission ID';
    redirect('assignments.php');
}

try {
    $db = getDB();
    
    // Get assignment max score
    $stmt = $db->prepare("
        SELECT t.max_score, s.tugas_id
        FROM submissions s
        INNER JOIN tugas t ON s.tugas_id = t.tugas_id
        WHERE s.submission_id = ?
    ");
    $stmt->execute([$submission_id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        $_SESSION['error_message'] = 'Submission not found';
        redirect('assignments.php');
    }
    
    if ($score > $data['max_score']) {
        $_SESSION['error_message'] = "Score cannot exceed max score ({$data['max_score']})";
        redirect("view_submissions.php?tugas_id={$data['tugas_id']}");
    }
    
    // Update score
    $stmt = $db->prepare("
        UPDATE submissions 
        SET score = ?, graded_at = NOW()
        WHERE submission_id = ?
    ");
    $stmt->execute([$score, $submission_id]);
    
    $_SESSION['success_message'] = 'Score updated successfully';
    redirect("view_submissions.php?tugas_id={$data['tugas_id']}");
    
} catch (PDOException $e) {
    error_log("Grade Submission Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database error occurred';
    redirect('assignments.php');
}
