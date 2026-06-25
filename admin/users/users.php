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
    <title>Manajemen Users</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; }
        
        .form-container { 
            border: 1px solid #ccc; 
            padding: 15px; 
            margin-bottom: 20px; 
            background: #fafafa;
            display: none; 
        }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 130px; }
        .form-group input, .form-group select { padding: 5px; width: 250px; }
        .note { font-size: 0.85em; color: #666; margin-left: 135px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Users</h2>
    <a href="../../logout-admin.php" style="float: right;">Logout</a>
    <button onclick="showFormAdd()">+ Tambah User</button>

    <!-- FORM TAMBAH / EDIT -->
    <div class="form-container" id="formContainer">
        <h3 id="formTitle">Tambah User</h3>
        <form id="userForm" onsubmit="saveUser(event)">
            <!-- Hidden input untuk menentukan ini update atau insert -->
            <input type="hidden" name="user_id" id="user_id">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="full_name" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role_id" id="role_id" required>
                    <option value="">-- Loading Roles --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password">
                <span class="note" id="password_note">Wajib diisi untuk user baru.</span>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="phone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email">
            </div>
            <div class="form-group">
                <label>Photo</label>
                <input type="file" name="photo" id="photo" accept="image/jpeg, image/png, image/webp">
                <span class="note">Maksimal 500KB (JPG/PNG/WEBP).</span>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            
            <button type="submit">Simpan</button>
            <button type="button" onclick="hideForm()">Batal</button>
        </form>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Phone / Email</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <tr><td colspan="8">Loading data...</td></tr>
        </tbody>
    </table>
</div>

<script>
let usersData = [];

// Saat halaman load, ambil data role dan users dari API
window.onload = () => {
    fetchRoles();
    fetchUsers();
};

async function fetchRoles() {
    try {
        const response = await fetch('../../api/role/list.php');
        const result = await response.json();
        if (result.status === 'success') {
            const select = document.getElementById('role_id');
            select.innerHTML = '<option value="">-- Pilih Role --</option>';
            result.data.forEach(role => {
                select.innerHTML += `<option value="${role.role_id}">${role.role_name}</option>`;
            });
        }
    } catch (error) {
        console.error('Gagal mengambil data role:', error);
    }
}

async function fetchUsers() {
    try {
        // Karena ada $_SESSION, browser akan mengirimkan Session cookie secara otomatis.
        const response = await fetch('../../api/users/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            usersData = result.data;
            if (usersData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8">Belum ada data users.</td></tr>';
                return;
            }
            
            usersData.forEach(user => {
                // Tampilkan foto jika ada
                const photoSrc = user.photo ? `../../images/${user.photo}` : '';
                const photoHtml = photoSrc ? `<img src="${photoSrc}" width="50" style="border-radius:4px;">` : '-';
                const statusHtml = user.is_active == 1 ? '<span style="color:green;">Aktif</span>' : '<span style="color:red;">Nonaktif</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>${photoHtml}</td>
                        <td>${user.username}</td>
                        <td>${user.full_name}</td>
                        <td>${user.role_name || '-'}</td>
                        <td>
                            ${user.phone || '-'}<br>
                            <small>${user.email || '-'}</small>
                        </td>
                        <td>${statusHtml}</td>
                        <td>
                            <button onclick="editUser(${user.user_id})">Edit</button>
                            ${user.is_active == 1 ? `<button onclick="nonactiveUser(${user.user_id})">Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="8" style="color:red;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Gagal mengambil data user:', error);
        document.getElementById('userTableBody').innerHTML = '<tr><td colspan="8">Terjadi kesalahan jaringan.</td></tr>';
    }
}

function showFormAdd() {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Tambah User Baru';
    document.getElementById('userForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_note').innerText = 'Wajib diisi untuk user baru.';
}

function hideForm() {
    document.getElementById('formContainer').style.display = 'none';
    document.getElementById('userForm').reset();
}

function editUser(id) {
    const user = usersData.find(u => u.user_id == id);
    if (!user) return;
    
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Edit User';
    
    // Isi field form dengan data eksisting
    document.getElementById('user_id').value = user.user_id;
    document.getElementById('username').value = user.username;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('role_id').value = user.role_id;
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('is_active').value = user.is_active;
    
    // Password menjadi opsional saat edit
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_note').innerText = 'Kosongkan jika tidak ingin mengganti password.';
    
    // Field photo dikosongkan (jika diisi, nanti akan menimpa foto lama)
    document.getElementById('photo').value = '';
}

async function saveUser(e) {
    e.preventDefault();
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    
    // Tentukan URL: jika user_id ada isinya berarti Update, jika kosong berarti Save (Insert)
    const userId = formData.get('user_id');
    const url = userId ? '../../api/users/update.php' : '../../api/users/save.php';
    
    try {
        // fetch dengan FormData otomatis menyesuaikan headers Content-Type (multipart/form-data)
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchUsers(); // Refresh tabel
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}

async function nonactiveUser(id) {
    if(!confirm('Apakah Anda yakin ingin menonaktifkan akun user ini?')) return;
    
    const formData = new FormData();
    formData.append('user_id', id);
    
    try {
        const response = await fetch('../../api/users/nonactive.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            alert('User berhasil dinonaktifkan.');
            fetchUsers(); // Refresh tabel
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}
</script>

</body>
</html>
