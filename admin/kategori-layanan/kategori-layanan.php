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

                <!-- FORM TAMBAH / EDIT -->
                <div id="formContainer" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Tambah Kategori</h5>
                    <form id="kategoriForm" onsubmit="saveKategori(event)">
                        <input type="hidden" name="id_kategori_layanan" id="id_kategori_layanan">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Kode Kategori</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="kode_kategori" id="kode_kategori" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nama Kategori</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Deskripsi</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="2"></textarea>
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
                            <button type="submit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Kategori</button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
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
                            <tr><td colspan="6" class="text-center">Loading data...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let kategoriData = [];

window.onload = () => {
    fetchData();
};

async function fetchData() {
    try {
        const response = await fetch('../../api/kategori-layanan/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            kategoriData = result.data;
            if (kategoriData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Belum ada data kategori layanan.</td></tr>';
                return;
            }
            
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
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" class="text-center">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

function showFormAdd() {
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Tambah Kategori Baru';
    document.getElementById('kategoriForm').reset();
    document.getElementById('id_kategori_layanan').value = '';
}

function hideForm() {
    $('#formContainer').fadeOut();
    document.getElementById('kategoriForm').reset();
}

function editData(id) {
    const item = kategoriData.find(k => k.id_kategori_layanan == id);
    if (!item) return;
    
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Edit Kategori Layanan';
    
    document.getElementById('id_kategori_layanan').value = item.id_kategori_layanan;
    document.getElementById('kode_kategori').value = item.kode_kategori;
    document.getElementById('nama_kategori').value = item.nama_kategori;
    document.getElementById('deskripsi').value = item.deskripsi || '';
    document.getElementById('is_active').value = item.is_active;
}

async function saveKategori(e) {
    e.preventDefault();
    const form = document.getElementById('kategoriForm');
    const formData = new FormData(form);
    
    const id = formData.get('id_kategori_layanan');
    const url = id ? '../../api/kategori-layanan/update.php' : '../../api/kategori-layanan/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchData();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}

async function nonactiveData(id) {
    if(!confirm('Apakah Anda yakin ingin menonaktifkan kategori layanan ini?')) return;
    
    const formData = new FormData();
    formData.append('id_kategori_layanan', id);
    
    try {
        const response = await fetch('../../api/kategori-layanan/nonactive.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            alert('Kategori berhasil dinonaktifkan.');
            fetchData();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}
</script>
