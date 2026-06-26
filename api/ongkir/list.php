<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit();
}

$query = "SELECT * FROM ongkir ORDER BY id_ongkir DESC";
$res = $koneksi->query($query);
$data = [];
if ($res) {
    while($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}
$koneksi->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $data]);
?>
