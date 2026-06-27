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

$kode_booking = $_GET['kode_booking'] ?? null;
$id_booking = $_GET['id_booking'] ?? null;
if (!$kode_booking && !$id_booking) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'kode_booking atau id_booking wajib disertakan']); exit();
}

$whereClause = $kode_booking ? "booking.kode_booking = ?" : "booking.id_booking = ?";

// Master Query
$stmt = $koneksi->prepare("
    SELECT
        pembayaran.id_pembayaran, 
        pembayaran.id_booking as p_id_booking, 
        booking.id_booking,
        booking.kode_booking, 
        booking.tanggal_booking,
        pembayaran.tanggal_bayar, 
        pembayaran.kode_pembayaran, 
        pembayaran.jumlah_bayar, 
        pembayaran.metode_pembayaran, 
        pembayaran.qris_transaction_id, 
        pembayaran.status_pembayaran, 
        pembayaran.created_at as pembayaran_created_at, 
        member.nama, 
        member.alamat, 
        booking.alamat_baru, 
        member.kecamatan, 
        booking.status_booking, 
        booking.tarif_ongkir, 
        terapis.nama_terapis, 
        member.whatsapp,
        booking.id_member_or_id_bayi,
        bayi.nama_bayi
    FROM
        booking
    LEFT JOIN pembayaran ON pembayaran.id_booking = booking.id_booking
    LEFT JOIN member ON booking.id_member = member.id_member
    LEFT JOIN terapis ON booking.id_terapis = terapis.id_terapis
    LEFT JOIN bayi ON booking.id_member_or_id_bayi = bayi.id_bayi
    WHERE $whereClause
");
if ($kode_booking) {
    $stmt->bind_param("s", $kode_booking);
} else {
    $stmt->bind_param("i", $id_booking);
}
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data invoice tidak ditemukan']); exit();
}
$invoice = $res->fetch_assoc();
$stmt->close();

// Fallback logic
if (empty($invoice['alamat_baru']) || $invoice['alamat_baru'] === '-') {
    $invoice['alamat_baru'] = $invoice['alamat'];
}

// Ambil Profil Usaha
$stmtP = $koneksi->prepare("SELECT nama_usaha, alamat, whatsapp1 FROM profile_usaha LIMIT 1");
$stmtP->execute();
$resP = $stmtP->get_result();
$profile = $resP->fetch_assoc();
$stmtP->close();
$invoice['profile_usaha'] = $profile;

// Detail Query
$stmtDetail = $koneksi->prepare("
    SELECT
        booking_detail.id_detail_booking, 
        booking_detail.id_booking, 
        booking_detail.kode_booking, 
        booking_detail.id_layanan, 
        booking_detail.id_harga_layanan, 
        layanan.nama_layanan, 
        layanan.durasi_menit, 
        kategori_layanan.nama_kategori, 
        booking_detail.keluhan, 
        booking_detail.nominal, 
        booking_detail.diskon, 
        booking_detail.total
    FROM
        booking_detail
        INNER JOIN layanan ON booking_detail.id_layanan = layanan.id_layanan
        LEFT JOIN kategori_layanan ON layanan.id_kategori_layanan = kategori_layanan.id_kategori_layanan
    WHERE booking_detail.kode_booking = ? OR booking_detail.id_booking = ?
");
$stmtDetail->bind_param("si", $kode_booking, $invoice['id_booking']);
$stmtDetail->execute();
$resDetail = $stmtDetail->get_result();

$details = [];
$grandTotal = 0;
while ($row = $resDetail->fetch_assoc()) {
    $details[] = $row;
    $grandTotal += (double)$row['total'];
}
$stmtDetail->close();

$grandTotal += (double)($invoice['tarif_ongkir'] ?? 0);

$invoice['details'] = $details;
$invoice['grand_total'] = $grandTotal;
$invoice['is_lunas'] = (!empty($invoice['id_pembayaran']) && $invoice['status_pembayaran'] === 'LUNAS');

$koneksi->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $invoice]);
?>
