<?php
session_start();
header('Content-Type: application/json');
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Mendapatkan data dari POST form-data atau JSON payload
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$username = $_POST['username'] ?? ($input['username'] ?? '');
$password = $_POST['password'] ?? ($input['password'] ?? '');

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Username dan password harus diisi'
    ]);
    exit();
}

// Mencari user berdasarkan username
$stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . $koneksi->error
    ]);
    exit();
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password (dicocokkan dengan password_hash di database)
    if (password_verify($password, $user['password'])) {
        
        // Opsional: Cek status aktif (is_active)
        if (isset($user['is_active']) && $user['is_active'] == 0) {
             http_response_code(403);
             echo json_encode([
                 'status' => 'error',
                 'message' => 'Akun Anda tidak aktif'
             ]);
             exit();
        }

        // Update last_login dengan datetime sekarang
        $current_datetime = date('Y-m-d H:i:s');
        $updateStmt = $koneksi->prepare("UPDATE users SET last_login = ? WHERE user_id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("si", $current_datetime, $user['user_id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        // Simpan session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Gunakan password hash yang ada di database sebagai Bearer Token
        $token = $user['password'];
        
        // Hapus password dari response utama, pindahkan ke field token
        unset($user['password']);
        $user['token'] = $token;
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $user
        ]);
    } else {
        // Password salah
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Username atau password salah'
        ]);
    }
} else {
    // Username tidak ditemukan
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Username atau password salah'
    ]);
}

$stmt->close();
$koneksi->close();
?>
