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
            <h4 class="mb-0 font-size-18">Laporan Komisi Terapis</h4>
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
                <form id="formFilter" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Tanggal Awal</label>
                            <input type="date" id="start_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" id="end_date" class="form-control" value="<?= date('Y-m-t') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Terapis (Opsional)</label>
                            <select id="id_terapis" class="form-control">
                                <option value="">- Semua Terapis -</option>
                                <?= $terapis_options ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4" id="summary_section" style="display:none;">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded">
                            <h5 class="font-size-15 mb-1">Total Transaksi Komisi</h5>
                            <h3 class="text-primary mb-0" id="summ_transaksi">0</h3>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light border rounded">
                            <h5 class="font-size-15 mb-1">Total Pencairan Komisi</h5>
                            <h3 class="text-success mb-0" id="summ_komisi">Rp 0</h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal Dibuat</th>
                                <th>Kode Pembayaran</th>
                                <th>Nama Terapis</th>
                                <th>Status Pencairan</th>
                                <th>Tgl Pencairan</th>
                                <th class="text-right">Nominal Komisi (Rp)</th>
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
    $('#table_body').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
    
    try {
        const res = await fetch(`../../api/laporan/komisi.php?start_date=${sd}&end_date=${ed}&id_terapis=${terapis}`);
        const json = await res.json();
        
        if (json.status === 'success') {
            $('#summ_transaksi').text(json.summary.total_transaksi_komisi);
            $('#summ_komisi').text('Rp ' + formatRp(json.summary.total_komisi));
            $('#summary_section').show();
            
            json.data.forEach((d, i) => {
                let createdObj = new Date(d.created_at.replace(' ', 'T'));
                let createdRapi = createdObj.toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':').replace(',', '');
                
                let cairRapi = '-';
                if (d.tanggal_pencairan) {
                    let cairObj = new Date(d.tanggal_pencairan.replace(' ', 'T'));
                    cairRapi = cairObj.toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':').replace(',', '');
                }

                let badgeStatus = d.status_pencairan === 'SUDAH_CAIR'
                    ? `<span class="badge badge-success">SUDAH CAIR</span>`
                    : `<span class="badge badge-secondary">${d.status_pencairan}</span>`;

                dtTable.row.add([
                    i + 1,
                    createdRapi,
                    d.kode_pembayaran || '-',
                    d.nama_terapis || '-',
                    badgeStatus,
                    cairRapi,
                    `<div class="text-right font-weight-bold">` + formatRp(d.nominal_komisi) + `</div>`
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
