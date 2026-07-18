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
$id_pencarian = isset($_POST['id_pencarian']) ? (int)$_POST['id_pencarian'] : 0;
$id_komisi = isset($_POST['id_komisi']) ? (int)$_POST['id_komisi'] : 0;
$nominal = isset($_POST['nominal']) ? (float)$_POST['nominal'] : 0;

$is_update = ($id_detail_pencairan > 0);

if ($is_update) {
    if (empty($nominal)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Parameter nominal wajib diisi untuk update.']);
        exit();
    }
} else {
    if (empty($id_pencarian) || empty($id_komisi) || empty($nominal)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Parameter id_pencarian, id_komisi, dan nominal wajib diisi.'
        ]);
        exit();
    }
}

// Cek status isClosed dari parent pencairan
$parent_id = $id_pencarian;
if ($is_update) {
    // Ambil id_pencarian dari tabel pencairan_detail
    $sql_pd = "SELECT id_pencarian FROM pencairan_detail WHERE id_detail_pencairan = ?";
    $stmt_pd = $koneksi->prepare($sql_pd);
    $stmt_pd->bind_param("i", $id_detail_pencairan);
    $stmt_pd->execute();
    $result_pd = $stmt_pd->get_result();
    if ($row_pd = $result_pd->fetch_assoc()) {
        $parent_id = $row_pd['id_pencarian'];
    }
    $stmt_pd->close();
}

if ($parent_id > 0) {
    $sql_check = "SELECT isClosed FROM pencairan WHERE id_pencarian = ?";
    $stmt_check = $koneksi->prepare($sql_check);
    $stmt_check->bind_param("i", $parent_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($row_check = $result_check->fetch_assoc()) {
        if ($row_check['isClosed'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa diubah karena transaksi utama sudah ditutup (closed).']);
            exit();
        }
    }
    $stmt_check->close();
}

// Simpan ke database
if ($is_update) {
    $sql = "UPDATE pencairan_detail SET nominal = ?, update_at = NOW() WHERE id_detail_pencairan = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("di", $nominal, $id_detail_pencairan);
} else {
    $sql = "INSERT INTO pencairan_detail 
            (id_pencarian, id_komisi, nominal, created_at, update_at) 
            VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("iid", $id_pencarian, $id_komisi, $nominal);
}

if ($stmt->execute()) {
    $inserted_id = $is_update ? $id_detail_pencairan : $stmt->insert_id;
    echo json_encode([
        'status' => 'success', 
        'message' => $is_update ? 'Detail pencairan berhasil diupdate' : 'Detail pencairan berhasil disimpan',
        'data' => [
            'id_detail_pencairan' => $inserted_id,
            'id_pencarian' => $parent_id,
            'nominal' => $nominal
        ]
    ]);
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan detail pencairan: ' . $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
