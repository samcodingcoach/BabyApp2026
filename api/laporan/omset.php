<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit();
}

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$id_terapis = $_GET['id_terapis'] ?? null;

if (!$start_date || !$end_date) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'start_date dan end_date wajib diisi (YYYY-MM-DD)']); exit();
}

$where = "pembayaran.status_pembayaran = 'LUNAS' AND DATE(pembayaran.tanggal_bayar) >= ? AND DATE(pembayaran.tanggal_bayar) <= ?";
$params = [$start_date, $end_date];
$types = "ss";

$sql = "
    SELECT
        pembayaran.id_pembayaran, 
        pembayaran.id_booking, 
        pembayaran.tanggal_bayar, 
        pembayaran.kode_pembayaran, 
        pembayaran.metode_pembayaran, 
        pembayaran.status_pembayaran, 
        pembayaran.jumlah_omset
    FROM
        pembayaran
        INNER JOIN
        booking
        ON 
            pembayaran.id_booking = booking.id_booking
    WHERE $where
    ORDER BY pembayaran.tanggal_bayar DESC
";

$stmt = $koneksi->prepare($sql);
if(!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $koneksi->error]); exit();
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$total_omset = 0;

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_omset += (float)$row['jumlah_omset'];
}

echo json_encode([
    'status' => 'success',
    'summary' => [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'id_terapis' => $id_terapis,
        'total_transaksi' => count($data),
        'total_omset' => $total_omset
    ],
    'data' => $data
]);
