<?php
session_start();
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
            <h4 class="mb-0 font-size-18">Pengaturan Profil Usaha (Klinik)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pengaturan</a></li>
                    <li class="breadcrumb-item active">Profil Usaha</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Informasi Utama Klinik</h4>

                <form id="profileForm" onsubmit="saveData(event)" enctype="multipart/form-data">
                    <input type="hidden" id="id_usaha" name="id_usaha">
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Status Operasional</label>
                        <div class="col-md-9">
                            <select class="form-control font-weight-bold" id="sedang_buka" name="sedang_buka" style="background-color: #fff3cd;">
                                <option value="1">Buka / Beroperasi</option>
                                <option value="0" class="text-danger">Tutup Sementara</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Nama Usaha</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="nama_usaha" name="nama_usaha" required placeholder="Cth: Klinik Pijat Sehat">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Nama Pemilik</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="nama_pemilik" name="nama_pemilik" placeholder="Nama Pemilik / Direktur">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Jam Operasional</label>
                        <div class="col-md-9 d-flex align-items-center">
                            <input type="time" class="form-control" id="jam_buka" name="jam_buka" style="width: auto;"> 
                            <span class="mx-3">s/d</span> 
                            <input type="time" class="form-control" id="jam_tutup" name="jam_tutup" style="width: auto;">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Alamat Lengkap</label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Jalan..."></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Link Google Maps (GPS)</label>
                        <div class="col-md-9">
                            <input type="url" class="form-control" id="alamat_gps" name="alamat_gps" placeholder="https://maps.app.goo.gl/...">
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3 mt-4 text-primary">Kontak & Media Sosial</h5>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">No. WhatsApp 1</label>
                        <div class="col-md-9">
                            <input type="tel" class="form-control" id="whatsapp1" name="whatsapp1" placeholder="08123456789">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">No. WhatsApp 2</label>
                        <div class="col-md-9">
                            <input type="tel" class="form-control" id="whatsapp2" name="whatsapp2" placeholder="Opsional (0898...)">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Instagram</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="ig" name="ig" placeholder="@username">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">Website</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="website" name="website" placeholder="www.domain.com">
                        </div>
                    </div>

                    <div class="mt-4 text-right">
                        <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">
                            <i class="mdi mdi-content-save mr-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title mb-4 text-left">Logo / Foto Usaha</h4>
                <div class="mb-3">
                    <img id="preview" src="../../images/placeholder.jpg" alt="Logo Usaha" class="img-thumbnail rounded-circle avatar-xl object-cover" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                
                <div class="custom-file mb-3 text-left">
                    <input type="file" class="custom-file-input" id="foto_usaha" name="foto_usaha" accept="image/*" form="profileForm" onchange="previewImage(event)">
                    <label class="custom-file-label" for="foto_usaha">Pilih File Baru</label>
                </div>
                <small class="text-muted text-left d-block">Format didukung: JPG, PNG, WEBP. Maks 2MB. Resolusi disarankan: 500x500px.</small>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
window.onload = fetchProfile;

function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('preview');
        output.src = reader.result;
    }
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

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
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading mr-1"></i> Menyimpan...';

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
    btn.innerHTML = originalText;
}
</script>
