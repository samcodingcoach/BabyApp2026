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
    
    // 1. Cek apakah sudah ada pembayaran sebelumnya
    $stmtCek = $koneksi->prepare("SELECT id_pembayaran, status_pembayaran FROM pembayaran WHERE id_booking = ?");
    $stmtCek->bind_param("i", $id_booking);
    $stmtCek->execute();
    $resCek = $stmtCek->get_result();
    $existing = $resCek->fetch_assoc();
    $stmtCek->close();
    
    if ($existing && $existing['status_pembayaran'] === 'LUNAS') {
        throw new Exception("Transaksi ini sudah lunas sebelumnya.");
    }
    
    $tanggal_bayar = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? null;
    $kode_pembayaran = 'MANUAL-' . $id_booking . '-' . time();
    
    // 2. Ambil data id_terapis dan tarif_ongkir dari tabel booking
    $stmtB = $koneksi->prepare("SELECT id_terapis, tarif_ongkir FROM booking WHERE id_booking = ?");
    $stmtB->bind_param("i", $id_booking);
    $stmtB->execute();
    $resB = $stmtB->get_result();
    if ($resB->num_rows === 0) {
         throw new Exception("Data booking tidak ditemukan.");
    }
    $b = $resB->fetch_assoc();
    $id_terapis = $b['id_terapis'];
    $tarif_ongkir = isset($b['tarif_ongkir']) ? (double)$b['tarif_ongkir'] : 0;
    $stmtB->close();

    // 3. Hitung potongan_ongkir (FLAT 60% untuk Terapis)
    $potongan_ongkir = $tarif_ongkir * 0.6;
    
    // 4. Ambil jumlah_komisi (SUM dari total_komisi di booking_detail)
    $stmtK = $koneksi->prepare("SELECT SUM(total_komisi) as jumlah_komisi FROM booking_detail WHERE id_booking = ?");
    $stmtK->bind_param("i", $id_booking);
    $stmtK->execute();
    $resK = $stmtK->get_result();
    $rowK = $resK->fetch_assoc();
    $jumlah_komisi = isset($rowK['jumlah_komisi']) ? (double)$rowK['jumlah_komisi'] : 0;
    $stmtK->close();

    // 5. Insert atau Update ke Pembayaran
    if ($existing) {
        $stmtBayar = $koneksi->prepare("UPDATE pembayaran SET user_id=?, tanggal_bayar=?, kode_pembayaran=?, jumlah_bayar=?, jumlah_komisi=?, potongan_ongkir=?, metode_pembayaran=?, status_pembayaran='LUNAS', qris_transaction_id=NULL, va_number=NULL, qris_image=NULL WHERE id_booking=?");
        $stmtBayar->bind_param("issdddsi", $user_id, $tanggal_bayar, $kode_pembayaran, $jumlah_bayar, $jumlah_komisi, $potongan_ongkir, $metode_pembayaran, $id_booking);
    } else {
        $stmtBayar = $koneksi->prepare("INSERT INTO pembayaran (id_booking, user_id, tanggal_bayar, kode_pembayaran, jumlah_bayar, jumlah_komisi, potongan_ongkir, metode_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'LUNAS')");
        $stmtBayar->bind_param("iissddds", $id_booking, $user_id, $tanggal_bayar, $kode_pembayaran, $jumlah_bayar, $jumlah_komisi, $potongan_ongkir, $metode_pembayaran);
    }
    
    if (!$stmtBayar->execute()) {
        throw new Exception("Gagal menyimpan data pembayaran: " . $stmtBayar->error);
    }
    $stmtBayar->close();
    
    // 6. Insert ke Komisi Terapis
    // Yang diterima terapis = jumlah_komisi + potongan_ongkir
    $total_komisi_terapis = $jumlah_komisi + $potongan_ongkir;
    
    if ($total_komisi_terapis > 0) {
        $stmtKom = $koneksi->prepare("INSERT INTO komisi_terapis (id_booking, id_terapis, nominal_komisi, status_pencairan) VALUES (?, ?, ?, 'BELUM_CAIR')");
        $stmtKom->bind_param("iid", $id_booking, $id_terapis, $total_komisi_terapis);
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
