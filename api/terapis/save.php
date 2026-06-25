<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    http_response_code(401); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit();
}

$kode_terapis = $_POST['kode_terapis'] ?? null;
$nama_terapis = $_POST['nama_terapis'] ?? null;
$jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
if ($jenis_kelamin === '') $jenis_kelamin = null;
$tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
if ($tanggal_lahir === '') $tanggal_lahir = null;
$agama = $_POST['agama'] ?? 1;
$alamat = $_POST['alamat'] ?? null;
$kecamatan = $_POST['kecamatan'] ?? null;
$alamat_gps = $_POST['alamat_gps'] ?? null;
$pendidikan = $_POST['pendidikan'] ?? 'SMA/K';
$ig = $_POST['ig'] ?? null;
$keterangan = $_POST['keterangan'] ?? null;
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : 1;

if (!$kode_terapis || !$nama_terapis) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'kode_terapis dan nama_terapis wajib diisi']); exit();
}

// Cek duplikasi kode_terapis
$stmtCheck = $koneksi->prepare("SELECT id_terapis FROM terapis WHERE kode_terapis = ?");
$stmtCheck->bind_param("s", $kode_terapis);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_terapis sudah digunakan']); exit();
}
$stmtCheck->close();

// Upload foto
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        $foto = $kode_terapis . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../../images/' . $foto);
    }
}

$stmt = $koneksi->prepare("INSERT INTO terapis (kode_terapis, nama_terapis, jenis_kelamin, tanggal_lahir, agama, alamat, kecamatan, alamat_gps, foto, pendidikan, ig, keterangan, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssisssssssssi", $kode_terapis, $nama_terapis, $jenis_kelamin, $tanggal_lahir, $agama, $alamat, $kecamatan, $alamat_gps, $foto, $pendidikan, $ig, $keterangan, $is_active);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Data Terapis berhasil disimpan', 'data' => ['id_terapis' => $stmt->insert_id]]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
