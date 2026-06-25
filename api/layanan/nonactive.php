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

$id_layanan = $_POST['id_layanan'] ?? null;
if (!$id_layanan) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_layanan wajib diisi']); exit();
}

$stmt = $koneksi->prepare("UPDATE layanan SET is_active = 0, update_at = CURRENT_TIMESTAMP WHERE id_layanan = ?");
$stmt->bind_param("i", $id_layanan);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Layanan berhasil dinonaktifkan']);
    } else {
        http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah nonaktif']);
    }
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
