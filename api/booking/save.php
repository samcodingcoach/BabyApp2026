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

$user_id = $_SESSION['user_id'] ?? null; // Siapa yang membuat dari web admin
$koneksi->begin_transaction();

try {
    $id_member = $_POST['id_member'] ?? null;
    $id_member_or_id_bayi = $_POST['id_member_or_id_bayi'] ?? null;
    if ($id_member_or_id_bayi === '') $id_member_or_id_bayi = null;

    $tanggal_booking = $_POST['tanggal_booking'] ?? null;
    $id_terapis = $_POST['id_terapis'] ?? null;
    $alamat_baru = $_POST['alamat_baru'] ?? null;
    $whatsapp_baru = $_POST['whatsapp_baru'] ?? null;
    $prioritas = isset($_POST['prioritas']) ? (int)$_POST['prioritas'] : 0;
    $catatan = $_POST['catatan'] ?? null;
    $tarif_ongkir = isset($_POST['tarif_ongkir']) ? (double)$_POST['tarif_ongkir'] : 0;

    if (!$id_member || !$tanggal_booking || !$id_terapis) {
        throw new Exception("id_member, tanggal_booking, dan id_terapis wajib diisi");
    }

    $detailsJson = $_POST['details'] ?? '[]';
    $details = json_decode($detailsJson, true);
    if (!is_array($details) || empty($details)) {
        throw new Exception("Layanan belum dipilih (Booking Detail kosong)");
    }

    // 0. Cek batas minimal pemesanan (Harus H+30 Menit dari Waktu Sekarang)
    $now = time();
    $min_booking_time = $now + (30 * 60);
    $req_start_time = strtotime($tanggal_booking);

    if ($req_start_time < $min_booking_time) {
        throw new Exception("Waktu booking minimal adalah 30 menit dari jam sekarang (" . date('d M Y, H:i') . ").");
    }

    // 1. Validasi Jam Operasional Usaha
    $stmtUsaha = $koneksi->prepare("SELECT jam_buka, jam_tutup, sedang_buka FROM profile_usaha LIMIT 1");
    $stmtUsaha->execute();
    $resUsaha = $stmtUsaha->get_result();
    if ($resUsaha->num_rows > 0) {
        $usaha = $resUsaha->fetch_assoc();
        if ((int)$usaha['sedang_buka'] === 0) {
            throw new Exception("Toko/Klinik saat ini sedang ditutup (Libur). Tidak dapat menerima booking baru.");
        }
        
        $reqTime = date('H:i:s', strtotime($tanggal_booking));
        if ($reqTime < $usaha['jam_buka'] || $reqTime > $usaha['jam_tutup']) {
            throw new Exception("Jadwal di luar jam operasional. Jam buka: {$usaha['jam_buka']} s/d {$usaha['jam_tutup']}.");
        }
    }
    $stmtUsaha->close();

    // 2. Hitung Durasi Total Layanan yang Dipesan
    $total_durasi_layanan = 0;
    $layananIds = array_column($details, 'id_layanan');
    if (!empty($layananIds)) {
        $inQuery = implode(',', array_fill(0, count($layananIds), '?'));
        $stmtDur = $koneksi->prepare("SELECT id_layanan, durasi_menit FROM layanan WHERE id_layanan IN ($inQuery)");
        $types = str_repeat('i', count($layananIds));
        $stmtDur->bind_param($types, ...$layananIds);
        $stmtDur->execute();
        $resDur = $stmtDur->get_result();
        
        $durasiMap = [];
        while ($row = $resDur->fetch_assoc()) {
            $durasiMap[$row['id_layanan']] = (int)$row['durasi_menit'];
        }
        $stmtDur->close();
        
        foreach ($details as $d) {
            $total_durasi_layanan += $durasiMap[$d['id_layanan']] ?? 0;
        }
    }
    
    // Total durasi booking + 60 menit waktu istirahat/persiapan (Sesuai SOP)
    $total_durasi_booking = $total_durasi_layanan + 60;
    
    $req_start_time = strtotime($tanggal_booking);
    $req_end_time = $req_start_time + ($total_durasi_booking * 60);

    // 3. Pengecekan Overlap Jadwal Terapis
    $dateOnly = date('Y-m-d', $req_start_time);
    $stmtJadwal = $koneksi->prepare("
        SELECT b.id_booking, b.tanggal_booking, 
               IFNULL((SELECT SUM(l.durasi_menit) FROM booking_detail bd JOIN layanan l ON bd.id_layanan = l.id_layanan WHERE bd.id_booking = b.id_booking), 0) as total_durasi_layanan
        FROM booking b
        WHERE b.id_terapis = ? 
          AND DATE(b.tanggal_booking) = ?
          AND b.status_booking IN ('MENUNGGU', 'DIJADWALKAN', 'DIKONFIRMASI')
    ");
    $stmtJadwal->bind_param("is", $id_terapis, $dateOnly);
    $stmtJadwal->execute();
    $resJadwal = $stmtJadwal->get_result();
    
    while ($exist = $resJadwal->fetch_assoc()) {
        $exist_start = strtotime($exist['tanggal_booking']);
        $exist_end = $exist_start + (($exist['total_durasi_layanan'] + 60) * 60);
        
        // Cek irisan (overlap) waktu
        if ($req_start_time < $exist_end && $req_end_time > $exist_start) {
            throw new Exception("Jadwal terapis bertabrakan. Pesanan baru memakan waktu {$total_durasi_booking} menit (sampai jam " . date('H:i', $req_end_time) . "). Sementara terapis tersebut memiliki jadwal lain pada jam " . date('H:i', $exist_start) . " s/d " . date('H:i', $exist_end) . ".");
        }
    }
    $stmtJadwal->close();

    // Auto-Generate kode_booking (Contoh: B260626-001)
    $datePrefix = date('ymd', strtotime($tanggal_booking));
    $stmtSeq = $koneksi->prepare("SELECT COUNT(*) as cnt FROM booking WHERE DATE(tanggal_booking) = DATE(?)");
    $stmtSeq->bind_param("s", $tanggal_booking);
    $stmtSeq->execute();
    $resSeq = $stmtSeq->get_result()->fetch_assoc();
    $seq = str_pad($resSeq['cnt'] + 1, 3, '0', STR_PAD_LEFT);
    $stmtSeq->close();
    
    $kode_booking = "B{$datePrefix}-{$seq}";

    $stmtMaster = $koneksi->prepare("INSERT INTO booking (kode_booking, id_member, id_member_or_id_bayi, tanggal_booking, id_terapis, alamat_baru, whatsapp_baru, prioritas, catatan, user_id, tarif_ongkir) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtMaster->bind_param("siisisissid", $kode_booking, $id_member, $id_member_or_id_bayi, $tanggal_booking, $id_terapis, $alamat_baru, $whatsapp_baru, $prioritas, $catatan, $user_id, $tarif_ongkir);
    
    if (!$stmtMaster->execute()) {
        throw new Exception("Gagal menyimpan Master Booking: " . $stmtMaster->error);
    }
    
    $id_booking = $stmtMaster->insert_id;
    $stmtMaster->close();

    // Memproses Insert Details
    $stmtDetail = $koneksi->prepare("INSERT INTO booking_detail (id_booking, kode_booking, id_layanan, id_harga_layanan, keluhan, nominal, diskon, ppn, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($details as $d) {
        $id_layanan = $d['id_layanan'] ?? null;
        $id_harga_layanan = isset($d['id_harga_layanan']) && $d['id_harga_layanan'] !== '' ? $d['id_harga_layanan'] : null;
        $keluhan = $d['keluhan'] ?? null;
        $nominal = isset($d['nominal']) ? (double)$d['nominal'] : 0;
        $diskon = isset($d['diskon']) ? (double)$d['diskon'] : 0;
        $ppn = isset($d['ppn']) ? (double)$d['ppn'] : 0;
        $total = isset($d['total']) ? (double)$d['total'] : 0;
        
        $stmtDetail->bind_param("isiisdddd", $id_booking, $kode_booking, $id_layanan, $id_harga_layanan, $keluhan, $nominal, $diskon, $ppn, $total);
        if (!$stmtDetail->execute()) {
            throw new Exception("Gagal menyimpan Booking Detail: " . $stmtDetail->error);
        }
    }
    $stmtDetail->close();

    $koneksi->commit();
    
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Transaksi Booking berhasil dibuat', 'data' => ['id_booking' => $id_booking, 'kode_booking' => $kode_booking]]);

} catch (Exception $e) {
    $koneksi->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$koneksi->close();
?>
