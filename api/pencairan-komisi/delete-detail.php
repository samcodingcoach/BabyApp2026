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

$id_detail_pencairan = isset($_POST['id_detail_pencairan']) ? (int)$_POST['id_detail_pencairan'] : 0;

if (empty($id_detail_pencairan)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Detail Pencairan wajib disertakan.']);
    exit();
}

// Cek status isClosed dari parent pencairan
$sql_pd = "SELECT p.isClosed FROM pencairan_detail pd 
           INNER JOIN pencairan p ON pd.id_pencarian = p.id_pencarian 
           WHERE pd.id_detail_pencairan = ?";
$stmt_pd = $koneksi->prepare($sql_pd);
$stmt_pd->bind_param("i", $id_detail_pencairan);
$stmt_pd->execute();
$result_pd = $stmt_pd->get_result();

if ($row_pd = $result_pd->fetch_assoc()) {
    if ($row_pd['isClosed'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa dihapus karena transaksi utama sudah ditutup (closed).']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data detail pencairan tidak ditemukan.']);
    exit();
}
$stmt_pd->close();

// Hapus data
$sql_del = "DELETE FROM pencairan_detail WHERE id_detail_pencairan = ?";
$stmt_del = $koneksi->prepare($sql_del);
$stmt_del->bind_param("i", $id_detail_pencairan);

if ($stmt_del->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Data detail pencairan berhasil dihapus']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $stmt_del->error]);
}

$stmt_del->close();
$koneksi->close();
?>
