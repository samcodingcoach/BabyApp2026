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

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
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
                            <!-- Data akan dimuat dengan JS -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Tambah User</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="userForm" onsubmit="saveUser(event)">
                <div class="modal-body">
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
                                    <select class="form-control select2" name="role_id" id="role_id" required style="width: 100%;">
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
                                    <input type="file" class="dropify" name="photo" id="photo" accept="image/jpeg, image/png, image/webp">
                                    <small class="form-text text-muted">Maksimal 500KB (JPG/PNG/WEBP).</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Status</label>
                                <div class="col-sm-8">
                                    <select class="form-control font-weight-bold select2" name="is_active" id="is_active" style="width: 100%;">
                                        <option value="1">Aktif</option>
                                        <option value="0" class="text-danger">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let usersData = [];
let dataTable = null;

window.onload = () => {
    // Inisialisasi plugin select2 (jika diperlukan)
    if($().select2) {
        $('.select2').select2({
            dropdownParent: $('#formModal')
        });
    }

    $('.dropify').dropify();
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
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            usersData = result.data;
            
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
            Swal.fire('Error', result.message, 'error');
        }
        
        // Re-init DataTable
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data users."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan saat memuat data.', 'error');
    }
}

function showFormAdd() {
    document.getElementById('userForm').reset();
    document.getElementById('formTitle').innerText = 'Tambah User Baru';
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_note').innerText = 'Wajib diisi untuk user baru.';
    
    // Reset Select2 jika ada
    if($().select2) {
        $('#role_id').val('').trigger('change');
        $('#is_active').val('1').trigger('change');
    }

    $('.dropify-clear').click();

    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
    document.getElementById('userForm').reset();
}

function editUser(id) {
    const user = usersData.find(u => u.user_id == id);
    if (!user) return;
    
    document.getElementById('formTitle').innerText = 'Edit User';
    
    document.getElementById('user_id').value = user.user_id;
    document.getElementById('username').value = user.username;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('role_id').value = user.role_id;
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('is_active').value = user.is_active;
    
    // Update Select2 jika ada
    if($().select2) {
        $('#role_id').val(user.role_id).trigger('change');
        $('#is_active').val(user.is_active).trigger('change');
    }
    
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_note').innerText = 'Kosongkan jika tidak ingin mengganti password.';
    
    document.getElementById('photo').value = '';
    $('.dropify-clear').click();
    
    $('#formModal').modal('show');
}

async function saveUser(e) {
    e.preventDefault();
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    
    const userId = formData.get('user_id');
    const url = userId ? '../../api/users/update.php' : '../../api/users/save.php';
    
    const btn = document.getElementById('btnSubmit');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading mr-1"></i> Menyimpan...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire('Sukses!', result.message, 'success');
            hideForm();
            fetchUsers(); 
        } else {
            Swal.fire('Gagal!', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error!', 'Terjadi kesalahan sistem: ' + error, 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = originalText;
}

function nonactiveUser(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Akun user ini akan dinonaktifkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Nonaktifkan!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('user_id', id);
            
            try {
                const response = await fetch('../../api/users/nonactive.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                
                if(res.status === 'success') {
                    Swal.fire('Berhasil!', 'User berhasil dinonaktifkan.', 'success');
                    fetchUsers(); 
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Terjadi kesalahan sistem: ' + error, 'error');
            }
        }
    });
}
</script>
