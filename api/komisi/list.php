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

$status_pencairan = $_GET['status_pencairan'] ?? null;
$id_booking = $_GET['id_booking'] ?? null;
$id_terapis = $_GET['id_terapis'] ?? null;
$tanggal = $_GET['tanggal'] ?? null;

$query = "
    SELECT 
        k.id_komisi, 
        k.nominal_komisi, 
        k.status_pencairan, 
        k.tanggal_pencairan, 
        k.created_at,
        b.kode_booking,
        b.tarif_ongkir,
        t.nama_terapis
    FROM komisi_terapis k
    JOIN booking b ON k.id_booking = b.id_booking
    JOIN terapis t ON k.id_terapis = t.id_terapis
    WHERE 1=1
";

$types = "";
$params = [];

if (!empty($status_pencairan)) {
    $query .= " AND k.status_pencairan = ?";
    $types .= "s";
    $params[] = $status_pencairan;
}

if (!empty($id_booking)) {
    $query .= " AND k.id_booking = ?";
    $types .= "i";
    $params[] = $id_booking;
}

if (!empty($id_terapis)) {
    $query .= " AND k.id_terapis = ?";
    $types .= "i";
    $params[] = $id_terapis;
}

if (!empty($tanggal)) {
    $query .= " AND DATE(b.tanggal_booking) = ?";
    $types .= "s";
    $params[] = $tanggal;
}

$query .= " ORDER BY k.created_at DESC";

$stmt = $koneksi->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
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
