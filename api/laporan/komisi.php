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

$where = "DATE(komisi_terapis.created_at) >= ? AND DATE(komisi_terapis.created_at) <= ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($id_terapis) {
    $where .= " AND komisi_terapis.id_terapis = ?";
    $params[] = $id_terapis;
    $types .= "i";
}

$sql = "
    SELECT
        komisi_terapis.id_komisi, 
        komisi_terapis.id_booking, 
        komisi_terapis.id_terapis, 
        terapis.nama_terapis, 
        komisi_terapis.nominal_komisi, 
        komisi_terapis.status_pencairan, 
        komisi_terapis.tanggal_pencairan, 
        komisi_terapis.created_at, 
        pembayaran.kode_pembayaran,
        booking.tarif_ongkir
    FROM
        komisi_terapis
        INNER JOIN
        terapis
        ON 
            komisi_terapis.id_terapis = terapis.id_terapis
        INNER JOIN
        booking
        ON 
            komisi_terapis.id_booking = booking.id_booking
        INNER JOIN
        pembayaran
        ON 
            booking.id_booking = pembayaran.id_booking
    WHERE $where
    ORDER BY komisi_terapis.created_at DESC
";

$stmt = $koneksi->prepare($sql);
if(!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $koneksi->error]); exit();
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$total_komisi = 0;
$total_transaksi = 0;

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    if ($row['status_pencairan'] === 'SUDAH_CAIR') {
        $total_komisi += (float)$row['nominal_komisi'];
        $total_transaksi++;
    }
}

echo json_encode([
    'status' => 'success',
    'summary' => [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'id_terapis' => $id_terapis,
        'total_transaksi_komisi' => $total_transaksi,
        'total_komisi' => $total_komisi
    ],
    'data' => $data
]);
