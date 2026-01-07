<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple token validation (dalam produksi gunakan JWT)
function validateToken() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit();
    }
    
    // Dalam produksi, validasi token yang sebenarnya
    return true;
}

// GET user profile
if ($method == 'GET') {
    validateToken();
    
    $db = getDB();
    
    if (isset($_GET['id'])) {
        // Get specific user
        $query = "SELECT id, nim, nama_lengkap, email, role, created_at FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_GET['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }
    } else {
        // Get all users (for admin)
        $query = "SELECT id, nim, nama_lengkap, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'count' => count($users)
        ]);
    }
}

// POST - Create new user (register)
else if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->nim) || !isset($data->nama_lengkap) || !isset($data->email) || !isset($data->password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap'
        ]);
        exit();
    }
    
    $db = getDB();
    
    try {
        // Check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":email", $data->email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ]);
            exit();
        }
        
        // Hash password
        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (nim, nama_lengkap, email, password, role) 
                  VALUES (:nim, :nama_lengkap, :email, :password, :role)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":nim", $data->nim);
        $stmt->bindParam(":nama_lengkap", $data->nama_lengkap);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $hashedPassword);
        $role = isset($data->role) ? $data->role : 'mahasiswa';
        $stmt->bindParam(":role", $role);
        
        if ($stmt->execute()) {
            $lastId = $db->lastInsertId();
            
            // Get the created user
            $getQuery = "SELECT id, nim, nama_lengkap, email, role, created_at FROM users WHERE id = :id";
            $getStmt = $db->prepare($getQuery);
            $getStmt->bindParam(":id", $lastId);
            $getStmt->execute();
            $newUser = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => $newUser
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mendaftar'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
}
?>