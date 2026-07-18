<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']); 
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek autentikasi
if (!check_auth($koneksi)) {
    http_response_code(401); 
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); 
    exit();
}

$kode_pencairan = isset($_POST['kode_pencairan']) ? $_POST['kode_pencairan'] : '';

if (empty($kode_pencairan)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode Pencairan wajib disertakan.']);
    exit();
}

// Pastikan transaksi belum ditutup
$check_sql = "SELECT isClosed FROM pencairan WHERE kode_pencairan = ?";
$stmt_check = $koneksi->prepare($check_sql);
$stmt_check->bind_param("s", $kode_pencairan);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi pencairan tidak ditemukan.']);
    exit();
}

$row_check = $result_check->fetch_assoc();
if ((int)$row_check['isClosed'] === 1) {
    echo json_encode(['status' => 'error', 'message' => 'Transaksi pencairan sudah ditutup sebelumnya.']);
    exit();
}
$stmt_check->close();

$bukti_url = null;

// Handle Upload Bukti Transfer (wajib saat closing)
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../images/pencairan/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
    $file_name = "bukti_closing_" . $kode_pencairan . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Format file bukti tidak diizinkan. Hanya menerima JPG, PNG, atau PDF.']);
        exit();
    }
    
    // Maksimal 2MB
    if ($_FILES['bukti']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file bukti terlalu besar. Maksimal 2 MB.']);
        exit();
    }
    
    if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
        $bukti_url = $file_name;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file bukti.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'File bukti wajib diunggah untuk menutup transaksi ini.']);
    exit();
}

// Update table pencairan
$sql = "UPDATE pencairan SET 
            bukti = ?, 
            isClosed = 1, 
            edit_at = NOW() 
        WHERE kode_pencairan = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ss", $bukti_url, $kode_pencairan);

if ($stmt->execute()) {
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Transaksi pencairan berhasil di-closing.'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal melakukan closing transaksi: ' . $stmt->error
    ]);
}

$stmt->close();
$koneksi->close();
?>
