<?php
session_start();
// Proteksi halaman admin, harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Role Sistem</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        .back-link { text-decoration: none; color: #0056b3; font-weight: bold; margin-right: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Role / Hak Akses</h2>
    <a href="../users/users.php" class="back-link">&larr; Kembali ke Users</a>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>

    <!-- TABEL DATA ROLE -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID Role</th>
                <th style="width: 30%;">Nama Role</th>
                <th style="width: 60%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody id="roleTableBody">
            <tr><td colspan="3" style="text-align: center;">Loading data...</td></tr>
        </tbody>
    </table>
</div>

<script>
// Panggil list role segera setelah halaman diload
window.onload = () => {
    fetchRolesList();
};

async function fetchRolesList() {
    try {
        // Melakukan HTTP Request ke API Role
        const response = await fetch('../../api/role/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('roleTableBody');
        tbody.innerHTML = ''; // Kosongkan state loading
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Belum ada data Role di dalam database.</td></tr>';
                return;
            }
            
            // Looping dan cetak tr td
            result.data.forEach(role => {
                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${role.role_id}</td>
                        <td><strong>${role.role_name}</strong></td>
                        <td>${role.description || '-'}</td>
                    </tr>
                `;
            });
        } else {
            // Jika status = error dari JSON (misal token kadaluarsa / gagal auth)
            tbody.innerHTML = `<tr><td colspan="3" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Gagal mengambil data role:', error);
        document.getElementById('roleTableBody').innerHTML = '<tr><td colspan="3" style="text-align: center;">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}
</script>

</body>
</html>
