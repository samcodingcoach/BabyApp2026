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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$id_booking = $input['id_booking'] ?? null;
$grossAmount = isset($input['jumlah_bayar']) ? (float)$input['jumlah_bayar'] : 0;
$bank = $input['bank'] ?? 'bca'; // default bca
$user_id = $_SESSION['user_id'] ?? null;

if (!$id_booking || $grossAmount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data id_booking dan jumlah_bayar tidak valid.']);
    exit();
}

// Generate Order ID (kode_pembayaran)
$kode_pembayaran = 'TRX-' . $id_booking . '-' . time();

$params = [
    'payment_type' => 'bank_transfer',
    'transaction_details' => [
        'order_id' => $kode_pembayaran,
        'gross_amount' => $grossAmount,
    ],
    'bank_transfer' => [
        'bank' => strtolower($bank)
    ]
];

try {
    // 1. Panggil API Midtrans
    $response = \Midtrans\CoreApi::charge($params);

    $vaNumber = null;
    $bankResponse = null;

    if (!empty($response->va_numbers)) {
        $vaNumber = $response->va_numbers[0]->va_number ?? null;
        $bankResponse = $response->va_numbers[0]->bank ?? null;
    }

    $transactionId = $response->transaction_id ?? null;

    // 2. Simpan record 'BELUM_LUNAS' ke tabel pembayaran
    $metode_pembayaran = 'VA ' . strtoupper($bankResponse);
    $status_pembayaran = 'BELUM_LUNAS';
    $tanggal_bayar = date('Y-m-d H:i:s');
    
    $stmt = $koneksi->prepare("INSERT INTO pembayaran (id_booking, user_id, tanggal_bayar, kode_pembayaran, jumlah_bayar, metode_pembayaran, qris_transaction_id, status_pembayaran, va_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdssss", $id_booking, $user_id, $tanggal_bayar, $kode_pembayaran, $grossAmount, $metode_pembayaran, $transactionId, $status_pembayaran, $vaNumber);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data transaksi VA ke database lokal.");
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Virtual Account berhasil dibuat',
        'data' => [
            'transaction_id' => $transactionId,
            'kode_pembayaran' => $kode_pembayaran,
            'va_number' => $vaNumber,
            'bank' => $bankResponse
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
