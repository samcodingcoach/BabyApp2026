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

$id_pencarian = isset($_POST['id_pencarian']) ? (int)$_POST['id_pencarian'] : 0;
$keterangan = $_POST['keterangan'] ?? '';
$user_id = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? null);
$tanggal_transfer = $_POST['tanggal_transfer'] ?? null;
$bank = $_POST['bank'] ?? '';
$biaya_admin = isset($_POST['biaya_admin']) ? (float)$_POST['biaya_admin'] : 0;

if (empty($user_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan.']);
    exit();
}

$is_update = ($id_pencarian > 0);
$kode_pencairan = '';
$bukti_url = '';

if ($is_update) {
    // Cek apakah isClosed = 0
    $sql_check = "SELECT kode_pencairan, isClosed, bukti FROM pencairan WHERE id_pencarian = ?";
    $stmt_check = $koneksi->prepare($sql_check);
    $stmt_check->bind_param("i", $id_pencarian);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($row_check = $result_check->fetch_assoc()) {
        if ($row_check['isClosed'] == 1) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak bisa diubah karena sudah ditutup (closed).']);
            exit();
        }
        $kode_pencairan = $row_check['kode_pencairan'];
        $bukti_url = $row_check['bukti']; // retain old image if not updated
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data pencairan tidak ditemukan.']);
        exit();
    }
    $stmt_check->close();
} else {
    // Generate Kode Pencairan (TP-YYMMDD-001) untuk insert baru
    $date_prefix = date('ymd');
    $kode_prefix = "TP-" . $date_prefix . "-";

    $sql_kode = "SELECT kode_pencairan FROM pencairan WHERE kode_pencairan LIKE ? ORDER BY id_pencarian DESC LIMIT 1";
    $stmt_kode = $koneksi->prepare($sql_kode);
    $search_kode = $kode_prefix . "%";
    $stmt_kode->bind_param("s", $search_kode);
    $stmt_kode->execute();
    $result_kode = $stmt_kode->get_result();

    $next_number = 1;
    if ($row_kode = $result_kode->fetch_assoc()) {
        $last_kode = $row_kode['kode_pencairan'];
        $last_number = (int) substr($last_kode, -3);
        $next_number = $last_number + 1;
    }
    $stmt_kode->close();

    $kode_pencairan = $kode_prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);
}

// Handle Upload Bukti Transfer (jika ada)
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../images/pencairan/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
    $file_name = "bukti_" . $kode_pencairan . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    $allowed_types = ['jpg', 'jpeg', 'pdf'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Format file tidak diizinkan. Hanya menerima JPG atau PDF.']);
        exit();
    }
    
    // Validasi ukuran maksimal 2MB
    if ($_FILES['bukti']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 2 MB.']);
        exit();
    }
    
    if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
        $bukti_url = $file_name;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file bukti.']);
        exit();
    }
}

// Simpan ke database
if ($is_update) {
    $sql = "UPDATE pencairan SET 
                keterangan = ?, 
                tanggal_transfer = ?, 
                bank = ?, 
                biaya_admin = ?, 
                bukti = ?,
                edit_at = NOW() 
            WHERE id_pencarian = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sssdsi", $keterangan, $tanggal_transfer, $bank, $biaya_admin, $bukti_url, $id_pencarian);
} else {
    $sql = "INSERT INTO pencairan 
            (kode_pencairan, keterangan, user_id, created_at, edit_at, bukti, tanggal_transfer, bank, biaya_admin) 
            VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssisssd", 
        $kode_pencairan, 
        $keterangan, 
        $user_id, 
        $bukti_url, 
        $tanggal_transfer, 
        $bank, 
        $biaya_admin
    );
}

if ($stmt->execute()) {
    $inserted_id = $is_update ? $id_pencarian : $stmt->insert_id;
    echo json_encode([
        'status' => 'success', 
        'message' => $is_update ? 'Data pencairan berhasil diupdate' : 'Data pencairan berhasil disimpan',
        'data' => [
            'id_pencarian' => $inserted_id,
            'kode_pencairan' => $kode_pencairan
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
}

$stmt->close();
$koneksi->close();
?>
