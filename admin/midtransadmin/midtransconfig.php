<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Verifikasi auth
if (!check_auth($koneksi)) {
    header('Location: ../../login-admin.php');
    exit();
}

$page_title = "Konfigurasi Midtrans";
$active_menu = "midtrans";
$base_url = "/terapi/admin"; 
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 font-size-18">Konfigurasi Midtrans</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Pengaturan</a></li>
                                <li class="breadcrumb-item active">Midtrans</li>
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
                            <h4 class="card-title mb-4">Pengaturan API Keys Midtrans</h4>
                            
                            <form id="formMidtrans" onsubmit="saveConfig(event)">
                                <input type="hidden" name="id_midtrans" id="id_midtrans" value="">
                                
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Merchant ID</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="Merchant_ID" id="Merchant_ID" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Client Key</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="ClientKey" id="ClientKey" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Server Key</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="ServerKey" id="ServerKey" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">
                                            Simpan Konfigurasi
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="mt-4">
                                <p class="text-muted mb-0"><i class="mdi mdi-information text-info"></i> Terakhir diupdate: <span id="lblUpdateAt">-</span></p>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

<?php include '../includes/footer.php'; ?>

<script>
window.onload = async () => {
    await fetchConfig();
};

async function fetchConfig() {
    try {
        const response = await fetch('../../api/midtransapi/list.php');
        const result = await response.json();
        
        if (result.status === 'success' && result.data.length > 0) {
            const data = result.data[0]; // Ambil data pertama
            document.getElementById('id_midtrans').value = data.id_midtrans;
            document.getElementById('Merchant_ID').value = data.Merchant_ID;
            document.getElementById('ClientKey').value = data.ClientKey;
            document.getElementById('ServerKey').value = data.ServerKey;
            
            if(data.update_at) {
                document.getElementById('lblUpdateAt').innerText = data.update_at;
            }
        }
    } catch (e) {
        Swal.fire('Error', 'Gagal mengambil data konfigurasi dari server.', 'error');
    }
}

async function saveConfig(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const formData = new FormData(e.target);
    const id = document.getElementById('id_midtrans').value;
    const url = id ? '../../api/midtransapi/update.php' : '../../api/midtransapi/save.php';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            Swal.fire('Berhasil!', result.message, 'success');
            fetchConfig(); // Reload data
        } else {
            Swal.fire('Gagal!', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Simpan Konfigurasi';
    }
}
</script>
