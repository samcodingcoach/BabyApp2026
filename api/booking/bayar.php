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

if (!check_auth($koneksi)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$koneksi->begin_transaction();

try {
    $id_booking = $_POST['id_booking'] ?? null;
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? null;
    
    // Clean string dari jQuery Mask (contoh: 150.000 -> 150000)
    $jumlah_bayar_str = $_POST['jumlah_bayar'] ?? '0';
    $jumlah_bayar = (double)str_replace('.', '', $jumlah_bayar_str);
    
    if (!$id_booking || !$metode_pembayaran || $jumlah_bayar <= 0) {
        throw new Exception("Data tidak lengkap atau jumlah bayar tidak valid.");
    }
    
    // 1. Cek apakah sudah lunas sebelumnya
    $stmtCek = $koneksi->prepare("SELECT id_pembayaran FROM pembayaran WHERE id_booking = ?");
    $stmtCek->bind_param("i", $id_booking);
    $stmtCek->execute();
    if ($stmtCek->get_result()->num_rows > 0) {
        throw new Exception("Transaksi ini sudah lunas sebelumnya.");
    }
    $stmtCek->close();
    
    $tanggal_bayar = date('Y-m-d H:i:s');
    
    // 2. Insert ke Pembayaran
    $stmtBayar = $koneksi->prepare("INSERT INTO pembayaran (id_booking, jumlah_bayar, metode_pembayaran, tanggal_bayar, status_pembayaran) VALUES (?, ?, ?, ?, 'LUNAS')");
    $stmtBayar->bind_param("idss", $id_booking, $jumlah_bayar, $metode_pembayaran, $tanggal_bayar);
    if (!$stmtBayar->execute()) {
        throw new Exception("Gagal menyimpan data pembayaran: " . $stmtBayar->error);
    }
    $stmtBayar->close();
    
    // 3. Ambil data id_terapis dari tabel booking
    $stmtB = $koneksi->prepare("SELECT id_terapis FROM booking WHERE id_booking = ?");
    $stmtB->bind_param("i", $id_booking);
    $stmtB->execute();
    $resB = $stmtB->get_result();
    if ($resB->num_rows === 0) {
         throw new Exception("Data booking tidak ditemukan.");
    }
    $b = $resB->fetch_assoc();
    $id_terapis = $b['id_terapis'];
    $stmtB->close();
    
    // 4. Hitung Komisi Terapis
    // Ambil semua detail layanan, hitung: total * (komisi_persentase / 100)
    // Berdasarkan kesepakatan: Komisi dihitung dari Total (setelah diskon)
    $stmtDetail = $koneksi->prepare("
        SELECT bd.total, lh.komisi_persentase 
        FROM booking_detail bd
        LEFT JOIN layanan_harga lh ON bd.id_harga_layanan = lh.id_harga_layanan
        WHERE bd.id_booking = ?
    ");
    $stmtDetail->bind_param("i", $id_booking);
    $stmtDetail->execute();
    $resDetail = $stmtDetail->get_result();
    
    $total_komisi = 0;
    while ($row = $resDetail->fetch_assoc()) {
        $persen = isset($row['komisi_persentase']) ? (double)$row['komisi_persentase'] : 0;
        $total_layanan = isset($row['total']) ? (double)$row['total'] : 0;
        
        $komisi_item = $total_layanan * ($persen / 100);
        $total_komisi += $komisi_item;
    }
    $stmtDetail->close();
    
    // 5. Insert ke Komisi Terapis jika > 0
    if ($total_komisi > 0) {
        $stmtKom = $koneksi->prepare("INSERT INTO komisi_terapis (id_booking, id_terapis, nominal_komisi, status_pencairan) VALUES (?, ?, ?, 'BELUM_CAIR')");
        $stmtKom->bind_param("iid", $id_booking, $id_terapis, $total_komisi);
        if (!$stmtKom->execute()) {
             throw new Exception("Gagal mencatat komisi terapis: " . $stmtKom->error);
        }
        $stmtKom->close();
    }
    
    $koneksi->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil, transaksi telah dilunaskan.']);
    
} catch (Exception $e) {
    $koneksi->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$koneksi->close();
?>
