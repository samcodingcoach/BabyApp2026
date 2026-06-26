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

$id_usaha = $_POST['id_usaha'] ?? null;
$nama_usaha = $_POST['nama_usaha'] ?? null;
$nama_pemilik = $_POST['nama_pemilik'] ?? null;
$alamat = $_POST['alamat'] ?? null;
$alamat_gps = $_POST['alamat_gps'] ?? null;
$whatsapp1 = $_POST['whatsapp1'] ?? null;
$whatsapp2 = $_POST['whatsapp2'] ?? null;
$ig = $_POST['ig'] ?? null;
$jam_buka = $_POST['jam_buka'] ?? null;
$jam_tutup = $_POST['jam_tutup'] ?? null;
$website = $_POST['website'] ?? null;
$sedang_buka = isset($_POST['sedang_buka']) ? (int)$_POST['sedang_buka'] : 1;

// Jika tabel profile_usaha masih kosong, kita insert.
// Jika tidak, kita update
$queryCek = $koneksi->query("SELECT id_usaha FROM profile_usaha LIMIT 1");
if ($queryCek->num_rows === 0) {
    // Insert initial
    $koneksi->query("INSERT INTO profile_usaha (id_usaha, sedang_buka) VALUES (1, 1)");
    $id_usaha = 1;
} else {
    $row = $queryCek->fetch_assoc();
    $id_usaha = $row['id_usaha'];
}

// Cek apakah ada upload foto
$foto_usaha = null;
if (isset($_FILES['foto_usaha']) && $_FILES['foto_usaha']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['foto_usaha']['tmp_name'];
    $name = $_FILES['foto_usaha']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $foto_usaha = "profile_usaha_" . time() . "." . $ext;
        $target_dir = "../../images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($tmp_name, $target_dir . $foto_usaha);
    }
}

if ($foto_usaha) {
    $stmt = $koneksi->prepare("UPDATE profile_usaha SET nama_usaha=?, nama_pemilik=?, alamat=?, alamat_gps=?, whatsapp1=?, whatsapp2=?, ig=?, jam_buka=?, jam_tutup=?, website=?, sedang_buka=?, foto_usaha=? WHERE id_usaha=?");
    $stmt->bind_param("ssssssssssisi", $nama_usaha, $nama_pemilik, $alamat, $alamat_gps, $whatsapp1, $whatsapp2, $ig, $jam_buka, $jam_tutup, $website, $sedang_buka, $foto_usaha, $id_usaha);
} else {
    $stmt = $koneksi->prepare("UPDATE profile_usaha SET nama_usaha=?, nama_pemilik=?, alamat=?, alamat_gps=?, whatsapp1=?, whatsapp2=?, ig=?, jam_buka=?, jam_tutup=?, website=?, sedang_buka=? WHERE id_usaha=?");
    $stmt->bind_param("ssssssssssii", $nama_usaha, $nama_pemilik, $alamat, $alamat_gps, $whatsapp1, $whatsapp2, $ig, $jam_buka, $jam_tutup, $website, $sedang_buka, $id_usaha);
}

if ($stmt->execute()) {
    http_response_code(200); echo json_encode(['status' => 'success', 'message' => 'Profile Usaha berhasil diperbarui']);
} else {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
