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

// Cek apakah user sudah login (Session Browser ATAU Token Bearer)
$auth_user_id = check_auth($koneksi);
if (!$auth_user_id) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Anda belum login atau token telah berakhir.'
    ]);
    exit();
}

// Ambil Input dari form-data maupun RAW JSON
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE) ?? [];

$role_id = $_POST['role_id'] ?? ($input['role_id'] ?? '');
$username = $_POST['username'] ?? ($input['username'] ?? '');
$password = $_POST['password'] ?? ($input['password'] ?? '');
$full_name = $_POST['full_name'] ?? ($input['full_name'] ?? '');
$phone = $_POST['phone'] ?? ($input['phone'] ?? '');
$email = $_POST['email'] ?? ($input['email'] ?? '');
$is_active = $_POST['is_active'] ?? ($input['is_active'] ?? 1);
$base64_photo = $input['photo'] ?? null; // Khusus untuk payload RAW JSON (base64 string)

if (empty($role_id) || empty($username) || empty($password) || empty($full_name)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Data wajib (role_id, username, password, full_name) harus diisi.'
    ]);
    exit();
}

// Cek duplikasi: username, phone, email
$checkQuery = "SELECT username, phone, email FROM users WHERE username = ? OR (phone = ? AND phone != '') OR (email = ? AND email != '')";
$stmtCheck = $koneksi->prepare($checkQuery);
if (!$stmtCheck) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query error (check): ' . $koneksi->error]);
    exit();
}

$stmtCheck->bind_param("sss", $username, $phone, $email);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

while ($row = $resultCheck->fetch_assoc()) {
    if (strtolower($row['username']) === strtolower($username)) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh user lain.']);
        exit();
    }
    if (!empty($phone) && $row['phone'] === $phone) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Nomor telepon sudah digunakan oleh user lain.']);
        exit();
    }
    if (!empty($email) && strtolower($row['email']) === strtolower($email)) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Email sudah digunakan oleh user lain.']);
        exit();
    }
}
$stmtCheck->close();


// Validasi Foto (Mendukung Form-Data atau Base64 Raw JSON)
$photo_is_form_data = false;
$photo_is_base64 = false;
$decoded_base64_data = null;

// Skenario 1: Upload via form-data (multipart/form-data)
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileSize = $_FILES['photo']['size'];
    $fileType = mime_content_type($fileTmpPath);

    if ($fileSize > 512000) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ukuran foto maksimal adalah 500KB.']);
        exit();
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format foto harus berupa JPG, PNG, atau WEBP.']);
        exit();
    }
    $photo_is_form_data = true;

// Skenario 2: Upload via Raw JSON Body (Base64 string)
} elseif (!empty($base64_photo)) {
    // Ekstrak base64 murni jika ada Data URI (contoh: "data:image/jpeg;base64,.....")
    $pure_base64 = $base64_photo;
    if (preg_match('/^data:image\/(\w+);base64,/', $base64_photo, $type)) {
        $pure_base64 = substr($base64_photo, strpos($base64_photo, ',') + 1);
        $fileTypeStr = strtolower($type[1]);
        if (!in_array($fileTypeStr, ['jpeg', 'jpg', 'png', 'webp'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Format foto base64 harus JPG, PNG, atau WEBP.']);
            exit();
        }
    }
    
    $decoded_base64_data = base64_decode($pure_base64);
    if ($decoded_base64_data === false) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format base64 foto tidak valid.']);
        exit();
    }
    if (strlen($decoded_base64_data) > 512000) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ukuran foto (hasil decode base64) maksimal 500KB.']);
        exit();
    }
    $photo_is_base64 = true;
}


// 1. Hash Password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 2. Insert ke Database (Photo diisi null dulu, akan di-update setelah kita dapat user_id)
$insertQuery = "INSERT INTO users (role_id, username, password, full_name, phone, email, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmtInsert = $koneksi->prepare($insertQuery);
if (!$stmtInsert) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query error (insert): ' . $koneksi->error]);
    exit();
}

$stmtInsert->bind_param("isssssi", $role_id, $username, $hashed_password, $full_name, $phone, $email, $is_active);

if (!$stmtInsert->execute()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data user: ' . $stmtInsert->error]);
    exit();
}

$new_user_id = $koneksi->insert_id;
$stmtInsert->close();

// 3. Proses Simpan Foto (Dari Form-Data atau Base64) jika ada
$photo_name = null;
if ($photo_is_form_data || $photo_is_base64) {
    $targetDir = '../../images/';
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $photo_name = $new_user_id . '.jpg';
    $targetFilePath = $targetDir . $photo_name;

    $upload_success = false;
    
    if ($photo_is_form_data) {
        $upload_success = move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath);
    } else if ($photo_is_base64) {
        $upload_success = (file_put_contents($targetFilePath, $decoded_base64_data) !== false);
    }

    if ($upload_success) {
        $updatePhotoQuery = "UPDATE users SET photo = ? WHERE user_id = ?";
        $stmtUpdate = $koneksi->prepare($updatePhotoQuery);
        if ($stmtUpdate) {
            $stmtUpdate->bind_param("si", $photo_name, $new_user_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    } else {
        $photo_name = null; 
    }
}

// Response Berhasil
http_response_code(201); 
echo json_encode([
    'status' => 'success',
    'message' => 'User berhasil ditambahkan',
    'data' => [
        'user_id' => $new_user_id,
        'username' => $username,
        'full_name' => $full_name,
        'photo' => $photo_name
    ]
]);

$koneksi->close();
?>
