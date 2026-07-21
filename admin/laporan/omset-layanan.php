<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
include '../includes/header.php';
include '../includes/sidebar.php';

require_once '../../config/koneksi.php';
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
                <form id="formFilter" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="font-weight-bold">Tanggal Awal</label>
                            <input type="date" id="start_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Tanggal Akhir</label>
                            <input type="date" id="end_date" class="form-control" value="<?= date('Y-m-t') ?>" required>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="mdi mdi-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" onclick="cetakLaporan()" class="btn btn-danger btn-block font-weight-bold"><i class="mdi mdi-printer"></i> Cetak</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4" id="summary_section" style="display:none;">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded">
                            <h5 class="font-size-15 mb-1">Total Transaksi Lunas</h5>
                            <h3 class="text-primary mb-0" id="summ_transaksi">0</h3>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded">
                            <h5 class="font-size-15 mb-1">Total Omset</h5>
                            <h3 class="text-success mb-0" id="summ_omset">Rp 0</h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Bayar</th>
                                <th>Kode Pembayaran</th>

                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th class="text-right">Nominal (Rp)</th>
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
    
    dtTable.clear().draw();
    $('#table_body').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
    
    try {
        const res = await fetch(`../../api/laporan/omset.php?start_date=${sd}&end_date=${ed}`);
        const json = await res.json();
        
        if (json.status === 'success') {
            $('#summ_transaksi').text(json.summary.total_transaksi);
            $('#summ_omset').text('Rp ' + formatRp(json.summary.total_omset));
            $('#summary_section').show();
            
            json.data.forEach((d, i) => {
                const dateObj = new Date(d.tanggal_bayar.replace(' ', 'T'));
                const tglRapi = dateObj.toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':').replace(',', '');
                
                let badgeStatus = d.status_pembayaran === 'LUNAS' 
                    ? `<span class="badge badge-success">LUNAS</span>` 
                    : `<span class="badge badge-warning">${d.status_pembayaran}</span>`;

                dtTable.row.add([
                    i + 1,
                    tglRapi,
                    d.kode_pembayaran || '-',
                    d.metode_pembayaran || '-',
                    badgeStatus,
                    `<div class="text-right font-weight-bold text-success">` + formatRp(d.jumlah_omset) + `</div>`
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

function cetakLaporan() {
    const sd = $('#start_date').val();
    const ed = $('#end_date').val();
    const terapis = $('#id_terapis').val();
    
    // Buka tab baru untuk print
    window.open(`cetak-omset.php?start_date=${sd}&end_date=${ed}&id_terapis=${terapis}`, '_blank');
}
</script>
