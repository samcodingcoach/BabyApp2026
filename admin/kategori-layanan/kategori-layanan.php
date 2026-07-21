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
            <h4 class="mb-0 font-size-18">Manajemen Kategori Layanan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Kategori</li>
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
                    <h4 class="card-title">Daftar Kategori Layanan</h4>
                    <button onclick="showFormAdd()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Tambah Kategori Baru
                    </button>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="width: 15%;">Kode</th>
                                <th style="width: 25%;">Nama Kategori</th>
                                <th style="width: 35%;">Deskripsi</th>
                                <th style="width: 10%; text-align: center;">Status</th>
                                <th style="width: 10%; text-align: center;">Aksi</th>
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

<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Tambah Kategori</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="kategoriForm" onsubmit="saveKategori(event)">
                <div class="modal-body">
                    <input type="hidden" name="id_kategori_layanan" id="id_kategori_layanan">
                    
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-kategori" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">Kategori</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-deskripsi" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Deskripsi</span>    
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="tab-kategori" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kode Kategori</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="kode_kategori" id="kode_kategori" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Kategori</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-control font-weight-bold select2" name="is_active" id="is_active" style="width: 100%;">
                                        <option value="1">Aktif</option>
                                        <option value="0" class="text-danger">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-deskripsi" role="tabpanel">
                            <div class="form-group">
                                <label class="font-weight-bold">Deskripsi</label>
                                <input type="hidden" name="deskripsi" id="deskripsi_hidden">
                                <div id="deskripsi_editor" style="height: 150px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let kategoriData = [];
let dataTable = null;
let quillDeskripsi;

window.onload = () => {
    if($().select2) {
        $('.select2').select2({ dropdownParent: $('#formModal') });
    }
    quillDeskripsi = new Quill('#deskripsi_editor', { theme: 'snow' });
    fetchData();
};

async function fetchData() {
    try {
        const response = await fetch('../../api/kategori-layanan/list.php');
        const result = await response.json();
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            kategoriData = result.data;
            
            kategoriData.forEach((item, index) => {
                const statusHtml = item.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle">${item.kode_kategori}</td>
                        <td class="align-middle"><strong>${item.nama_kategori}</strong></td>
                        <td class="align-middle">${item.deskripsi || '-'}</td>
                        <td class="text-center align-middle">${statusHtml}</td>
                        <td class="text-center align-middle">
                            <button onclick="editData(${item.id_kategori_layanan})" class="btn btn-sm btn-info waves-effect waves-light mb-1"><i class="mdi mdi-pencil"></i> Edit</button>
                            ${item.is_active == 1 ? `<button onclick="nonactiveData(${item.id_kategori_layanan})" class="btn btn-sm btn-danger waves-effect waves-light mb-1"><i class="mdi mdi-block-helper"></i> Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data kategori layanan."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan jaringan atau API tidak merespons.', 'error');
    }
}

function showFormAdd() {
    document.getElementById('kategoriForm').reset();
    document.getElementById('formTitle').innerText = 'Tambah Kategori Baru';
    document.getElementById('id_kategori_layanan').value = '';
    
    if($().select2) {
        $('#is_active').val('1').trigger('change');
    }
    
    if (quillDeskripsi) quillDeskripsi.setContents([]);
    
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
    document.getElementById('kategoriForm').reset();
}

function editData(id) {
    const item = kategoriData.find(k => k.id_kategori_layanan == id);
    if (!item) return;
    
    document.getElementById('formTitle').innerText = 'Edit Kategori Layanan';
    
    document.getElementById('id_kategori_layanan').value = item.id_kategori_layanan;
    document.getElementById('kode_kategori').value = item.kode_kategori;
    document.getElementById('nama_kategori').value = item.nama_kategori;
    
    if (quillDeskripsi) {
        quillDeskripsi.clipboard.dangerouslyPasteHTML(item.deskripsi || '');
    }
    
    document.getElementById('is_active').value = item.is_active;
    
    if($().select2) {
        $('#is_active').val(item.is_active).trigger('change');
    }
    
    $('#formModal').modal('show');
}

async function saveKategori(e) {
    e.preventDefault();
    
    const deskripsiText = quillDeskripsi.root.innerHTML === '<p><br></p>' ? '' : quillDeskripsi.root.innerHTML;
    document.getElementById('deskripsi_hidden').value = deskripsiText;

    const form = document.getElementById('kategoriForm');
    const formData = new FormData(form);
    
    const id = formData.get('id_kategori_layanan');
    const url = id ? '../../api/kategori-layanan/update.php' : '../../api/kategori-layanan/save.php';
    
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
            fetchData();
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan sistem: ' + error, 'error');
    }
    btn.disabled = false;
    btn.innerHTML = oriText;
}

function nonactiveData(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Kategori layanan ini akan dinonaktifkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Nonaktifkan'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_kategori_layanan', id);
            
            try {
                const response = await fetch('../../api/kategori-layanan/nonactive.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                
                if(res.status === 'success') {
                    Swal.fire('Berhasil', 'Kategori berhasil dinonaktifkan.', 'success');
                    fetchData();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem: ' + error, 'error');
            }
        }
    });
}
</script>
