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

$id_member = $_POST['id_member'] ?? null;
$nama_bayi = $_POST['nama_bayi'] ?? null;
$anak_ke = $_POST['anak_ke'] ?? null;
if ($anak_ke === '') $anak_ke = null;

$tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
if ($tanggal_lahir === '') $tanggal_lahir = null;

$jenis_kelamin = isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] !== '' ? $_POST['jenis_kelamin'] : null;

$berat_kg = isset($_POST['berat_kg']) && $_POST['berat_kg'] !== '' ? (double)$_POST['berat_kg'] : null;
$tinggi_cm = isset($_POST['tinggi_cm']) && $_POST['tinggi_cm'] !== '' ? (double)$_POST['tinggi_cm'] : null;
$lingkar_kepala_cm = isset($_POST['lingkar_kepala_cm']) && $_POST['lingkar_kepala_cm'] !== '' ? (double)$_POST['lingkar_kepala_cm'] : null;

$golongan_darah = $_POST['golongan_darah'] ?? null;
$alergi = $_POST['alergi'] ?? null;
$keterangan = $_POST['keterangan'] ?? null;
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : 1;

if (!$id_member || !$nama_bayi) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_member dan nama_bayi wajib diisi']); exit();
}

// Cek id_member valid (Orang tuanya harus terdaftar di sistem)
$stmtCheck = $koneksi->prepare("SELECT id_member FROM member WHERE id_member = ?");
$stmtCheck->bind_param("i", $id_member);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data orangtua (Member) tidak ditemukan']); exit();
}
$stmtCheck->close();

// Upload foto (Dengan sistem unique naming anti-tabrakan)
$photo = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        // Membersihkan nama agar aman untuk URL/Sistem File
        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $nama_bayi));
        $photo = "bayi_{$id_member}_{$safeName}_" . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../../images/' . $photo);
    }
}

$stmt = $koneksi->prepare("INSERT INTO bayi (id_member, anak_ke, nama_bayi, tanggal_lahir, jenis_kelamin, berat_kg, tinggi_cm, lingkar_kepala_cm, golongan_darah, alergi, photo, keterangan, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssdddssssi", $id_member, $anak_ke, $nama_bayi, $tanggal_lahir, $jenis_kelamin, $berat_kg, $tinggi_cm, $lingkar_kepala_cm, $golongan_darah, $alergi, $photo, $keterangan, $is_active);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Data Bayi berhasil disimpan', 'data' => ['id_bayi' => $stmt->insert_id]]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
