<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); exit();
}

require_once '../../config/koneksi.php';

// Ambil baris pertama profil usaha
$stmt = $koneksi->prepare("SELECT * FROM profile_usaha LIMIT 1");
$stmt->execute();
$res = $stmt->get_result();

$data = null;
if ($res->num_rows > 0) {
    $data = $res->fetch_assoc();
}
$stmt->close();
$koneksi->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $data]);
?>
