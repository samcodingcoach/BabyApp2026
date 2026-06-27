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

try {
    $id_booking = $_POST['id_booking'] ?? null;
    $tanggal_booking = $_POST['tanggal_booking'] ?? null;

    if (!$id_booking || !$tanggal_booking) {
        throw new Exception("Parameter id_booking dan tanggal_booking wajib diisi.");
    }

    // 0. Cek batas minimal pemesanan (Harus H+30 Menit dari Waktu Sekarang)
    $now = time();
    $min_booking_time = $now + (30 * 60);
    $req_start_time = strtotime($tanggal_booking);

    if ($req_start_time < $min_booking_time) {
        throw new Exception("Waktu penjadwalan ulang minimal 30 menit dari jam sekarang (" . date('d M Y, H:i') . ").");
    }

    // Ambil info booking master saat ini
    $stmtB = $koneksi->prepare("SELECT id_terapis, status_booking FROM booking WHERE id_booking = ?");
    $stmtB->bind_param("i", $id_booking);
    $stmtB->execute();
    $resB = $stmtB->get_result();
    if($resB->num_rows === 0) throw new Exception("Data booking tidak ditemukan.");
    $bookingData = $resB->fetch_assoc();
    $id_terapis = $bookingData['id_terapis'];
    $stmtB->close();

    // 1. Validasi Jam Operasional Usaha
    $stmtUsaha = $koneksi->prepare("SELECT jam_buka, jam_tutup, sedang_buka FROM profile_usaha LIMIT 1");
    $stmtUsaha->execute();
    $resUsaha = $stmtUsaha->get_result();
    if ($resUsaha->num_rows > 0) {
        $usaha = $resUsaha->fetch_assoc();
        if ((int)$usaha['sedang_buka'] === 0) {
            throw new Exception("Klinik saat ini ditutup sementara. Tidak dapat mengubah jadwal.");
        }
        
        $reqTime = date('H:i:s', $req_start_time);
        if ($reqTime < $usaha['jam_buka'] || $reqTime > $usaha['jam_tutup']) {
            throw new Exception("Jadwal di luar jam operasional. Jam buka: {$usaha['jam_buka']} s/d {$usaha['jam_tutup']}.");
        }
    }
    $stmtUsaha->close();

    // 2. Hitung Durasi Total Layanan yang Dipesan
    $total_durasi_layanan = 0;
    $stmtDur = $koneksi->prepare("
        SELECT SUM(l.durasi_menit) as durasi
        FROM booking_detail bd
        JOIN layanan l ON bd.id_layanan = l.id_layanan
        WHERE bd.id_booking = ?
    ");
    $stmtDur->bind_param("i", $id_booking);
    $stmtDur->execute();
    $resDur = $stmtDur->get_result();
    if($row = $resDur->fetch_assoc()) {
        $total_durasi_layanan = (int)$row['durasi'];
    }
    $stmtDur->close();
    
    // Total durasi booking + 60 menit waktu istirahat
    $total_durasi_booking = $total_durasi_layanan + 60;
    $req_end_time = $req_start_time + ($total_durasi_booking * 60);

    // 3. Pengecekan Overlap Jadwal Terapis (Abaikan ID Booking ini sendiri)
    $dateOnly = date('Y-m-d', $req_start_time);
    $stmtJadwal = $koneksi->prepare("
        SELECT b.id_booking, b.tanggal_booking, 
               IFNULL((SELECT SUM(l.durasi_menit) FROM booking_detail bd JOIN layanan l ON bd.id_layanan = l.id_layanan WHERE bd.id_booking = b.id_booking), 0) as total_durasi_layanan
        FROM booking b
        WHERE b.id_terapis = ? 
          AND b.id_booking != ?
          AND DATE(b.tanggal_booking) = ?
          AND b.status_booking IN ('MENUNGGU', 'DIJADWALKAN', 'DIKONFIRMASI')
    ");
    $stmtJadwal->bind_param("iis", $id_terapis, $id_booking, $dateOnly);
    $stmtJadwal->execute();
    $resJadwal = $stmtJadwal->get_result();
    
    while ($exist = $resJadwal->fetch_assoc()) {
        $exist_start = strtotime($exist['tanggal_booking']);
        $exist_end = $exist_start + (($exist['total_durasi_layanan'] + 60) * 60);
        
        // Cek irisan waktu
        if ($req_start_time < $exist_end && $req_end_time > $exist_start) {
            throw new Exception("Jadwal terapis bertabrakan. Reschedule memakan waktu {$total_durasi_booking} menit (sampai jam " . date('H:i', $req_end_time) . "). Sementara terapis tersebut memiliki jadwal lain pada jam " . date('H:i', $exist_start) . " s/d " . date('H:i', $exist_end) . ".");
        }
    }
    $stmtJadwal->close();

    // Lolos Validasi -> Update Data
    $tanggal_booking_sql = date('Y-m-d H:i:s', $req_start_time);
    $stmtUpdate = $koneksi->prepare("UPDATE booking SET tanggal_booking = ?, status_booking = 'DIJADWALKAN' WHERE id_booking = ?");
    $stmtUpdate->bind_param("si", $tanggal_booking_sql, $id_booking);
    
    if (!$stmtUpdate->execute()) {
        throw new Exception("Gagal melakukan Reschedule: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Berhasil Reschedule! Jadwal dipindah ke ' . date('d M Y H:i', $req_start_time) . ' dan status diubah menjadi DIJADWALKAN.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$koneksi->close();
?>
