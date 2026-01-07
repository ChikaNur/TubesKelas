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
            $kode_mk = sanitize_input($_POST['kode_mk']);
            $nama_mk = sanitize_input($_POST['nama_mk']);
            $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $dosen_id = !empty($_POST['dosen_id']) ? (int)$_POST['dosen_id'] : null;
            $status = $_POST['status'];
            
            if (empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($semester)) {
                throw new Exception('Kode MK, Nama MK, SKS, dan Semester wajib diisi');
            }
            
            // Check if course code already exists
            $stmt = $db->prepare("SELECT mk_id FROM mata_kuliah WHERE kode_mk = ?");
            $stmt->execute([$kode_mk]);
            if ($stmt->fetch()) {
                throw new Exception('Kode mata kuliah sudah ada');
            }
            
            $stmt = $db->prepare("INSERT INTO mata_kuliah (kode_mk, nama_mk, deskripsi, sks, semester, dosen_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$kode_mk, $nama_mk, $deskripsi, $sks, $semester, $dosen_id, $status]);
            
            $_SESSION['success_message'] = 'Mata kuliah berhasil ditambahkan';
            break;
            
        case 'edit':
            $mk_id = (int)$_POST['mk_id'];
            $kode_mk = sanitize_input($_POST['kode_mk']);
            $nama_mk = sanitize_input($_POST['nama_mk']);
            $deskripsi = sanitize_input($_POST['deskripsi'] ?? '');
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $dosen_id = !empty($_POST['dosen_id']) ? (int)$_POST['dosen_id'] : null;
            $status = $_POST['status'];
            
            if (empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($semester)) {
                throw new Exception('Kode MK, Nama MK, SKS, dan Semester wajib diisi');
            }
            
            // Check if course code is taken by another course
            $stmt = $db->prepare("SELECT mk_id FROM mata_kuliah WHERE kode_mk = ? AND mk_id != ?");
            $stmt->execute([$kode_mk, $mk_id]);
            if ($stmt->fetch()) {
                throw new Exception('Kode mata kuliah sudah digunakan course lain');
            }
            
            $stmt = $db->prepare("UPDATE mata_kuliah SET kode_mk = ?, nama_mk = ?, deskripsi = ?, sks = ?, semester = ?, dosen_id = ?, status = ? WHERE mk_id = ?");
            $stmt->execute([$kode_mk, $nama_mk, $deskripsi, $sks, $semester, $dosen_id, $status, $mk_id]);
            
            $_SESSION['success_message'] = 'Mata kuliah berhasil diupdate';
            break;
            
        case 'delete':
            $mk_id = (int)$_GET['mk_id'];
            
            // Check if course has enrollments
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM enrollments WHERE mk_id = ?");
            $stmt->execute([$mk_id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Tidak dapat menghapus mata kuliah yang memiliki mahasiswa terdaftar');
            }
            
            $stmt = $db->prepare("DELETE FROM mata_kuliah WHERE mk_id = ?");
            $stmt->execute([$mk_id]);
            
            $_SESSION['success_message'] = 'Mata kuliah berhasil dihapus';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

redirect('courses.php');
?>
