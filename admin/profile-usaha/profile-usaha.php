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
    <title>Manajemen Profil Usaha</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; max-width: 800px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: inline-block; width: 180px; font-weight: bold; vertical-align: top;}
        .form-group input[type="text"], .form-group input[type="time"], .form-group select { padding: 8px; width: calc(100% - 200px); border: 1px solid #ccc; border-radius:3px; }
        .form-group textarea { padding: 8px; width: calc(100% - 200px); border: 1px solid #ccc; border-radius:3px; height: 80px; }
        button { padding: 10px 20px; cursor: pointer; border-radius:3px; font-weight: bold; border:none; }
        .btn-save { background: #28a745; color: white; }
        .preview-img { max-width: 200px; max-height: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; background: #fff; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="border-bottom: 2px solid #007bff; padding-bottom: 10px;">Pengaturan Profil Usaha (Klinik)</h2>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none; margin-top:-45px;">Logout</a>
    
    <form id="profileForm" onsubmit="saveData(event)" enctype="multipart/form-data">
        <input type="hidden" id="id_usaha" name="id_usaha">
        
        <div class="form-group">
            <label>Status Operasional</label>
            <select id="sedang_buka" name="sedang_buka" style="background:#fff3cd; font-weight:bold;">
                <option value="1">Buka / Beroperasi</option>
                <option value="0">Tutup Sementara</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Nama Usaha</label>
            <input type="text" id="nama_usaha" name="nama_usaha" required placeholder="Cth: Klinik Pijat Sehat">
        </div>

        <div class="form-group">
            <label>Nama Pemilik</label>
            <input type="text" id="nama_pemilik" name="nama_pemilik" placeholder="Nama Pemilik / Direktur">
        </div>

        <div class="form-group">
            <label>Jam Buka / Tutup</label>
            <input type="time" id="jam_buka" name="jam_buka" style="width: 100px;"> 
            s/d 
            <input type="time" id="jam_tutup" name="jam_tutup" style="width: 100px;">
        </div>

        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea id="alamat" name="alamat" placeholder="Jalan..."></textarea>
        </div>

        <div class="form-group">
            <label>Link Google Maps (GPS)</label>
            <input type="text" id="alamat_gps" name="alamat_gps" placeholder="https://maps.app.goo.gl/...">
        </div>

        <div class="form-group">
            <label>No. WhatsApp 1</label>
            <input type="text" id="whatsapp1" name="whatsapp1" placeholder="08123456789">
        </div>

        <div class="form-group">
            <label>No. WhatsApp 2 (Opsional)</label>
            <input type="text" id="whatsapp2" name="whatsapp2" placeholder="08987654321">
        </div>

        <div class="form-group">
            <label>Instagram</label>
            <input type="text" id="ig" name="ig" placeholder="@username">
        </div>

        <div class="form-group">
            <label>Website</label>
            <input type="text" id="website" name="website" placeholder="www.domain.com">
        </div>

        <div class="form-group">
            <label>Logo / Foto Usaha</label>
            <input type="file" id="foto_usaha" name="foto_usaha" accept="image/*">
            <br>
            <img id="preview" class="preview-img" src="" alt="Belum ada logo" style="display:none; margin-left:185px;">
        </div>

        <div class="form-group" style="text-align: right; margin-top: 30px;">
            <button type="submit" id="btnSubmit" class="btn-save">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
window.onload = fetchProfile;

async function fetchProfile() {
    try {
        const response = await fetch('../../api/profile-usaha/list.php');
        const result = await response.json();
        
        if (result.status === 'success' && result.data) {
            const data = result.data;
            document.getElementById('id_usaha').value = data.id_usaha || '';
            document.getElementById('nama_usaha').value = data.nama_usaha || '';
            document.getElementById('nama_pemilik').value = data.nama_pemilik || '';
            document.getElementById('jam_buka').value = data.jam_buka || '';
            document.getElementById('jam_tutup').value = data.jam_tutup || '';
            document.getElementById('alamat').value = data.alamat || '';
            document.getElementById('alamat_gps').value = data.alamat_gps || '';
            document.getElementById('whatsapp1').value = data.whatsapp1 || '';
            document.getElementById('whatsapp2').value = data.whatsapp2 || '';
            document.getElementById('ig').value = data.ig || '';
            document.getElementById('website').value = data.website || '';
            document.getElementById('sedang_buka').value = data.sedang_buka !== null ? data.sedang_buka : 1;
            
            if (data.foto_usaha) {
                const img = document.getElementById('preview');
                img.src = '../../images/' + data.foto_usaha;
                img.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Gagal memuat data profile.');
    }
}

async function saveData(e) {
    e.preventDefault();
    
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    try {
        const response = await fetch('../../api/profile-usaha/update.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            alert(result.message);
            fetchProfile(); // Refresh Data
        } else {
            alert('Gagal: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan jaringan.');
    }
    
    btn.disabled = false;
    btn.innerText = 'Simpan Perubahan';
}
</script>

</body>
</html>
