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

$id_bayi = $_GET['id_bayi'] ?? null;
$id_member = $_GET['id_member'] ?? null;
$nama_bayi = $_GET['nama_bayi'] ?? null;
$nik_member = $_GET['nik_member'] ?? null;

$query = "
    SELECT
        bayi.id_bayi, 
        bayi.id_member, 
        member.nik, 
        member.nama, 
        bayi.anak_ke, 
        bayi.nama_bayi, 
        bayi.tanggal_lahir, 
        bayi.jenis_kelamin, 
        bayi.berat_kg, 
        bayi.tinggi_cm, 
        bayi.lingkar_kepala_cm, 
        bayi.golongan_darah, 
        bayi.alergi, 
        bayi.photo, 
        bayi.keterangan, 
        bayi.is_active, 
        bayi.created_at, 
        bayi.update_at
    FROM
        bayi
        INNER JOIN member ON bayi.id_member = member.id_member
    WHERE 1=1
";

$types = "";
$params = [];

if (!empty($id_bayi)) { $query .= " AND bayi.id_bayi = ?"; $types .= "i"; $params[] = $id_bayi; }
if (!empty($id_member)) { $query .= " AND bayi.id_member = ?"; $types .= "i"; $params[] = $id_member; }
if (!empty($nama_bayi)) { $query .= " AND bayi.nama_bayi LIKE ?"; $types .= "s"; $params[] = "%".$nama_bayi."%"; }
if (!empty($nik_member)) { $query .= " AND member.nik = ?"; $types .= "s"; $params[] = $nik_member; }

$query .= " ORDER BY bayi.id_bayi DESC";

$stmt = $koneksi->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);

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
