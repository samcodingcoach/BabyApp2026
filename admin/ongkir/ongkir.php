<?php
session_start();
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
            <h4 class="mb-0 font-size-18">Manajemen Ongkos Kirim (Ongkir)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Tarif Ongkir</li>
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
                    <h4 class="card-title">Daftar Rute Jarak Tempuh Layanan</h4>
                    <button onclick="showAddForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Tambah Data Ongkir
                    </button>
                </div>

                <!-- FORM TAMBAH / EDIT -->
                <div id="formContainer" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Tambah Ongkir Baru</h5>
                    <form id="ongkirForm" onsubmit="saveData(event)">
                        <input type="hidden" id="id_ongkir">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Dari Kecamatan *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="dari_kecamatan" required placeholder="Contoh: Lowokwaru">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Ke Kecamatan *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="ke_kecamatan" required placeholder="Contoh: Klojen">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Harga (Tarif) *</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" id="harga" required placeholder="Contoh: 15000">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status Aktif</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" id="is_active">
                                            <option value="1">Aktif</option>
                                            <option value="0" class="text-danger">Tidak Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Data</button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th>Rute Pengiriman (Dari <i class="mdi mdi-arrow-right"></i> Ke)</th>
                                <th>Tarif Dasar (Rp)</th>
                                <th>Status</th>
                                <th>Terakhir Diubah</th>
                                <th style="width: 10%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
window.onload = fetchList;

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
}

let onEditData = {};

async function fetchList() {
    try {
        const response = await fetch('../../api/ongkir/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Belum ada data ongkir.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const tr = document.createElement('tr');
                
                const rute = `<strong>${item.dari_kecamatan}</strong> <i class="mdi mdi-arrow-right mx-1 text-primary"></i> <strong>${item.ke_kecamatan}</strong>`;
                const status = item.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
                const d = new Date(item.update_at || item.created_at);
                const tgl = d.toLocaleString('id-ID');
                
                tr.innerHTML = `
                    <td class="text-center align-middle">${index + 1}</td>
                    <td class="align-middle">${rute}</td>
                    <td class="align-middle font-weight-bold text-warning">Rp ${formatRp(item.harga)}</td>
                    <td class="align-middle">${status}</td>
                    <td class="align-middle">${tgl}</td>
                    <td class="text-center align-middle">
                        <button onclick='editData(${JSON.stringify(item)})' class="btn btn-sm btn-info waves-effect waves-light"><i class="mdi mdi-pencil"></i> Edit</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data dari server.</td></tr>';
    }
}

function showAddForm() {
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Tambah Ongkir Baru';
    document.getElementById('ongkirForm').reset();
    document.getElementById('id_ongkir').value = '';
    onEditData = {};
}

function editData(item) {
    $('#formContainer').fadeIn();
    document.getElementById('formTitle').innerText = 'Edit Data Ongkir';
    
    document.getElementById('id_ongkir').value = item.id_ongkir;
    document.getElementById('dari_kecamatan').value = item.dari_kecamatan;
    document.getElementById('ke_kecamatan').value = item.ke_kecamatan;
    document.getElementById('harga').value = item.harga;
    document.getElementById('is_active').value = item.is_active;
    
    onEditData = item;
}

function hideForm() {
    $('#formContainer').fadeOut();
}

async function saveData(e) {
    e.preventDefault();
    
    const id_ongkir = document.getElementById('id_ongkir').value;
    const isEdit = id_ongkir !== '';
    
    const params = new URLSearchParams();
    if (isEdit) params.append('id_ongkir', id_ongkir);
    params.append('dari_kecamatan', document.getElementById('dari_kecamatan').value);
    params.append('ke_kecamatan', document.getElementById('ke_kecamatan').value);
    params.append('harga', document.getElementById('harga').value);
    params.append('is_active', document.getElementById('is_active').value);
    
    const endpoint = isEdit ? '../../api/ongkir/update.php' : '../../api/ongkir/save.php';
    const btn = document.getElementById('btnSubmit');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading mr-1"></i> Menyimpan...';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchList();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan jaringan.');
    }
    
    btn.disabled = false;
    btn.innerHTML = originalText;
}
</script>
