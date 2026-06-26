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

if (!$id_booking) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_booking wajib disertakan']); exit();
}

// Ambil Header (Booking Master) beserta join informasi orang tua, bayi, dan terapis
$stmt = $koneksi->prepare("
    SELECT 
        b.id_booking, b.kode_booking, b.id_member, m.nama as nama_member, m.whatsapp as whatsapp_member, m.alamat as alamat_member,
        b.id_member_or_id_bayi, byi.nama_bayi,
        b.tanggal_booking, b.id_terapis, t.nama_terapis, 
        b.status_booking, b.alamat_baru, b.whatsapp_baru, b.prioritas, b.catatan, b.created_at
    FROM booking b
    LEFT JOIN member m ON b.id_member = m.id_member
    LEFT JOIN terapis t ON b.id_terapis = t.id_terapis
    LEFT JOIN bayi byi ON b.id_member_or_id_bayi = byi.id_bayi
    WHERE b.id_booking = ?
");
$stmt->bind_param("i", $id_booking);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data booking (transaksi) tidak ditemukan']); exit();
}
$booking = $res->fetch_assoc();
$stmt->close();

// Fallback logic jika kosong (0 atau blank)
if (empty($booking['whatsapp_baru']) || $booking['whatsapp_baru'] === '0') {
    $booking['whatsapp_baru'] = $booking['whatsapp_member'];
}
if (empty($booking['alamat_baru']) || $booking['alamat_baru'] === '-') {
    $booking['alamat_baru'] = $booking['alamat_member'];
}

// Ambil Details Layanan yang melekat pada struk tersebut
$stmtDetail = $koneksi->prepare("
    SELECT 
        bd.*, l.nama_layanan
    FROM booking_detail bd
    LEFT JOIN layanan l ON bd.id_layanan = l.id_layanan
    WHERE bd.id_booking = ?
");
$stmtDetail->bind_param("i", $id_booking);
$stmtDetail->execute();
$resDetail = $stmtDetail->get_result();

$details = [];
$grandTotal = 0;
while ($row = $resDetail->fetch_assoc()) {
    $details[] = $row;
    $grandTotal += (double)$row['total'];
}
$stmtDetail->close();

// Gabungkan response
$booking['details'] = $details;
$booking['grand_total'] = $grandTotal;

$koneksi->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $booking]);
?>
