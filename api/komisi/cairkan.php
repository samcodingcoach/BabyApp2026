<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id_komisi = $_POST['id_komisi'] ?? null;

if (!$id_komisi) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Komisi tidak valid']);
    exit();
}

$tanggal_pencairan = date('Y-m-d H:i:s');

$stmt = $koneksi->prepare("UPDATE komisi_terapis SET status_pencairan = 'SUDAH_CAIR', tanggal_pencairan = ? WHERE id_komisi = ?");
$stmt->bind_param("si", $tanggal_pencairan, $id_komisi);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Komisi berhasil dicairkan!']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mencairkan komisi.']);
}

$stmt->close();
$koneksi->close();
?>
