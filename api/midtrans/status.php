<?php
header('Content-Type: application/json');
require_once 'init.php';

$kode_pembayaran = $_GET['order_id'] ?? '';

if (empty(trim($kode_pembayaran))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter order_id is required']);
    exit;
}

try {
    // 1. Panggil API status Midtrans dengan native method
    $statusResponse = \Midtrans\Transaction::status($kode_pembayaran);

    // 2. Format response sama seperti sebelumnya
    $filteredData = [
        'order_id' => $statusResponse->order_id ?? null,
        'gross_amount' => $statusResponse->gross_amount ?? null,
        'transaction_status' => $statusResponse->transaction_status ?? null,
        'transaction_id' => $statusResponse->transaction_id ?? null,
        'acquirer' => $statusResponse->acquirer ?? null,
        'settlement_time' => $statusResponse->settlement_time ?? null,
    ];
    
    echo json_encode([$filteredData]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
