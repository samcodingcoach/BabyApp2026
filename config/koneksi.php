<?php
$host     = "localhost";
$username = "matos";
$password = "1234";
$database = "klinik-bayi";

// Matikan report error default mysqli agar tidak mengganggu output JSON
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = @mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]);
    exit();
}
?>
