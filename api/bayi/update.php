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

$id_bayi = $_POST['id_bayi'] ?? null;
if (!$id_bayi) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_bayi wajib diisi']); exit();
}

$stmtCheck = $koneksi->prepare("SELECT * FROM bayi WHERE id_bayi = ?");
$stmtCheck->bind_param("i", $id_bayi);
$stmtCheck->execute();
$res = $stmtCheck->get_result();
if ($res->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data bayi tidak ditemukan']); exit();
}
$existing = $res->fetch_assoc();
$stmtCheck->close();

$id_member = $_POST['id_member'] ?? $existing['id_member'];
$nama_bayi = $_POST['nama_bayi'] ?? $existing['nama_bayi'];
$anak_ke = isset($_POST['anak_ke']) ? ($_POST['anak_ke'] === '' ? null : $_POST['anak_ke']) : $existing['anak_ke'];
$tanggal_lahir = isset($_POST['tanggal_lahir']) ? ($_POST['tanggal_lahir'] === '' ? null : $_POST['tanggal_lahir']) : $existing['tanggal_lahir'];
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? ($_POST['jenis_kelamin'] === '' ? null : $_POST['jenis_kelamin']) : $existing['jenis_kelamin'];

$berat_kg = isset($_POST['berat_kg']) ? ($_POST['berat_kg'] === '' ? null : (double)$_POST['berat_kg']) : $existing['berat_kg'];
$tinggi_cm = isset($_POST['tinggi_cm']) ? ($_POST['tinggi_cm'] === '' ? null : (double)$_POST['tinggi_cm']) : $existing['tinggi_cm'];
$lingkar_kepala_cm = isset($_POST['lingkar_kepala_cm']) ? ($_POST['lingkar_kepala_cm'] === '' ? null : (double)$_POST['lingkar_kepala_cm']) : $existing['lingkar_kepala_cm'];

$golongan_darah = $_POST['golongan_darah'] ?? $existing['golongan_darah'];
$alergi = $_POST['alergi'] ?? $existing['alergi'];
$keterangan = $_POST['keterangan'] ?? $existing['keterangan'];
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : $existing['is_active'];

// Upload foto pengganti (Opsional)
$photo = $existing['photo'];
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $nama_bayi));
        $photo = "bayi_{$id_member}_{$safeName}_" . uniqid() . '.' . $ext;
        $dest = '../../images/' . $photo;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            // Bersihkan file lama agar tidak menyampah di server
            if ($existing['photo'] && file_exists('../../images/' . $existing['photo'])) {
                @unlink('../../images/' . $existing['photo']);
            }
        }
    }
}

$stmt = $koneksi->prepare("UPDATE bayi SET id_member=?, anak_ke=?, nama_bayi=?, tanggal_lahir=?, jenis_kelamin=?, berat_kg=?, tinggi_cm=?, lingkar_kepala_cm=?, golongan_darah=?, alergi=?, photo=?, keterangan=?, is_active=?, update_at=CURRENT_TIMESTAMP WHERE id_bayi=?");
$stmt->bind_param("iisssdddssssii", $id_member, $anak_ke, $nama_bayi, $tanggal_lahir, $jenis_kelamin, $berat_kg, $tinggi_cm, $lingkar_kepala_cm, $golongan_darah, $alergi, $photo, $keterangan, $is_active, $id_bayi);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Profil medis Bayi berhasil diupdate']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
