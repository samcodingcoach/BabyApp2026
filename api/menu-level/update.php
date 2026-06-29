<?php
header('Content-Type: application/json');
require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_levelmenu = $_POST['id_levelmenu'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $nama_menu = $_POST['nama_menu'] ?? '';
    $link = $_POST['link'] ?? '';
    $terlihat = $_POST['terlihat'] ?? 0;

    if (empty($id_levelmenu)) {
        echo json_encode(['status' => 'error', 'message' => 'ID Menu Level tidak ditemukan!']);
        exit;
    }

    $stmt = $koneksi->prepare("UPDATE menu_level SET role_id = ?, nama_menu = ?, link = ?, terlihat = ? WHERE id_levelmenu = ?");
    $stmt->bind_param("issii", $role_id, $nama_menu, $link, $terlihat, $id_levelmenu);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Menu level berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui menu level: ' . $stmt->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
