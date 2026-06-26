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
            <h4 class="mb-0 font-size-18">Riwayat & Pengaturan Harga Layanan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="../layanan/layanan.php">Layanan</a></li>
                    <li class="breadcrumb-item active">Harga Layanan</li>
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
                    <h4 class="card-title">Histori Harga Layanan</h4>
                    <div>
                        <a href="../layanan/layanan.php" class="btn btn-secondary waves-effect waves-light font-weight-bold mr-2"><i class="mdi mdi-arrow-left mr-1"></i> Kembali ke Layanan</a>
                        <button onclick="showFormAdd()" class="btn btn-success waves-effect waves-light font-weight-bold">
                            <i class="mdi mdi-plus mr-1"></i> Catat Harga Baru
                        </button>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th>Tgl Efektif</th>
                                <th>Kode Layanan</th>
                                <th>Nama Layanan</th>
                                <th>Kategori</th>
                                <th style="text-align: right;">Harga (Rp)</th>
                                <th style="text-align: center;">Komisi (%)</th>
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

<!-- Modal Form Harga -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-warning">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="formTitle"><i class="mdi mdi-cash-multiple mr-1"></i>Catat / Perbarui Harga</h5>
                <button type="button" class="close text-dark waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="hargaForm" onsubmit="saveData(event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Kode Layanan</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="input_kode_layanan" placeholder="Contoh: LYN-01" oninput="checkKodeLayanan()">
                                    <input type="hidden" name="id_layanan" id="id_layanan">
                                    <div id="layanan_label" class="mt-1 font-weight-bold font-size-13"></div>
                                    <small class="form-text text-muted">Jika diisi (valid) dan Tgl Efektif = Hari ini, harga langsung aktif.</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Tanggal Efektif</label>
                                <div class="col-sm-8">
                                    <input type="date" class="form-control" name="tanggal_efektif" id="tanggal_efektif" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Harga Baru (Rp)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="harga_input" data-toggle="input-mask" data-mask-format="000.000.000" data-reverse="true" required>
                                    <input type="hidden" name="harga" id="harga">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Komisi Persentase (%)</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" class="form-control" name="komisi_persentase" id="komisi_persentase" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-warning waves-effect waves-light font-weight-bold px-4 text-dark">Simpan Harga</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let dataTable = null;

window.onload = () => {
    fetchHargaList();
};

let typingTimer;
async function checkKodeLayanan() {
    clearTimeout(typingTimer);
    const kode = document.getElementById('input_kode_layanan').value.trim();
    const label = document.getElementById('layanan_label');
    const idInput = document.getElementById('id_layanan');
    
    if (!kode) {
        label.innerHTML = '';
        idInput.value = '';
        return;
    }
    
    label.innerHTML = '<span class="text-primary"><i class="mdi mdi-spin mdi-loading mr-1"></i>Mencari...</span>';
    
    typingTimer = setTimeout(async () => {
        try {
            const response = await fetch('../../api/layanan/list.php?kode_layanan=' + encodeURIComponent(kode));
            const result = await response.json();
            
            if (result.status === 'success' && result.data.length > 0) {
                const item = result.data[0];
                label.innerHTML = `Terdeteksi: <span class="text-success">${item.nama_layanan}</span>`;
                idInput.value = item.id_layanan;
            } else {
                label.innerHTML = '<span class="text-danger">Kode tidak ditemukan!</span>';
                idInput.value = '';
            }
        } catch (error) {
            label.innerHTML = '<span class="text-danger">Gagal mengecek layanan</span>';
            idInput.value = '';
        }
    }, 500);
}

async function fetchHargaList() {
    try {
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch('../../api/harga-layanan/list-harga.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            result.data.forEach((item, index) => {
                const hargaFormatted = parseFloat(item.harga).toLocaleString('id-ID');
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle"><strong>${item.tanggal_efektif}</strong></td>
                        <td class="align-middle">${item.kode_layanan || '-'}</td>
                        <td class="align-middle">${item.nama_layanan || '-'}</td>
                        <td class="align-middle">${item.nama_kategori || '-'}</td>
                        <td class="text-right align-middle text-warning font-weight-bold">Rp ${hargaFormatted}</td>
                        <td class="text-center align-middle">${item.komisi_persentase}%</td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada riwayat harga tercatat."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan jaringan atau API tidak merespons.', 'error');
    }
}

function showFormAdd() {
    document.getElementById('hargaForm').reset();
    document.getElementById('layanan_label').innerHTML = '';
    document.getElementById('id_layanan').value = '';
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_efektif').value = today;
    document.getElementById('harga_input').value = '';
    document.getElementById('harga').value = '';
    
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

async function saveData(e) {
    e.preventDefault();
    
    const hargaClean = $('#harga_input').cleanVal() ? $('#harga_input').cleanVal() : $('#harga_input').val().replace(/\D/g,'');
    document.getElementById('harga').value = hargaClean;

    const form = document.getElementById('hargaForm');
    const formData = new FormData(form);
    
    const btn = document.getElementById('btnSubmit');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading mr-1"></i> Memproses...';
    
    try {
        const response = await fetch('../../api/harga-layanan/save.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire('Sukses', result.message, 'success');
            hideForm();
            fetchHargaList(); 
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan sistem: ' + error, 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = oriText;
}
</script>
