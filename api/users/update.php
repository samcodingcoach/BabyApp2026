<?php
session_start();
header('Content-Type: application/json');

// Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Gunakan method POST.'
    ]);
    exit();
}

require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Cek Otorisasi (Session atau Bearer)
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau token tidak valid.'
    ]);
    exit();
}

// Parsing Input (Bisa dari Form-Data atau RAW JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE) ?? [];

// user_id bersifat wajib sebagai identifier
$user_id = $_POST['user_id'] ?? ($input['user_id'] ?? null);
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter user_id wajib disertakan.']);
    exit();
}

// 1. Dapatkan data user lama
$stmtCheckUser = $koneksi->prepare("SELECT * FROM users WHERE user_id = ?");
if (!$stmtCheckUser) {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]); exit();
}
$stmtCheckUser->bind_param("i", $user_id);
$stmtCheckUser->execute();
$resultCheckUser = $stmtCheckUser->get_result();

if ($resultCheckUser->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data user tidak ditemukan di sistem.']);
    exit();
}

$existing_user = $resultCheckUser->fetch_assoc();
$stmtCheckUser->close();


// 2. Tentukan field baru (Jika kosong di payload, pakai data lama)
$role_id = $_POST['role_id'] ?? ($input['role_id'] ?? $existing_user['role_id']);
$username = $_POST['username'] ?? ($input['username'] ?? $existing_user['username']);
$full_name = $_POST['full_name'] ?? ($input['full_name'] ?? $existing_user['full_name']);
$phone = $_POST['phone'] ?? ($input['phone'] ?? $existing_user['phone']);
$email = $_POST['email'] ?? ($input['email'] ?? $existing_user['email']);
$password = $_POST['password'] ?? ($input['password'] ?? ''); 
$is_active = isset($_POST['is_active']) ? $_POST['is_active'] : (isset($input['is_active']) ? $input['is_active'] : $existing_user['is_active']);

$base64_photo = $input['photo'] ?? null;

if (empty($role_id) || empty($username) || empty($full_name)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'role_id, username, dan full_name tidak boleh kosong.']);
    exit();
}

// 3. Cek Duplikasi ke record lain (Pastikan tidak bentrok dengan user_id lain)
$checkQuery = "SELECT username, phone, email FROM users WHERE (username = ? OR (phone = ? AND phone != '') OR (email = ? AND email != '')) AND user_id != ?";
$stmtCheck = $koneksi->prepare($checkQuery);
$stmtCheck->bind_param("sssi", $username, $phone, $email, $user_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

while ($row = $resultCheck->fetch_assoc()) {
    if (strtolower($row['username']) === strtolower($username)) {
        http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan.']); exit();
    }
    if (!empty($phone) && $row['phone'] === $phone) {
        http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'Nomor telepon sudah digunakan.']); exit();
    }
    if (!empty($email) && strtolower($row['email']) === strtolower($email)) {
        http_response_code(409); echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan.']); exit();
    }
}
$stmtCheck->close();


// 4. Validasi Photo Baru (Jika diunggah)
$photo_is_form_data = false;
$photo_is_base64 = false;
$decoded_base64_data = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['photo']['size'] > 512000) {
        http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Ukuran foto maksimal 500KB.']); exit();
    }
    $fileType = mime_content_type($_FILES['photo']['tmp_name']);
    if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
        http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Format foto harus JPG, PNG, atau WEBP.']); exit();
    }
    $photo_is_form_data = true;
    
} elseif (!empty($base64_photo)) {
    $pure_base64 = $base64_photo;
    if (preg_match('/^data:image\/(\w+);base64,/', $base64_photo, $type)) {
        $pure_base64 = substr($base64_photo, strpos($base64_photo, ',') + 1);
        if (!in_array(strtolower($type[1]), ['jpeg', 'jpg', 'png', 'webp'])) {
            http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Format base64 harus JPG, PNG, atau WEBP.']); exit();
        }
    }
    $decoded_base64_data = base64_decode($pure_base64);
    if ($decoded_base64_data === false || strlen($decoded_base64_data) > 512000) {
        http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Format base64 invalid atau ukuran melebihi 500KB.']); exit();
    }
    $photo_is_base64 = true;
}


// 5. Eksekusi Simpan Foto
$photo_name = $existing_user['photo']; // Jika tidak diupdate, pakai foto lama
if ($photo_is_form_data || $photo_is_base64) {
    $targetDir = '../../images/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    
    $photo_name = $user_id . '.jpg';
    $targetFilePath = $targetDir . $photo_name;
    
    if ($photo_is_form_data) {
        move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath);
    } else {
        file_put_contents($targetFilePath, $decoded_base64_data);
    }
}


// 6. Tentukan Password
$final_password = $existing_user['password'];
if (!empty($password)) { // User menginput password baru
    $final_password = password_hash($password, PASSWORD_DEFAULT);
}


// 7. Proses Update ke Database
$updateQuery = "UPDATE users SET role_id = ?, username = ?, password = ?, full_name = ?, phone = ?, email = ?, photo = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
$stmtUpdate = $koneksi->prepare($updateQuery);

if (!$stmtUpdate) {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => 'Error update query: ' . $koneksi->error]); exit();
}

$stmtUpdate->bind_param("issssssii", $role_id, $username, $final_password, $full_name, $phone, $email, $photo_name, $is_active, $user_id);

if (!$stmtUpdate->execute()) {
    http_response_code(500); echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data: ' . $stmtUpdate->error]); exit();
}

$stmtUpdate->close();
$koneksi->close();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data user berhasil diupdate',
    'data' => [
        'user_id' => $user_id,
        'username' => $username,
        'full_name' => $full_name,
        'photo' => $photo_name
    ]
]);
?>
