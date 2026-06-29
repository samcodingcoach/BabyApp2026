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
            <h4 class="mb-0 font-size-18">Manajemen Member</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Member</li>
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
                    <h4 class="card-title">Daftar Pendaftaran Member</h4>
                    <button onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Daftarkan Member Baru
                    </button>
                </div>

                <!-- PENCARIAN & FILTER (Server Side) -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Pencarian Pintar:</strong>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="filter_nik" placeholder="Ketik NIK eksak..." oninput="applyFilter()">
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik bagian Nama Member..." oninput="applyFilter()">
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
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>L/P</th>
                                <th>Kecamatan</th>
                                <th>Whatsapp</th>
                                <th>Status</th>
                                <th style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION (Server Side) -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted font-weight-bold" id="pageInfo">Halaman 1 dari 1 (Total: 0 Data)</span>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" id="btnPrev" onclick="changePage(-1)" disabled><i class="mdi mdi-chevron-left"></i> Prev</button>
                        <button class="btn btn-sm btn-primary" id="btnNext" onclick="changePage(1)" disabled>Next <i class="mdi mdi-chevron-right"></i></button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Form Member -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Form Member</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="memberForm" onsubmit="saveData(event)">
                <div class="modal-body">
                    <input type="hidden" name="id_member" id="id_member">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-identitas" role="tab">Identitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-alamat" role="tab">Alamat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-lainnya" role="tab">Lainnya</a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- TAB 1: IDENTITAS -->
                        <div class="tab-pane active" id="tab-identitas" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nomor Induk (NIK) *</label>
                                        <input type="text" class="form-control" name="nik" id="nik" placeholder="No KTP / ID" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Lengkap *</label>
                                        <input type="text" class="form-control" name="nama" id="nama" required>
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
                                    <div class="form-group">
                                        <label>Foto Profile</label>
                                        <input type="file" class="dropify" name="photo" accept="image/jpeg, image/png, image/webp" data-height="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 2: ALAMAT -->
                        <div class="tab-pane" id="tab-alamat" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Alamat Lengkap</label>
                                        <input type="hidden" name="alamat" id="alamat_hidden">
                                        <div id="alamat_editor" style="height: 120px;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Domisili (Kecamatan)</label>
                                        <input type="text" class="form-control" name="kecamatan" id="kecamatan">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Alamat (Titik GPS)</label>
                                        <input type="text" class="form-control" name="alamat_gps" id="alamat_gps" placeholder="Cth: Paste URL Maps">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: LAINNYA -->
                        <div class="tab-pane" id="tab-lainnya" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Whatsapp</label>
                                        <input type="text" class="form-control" id="whatsapp_input" placeholder="08123..." data-toggle="input-mask" data-mask-format="0000-0000-00000">
                                        <input type="hidden" name="whatsapp" id="whatsapp">
                                    </div>
                                    <div class="form-group">
                                        <label>Password Akun</label>
                                        <input type="password" class="form-control" name="password" id="password" placeholder="(Kosongi jika tidak diubah)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Member</label>
                                        <select class="form-control font-weight-bold select2" name="is_active" id="is_active" style="width: 100%;">
                                            <option value="1">Aktif (Approved)</option>
                                            <option value="0" class="text-danger">Belum Aktif / Blokir</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Profil Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let currentPage = 1;
let totalPages = 1;
let currentList = [];
let dataTable = null;
let filterTimer;
let quillAlamat;

window.onload = () => {
    if($().select2) {
        $('.select2').select2({ dropdownParent: $('#formModal') });
    }
    $('.dropify').dropify();
    quillAlamat = new Quill('#alamat_editor', { theme: 'snow' });
    fetchList(currentPage);
};

function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        currentPage = 1; 
        fetchList(currentPage);
    }, 500);
}

function getKelaminText(id) {
    if (id === '1' || id === 1) return 'Laki-Laki';
    if (id === '0' || id === 0) return 'Perempuan';
    return '-';
}

function changePage(direction) {
    let newPage = currentPage + direction;
    if (newPage < 1) newPage = 1;
    if (newPage > totalPages) newPage = totalPages;
    
    if (newPage !== currentPage) {
        fetchList(newPage);
    }
}

async function fetchList(page = 1) {
    try {
        const filterNik = document.getElementById('filter_nik').value.trim();
        const filterNama = document.getElementById('filter_nama').value.trim();
        
        let url = '../../api/member/list.php?page=' + page;
        if (filterNik) url += '&nik=' + encodeURIComponent(filterNik);
        if (filterNama) url += '&nama=' + encodeURIComponent(filterNama);
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            currentPage = result.current_page;
            totalPages = result.total_pages > 0 ? result.total_pages : 1;
            
            document.getElementById('pageInfo').innerText = `Halaman ${currentPage} dari ${totalPages} (Total: ${result.total_rows} Member)`;
            document.getElementById('btnPrev').disabled = (currentPage <= 1);
            document.getElementById('btnNext').disabled = (currentPage >= totalPages);
            
            let startIndex = (currentPage - 1) * result.per_page;
            
            result.data.forEach((item, index) => {
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non-Aktif</span>';
                const imgTag = item.photo ? `<img src="../../images/${item.photo}" class="rounded avatar-sm object-cover" style="width: 40px; height: 40px; object-fit: cover;">` : '<div class="avatar-sm d-inline-block"><span class="avatar-title rounded bg-light text-dark font-size-12">No Img</span></div>';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${startIndex + index + 1}</td>
                        <td class="text-center align-middle">${imgTag}</td>
                        <td class="align-middle"><strong>${item.nik}</strong></td>
                        <td class="align-middle">${item.nama}</td>
                        <td class="align-middle">${getKelaminText(item.jenis_kelamin)}</td>
                        <td class="align-middle">${item.kecamatan || '-'}</td>
                        <td class="align-middle">${item.whatsapp || '-'}</td>
                        <td class="align-middle">${statusHtml}</td>
                        <td class="align-middle">
                            <button onclick="editData(${index})" class="btn btn-sm btn-info waves-effect waves-light mb-1"><i class="mdi mdi-pencil"></i> Edit</button>
                            <a href="../bayi/bayi.php?id_member=${item.id_member}" class="btn btn-sm btn-warning waves-effect waves-light mb-1 text-dark font-weight-bold"><i class="mdi mdi-account-child"></i> Data Anak</a>
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        // Disable internal pagination/searching of DataTables because we use server-side manual
        dataTable = $('#datatable').DataTable({
            paging: false,
            searching: false,
            info: false,
            language: {
                emptyTable: "Data Member tidak ditemukan atau database kosong."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan koneksi ke sistem API.', 'error');
    }
}

function showForm() {
    document.getElementById('memberForm').reset();
    document.getElementById('id_member').value = '';
    document.getElementById('formTitle').innerText = 'Daftarkan Member Baru';
    
    // Reset tab to default (Identitas)
    $('.nav-tabs a[href="#tab-identitas"]').tab('show');
    
    if($().select2) {
        $('#jenis_kelamin').val('').trigger('change');
        $('#is_active').val('1').trigger('change');
    }
    
    if (quillAlamat) quillAlamat.setContents([]);
    document.getElementById('whatsapp_input').value = '';
    document.getElementById('whatsapp').value = '';
    $('.dropify-clear').click();
    
    $('#formModal').modal('show');
}

function hideForm() {
    $('#formModal').modal('hide');
}

function editData(index) {
    document.getElementById('formTitle').innerText = 'Edit Profil Member';
    
    // Reset tab to default (Identitas)
    $('.nav-tabs a[href="#tab-identitas"]').tab('show');
    
    const item = currentList[index];
    
    document.getElementById('id_member').value = item.id_member;
    document.getElementById('nik').value = item.nik;
    document.getElementById('nama').value = item.nama;
    document.getElementById('jenis_kelamin').value = item.jenis_kelamin !== null ? item.jenis_kelamin : '';
    
    if (quillAlamat) quillAlamat.clipboard.dangerouslyPasteHTML(item.alamat || '');
    
    document.getElementById('kecamatan').value = item.kecamatan || '';
    document.getElementById('alamat_gps').value = item.alamat_gps || '';
    document.getElementById('whatsapp_input').value = item.whatsapp || '';
    $('#whatsapp_input').trigger('input');
    document.getElementById('is_active').value = item.is_active;
    
    if($().select2) {
        $('#jenis_kelamin').val(item.jenis_kelamin !== null ? item.jenis_kelamin : '').trigger('change');
        $('#is_active').val(item.is_active).trigger('change');
    }
    
    // Kosongkan password field di form edit
    document.getElementById('password').value = '';
    
    $('#formModal').modal('show');
}

async function saveData(e) {
    e.preventDefault();
    
    const alamatText = quillAlamat.root.innerHTML === '<p><br></p>' ? '' : quillAlamat.root.innerHTML;
    document.getElementById('alamat_hidden').value = alamatText;

    const waClean = $('#whatsapp_input').cleanVal() ? $('#whatsapp_input').cleanVal() : $('#whatsapp_input').val().replace(/\D/g,'');
    document.getElementById('whatsapp').value = waClean;
    
    const form = document.getElementById('memberForm');
    const formData = new FormData(form);
    
    const idMember = document.getElementById('id_member').value;
    const url = idMember ? '../../api/member/update.php' : '../../api/member/save.php';
    
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
            fetchList(currentPage); 
        } else {
            Swal.fire('Gagal', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan pengiriman data ke server!', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = oriText;
}
</script>
