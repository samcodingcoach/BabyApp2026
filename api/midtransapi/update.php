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

$id_midtrans = $_POST['id_midtrans'] ?? null;
$Merchant_ID = $_POST['Merchant_ID'] ?? null;
$ClientKey = $_POST['ClientKey'] ?? null;
$ServerKey = $_POST['ServerKey'] ?? null;
$update_at = date('Y-m-d H:i:s');

if (!$id_midtrans || !$Merchant_ID || !$ClientKey || !$ServerKey) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
    exit();
}

$stmt = $koneksi->prepare("UPDATE midtrans SET Merchant_ID = ?, ClientKey = ?, ServerKey = ?, update_at = ? WHERE id_midtrans = ?");
$stmt->bind_param("ssssi", $Merchant_ID, $ClientKey, $ServerKey, $update_at, $id_midtrans);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data konfigurasi berhasil diupdate.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data: ' . $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
