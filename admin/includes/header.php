<?php
// Tentukan Base URL
$base_url = "/terapi/admin";

// Ambil data user yang sedang login
$user_photo_path = $base_url . "/assets/images/users/avatar-3.jpg"; // Default
$user_fullname = "Administrator";

if (isset($_SESSION['user_id'])) {
    $header_user_id = $_SESSION['user_id'];
    require_once __DIR__ . '/../../config/koneksi.php';
    $header_stmt = $koneksi->prepare("SELECT full_name, photo FROM users WHERE user_id = ?");
    if ($header_stmt) {
        $header_stmt->bind_param("i", $header_user_id);
        $header_stmt->execute();
        $header_res = $header_stmt->get_result();
        if ($header_res->num_rows > 0) {
            $header_user = $header_res->fetch_assoc();
            if (!empty($header_user['full_name'])) {
                $user_fullname = $header_user['full_name'];
            }
            if (!empty($header_user['photo'])) {
                $user_photo_path = "/terapi/images/" . $header_user['photo'];
            }
        }
        $header_stmt->close();
    }
}

// ======== PROTEKSI HALAMAN BERDASARKAN MENU LEVEL ========
if (isset($_SESSION['user_id'])) {
    $current_role = $_SESSION['role_id'];
    $current_path = ltrim(str_replace('/terapi/', '', $_SERVER['SCRIPT_NAME']), '/');

    // Daftar halaman yang selalu boleh diakses tanpa cek menu_level (Whitelist)
    $whitelist = [
        'admin/index.php', 
        'admin/profile-usaha/profile-usaha.php',
    ];

    // Superadmin (role 1) selalu bisa akses Manajemen Menu Level
    if ($current_role == 1) {
        $whitelist[] = 'admin/menu-level/menu-level.php';
    }

    $is_allowed = in_array($current_path, $whitelist);

    if (!$is_allowed) {
        // Cek menggunakan API (HTTP Request ke Endpoint)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
        $api_url = $protocol . $_SERVER['HTTP_HOST'] . "/terapi/api/menu-level/list.php?role_id=" . $current_role;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Hindari hang terlalu lama
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $api_data = json_decode($response, true);
            if (isset($api_data['status']) && $api_data['status'] === 'success') {
                foreach ($api_data['data'] as $menu) {
                    // Hanya izinkan jika terdaftar DAN akses = 1
                    if ($menu['link'] === $current_path && isset($menu['akses']) && $menu['akses'] == 1) {
                        $is_allowed = true;
                        break;
                    }
                }
            }
        }
    }

    if (!$is_allowed) {
        echo "<!DOCTYPE html><html><head>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <script src='/terapi/admin/plugins/sweetalert2/sweetalert2.min.js'></script>
        <link href='/terapi/admin/plugins/sweetalert2/sweetalert2.min.css' rel='stylesheet' type='text/css' />
        <style>body { background-color: #f8f9fa; font-family: sans-serif; }</style>
        </head><body><script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak!',
                text: 'Anda tidak memiliki hak akses untuk halaman ini.',
                confirmButtonText: 'Kembali ke Dashboard',
                allowOutsideClick: false
            }).then(() => {
                window.location.href = '/terapi/admin/index.php';
            });
        </script></body></html>";
        exit();
    }
}
// ======== END PROTEKSI ========
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Sistem Manajemen Klinik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= $base_url ?>/assets/images/favicon.ico">

    <!-- Plugins css -->
    <link href="<?= $base_url ?>/plugins/datatables/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/plugins/datatables/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <!-- Dropify & Quill CSS -->
    <link href="<?= $base_url ?>/plugins/dropify/dropify.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/plugins/quill/quill.snow.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="<?= $base_url ?>/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/assets/css/theme.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Custom CSS (Opsional) -->
    <style>
        .page-content { padding-top: 90px; }
        .page-title-box { margin-bottom: 20px; }
    </style>
</head>
<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex align-items-left">
                    <button type="button" class="btn btn-sm mr-2 d-lg-none px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?= $user_photo_path ?>" alt="Header Avatar" style="object-fit: cover;">
                            <span class="d-none d-sm-inline-block ml-1"><?= htmlspecialchars($user_fullname) ?></span>
                            <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= $base_url ?>/profile-usaha/profile-usaha.php">
                                <span>Pengaturan Klinik</span>
                            </a>
                            <a class="dropdown-item d-flex align-items-center justify-content-between" href="/terapi/logout-admin.php">
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
