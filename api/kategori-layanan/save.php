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
$kode_kategori = $_POST['kode_kategori'] ?? ($input['kode_kategori'] ?? '');
$nama_kategori = $_POST['nama_kategori'] ?? ($input['nama_kategori'] ?? '');
$is_active = $_POST['is_active'] ?? ($input['is_active'] ?? 1);
$deskripsi = $_POST['deskripsi'] ?? ($input['deskripsi'] ?? '');

if (empty($kode_kategori) || empty($nama_kategori)) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'kode_kategori dan nama_kategori wajib diisi']); exit();
}

$stmtCheck = $koneksi->prepare("SELECT id_kategori_layanan FROM kategori_layanan WHERE kode_kategori = ?");
$stmtCheck->bind_param("s", $kode_kategori);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_kategori sudah digunakan']); exit();
}
$stmtCheck->close();

$stmt = $koneksi->prepare("INSERT INTO kategori_layanan (kode_kategori, nama_kategori, is_active, deskripsi) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $kode_kategori, $nama_kategori, $is_active, $deskripsi);
if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan', 'data' => ['id_kategori_layanan' => $stmt->insert_id]]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
