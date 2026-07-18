<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); 
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); 
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek autentikasi (opsional jika dibutuhkan sesuai standar sistem)
if (!check_auth($koneksi)) {
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit();
}

$kode_pencairan = $_GET['kode_pencairan'] ?? null;

if (empty($kode_pencairan)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter kode_pencairan wajib diisi']);
    exit();
}

$query = "
    SELECT
        pencairan_detail.id_detail_pencairan, 
        pencairan.kode_pencairan, 
        pencairan_detail.id_pencarian, 
        pencairan_detail.id_komisi, 
        komisi_terapis.id_booking, 
        booking.kode_booking, 
        booking.tarif_ongkir, 
        pencairan_detail.nominal, 
        pencairan_detail.created_at, 
        pencairan_detail.update_at,
        terapis.id_terapis,
        terapis.kode_terapis,
        terapis.nama_terapis
    FROM
        pencairan_detail
        INNER JOIN pencairan ON pencairan_detail.id_pencarian = pencairan.id_pencarian
        INNER JOIN komisi_terapis ON pencairan_detail.id_komisi = komisi_terapis.id_komisi
        INNER JOIN booking ON komisi_terapis.id_booking = booking.id_booking
        INNER JOIN terapis ON komisi_terapis.id_terapis = terapis.id_terapis
    WHERE 1=1
";

$types = "s";
$params = [$kode_pencairan];

$query .= " AND pencairan.kode_pencairan = ? ORDER BY pencairan_detail.id_detail_pencairan DESC";

$stmt = $koneksi->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
$total_nominal = 0;
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_nominal += (float)$row['nominal'];
}
$stmt->close();
$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'data' => $data, 
    'count' => count($data),
    'total_nominal' => $total_nominal
]);
?>
