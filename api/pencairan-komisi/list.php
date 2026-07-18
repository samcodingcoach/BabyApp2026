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
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$query = "
    SELECT
        pencairan.id_pencarian, 
        pencairan.kode_pencairan, 
        pencairan.keterangan, 
        pencairan.user_id, 
        users.full_name, 
        pencairan.created_at, 
        pencairan.edit_at, 
        pencairan.bukti, 
        pencairan.tanggal_transfer, 
        pencairan.bank, 
        pencairan.biaya_admin, 
        pencairan.isClosed
    FROM
        pencairan
        INNER JOIN users ON pencairan.user_id = users.user_id
    WHERE 1=1
";

$types = "";
$params = [];

if (!empty($kode_pencairan)) { 
    $query .= " AND pencairan.kode_pencairan LIKE ?"; 
    $types .= "s"; 
    $params[] = "%".$kode_pencairan."%"; 
}

if (!empty($start_date)) {
    $query .= " AND DATE(pencairan.created_at) >= ?";
    $types .= "s";
    $params[] = $start_date;
}

if (!empty($end_date)) {
    $query .= " AND DATE(pencairan.created_at) <= ?";
    $types .= "s";
    $params[] = $end_date;
}

$query .= " ORDER BY pencairan.created_at DESC";

$stmt = $koneksi->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();
$koneksi->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $data, 'count' => count($data)]);
?>
