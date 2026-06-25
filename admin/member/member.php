<?php
session_start();
// Proteksi halaman admin
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
    <title>Manajemen Member</title>
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
        img.thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #ccc; }
        .badge-active { color: green; font-weight: bold; }
        .badge-inactive { color: red; font-weight: bold; }
        .pagination { margin-top: 15px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; }
        .pagination button { padding: 5px 15px; font-weight: bold; background: #0056b3; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .pagination button:disabled { background: #ccc; cursor: not-allowed; }
        .pagination-info { font-size: 0.9em; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Pendaftaran Member</h2>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showForm()">+ Daftarkan Member Baru</button>

    <!-- FORM (SAVE & UPDATE) -->
    <div class="form-container" id="formMember">
        <h3 id="formTitle">Form Member</h3>
        <form id="memberForm" onsubmit="saveData(event)">
            <input type="hidden" name="id_member" id="id_member">
            
            <div class="form-group">
                <label>Nomor Induk (NIK) *</label>
                <input type="text" name="nik" id="nik" placeholder="No KTP / ID" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="nama" id="nama" required>
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
                <label>Alamat Domisili</label>
                <textarea name="alamat" id="alamat" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Kecamatan</label>
                <input type="text" name="kecamatan" id="kecamatan">
            </div>
            <div class="form-group">
                <label>Alamat (Titik GPS)</label>
                <input type="text" name="alamat_gps" id="alamat_gps" placeholder="Cth: Paste URL Maps">
            </div>
            <div class="form-group">
                <label>No. Whatsapp</label>
                <input type="text" name="whatsapp" id="whatsapp" placeholder="08123...">
            </div>
            <div class="form-group">
                <label>Password Akun</label>
                <input type="password" name="password" id="password" placeholder="(Kosongi jika tidak ingin diubah)">
            </div>
            <div class="form-group">
                <label>Status Member</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif (Approved)</option>
                    <option value="0">Belum Aktif / Blokir</option>
                </select>
            </div>
            <div class="form-group">
                <label>Foto Profile (JPG/PNG)</label>
                <input type="file" name="photo" accept="image/jpeg, image/png, image/webp">
                <br><small style="margin-left: 185px; color:#666;">(Otomatis ter-rename sesuai NIK pendaftar)</small>
            </div>
            
            <button type="submit" style="background: blue; color: white; border-radius:3px; padding:8px 15px; border:none; font-weight:bold;">Simpan Profil Member</button>
            <button type="button" onclick="hideForm()" style="border-radius:3px; padding:8px 15px; border:1px solid #ccc;">Batal</button>
        </form>
    </div>

    <!-- PENCARIAN & FILTER -->
    <div style="margin-bottom: 15px; background: #eef5ff; padding: 15px; border-radius: 5px; border: 1px solid #cce0ff;">
        <strong style="margin-right: 10px; color: #0056b3;">Pencarian Pintar:</strong>
        <input type="text" id="filter_nik" placeholder="Ketik NIK eksak..." oninput="applyFilter()" style="padding: 7px; width: 180px; border-radius: 3px; border: 1px solid #ccc;">
        <input type="text" id="filter_nama" placeholder="Ketik bagian Nama Member..." oninput="applyFilter()" style="padding: 7px; margin-left: 10px; width: 250px; border-radius: 3px; border: 1px solid #ccc;">
    </div>

    <!-- TABEL DATA -->
    <table>
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
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="9" style="text-align: center;">Memuat database member...</td></tr>
        </tbody>
    </table>

    <!-- PAGINATION -->
    <div class="pagination">
        <span class="pagination-info" id="pageInfo">Halaman 1 dari 1 (Total: 0 Data)</span>
        <button id="btnPrev" onclick="changePage(-1)" disabled>&laquo; Prev</button>
        <button id="btnNext" onclick="changePage(1)" disabled>Next &raquo;</button>
    </div>
</div>

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
        
        // Membangun Query Parameter
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
            
            // Render Pagination Info & Tombol
            document.getElementById('pageInfo').innerText = `Halaman ${currentPage} dari ${totalPages} (Total: ${result.total_rows} Member)`;
            document.getElementById('btnPrev').disabled = (currentPage <= 1);
            document.getElementById('btnNext').disabled = (currentPage >= totalPages);
            
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">Data Member tidak ditemukan atau database kosong.</td></tr>';
                return;
            }
            
            // Logika Penomoran Tabel berkelanjutan antar halaman
            let startIndex = (currentPage - 1) * result.per_page;
            
            result.data.forEach((item, index) => {
                const statusHtml = parseInt(item.is_active) === 1 ? '<span class="badge-active">Aktif</span>' : '<span class="badge-inactive">Non-Aktif</span>';
                const imgTag = item.photo ? `<img src="../../images/${item.photo}" class="thumb">` : '<div class="thumb" style="background:#eee;text-align:center;font-size:10px;line-height:50px;">No Img</div>';

                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${startIndex + index + 1}</td>
                        <td style="text-align: center;">${imgTag}</td>
                        <td><strong>${item.nik}</strong></td>
                        <td>${item.nama}</td>
                        <td>${getKelaminText(item.jenis_kelamin)}</td>
                        <td>${item.kecamatan || '-'}</td>
                        <td>${item.whatsapp || '-'}</td>
                        <td>${statusHtml}</td>
                        <td>
                            <button onclick="editData(${index})">Edit Data</button>
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

// Munculkan Form Kosong (Mode Tambah)
function showForm() {
    document.getElementById('formMember').style.display = 'block';
    document.getElementById('memberForm').reset();
    document.getElementById('id_member').value = '';
    document.getElementById('formTitle').innerText = 'Daftarkan Member Baru';
}

function hideForm() {
    document.getElementById('formMember').style.display = 'none';
}

// Munculkan Form Terisi (Mode Edit)
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

// Eksekusi API Save / Update
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
            // Refresh tanpa pindah halaman (tetap berada di halaman paging saat ini)
            fetchList(currentPage); 
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan pengiriman data gambar/form ke server!');
    }
}
</script>

</body>
</html>
