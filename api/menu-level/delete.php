<?php
header('Content-Type: application/json');
require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_levelmenu = $_POST['id_levelmenu'] ?? '';

    if (empty($id_levelmenu)) {
        echo json_encode(['status' => 'error', 'message' => 'ID Menu Level tidak ditemukan!']);
        exit;
    }

    $stmt = $koneksi->prepare("DELETE FROM menu_level WHERE id_levelmenu = ?");
    $stmt->bind_param("i", $id_levelmenu);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Menu level berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus menu level: ' . $stmt->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid']);
}
