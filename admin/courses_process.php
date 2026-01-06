<?php
/**
 * Courses CRUD Process Handler
 * Handles create, update, and delete operations for courses
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$db = getDB();
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Add new course
            $kode_mk = sanitize_input($_POST['kode_mk']);
            $nama_mk = sanitize_input($_POST['nama_mk']);
            $deskripsi = sanitize_input($_POST['deskripsi']);
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $dosen_id = !empty($_POST['dosen_id']) ? (int)$_POST['dosen_id'] : null;
            $status = $_POST['status'];
            
            // Check if course code already exists
            $stmt = $db->prepare("SELECT mk_id FROM mata_kuliah WHERE kode_mk = ?");
            $stmt->execute([$kode_mk]);
            
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "Kode mata kuliah sudah digunakan";
                redirect('../admin/courses.php');
            }
            
            $stmt = $db->prepare("
                INSERT INTO mata_kuliah (kode_mk, nama_mk, deskripsi, sks, semester, dosen_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$kode_mk, $nama_mk, $deskripsi, $sks, $semester, $dosen_id, $status]);
            
            $_SESSION['success_message'] = "Mata kuliah berhasil ditambahkan";
            break;
            
        case 'edit':
            // Update existing course
            $mk_id = (int)$_POST['mk_id'];
            $kode_mk = sanitize_input($_POST['kode_mk']);
            $nama_mk = sanitize_input($_POST['nama_mk']);
            $deskripsi = sanitize_input($_POST['deskripsi']);
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $dosen_id = !empty($_POST['dosen_id']) ? (int)$_POST['dosen_id'] : null;
            $status = $_POST['status'];
            
            // Check if course code is used by another course
            $stmt = $db->prepare("SELECT mk_id FROM mata_kuliah WHERE kode_mk = ? AND mk_id != ?");
            $stmt->execute([$kode_mk, $mk_id]);
            
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "Kode mata kuliah sudah digunakan";
                redirect('../admin/courses.php');
            }
            
            $stmt = $db->prepare("
                UPDATE mata_kuliah 
                SET kode_mk = ?, nama_mk = ?, deskripsi = ?, sks = ?, semester = ?, dosen_id = ?, status = ?
                WHERE mk_id = ?
            ");
            $stmt->execute([$kode_mk, $nama_mk, $deskripsi, $sks, $semester, $dosen_id, $status, $mk_id]);
            
            $_SESSION['success_message'] = "Mata kuliah berhasil diperbarui";
            break;
            
        case 'delete':
            // Delete course
            $mk_id = (int)$_GET['mk_id'];
            
            // Check if course has enrollments
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM enrollments WHERE mk_id = ?");
            $stmt->execute([$mk_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count > 0) {
                $_SESSION['error_message'] = "Tidak dapat menghapus mata kuliah yang memiliki mahasiswa terdaftar";
                redirect('../admin/courses.php');
            }
            
            $stmt = $db->prepare("DELETE FROM mata_kuliah WHERE mk_id = ?");
            $stmt->execute([$mk_id]);
            
            $_SESSION['success_message'] = "Mata kuliah berhasil dihapus";
            break;
            
        default:
            $_SESSION['error_message'] = "Aksi tidak valid";
            break;
    }
} catch (PDOException $e) {
    error_log("Course CRUD Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan sistem: " . $e->getMessage();
}

redirect('../admin/courses.php');
