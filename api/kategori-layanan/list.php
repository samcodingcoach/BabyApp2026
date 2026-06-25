<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed', 'data' => [], 'count' => 0]);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized', 'data' => [], 'count' => 0]);
    exit();
}

$query = "SELECT * FROM kategori_layanan ORDER BY id_kategori_layanan DESC";
$result = $koneksi->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data kategori berhasil diambil',
    'data' => $data,
    'count' => count($data)
]);
?>
