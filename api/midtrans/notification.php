<?php
require_once 'init.php';

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

$status_pembayaran_baru = null;

if ($transaction == 'capture') {
    if ($type == 'credit_card') {
        if ($fraud == 'challenge') {
            $status_pembayaran_baru = 'BELUM_LUNAS'; // butuh verifikasi manual
        } else {
            $status_pembayaran_baru = 'LUNAS';
        }
    }
} else if ($transaction == 'settlement') {
    $status_pembayaran_baru = 'LUNAS';
} else if ($transaction == 'pending') {
    $status_pembayaran_baru = 'BELUM_LUNAS';
} else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
    // Bisa dicatat sebagai GAGAL atau tetap BELUM_LUNAS, tapi biarkan dulu untuk sementara.
}

if ($status_pembayaran_baru === 'LUNAS') {
    // 1. Cek tabel pembayaran
    $stmtCek = $koneksi->prepare("SELECT id_booking, status_pembayaran FROM pembayaran WHERE kode_pembayaran = ?");
    $stmtCek->bind_param("s", $order_id);
    $stmtCek->execute();
    $resCek = $stmtCek->get_result();
    
    if ($resCek->num_rows > 0) {
        $rowPem = $resCek->fetch_assoc();
        $id_booking = $rowPem['id_booking'];
        
        // Pastikan belum LUNAS agar komisi tidak terhitung dobel!
        if ($rowPem['status_pembayaran'] !== 'LUNAS') {
            $koneksi->begin_transaction();
            try {
                // Update pembayaran menjadi LUNAS
                $stmtUpdate = $koneksi->prepare("UPDATE pembayaran SET status_pembayaran = 'LUNAS' WHERE kode_pembayaran = ?");
                $stmtUpdate->bind_param("s", $order_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                // Cari id_terapis
                $stmtB = $koneksi->prepare("SELECT id_terapis FROM booking WHERE id_booking = ?");
                $stmtB->bind_param("i", $id_booking);
                $stmtB->execute();
                $resB = $stmtB->get_result();
                $id_terapis = null;
                if ($resB->num_rows > 0) {
                    $id_terapis = $resB->fetch_assoc()['id_terapis'];
                }
                $stmtB->close();
                
                if ($id_terapis) {
                    // Hitung Komisi
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
                        $total_komisi += $total_layanan * ($persen / 100);
                    }
                    $stmtDetail->close();
                    
                    if ($total_komisi > 0) {
                        $stmtKom = $koneksi->prepare("INSERT INTO komisi_terapis (id_booking, id_terapis, nominal_komisi, status_pencairan) VALUES (?, ?, ?, 'BELUM_CAIR')");
                        $stmtKom->bind_param("iid", $id_booking, $id_terapis, $total_komisi);
                        $stmtKom->execute();
                        $stmtKom->close();
                    }
                }
                
                $koneksi->commit();
            } catch (Exception $e) {
                $koneksi->rollback();
                http_response_code(500);
                echo "Error: " . $e->getMessage();
                exit;
            }
        }
    }
    $stmtCek->close();
}

http_response_code(200);
echo "OK";
?>
