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

$id_layanan = $_POST['id_layanan'] ?? null;
if (!$id_layanan) {
    http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'id_layanan wajib diisi']); exit();
}

// Get existing data
$stmtCheck = $koneksi->prepare("SELECT * FROM layanan WHERE id_layanan = ?");
$stmtCheck->bind_param("i", $id_layanan);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
if ($result->num_rows === 0) {
    http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']); exit();
}
$existing = $result->fetch_assoc();
$stmtCheck->close();

$id_kategori_layanan = $_POST['id_kategori_layanan'] ?? $existing['id_kategori_layanan'];
$id_harga_layanan = isset($_POST['id_harga_layanan']) ? ($_POST['id_harga_layanan'] === '' ? null : $_POST['id_harga_layanan']) : $existing['id_harga_layanan'];
$kode_layanan = $_POST['kode_layanan'] ?? $existing['kode_layanan'];
$nama_layanan = $_POST['nama_layanan'] ?? $existing['nama_layanan'];
$durasi_menit = $_POST['durasi_menit'] ?? $existing['durasi_menit'];
$deskripsi = $_POST['deskripsi'] ?? $existing['deskripsi'];
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : $existing['is_active'];
$video1 = $_POST['video1'] ?? $existing['video1'];

// Cek Duplikasi Kode
$stmtDup = $koneksi->prepare("SELECT id_layanan FROM layanan WHERE kode_layanan = ? AND id_layanan != ?");
$stmtDup->bind_param("si", $kode_layanan, $id_layanan);
$stmtDup->execute();
if ($stmtDup->get_result()->num_rows > 0) {
    http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'kode_layanan sudah digunakan']); exit();
}
$stmtDup->close();

// Upload Gambar & Timpa Gambar Lama Jika Ada Update
function uploadGambar($file, $kode, $no, $oldPicture) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = $kode . '-' . $no . '.' . $ext;
            $dest = '../../images/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Delete old image if it differs in name/ext
                if ($oldPicture && $oldPicture !== $filename && file_exists('../../images/' . $oldPicture)) {
                    @unlink('../../images/' . $oldPicture);
                }
                return $filename;
            }
        }
    }
    // Jika kode_layanan berubah, kita rename file fisik agar tetap sesuai aturan kode_layanan-x.jpg
    if (!$file && $oldPicture && file_exists('../../images/' . $oldPicture)) {
        $oldExt = pathinfo($oldPicture, PATHINFO_EXTENSION);
        $newFilename = $kode . '-' . $no . '.' . $oldExt;
        if ($oldPicture !== $newFilename) {
            rename('../../images/' . $oldPicture, '../../images/' . $newFilename);
            return $newFilename;
        }
    }
    return $oldPicture; // Retain old if no new upload and no kode change
}

$picture1 = uploadGambar($_FILES['picture1'] ?? null, $kode_layanan, 1, $existing['picture1']);
$picture2 = uploadGambar($_FILES['picture2'] ?? null, $kode_layanan, 2, $existing['picture2']);
$picture3 = uploadGambar($_FILES['picture3'] ?? null, $kode_layanan, 3, $existing['picture3']);

$stmt = $koneksi->prepare("UPDATE layanan SET id_kategori_layanan=?, id_harga_layanan=?, kode_layanan=?, nama_layanan=?, durasi_menit=?, deskripsi=?, is_active=?, update_at=CURRENT_TIMESTAMP, picture1=?, picture2=?, picture3=?, video1=? WHERE id_layanan=?");
$stmt->bind_param("iississssssi", $id_kategori_layanan, $id_harga_layanan, $kode_layanan, $nama_layanan, $durasi_menit, $deskripsi, $is_active, $picture1, $picture2, $picture3, $video1, $id_layanan);

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Data layanan berhasil diupdate']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$koneksi->close();
?>
