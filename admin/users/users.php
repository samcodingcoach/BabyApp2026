<?php
session_start();
// Proteksi halaman admin, harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">Manajemen Users</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Sistem</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Daftar Pengguna Sistem</h4>
                    <div>
                        <a href="../role/role.php" class="btn btn-info waves-effect waves-light font-weight-bold mr-2"><i class="mdi mdi-shield-account mr-1"></i> Atur Hak Akses (Role)</a>
                        <button onclick="showFormAdd()" class="btn btn-success waves-effect waves-light font-weight-bold">
                            <i class="mdi mdi-plus mr-1"></i> Tambah User Baru
                        </button>
                    </div>
                </div>

                <!-- FORM TAMBAH / EDIT -->
                <div id="formContainer" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Tambah User</h5>
                    <form id="userForm" onsubmit="saveUser(event)">
                        <!-- Hidden input untuk menentukan ini update atau insert -->
                        <input type="hidden" name="user_id" id="user_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Username</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="username" id="username" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Full Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="full_name" id="full_name" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Role</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="role_id" id="role_id" required>
                                            <option value="">-- Loading Roles --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Password</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" name="password" id="password">
                                        <small class="form-text text-muted" id="password_note">Wajib diisi untuk user baru.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Phone</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="phone" id="phone">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" class="form-control" name="email" id="email">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Photo</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="photo" id="photo" accept="image/jpeg, image/png, image/webp">
                                        <small class="form-text text-muted">Maksimal 500KB (JPG/PNG/WEBP).</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" name="is_active" id="is_active">
                                            <option value="1">Aktif</option>
                                            <option value="0" class="text-danger">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan User</button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="text-align: center;">Photo</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Phone / Email</th>
                                <th style="text-align: center;">Status</th>
                                <th style="width: 10%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <tr><td colspan="8" class="text-center">Loading data...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let usersData = [];

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
        const response = await fetch('../../api/users/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            usersData = result.data;
            if (usersData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Belum ada data users.</td></tr>';
                return;
            }
            
            usersData.forEach((user, index) => {
                const photoSrc = user.photo ? `../../images/${user.photo}` : '';
                const photoHtml = photoSrc ? `<img src="${photoSrc}" class="rounded-circle avatar-sm object-cover" style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #ddd;">` : '<div class="avatar-sm d-inline-block"><span class="avatar-title rounded-circle bg-light text-dark font-size-12 border">No Img</span></div>';
                const statusHtml = user.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="text-center align-middle">${photoHtml}</td>
                        <td class="align-middle"><strong>${user.username}</strong></td>
                        <td class="align-middle">${user.full_name}</td>
                        <td class="align-middle">${user.role_name || '-'}</td>
                        <td class="align-middle">
                            ${user.phone || '-'}<br>
                            <small class="text-muted">${user.email || '-'}</small>
                        </td>
                        <td class="text-center align-middle">${statusHtml}</td>
                        <td class="text-center align-middle">
                            <button onclick="editUser(${user.user_id})" class="btn btn-sm btn-info waves-effect waves-light mb-1"><i class="mdi mdi-pencil"></i> Edit</button>
                            ${user.is_active == 1 ? `<button onclick="nonactiveUser(${user.user_id})" class="btn btn-sm btn-danger waves-effect waves-light mb-1"><i class="mdi mdi-block-helper"></i> Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('userTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Terjadi kesalahan jaringan.</td></tr>';
    }
}

function showFormAdd() {
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Tambah User Baru';
    document.getElementById('userForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_note').innerText = 'Wajib diisi untuk user baru.';
}

function hideForm() {
    $('#formContainer').fadeOut();
    document.getElementById('userForm').reset();
}

function editUser(id) {
    const user = usersData.find(u => u.user_id == id);
    if (!user) return;
    
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Edit User';
    
    document.getElementById('user_id').value = user.user_id;
    document.getElementById('username').value = user.username;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('role_id').value = user.role_id;
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('is_active').value = user.is_active;
    
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_note').innerText = 'Kosongkan jika tidak ingin mengganti password.';
    
    document.getElementById('photo').value = '';
}

async function saveUser(e) {
    e.preventDefault();
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    
    const userId = formData.get('user_id');
    const url = userId ? '../../api/users/update.php' : '../../api/users/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchUsers(); 
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
            fetchUsers(); 
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}
</script>
