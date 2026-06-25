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

// Menangkap field wajib
$id_kategori_layanan = $_POST['id_kategori_layanan'] ?? null;
$id_harga_layanan = $_POST['id_harga_layanan'] ?? null;
$kode_layanan = $_POST['kode_layanan'] ?? null;
$nama_layanan = $_POST['nama_layanan'] ?? null;
$durasi_menit = $_POST['durasi_menit'] ?? null;

if ($id_harga_layanan === '') $id_harga_layanan = null;

// Menangkap field opsional
$deskripsi = $_POST['deskripsi'] ?? '';
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : 1;
$video1 = $_POST['video1'] ?? '';

// Validasi
if (!$id_kategori_layanan || !$kode_layanan || !$nama_layanan || !$durasi_menit) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Field bertanda * wajib diisi']); exit();
}

// Cek duplikasi kode_layanan
$stmtCheck = $koneksi->prepare("SELECT id_layanan FROM layanan WHERE kode_layanan = ?");
$stmtCheck->bind_param("s", $kode_layanan);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_layanan sudah digunakan']); exit();
}
$stmtCheck->close();

// Upload Gambar (1, 2, dan 3)
function uploadGambar($file, $kode, $no) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = $kode . '-' . $no . '.' . $ext;
            $dest = '../../images/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return $filename;
            }
        }
    }
    return null;
}

$picture1 = uploadGambar($_FILES['picture1'] ?? null, $kode_layanan, 1);
$picture2 = uploadGambar($_FILES['picture2'] ?? null, $kode_layanan, 2);
$picture3 = uploadGambar($_FILES['picture3'] ?? null, $kode_layanan, 3);

$stmt = $koneksi->prepare("INSERT INTO layanan (id_kategori_layanan, id_harga_layanan, kode_layanan, nama_layanan, durasi_menit, deskripsi, is_active, picture1, picture2, picture3, video1) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissisissss", $id_kategori_layanan, $id_harga_layanan, $kode_layanan, $nama_layanan, $durasi_menit, $deskripsi, $is_active, $picture1, $picture2, $picture3, $video1);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Layanan berhasil disimpan', 'data' => ['id_layanan' => $stmt->insert_id]]);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
