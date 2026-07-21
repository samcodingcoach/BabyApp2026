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
                            <select id="id_terapis" class="form-control select2" style="width: 100%;">
                                <option value="">- Semua Terapis -</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>

                <div class="row mb-4" id="summary_section" style="display:none;">
                    <div class="col-12">
                        <div class="p-3 bg-light border rounded text-center">
                            <h5 class="font-size-15 mb-2" id="title_summ_komisi">Total Pencairan Komisi</h5>
                            <h3 class="text-success mb-0" id="summ_komisi">Rp 0</h3>
                            <small class="text-muted mt-2 d-block">* Nilai Total Pencairan belum termasuk biaya admin</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Kode Pembayaran</th>
                                <th>Nama Terapis</th>
                                <th>Status Pencairan</th>
                                <th class="text-right">Komisi (Rp)</th>
                                <th class="text-center">Detail</th>
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

<!-- Modal Detail Pencairan -->
<div class="modal fade" id="modalDetailPencairan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pencairan Komisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeDetailPencairan" src="" style="width:100%; height:70vh; border:none;"></iframe>
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

$(document).ready(async function() {
    dtTable = $('#datatable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" }
    });
    
    $('.select2').select2();
    
    await loadTerapisOptions();
    loadData();

    $('#formFilter').submit(function(e) {
        e.preventDefault();
        loadData();
    });
});

async function loadTerapisOptions() {
    try {
        const res = await fetch('../../api/terapis/list.php');
        const json = await res.json();
        if (json.status === 'success') {
            let html = '<option value="">- Semua Terapis -</option>';
            json.data.forEach(t => {
                if (parseInt(t.is_active) === 1) {
                    html += `<option value="${t.id_terapis}">${t.nama_terapis} (${t.kode_terapis})</option>`;
                }
            });
            $('#id_terapis').html(html).trigger('change');
        }
    } catch(e) {
        console.error('Gagal memuat opsi terapis:', e);
    }
}

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
            const txCount = json.summary.total_transaksi_komisi || 0;
            $('#title_summ_komisi').text(`Total Pencairan Komisi (${txCount} Transaksi)`);
            $('#summ_komisi').text('Rp ' + formatRp(json.summary.total_komisi));
            $('#summary_section').show();
            
            json.data.forEach((d, i) => {
                let cairRapi = '';
                if (d.tanggal_pencairan) {
                    let cairObj = new Date(d.tanggal_pencairan.replace(' ', 'T'));
                    cairRapi = '<br><small class="text-muted"><i class="mdi mdi-clock-outline"></i> ' + cairObj.toLocaleString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':').replace(',', '') + '</small>';
                }

                let badgeStatus = d.status_pencairan === 'SUDAH_CAIR'
                    ? `<span class="badge badge-success">SUDAH CAIR</span>${cairRapi}`
                    : `<span class="badge badge-secondary">${d.status_pencairan}</span>`;
                
                let komisi = parseFloat(d.nominal_komisi) || 0;

                let btnDetail = '-';
                if (d.kode_pencairan) {
                    btnDetail = `<button class="btn btn-sm btn-info" onclick="showDetailPencairan('${d.kode_pencairan}')"><i class="mdi mdi-printer"></i> Struk</button>`;
                }

                dtTable.row.add([
                    i + 1,
                    d.kode_pembayaran || '-',
                    d.nama_terapis || '-',
                    badgeStatus,
                    `<div class="text-right font-weight-bold text-success">` + formatRp(komisi) + `</div>`,
                    `<div class="text-center">` + btnDetail + `</div>`
                ]);
            });
            dtTable.draw();
        } else {
            console.error('API Error:', json.message);
        }
    } catch(e) {
        console.error('Fetch Error:', e);
    }
}

function showDetailPencairan(kode) {
    $('#iframeDetailPencairan').attr('src', `../pencairan-komisi/print.php?kode=${kode}`);
    $('#modalDetailPencairan').modal('show');
}
</script>
