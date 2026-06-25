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

$input = json_decode(file_get_contents('php://input'), TRUE) ?? [];
$id_kategori_layanan = $_POST['id_kategori_layanan'] ?? ($input['id_kategori_layanan'] ?? null);

if (!$id_kategori_layanan) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_kategori_layanan wajib diisi']); exit();
}

$stmt = $koneksi->prepare("UPDATE kategori_layanan SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id_kategori_layanan = ?");
$stmt->bind_param("i", $id_kategori_layanan);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Kategori dinonaktifkan']);
    } else {
        http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah nonaktif']);
    }
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
