<?php
session_start();
header('Content-Type: application/json');

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Gunakan method GET.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek Otorisasi (Mendukung Session Browser ATAU Bearer Token)
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau token telah berakhir.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

// Tangkap parameter filter (search) dari GET
$id_layanan = $_GET['id_layanan'] ?? null;
$kode_layanan = $_GET['kode_layanan'] ?? null;
$kode_kategori = $_GET['kode_kategori'] ?? null;
$nama_layanan = $_GET['nama_layanan'] ?? null;

// Query Dasar sesuai dengan skema
$baseQuery = "
    SELECT
        layanan.id_layanan, 
        layanan.id_kategori_layanan, 
        kategori_layanan.kode_kategori, 
        kategori_layanan.nama_kategori, 
        layanan.kode_layanan, 
        layanan.nama_layanan, 
        layanan.durasi_menit, 
        layanan.deskripsi, 
        layanan.is_active, 
        layanan.created_at, 
        layanan.update_at, 
        layanan.id_harga_layanan, 
        layanan_harga.tanggal_efektif, 
        layanan_harga.harga, 
        layanan_harga.komisi_persentase,
        layanan.picture1,
        layanan.picture2,
        layanan.picture3,
        layanan.video1
    FROM
        layanan
        INNER JOIN kategori_layanan 
            ON layanan.id_kategori_layanan = kategori_layanan.id_kategori_layanan
        LEFT JOIN layanan_harga 
            ON layanan.id_harga_layanan = layanan_harga.id_harga_layanan
    WHERE 1=1
";

// Menyiapkan binding dinamis
$types = "";
$params = [];

if (!empty($id_layanan)) {
    $baseQuery .= " AND layanan.id_layanan = ?";
    $types .= "i";
    $params[] = $id_layanan;
}

if (!empty($kode_layanan)) {
    $baseQuery .= " AND layanan.kode_layanan = ?";
    $types .= "s";
    $params[] = $kode_layanan;
}

if (!empty($kode_kategori)) {
    $baseQuery .= " AND kategori_layanan.kode_kategori = ?";
    $types .= "s";
    $params[] = $kode_kategori;
}

if (!empty($nama_layanan)) {
    $baseQuery .= " AND layanan.nama_layanan LIKE ?";
    $types .= "s";
    $params[] = "%" . $nama_layanan . "%";
}

// Urutkan ID terbaru di atas
$baseQuery .= " ORDER BY layanan.id_layanan DESC";

$stmt = $koneksi->prepare($baseQuery);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
    exit();
}

// Bind parameter dinamis jika ada filter
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
    'message' => 'Data layanan berhasil diambil',
    'data' => $data,
    'count' => count($data)
]);
?>
