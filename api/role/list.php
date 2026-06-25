<?php
session_start();
header('Content-Type: application/json');

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Gunakan method GET.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek Otorisasi (Session Browser ATAU Bearer Token)
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau token telah berakhir.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

// Ambil data roles
$query = "SELECT role_id, role_name, description FROM roles ORDER BY role_id ASC";
$result = $koneksi->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . $koneksi->error,
        'data' => [],
        'count' => 0
    ]);
    exit();
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$koneksi->close();

// Output JSON
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data roles berhasil diambil',
    'data' => $data,
    'count' => count($data)
]);
?>
