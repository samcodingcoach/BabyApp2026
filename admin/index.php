<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login-admin.php");
    exit();
}
require_once '../config/koneksi.php';

// Ambil statistik sederhana
$today = date('Y-m-d');
$totalBookingHariIni = $koneksi->query("SELECT COUNT(*) as cnt FROM booking WHERE DATE(tanggal_booking) = '$today'")->fetch_assoc()['cnt'] ?? 0;
$totalMember = $koneksi->query("SELECT COUNT(*) as cnt FROM member")->fetch_assoc()['cnt'] ?? 0;
$totalTerapis = $koneksi->query("SELECT COUNT(*) as cnt FROM terapis WHERE is_active=1")->fetch_assoc()['cnt'] ?? 0;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">Dashboard Utama</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Klinik</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-md-4">
        <div class="card bg-primary border-primary">
            <div class="card-body">
                <div class="mb-4">
                    <span class="badge badge-soft-light float-right">Hari Ini</span>
                    <h5 class="card-title mb-0 text-white">Total Booking</h5>
                </div>
                <div class="row d-flex align-items-center mb-4">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0 text-white">
                            <?= $totalBookingHariIni ?> Pesanan
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end col-->

    <div class="col-md-4">
        <div class="card bg-success border-success">
            <div class="card-body">
                <div class="mb-4">
                    <span class="badge badge-soft-light float-right">Semua Waktu</span>
                    <h5 class="card-title mb-0 text-white">Total Klien / Member</h5>
                </div>
                <div class="row d-flex align-items-center mb-4">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center text-white mb-0">
                            <?= $totalMember ?> Orang
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end col-->

    <div class="col-md-4">
        <div class="card bg-warning border-warning">
            <div class="card-body">
                <div class="mb-4">
                    <span class="badge badge-soft-light float-right">Aktif Bekerja</span>
                    <h5 class="card-title mb-0 text-white">Total Terapis</h5>
                </div>
                <div class="row d-flex align-items-center mb-4">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center text-white mb-0">
                            <?= $totalTerapis ?> Terapis
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- end col-->
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Selamat Datang di Sistem Manajemen Klinik</h4>
                <p class="card-subtitle mb-4">Pilih menu di navigasi sebelah kiri untuk mengelola master data atau transaksi.</p>
                <a href="booking/booking.php" class="btn btn-primary waves-effect waves-light">Manajemen Booking</a>
                <a href="terapis/terapis.php" class="btn btn-outline-success waves-effect waves-light">Kelola Terapis</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
