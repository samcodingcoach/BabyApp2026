<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); 
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek autentikasi
if (!check_auth($koneksi)) {
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit();
}

$id_pencarian = isset($_POST['id_pencarian']) ? (int)$_POST['id_pencarian'] : 0;

if (empty($id_pencarian)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Pencairan wajib disertakan.']);
    exit();
}

// Cek apakah isClosed = 0
$sql_check = "SELECT isClosed, bukti FROM pencairan WHERE id_pencarian = ?";
$stmt_check = $koneksi->prepare($sql_check);
$stmt_check->bind_param("i", $id_pencarian);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($row_check = $result_check->fetch_assoc()) {
    if ($row_check['isClosed'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa dihapus karena sudah ditutup (closed).']);
        exit();
    }
    
    // Hapus file gambar jika ada
    $bukti_file = $row_check['bukti'];
    if (!empty($bukti_file)) {
        $file_path = "../../images/pencairan/" . $bukti_file;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data pencairan tidak ditemukan.']);
    exit();
}
$stmt_check->close();

// Hapus detail terlebih dahulu untuk menghindari orphan data / foreign key constraint
$sql_del_detail = "DELETE FROM pencairan_detail WHERE id_pencarian = ?";
$stmt_del_detail = $koneksi->prepare($sql_del_detail);
$stmt_del_detail->bind_param("i", $id_pencarian);
$stmt_del_detail->execute();
$stmt_del_detail->close();

// Hapus data utama
$sql_del = "DELETE FROM pencairan WHERE id_pencarian = ?";
$stmt_del = $koneksi->prepare($sql_del);
$stmt_del->bind_param("i", $id_pencarian);

if ($stmt_del->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Data pencairan berhasil dihapus']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $stmt_del->error]);
}

$stmt_del->close();
$koneksi->close();
?>
