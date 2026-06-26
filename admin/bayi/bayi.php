<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}

// Memaksa admin untuk masuk ke halaman ini dengan membawa ID Member
$id_member = $_GET['id_member'] ?? null;
if (!$id_member) {
    echo "<script>alert('Akses Ditolak! Anda harus memilih Member/Orang Tua terlebih dahulu.'); window.location.href='../member/member.php';</script>";
    exit();
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">Manajemen Rekam Medis Anak</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="../member/member.php">Member</a></li>
                    <li class="breadcrumb-item active">Data Anak</li>
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
                    <div>
                        <h4 class="card-title mb-1">Keluarga Member: <span id="namaOrangTua" class="text-primary font-weight-bold">Sedang Memuat...</span></h4>
                        <a href="../member/member.php" class="text-muted"><i class="mdi mdi-arrow-left"></i> Kembali ke Daftar Member</a>
                    </div>
                    <button onclick="showForm()" class="btn btn-warning waves-effect waves-light font-weight-bold text-dark">
                        <i class="mdi mdi-plus mr-1"></i> Daftarkan Anak Baru
                    </button>
                </div>

                <!-- FORM (SAVE & UPDATE) -->
                <div id="formBayi" style="display: none; background: #fffcf0; border: 1px solid #ffeeba; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-warning mb-4 text-dark" id="formTitle">Form Rekam Medis Anak</h5>
                    <form id="bayiForm" onsubmit="saveData(event)">
                        <!-- ID Bayi disembunyikan (Trigger Mode Edit) -->
                        <input type="hidden" name="id_bayi" id="id_bayi">
                        <!-- ID Member dikunci mati ke parent saat ini -->
                        <input type="hidden" name="id_member" id="id_member" value="<?= htmlspecialchars($id_member) ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nama Anak/Bayi *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_bayi" id="nama_bayi" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Anak Ke-</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" name="anak_ke" id="anak_ke" min="1" placeholder="Cth: 1">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Tanggal Lahir</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Jenis Kelamin</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="jenis_kelamin" id="jenis_kelamin">
                                            <option value="">-- Belum Diset --</option>
                                            <option value="1">Laki-Laki</option>
                                            <option value="0">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Berat Badan (Kg)</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.01" class="form-control" name="berat_kg" id="berat_kg" placeholder="Cth: 4.5">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Tinggi/Panjang (Cm)</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.1" class="form-control" name="tinggi_cm" id="tinggi_cm" placeholder="Cth: 50.5">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Lingkar Kepala (Cm)</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.1" class="form-control" name="lingkar_kepala_cm" id="lingkar_kepala_cm">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Golongan Darah</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold text-danger" name="golongan_darah" id="golongan_darah">
                                            <option value="">-- Belum Diset --</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="AB">AB</option>
                                            <option value="O">O</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Riwayat Alergi Khusus</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="alergi" id="alergi" rows="2" placeholder="Cth: Alergi susu sapi, debu..."></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Catatan Medis</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="keterangan" id="keterangan" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status Profil Anak</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" name="is_active" id="is_active">
                                            <option value="1">Aktif</option>
                                            <option value="0" class="text-danger">Sembunyikan / Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Foto Bayi (JPG/PNG)</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="photo" accept="image/jpeg, image/png, image/webp">
                                        <small class="form-text text-muted">File lama otomatis akan tertimpa.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-warning waves-effect waves-light font-weight-bold px-4 text-dark">Simpan Rekam Bayi</button>
                        </div>
                    </form>
                </div>

                <!-- PENCARIAN & FILTER -->
                <div class="p-3 border rounded mb-4" style="background-color: #fffcf0; border-color: #ffeeba;">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-warning text-dark"><i class="mdi mdi-magnify mr-1"></i>Cari Anak di Keluarga Ini:</strong>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control border-warning" id="filter_nama" placeholder="Ketik Nama Anak..." oninput="applyFilter()">
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
                                <th>Nama Anak</th>
                                <th>L/P</th>
                                <th>Estimasi Usia</th>
                                <th>B / T / LK</th>
                                <th>Gol. Darah</th>
                                <th>Alergi</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr><td colspan="9" class="text-center">Menarik rekam medis anak...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Menangkap ID Member dari PHP ke Javascript
const ID_MEMBER = <?= json_encode($id_member) ?>;

let currentList = [];
let filterTimer;

window.onload = async () => {
    await fetchParentInfo(); 
    fetchList();             
};

async function fetchParentInfo() {
    try {
        const response = await fetch('../../api/member/list.php?id_member=' + ID_MEMBER);
        const result = await response.json();
        if (result.status === 'success' && result.data.length > 0) {
            const ortu = result.data[0];
            document.getElementById('namaOrangTua').innerText = ortu.nama + ' (NIK: ' + ortu.nik + ')';
        } else {
            document.getElementById('namaOrangTua').innerText = 'Data Member Terhapus / Ilegal';
            document.getElementById('namaOrangTua').classList.add('text-danger');
        }
    } catch (e) {
        document.getElementById('namaOrangTua').innerText = 'Gagal memuat sistem member';
    }
}

function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        fetchList();
    }, 500);
}

function getKelaminText(id) {
    if (id === '1' || id === 1) return 'Laki-Laki';
    if (id === '0' || id === 0) return 'Perempuan';
    return '-';
}

function hitungUsia(tanggalLahir) {
    if (!tanggalLahir) return '<i class="text-muted">Kosong</i>';
    const lahir = new Date(tanggalLahir);
    const sekarang = new Date();
    
    let tahun = sekarang.getFullYear() - lahir.getFullYear();
    let bulan = sekarang.getMonth() - lahir.getMonth();
    
    if (bulan < 0 || (bulan === 0 && sekarang.getDate() < lahir.getDate())) {
        tahun--;
        bulan += 12;
    }
    
    let result = '';
    if (tahun > 0) result += tahun + ' th ';
    if (bulan > 0) result += bulan + ' bln';
    
    return result || '0 bln (Baru Lahir)';
}

async function fetchList() {
    try {
        const filterNama = document.getElementById('filter_nama').value.trim();
        
        let url = '../../api/bayi/list.php?id_member=' + ID_MEMBER;
        if (filterNama) url += '&nama_bayi=' + encodeURIComponent(filterNama);
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Belum ada anak/bayi yang didaftarkan untuk Member ini.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const imgTag = item.photo ? `<img src="../../images/${item.photo}" class="rounded-circle avatar-sm object-cover" style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #ddd;">` : '<div class="avatar-sm d-inline-block"><span class="avatar-title rounded-circle bg-light text-dark font-size-12 border">No Img</span></div>';
                
                const btlk = [];
                if (item.berat_kg) btlk.push(item.berat_kg + 'kg');
                if (item.tinggi_cm) btlk.push(item.tinggi_cm + 'cm');
                if (item.lingkar_kepala_cm) btlk.push(item.lingkar_kepala_cm + 'cm');
                const strBtlk = btlk.length > 0 ? btlk.join(' / ') : '-';

                const styleRow = parseInt(item.is_active) === 0 ? 'background:#ffebeb;' : '';

                tbody.innerHTML += `
                    <tr style="${styleRow}">
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="text-center align-middle">${imgTag}</td>
                        <td class="align-middle"><strong>${item.nama_bayi}</strong><br><small class="text-muted">Anak ke-${item.anak_ke || '?'}</small></td>
                        <td class="align-middle">${getKelaminText(item.jenis_kelamin)}</td>
                        <td class="align-middle">${hitungUsia(item.tanggal_lahir)}</td>
                        <td class="align-middle">${strBtlk}</td>
                        <td class="align-middle"><span class="text-danger font-weight-bold">${item.golongan_darah || '-'}</span></td>
                        <td class="align-middle"><small class="text-warning font-weight-bold">${item.alergi || '-'}</small></td>
                        <td class="align-middle">
                            <button onclick="editData(${index})" class="btn btn-sm btn-info waves-effect waves-light"><i class="mdi mdi-pencil"></i> Edit</button>
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
    $('#formBayi').fadeIn();
    document.getElementById('bayiForm').reset();
    document.getElementById('id_bayi').value = '';
    document.getElementById('id_member').value = ID_MEMBER;
    document.getElementById('formTitle').innerText = 'Daftarkan Rekam Medis Anak Baru';
}

function hideForm() {
    $('#formBayi').fadeOut();
}

function editData(index) {
    showForm();
    document.getElementById('formTitle').innerText = 'Edit Rekam Medis Anak';
    const item = currentList[index];
    
    document.getElementById('id_bayi').value = item.id_bayi;
    document.getElementById('id_member').value = item.id_member; 
    document.getElementById('nama_bayi').value = item.nama_bayi;
    document.getElementById('anak_ke').value = item.anak_ke || '';
    document.getElementById('tanggal_lahir').value = item.tanggal_lahir || '';
    document.getElementById('jenis_kelamin').value = item.jenis_kelamin !== null ? item.jenis_kelamin : '';
    document.getElementById('berat_kg').value = item.berat_kg || '';
    document.getElementById('tinggi_cm').value = item.tinggi_cm || '';
    document.getElementById('lingkar_kepala_cm').value = item.lingkar_kepala_cm || '';
    document.getElementById('golongan_darah').value = item.golongan_darah || '';
    document.getElementById('alergi').value = item.alergi || '';
    document.getElementById('keterangan').value = item.keterangan || '';
    document.getElementById('is_active').value = item.is_active;
}

async function saveData(e) {
    e.preventDefault();
    const form = document.getElementById('bayiForm');
    const formData = new FormData(form);
    
    const idBayi = document.getElementById('id_bayi').value;
    const url = idBayi ? '../../api/bayi/update.php' : '../../api/bayi/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
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
        alert('Terjadi kesalahan pengiriman Form/Foto ke server!');
    }
}
</script>
