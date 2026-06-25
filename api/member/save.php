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

$nik = $_POST['nik'] ?? null;
$nama = $_POST['nama'] ?? null;
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? ($_POST['jenis_kelamin'] === '' ? null : $_POST['jenis_kelamin']) : null;
$alamat = $_POST['alamat'] ?? null;
$kecamatan = $_POST['kecamatan'] ?? null;
$alamat_gps = $_POST['alamat_gps'] ?? null;
$password = $_POST['password'] ?? null;
$whatsapp = $_POST['whatsapp'] ?? null;
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : 1;

if (!$nik || !$nama) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'NIK dan nama wajib diisi']); exit();
}

// Cek duplikasi NIK
$stmtCheck = $koneksi->prepare("SELECT id_member FROM member WHERE nik = ?");
$stmtCheck->bind_param("s", $nik);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'NIK sudah terdaftar di sistem']); exit();
}
$stmtCheck->close();

// Hash Password dengan standar BCRYPT modern
if ($password) {
    $password = password_hash($password, PASSWORD_DEFAULT);
}

// Upload foto
$photo = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        $photo = $nik . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../../images/' . $photo);
    }
}

$stmt = $koneksi->prepare("INSERT INTO member (nik, nama, jenis_kelamin, alamat, kecamatan, alamat_gps, password, whatsapp, photo, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssissssssi", $nik, $nama, $jenis_kelamin, $alamat, $kecamatan, $alamat_gps, $password, $whatsapp, $photo, $is_active);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Member berhasil didaftarkan', 'data' => ['id_member' => $stmt->insert_id]]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
