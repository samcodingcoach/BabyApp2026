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

$id_member = $_GET['id_member'] ?? null;
$nik = $_GET['nik'] ?? null;
$nama = $_GET['nama'] ?? null;

// Fitur Paging Limiter 200 per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 200;
$offset = ($page - 1) * $limit;

// Secara default kita tidak me-return password untuk keamanan
$query = "SELECT id_member, nik, nama, jenis_kelamin, alamat, kecamatan, alamat_gps, whatsapp, photo, is_active, created_at, update_at FROM member WHERE 1=1";
$types = "";
$params = [];

if (!empty($id_member)) { $query .= " AND id_member = ?"; $types .= "i"; $params[] = $id_member; }
if (!empty($nik)) { $query .= " AND nik = ?"; $types .= "s"; $params[] = $nik; }
if (!empty($nama)) { $query .= " AND nama LIKE ?"; $types .= "s"; $params[] = "%".$nama."%"; }

$query .= " ORDER BY id_member DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $koneksi->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

// Dapatkan Total Seluruh Data (Untuk Keperluan UI Pagination)
$countQuery = "SELECT COUNT(*) as total FROM member WHERE 1=1";
$countTypes = "";
$countParams = [];
if (!empty($id_member)) { $countQuery .= " AND id_member = ?"; $countTypes .= "i"; $countParams[] = $id_member; }
if (!empty($nik)) { $countQuery .= " AND nik = ?"; $countTypes .= "s"; $countParams[] = $nik; }
if (!empty($nama)) { $countQuery .= " AND nama LIKE ?"; $countTypes .= "s"; $countParams[] = "%".$nama."%"; }

$totalRows = 0;
$stmtCount = $koneksi->prepare($countQuery);
if (!empty($countParams)) $stmtCount->bind_param($countTypes, ...$countParams);
$stmtCount->execute();
$resCount = $stmtCount->get_result();
if ($rowCount = $resCount->fetch_assoc()) {
    $totalRows = $rowCount['total'];
}
$stmtCount->close();

$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'message' => 'Data member berhasil diambil', 
    'data' => $data, 
    'count' => count($data),
    'total_rows' => $totalRows,
    'current_page' => $page,
    'per_page' => $limit,
    'total_pages' => ceil($totalRows / $limit)
]);
?>
