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

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
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
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Form Ongkir -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Tambah Ongkir Baru</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="ongkirForm" onsubmit="saveData(event)">
                <div class="modal-body">
                    <input type="hidden" id="id_ongkir">
                    
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-rute" role="tab">
                                <span class="d-none d-sm-block">Rute</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-aktif" role="tab">
                                <span class="d-none d-sm-block">Aktif</span>    
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- Tab Rute -->
                        <div class="tab-pane active" id="tab-rute" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Dari Kecamatan *</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2-tags" id="dari_kecamatan" required style="width:100%;"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Ke Kecamatan *</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2-tags" id="ke_kecamatan" required style="width:100%;"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Harga (Tarif) *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="harga_input" required placeholder="Contoh: 15.000" data-toggle="input-mask" data-mask-format="000.000.000" data-reverse="true">
                                    <input type="hidden" id="harga">
                                </div>
                            </div>
                        </div>

                        <!-- Tab Aktif -->
                        <div class="tab-pane" id="tab-aktif" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status Aktif</label>
                                <div class="col-sm-9">
                                    <select class="form-control font-weight-bold select2" id="is_active" style="width: 100%;">
                                        <option value="1">Aktif</option>
                                        <option value="0" class="text-danger">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let dataTable = null;

window.onload = () => {
    if($().select2) {
        $('.select2').select2({ dropdownParent: $('#formModal') });
        $('.select2-tags').select2({ 
            dropdownParent: $('#formModal'),
            tags: true,
            placeholder: "Pilih atau ketik rute baru"
        });
    }
    fetchList();
};

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
}

let currentList = [];

async function fetchList() {
    try {
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch('../../api/ongkir/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            
            const lokasiSet = new Set();
            
            result.data.forEach((item, index) => {
                lokasiSet.add(item.dari_kecamatan);
                lokasiSet.add(item.ke_kecamatan);
                const tr = document.createElement('tr');
                
                const rute = `<strong>${item.dari_kecamatan}</strong> <i class="mdi mdi-arrow-right mx-1 text-primary"></i> <strong>${item.ke_kecamatan}</strong>`;
                const status = item.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
                const d = new Date(item.update_at || item.created_at);
                const tgl = d.toLocaleString('id-ID');
                
                // Save JSON index instead of the full item directly
                tr.innerHTML = `
                    <td class="text-center align-middle">${index + 1}</td>
                    <td class="align-middle">${rute}</td>
                    <td class="align-middle font-weight-bold text-warning">Rp ${formatRp(item.harga)}</td>
                    <td class="align-middle">${status}</td>
                    <td class="align-middle">${tgl}</td>
                    <td class="text-center align-middle">
                        <button onclick='editData(${index})' class="btn btn-sm btn-info waves-effect waves-light"><i class="mdi mdi-pencil"></i> Edit</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            const listLokasi = Array.from(lokasiSet);
            populateSelectOptions('#dari_kecamatan', listLokasi);
            populateSelectOptions('#ke_kecamatan', listLokasi);
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data ongkir."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Gagal memuat data dari server.', 'error');
    }
}

function populateSelectOptions(selector, dataArray) {
    const sel = $(selector);
    const currentVal = sel.val();
    sel.empty();
    sel.append('<option value="">-- Pilih / Ketik --</option>');
    dataArray.sort().forEach(item => {
        if(item) sel.append(new Option(item, item, false, false));
    });
    if (currentVal && dataArray.includes(currentVal)) {
        sel.val(currentVal).trigger('change');
    }
}

function showAddForm() {
    document.getElementById('formTitle').innerText = 'Tambah Ongkir Baru';
    document.getElementById('ongkirForm').reset();
    document.getElementById('id_ongkir').value = '';
    document.getElementById('harga_input').value = '';
    document.getElementById('harga').value = '';
    
    if($().select2) {
        $('#dari_kecamatan').val(null).trigger('change');
        $('#ke_kecamatan').val(null).trigger('change');
        $('#is_active').val('1').trigger('change');
    }
    
    $('#formModal').modal('show');
}

function editData(index) {
    document.getElementById('formTitle').innerText = 'Edit Ongkir';
    const item = currentList[index];
    
    document.getElementById('id_ongkir').value = item.id_ongkir;
    
    // Gunakan fungsi trigger change select2 agar nilai ditampilkan jika cocok, atau select2 menangani custom tags jika valid
    let $dari = $('#dari_kecamatan');
    if ($dari.find("option[value='" + item.dari_kecamatan + "']").length) {
        $dari.val(item.dari_kecamatan).trigger('change');
    } else { 
        var newOption = new Option(item.dari_kecamatan, item.dari_kecamatan, true, true);
        $dari.append(newOption).trigger('change');
    }

    let $ke = $('#ke_kecamatan');
    if ($ke.find("option[value='" + item.ke_kecamatan + "']").length) {
        $ke.val(item.ke_kecamatan).trigger('change');
    } else { 
        var newOptionKe = new Option(item.ke_kecamatan, item.ke_kecamatan, true, true);
        $ke.append(newOptionKe).trigger('change');
    }

    document.getElementById('harga_input').value = item.harga;
    $('#harga_input').trigger('input');
    document.getElementById('is_active').value = item.is_active;
    
    if($().select2) {
        $('#is_active').val(item.is_active).trigger('change');
    }
    
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

async function saveData(e) {
    e.preventDefault();
    
    const id_ongkir = document.getElementById('id_ongkir').value;
    const isEdit = id_ongkir !== '';
    
    const params = new URLSearchParams();
    if (isEdit) params.append('id_ongkir', id_ongkir);
    const hargaVal = $('#harga_input').cleanVal() ? $('#harga_input').cleanVal() : $('#harga_input').val().replace(/\D/g,'');
    document.getElementById('harga').value = hargaVal;
    
    params.append('dari_kecamatan', $('#dari_kecamatan').val());
    params.append('ke_kecamatan', $('#ke_kecamatan').val());
    params.append('harga', document.getElementById('harga').value);
    params.append('is_active', $('#is_active').val());
    
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
            Swal.fire('Sukses', result.message, 'success');
            hideForm();
            fetchList();
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = originalText;
}
</script>
