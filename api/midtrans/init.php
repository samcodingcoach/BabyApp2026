<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../midtrans/Midtrans.php';

// Ambil konfigurasi dari database
$serverKey = '';
$clientKey = '';
$isProduction = false; // Hardcode to false untuk testing/sandbox

$sqlMidtrans = "SELECT ServerKey, ClientKey FROM midtrans ORDER BY id_midtrans DESC LIMIT 1";
$resMidtrans = $koneksi->query($sqlMidtrans);

if ($resMidtrans && $resMidtrans->num_rows > 0) {
    $rowMidtrans = $resMidtrans->fetch_assoc();
    $serverKey = $rowMidtrans['ServerKey'];
    $clientKey = $rowMidtrans['ClientKey'];
}

// Konfigurasi Library Midtrans
\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = $isProduction;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>
