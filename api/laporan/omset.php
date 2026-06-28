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

if ($id_terapis) {
    $where .= " AND booking.id_terapis = ?";
    $params[] = $id_terapis;
    $types .= "i";
}

$sql = "
    SELECT 
        booking.id_booking, 
        booking.kode_booking, 
        pembayaran.kode_pembayaran,
        booking.tanggal_booking,
        pembayaran.tanggal_bayar,
        pembayaran.jumlah_bayar,
        pembayaran.metode_pembayaran,
        terapis.id_terapis,
        terapis.nama_terapis,
        member.nama as nama_pasien
    FROM booking
    JOIN pembayaran ON booking.id_booking = pembayaran.id_booking
    LEFT JOIN terapis ON booking.id_terapis = terapis.id_terapis
    LEFT JOIN member ON booking.id_member = member.id_member
    WHERE $where
    ORDER BY pembayaran.tanggal_bayar DESC
";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$total_omset = 0;

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_omset += $row['jumlah_bayar']; // Atau grand_total, asumsikan pembayaran = grand_total jika lunas
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
