<?php
session_start();
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php"); // Sesuaikan dengan halaman dashboard Anda
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Login Admin - Klinik Terapi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="admin/assets/images/favicon.ico">

    <!-- App css -->
    <link href="admin/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="admin/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="admin/assets/css/theme.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="w-100 d-block bg-white shadow-lg rounded my-5">
                            <div class="row">
                                <div class="col-lg-5 d-none d-lg-block bg-login rounded-left"></div>
                                <div class="col-lg-7">
                                    <div class="p-5">
                                        <div class="text-center mb-5">
                                            <a href="#" class="text-dark font-size-22 font-family-secondary">
                                                <i class="mdi mdi-hospital-building"></i> <b>Klinik Terapi</b>
                                            </a>
                                        </div>
                                        <h1 class="h5 mb-1">Selamat Datang!</h1>
                                        <p class="text-muted mb-4">Masukkan username dan password untuk masuk ke panel admin.</p>
                                        
                                        <div id="alertMessage" class="alert" style="display:none;" role="alert"></div>

                                        <form id="loginForm" class="user">
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-user" id="username" name="username" required autocomplete="username" placeholder="Username">
                                            </div>
                                            <div class="form-group">
                                                <input type="password" class="form-control form-control-user" id="password" name="password" required autocomplete="current-password" placeholder="Password">
                                            </div>
                                            <button type="submit" id="btnSubmit" class="btn btn-success btn-block waves-effect waves-light"> Log In </button>
                                        </form>

                                    </div> <!-- end .padding-5 -->
                                </div> <!-- end col -->
                            </div> <!-- end row -->
                        </div> <!-- end .w-100 -->
                    </div> <!-- end .d-flex -->
                </div> <!-- end col-->
            </div> <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <!-- jQuery  -->
    <script src="admin/assets/js/jquery.min.js"></script>
    <script src="admin/assets/js/bootstrap.bundle.min.js"></script>
    <script src="admin/assets/js/metismenu.min.js"></script>
    <script src="admin/assets/js/waves.js"></script>
    <script src="admin/assets/js/simplebar.min.js"></script>

    <!-- App js -->
    <script src="admin/assets/js/theme.js"></script>

    <!-- JS Logic Login -->
    <script>
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const alertMessage = document.getElementById('alertMessage');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Loading...';
            
            alertMessage.style.display = 'none';
            alertMessage.className = 'alert';
            alertMessage.textContent = '';
            
            const formData = new FormData(loginForm);
            
            try {
                const response = await fetch('config/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                alertMessage.style.display = 'block';
                
                if (response.ok && result.status === 'success') {
                    alertMessage.classList.add('alert-success');
                    alertMessage.textContent = 'Login berhasil! Mengalihkan...';
                    
                    setTimeout(() => {
                        window.location.href = 'admin/index.php'; // Ganti dengan halaman dashboard
                    }, 1000);
                } else {
                    alertMessage.classList.add('alert-danger');
                    alertMessage.textContent = result.message || 'Login gagal.';
                    
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Log In';
                }
            } catch (error) {
                console.error('Error:', error);
                
                alertMessage.style.display = 'block';
                alertMessage.classList.add('alert-danger');
                alertMessage.textContent = 'Terjadi kesalahan sistem.';
                
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Log In';
            }
        });
    </script>
</body>
</html>
