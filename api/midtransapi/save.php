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

$Merchant_ID = $_POST['Merchant_ID'] ?? null;
$ClientKey = $_POST['ClientKey'] ?? null;
$ServerKey = $_POST['ServerKey'] ?? null;
$update_at = date('Y-m-d H:i:s');

if (!$Merchant_ID || !$ClientKey || !$ServerKey) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
    exit();
}

$stmt = $koneksi->prepare("INSERT INTO midtrans (Merchant_ID, ClientKey, ServerKey, update_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $Merchant_ID, $ClientKey, $ServerKey, $update_at);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data konfigurasi berhasil disimpan.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
