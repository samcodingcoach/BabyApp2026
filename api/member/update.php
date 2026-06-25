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
if (!$id_member) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_member wajib diisi']); exit();
}

$stmtCheck = $koneksi->prepare("SELECT * FROM member WHERE id_member = ?");
$stmtCheck->bind_param("i", $id_member);
$stmtCheck->execute();
$res = $stmtCheck->get_result();
if ($res->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data member tidak ditemukan']); exit();
}
$existing = $res->fetch_assoc();
$stmtCheck->close();

$nik = $_POST['nik'] ?? $existing['nik'];
$nama = $_POST['nama'] ?? $existing['nama'];
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? ($_POST['jenis_kelamin'] === '' ? null : $_POST['jenis_kelamin']) : $existing['jenis_kelamin'];
$alamat = $_POST['alamat'] ?? $existing['alamat'];
$kecamatan = $_POST['kecamatan'] ?? $existing['kecamatan'];
$alamat_gps = $_POST['alamat_gps'] ?? $existing['alamat_gps'];
$whatsapp = $_POST['whatsapp'] ?? $existing['whatsapp'];
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : $existing['is_active'];

// Update Password hanya jika diisi (password baru masuk)
$password = $existing['password'];
if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
}

// Cek duplikasi NIK baru dengan member lain
$stmtDup = $koneksi->prepare("SELECT id_member FROM member WHERE nik = ? AND id_member != ?");
$stmtDup->bind_param("si", $nik, $id_member);
$stmtDup->execute();
if ($stmtDup->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'NIK sudah digunakan oleh member lain']); exit();
}
$stmtDup->close();

// Upload dan sinkronisasi rename foto
$photo = $existing['photo'];
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        $photo = $nik . '.' . $ext;
        $dest = '../../images/' . $photo;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            // Hapus file fisik lama jika berganti ekstensi/nama
            if ($existing['photo'] && $existing['photo'] !== $photo && file_exists('../../images/' . $existing['photo'])) {
                @unlink('../../images/' . $existing['photo']);
            }
        }
    }
} else if ($nik !== $existing['nik'] && $existing['photo'] && file_exists('../../images/' . $existing['photo'])) {
    // Sinkronisasi rename file photo jika NIK diubah tapi gambar tidak upload baru
    $oldExt = pathinfo($existing['photo'], PATHINFO_EXTENSION);
    $newFilename = $nik . '.' . $oldExt;
    if ($existing['photo'] !== $newFilename) {
        rename('../../images/' . $existing['photo'], '../../images/' . $newFilename);
        $photo = $newFilename;
    }
}

$stmt = $koneksi->prepare("UPDATE member SET nik=?, nama=?, jenis_kelamin=?, alamat=?, kecamatan=?, alamat_gps=?, password=?, whatsapp=?, photo=?, is_active=?, update_at=CURRENT_TIMESTAMP WHERE id_member=?");
$stmt->bind_param("ssissssssii", $nik, $nama, $jenis_kelamin, $alamat, $kecamatan, $alamat_gps, $password, $whatsapp, $photo, $is_active, $id_member);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Data member berhasil diupdate']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
