<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight request
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Helper function untuk response JSON
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Handle POST request untuk login
if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->email) || !isset($data->password) || !isset($data->role)) {
        jsonResponse(false, "Email, password, dan role harus diisi", null, 400);
    }
    
    $email = $data->email;
    $password = $data->password;
    $role = $data->role;
    
    $db = getDB();
    
    try {
        // Login untuk user/mahasiswa/dosen
        if ($role == 'user') {
            $query = "SELECT id, nim, nama_lengkap, email, password, role FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (password_hash di database)
                if (password_verify($password, $user['password'])) {
                    // Remove password from response
                    unset($user['password']);
                    
                    // Generate token sederhana (dalam produksi gunakan JWT)
                    $token = bin2hex(random_bytes(32));
                    
                    jsonResponse(true, "Login berhasil", [
                        'user' => $user,
                        'token' => $token
                    ]);
                } else {
                    jsonResponse(false, "Password salah", null, 401);
                }
            } else {
                jsonResponse(false, "Email tidak ditemukan", null, 404);
            }
        }
        // Login untuk admin
        else if ($role == 'admin') {
            $query = "SELECT id, username, email, full_name, role FROM admins WHERE email = :email OR username = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get password untuk verifikasi
                $queryPass = "SELECT password FROM admins WHERE email = :email OR username = :email";
                $stmtPass = $db->prepare($queryPass);
                $stmtPass->bindParam(":email", $email);
                $stmtPass->execute();
                $adminWithPass = $stmtPass->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $adminWithPass['password'])) {
                    // Update last login
                    $updateQuery = "UPDATE admins SET last_login = NOW() WHERE id = :id";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(":id", $admin['id']);
                    $updateStmt->execute();
                    
                    // Generate token
                    $token = bin2hex(random_bytes(32));
                    
                    jsonResponse(true, "Login admin berhasil", [
                        'admin' => $admin,
                        'token' => $token
                    ]);
                } else {
                    jsonResponse(false, "Password admin salah", null, 401);
                }
            } else {
                jsonResponse(false, "Admin tidak ditemukan", null, 404);
            }
        }
        
    } catch(PDOException $e) {
        jsonResponse(false, "Database error: " . $e->getMessage(), null, 500);
    }
}

// Handle GET request untuk check session
else if ($method == 'GET') {
    if (isset($_GET['token'])) {
        // Check token validity (sederhana)
        jsonResponse(true, "Session valid", ['valid' => true]);
    } else {
        jsonResponse(false, "Token tidak ditemukan", null, 401);
    }
}

else {
    jsonResponse(false, "Method tidak diizinkan", null, 405);
}
?>