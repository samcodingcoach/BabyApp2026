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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Rekam Medis Bayi</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;}
        .form-container { border: 1px solid #f5c6cb; padding: 15px; margin-bottom: 20px; background: #fffdf5; display: none; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 180px; vertical-align: top; font-weight: bold;}
        .form-group input, .form-group select, .form-group textarea { padding: 5px; width: 250px; }
        .back-link { text-decoration: none; color: #0056b3; font-weight: bold; margin-right: 20px; }
        img.thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Bayi (Keluarga Member: <span id="namaOrangTua" style="color:#0056b3;">Sedang Memuat...</span>)</h2>
    <a href="../member/member.php" class="back-link">&laquo; Kembali ke Daftar Member</a>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showForm()" style="background:#ff9800; color:white; border:none; border-radius:3px; padding:10px 15px; font-weight:bold;">+ Daftarkan Anak</button>

    <!-- FORM (SAVE & UPDATE) -->
    <div class="form-container" id="formBayi">
        <h3 id="formTitle" style="color: #ff9800;">Form Rekam Medis Anak</h3>
        <form id="bayiForm" onsubmit="saveData(event)">
            <!-- ID Bayi disembunyikan (Trigger Mode Edit) -->
            <input type="hidden" name="id_bayi" id="id_bayi">
            <!-- ID Member dikunci mati ke parent saat ini -->
            <input type="hidden" name="id_member" id="id_member" value="<?= htmlspecialchars($id_member) ?>">
            
            <div class="form-group">
                <label>Nama Anak/Bayi *</label>
                <input type="text" name="nama_bayi" id="nama_bayi" required>
            </div>
            <div class="form-group">
                <label>Anak Ke-</label>
                <input type="number" name="anak_ke" id="anak_ke" min="1" placeholder="Cth: 1">
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir">
            </div>
            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin">
                    <option value="">-- Belum Diset --</option>
                    <option value="1">Laki-Laki</option>
                    <option value="0">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Berat Badan (Kg)</label>
                <input type="number" step="0.01" name="berat_kg" id="berat_kg" placeholder="Cth: 4.5">
            </div>
            <div class="form-group">
                <label>Tinggi/Panjang (Cm)</label>
                <input type="number" step="0.1" name="tinggi_cm" id="tinggi_cm" placeholder="Cth: 50.5">
            </div>
            <div class="form-group">
                <label>Lingkar Kepala (Cm)</label>
                <input type="number" step="0.1" name="lingkar_kepala_cm" id="lingkar_kepala_cm">
            </div>
            <div class="form-group">
                <label>Golongan Darah</label>
                <select name="golongan_darah" id="golongan_darah">
                    <option value="">-- Belum Diset --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                </select>
            </div>
            <div class="form-group">
                <label>Riwayat Alergi Khusus</label>
                <textarea name="alergi" id="alergi" rows="2" placeholder="Cth: Alergi susu sapi, debu..."></textarea>
            </div>
            <div class="form-group">
                <label>Catatan Medis</label>
                <textarea name="keterangan" id="keterangan" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Status Profil Anak</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Sembunyikan / Non-Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto Bayi (JPG/PNG)</label>
                <input type="file" name="photo" accept="image/jpeg, image/png, image/webp">
                <br><small style="margin-left: 185px; color:#666;">(File lama akan tertimpa cerdas jika Anda upload baru)</small>
            </div>
            
            <button type="submit" style="background: orange; color: white; border-radius:3px; padding:8px 15px; border:none; font-weight:bold;">Simpan Rekam Bayi</button>
            <button type="button" onclick="hideForm()" style="border-radius:3px; padding:8px 15px; border:1px solid #ccc;">Batal</button>
        </form>
    </div>

    <!-- PENCARIAN & FILTER -->
    <div style="margin-bottom: 15px; background: #fffcf0; padding: 15px; border-radius: 5px; border: 1px solid #ffeeba;">
        <strong style="margin-right: 10px; color: #856404;">Cari Anak di Keluarga Ini:</strong>
        <input type="text" id="filter_nama" placeholder="Ketik Nama Anak..." oninput="applyFilter()" style="padding: 7px; width: 300px; border-radius: 3px; border: 1px solid #ccc;">
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="text-align: center;">Foto</th>
                <th>Nama Anak</th>
                <th>L/P</th>
                <th>Estimasi Usia</th>
                <th>B / T / LK</th>
                <th>Gol. Darah</th>
                <th>Alergi</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="9" style="text-align: center;">Menarik rekam medis anak...</td></tr>
        </tbody>
    </table>
</div>

<script>
// Menangkap ID Member dari PHP ke Javascript
const ID_MEMBER = <?= json_encode($id_member) ?>;

let currentList = [];
let filterTimer;

window.onload = async () => {
    await fetchParentInfo(); // Render nama member di judul
    fetchList();             // Render tabel anak
};

// Hit API Member untuk menarik Nama dan NIK orang tua
async function fetchParentInfo() {
    try {
        const response = await fetch('../../api/member/list.php?id_member=' + ID_MEMBER);
        const result = await response.json();
        if (result.status === 'success' && result.data.length > 0) {
            const ortu = result.data[0];
            document.getElementById('namaOrangTua').innerText = ortu.nama + ' (NIK: ' + ortu.nik + ')';
        } else {
            document.getElementById('namaOrangTua').innerText = 'Data Member Terhapus / Ilegal';
            document.getElementById('namaOrangTua').style.color = 'red';
        }
    } catch (e) {
        document.getElementById('namaOrangTua').innerText = 'Gagal memuat sistem member';
    }
}

// Timer Anti-Spam Saat Mengetik Search
function applyFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        fetchList();
    }, 500);
}

// Konverter 1/0 jadi String
function getKelaminText(id) {
    if (id === '1' || id === 1) return 'Laki-Laki';
    if (id === '0' || id === 0) return 'Perempuan';
    return '-';
}

// Kecerdasan Javascript untuk menghitung Umur presisi (Tahun & Bulan) dari Tanggal Lahir
function hitungUsia(tanggalLahir) {
    if (!tanggalLahir) return '<i style="color:#aaa">Kosong</i>';
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

// Tarik data tabel (Difilter otomatis hanya untuk ID_MEMBER ini)
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
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">Belum ada anak/bayi yang didaftarkan untuk Member ini.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                // Circle Thumbnail
                const imgTag = item.photo ? `<img src="../../images/${item.photo}" class="thumb">` : '<div class="thumb" style="background:#eee;text-align:center;font-size:10px;line-height:60px;display:inline-block;">No Img</div>';
                
                // Meringkas tampilan medis Berat, Tinggi, Lingkar Kepala (B/T/LK)
                const btlk = [];
                if (item.berat_kg) btlk.push(item.berat_kg + 'kg');
                if (item.tinggi_cm) btlk.push(item.tinggi_cm + 'cm');
                if (item.lingkar_kepala_cm) btlk.push(item.lingkar_kepala_cm + 'cm');
                const strBtlk = btlk.length > 0 ? btlk.join(' / ') : '-';

                // Render Background Merah jika anak di-Nonaktifkan
                const styleRow = parseInt(item.is_active) === 0 ? 'background:#ffebeb;' : '';

                tbody.innerHTML += `
                    <tr style="${styleRow}">
                        <td style="text-align: center;">${index + 1}</td>
                        <td style="text-align: center;">${imgTag}</td>
                        <td><strong>${item.nama_bayi}</strong><br><small style="color:#666;">Anak ke-${item.anak_ke || '?'}</small></td>
                        <td>${getKelaminText(item.jenis_kelamin)}</td>
                        <td>${hitungUsia(item.tanggal_lahir)}</td>
                        <td>${strBtlk}</td>
                        <td><span style="color:red; font-weight:bold;">${item.golongan_darah || '-'}</span></td>
                        <td><small style="color:orange;">${item.alergi || '-'}</small></td>
                        <td>
                            <button onclick="editData(${index})">Edit / Detail</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="9" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="9" style="text-align: center;">Terjadi gangguan koneksi ke sistem API.</td></tr>';
    }
}

// Munculkan Form Kosong
function showForm() {
    document.getElementById('formBayi').style.display = 'block';
    document.getElementById('bayiForm').reset();
    document.getElementById('id_bayi').value = '';
    // pastikan id_member tetap terkunci ke parent
    document.getElementById('id_member').value = ID_MEMBER;
    document.getElementById('formTitle').innerText = 'Daftarkan Rekam Medis Anak Baru';
}

function hideForm() {
    document.getElementById('formBayi').style.display = 'none';
}

// Munculkan Form Edit (Pre-fill dari object di memori js)
function editData(index) {
    showForm();
    document.getElementById('formTitle').innerText = 'Edit Rekam Medis Anak';
    const item = currentList[index];
    
    document.getElementById('id_bayi').value = item.id_bayi;
    document.getElementById('id_member').value = item.id_member; // tetap
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

// Eksekusi POST Data
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
            fetchList(); // Tarik ulang baris tabel
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan pengiriman Form/Foto ke server!');
    }
}
</script>

</body>
</html>
