<?php
session_start();
// Proteksi halaman admin, wajib login!
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
            <h4 class="mb-0 font-size-18">Manajemen Terapis</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Terapis</li>
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
                    <h4 class="card-title">Daftar Terapis Klinik</h4>
                    <button onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Daftarkan Terapis Baru
                    </button>
                </div>

                <!-- PENCARIAN & FILTER -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Pencarian Pintar:</strong>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="filter_kode" placeholder="Ketik Kode (Cth: TRP01)" oninput="applyFilter()">
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik Nama Terapis..." oninput="applyFilter()">
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="text-align: center;">Foto</th>
                                <th>Kode</th>
                                <th>Nama Terapis</th>
                                <th>L/P</th>
                                <th>Agama</th>
                                <th>Pendidikan</th>
                                <th>Kecamatan</th>
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

<!-- Modal Form Terapis -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Form Terapis</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="terapisForm" onsubmit="saveData(event)">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_terapis" id="id_terapis">
                    
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-identitas" role="tab">Identitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-alamat" role="tab">Alamat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-rekening" role="tab">Rekening</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-lainnya" role="tab">Lainnya</a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content text-muted">
                        <!-- TAB 1: IDENTITAS -->
                        <div class="tab-pane active" id="tab-identitas" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Nama Lengkap *</label>
                                        <input type="text" class="form-control" name="nama_terapis" id="nama_terapis" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Kelamin</label>
                                        <select class="form-control select2" name="jenis_kelamin" id="jenis_kelamin" style="width: 100%;">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">Laki-Laki</option>
                                            <option value="0">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Lahir</label>
                                        <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Agama</label>
                                        <select class="form-control select2" name="agama" id="agama" style="width: 100%;">
                                            <option value="1">Islam</option>
                                            <option value="2">Kristen</option>
                                            <option value="3">Katolik</option>
                                            <option value="4">Hindu</option>
                                            <option value="5">Budha</option>
                                            <option value="6">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: ALAMAT -->
                        <div class="tab-pane" id="tab-alamat" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alamat Domisili</label>
                                        <textarea class="form-control" name="alamat" id="alamat" rows="2" placeholder="Masukkan alamat lengkap"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kecamatan</label>
                                        <input type="text" class="form-control" name="kecamatan" id="kecamatan" placeholder="Cth: Lowokwaru">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Alamat (Titik GPS)</label>
                                        <input type="text" class="form-control" name="alamat_gps" id="alamat_gps" placeholder="Paste link GMaps">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: REKENING -->
                        <div class="tab-pane" id="tab-rekening" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Rekening 1</label>
                                        <input type="text" class="form-control" name="no_rek1" id="no_rek1" placeholder="Cth: 123456789">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Atas Nama Rekening 1</label>
                                        <input type="text" class="form-control" name="an_rek1" id="an_rek1" placeholder="Cth: Budi Santoso">
                                    </div>
                                </div>
                                <div class="col-md-12"><hr></div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Rekening 2</label>
                                        <input type="text" class="form-control" name="no_rek2" id="no_rek2" placeholder="Opsi Tambahan">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Atas Nama Rekening 2</label>
                                        <input type="text" class="form-control" name="an_rek2" id="an_rek2" placeholder="Opsi Tambahan">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: LAINNYA -->
                        <div class="tab-pane" id="tab-lainnya" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pendidikan Terakhir</label>
                                        <input type="text" class="form-control" name="pendidikan" id="pendidikan" value="SMA/K">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Akun Instagram</label>
                                        <input type="text" class="form-control" name="ig" id="ig" placeholder="@username">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Foto Profile</label>
                                        <input type="file" class="dropify" name="foto" id="foto_dropify" accept="image/jpeg, image/png, image/webp" data-height="100">
                                        <small class="form-text text-muted">Akan direname otomatis sesuai Kode Terapis.</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Keterangan / Bio</label>
                                        <input type="hidden" name="keterangan" id="keterangan_hidden">
                                        <div id="keterangan_editor" style="height: 60px;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Tayang</label>
                                        <select class="form-control font-weight-bold select2" name="is_active" id="is_active" style="width: 100%;">
                                            <option value="1">Aktif Tayang</option>
                                            <option value="0" class="text-danger">Non-Aktif (Bekukan)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Terapis *</label>
                                        <input type="text" class="form-control" name="kode_terapis" id="kode_terapis" placeholder="Contoh: TRP01" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentList = [];
let dataTable = null;
let filterTimer;
let quillKeterangan;

window.onload = () => {
    if($().select2) {
        $('.select2').select2({ dropdownParent: $('#formModal') });
    }
    $('.dropify').dropify();
    quillKeterangan = new Quill('#keterangan_editor', { theme: 'snow' });
    fetchList();
};

function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        fetchList();
    }, 500);
}

function getAgamaText(id) {
    const list = {1: 'Islam', 2: 'Kristen', 3: 'Katolik', 4: 'Hindu', 5: 'Budha', 6: 'Lainnya'};
    return list[id] || '-';
}

function getKelaminText(id) {
    if (id === '1' || id === 1) return 'Laki-Laki';
    if (id === '0' || id === 0) return 'Perempuan';
    return '-';
}

async function fetchList() {
    try {
        const filterKode = document.getElementById('filter_kode').value.trim();
        const filterNama = document.getElementById('filter_nama').value.trim();
        
        let url = '../../api/terapis/list.php?1=1';
        if (filterKode) url += '&kode_terapis=' + encodeURIComponent(filterKode);
        if (filterNama) url += '&nama_terapis=' + encodeURIComponent(filterNama);
        
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
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non-Aktif</span>';
                const imgTag = item.foto ? `<img src="../../images/${item.foto}" class="rounded avatar-sm object-cover" style="width: 40px; height: 40px; object-fit: cover;">` : '<div class="avatar-sm d-inline-block"><span class="avatar-title rounded bg-light text-dark font-size-12">No Img</span></div>';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="text-center align-middle">${imgTag}</td>
                        <td class="align-middle"><strong>${item.kode_terapis}</strong></td>
                        <td class="align-middle">${item.nama_terapis}</td>
                        <td class="align-middle">${getKelaminText(item.jenis_kelamin)}</td>
                        <td class="align-middle">${getAgamaText(item.agama)}</td>
                        <td class="align-middle">${item.pendidikan || '-'}</td>
                        <td class="align-middle">${item.kecamatan || '-'}</td>
                        <td class="align-middle">${statusHtml}</td>
                        <td class="align-middle">
                            <button onclick="editData(${index})" class="btn btn-sm btn-info waves-effect waves-light"><i class="mdi mdi-pencil"></i> Edit</button>
                            ${parseInt(item.is_active) === 1 ? `<button onclick="nonactiveData(${item.id_terapis})" class="btn btn-sm btn-danger waves-effect waves-light"><i class="mdi mdi-block-helper"></i> Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Data Terapis tidak ditemukan."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan koneksi ke sistem API.', 'error');
    }
}

function showForm() {
    document.getElementById('terapisForm').reset();
    document.getElementById('id_terapis').value = '';
    document.getElementById('pendidikan').value = 'SMA/K'; 
    document.getElementById('formTitle').innerText = 'Daftarkan Terapis Baru';
    
    if($().select2) {
        $('#jenis_kelamin').val('').trigger('change');
        $('#agama').val('1').trigger('change');
        $('#is_active').val('1').trigger('change');
    }
    
    if (quillKeterangan) quillKeterangan.setContents([]);
    $('.dropify-clear').click();
    
    $('.nav-tabs a[href="#tab-identitas"]').tab('show');
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

function editData(index) {
    const item = currentList[index];
    document.getElementById('formTitle').innerText = 'Edit Profil Terapis';
    
    document.getElementById('id_terapis').value = item.id_terapis;
    document.getElementById('kode_terapis').value = item.kode_terapis;
    document.getElementById('nama_terapis').value = item.nama_terapis;
    document.getElementById('jenis_kelamin').value = item.jenis_kelamin !== null ? item.jenis_kelamin : '';
    document.getElementById('tanggal_lahir').value = item.tanggal_lahir || '';
    document.getElementById('agama').value = item.agama || '1';
    
    document.getElementById('alamat').value = item.alamat || '';
    document.getElementById('kecamatan').value = item.kecamatan || '';
    document.getElementById('alamat_gps').value = item.alamat_gps || '';
    
    document.getElementById('no_rek1').value = item.nor_rek1 || '';
    document.getElementById('an_rek1').value = item.an_rek1 || '';
    document.getElementById('no_rek2').value = item.no_rek2 || '';
    document.getElementById('an_rek2').value = item.an_rek2 || '';
    
    if (quillKeterangan) quillKeterangan.clipboard.dangerouslyPasteHTML(item.keterangan || '');
    
    document.getElementById('pendidikan').value = item.pendidikan || '';
    document.getElementById('ig').value = item.ig || '';
    document.getElementById('is_active').value = item.is_active;
    
    if($().select2) {
        $('#jenis_kelamin').val(item.jenis_kelamin !== null ? item.jenis_kelamin : '').trigger('change');
        $('#agama').val(item.agama || '1').trigger('change');
        $('#is_active').val(item.is_active).trigger('change');
    }
    
    $('.nav-tabs a[href="#tab-identitas"]').tab('show');
    $('#formModal').modal('show');
}

async function saveData(e) {
    e.preventDefault();
    
    const keteranganText = quillKeterangan.root.innerHTML === '<p><br></p>' ? '' : quillKeterangan.root.innerHTML;
    document.getElementById('keterangan_hidden').value = keteranganText;
    
    const form = document.getElementById('terapisForm');
    const formData = new FormData(form);
    
    const idTerapis = document.getElementById('id_terapis').value;
    const url = idTerapis ? '../../api/terapis/update.php' : '../../api/terapis/save.php';
    
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

async function nonactiveData(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: "Yakin ingin membekukan (nonaktifkan) terapis ini? Data tidak akan dihapus dari histori transaksi namun akan hilang dari aplikasi publik.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Nonaktifkan'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_terapis', id);
            
            try {
                const response = await fetch('../../api/terapis/nonactive.php', { method: 'POST', body: formData });
                const res = await response.json();
                if (res.status === 'success') {
                    Swal.fire('Sukses', res.message, 'success');
                    fetchList();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (error) { Swal.fire('Error', 'Terjadi gangguan fungsi non-aktif', 'error'); }
        }
    });
}
</script>
