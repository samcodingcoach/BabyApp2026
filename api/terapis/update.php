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

$id_terapis = $_POST['id_terapis'] ?? null;
if (!$id_terapis) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_terapis wajib diisi']); exit();
}

$stmtCheck = $koneksi->prepare("SELECT * FROM terapis WHERE id_terapis = ?");
$stmtCheck->bind_param("i", $id_terapis);
$stmtCheck->execute();
$res = $stmtCheck->get_result();
if ($res->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']); exit();
}
$existing = $res->fetch_assoc();
$stmtCheck->close();

$kode_terapis = $_POST['kode_terapis'] ?? $existing['kode_terapis'];
$nama_terapis = $_POST['nama_terapis'] ?? $existing['nama_terapis'];
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? ($_POST['jenis_kelamin'] === '' ? null : $_POST['jenis_kelamin']) : $existing['jenis_kelamin'];
$tanggal_lahir = isset($_POST['tanggal_lahir']) ? ($_POST['tanggal_lahir'] === '' ? null : $_POST['tanggal_lahir']) : $existing['tanggal_lahir'];
$agama = $_POST['agama'] ?? $existing['agama'];
$alamat = $_POST['alamat'] ?? $existing['alamat'];
$kecamatan = $_POST['kecamatan'] ?? $existing['kecamatan'];
$alamat_gps = $_POST['alamat_gps'] ?? $existing['alamat_gps'];
$pendidikan = $_POST['pendidikan'] ?? $existing['pendidikan'];
$ig = $_POST['ig'] ?? $existing['ig'];
$keterangan = $_POST['keterangan'] ?? $existing['keterangan'];
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : $existing['is_active'];

// Cek duplikasi kode baru
$stmtDup = $koneksi->prepare("SELECT id_terapis FROM terapis WHERE kode_terapis = ? AND id_terapis != ?");
$stmtDup->bind_param("si", $kode_terapis, $id_terapis);
$stmtDup->execute();
if ($stmtDup->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_terapis sudah digunakan']); exit();
}
$stmtDup->close();

// Upload dan rename foto
$foto = $existing['foto'];
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed)) {
        $foto = $kode_terapis . '.' . $ext;
        $dest = '../../images/' . $foto;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
            // Hapus file fisik lama jika berganti ekstensi/nama
            if ($existing['foto'] && $existing['foto'] !== $foto && file_exists('../../images/' . $existing['foto'])) {
                @unlink('../../images/' . $existing['foto']);
            }
        }
    }
} else if ($kode_terapis !== $existing['kode_terapis'] && $existing['foto'] && file_exists('../../images/' . $existing['foto'])) {
    // Sinkronisasi rename file foto jika kode_terapis diubah tapi gambar tidak upload baru
    $oldExt = pathinfo($existing['foto'], PATHINFO_EXTENSION);
    $newFilename = $kode_terapis . '.' . $oldExt;
    if ($existing['foto'] !== $newFilename) {
        rename('../../images/' . $existing['foto'], '../../images/' . $newFilename);
        $foto = $newFilename;
    }
}

$stmt = $koneksi->prepare("UPDATE terapis SET kode_terapis=?, nama_terapis=?, jenis_kelamin=?, tanggal_lahir=?, agama=?, alamat=?, kecamatan=?, alamat_gps=?, foto=?, pendidikan=?, ig=?, keterangan=?, is_active=?, update_at=CURRENT_TIMESTAMP WHERE id_terapis=?");
$stmt->bind_param("ssisssssssssii", $kode_terapis, $nama_terapis, $jenis_kelamin, $tanggal_lahir, $agama, $alamat, $kecamatan, $alamat_gps, $foto, $pendidikan, $ig, $keterangan, $is_active, $id_terapis);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Data terapis berhasil diupdate']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
