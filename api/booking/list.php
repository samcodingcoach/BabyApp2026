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

$id_booking = $_GET['id_booking'] ?? null;
$kode_booking = $_GET['kode_booking'] ?? null;
$id_member = $_GET['id_member'] ?? null;
$status_booking = $_GET['status_booking'] ?? null;
$tanggal_awal = $_GET['tanggal_awal'] ?? null;
$tanggal_akhir = $_GET['tanggal_akhir'] ?? null;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 200;
$offset = ($page - 1) * $limit;

$query = "
    SELECT 
        b.id_booking, b.kode_booking, b.id_member, m.nama as nama_member, m.whatsapp as whatsapp_member,
        b.id_member_or_id_bayi,
        b.tanggal_booking, b.id_terapis, t.nama_terapis, 
        b.status_booking, b.alamat_baru, b.whatsapp_baru, b.prioritas, b.catatan, b.created_at, b.tarif_ongkir,
        (IFNULL((SELECT SUM(total) FROM booking_detail WHERE id_booking = b.id_booking), 0) + IFNULL(b.tarif_ongkir, 0)) as grand_total,
        (SELECT COUNT(*) FROM booking_detail WHERE id_booking = b.id_booking) as jumlah_layanan,
        DATE_ADD(b.tanggal_booking, INTERVAL ( IFNULL((SELECT SUM(l.durasi_menit) FROM booking_detail bd JOIN layanan l ON bd.id_layanan = l.id_layanan WHERE bd.id_booking = b.id_booking), 0) + 60 ) MINUTE) as waktu_selesai
    FROM booking b
    LEFT JOIN member m ON b.id_member = m.id_member
    LEFT JOIN terapis t ON b.id_terapis = t.id_terapis
    WHERE 1=1
";

$types = "";
$params = [];

if (!empty($id_booking)) { $query .= " AND b.id_booking = ?"; $types .= "i"; $params[] = $id_booking; }
if (!empty($kode_booking)) { $query .= " AND b.kode_booking = ?"; $types .= "s"; $params[] = $kode_booking; }
if (!empty($id_member)) { $query .= " AND b.id_member = ?"; $types .= "i"; $params[] = $id_member; }
if (!empty($status_booking)) { $query .= " AND b.status_booking = ?"; $types .= "s"; $params[] = $status_booking; }

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " AND DATE(b.tanggal_booking) BETWEEN ? AND ?";
    $types .= "ss";
    $params[] = $tanggal_awal;
    $params[] = $tanggal_akhir;
}

$query .= " ORDER BY b.tanggal_booking DESC, b.id_booking DESC LIMIT ? OFFSET ?";
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

// Pagination total
$countQuery = "
    SELECT COUNT(*) as total 
    FROM booking b
    WHERE 1=1
";
$countTypes = "";
$countParams = [];
if (!empty($id_booking)) { $countQuery .= " AND b.id_booking = ?"; $countTypes .= "i"; $countParams[] = $id_booking; }
if (!empty($kode_booking)) { $countQuery .= " AND b.kode_booking = ?"; $countTypes .= "s"; $countParams[] = $kode_booking; }
if (!empty($id_member)) { $countQuery .= " AND b.id_member = ?"; $countTypes .= "i"; $countParams[] = $id_member; }
if (!empty($status_booking)) { $countQuery .= " AND b.status_booking = ?"; $countTypes .= "s"; $countParams[] = $status_booking; }
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $countQuery .= " AND DATE(b.tanggal_booking) BETWEEN ? AND ?";
    $countTypes .= "ss";
    $countParams[] = $tanggal_awal;
    $countParams[] = $tanggal_akhir;
}

$totalRows = 0;
$stmtCount = $koneksi->prepare($countQuery);
if (!empty($countParams)) $stmtCount->bind_param($countTypes, ...$countParams);
$stmtCount->execute();
if ($rowCount = $stmtCount->get_result()->fetch_assoc()) {
    $totalRows = $rowCount['total'];
}
$stmtCount->close();

$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success', 
    'data' => $data, 
    'total_rows' => $totalRows,
    'current_page' => $page,
    'per_page' => $limit,
    'total_pages' => ceil($totalRows / $limit)
]);
?>
