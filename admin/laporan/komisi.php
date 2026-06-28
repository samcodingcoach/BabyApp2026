<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
include '../includes/header.php';
include '../includes/sidebar.php';

require_once '../../config/koneksi.php';
$q = $koneksi->query("SELECT id_terapis, nama_terapis FROM terapis WHERE is_active=1");
$terapis_options = '';
while($t = $q->fetch_assoc()){
    $terapis_options .= '<option value="'.$t['id_terapis'].'">'.$t['nama_terapis'].'</option>';
}
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">Laporan Komisi & Kinerja Terapis</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Laporan</a></li>
                    <li class="breadcrumb-item active">Komisi</li>
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
                <form id="formFilter" class="mb-4 bg-light p-3 border rounded">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="font-weight-bold">Tanggal Awal</label>
                            <input type="date" id="start_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">Tanggal Akhir</label>
                            <input type="date" id="end_date" class="form-control" value="<?= date('Y-m-t') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold">Terapis (Opsional)</label>
                            <select id="id_terapis" class="form-control custom-select">
                                <option value="">- Semua Terapis -</option>
                                <?= $terapis_options ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info btn-block font-weight-bold"><i class="mdi mdi-filter"></i> Analisis</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4" id="summary_section" style="display:none;">
                    <div class="col-sm-6">
                        <div class="p-3 bg-white border border-info rounded shadow-sm text-center">
                            <h5 class="font-size-15 mb-2 text-muted text-uppercase">Terapis Terlibat</h5>
                            <h2 class="text-info mb-0 font-weight-bold" id="summ_terapis">0</h2>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white border border-warning rounded shadow-sm text-center">
                            <h5 class="font-size-15 mb-2 text-muted text-uppercase">Total Pendapatan (Omset)</h5>
                            <h2 class="text-warning mb-0 font-weight-bold" id="summ_omset">Rp 0</h2>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="bg-info text-white">
                            <tr>
                                <th>No.</th>
                                <th>Nama Terapis</th>
                                <th>Jumlah Layanan Selesai</th>
                                <th>Total Omset (Rp)</th>
                                <th>Estimasi Komisi (Misal: 30%)</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script>
function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

let dtTable;

$(document).ready(function() {
    dtTable = $('#datatable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" }
    });
    
    loadData();

    $('#formFilter').submit(function(e) {
        e.preventDefault();
        loadData();
    });
});

async function loadData() {
    const sd = $('#start_date').val();
    const ed = $('#end_date').val();
    const terapis = $('#id_terapis').val();
    
    dtTable.clear().draw();
    
    try {
        const res = await fetch(`../../api/laporan/komisi.php?start_date=${sd}&end_date=${ed}&id_terapis=${terapis}`);
        const json = await res.json();
        
        if (json.status === 'success') {
            $('#summ_terapis').text(json.summary.jumlah_terapis_aktif + ' Orang');
            $('#summ_omset').text('Rp ' + formatRp(json.summary.grand_total_omset));
            $('#summary_section').show();
            
            json.data.forEach((d, i) => {
                // Menghitung persentase contoh 30% dari total omset
                const komisi30 = d.total_omset * 0.3;
                
                dtTable.row.add([
                    i + 1,
                    `<b class="text-dark font-size-16">${d.nama_terapis || 'Terapis Tidak Ditemukan'}</b>`,
                    `<span class="badge badge-soft-info font-size-14">${d.total_transaksi} Transaksi Lunas</span>`,
                    `<b class="text-primary font-size-15">${formatRp(d.total_omset)}</b>`,
                    `<b class="text-warning font-size-15">${formatRp(komisi30)}</b> <br><small class="text-muted">(Dapat disesuaikan)</small>`
                ]);
            });
            dtTable.draw();
        } else {
            Swal.fire('Error', json.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Gagal memuat data komisi.', 'error');
    }
}
</script>
