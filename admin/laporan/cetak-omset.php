<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

require_once '../../config/koneksi.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$id_terapis = $_GET['id_terapis'] ?? null;

// Query yang sama persis dengan API Omset
$where = "DATE(pembayaran.tanggal_bayar) >= ? AND DATE(pembayaran.tanggal_bayar) <= ?";
$params = [$start_date, $end_date];
$types = "ss";

$terapis_name_display = "Semua Terapis";

if ($id_terapis) {
    $where .= " AND booking.id_terapis = ?";
    $params[] = $id_terapis;
    $types .= "i";
    
    $qt = $koneksi->prepare("SELECT nama_terapis FROM terapis WHERE id_terapis = ?");
    $qt->bind_param("i", $id_terapis);
    $qt->execute();
    $rt = $qt->get_result();
    if($rt->num_rows > 0) {
        $terapis_name_display = $rt->fetch_assoc()['nama_terapis'];
    }
}

$sql = "
    SELECT
        pembayaran.id_pembayaran, 
        pembayaran.id_booking, 
        pembayaran.tanggal_bayar, 
        pembayaran.kode_pembayaran, 
        pembayaran.jumlah_bayar, 
        pembayaran.metode_pembayaran, 
        pembayaran.status_pembayaran, 
        booking.id_terapis, 
        terapis.nama_terapis
    FROM
        pembayaran
        INNER JOIN
        booking
        ON 
            pembayaran.id_booking = booking.id_booking
        INNER JOIN
        terapis
        ON 
            booking.id_terapis = terapis.id_terapis
    WHERE $where
    ORDER BY pembayaran.tanggal_bayar DESC
";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$total_omset = 0;
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_omset += $row['jumlah_bayar'];
}

function formatTanggal($tanggal) {
    return date('d/m/Y H:i', strtotime($tanggal));
}
function formatRp($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Omset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        .info-table {
            margin-bottom: 20px;
            width: 100%;
        }
        .info-table td {
            padding: 3px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .data-table td.text-right, .data-table th.text-right {
            text-align: right;
        }
        .data-table td.text-center, .data-table th.text-center {
            text-align: center;
        }
        .summary-box {
            float: right;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 5px;
            min-width: 200px;
        }
        .summary-box h4 {
            margin: 0 0 10px 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #007bff; color: #fff; border: none; border-radius: 4px;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #6c757d; color: #fff; border: none; border-radius: 4px; margin-left: 10px;">Tutup</button>
    </div>

    <div class="header">
        <h2>Laporan Omset Layanan</h2>
        <p>Periode: <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?></p>
    </div>

    <table class="info-table">
        <tr>
            <td width="120"><strong>Terapis</strong></td>
            <td width="10">:</td>
            <td><?= htmlspecialchars($terapis_name_display) ?></td>
        </tr>
        <tr>
            <td><strong>Tgl Cetak</strong></td>
            <td>:</td>
            <td><?= date('d M Y H:i') ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" width="50">No.</th>
                <th>Tanggal Bayar</th>
                <th>Kode Pembayaran</th>
                <th>Terapis</th>
                <th>Metode Bayar</th>
                <th class="text-center">Status</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data)): ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada transaksi pada periode ini</td>
            </tr>
            <?php else: ?>
                <?php foreach($data as $i => $d): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= formatTanggal($d['tanggal_bayar']) ?></td>
                    <td><?= $d['kode_pembayaran'] ?: '-' ?></td>
                    <td><?= $d['nama_terapis'] ?: '-' ?></td>
                    <td><?= $d['metode_pembayaran'] ?: '-' ?></td>
                    <td class="text-center"><?= $d['status_pembayaran'] ?></td>
                    <td class="text-right"><?= formatRp($d['jumlah_bayar']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <h4>Ringkasan</h4>
        <div class="summary-row">
            <span>Total Transaksi</span>
            <span><?= count($data) ?></span>
        </div>
        <div class="summary-row">
            <span>Total Omset</span>
            <span><?= formatRp($total_omset) ?></span>
        </div>
    </div>
    <div style="clear: both;"></div>

    <script>
        // Otomatis muncul dialog print saat halaman diakses (Opsional)
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
