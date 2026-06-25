<?php
session_start();
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Sesuaikan dengan halaman dashboard Anda
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Klinik Bayi</title>
</head>
<body>
    <h2>Form Login Admin</h2>
    
    <div id="alertMessage" style="display:none; padding:10px; margin-bottom:10px; border:1px solid #ccc;"></div>

    <form id="loginForm">
        <div>
            <label for="username">Username:</label>
            <br>
            <input type="text" id="username" name="username" required autocomplete="username">
        </div>
        <br>
        <div>
            <label for="password">Password:</label>
            <br>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <br>
        <button type="submit" id="btnSubmit">Login</button>
    </form>

    <script>
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const alertMessage = document.getElementById('alertMessage');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Loading...';
            
            alertMessage.style.display = 'none';
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
                    alertMessage.style.borderColor = 'green';
                    alertMessage.style.color = 'green';
                    alertMessage.textContent = 'Login berhasil! Mengalihkan...';
                    
                    setTimeout(() => {
                        window.location.href = 'index.php'; // Ganti dengan halaman dashboard
                    }, 1000);
                } else {
                    alertMessage.style.borderColor = 'red';
                    alertMessage.style.color = 'red';
                    alertMessage.textContent = result.message || 'Login gagal.';
                    
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Login';
                }
            } catch (error) {
                console.error('Error:', error);
                
                alertMessage.style.display = 'block';
                alertMessage.style.borderColor = 'red';
                alertMessage.style.color = 'red';
                alertMessage.textContent = 'Terjadi kesalahan sistem.';
                
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Login';
            }
        });
    </script>
</body>
</html>
