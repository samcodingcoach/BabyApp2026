<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit();
}

$id_booking = $_POST['id_booking'] ?? null;
$status_booking = $_POST['status_booking'] ?? null; // 'MENUNGGU','DIJADWALKAN','DIKONFIRMASI','SELESAI','BATAL'

if (!$id_booking || !$status_booking) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_booking dan status_booking wajib diisi']); exit();
}

$allowedStatus = ['MENUNGGU','DIJADWALKAN','DIKONFIRMASI','SELESAI','BATAL'];
if (!in_array($status_booking, $allowedStatus)) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']); exit();
}

$stmt = $koneksi->prepare("UPDATE booking SET status_booking = ? WHERE id_booking = ?");
$stmt->bind_param("si", $status_booking, $id_booking);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Status booking berhasil diubah menjadi ' . $status_booking]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
