<?php
session_start();
// Proteksi halaman admin, wajib login!
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
    <title>Manajemen Terapis</title>
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
    <h2>Manajemen Terapis</h2>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showForm()">+ Daftarkan Terapis Baru</button>

    <!-- FORM (SAVE & UPDATE) -->
    <div class="form-container" id="formTerapis">
        <h3 id="formTitle">Form Terapis</h3>
        <form id="terapisForm" onsubmit="saveData(event)">
            <input type="hidden" name="id_terapis" id="id_terapis">
            
            <div class="form-group">
                <label>Kode Terapis *</label>
                <input type="text" name="kode_terapis" id="kode_terapis" placeholder="Contoh: TRP01" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="nama_terapis" id="nama_terapis" required>
            </div>
            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin">
                    <option value="">-- Pilih --</option>
                    <option value="1">Laki-Laki</option>
                    <option value="0">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir">
            </div>
            <div class="form-group">
                <label>Agama</label>
                <select name="agama" id="agama">
                    <option value="1">Islam</option>
                    <option value="2">Kristen</option>
                    <option value="3">Katolik</option>
                    <option value="4">Hindu</option>
                    <option value="5">Budha</option>
                    <option value="6">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label>Alamat Domisili</label>
                <textarea name="alamat" id="alamat" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Kecamatan</label>
                <input type="text" name="kecamatan" id="kecamatan" placeholder="Cth: Lowokwaru">
            </div>
            <div class="form-group">
                <label>Alamat (Titik GPS)</label>
                <input type="text" name="alamat_gps" id="alamat_gps" placeholder="Paste link GMaps">
            </div>
            <div class="form-group">
                <label>Pendidikan Terakhir</label>
                <input type="text" name="pendidikan" id="pendidikan" value="SMA/K">
            </div>
            <div class="form-group">
                <label>Akun Instagram</label>
                <input type="text" name="ig" id="ig" placeholder="@username">
            </div>
            <div class="form-group">
                <label>Keterangan / Bio</label>
                <textarea name="keterangan" id="keterangan" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Status Tayang</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif Tayang</option>
                    <option value="0">Non-Aktif (Bekukan)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto Profile (JPG/PNG)</label>
                <input type="file" name="foto" accept="image/jpeg, image/png, image/webp">
                <br><small style="margin-left: 185px; color:#666;">(Otomatis direname sesuai Kode Terapis, hindari nama spasi di kode jika memungkinkan)</small>
            </div>
            
            <button type="submit" style="background: blue; color: white;">Simpan Profil Terapis</button>
            <button type="button" onclick="hideForm()">Batal</button>
        </form>
    </div>

    <!-- PENCARIAN & FILTER -->
    <div style="margin-bottom: 15px; background: #eef5ff; padding: 15px; border-radius: 5px; border: 1px solid #cce0ff;">
        <strong style="margin-right: 10px; color: #0056b3;">Pencarian Pintar:</strong>
        <input type="text" id="filter_kode" placeholder="Ketik Kode (Cth: TRP01)" oninput="applyFilter()" style="padding: 7px; width: 180px; border-radius: 3px; border: 1px solid #ccc;">
        <input type="text" id="filter_nama" placeholder="Ketik Nama Terapis..." oninput="applyFilter()" style="padding: 7px; margin-left: 10px; width: 250px; border-radius: 3px; border: 1px solid #ccc;">
    </div>

    <!-- TABEL DATA -->
    <table>
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
                <th style="width: 20%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="10" style="text-align: center;">Memuat database terapis...</td></tr>
        </tbody>
    </table>
</div>

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
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">Data Terapis tidak ditemukan.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge-active">Aktif</span>' : '<span class="badge-inactive">Non-Aktif</span>';
                const imgTag = item.foto ? `<img src="../../images/${item.foto}" class="thumb">` : '<div class="thumb" style="background:#eee;text-align:center;font-size:10px;line-height:50px;">No Img</div>';

                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td style="text-align: center;">${imgTag}</td>
                        <td><strong>${item.kode_terapis}</strong></td>
                        <td>${item.nama_terapis}</td>
                        <td>${getKelaminText(item.jenis_kelamin)}</td>
                        <td>${getAgamaText(item.agama)}</td>
                        <td>${item.pendidikan || '-'}</td>
                        <td>${item.kecamatan || '-'}</td>
                        <td>${statusHtml}</td>
                        <td>
                            <button onclick="editData(${index})">Edit Data</button>
                            ${parseInt(item.is_active) === 1 ? `<button onclick="nonactiveData(${item.id_terapis})" style="background:red; color:white; border:none; border-radius:3px; padding:7px;">Nonaktifkan</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="10" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center;">Terjadi gangguan koneksi ke sistem API.</td></tr>';
    }
}

// Munculkan Form Kosong (Mode Tambah)
function showForm() {
    document.getElementById('formTerapis').style.display = 'block';
    document.getElementById('terapisForm').reset();
    document.getElementById('id_terapis').value = '';
    document.getElementById('pendidikan').value = 'SMA/K'; // Reset to default
    document.getElementById('formTitle').innerText = 'Daftarkan Terapis Baru';
}

function hideForm() {
    document.getElementById('formTerapis').style.display = 'none';
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

</body>
</html>
