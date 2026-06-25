<?php
session_start();

// Hapus semua variabel session
session_unset();

// Hancurkan session
session_destroy();

// Redirect kembali ke halaman login admin
header("Location: login-admin.php");
exit();
?>
