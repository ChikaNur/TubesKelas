<?php
/**
 * Upload Course Material (BLOB)
 * Handles file upload and stores it as BLOB in database
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $mk_id = (int)$_POST['mk_id'];
    $judul = sanitize_input($_POST['judul']);
    $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
    
    if (empty($mk_id) || empty($judul)) {
        $response['message'] = 'Mata kuliah dan judul harus diisi';
        echo json_encode($response);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File tidak ditemukan atau terjadi kesalahan upload';
        echo json_encode($response);
        exit;
    }
    
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];
    
    // Validate file size (max 10MB)
    if ($file_size > 10485760) {
        $response['message'] = 'Ukuran file maksimal 10MB';
        echo json_encode($response);
        exit;
    }
    
    // Validate file type
    $allowed_types = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'video/mp4',
        'video/mpeg',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];
    
    if (!in_array($file_type, $allowed_types)) {
        $response['message'] = 'Tipe file tidak diizinkan. Gunakan PDF, gambar, video, Word, atau PowerPoint';
        echo json_encode($response);
        exit;
    }
    
    try {
        $db = getDB();
        
        // Verify course exists
        $stmt = $db->prepare("SELECT mk_id FROM mata_kuliah WHERE mk_id = ?");
        $stmt->execute([$mk_id]);
        if (!$stmt->fetch()) {
            $response['message'] = 'Mata kuliah tidak ditemukan';
            echo json_encode($response);
            exit;
        }
        
        // Read file content
        $file_content = file_get_contents($file_tmp);
        
        // Insert into database
        $stmt = $db->prepare("
            INSERT INTO materi (mk_id, judul, deskripsi, tipe_file, nama_file, file, ukuran_file)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $mk_id,
            $judul,
            $deskripsi,
            $file_type,
            $file_name,
            $file_content,
            $file_size
        ]);
        
        $response['success'] = true;
        $response['message'] = 'Materi berhasil diupload';
        $response['materi_id'] = $db->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("Upload Material Error: " . $e->getMessage());
        $response['message'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
