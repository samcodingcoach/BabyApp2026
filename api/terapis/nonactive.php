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

$id_terapis = $_POST['id_terapis'] ?? null;
if (!$id_terapis) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_terapis wajib diisi']); exit();
}

$stmt = $koneksi->prepare("UPDATE terapis SET is_active = 0, update_at = CURRENT_TIMESTAMP WHERE id_terapis = ?");
$stmt->bind_param("i", $id_terapis);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Terapis berhasil dinonaktifkan']);
    } else {
        http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah nonaktif']);
    }
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
