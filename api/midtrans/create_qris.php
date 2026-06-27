<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit();
}

require_once 'init.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Ambil input JSON atau POST
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$id_booking = $input['id_booking'] ?? null;
$grossAmount = isset($input['jumlah_bayar']) ? (float)$input['jumlah_bayar'] : 0;
$user_id = $_SESSION['user_id'] ?? null;

if (!$id_booking || $grossAmount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data id_booking dan jumlah_bayar tidak valid.']);
    exit();
}

// Generate Order ID (kode_pembayaran)
$kode_pembayaran = 'TRX-' . $id_booking . '-' . time();

$params = [
    'payment_type' => 'qris',
    'transaction_details' => [
        'order_id' => $kode_pembayaran,
        'gross_amount' => $grossAmount,
    ],
];

try {
    // 1. Panggil API Midtrans
    $qrisTransaction = \Midtrans\CoreApi::charge($params);

    // Dapatkan URL QR Code
    $qrisImageUrl = $qrisTransaction->actions[0]->url;
    $qrisTransactionId = $qrisTransaction->transaction_id ?? null;

    // 2. Simpan record 'BELUM_LUNAS' ke tabel pembayaran
    $metode_pembayaran = 'QRIS';
    $status_pembayaran = 'BELUM_LUNAS';
    $tanggal_bayar = date('Y-m-d H:i:s');
    
    $stmt = $koneksi->prepare("INSERT INTO pembayaran (id_booking, user_id, tanggal_bayar, kode_pembayaran, jumlah_bayar, metode_pembayaran, qris_transaction_id, status_pembayaran, qris_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdssss", $id_booking, $user_id, $tanggal_bayar, $kode_pembayaran, $grossAmount, $metode_pembayaran, $qrisTransactionId, $status_pembayaran, $qrisImageUrl);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data transaksi ke database lokal.");
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'qris_url' => $qrisImageUrl,
        'kode_pembayaran' => $kode_pembayaran,
        'message' => 'QRIS transaction created successfully.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
