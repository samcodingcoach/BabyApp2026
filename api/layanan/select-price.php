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

// Support form-data maupun RAW JSON
$input = json_decode(file_get_contents('php://input'), TRUE) ?? [];
$id_layanan = $_POST['id_layanan'] ?? ($input['id_layanan'] ?? null);
$tanggal_efektif = $_POST['tanggal_efektif'] ?? ($input['tanggal_efektif'] ?? null);
$harga = $_POST['harga'] ?? ($input['harga'] ?? null);
$komisi_persentase = $_POST['komisi_persentase'] ?? ($input['komisi_persentase'] ?? null);

// Validasi
if (!$id_layanan || !$tanggal_efektif || $harga === null || $komisi_persentase === null) {
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => 'Seluruh parameter (id_layanan, tanggal_efektif, harga, komisi_persentase) wajib diisi']); 
    exit();
}

// 1. Insert riwayat ke tabel layanan_harga
$stmtInsert = $koneksi->prepare("INSERT INTO layanan_harga (id_layanan, tanggal_efektif, harga, komisi_persentase) VALUES (?, ?, ?, ?)");
$stmtInsert->bind_param("isdd", $id_layanan, $tanggal_efektif, $harga, $komisi_persentase);

if ($stmtInsert->execute()) {
    $new_id_harga_layanan = $stmtInsert->insert_id;
    $stmtInsert->close();
    
    // 2. Cek apakah tanggal efektif = tanggal server HARI INI
    $today = date('Y-m-d');
    $is_updated = false;
    
    // Jika tanggal sama dengan hari ini, maka update id_harga_layanan di tabel parent (layanan)
    if ($tanggal_efektif === $today) {
        $stmtUpdate = $koneksi->prepare("UPDATE layanan SET id_harga_layanan = ?, update_at = CURRENT_TIMESTAMP WHERE id_layanan = ?");
        $stmtUpdate->bind_param("ii", $new_id_harga_layanan, $id_layanan);
        if ($stmtUpdate->execute()) {
            $is_updated = true;
        }
        $stmtUpdate->close();
    }
    
    http_response_code(201);
    echo json_encode([
        'status' => 'success', 
        'message' => 'Harga berhasil dicatat' . ($is_updated ? ' dan otomatis ter-update ke tabel layanan utama.' : ' (Berlaku untuk masa depan).'),
        'data' => [
            'id_harga_layanan' => $new_id_harga_layanan,
            'is_layanan_updated' => $is_updated
        ]
    ]);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => $stmtInsert->error]);
}

$koneksi->close();
?>
