<?php
session_start();
header('Content-Type: application/json');

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Gunakan method POST.'
    ]);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek Otorisasi (Session atau Bearer)
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau token tidak valid.'
    ]);
    exit();
}

// Parsing Input (Bisa dari Form-Data atau RAW JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE) ?? [];

// Ambil user_id
$user_id = $_POST['user_id'] ?? ($input['user_id'] ?? null);

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter user_id wajib disertakan.']);
    exit();
}

// Eksekusi Update is_active = 0 (Soft Delete)
$updateQuery = "UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
$stmtUpdate = $koneksi->prepare($updateQuery);

if (!$stmtUpdate) {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Error update query: ' . $koneksi->error]); 
    exit();
}

$stmtUpdate->bind_param("i", $user_id);

if (!$stmtUpdate->execute()) {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Gagal menonaktifkan user: ' . $stmtUpdate->error]); 
    exit();
}

$affected_rows = $stmtUpdate->affected_rows;
$stmtUpdate->close();
$koneksi->close();

if ($affected_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Data user tidak ditemukan atau user sudah dalam status tidak aktif.'
    ]);
    exit();
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'User berhasil dinonaktifkan (is_active = 0)',
    'data' => [
        'user_id' => $user_id
    ]
]);
?>
