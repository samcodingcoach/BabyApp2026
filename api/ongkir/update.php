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

$id_ongkir = $_POST['id_ongkir'] ?? null;
$dari_kecamatan = $_POST['dari_kecamatan'] ?? null;
$ke_kecamatan = $_POST['ke_kecamatan'] ?? null;
$harga = isset($_POST['harga']) ? (double)$_POST['harga'] : null;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

if (!$id_ongkir || !$dari_kecamatan || !$ke_kecamatan || $harga === null) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']); exit();
}

$stmt = $koneksi->prepare("UPDATE ongkir SET dari_kecamatan = ?, ke_kecamatan = ?, harga = ?, is_active = ? WHERE id_ongkir = ?");
$stmt->bind_param("ssdii", $dari_kecamatan, $ke_kecamatan, $harga, $is_active, $id_ongkir);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Data Ongkir berhasil diperbarui']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
