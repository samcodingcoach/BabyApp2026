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

// Mendukung Form-Data dan RAW JSON
$input = json_decode(file_get_contents('php://input'), TRUE) ?? [];
$id_layanan = $_POST['id_layanan'] ?? ($input['id_layanan'] ?? null);
if ($id_layanan === '') $id_layanan = null;

$tanggal_efektif = $_POST['tanggal_efektif'] ?? ($input['tanggal_efektif'] ?? null);
$harga = $_POST['harga'] ?? ($input['harga'] ?? null);
$komisi_persentase = $_POST['komisi_persentase'] ?? ($input['komisi_persentase'] ?? null);

if (!$tanggal_efektif || $harga === null || $komisi_persentase === null) {
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => 'Parameter tanggal_efektif, harga, dan komisi_persentase wajib diisi']); 
    exit();
}

$stmtInsert = $koneksi->prepare("INSERT INTO layanan_harga (id_layanan, tanggal_efektif, harga, komisi_persentase) VALUES (?, ?, ?, ?)");
$stmtInsert->bind_param("isdd", $id_layanan, $tanggal_efektif, $harga, $komisi_persentase);

if ($stmtInsert->execute()) {
    $new_id_harga_layanan = $stmtInsert->insert_id;
    $is_updated = false;
    
    // Auto-sync jika id_layanan dikirimkan dan tanggalnya = hari ini
    if ($id_layanan !== null && $tanggal_efektif === date('Y-m-d')) {
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
        'message' => 'Data harga layanan berhasil disimpan' . ($is_updated ? ' dan disinkronisasikan ke tabel layanan.' : ''),
        'data' => [
            'id_harga_layanan' => $new_id_harga_layanan,
            'is_layanan_updated' => $is_updated
        ]
    ]);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => $stmtInsert->error]);
}

$stmtInsert->close();
$koneksi->close();
?>
