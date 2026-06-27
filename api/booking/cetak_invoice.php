<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

if (!check_auth($koneksi)) {
    die("Unauthorized access.");
}

$id_booking = $_GET['id_booking'] ?? null;
if (!$id_booking) {
    die("ID Booking tidak disertakan.");
}

// 1. Ambil Data Master
$stmt = $koneksi->prepare("
    SELECT
        pembayaran.id_pembayaran, 
        pembayaran.id_booking as p_id_booking, 
        booking.id_booking,
        booking.kode_booking, 
        booking.tanggal_booking,
        pembayaran.tanggal_bayar, 
        pembayaran.kode_pembayaran, 
        pembayaran.jumlah_bayar, 
        pembayaran.metode_pembayaran, 
        pembayaran.qris_transaction_id, 
        pembayaran.status_pembayaran, 
        pembayaran.va_number,
        pembayaran.qris_image,
        pembayaran.created_at as pembayaran_created_at, 
        member.nama, 
        member.alamat, 
        booking.alamat_baru, 
        member.kecamatan, 
        booking.status_booking, 
        booking.tarif_ongkir, 
        terapis.nama_terapis, 
        member.whatsapp,
        booking.id_member_or_id_bayi,
        bayi.nama_bayi
    FROM
        booking
    LEFT JOIN pembayaran ON pembayaran.id_booking = booking.id_booking
    LEFT JOIN member ON booking.id_member = member.id_member
    LEFT JOIN terapis ON booking.id_terapis = terapis.id_terapis
    LEFT JOIN bayi ON booking.id_member_or_id_bayi = bayi.id_bayi
    WHERE booking.id_booking = ?
");
$stmt->bind_param("i", $id_booking);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Data booking tidak ditemukan.");
}
$booking = $res->fetch_assoc();
$stmt->close();

$is_lunas = (!empty($booking['id_pembayaran']) && $booking['status_pembayaran'] === 'LUNAS');

if (empty($booking['id_pembayaran'])) {
    die("Transaksi ini belum memiliki tagihan pembayaran sehingga invoice tidak dapat dicetak.");
}

$title = $is_lunas ? 'FAKTUR LUNAS' : 'INVOICE TAGIHAN';

// Ambil Profil Usaha
$stmtP = $koneksi->prepare("SELECT nama_usaha, alamat, whatsapp1 FROM profile_usaha LIMIT 1");
$stmtP->execute();
$resP = $stmtP->get_result();
$profile = $resP->fetch_assoc();
$stmtP->close();

$nama_usaha = $profile['nama_usaha'] ?? 'Klinik Terapi';
$alamat_usaha = $profile['alamat'] ?? '-';
$wa_usaha = $profile['whatsapp1'] ?? '-';

$stmtDetail = $koneksi->prepare("
    SELECT
        booking_detail.id_detail_booking, 
        booking_detail.id_booking, 
        booking_detail.kode_booking, 
        booking_detail.id_layanan, 
        booking_detail.id_harga_layanan, 
        layanan.nama_layanan, 
        layanan.durasi_menit, 
        kategori_layanan.nama_kategori, 
        booking_detail.keluhan, 
        booking_detail.nominal, 
        booking_detail.diskon, 
        booking_detail.total
    FROM
        booking_detail
        INNER JOIN layanan ON booking_detail.id_layanan = layanan.id_layanan
        LEFT JOIN kategori_layanan ON layanan.id_kategori_layanan = kategori_layanan.id_kategori_layanan
    WHERE booking_detail.id_booking = ?
");
$stmtDetail->bind_param("i", $id_booking);
$stmtDetail->execute();
$resDetail = $stmtDetail->get_result();
$details = [];
$grandTotal = 0;
while ($row = $resDetail->fetch_assoc()) {
    $details[] = $row;
    $grandTotal += (double)$row['total'];
}
$stmtDetail->close();
$grandTotal += (double)($booking['tarif_ongkir'] ?? 0);

$nama_pasien = $booking['nama_bayi'] ? "Anak: " . $booking['nama_bayi'] : "Diri Sendiri";
$alamat = $booking['alamat_baru'] ? $booking['alamat_baru'] : $booking['alamat'];
if ($booking['kecamatan']) $alamat .= ' (Kec. ' . $booking['kecamatan'] . ')';
$wa = $booking['whatsapp'] ? $booking['whatsapp'] : '-';

// 2. Generate PDF via TCPDF
require_once '../../tcpdf/tcpdf.php';

// Extend TCPDF to remove Header and Footer as requested by new design
class MYPDF extends TCPDF {
    public function Header() {
        // No header
    }

    public function Footer() {
        // No footer
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Terapi');
$pdf->SetTitle($title . ' - ' . $booking['kode_booking']);
$pdf->SetSubject('Invoice');
$pdf->SetKeywords('TCPDF, PDF, invoice, faktur');

$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 10);

$color = $is_lunas ? '#28a745' : '#dc3545';
$status_teks = $is_lunas ? 'LUNAS' : 'BELUM BAYAR';
$jatuh_tempo = $is_lunas ? '-' : 'Hari ini';
$created_at_date = $booking['pembayaran_created_at'] ? date('d M Y', strtotime($booking['pembayaran_created_at'])) : date('d M Y');

$va_or_qris = '-';
if (strpos(strtoupper($booking['metode_pembayaran']), 'QRIS') !== false) {
    if (!empty($booking['qris_image'])) {
        $va_or_qris = '<br><img src="'.$booking['qris_image'].'" width="80" height="80" /><br><span style="font-size:8px;">Scan dengan E-Wallet/M-Banking</span>';
    } else {
        $va_or_qris = 'Gunakan Aplikasi Pembayaran yang mendukung QRIS';
    }
} else if (strpos(strtoupper($booking['metode_pembayaran']), 'VA') !== false) {
    if (!empty($booking['va_number'])) {
        $va_or_qris = '<b>'.$booking['va_number'].'</b>';
    } else {
        $va_or_qris = 'Cek email / riwayat VA'; // Default fallback
    }
}

// Title and Header (Kop)
$html = '
<table cellspacing="0" cellpadding="2" border="0" style="text-align:center; border-bottom: 1px solid #000; margin-bottom: 20px;">
    <tr>
        <td>
            <h2 style="margin:0; font-size: 16pt;">'.strtoupper($nama_usaha).'</h2>
            <div style="font-size: 10pt; color: #333;">
                '.strip_tags($alamat_usaha).' | Telp/WA: '.$wa_usaha.'
            </div>
        </td>
    </tr>
</table>
<br><br>

<h1 style="text-align:center; margin-bottom: 2px;">'.$title.'</h1>
<h3 style="text-align:center; margin-top: 0; font-weight:normal;">'.($booking['kode_pembayaran'] ?: $booking['kode_booking']).'</h3>
<br><br>

<table cellspacing="0" cellpadding="2" border="0">
    <tr>
        <td width="50%">
            Ditagihkan kepada,<br>
            <b>'.$booking['nama'].'</b><br>
            Pasien : '.$nama_pasien.'<br>
            Alamat : <br>
            '.strip_tags($alamat).'<br>
            Whatsapp : '.$wa.'
        </td>
        <td width="50%" align="right">
            Reservasi<br>
            '.date('d M Y, H:i', strtotime($booking['tanggal_booking'])).'<br><br>
            <b style="color:'.$color.'; font-size:12pt; font-style:italic;">'.$status_teks.'</b>
        </td>
    </tr>
</table>
<br><br>

<h3 style="margin-bottom: 10px;">Detail Layanan</h3>
<table cellspacing="0" cellpadding="5" border="1" style="border-color:#000;">
    <tr style="background-color:#f4f4f4; font-weight:bold;">
        <th width="5%" align="center">No</th>
        <th width="45%">Layanan – Keluhan</th>
        <th width="15%" align="right">Harga</th>
        <th width="15%" align="right">Diskon</th>
        <th width="20%" align="right">Total</th>
    </tr>';

$no = 1;
foreach ($details as $d) {
    $keluhan = $d['keluhan'] ? '<br>Keluhan:<br>'.$d['keluhan'] : '';
    $kategori = $d['nama_kategori'] ? $d['nama_kategori'].'<br>&nbsp;&nbsp;' : '';
    $durasi = $d['durasi_menit'] ? ' - '.$d['durasi_menit'].' menit' : '';
    $html .= '
    <tr>
        <td align="center">'.$no++.'</td>
        <td>'.$kategori.$d['nama_layanan'].$durasi.$keluhan.'</td>
        <td align="right">Rp '.number_format($d['nominal'], 0, ',', '.').'</td>
        <td align="right">Rp '.number_format($d['diskon'], 0, ',', '.').'</td>
        <td align="right">Rp '.number_format($d['total'], 0, ',', '.').'</td>
    </tr>';
}

$html .= '
    <tr>
        <td colspan="4" align="right">Ongkos Kirim</td>
        <td align="right">Rp '.number_format($booking['tarif_ongkir'], 0, ',', '.').'</td>
    </tr>
    <tr>
        <td colspan="4" align="right">Grand Total</td>
        <td align="right"><b>Rp '.number_format($grandTotal, 0, ',', '.').'</b></td>
    </tr>
</table>
<br><br>

<h3 style="margin-bottom: 10px;">Pembayaran</h3>
<table cellspacing="0" cellpadding="2" border="0">
    <tr>
        <td width="25%"><b>Kode Booking</b></td>
        <td width="3%">:</td>
        <td width="72%">'.$booking['kode_booking'].'</td>
    </tr>
    <tr>
        <td><b>Metode Pembayaran</b></td>
        <td>:</td>
        <td>'.($booking['metode_pembayaran'] ? strtoupper($booking['metode_pembayaran']) : '-').'</td>
    </tr>';

if ($is_lunas) {
    $html .= '
    <tr>
        <td><b>Tanggal Pembayaran</b></td>
        <td>:</td>
        <td>'.($booking['tanggal_bayar'] ? date('d M Y, H:i', strtotime($booking['tanggal_bayar'])) : '-').'</td>
    </tr>';
}

if (strpos(strtoupper($booking['metode_pembayaran']), 'VA') !== false) {
    $html .= '
    <tr>
        <td><b>Nomor Virtual Account</b></td>
        <td>:</td>
        <td><i style="color:#111; font-weight:bold;">'.$va_or_qris.'</i></td>
    </tr>';
}

$html .= '
</table>

<br><br><br>
<table border="0" cellpadding="2">
    <tr>
        <td width="60%"></td>
        <td width="40%">
            Hormat kami,<br>
            Samarinda, '.$created_at_date.'<br><br><br><br><br>
            Terapis<br>
            <b>'.$booking['nama_terapis'].'</b>
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('Invoice_'.$booking['kode_booking'].'.pdf', 'D'); // D = Download
?>
