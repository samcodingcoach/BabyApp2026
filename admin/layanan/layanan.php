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
            <h4 class="mb-0 font-size-18">Manajemen Layanan Induk</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Layanan</li>
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
                    <h4 class="card-title">Daftar Layanan Klinik</h4>
                    <button onclick="showFormLayanan()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Tambah Layanan Baru
                    </button>
                </div>

                <!-- FILTER & PENCARIAN -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Pencarian Data:</strong>
                        </div>
                        <div class="col-md-3">
                            <select id="filter_kategori" class="custom-select select2" onchange="applyFilter()" style="width: 100%;">
                                <option value="">-- Semua Kategori --</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik Nama Layanan..." oninput="applyFilter()">
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="text-align: center;">Gambar</th>
                                <th>Kode</th>
                                <th>Layanan</th>
                                <th>Kategori</th>
                                <th>Durasi</th>
                                <th>Harga Tayang Saat Ini</th>
                                <th>Status</th>
                                <th style="width: 25%;">Aksi</th>
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

<!-- Modal Form Layanan -->
<div class="modal fade" id="modalLayanan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="formTitle">Form Layanan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="layananForm" onsubmit="saveLayanan(event)">
                <div class="modal-body">
                    <!-- Digunakan untuk trigger update -->
                    <input type="hidden" name="id_layanan" id="id_layanan">
                    
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-layanan" role="tab">
                                <span class="d-none d-sm-block">Layanan</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-deskripsi" role="tab">
                                <span class="d-none d-sm-block">Durasi & Deskripsi</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-gambar" role="tab">
                                <span class="d-none d-sm-block">Gambar</span>    
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-video" role="tab">
                                <span class="d-none d-sm-block">Video</span>    
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- Tab Layanan -->
                        <div class="tab-pane active" id="tab-layanan" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kategori *</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" name="id_kategori_layanan" id="id_kategori_layanan" required style="width: 100%;"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kode Layanan *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="kode_layanan" id="kode_layanan" placeholder="Misal: LYN-01" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Layanan *</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="nama_layanan" id="nama_layanan" required>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Durasi dan Deskripsi -->
                        <div class="tab-pane" id="tab-deskripsi" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Durasi (Menit) *</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="durasi_menit" id="durasi_menit" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status *</label>
                                <div class="col-sm-9">
                                    <select class="form-control font-weight-bold select2" name="is_active" id="is_active" style="width: 100%;">
                                        <option value="1">Aktif</option>
                                        <option value="0" class="text-danger">Non-Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Deskripsi</label>
                                <input type="hidden" name="deskripsi" id="deskripsi_hidden">
                                <div id="deskripsi_editor" style="height: 150px;"></div>
                            </div>
                        </div>

                        <!-- Tab Gambar -->
                        <div class="tab-pane" id="tab-gambar" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Gambar 1 (Utama)</label>
                                <div class="col-sm-9">
                                    <input type="file" class="dropify" name="picture1" accept="image/*">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Gambar 2</label>
                                <div class="col-sm-9">
                                    <input type="file" class="dropify" name="picture2" accept="image/*">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Gambar 3</label>
                                <div class="col-sm-9">
                                    <input type="file" class="dropify" name="picture3" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Tab Video -->
                        <div class="tab-pane" id="tab-video" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">URL Video 1</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="video1" id="video1" placeholder="https://youtube.com/...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitLayanan" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Layanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Harga -->
<div class="modal fade" id="modalHarga" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-warning">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="mdi mdi-cash-multiple mr-1"></i>Atur Harga Baru</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="hargaForm" onsubmit="saveHarga(event)">
                <div class="modal-body">
                    <div class="alert alert-warning text-dark font-weight-bold" role="alert">
                        Layanan: <span id="labelNamaLayanan"></span>
                    </div>
                    <input type="hidden" name="id_layanan" id="harga_id_layanan">
                    
                    <div class="form-group">
                        <label>Tanggal Efektif</label>
                        <input type="date" class="form-control" name="tanggal_efektif" id="harga_tanggal" required>
                        <small class="form-text text-muted">Pilih hari ini agar sistem men-sync dan langsung menayangkannya!</small>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="text" class="form-control" id="harga_input" data-toggle="input-mask" data-mask-format="000.000.000" data-reverse="true" required>
                        <input type="hidden" name="harga" id="harga_hidden">
                    </div>
                    <div class="form-group">
                        <label>Komisi Terapis (%)</label>
                        <input type="number" step="0.01" class="form-control" name="komisi_persentase" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitHarga" class="btn btn-warning waves-effect waves-light font-weight-bold px-4 text-dark">Catat & Sinkronisasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let dataTable = null;
let currentLayananList = [];
let quillDeskripsi;

window.onload = () => {
    if($().select2) {
        $('.select2').select2();
        $('#id_kategori_layanan').select2({ dropdownParent: $('#modalLayanan') });
        $('#is_active').select2({ dropdownParent: $('#modalLayanan') });
    }
    
    $('.dropify').dropify();
    quillDeskripsi = new Quill('#deskripsi_editor', { theme: 'snow' });
    
    
    fetchKategoriList();
    fetchLayananList();
};

async function fetchKategoriList() {
    try {
        const response = await fetch('../../api/kategori-layanan/list.php');
        const result = await response.json();
        if (result.status === 'success') {
            const select = document.getElementById('id_kategori_layanan');
            const selectFilter = document.getElementById('filter_kategori');
            
            select.innerHTML = '<option value="">-- Pilih Kategori --</option>';
            selectFilter.innerHTML = '<option value="">-- Semua Kategori --</option>';
            
            result.data.forEach(item => {
                select.innerHTML += `<option value="${item.id_kategori_layanan}">${item.nama_kategori}</option>`;
                selectFilter.innerHTML += `<option value="${item.kode_kategori}">${item.nama_kategori}</option>`;
            });
            
            // Re-init select2 after populate
            if($().select2) {
                $('#filter_kategori').trigger('change.select2');
            }
        }
    } catch (error) { console.error('Gagal meload dropdown kategori'); }
}

let filterTimer;
function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        fetchLayananList();
    }, 500);
}

async function fetchLayananList() {
    try {
        const filterKategori = document.getElementById('filter_kategori').value;
        const filterNama = document.getElementById('filter_nama').value.trim();
        
        let url = '../../api/layanan/list.php?1=1';
        if (filterKategori) url += '&kode_kategori=' + encodeURIComponent(filterKategori);
        if (filterNama) url += '&nama_layanan=' + encodeURIComponent(filterNama);
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentLayananList = result.data;
            
            result.data.forEach((item, index) => {
                const hargaTxt = item.harga ? 'Rp ' + parseFloat(item.harga).toLocaleString('id-ID') : '<i class="text-danger">Belum Diset</i>';
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non-Aktif</span>';
                
                const imgTag = item.picture1 ? `<img src="../../images/${item.picture1}" class="rounded avatar-sm object-cover" style="width: 40px; height: 40px; object-fit: cover;">` : '<div class="avatar-sm d-inline-block"><span class="avatar-title rounded bg-light text-dark font-size-12">No Img</span></div>';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="text-center align-middle">${imgTag}</td>
                        <td class="align-middle">${item.kode_layanan}</td>
                        <td class="align-middle"><strong>${item.nama_layanan}</strong></td>
                        <td class="align-middle">${item.nama_kategori || '-'}</td>
                        <td class="align-middle">${item.durasi_menit} Menit</td>
                        <td class="align-middle font-weight-bold text-warning">${hargaTxt}</td>
                        <td class="align-middle">${statusHtml}</td>
                        <td class="align-middle">
                            <button onclick="editLayanan(${index})" class="btn btn-sm btn-info waves-effect waves-light mb-1"><i class="mdi mdi-pencil"></i> Edit</button>
                            <button onclick="openFormHarga(${item.id_layanan}, '${item.nama_layanan.replace(/'/g, "\\'")}')" class="btn btn-sm btn-warning waves-effect waves-light mb-1 text-dark font-weight-bold"><i class="mdi mdi-cash"></i> Ubah Harga</button>
                            ${parseInt(item.is_active) === 1 ? `<button onclick="nonactiveLayanan(${item.id_layanan})" class="btn btn-sm btn-danger waves-effect waves-light mb-1"><i class="mdi mdi-block-helper"></i> Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada master layanan."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan jaringan atau API tidak merespons.', 'error');
    }
}

// ================= LAYANAN CORE LOGIC =================
function showFormLayanan() {
    document.getElementById('layananForm').reset();
    document.getElementById('id_layanan').value = '';
    document.getElementById('formTitle').innerText = 'Tambah Layanan Induk Baru';
    
    if($().select2) {
        $('#id_kategori_layanan').val('').trigger('change');
        $('#is_active').val('1').trigger('change');
    }
    
    if (quillDeskripsi) quillDeskripsi.setContents([]);
    $('.dropify-clear').click();
    
    $('#modalLayanan').modal('show');
}

function editLayanan(index) {
    const item = currentLayananList[index];
    document.getElementById('formTitle').innerText = 'Edit Master Layanan';
    
    document.getElementById('id_layanan').value = item.id_layanan;
    document.getElementById('id_kategori_layanan').value = item.id_kategori_layanan;
    document.getElementById('kode_layanan').value = item.kode_layanan;
    document.getElementById('nama_layanan').value = item.nama_layanan;
    document.getElementById('durasi_menit').value = item.durasi_menit;
    
    if (quillDeskripsi) {
        quillDeskripsi.clipboard.dangerouslyPasteHTML(item.deskripsi || '');
    }
    
    document.getElementById('is_active').value = item.is_active;
    document.getElementById('video1').value = item.video1 || '';
    
    if($().select2) {
        $('#id_kategori_layanan').val(item.id_kategori_layanan).trigger('change');
        $('#is_active').val(item.is_active).trigger('change');
    }
    
    $('#modalLayanan').modal('show');
}

async function saveLayanan(e) {
    e.preventDefault();
    
    const deskripsiText = quillDeskripsi.root.innerHTML === '<p><br></p>' ? '' : quillDeskripsi.root.innerHTML;
    document.getElementById('deskripsi_hidden').value = deskripsiText;
    
    const form = document.getElementById('layananForm');
    const formData = new FormData(form);
    
    const idLayanan = document.getElementById('id_layanan').value;
    const url = idLayanan ? '../../api/layanan/update.php' : '../../api/layanan/save.php';
    
    const btn = document.getElementById('btnSubmitLayanan');
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
            $('#modalLayanan').modal('hide');
            fetchLayananList();
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan sistem pengiriman data!', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = oriText;
}

function nonactiveLayanan(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: "Yakin ingin membekukan/menonaktifkan layanan ini dari tayangan publik?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Nonaktifkan'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_layanan', id);
            
            try {
                const response = await fetch('../../api/layanan/nonactive.php', { method: 'POST', body: formData });
                const res = await response.json();
                if (res.status === 'success') {
                    Swal.fire('Sukses', res.message, 'success');
                    fetchLayananList();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (error) { Swal.fire('Error', 'Error sistem', 'error'); }
        }
    });
}

// ================= SMART SELECT-PRICE LOGIC =================
function openFormHarga(id_layanan, namaLayanan) {
    document.getElementById('hargaForm').reset();
    document.getElementById('harga_id_layanan').value = id_layanan;
    document.getElementById('labelNamaLayanan').innerText = namaLayanan;
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('harga_tanggal').value = today;
    document.getElementById('harga_input').value = '';
    document.getElementById('harga_hidden').value = '';
    
    
    $('#modalHarga').modal('show');
}

async function saveHarga(e) {
    e.preventDefault();
    
    const hargaClean = $('#harga_input').cleanVal() ? $('#harga_input').cleanVal() : $('#harga_input').val().replace(/\D/g,'');
    document.getElementById('harga_hidden').value = hargaClean;

    const form = document.getElementById('hargaForm');
    const formData = new FormData(form);
    
    const btn = document.getElementById('btnSubmitHarga');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading"></i> Memproses...';
    
    try {
        const response = await fetch('../../api/layanan/select-price.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire('Sukses', result.message, 'success');
            $('#modalHarga').modal('hide');
            fetchLayananList(); 
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error sistem!', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = oriText;
}
</script>
