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

                <!-- FORM (SAVE & UPDATE) -->
                <div id="formTerapis" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Form Terapis</h5>
                    <form id="terapisForm" onsubmit="saveData(event)">
                        <input type="hidden" name="id_terapis" id="id_terapis">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Kode Terapis *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="kode_terapis" id="kode_terapis" placeholder="Contoh: TRP01" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nama Lengkap *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_terapis" id="nama_terapis" required>
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
                                    <label class="col-sm-4 col-form-label">Tanggal Lahir</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Agama</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="agama" id="agama">
                                            <option value="1">Islam</option>
                                            <option value="2">Kristen</option>
                                            <option value="3">Katolik</option>
                                            <option value="4">Hindu</option>
                                            <option value="5">Budha</option>
                                            <option value="6">Lainnya</option>
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
                                        <input type="text" class="form-control" name="kecamatan" id="kecamatan" placeholder="Cth: Lowokwaru">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Alamat (Titik GPS)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="alamat_gps" id="alamat_gps" placeholder="Paste link GMaps">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Pendidikan Terakhir</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="pendidikan" id="pendidikan" value="SMA/K">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Akun Instagram</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="ig" id="ig" placeholder="@username">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Keterangan / Bio</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="keterangan" id="keterangan" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status Tayang</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" name="is_active" id="is_active">
                                            <option value="1">Aktif Tayang</option>
                                            <option value="0" class="text-danger">Non-Aktif (Bekukan)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Foto Profile</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="foto" accept="image/jpeg, image/png, image/webp">
                                        <small class="form-text text-muted">Akan direname otomatis sesuai Kode Terapis.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Profil Terapis</button>
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
                            <input type="text" class="form-control" id="filter_kode" placeholder="Ketik Kode (Cth: TRP01)" oninput="applyFilter()">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="filter_nama" placeholder="Ketik Nama Terapis..." oninput="applyFilter()">
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
                            <tr><td colspan="10" class="text-center">Memuat database terapis...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
window.onload = () => {
    fetchList();
};

let currentList = [];
let filterTimer;

// Mencegah tembakan API bertubi-tubi saat admin mengetik (Delay 500ms)
function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        fetchList();
    }, 500);
}

// Konversi Angka ke String Agama
function getAgamaText(id) {
    const list = {1: 'Islam', 2: 'Kristen', 3: 'Katolik', 4: 'Hindu', 5: 'Budha', 6: 'Lainnya'};
    return list[id] || '-';
}

// Konversi Angka ke String Kelamin
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
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">Data Terapis tidak ditemukan.</td></tr>';
                return;
            }
            
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
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">Terjadi gangguan koneksi ke sistem API.</td></tr>';
    }
}

// Munculkan Form Kosong (Mode Tambah)
function showForm() {
    $('#formTerapis').fadeIn();
    document.getElementById('terapisForm').reset();
    document.getElementById('id_terapis').value = '';
    document.getElementById('pendidikan').value = 'SMA/K'; 
    document.getElementById('formTitle').innerText = 'Daftarkan Terapis Baru';
}

function hideForm() {
    $('#formTerapis').fadeOut();
}

// Munculkan Form Terisi (Mode Edit)
function editData(index) {
    showForm();
    document.getElementById('formTitle').innerText = 'Edit Profil Terapis';
    const item = currentList[index];
    
    document.getElementById('id_terapis').value = item.id_terapis;
    document.getElementById('kode_terapis').value = item.kode_terapis;
    document.getElementById('nama_terapis').value = item.nama_terapis;
    document.getElementById('jenis_kelamin').value = item.jenis_kelamin !== null ? item.jenis_kelamin : '';
    document.getElementById('tanggal_lahir').value = item.tanggal_lahir || '';
    document.getElementById('agama').value = item.agama || '1';
    document.getElementById('alamat').value = item.alamat || '';
    document.getElementById('kecamatan').value = item.kecamatan || '';
    document.getElementById('alamat_gps').value = item.alamat_gps || '';
    document.getElementById('pendidikan').value = item.pendidikan || '';
    document.getElementById('ig').value = item.ig || '';
    document.getElementById('keterangan').value = item.keterangan || '';
    document.getElementById('is_active').value = item.is_active;
}

// Eksekusi API Save / Update
async function saveData(e) {
    e.preventDefault();
    const form = document.getElementById('terapisForm');
    const formData = new FormData(form);
    
    // Validasi jalur: Jika ada ID maka Update, jika kosong maka Insert (Save)
    const idTerapis = document.getElementById('id_terapis').value;
    const url = idTerapis ? '../../api/terapis/update.php' : '../../api/terapis/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchList(); // Tarik ulang tabel
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan pengiriman data gambar/form ke server!');
    }
}

// Eksekusi API NonAktif
async function nonactiveData(id) {
    if (!confirm('Yakin ingin membekukan (nonaktifkan) terapis ini? Data tidak akan dihapus dari histori transaksi namun akan hilang dari aplikasi publik.')) return;
    
    const formData = new FormData();
    formData.append('id_terapis', id);
    
    try {
        const response = await fetch('../../api/terapis/nonactive.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.status === 'success') {
            alert(result.message);
            fetchList();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) { alert('Terjadi gangguan fungsi non-aktif'); }
}
</script>
