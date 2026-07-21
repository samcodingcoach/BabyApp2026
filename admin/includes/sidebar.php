<?php
// Ambil menu yang diperbolehkan dari API (terlihat = 1)
$dynamic_menus = [];

if (isset($_SESSION['role_id'])) {
    $role_id = $_SESSION['role_id'];
    
    // Ambil dari API (HTTP Request ke Endpoint)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
    $api_url = $protocol . $_SERVER['HTTP_HOST'] . "/terapi/api/menu-level/list.php?role_id=" . $role_id;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $api_data = json_decode($response, true);
        if (isset($api_data['status']) && $api_data['status'] === 'success') {
            foreach ($api_data['data'] as $row) {
                // Hanya ambil yang terlihat = 1 DAN akses = 1
                if (isset($row['terlihat']) && $row['terlihat'] == 1 && isset($row['akses']) && $row['akses'] == 1) {
                    $kategori = !empty($row['kategori_menu']) ? $row['kategori_menu'] : 'Lainnya';
                    if (!isset($dynamic_menus[$kategori])) {
                        $dynamic_menus[$kategori] = [];
                    }
                    $dynamic_menus[$kategori][] = $row;
                }
            }
        }
    }
}

// Icon mapping opsional agar tidak terlalu polos (jika tidak ada akan pakai default)
function getMenuIcon($nama_menu) {
    $icons = [
        'Dashboard' => 'feather-home',
        'Booking Layanan' => 'feather-calendar',
        'Laporan Omset' => 'feather-pie-chart',
        'Laporan Komisi' => 'feather-dollar-sign',
        'Data Member' => 'feather-users',
        'Data Anak / Bayi' => 'feather-user-plus',
        'Data Terapis' => 'feather-user-check',
        'Daftar Layanan' => 'feather-list',
        'Kategori Layanan' => 'feather-grid',
        'Ongkos Kirim' => 'feather-map-pin',
        'Pencairan Terapis'=> 'feather-dollar-sign',
        'Akun Administrator' => 'feather-settings',
        'Atur Midtrans' => 'feather-credit-card',
        'Atur Role' => 'feather-shield',
        'Menu Level' => 'feather-shield'
    ];
    return isset($icons[$nama_menu]) ? $icons[$nama_menu] : 'feather-circle';
}
?>
        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div class="navbar-brand-box">
                    <a href="<?= $base_url ?>/index.php" class="logo">
                        <i class="mdi mdi-hospital-building"></i>
                        <span>Klinik Terapi</span>
                    </a>
                </div>

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        
                        <!-- Dashboard (Selalu ada di Menu Utama) -->
                        <li class="menu-title">Menu Utama</li>
                        <li>
                            <a href="<?= $base_url ?>/index.php" class="waves-effect">
                                <i class="feather-home"></i><span>Dashboard</span>
                            </a>
                        </li>
                        
                        <?php foreach ($dynamic_menus as $kategori => $menus): ?>
                            <?php if ($kategori !== 'Menu Utama'): ?>
                                <li class="menu-title"><?= htmlspecialchars($kategori) ?></li>
                            <?php endif; ?>
                            
                            <?php foreach ($menus as $menu): ?>
                                <?php 
                                    // Bersihkan link untuk menangani typo atau multiple admin/
                                    $link_val = ltrim($menu['link'], '/');
                                    if (strpos($link_val, 'menu-level.php/menu-level.php') !== false) {
                                        $link_val = 'admin/menu-level/menu-level.php'; // Handle typo khusus
                                    }
                                    
                                    if (strpos($link_val, 'http') === 0) {
                                        $link_url = $link_val;
                                    } else {
                                        $clean_link = (strpos($link_val, 'admin/') === 0) ? substr($link_val, 6) : $link_val;
                                        $link_url = $base_url . '/' . $clean_link;
                                    }
                                    
                                    $icon = getMenuIcon($menu['nama_menu']);
                                ?>
                                <li>
                                    <a href="<?= $link_url ?>" class="waves-effect">
                                        <i class="<?= $icon ?>"></i><span><?= htmlspecialchars($menu['nama_menu']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->
        
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
