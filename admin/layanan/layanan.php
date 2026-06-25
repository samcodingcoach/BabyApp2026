<?php
session_start();
// Proteksi halaman admin, harus login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Layanan</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;}
        .form-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #fafafa; display: none; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 180px; vertical-align: top; font-weight: bold;}
        .form-group input, .form-group select, .form-group textarea { padding: 5px; width: 250px; }
        .back-link { text-decoration: none; color: #0056b3; font-weight: bold; margin-right: 20px; }
        img.thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #ccc; }
        .badge-active { color: green; font-weight: bold; }
        .badge-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Layanan Induk</h2>

    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showFormLayanan()">+ Tambah Layanan</button>

    <!-- FORM LAYANAN (SAVE & UPDATE) -->
    <div class="form-container" id="formLayanan">
        <h3 id="formTitle">Form Layanan</h3>
        <form id="layananForm" onsubmit="saveLayanan(event)">
            <!-- Digunakan untuk trigger update -->
            <input type="hidden" name="id_layanan" id="id_layanan">
            
            <div class="form-group">
                <label>Kategori *</label>
                <select name="id_kategori_layanan" id="id_kategori_layanan" required></select>
            </div>
            <div class="form-group">
                <label>Kode Layanan *</label>
                <input type="text" name="kode_layanan" id="kode_layanan" placeholder="Misal: LYN-01" required>
            </div>
            <div class="form-group">
                <label>Nama Layanan *</label>
                <input type="text" name="nama_layanan" id="nama_layanan" required>
            </div>
            <div class="form-group">
                <label>Durasi (Menit) *</label>
                <input type="number" name="durasi_menit" id="durasi_menit" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label>Gambar 1 (Utama)</label>
                <input type="file" name="picture1" accept="image/*">
            </div>
            <div class="form-group">
                <label>Gambar 2</label>
                <input type="file" name="picture2" accept="image/*">
            </div>
            <div class="form-group">
                <label>Gambar 3</label>
                <input type="file" name="picture3" accept="image/*">
            </div>
            <div class="form-group">
                <label>URL Video 1</label>
                <input type="text" name="video1" id="video1" placeholder="https://youtube.com/...">
            </div>
            
            <button type="submit" style="background: blue; color: white;">Simpan Layanan</button>
            <button type="button" onclick="hideFormLayanan()">Batal</button>
        </form>
    </div>

    <!-- FORM UBAH HARGA (SELECT-PRICE API) -->
    <div class="form-container" id="formHarga" style="border-color: orange; background: #fffcf0;">
        <h3 style="color: orange;">Atur Harga Baru: <span id="labelNamaLayanan" style="color:black;"></span></h3>
        <form id="hargaForm" onsubmit="saveHarga(event)">
            <input type="hidden" name="id_layanan" id="harga_id_layanan">
            <div class="form-group">
                <label>Tanggal Efektif</label>
                <input type="date" name="tanggal_efektif" id="harga_tanggal" required>
                <br><span style="font-size:0.8em; color:#666; margin-left: 185px;">(Pilih hari ini agar sistem men-sync dan langsung menayangkannya!)</span>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" required>
            </div>
            <div class="form-group">
                <label>Komisi Persentase (%)</label>
                <input type="number" step="0.01" name="komisi_persentase" required>
            </div>
            <button type="submit" style="background: orange; color: white;">Catat & Sinkronisasi Harga</button>
            <button type="button" onclick="hideFormHarga()">Batal</button>
        </form>
    </div>

    <!-- FILTER & PENCARIAN -->
    <div style="margin-bottom: 15px; background: #eef5ff; padding: 15px; border-radius: 5px; border: 1px solid #cce0ff;">
        <strong style="margin-right: 10px; color: #0056b3;">Pencarian Data:</strong>
        <select id="filter_kategori" onchange="applyFilter()" style="padding: 7px; width: 220px; border-radius: 3px; border: 1px solid #ccc;">
            <option value="">-- Semua Kategori --</option>
        </select>
        
        <input type="text" id="filter_nama" placeholder="Ketik Nama Layanan..." oninput="applyFilter()" style="padding: 7px; margin-left: 10px; width: 300px; border-radius: 3px; border: 1px solid #ccc;">
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th>Gambar</th>
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
            <tr><td colspan="9" style="text-align: center;">Loading data...</td></tr>
        </tbody>
    </table>
</div>

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
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">Belum ada master layanan.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const hargaTxt = item.harga ? 'Rp ' + parseFloat(item.harga).toLocaleString('id-ID') : '<i style="color:red">Belum Diset</i>';
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge-active">Aktif</span>' : '<span class="badge-inactive">Non-Aktif</span>';
                
                // Mencegah error gambar yang kosong / belum upload
                const imgTag = item.picture1 ? `<img src="../../images/${item.picture1}" class="thumb">` : '<div class="thumb" style="background:#eee;text-align:center;font-size:10px;line-height:50px;">No Img</div>';

                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td style="text-align: center;">${imgTag}</td>
                        <td>${item.kode_layanan}</td>
                        <td><strong>${item.nama_layanan}</strong></td>
                        <td>${item.nama_kategori || '-'}</td>
                        <td>${item.durasi_menit} Menit</td>
                        <td style="font-weight:bold; color:orange;">${hargaTxt}</td>
                        <td>${statusHtml}</td>
                        <td>
                            <button onclick="editLayanan(${index})">Edit Data</button>
                            <button onclick="openFormHarga(${item.id_layanan}, '${item.nama_layanan}')" style="background:orange; color:white; border:none; border-radius:3px; padding:7px;">Ubah Harga</button>
                            ${parseInt(item.is_active) === 1 ? `<button onclick="nonactiveLayanan(${item.id_layanan})" style="background:red; color:white; border:none; border-radius:3px; padding:7px;">Nonaktifkan</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="9" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

// ================= LAYANAN CORE LOGIC =================
function showFormLayanan() {
    hideFormHarga();
    document.getElementById('formLayanan').style.display = 'block';
    document.getElementById('layananForm').reset();
    document.getElementById('id_layanan').value = '';
    document.getElementById('formTitle').innerText = 'Tambah Layanan Induk Baru';
}

function hideFormLayanan() {
    document.getElementById('formLayanan').style.display = 'none';
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
    const formData = new FormData(form); // Otomatis menangkap <input type="file">
    
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
    document.getElementById('formHarga').style.display = 'block';
    document.getElementById('hargaForm').reset();
    document.getElementById('harga_id_layanan').value = id_layanan;
    document.getElementById('labelNamaLayanan').innerText = namaLayanan;
    
    // Set default date ke hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('harga_tanggal').value = today;
}

function hideFormHarga() {
    document.getElementById('formHarga').style.display = 'none';
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
            alert(result.message); // Memunculkan konfirmasi apakah tersinkronisasi atau hanya history
            hideFormHarga();
            fetchLayananList(); // Refresh table agar Harga Tayang Saat Ini berubah seketika!
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Error sistem!');
    }
}
</script>

</body>
</html>
