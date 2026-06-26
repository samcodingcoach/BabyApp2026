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

                <!-- FORM LAYANAN (SAVE & UPDATE) -->
                <div id="formLayanan" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Form Layanan</h5>
                    <form id="layananForm" onsubmit="saveLayanan(event)">
                        <!-- Digunakan untuk trigger update -->
                        <input type="hidden" name="id_layanan" id="id_layanan">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Kategori *</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="id_kategori_layanan" id="id_kategori_layanan" required></select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Kode Layanan *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="kode_layanan" id="kode_layanan" placeholder="Misal: LYN-01" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Nama Layanan *</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="nama_layanan" id="nama_layanan" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Durasi (Menit) *</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" name="durasi_menit" id="durasi_menit" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Deskripsi</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Status</label>
                                    <div class="col-sm-8">
                                        <select class="form-control font-weight-bold" name="is_active" id="is_active">
                                            <option value="1">Aktif</option>
                                            <option value="0" class="text-danger">Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Gambar 1 (Utama)</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="picture1" accept="image/*">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Gambar 2</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="picture2" accept="image/*">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Gambar 3</label>
                                    <div class="col-sm-8">
                                        <input type="file" class="form-control-file mt-1" name="picture3" accept="image/*">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">URL Video 1</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="video1" id="video1" placeholder="https://youtube.com/...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideFormLayanan()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Layanan</button>
                        </div>
                    </form>
                </div>

                <!-- FORM UBAH HARGA (SELECT-PRICE API) -->
                <div id="formHarga" style="display: none; background: #fffcf0; border: 1px solid #ffeeba; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-warning mb-4 text-dark"><i class="mdi mdi-cash-multiple mr-1"></i>Atur Harga Baru: <span id="labelNamaLayanan" class="font-weight-bold"></span></h5>
                    <form id="hargaForm" onsubmit="saveHarga(event)">
                        <input type="hidden" name="id_layanan" id="harga_id_layanan">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Tanggal Efektif</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" name="tanggal_efektif" id="harga_tanggal" required>
                                        <small class="form-text text-muted">Pilih hari ini agar sistem men-sync dan langsung menayangkannya!</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Harga (Rp)</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" name="harga" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Komisi Terapis (%)</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.01" class="form-control" name="komisi_persentase" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" onclick="hideFormHarga()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-warning waves-effect waves-light font-weight-bold px-4 text-dark">Catat & Sinkronisasi Harga</button>
                        </div>
                    </form>
                </div>

                <!-- FILTER & PENCARIAN -->
                <div class="bg-light p-3 border rounded mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <strong class="text-primary"><i class="mdi mdi-filter mr-1"></i>Pencarian Data:</strong>
                        </div>
                        <div class="col-md-3">
                            <select id="filter_kategori" class="custom-select" onchange="applyFilter()">
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
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
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
                            <tr><td colspan="9" class="text-center">Loading data...</td></tr>
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
    fetchKategoriList();
    fetchLayananList();
};

let currentLayananList = [];

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
        
        const response = await fetch(url);
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentLayananList = result.data;
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Belum ada master layanan.</td></tr>';
                return;
            }
            
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
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

// ================= LAYANAN CORE LOGIC =================
function showFormLayanan() {
    hideFormHarga();
    $('#formLayanan').fadeIn();
    document.getElementById('layananForm').reset();
    document.getElementById('id_layanan').value = '';
    document.getElementById('formTitle').innerText = 'Tambah Layanan Induk Baru';
}

function hideFormLayanan() {
    $('#formLayanan').fadeOut();
}

function editLayanan(index) {
    showFormLayanan();
    document.getElementById('formTitle').innerText = 'Edit Master Layanan';
    const item = currentLayananList[index];
    
    document.getElementById('id_layanan').value = item.id_layanan;
    document.getElementById('id_kategori_layanan').value = item.id_kategori_layanan;
    document.getElementById('kode_layanan').value = item.kode_layanan;
    document.getElementById('nama_layanan').value = item.nama_layanan;
    document.getElementById('durasi_menit').value = item.durasi_menit;
    document.getElementById('deskripsi').value = item.deskripsi || '';
    document.getElementById('is_active').value = item.is_active;
    document.getElementById('video1').value = item.video1 || '';
}

async function saveLayanan(e) {
    e.preventDefault();
    const form = document.getElementById('layananForm');
    const formData = new FormData(form);
    
    const idLayanan = document.getElementById('id_layanan').value;
    const url = idLayanan ? '../../api/layanan/update.php' : '../../api/layanan/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideFormLayanan();
            fetchLayananList();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem pengiriman data!');
    }
}

async function nonactiveLayanan(id) {
    if (!confirm('Yakin ingin membekukan/menonaktifkan layanan ini dari tayangan publik?')) return;
    
    const formData = new FormData();
    formData.append('id_layanan', id);
    
    try {
        const response = await fetch('../../api/layanan/nonactive.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.status === 'success') {
            alert(result.message);
            fetchLayananList();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) { alert('Error sistem'); }
}

// ================= SMART SELECT-PRICE LOGIC =================
function openFormHarga(id_layanan, namaLayanan) {
    hideFormLayanan();
    $('#formHarga').fadeIn();
    document.getElementById('hargaForm').reset();
    document.getElementById('harga_id_layanan').value = id_layanan;
    document.getElementById('labelNamaLayanan').innerText = namaLayanan;
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('harga_tanggal').value = today;
}

function hideFormHarga() {
    $('#formHarga').fadeOut();
}

async function saveHarga(e) {
    e.preventDefault();
    const form = document.getElementById('hargaForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('../../api/layanan/select-price.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message); 
            hideFormHarga();
            fetchLayananList(); 
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Error sistem!');
    }
}
</script>
