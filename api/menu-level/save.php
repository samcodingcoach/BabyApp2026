<?php
header('Content-Type: application/json');
require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = $_POST['role_id'] ?? '';
    $nama_menu = $_POST['nama_menu'] ?? '';
    $link = $_POST['link'] ?? '';
    $terlihat = $_POST['terlihat'] ?? 0;

    $kategori_menu = $_POST['kategori_menu'] ?? 'Lainnya';

    if (empty($role_id) || empty($nama_menu)) {
        echo json_encode(['status' => 'error', 'message' => 'Role ID dan Nama Menu harus diisi!']);
        exit;
    }

    $stmt = $koneksi->prepare("INSERT INTO menu_level (role_id, kategori_menu, nama_menu, link, terlihat) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $role_id, $kategori_menu, $nama_menu, $link, $terlihat);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Menu level berhasil disimpan']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan menu level: ' . $stmt->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
