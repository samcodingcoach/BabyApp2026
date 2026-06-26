<?php
// Tentukan Base URL
$base_url = "/terapi/admin";
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

    <!-- App css -->
    <link href="<?= $base_url ?>/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url ?>/assets/css/theme.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Custom CSS (Opsional) -->
    <style>
        .page-content { padding-top: 70px; }
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
                            <img class="rounded-circle header-profile-user" src="<?= $base_url ?>/assets/images/users/avatar-3.jpg" alt="Header Avatar">
                            <span class="d-none d-sm-inline-block ml-1">Administrator</span>
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
