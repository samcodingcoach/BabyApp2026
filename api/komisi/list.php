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

if (!check_auth($koneksi)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$query = "
    SELECT 
        k.id_komisi, 
        k.nominal_komisi, 
        k.status_pencairan, 
        k.tanggal_pencairan, 
        k.created_at,
        b.kode_booking,
        t.nama_terapis
    FROM komisi_terapis k
    JOIN booking b ON k.id_booking = b.id_booking
    JOIN terapis t ON k.id_terapis = t.id_terapis
    ORDER BY k.created_at DESC
";

$result = $koneksi->query($query);
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'data' => $data
]);
?>
