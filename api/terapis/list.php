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

$id_terapis = $_GET['id_terapis'] ?? null;
$kode_terapis = $_GET['kode_terapis'] ?? null;
$nama_terapis = $_GET['nama_terapis'] ?? null;

$query = "SELECT * FROM terapis WHERE 1=1";
$types = "";
$params = [];

if (!empty($id_terapis)) { $query .= " AND id_terapis = ?"; $types .= "i"; $params[] = $id_terapis; }
if (!empty($kode_terapis)) { $query .= " AND kode_terapis = ?"; $types .= "s"; $params[] = $kode_terapis; }
if (!empty($nama_terapis)) { $query .= " AND nama_terapis LIKE ?"; $types .= "s"; $params[] = "%".$nama_terapis."%"; }

$query .= " ORDER BY id_terapis DESC";

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
echo json_encode(['status' => 'success', 'message' => 'Data terapis berhasil diambil', 'data' => $data, 'count' => count($data)]);
?>
