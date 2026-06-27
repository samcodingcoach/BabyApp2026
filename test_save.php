<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_POST = [
    'id_member' => 3, 
    'id_member_or_id_bayi' => '', 
    'tanggal_booking' => date('Y-m-d H:i:s', time() + 3600), 
    'id_terapis' => 3, 
    'prioritas' => 0, 
    'tarif_ongkir' => 0, 
    'details' => json_encode([
        ['id_layanan' => 1, 'id_harga_layanan' => 1, 'nominal' => 100000, 'total' => 100000]
    ])
];
require 'api/booking/save.php';
