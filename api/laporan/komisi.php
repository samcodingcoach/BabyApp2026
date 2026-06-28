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

$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$id_terapis = $_GET['id_terapis'] ?? null;

if (!$start_date || !$end_date) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'start_date dan end_date wajib diisi (YYYY-MM-DD)']); exit();
}

$where = "pembayaran.status_pembayaran = 'LUNAS' AND DATE(pembayaran.tanggal_bayar) >= ? AND DATE(pembayaran.tanggal_bayar) <= ?";
$params = [$start_date, $end_date];
$types = "ss";

if ($id_terapis) {
    $where .= " AND terapis.id_terapis = ?";
    $params[] = $id_terapis;
    $types .= "i";
}

// Grouping berdasarkan terapis untuk melihat komisi keseluruhan
$sql = "
    SELECT 
        terapis.id_terapis,
        terapis.nama_terapis,
        COUNT(booking.id_booking) as total_transaksi,
        SUM(pembayaran.jumlah_bayar) as total_omset
    FROM booking
    JOIN pembayaran ON booking.id_booking = pembayaran.id_booking
    JOIN terapis ON booking.id_terapis = terapis.id_terapis
    WHERE $where
    GROUP BY terapis.id_terapis, terapis.nama_terapis
    ORDER BY total_omset DESC
";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$grand_total_omset = 0;

while ($row = $result->fetch_assoc()) {
    // Karena belum ada tabel atau kolom komisi khusus, kita kembalikan omset. 
    // Persentase komisi bisa dihitung di frontend atau ditambahkan nanti.
    $data[] = [
        'id_terapis' => $row['id_terapis'],
        'nama_terapis' => $row['nama_terapis'],
        'total_transaksi' => (int)$row['total_transaksi'],
        'total_omset' => (int)$row['total_omset']
    ];
    $grand_total_omset += (int)$row['total_omset'];
}

echo json_encode([
    'status' => 'success',
    'summary' => [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'id_terapis' => $id_terapis,
        'jumlah_terapis_aktif' => count($data),
        'grand_total_omset' => $grand_total_omset
    ],
    'data' => $data
]);
