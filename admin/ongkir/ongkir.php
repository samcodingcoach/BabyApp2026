<?php
session_start();
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
    <title>Manajemen Ongkos Kirim (Ongkir)</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;}
        .form-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #fffcf0; display: none; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 150px; font-weight: bold;}
        .form-group input, .form-group select { padding: 6px; width: 300px; border: 1px solid #ccc; border-radius:3px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Ongkir (Jarak Tempuh Layanan)</h2>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showAddForm()" style="background:#28a745; color:white; border:none; border-radius:3px;">+ Tambah Data Ongkir</button>

    <div class="form-container" id="formContainer">
        <h3 id="formTitle">Tambah Ongkir Baru</h3>
        <form id="ongkirForm" onsubmit="saveData(event)">
            <input type="hidden" id="id_ongkir">
            <div class="form-group">
                <label>Dari Kecamatan *</label>
                <input type="text" id="dari_kecamatan" required placeholder="Contoh: Lowokwaru">
            </div>
            <div class="form-group">
                <label>Ke Kecamatan *</label>
                <input type="text" id="ke_kecamatan" required placeholder="Contoh: Klojen">
            </div>
            <div class="form-group">
                <label>Harga (Tarif) *</label>
                <input type="number" id="harga" required placeholder="Contoh: 15000">
            </div>
            <div class="form-group">
                <label>Status Aktif</label>
                <select id="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label></label>
                <button type="submit" id="btnSubmit" style="background: #007bff; color: white; border: none; border-radius:3px;">Simpan Data</button>
                <button type="button" onclick="hideForm()" style="background: #ccc; border: none; border-radius:3px;">Batal</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Rute Pengiriman (Dari -> Ke)</th>
                <th>Tarif Dasar (Rp)</th>
                <th>Status</th>
                <th>Terakhir Diubah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="6" style="text-align: center;">Memuat data...</td></tr>
        </tbody>
    </table>
</div>

<script>
window.onload = fetchList;

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
}

let onEditData = {};

async function fetchList() {
    try {
        const response = await fetch('../../api/ongkir/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Belum ada data ongkir.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const tr = document.createElement('tr');
                
                const rute = `<strong>${item.dari_kecamatan}</strong> &#10142; <strong>${item.ke_kecamatan}</strong>`;
                const status = item.is_active == 1 ? '<span style="color:green; font-weight:bold;">Aktif</span>' : '<span style="color:red;">Nonaktif</span>';
                const d = new Date(item.update_at || item.created_at);
                const tgl = d.toLocaleString('id-ID');
                
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${rute}</td>
                    <td>Rp ${formatRp(item.harga)}</td>
                    <td>${status}</td>
                    <td>${tgl}</td>
                    <td>
                        <button onclick='editData(${JSON.stringify(item)})' style="background:#ffc107; border:none; border-radius:3px;">Edit</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: red;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Gagal memuat data dari server.</td></tr>';
    }
}

function showAddForm() {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Tambah Ongkir Baru';
    document.getElementById('ongkirForm').reset();
    document.getElementById('id_ongkir').value = '';
    onEditData = {};
}

function editData(item) {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('formTitle').innerText = 'Edit Data Ongkir';
    
    document.getElementById('id_ongkir').value = item.id_ongkir;
    document.getElementById('dari_kecamatan').value = item.dari_kecamatan;
    document.getElementById('ke_kecamatan').value = item.ke_kecamatan;
    document.getElementById('harga').value = item.harga;
    document.getElementById('is_active').value = item.is_active;
    
    onEditData = item;
}

function hideForm() {
    document.getElementById('formContainer').style.display = 'none';
}

async function saveData(e) {
    e.preventDefault();
    
    const id_ongkir = document.getElementById('id_ongkir').value;
    const isEdit = id_ongkir !== '';
    
    const params = new URLSearchParams();
    if (isEdit) params.append('id_ongkir', id_ongkir);
    params.append('dari_kecamatan', document.getElementById('dari_kecamatan').value);
    params.append('ke_kecamatan', document.getElementById('ke_kecamatan').value);
    params.append('harga', document.getElementById('harga').value);
    params.append('is_active', document.getElementById('is_active').value);
    
    const endpoint = isEdit ? '../../api/ongkir/update.php' : '../../api/ongkir/save.php';
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
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
        alert('Terjadi kesalahan jaringan.');
    }
    
    btn.disabled = false;
    btn.innerText = 'Simpan Data';
}
</script>

</body>
</html>
