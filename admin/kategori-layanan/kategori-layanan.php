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
    <title>Manajemen Kategori Layanan</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; }
        .form-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #fafafa; display: none; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 130px; vertical-align: top; }
        .form-group input, .form-group select, .form-group textarea { padding: 5px; width: 250px; }
        .form-group textarea { height: 60px; font-family: inherit; }
        .back-link { text-decoration: none; color: #0056b3; font-weight: bold; margin-right: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Kategori Layanan</h2>
    <a href="../users/users.php" class="back-link">&larr; Kembali ke Users</a>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    <button onclick="showFormAdd()">+ Tambah Kategori</button>

    <!-- FORM TAMBAH / EDIT -->
    <div class="form-container" id="formContainer">
        <h3 id="formTitle">Tambah Kategori</h3>
        <form id="kategoriForm" onsubmit="saveKategori(event)">
            <input type="hidden" name="id_kategori_layanan" id="id_kategori_layanan">
            
            <div class="form-group">
                <label>Kode Kategori</label>
                <input type="text" name="kode_kategori" id="kode_kategori" required>
            </div>
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" id="nama_kategori" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi"></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active" id="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            
            <button type="submit">Simpan</button>
            <button type="button" onclick="hideForm()">Batal</button>
        </form>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 15%;">Kode</th>
                <th style="width: 25%;">Nama Kategori</th>
                <th style="width: 35%;">Deskripsi</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 10%; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="6" style="text-align: center;">Loading data...</td></tr>
        </tbody>
    </table>
</div>

<script>
let kategoriData = [];

window.onload = () => {
    fetchData();
};

async function fetchData() {
    try {
        const response = await fetch('../../api/kategori-layanan/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            kategoriData = result.data;
            if (kategoriData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Belum ada data kategori layanan.</td></tr>';
                return;
            }
            
            kategoriData.forEach((item, index) => {
                const statusHtml = item.is_active == 1 ? '<span style="color:green; font-weight:bold;">Aktif</span>' : '<span style="color:red; font-weight:bold;">Nonaktif</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td>${item.kode_kategori}</td>
                        <td>${item.nama_kategori}</td>
                        <td>${item.deskripsi || '-'}</td>
                        <td style="text-align: center;">${statusHtml}</td>
                        <td style="text-align: center;">
                            <button onclick="editData(${item.id_kategori_layanan})" style="margin-bottom: 5px;">Edit</button>
                            ${item.is_active == 1 ? `<button onclick="nonactiveData(${item.id_kategori_layanan})">Nonaktif</button>` : ''}
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Gagal mengambil data:', error);
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" style="text-align: center;">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

function showFormAdd() {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Tambah Kategori Baru';
    document.getElementById('kategoriForm').reset();
    document.getElementById('id_kategori_layanan').value = '';
}

function hideForm() {
    document.getElementById('formContainer').style.display = 'none';
    document.getElementById('kategoriForm').reset();
}

function editData(id) {
    const item = kategoriData.find(k => k.id_kategori_layanan == id);
    if (!item) return;
    
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Edit Kategori Layanan';
    
    document.getElementById('id_kategori_layanan').value = item.id_kategori_layanan;
    document.getElementById('kode_kategori').value = item.kode_kategori;
    document.getElementById('nama_kategori').value = item.nama_kategori;
    document.getElementById('deskripsi').value = item.deskripsi || '';
    document.getElementById('is_active').value = item.is_active;
}

async function saveKategori(e) {
    e.preventDefault();
    const form = document.getElementById('kategoriForm');
    const formData = new FormData(form);
    
    const id = formData.get('id_kategori_layanan');
    const url = id ? '../../api/kategori-layanan/update.php' : '../../api/kategori-layanan/save.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            hideForm();
            fetchData();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}

async function nonactiveData(id) {
    if(!confirm('Apakah Anda yakin ingin menonaktifkan kategori layanan ini?')) return;
    
    const formData = new FormData();
    formData.append('id_kategori_layanan', id);
    
    try {
        const response = await fetch('../../api/kategori-layanan/nonactive.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            alert('Kategori berhasil dinonaktifkan.');
            fetchData();
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan sistem: ' + error);
    }
}
</script>

</body>
</html>
