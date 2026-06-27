<?php
require 'c:/xampp/htdocs/terapi/config/koneksi.php';
$res = $koneksi->query("
    SELECT bd.total, lh.komisi_persentase 
    FROM booking_detail bd
    LEFT JOIN layanan_harga lh ON bd.id_harga_layanan = lh.id_harga_layanan
    JOIN booking b ON bd.id_booking = b.id_booking
    WHERE b.kode_booking = 'B260628-001'
");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
