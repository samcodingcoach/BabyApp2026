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
            <h4 class="mb-0 font-size-18">Manajemen Pencairan Komisi</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Keuangan</a></li>
                    <li class="breadcrumb-item active">Pencairan Komisi</li>
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
                    <h4 class="card-title">Daftar Pencairan Komisi Terapis</h4>
                    <button onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Buat Pencairan Baru
                    </button>
                </div>

                <!-- PENCARIAN & FILTER -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Filter Tanggal:</strong>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control font-weight-bold" id="filter_start" onchange="fetchList()">
                        </div>
                        <div class="col-md-1 text-center font-weight-bold">s/d</div>
                        <div class="col-md-3">
                            <input type="date" class="form-control font-weight-bold" id="filter_end" onchange="fetchList()">
                        </div>
                        <div class="col-md-3 text-right">
                            <button class="btn btn-secondary waves-effect" onclick="resetFilter()">Reset</button>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th>Kode</th>
                                <th>Keterangan</th>
                                <th>Bank</th>
                                <th>Biaya Admin</th>
                                <th>Status</th>
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

<!-- Modal Form Pencairan -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Form Pencairan Komisi</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="pencairanForm" onsubmit="saveData(event)" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_pencarian" id="id_pencarian">
                    
                    <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-pembayaran" role="tab">Pembayaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-bukti" role="tab">Bukti Transfer</a>
                        </li>
                    </ul>

                    <div class="tab-content text-muted">
                        <!-- TAB 1: PEMBAYARAN -->
                        <div class="tab-pane active" id="tab-pembayaran" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12 form-group mb-3">
                                    <label class="font-weight-bold">Bank Tujuan *</label>
                                    <select class="form-control select2" name="bank" id="bank" style="width: 100%;" required>
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="BCA">BCA (Bank Central Asia)</option>
                                        <option value="BANK MANDIRI">BANK MANDIRI</option>
                                        <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                        <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                        <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                        <option value="CIMB NIAGA">CIMB NIAGA</option>
                                        <option value="PERMATA BANK">PERMATA BANK</option>
                                        <option value="DANAMON">DANAMON</option>
                                        <option value="MEGA">MEGA</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold">Tanggal Transfer *</label>
                                    <input type="date" class="form-control" name="tanggal_transfer" id="tanggal_transfer" required>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="font-weight-bold">Biaya Transfer / Admin</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" class="form-control" name="biaya_admin" id="biaya_admin" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 2: BUKTI TRANSFER -->
                        <div class="tab-pane" id="tab-bukti" role="tabpanel">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Upload Bukti Transfer <small class="text-muted">(JPG/PDF, Maks 2MB)</small></label>
                                <input type="file" class="dropify" name="bukti" id="bukti" accept=".jpg,.jpeg,.pdf" data-height="150">
                                <small id="bukti_hint" class="text-info d-none mt-1 d-block">Abaikan form ini jika tidak ingin mengubah file bukti lama.</small>
                            </div>
                            
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Keterangan / Catatan Tambahan</label>
                                <textarea class="form-control" name="keterangan" id="keterangan" rows="4" placeholder="Tambahkan catatan jika perlu..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentList = [];
let dataTable = null;

window.onload = () => {
    fetchList();
    $('.select2').select2({
        dropdownParent: $('#formModal')
    });
    $('.dropify').dropify();
};

function resetFilter() {
    document.getElementById('filter_start').value = '';
    document.getElementById('filter_end').value = '';
    fetchList();
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

async function fetchList() {
    try {
        const startDate = document.getElementById('filter_start').value;
        const endDate = document.getElementById('filter_end').value;
        
        let url = '../../api/pencairan-komisi/list.php?1=1';
        if (startDate) url += '&start_date=' + startDate;
        if (endDate) url += '&end_date=' + endDate;
        
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
                const isClosed = parseInt(item.isClosed) === 1;
                const statusHtml = isClosed 
                    ? '<span class="badge badge-soft-success font-size-12"><i class="mdi mdi-check-circle"></i> Closed</span>' 
                    : '<span class="badge badge-soft-warning font-size-12"><i class="mdi mdi-clock-outline"></i> Open</span>';
                
                let actionHtml = `<a href="detail-pencairan-komisi.php?kode=${item.kode_pencairan}" class="btn btn-sm btn-primary waves-effect waves-light mr-1 mb-1" title="Lihat Rincian"><i class="mdi mdi-eye"></i> Detail</a>`;
                
                if (item.bukti) {
                    actionHtml += `<a href="../../images/pencairan/${item.bukti}" target="_blank" class="btn btn-sm btn-success waves-effect waves-light mr-1 mb-1" title="Lihat Bukti"><i class="mdi mdi-download"></i> Bukti</a>`;
                }

                if (!isClosed) {
                    actionHtml += `
                        <button onclick="editData(${index})" class="btn btn-sm btn-info waves-effect waves-light mr-1 mb-1" title="Edit"><i class="mdi mdi-pencil"></i></button>
                        <button onclick="deleteData(${item.id_pencarian})" class="btn btn-sm btn-danger waves-effect waves-light mb-1" title="Hapus"><i class="mdi mdi-trash-can"></i></button>
                    `;
                } else {
                    actionHtml += `<a href="print.php?kode=${item.kode_pencairan}" target="_blank" class="btn btn-sm btn-dark waves-effect waves-light mr-1 mb-1" title="Cetak Struk / Invoice"><i class="mdi mdi-printer"></i> Cetak</a>`;
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle font-weight-bold text-primary">${item.kode_pencairan}</td>
                        <td class="align-middle">${item.keterangan || '-'}</td>
                        <td class="align-middle">${item.bank || '-'}</td>
                        <td class="align-middle text-right">${formatRupiah(item.biaya_admin)}</td>
                        <td class="align-middle">${statusHtml}</td>
                        <td class="align-middle">${actionHtml}</td>
                    </tr>
                `;
            });
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data pencairan."
            }
        });
        
    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'Terjadi gangguan koneksi ke server.', 'error');
    }
}

function showForm() {
    document.getElementById('pencairanForm').reset();
    document.getElementById('id_pencarian').value = '';
    $('#bank').val('').trigger('change');
    document.getElementById('formTitle').innerText = 'Buat Pencairan Komisi Baru';
    document.getElementById('bukti').required = false; // TIDAK WAJIB saat membuat baru
    document.getElementById('bukti_hint').classList.remove('d-none');
    
    // Reset dropify
    let drEvent = $('#bukti').dropify();
    drEvent = drEvent.data('dropify');
    if(drEvent) {
        drEvent.resetPreview();
        drEvent.clearElement();
    }
    
    // Reset to tab 1
    $('.nav-tabs a[href="#tab-pembayaran"]').tab('show');
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

function editData(index) {
    const item = currentList[index];
    if (parseInt(item.isClosed) === 1) {
        Swal.fire('Ditolak', 'Data ini sudah berstatus Closed dan tidak bisa diedit.', 'warning');
        return;
    }

    document.getElementById('formTitle').innerText = 'Edit Pencairan Komisi';
    
    document.getElementById('id_pencarian').value = item.id_pencarian;
    $('#bank').val(item.bank || '').trigger('change');
    document.getElementById('tanggal_transfer').value = item.tanggal_transfer || '';
    document.getElementById('biaya_admin').value = item.biaya_admin || 0;
    document.getElementById('keterangan').value = item.keterangan || '';
    
    document.getElementById('bukti').required = false;
    document.getElementById('bukti_hint').classList.remove('d-none');
    
    // Reset dropify for edit
    let drEvent = $('#bukti').dropify();
    drEvent = drEvent.data('dropify');
    if(drEvent) {
        drEvent.resetPreview();
        drEvent.clearElement();
    }
    
    $('#formModal').modal('show');
}

async function saveData(e) {
    e.preventDefault();
    
    const form = document.getElementById('pencairanForm');
    const formData = new FormData(form);
    
    const btn = document.getElementById('btnSubmit');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading"></i> Menyimpan...';
    
    try {
        const response = await fetch('../../api/pencairan-komisi/save.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            hideForm();
            Swal.fire({
                title: 'Berhasil!',
                text: result.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            fetchList();
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Gangguan koneksi ke server', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = oriText;
}

function deleteData(id) {
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: "Data pencairan beserta semua rincian komisinya akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id_pencarian', id);
                
                const response = await fetch('../../api/pencairan-komisi/delete.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                
                if (res.status === 'success') {
                    Swal.fire('Terhapus!', res.message, 'success');
                    fetchList();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    });
}
</script>
