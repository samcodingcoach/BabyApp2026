<?php
session_start();
// Proteksi halaman admin
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
            <h4 class="mb-0 font-size-18">Manajemen Menu Level</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pengaturan</a></li>
                    <li class="breadcrumb-item active">Menu Level</li>
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
                    <h4 class="card-title">Daftar Hak Akses Menu</h4>
                    <button onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Tambah Menu Baru
                    </button>
                </div>

                <!-- PENCARIAN & FILTER -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Filter Role:</strong>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control font-weight-bold custom-select" id="filter_role" onchange="fetchList()">
                                <option value="">Semua Role</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th>Role ID</th>
                                <th>Kategori</th>
                                <th>Nama Menu</th>
                                <th>Link URL</th>
                                <th>Visibilitas</th>
                                <th>Hak Akses</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Form Menu Level -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Form Menu Level</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="menuForm" onsubmit="saveData(event)">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_levelmenu" id="id_levelmenu">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-role" role="tab">
                                <span class="d-none d-sm-block">Role</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-konfigurasi" role="tab">
                                <span class="d-none d-sm-block">Konfigurasi</span>    
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- Tab Role -->
                        <div class="tab-pane active" id="tab-role" role="tabpanel">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Role ID *</label>
                                <select class="form-control select2" name="role_id" id="role_id" required style="width: 100%;">
                                    <option value="">-- Pilih Role --</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Kategori Menu *</label>
                                <select class="form-control select2" name="kategori_menu" id="kategori_menu" required style="width: 100%;">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Menu Utama">Menu Utama</option>
                                    <option value="Keuangan">Keuangan</option>
                                    <option value="Master Data">Master Data</option>
                                    <option value="Pengaturan">Pengaturan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Nama Menu *</label>
                                <input type="text" class="form-control" name="nama_menu" id="nama_menu" placeholder="Contoh: Laporan Omset" required>
                            </div>
                        </div>

                        <!-- Tab Konfigurasi -->
                        <div class="tab-pane" id="tab-konfigurasi" role="tabpanel">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Link (URL) *</label>
                                <input type="text" class="form-control" name="link" id="link" placeholder="Contoh: admin/laporan/omset-layanan.php" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Hak Akses (Izin Buka Halaman)?</label>
                                <select class="form-control select2" name="akses" id="akses" required style="width: 100%;">
                                    <option value="1">Ya, Diizinkan</option>
                                    <option value="0" class="text-danger">Akses Ditolak</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Visibilitas (Tampil di Sidebar)?</label>
                                <select class="form-control select2" name="terlihat" id="terlihat" required style="width: 100%;">
                                    <option value="1">Ya, Tampilkan</option>
                                    <option value="0" class="text-warning">Sembunyikan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentList = [];
let roleList = {};
let dataTable = null;

window.onload = async () => {
    if($().select2) {
        $('.select2').select2({ dropdownParent: $('#formModal') });
    }
    await fetchRoles();
    fetchList();
};

async function fetchRoles() {
    try {
        const response = await fetch('../../api/role/list.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            const filterSelect = document.getElementById('filter_role');
            const formSelect = document.getElementById('role_id');
            
            result.data.forEach(role => {
                roleList[role.role_id] = role.role_name;
                
                const opt1 = document.createElement('option');
                opt1.value = role.role_id;
                opt1.textContent = role.role_name;
                filterSelect.appendChild(opt1);
                
                const opt2 = document.createElement('option');
                opt2.value = role.role_id;
                opt2.textContent = role.role_name;
                formSelect.appendChild(opt2);
            });
        }
    } catch (error) {
        console.error("Gagal memuat daftar role", error);
    }
}

function getRoleName(id) {
    const name = roleList[id] || `Role ${id}`;
    if (id == 1) return `<span class="badge badge-primary">${name}</span>`;
    if (id == 2) return `<span class="badge badge-info">${name}</span>`;
    if (id == 3) return `<span class="badge badge-secondary">${name}</span>`;
    return `<span class="badge badge-dark">${name}</span>`;
}

async function fetchList() {
    try {
        const filterRole = document.getElementById('filter_role').value;
        let url = '../../api/menu-level/list.php';
        
        if (filterRole) {
            url += '?role_id=' + filterRole;
        }
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            
            result.data.forEach((item, index) => {
                const statusHtml = parseInt(item.terlihat) === 1 
                    ? '<span class="text-primary font-weight-bold"><i class="mdi mdi-eye"></i> Tampil</span>' 
                    : '<span class="text-warning font-weight-bold"><i class="mdi mdi-eye-off"></i> Sembunyi</span>';
                
                const aksesHtml = parseInt(item.akses) === 1
                    ? '<span class="text-success font-weight-bold"><i class="mdi mdi-check-circle"></i> Diizinkan</span>'
                    : '<span class="text-danger font-weight-bold"><i class="mdi mdi-close-circle"></i> Ditolak</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle">${getRoleName(item.role_id)}</td>
                        <td class="align-middle"><span class="badge badge-soft-primary">${item.kategori_menu || '-'}</span></td>
                        <td class="align-middle font-weight-bold text-dark">${item.nama_menu}</td>
                        <td class="align-middle"><small class="text-muted">${item.link || '-'}</small></td>
                        <td class="align-middle">${statusHtml}</td>
                        <td class="align-middle">${aksesHtml}</td>
                        <td class="align-middle">
                            <button onclick="editData(${index})" class="btn btn-sm btn-info waves-effect waves-light mr-1"><i class="mdi mdi-pencil"></i></button>
                            <button onclick="deleteData(${item.id_levelmenu})" class="btn btn-sm btn-danger waves-effect waves-light"><i class="mdi mdi-trash-can"></i></button>
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message || 'Gagal mengambil data', 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Data Menu Level tidak ditemukan."
            }
        });
        
    } catch (error) {
        console.error("fetchList Error:", error);
        Swal.fire('Error', 'Terjadi gangguan koneksi ke sistem API.', 'error');
    }
}

function showForm() {
    document.getElementById('menuForm').reset();
    document.getElementById('id_levelmenu').value = '';
    document.getElementById('formTitle').innerText = 'Tambah Menu Level Baru';
    
    if($().select2) {
        $('#role_id').val('').trigger('change');
        $('#kategori_menu').val('').trigger('change');
        $('#akses').val('1').trigger('change');
        $('#terlihat').val('1').trigger('change');
    }
    
    $('.nav-tabs a[href="#tab-role"]').tab('show');
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

function editData(index) {
    document.getElementById('formTitle').innerText = 'Edit Menu Level';
    const item = currentList[index];
    
    document.getElementById('id_levelmenu').value = item.id_levelmenu;
    
    if($().select2) {
        $('#role_id').val(item.role_id).trigger('change');
        $('#kategori_menu').val(item.kategori_menu || '').trigger('change');
        $('#akses').val(item.akses || 0).trigger('change');
        $('#terlihat').val(item.terlihat).trigger('change');
    } else {
        document.getElementById('role_id').value = item.role_id;
        document.getElementById('kategori_menu').value = item.kategori_menu || '';
        document.getElementById('akses').value = item.akses || 0;
        document.getElementById('terlihat').value = item.terlihat;
    }
    
    document.getElementById('nama_menu').value = item.nama_menu;
    document.getElementById('link').value = item.link || '';
    
    $('.nav-tabs a[href="#tab-role"]').tab('show');
    $('#formModal').modal('show');
}

async function saveData(e) {
    e.preventDefault();
    
    const form = document.getElementById('menuForm');
    const formData = new FormData(form);
    
    const idMenu = document.getElementById('id_levelmenu').value;
    const url = idMenu ? '../../api/menu-level/update.php' : '../../api/menu-level/save.php';
    
    const btn = document.getElementById('btnSubmit');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading"></i> Menyimpan...';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire('Sukses', result.message, 'success');
            hideForm();
            fetchList(); 
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan pengiriman data ke server!', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = oriText;
}

function deleteData(id) {
    Swal.fire({
        title: 'Hapus Menu Level?',
        text: "Anda tidak akan dapat mengembalikan data ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id_levelmenu', id);
                
                const response = await fetch('../../api/menu-level/delete.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                
                if (res.status === 'success') {
                    Swal.fire('Terhapus!', res.message, 'success');
                    fetchList();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            }
        }
    });
}
</script>
