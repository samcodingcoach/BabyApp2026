<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Proteksi halaman
if (!check_auth($koneksi)) {
    echo "Akses ditolak. Silakan login terlebih dahulu.";
    exit();
}

$kode = isset($_GET['kode']) ? $_GET['kode'] : '';

if (empty($kode)) {
    echo "Kode Pencairan tidak ditemukan.";
    exit();
}

// Ambil Info Transaksi
$sql = "SELECT p.*, u.full_name as admin_name 
        FROM pencairan p 
        LEFT JOIN users u ON p.user_id = u.user_id 
        WHERE p.kode_pencairan = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $kode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Transaksi pencairan tidak ditemukan.";
    exit();
}

$info = $result->fetch_assoc();
$id_pencarian = $info['id_pencarian'];

// Ambil Rincian Komisi
$sql_detail = "
    SELECT 
        pd.id_detail_pencairan, 
        pd.nominal,
        b.kode_booking,
        t.nama_terapis,
        t.kode_terapis
    FROM pencairan_detail pd
    JOIN komisi_terapis kt ON pd.id_komisi = kt.id_komisi
    JOIN booking b ON kt.id_booking = b.id_booking
    JOIN terapis t ON kt.id_terapis = t.id_terapis
    WHERE pd.id_pencarian = ?
";
$stmt_detail = $koneksi->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_pencarian);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

$details = [];
$nama_terapis = "-";
$kode_terapis = "-";
$subtotal = 0;

while ($row = $result_detail->fetch_assoc()) {
    $details[] = $row;
    $subtotal += (float)$row['nominal'];
    $nama_terapis = $row['nama_terapis'];
    $kode_terapis = $row['kode_terapis'];
}

$biaya_admin = (float)$info['biaya_admin'];
$total_bersih = $subtotal - $biaya_admin;

function rp($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Ambil Info Usaha
$sql_usaha = "SELECT * FROM profile_usaha LIMIT 1";
$res_usaha = $koneksi->query($sql_usaha);
$usaha = $res_usaha->fetch_assoc();
$alamat_usaha = strip_tags($usaha['alamat'] ?? '');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Bukti - <?= htmlspecialchars($kode) ?></title>
    <style>
        /* Desain Formal Kertas A4/A5 */
        @page { size: auto; margin: 10mm; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header p {
            margin: 0 0 3px 0;
            font-size: 11pt;
        }
        .header .contact-info {
            font-size: 10pt;
            color: #333;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px 6px; /* Super compact padding */
        }
        .data-table th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            text-align: center;
            width: 50%;
        }
        .signature-line {
            display: inline-block;
            width: 180px;
            border-bottom: 1px solid #000;
            margin-top: 60px; /* Space for signature */
        }
        .notes {
            font-size: 11pt;
            font-style: italic;
            border: 1px dashed #000;
            padding: 5px 8px;
            margin-bottom: 15px;
        }
        .btn-print {
            text-align: center;
            margin-top: 30px;
        }
        .btn-print button {
            padding: 8px 16px;
            font-size: 11pt;
            cursor: pointer;
            border: 1px solid #000;
            background: #e0e0e0;
        }
        @media print {
            .btn-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h2><?= htmlspecialchars($usaha['nama_usaha'] ?? 'KLINIK TERAPI') ?></h2>
        <p><?= htmlspecialchars($alamat_usaha) ?></p>
        <p class="contact-info">
            <?php if(!empty($usaha['whatsapp1'])): ?>WA: <?= htmlspecialchars($usaha['whatsapp1']) ?> <?php endif; ?>
            <?php if(!empty($usaha['ig'])): ?> | IG: <?= htmlspecialchars($usaha['ig']) ?> <?php endif; ?>
        </p>
        <h3 style="margin-top: 15px; margin-bottom: 0; text-decoration: underline; font-size: 14pt;">BUKTI PENCAIRAN KOMISI</h3>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>No. Transaksi</strong></td>
            <td width="35%">: <?= htmlspecialchars($kode) ?></td>
            <td width="15%"><strong>Tanggal</strong></td>
            <td width="35%">: <?= date('d/m/Y', strtotime($info['tanggal_transfer'])) ?></td>
        </tr>
        <tr>
            <td><strong>Dibayarkan Ke</strong></td>
            <td>: <?= htmlspecialchars($nama_terapis) ?> (<?= htmlspecialchars($kode_terapis) ?>)</td>
            <td><strong>Transfer Dari</strong></td>
            <td>: <?= htmlspecialchars($info['bank']) ?></td>
        </tr>
        <tr>
            <td><strong>Rekening Tujuan</strong></td>
            <td>: <?= htmlspecialchars($info['no_rek'] ?? '-') ?></td>
            <td><strong>Atas Nama</strong></td>
            <td>: <?= htmlspecialchars($info['an_rek'] ?? '-') ?></td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>: <?= ((int)$info['isClosed'] === 1) ? 'LUNAS / CLOSED' : 'PENDING' ?></td>
            <td><strong>Admin</strong></td>
            <td>: <?= htmlspecialchars($info['admin_name']) ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="55%" class="text-left">Rincian (Kode Booking)</th>
                <th width="40%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($details) > 0): ?>
                <?php foreach ($details as $index => $row): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                        <td class="text-right"><?= rp($row['nominal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">Belum ada rincian.</td>
                </tr>
            <?php endif; ?>
            
            <tr>
                <td colspan="2" class="text-right text-bold">Subtotal</td>
                <td class="text-right text-bold"><?= rp($subtotal) ?></td>
            </tr>
            <tr>
                <td colspan="2" class="text-right">Biaya Admin</td>
                <td class="text-right">- <?= rp($biaya_admin) ?></td>
            </tr>
            <tr>
                <td colspan="2" class="text-right text-bold" style="font-size: 13pt;">TOTAL BERSIH (DITERIMA)</td>
                <td class="text-right text-bold" style="font-size: 13pt;"><?= rp($total_bersih) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($info['keterangan'])): ?>
        <div class="notes">
            <strong>Keterangan Tambahan:</strong> <?= nl2br(htmlspecialchars($info['keterangan'])) ?>
        </div>
    <?php endif; ?>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Admin</strong>
                <br>
                <div class="signature-line"></div>
                <br>
                ( <?= htmlspecialchars($info['admin_name']) ?> )
            </td>
            <td>
                Penerima,<br>
                <strong>Terapis</strong>
                <br>
                <div class="signature-line"></div>
                <br>
                ( <?= htmlspecialchars($nama_terapis) ?> )
            </td>
        </tr>
    </table>

    <div class="btn-print">
        <button onclick="window.print()">[ Cetak Struk ]</button>
    </div>

</div>
</body>
</html>
