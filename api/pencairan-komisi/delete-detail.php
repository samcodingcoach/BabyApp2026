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

// Cek autentikasi (dimatikan sementara untuk testing Postman)
// if (!check_auth($koneksi)) {
//     http_response_code(401); 
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
//     exit();
// }

$id_pencarian = isset($_POST['id_pencarian']) ? (int)$_POST['id_pencarian'] : 0;
$id_komisi = isset($_POST['id_komisi']) ? (int)$_POST['id_komisi'] : 0;

// Support JSON payload if $_POST is empty
if (empty($id_pencarian) || empty($id_komisi)) {
    $json_input = file_get_contents('php://input');
    $decoded = json_decode($json_input, true);
    if (is_array($decoded)) {
        $id_pencarian = isset($decoded['id_pencarian']) ? (int)$decoded['id_pencarian'] : $id_pencarian;
        $id_komisi = isset($decoded['id_komisi']) ? (int)$decoded['id_komisi'] : $id_komisi;
    }
}

if (empty($id_pencarian) || empty($id_komisi)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Pencairan dan ID Komisi wajib disertakan.']);
    exit();
}

// Cek status isClosed dari parent pencairan
$sql_pd = "SELECT isClosed FROM pencairan WHERE id_pencarian = ?";
$stmt_pd = $koneksi->prepare($sql_pd);
$stmt_pd->bind_param("i", $id_pencarian);
$stmt_pd->execute();
$result_pd = $stmt_pd->get_result();

if ($row_pd = $result_pd->fetch_assoc()) {
    if ($row_pd['isClosed'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa dihapus karena transaksi utama sudah ditutup (closed).']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data transaksi pencairan tidak ditemukan.']);
    exit();
}
$stmt_pd->close();

// Hapus data
$sql_del = "DELETE FROM pencairan_detail WHERE id_pencarian = ? AND id_komisi = ?";
$stmt_del = $koneksi->prepare($sql_del);
$stmt_del->bind_param("ii", $id_pencarian, $id_komisi);

if ($stmt_del->execute()) {
    if ($stmt_del->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Data detail pencairan berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Rincian tidak ditemukan atau sudah terhapus.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $stmt_del->error]);
}

$stmt_del->close();
$koneksi->close();
?>
