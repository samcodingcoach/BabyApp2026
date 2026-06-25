<?php
// config/auth_helper.php

function check_auth($koneksi) {
    // 1. Cek dari Session (Jika diakses via Browser)
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // 2. Cek dari Bearer Token (Header Authorization)
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1]; // Dalam hal ini, token adalah password_hash dari tabel users
            
            // Cek database
            $stmt = $koneksi->prepare("SELECT user_id FROM users WHERE password = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    return $row['user_id'];
                }
                $stmt->close();
            }
        }
    }
    
    return false; // Unauthorized
}
?>
