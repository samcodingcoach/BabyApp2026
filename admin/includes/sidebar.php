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
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Menu Utama</li>

                        <li>
                            <a href="<?= $base_url ?>/index.php" class="waves-effect"><i class="feather-home"></i><span>Dashboard</span></a>
                        </li>

                        <li>
                            <a href="<?= $base_url ?>/booking/booking.php" class="waves-effect"><i class="feather-calendar"></i><span>Booking Transaksi</span></a>
                        </li>

                        <li class="menu-title">Keuangan</li>
                        <li>
                            <a href="<?= $base_url ?>/komisi/komisi.php" class="waves-effect"><i class="feather-dollar-sign"></i><span>Komisi Terapis</span></a>
                        </li>

                        <li class="menu-title">Master Data</li>
                        
                        <li>
                            <a href="<?= $base_url ?>/member/member.php" class="waves-effect"><i class="feather-users"></i><span>Data Klien / Member</span></a>
                        </li>
                        <li>
                            <a href="<?= $base_url ?>/bayi/bayi.php" class="waves-effect"><i class="feather-user-plus"></i><span>Data Anak / Bayi</span></a>
                        </li>
                        <li>
                            <a href="<?= $base_url ?>/terapis/terapis.php" class="waves-effect"><i class="feather-user-check"></i><span>Data Terapis</span></a>
                        </li>
                        
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect"><i class="feather-list"></i><span>Manajemen Layanan</span></a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="<?= $base_url ?>/layanan/layanan.php">Daftar Layanan</a></li>
                                <li><a href="<?= $base_url ?>/kategori-layanan/kategori-layanan.php">Kategori Layanan</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="<?= $base_url ?>/ongkir/ongkir.php" class="waves-effect"><i class="feather-map-pin"></i><span>Ongkos Kirim</span></a>
                        </li>
                        
                        <li class="menu-title">Pengaturan</li>
                        <li>
                            <a href="<?= $base_url ?>/users/users.php" class="waves-effect"><i class="feather-settings"></i><span>Akun Administrator</span></a>
                        </li>
                        
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
