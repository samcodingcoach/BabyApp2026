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

                <!-- FORM (SAVE & UPDATE) -->
                <div id="formMember" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Form Member</h5>
                    <form id="memberForm" onsubmit="saveData(event)">
                        <input type="hidden" name="id_member" id="id_member">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nomor Induk (NIK) *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nik" id="nik" placeholder="No KTP / ID" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nama Lengkap *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama" id="nama" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Jenis Kelamin</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="jenis_kelamin" id="jenis_kelamin">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">Laki-Laki</option>
                                            <option value="0">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Alamat Domisili</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="alamat" id="alamat" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Kecamatan</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="kecamatan" id="kecamatan">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Alamat (Titik GPS)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="alamat_gps" id="alamat_gps" placeholder="Cth: Paste URL Maps">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">No. Whatsapp</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="whatsapp" id="whatsapp" placeholder="08123...">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Password Akun</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="(Kosongi jika tidak ingin diubah)">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status Member</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" name="is_active" id="is_active">
                                            <option value="1">Aktif (Approved)</option>
                                            <option value="0" class="text-danger">Belum Aktif / Blokir</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Foto Profile</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="photo" accept="image/jpeg, image/png, image/webp">
                                        <small class="form-text text-muted">Akan direname otomatis sesuai NIK pendaftar.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Profil Member</button>
                        </div>
                    </form>
                </div>

                <!-- PENCARIAN & FILTER -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Pencarian Pintar:</strong>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="filter_nik" placeholder="Ketik NIK eksak..." oninput="applyFilter()">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik bagian Nama Member..." oninput="applyFilter()">
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
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
                            <tr><td colspan="9" class="text-center">Memuat database member...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
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

<?php include '../includes/footer.php'; ?>

<script>
let currentPage = 1;
let totalPages = 1;
let currentList = [];
let filterTimer;

window.onload = () => {
    fetchList(currentPage);
};

// Mencegah tembakan API bertubi-tubi saat admin mengetik (Delay 500ms)
function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        currentPage = 1; // Kembali ke hal 1 setiap kali query filter berubah
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
            
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Data Member tidak ditemukan atau database kosong.</td></tr>';
                return;
            }
            
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
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="9" class="text-center">Terjadi gangguan koneksi ke sistem API.</td></tr>';
    }
}

function showForm() {
    $('#formMember').fadeIn();
    document.getElementById('memberForm').reset();
    document.getElementById('id_member').value = '';
    document.getElementById('formTitle').innerText = 'Daftarkan Member Baru';
}

function hideForm() {
    $('#formMember').fadeOut();
}

function editData(index) {
    showForm();
    document.getElementById('formTitle').innerText = 'Edit Profil Member';
    const item = currentList[index];
    
    document.getElementById('id_member').value = item.id_member;
    document.getElementById('nik').value = item.nik;
    document.getElementById('nama').value = item.nama;
    document.getElementById('jenis_kelamin').value = item.jenis_kelamin !== null ? item.jenis_kelamin : '';
    document.getElementById('alamat').value = item.alamat || '';
    document.getElementById('kecamatan').value = item.kecamatan || '';
    document.getElementById('alamat_gps').value = item.alamat_gps || '';
    document.getElementById('whatsapp').value = item.whatsapp || '';
    document.getElementById('is_active').value = item.is_active;
    
    // Kosongkan password field di form edit, admin harus ngetik ulang bila ingin merubah
    document.getElementById('password').value = '';
}

async function saveData(e) {
    e.preventDefault();
    const form = document.getElementById('memberForm');
    const formData = new FormData(form);
    
    const idMember = document.getElementById('id_member').value;
    const url = idMember ? '../../api/member/update.php' : '../../api/member/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            // Refresh tanpa pindah halaman
            fetchList(currentPage); 
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan pengiriman data gambar/form ke server!');
    }
}
</script>
