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
    <title>Histori Harga Layanan</title>
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
        .form-group label { display: inline-block; width: 150px; vertical-align: top; }
        .form-group input, .form-group select { padding: 5px; width: 250px; }
        .back-link { text-decoration: none; color: #0056b3; font-weight: bold; margin-right: 20px; }
        .note { font-size: 0.85em; color: #666; margin-left: 155px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <h2>Riwayat & Pengaturan Harga Layanan</h2>
    <a href="../users/users.php" class="back-link">&larr; Kembali ke Users</a>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showFormAdd()">+ Catat Harga Baru</button>

    <!-- FORM TAMBAH -->
    <div class="form-container" id="formContainer">
        <h3 id="formTitle">Catat / Perbarui Harga</h3>
        <form id="hargaForm" onsubmit="saveData(event)">
            <div class="form-group">
                <label>Layanan (Opsional)</label>
                <input type="text" id="input_kode_layanan" placeholder="Contoh: LYN-01" oninput="checkKodeLayanan()">
                <input type="hidden" name="id_layanan" id="id_layanan">
                <div id="layanan_label" style="margin-left: 155px; font-weight: bold; margin-top: 5px; font-size: 0.9em;"></div>
                <span class="note">Jika diisi (valid) dan Tgl Efektif = Hari ini, harga langsung aktif.</span>
            </div>
            <div class="form-group">
                <label>Tanggal Efektif</label>
                <input type="date" name="tanggal_efektif" id="tanggal_efektif" required>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="harga" required>
            </div>
            <div class="form-group">
                <label>Komisi Persentase (%)</label>
                <input type="number" step="0.01" name="komisi_persentase" id="komisi_persentase" required>
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
                <th>Tgl Efektif</th>
                <th>Kode Layanan</th>
                <th>Nama Layanan</th>
                <th>Kategori</th>
                <th style="text-align: right;">Harga (Rp)</th>
                <th style="text-align: center;">Komisi (%)</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="7" style="text-align: center;">Loading data...</td></tr>
        </tbody>
    </table>
</div>

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
    
    label.innerHTML = '<span style="color:blue;">Mencari...</span>';
    
    typingTimer = setTimeout(async () => {
        try {
            const response = await fetch('../../api/layanan/list.php?kode_layanan=' + encodeURIComponent(kode));
            const result = await response.json();
            
            if (result.status === 'success' && result.data.length > 0) {
                const item = result.data[0];
                label.innerHTML = `Terdeteksi: <span style="color:green;">${item.nama_layanan}</span>`;
                idInput.value = item.id_layanan;
            } else {
                label.innerHTML = '<span style="color:red;">Kode tidak ditemukan!</span>';
                idInput.value = '';
            }
        } catch (error) {
            label.innerHTML = '<span style="color:red;">Gagal mengecek layanan</span>';
            idInput.value = '';
        }
    }, 500); // 500ms debounce
}

async function fetchHargaList() {
    try {
        const response = await fetch('../../api/harga-layanan/list-harga.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Belum ada riwayat harga tercatat.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                // Memformat angka harga agar menjadi 150.000 bukan 150000
                const hargaFormatted = parseFloat(item.harga).toLocaleString('id-ID');
                
                tbody.innerHTML += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td><strong>${item.tanggal_efektif}</strong></td>
                        <td>${item.kode_layanan || '-'}</td>
                        <td>${item.nama_layanan || '-'}</td>
                        <td>${item.nama_kategori || '-'}</td>
                        <td style="text-align: right;">${hargaFormatted}</td>
                        <td style="text-align: center;">${item.komisi_persentase}</td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="color:red; text-align: center;">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Gagal mengambil data harga:', error);
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="7" style="text-align: center;">Terjadi gangguan jaringan atau API tidak merespons.</td></tr>';
    }
}

function showFormAdd() {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('hargaForm').reset();
    document.getElementById('layanan_label').innerHTML = '';
    document.getElementById('id_layanan').value = '';
    
    // Secara otomatis mengisi input date dengan tanggal hari ini saat form dibuka
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_efektif').value = today;
}

function hideForm() {
    document.getElementById('formContainer').style.display = 'none';
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
            alert(result.message); // Notifikasi detail apakah di-sync atau tidak
            hideForm();
            fetchHargaList(); // Render ulang tabel agar data baru muncul
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
