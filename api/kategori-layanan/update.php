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

$stmtCheck = $koneksi->prepare("SELECT * FROM kategori_layanan WHERE id_kategori_layanan = ?");
$stmtCheck->bind_param("i", $id_kategori_layanan);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
if ($result->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']); exit();
}
$existing = $result->fetch_assoc();
$stmtCheck->close();

$kode_kategori = $_POST['kode_kategori'] ?? ($input['kode_kategori'] ?? $existing['kode_kategori']);
$nama_kategori = $_POST['nama_kategori'] ?? ($input['nama_kategori'] ?? $existing['nama_kategori']);
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : (isset($input['is_active']) ? $input['is_active'] : $existing['is_active']);
$deskripsi = $_POST['deskripsi'] ?? ($input['deskripsi'] ?? $existing['deskripsi']);

if (empty($kode_kategori) || empty($nama_kategori)) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'kode_kategori dan nama_kategori wajib diisi']); exit();
}

$stmtDup = $koneksi->prepare("SELECT id_kategori_layanan FROM kategori_layanan WHERE kode_kategori = ? AND id_kategori_layanan != ?");
$stmtDup->bind_param("si", $kode_kategori, $id_kategori_layanan);
$stmtDup->execute();
if ($stmtDup->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_kategori sudah digunakan']); exit();
}
$stmtDup->close();

$stmt = $koneksi->prepare("UPDATE kategori_layanan SET kode_kategori = ?, nama_kategori = ?, is_active = ?, deskripsi = ?, updated_at = CURRENT_TIMESTAMP WHERE id_kategori_layanan = ?");
$stmt->bind_param("ssisi", $kode_kategori, $nama_kategori, $is_active, $deskripsi, $id_kategori_layanan);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
