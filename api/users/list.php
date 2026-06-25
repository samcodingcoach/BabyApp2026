<?php
session_start();
header('Content-Type: application/json');

// Cek apakah request menggunakan method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Gunakan method GET.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

// Cek apakah user sudah login dengan mengecek session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau sesi telah berakhir.',
        'data' => [],
        'count' => 0
    ]);
    exit();
}

// Include koneksi database (naik 2 level ke folder config)
require_once '../../config/koneksi.php';

// Query SQL dari dokumentasi users.md
$query = "
    SELECT
        users.user_id, 
        users.role_id, 
        roles.role_name, 
        roles.description, 
        users.username, 
        users.full_name, 
        users.phone, 
        users.email, 
        users.photo, 
        users.is_active, 
        users.last_login, 
        users.created_at
    FROM
        users
        INNER JOIN
        roles
        ON 
            users.role_id = roles.role_id
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    // Jika eksekusi query gagal
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . mysqli_error($koneksi),
        'data' => [],
        'count' => 0
    ]);
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$count = count($data);

// Jika sukses, kembalikan respons OK (200) dengan data JSON
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Berhasil mengambil data users',
    'data' => $data,
    'count' => $count
]);

mysqli_free_result($result);
mysqli_close($koneksi);
?>
