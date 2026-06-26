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

                <!-- FORM TAMBAH -->
                <div id="formContainer" style="display: none; background: #fffcf0; border: 1px solid #ffeeba; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-warning mb-4 text-dark" id="formTitle"><i class="mdi mdi-cash-multiple mr-1"></i>Catat / Perbarui Harga</h5>
                    <form id="hargaForm" onsubmit="saveData(event)">
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
                                        <input type="number" class="form-control" name="harga" id="harga" required>
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
                        
                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" class="btn btn-warning waves-effect waves-light font-weight-bold px-4 text-dark">Simpan Harga</button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
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
                            <tr><td colspan="7" class="text-center">Loading data...</td></tr>
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
        const response = await fetch('../../api/harga-layanan/list-harga.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada riwayat harga tercatat.</td></tr>';
                return;
            }
            
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
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="7" class="text-center">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

function showFormAdd() {
    $('#formContainer').fadeIn();
    document.getElementById('hargaForm').reset();
    document.getElementById('layanan_label').innerHTML = '';
    document.getElementById('id_layanan').value = '';
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_efektif').value = today;
}

function hideForm() {
    $('#formContainer').fadeOut();
    document.getElementById('hargaForm').reset();
    document.getElementById('layanan_label').innerHTML = '';
    document.getElementById('id_layanan').value = '';
}

async function saveData(e) {
    e.preventDefault();
    const form = document.getElementById('hargaForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('../../api/harga-layanan/save.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message); 
            hideForm();
            fetchHargaList(); 
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}
</script>
