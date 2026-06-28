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
            <h4 class="mb-0 font-size-18">Laporan Omset Layanan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Laporan</a></li>
                    <li class="breadcrumb-item active">Omset</li>
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
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="mdi mdi-filter"></i> Filter Data</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4" id="summary_section" style="display:none;">
                    <div class="col-sm-6">
                        <div class="p-3 bg-white border border-primary rounded shadow-sm text-center">
                            <h5 class="font-size-15 mb-2 text-muted text-uppercase">Total Transaksi</h5>
                            <h2 class="text-primary mb-0 font-weight-bold" id="summ_transaksi">0</h2>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white border border-success rounded shadow-sm text-center">
                            <h5 class="font-size-15 mb-2 text-muted text-uppercase">Total Omset Layanan</h5>
                            <h2 class="text-success mb-0 font-weight-bold" id="summ_omset">Rp 0</h2>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Bayar</th>
                                <th>Kode Booking</th>
                                <th>Pasien</th>
                                <th>Terapis</th>
                                <th>Metode</th>
                                <th>Nominal Omset (Rp)</th>
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
        const res = await fetch(`../../api/laporan/omset.php?start_date=${sd}&end_date=${ed}&id_terapis=${terapis}`);
        const json = await res.json();
        
        if (json.status === 'success') {
            $('#summ_transaksi').text(json.summary.total_transaksi);
            $('#summ_omset').text('Rp ' + formatRp(json.summary.total_omset));
            $('#summary_section').show();
            
            json.data.forEach((d, i) => {
                const dateObj = new Date(d.tanggal_bayar.replace(' ', 'T'));
                const tglRapi = dateObj.toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':').replace(',', '');
                
                dtTable.row.add([
                    i + 1,
                    tglRapi,
                    `<b class="text-dark">${d.kode_booking}</b>` + (d.kode_pembayaran ? `<br><small class="text-muted">${d.kode_pembayaran}</small>` : ''),
                    d.nama_pasien,
                    `<span class="badge badge-soft-info">${d.nama_terapis || '-'}</span>`,
                    d.metode_pembayaran,
                    `<b class="text-success">${formatRp(d.jumlah_bayar)}</b>`
                ]);
            });
            dtTable.draw();
        } else {
            Swal.fire('Error', json.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Gagal memuat data laporan.', 'error');
    }
}
</script>
