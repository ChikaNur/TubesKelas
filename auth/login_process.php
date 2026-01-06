<?php
/**
 * Login Process Handler
 * Handles user authentication
 */

require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email dan password harus diisi';
    } elseif (!is_valid_email($email)) {
        $response['message'] = 'Format email tidak valid';
    } else {
        try {
            $db = getDB();
            
            // Query user by email
            $stmt = $db->prepare("SELECT user_id, nama, nim, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Password correct, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['nim'] = $user['nim'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $response['success'] = true;
                $response['message'] = 'Login berhasil';
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    $response['redirect'] = '../admin/dashboard.php';
                } else {
                    $response['redirect'] = '../student/dashboard.php';
                }
            } else {
                $response['message'] = 'Email atau password salah';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
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
        $_SESSION['login_error'] = $response['message'];
        redirect('../login.php');
    }
}
