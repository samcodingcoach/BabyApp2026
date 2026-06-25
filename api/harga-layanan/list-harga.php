<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Gunakan method GET.']);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek Otorisasi
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Anda belum login atau token telah berakhir.']);
    exit();
}

// Menangkap parameter filter
$kode_layanan = $_GET['kode_layanan'] ?? null;
$nama_kategori = $_GET['nama_kategori'] ?? null;

$baseQuery = "
    SELECT
        layanan_harga.id_harga_layanan, 
        layanan_harga.tanggal_efektif, 
        layanan_harga.harga, 
        layanan_harga.komisi_persentase, 
        layanan_harga.id_layanan, 
        layanan.kode_layanan,
        layanan.nama_layanan, 
        kategori_layanan.nama_kategori, 
        layanan.update_at
    FROM
        layanan_harga
        LEFT JOIN layanan 
            ON layanan_harga.id_layanan = layanan.id_layanan
        LEFT JOIN kategori_layanan 
            ON layanan.id_kategori_layanan = kategori_layanan.id_kategori_layanan
    WHERE 1=1
";

$types = "";
$params = [];

if (!empty($kode_layanan)) {
    $baseQuery .= " AND layanan.kode_layanan = ?";
    $types .= "s";
    $params[] = $kode_layanan;
}

if (!empty($nama_kategori)) {
    // Menggunakan pencarian LIKE agar lebih fleksibel mencari nama_kategori
    $baseQuery .= " AND kategori_layanan.nama_kategori LIKE ?";
    $types .= "s";
    $params[] = "%" . $nama_kategori . "%";
}

$baseQuery .= " ORDER BY layanan_harga.id_harga_layanan DESC";

$stmt = $koneksi->prepare($baseQuery);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
    exit();
}

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

$stmt->close();
$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data harga layanan berhasil diambil',
    'data' => $data,
    'count' => count($data)
]);
?>
