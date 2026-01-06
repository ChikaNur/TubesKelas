<?php
/**
 * Registration Process Handler
 * Handles new user registration
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $nama = sanitize_input($_POST['fullName'] ?? '');
    $nim = sanitize_input($_POST['nim'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate input
    if (empty($nama) || empty($nim) || empty($email) || empty($password)) {
        $response['message'] = 'Semua field harus diisi';
    } elseif (!is_valid_email($email)) {
        $response['message'] = 'Format email tidak valid';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password minimal 6 karakter';
    } elseif ($password !== $confirmPassword) {
        $response['message'] = 'Password dan konfirmasi password tidak cocok';
    } else {
        try {
            $db = getDB();
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $response['message'] = 'Email sudah terdaftar';
            } else {
                // Check if NIM already exists
                $stmt = $db->prepare("SELECT user_id FROM users WHERE nim = ?");
                $stmt->execute([$nim]);
                
                if ($stmt->fetch()) {
                    $response['message'] = 'NIM sudah terdaftar';
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Insert new user
                    $stmt = $db->prepare("
                        INSERT INTO users (nama, nim, email, password, role) 
                        VALUES (?, ?, ?, ?, 'mahasiswa')
                    ");
                    
                    if ($stmt->execute([$nama, $nim, $email, $hashedPassword])) {
                        // Get the new user ID
                        $userId = $db->lastInsertId();
                        
                        // Auto-login after registration
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['nama'] = $nama;
                        $_SESSION['nim'] = $nim;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'mahasiswa';
                        
                        $response['success'] = true;
                        $response['message'] = 'Registrasi berhasil';
                        $response['redirect'] = '../student/dashboard.php';
                    } else {
                        $response['message'] = 'Gagal mendaftarkan user';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $response['message'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

// Return JSON response for AJAX, or redirect for traditional form
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        redirect($response['redirect']);
    } else {
        $_SESSION['register_error'] = $response['message'];
        redirect('../signup.php');
    }
}
