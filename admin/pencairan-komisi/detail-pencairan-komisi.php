<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
include '../includes/header.php';
include '../includes/sidebar.php';

$kode_pencairan = $_GET['kode'] ?? '';
if (empty($kode_pencairan)) {
    echo "<script>alert('Kode pencairan tidak valid'); window.location='pencairan-komisi.php';</script>";
    exit();
}
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">Rincian Komisi - <?= htmlspecialchars($kode_pencairan) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="pencairan-komisi.php">Pencairan Komisi</a></li>
                    <li class="breadcrumb-item active">Rincian</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-info" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-info-circle"></i></span>
                            <span class="d-none d-sm-block"><i class="mdi mdi-information-outline mr-1"></i> Informasi Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-rincian" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-list"></i></span>
                            <span class="d-none d-sm-block"><i class="mdi mdi-format-list-bulleted mr-1"></i> Daftar Rincian Komisi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-bukti" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-file-invoice"></i></span>
                            <span class="d-none d-sm-block"><i class="mdi mdi-receipt mr-1"></i> Bukti Transfer & Closing</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    
                    <!-- TAB 1: Informasi Transaksi -->
                    <div class="tab-pane active p-3" id="tab-info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <th scope="row" style="width: 35%;" class="text-muted">Kode Pencairan</th>
                                            <td>: <strong class="text-primary font-size-15" id="info_kode">...</strong></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted">Status</th>
                                            <td>: <span id="info_status">...</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted">Bank Tujuan</th>
                                            <td>: <span class="font-weight-bold" id="info_bank">...</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <th scope="row" style="width: 35%;" class="text-muted">Tanggal Transfer</th>
                                            <td>: <span class="font-weight-bold" id="info_tanggal">...</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted">Biaya Admin</th>
                                            <td>: <span id="info_admin" class="font-weight-bold text-danger">...</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted">Keterangan</th>
                                            <td>: <span id="info_keterangan" class="font-italic">...</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 2: Daftar Rincian Komisi -->
                    <div class="tab-pane p-3" id="tab-rincian" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">Data Komisi</h4>
                            <button id="btnTambahRincian" onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold d-none">
                                <i class="mdi mdi-plus mr-1"></i> Tambah Rincian
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 5%; text-align: center;">No.</th>
                                        <th>Terapis</th>
                                        <th>Booking</th>
                                        <th>Nominal Komisi</th>
                                        <th style="width: 10%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right font-size-15 text-muted">Subtotal Komisi:</th>
                                        <th colspan="2" class="font-size-15 text-muted" id="subtotal_komisi">Rp 0</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right font-size-15 text-muted">Biaya Admin:</th>
                                        <th colspan="2" class="font-size-15 text-danger" id="row_biaya_admin">- Rp 0</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right font-size-16">Total Komisi Cair (Bersih):</th>
                                        <th colspan="2" class="font-size-16 text-success font-weight-bold" id="total_cair">Rp 0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: Bukti Transfer -->
                    <div class="tab-pane p-3" id="tab-bukti" role="tabpanel">
                        <div id="buktiContainer">
                            <!-- Bukti View (if closed) -->
                            <div id="buktiView" class="d-none text-center">
                                <h5 class="text-success mb-3"><i class="mdi mdi-check-circle"></i> Transaksi Sudah Di-Closing</h5>
                                <div id="buktiImageContainer" class="mb-3"></div>
                                <a id="btnDownloadBukti" href="#" target="_blank" class="btn btn-primary"><i class="mdi mdi-download"></i> Unduh Bukti Transfer</a>
                            </div>

                            <!-- Bukti Form (if not closed) -->
                            <div id="buktiFormContainer" class="d-none">
                                <div class="alert alert-warning font-weight-bold">
                                    <i class="mdi mdi-alert mr-1"></i> Perhatian! Mengunggah bukti transfer akan melakukan CLOSING pada transaksi ini. Data rincian komisi tidak akan bisa diubah lagi.
                                </div>
                                <form id="closingForm" onsubmit="submitClosing(event)">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rekening Tujuan <span class="text-danger">*</span></label>
                                        <div id="rekeningContainer" class="row">
                                            <div class="col-12 text-muted"><em>Menunggu pilihan terapis...</em></div>
                                        </div>
                                        <input type="hidden" name="an_rek" id="an_rekClosing">
                                        <input type="hidden" name="no_rek" id="no_rekClosing">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Upload Bukti Transfer <span class="text-danger">*</span> <small class="text-muted">(JPG, PNG, PDF | Maks 2MB)</small></label>
                                        <input type="file" class="dropify" name="bukti" id="buktiClosing" accept=".jpg,.jpeg,.png,.pdf" data-height="200" required>
                                    </div>
                                    <div class="text-right mt-4">
                                        <button type="submit" id="btnSubmitClosing" class="btn btn-success waves-effect waves-light font-weight-bold"><i class="mdi mdi-check-all mr-1"></i> Simpan & Closing Transaksi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Detail Pencairan -->
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Tambah Rincian Komisi</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="detailForm" onsubmit="saveData(event)">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_detail_pencairan" id="id_detail_pencairan">
                    <input type="hidden" name="id_pencarian" id="form_id_pencarian">
                    
                    <div id="insertGroup">
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold">Terapis *</label>
                                <select class="form-control select2" id="sel_terapis" style="width: 100%;" required>
                                    <option value="">-- Cari Terapis --</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="font-weight-bold">Tanggal Booking</label>
                                <input type="date" class="form-control" id="filter_tanggal">
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Booking (Selesai)</label>
                            <select class="form-control select2" id="sel_booking" style="width: 100%;" disabled>
                                <option value="">-- Semua Booking Selesai --</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Pilih Komisi Belum Cair *</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 5%;"><input type="checkbox" id="checkAll"></th>
                                            <th>Kode Booking</th>
                                            <th>Komisi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="miniTableBody">
                                        <tr><td colspan="3" class="text-center text-muted">Pilih terapis terlebih dahulu...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0" id="nominalGroup" class="d-none">
                        <label class="font-weight-bold">Nominal Pencairan *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Rp</span>
                            </div>
                            <input type="text" class="form-control font-weight-bold text-success font-size-16" name="nominal" id="nominal" value="0" readonly>
                        </div>
                        <small class="text-muted" id="nominalHint">Total nominal dari komisi yang dipilih.</small>
                    </div>
                    
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary waves-effect waves-light font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-primary waves-effect waves-light font-weight-bold px-4">Simpan Rincian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
const KODE_PENCAIRAN = '<?= htmlspecialchars($kode_pencairan) ?>';
let idPencairan = null;
let isClosed = false;
let currentList = [];
let dataTable = null;
let allKomisiRaw = []; // To store komisi objects temporarily
let biayaAdmin = 0; // Global store for admin fee

window.onload = async () => {
    $('.select2').select2({ dropdownParent: $('#formModal') });
    
    await fetchInfoPencairan();
    if (idPencairan) {
        document.getElementById('form_id_pencarian').value = idPencairan;
        fetchList();
        setupSelect2Cascade();
    }
};

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

// 1. Fetch info pencairan dari list.php
async function fetchInfoPencairan() {
    try {
        const response = await fetch(`../../api/pencairan-komisi/list.php?kode_pencairan=${KODE_PENCAIRAN}&_t=${Date.now()}`);
        const result = await response.json();
        if (result.status === 'success' && result.data.length > 0) {
            const data = result.data[0];
            idPencairan = data.id_pencarian;
            isClosed = (parseInt(data.isClosed) === 1);
            biayaAdmin = parseFloat(data.biaya_admin) || 0;
            
            document.getElementById('info_kode').innerText = data.kode_pencairan;
            document.getElementById('info_bank').innerText = data.bank || '-';
            document.getElementById('info_tanggal').innerText = data.tanggal_transfer || '-';
            document.getElementById('info_admin').innerText = formatRupiah(biayaAdmin);
            document.getElementById('info_keterangan').innerText = data.keterangan || '-';
            
            document.getElementById('row_biaya_admin').innerText = '- ' + formatRupiah(biayaAdmin);
            
            if (isClosed) {
                document.getElementById('info_status').innerHTML = '<span class="badge badge-soft-success"><i class="mdi mdi-check-circle"></i> Closed</span>';
                document.getElementById('btnTambahRincian').classList.add('d-none'); // Hide tambah button
                
                $('#buktiFormContainer').addClass('d-none');
                $('#buktiView').removeClass('d-none');
                
                if (data.bukti) {
                    const ext = data.bukti.split('.').pop().toLowerCase();
                    const url = `../../images/pencairan/${data.bukti}`;
                    $('#btnDownloadBukti').attr('href', url);
                    
                    if (['jpg','jpeg','png'].includes(ext)) {
                        $('#buktiImageContainer').html(`<img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 400px;" alt="Bukti Transfer">`);
                    } else if (ext === 'pdf') {
                        $('#buktiImageContainer').html(`<embed src="${url}" type="application/pdf" width="100%" height="400px" />`);
                    } else {
                        $('#buktiImageContainer').html(`<p class="text-muted">Format file tidak mendukung preview.</p>`);
                    }
                } else {
                    $('#buktiImageContainer').html(`<p class="text-muted font-italic">Tidak ada file bukti yang terlampir.</p>`);
                    $('#btnDownloadBukti').addClass('d-none');
                }
            } else {
                document.getElementById('info_status').innerHTML = '<span class="badge badge-soft-warning"><i class="mdi mdi-clock-outline"></i> Open</span>';
                document.getElementById('btnTambahRincian').classList.remove('d-none'); // Show tambah button
                
                $('#buktiView').addClass('d-none');
                $('#buktiFormContainer').removeClass('d-none');
                
                if (!window.closingDropifyInit) {
                    $('#buktiClosing').dropify();
                    window.closingDropifyInit = true;
                }
            }
        } else {
            Swal.fire('Error', 'Data pencairan tidak ditemukan', 'error').then(() => {
                window.location = 'pencairan-komisi.php';
            });
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error', 'Gagal memuat informasi', 'error');
    }
}

// 2. Fetch list detail komisi
async function fetchList() {
    try {
        const response = await fetch(`../../api/pencairan-komisi/detail-komisi.php?kode_pencairan=${KODE_PENCAIRAN}&_t=${Date.now()}`);
        const result = await response.json();
        
        if (dataTable) dataTable.destroy();
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            currentList = result.data;
            let total_cair = 0;
            
            currentList.forEach((item, index) => {
                total_cair += parseFloat(item.nominal);
                let actionHtml = '-';
                
                if (!isClosed) {
                    actionHtml = `
                        <button onclick="deleteData(${item.id_pencarian}, ${item.id_komisi})" class="btn btn-sm btn-danger waves-effect waves-light" title="Hapus"><i class="mdi mdi-trash-can"></i></button>
                    `;
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${index + 1}</td>
                        <td class="align-middle font-weight-bold">${item.nama_terapis} <br><small class="text-muted">${item.kode_terapis}</small></td>
                        <td class="align-middle"><a href="../booking/detail.php?id=${item.id_booking}" target="_blank" class="text-info">${item.kode_booking}</a></td>
                        <td class="align-middle text-right font-weight-bold text-success">${formatRupiah(item.nominal)}</td>
                        <td class="align-middle text-center">${actionHtml}</td>
                    </tr>
                `;
            });
            
            document.getElementById('subtotal_komisi').innerText = formatRupiah(total_cair);
            const totalBersih = total_cair - biayaAdmin;
            document.getElementById('total_cair').innerText = formatRupiah(totalBersih);
            window.currentTotalBersih = totalBersih;
            
            if (currentList.length > 0 && !isClosed) {
                loadRekeningTerapis(currentList[0].kode_terapis);
            } else if (!isClosed) {
                $('#rekeningContainer').html('<div class="col-12"><div class="alert alert-warning">-- Data terapis kosong --</div></div>');
            }
        }
        
        dataTable = $('#datatable').DataTable({
            language: { emptyTable: "Belum ada rincian pencairan." }
        });
        
    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'Terjadi gangguan koneksi ke server.', 'error');
    }
}

async function loadRekeningTerapis(kode) {
    try {
        const res = await fetch(`../../api/terapis/list.php?kode_terapis=${kode}`);
        const json = await res.json();
        let html = '';
        if (json.status === 'success' && json.data.length > 0) {
            const t = json.data[0];
            let options = [];
            if (t.nor_rek1) options.push({no: t.nor_rek1, an: t.an_rek1, label: 'Rekening Utama'});
            if (t.no_rek2) options.push({no: t.no_rek2, an: t.an_rek2, label: 'Rekening Alternatif'});
            
            if (options.length === 0) {
                html = '<div class="col-12"><div class="alert alert-warning">Tidak ada data rekening untuk terapis ini.</div></div>';
            } else {
                options.forEach((opt, idx) => {
                    html += `
                    <div class="col-md-6 mb-3">
                        <div class="custom-control custom-radio border p-3 rounded shadow-sm bg-light" style="cursor: pointer;" onclick="document.getElementById('rek_${idx}').click()">
                            <input type="radio" id="rek_${idx}" name="rekening_radio" class="custom-control-input" value="${opt.no}" data-an="${opt.an}">
                            <label class="custom-control-label font-weight-bold" for="rek_${idx}" style="cursor: pointer; width: 100%;">
                                ${opt.an}<br>
                                <span class="text-primary font-size-18">${opt.no}</span>
                            </label>
                        </div>
                    </div>`;
                });
            }
        } else {
            html = '<div class="col-12"><div class="alert alert-warning">Data terapis tidak ditemukan.</div></div>';
        }
        $('#rekeningContainer').html(html);
    } catch(e) {
        console.error(e);
        $('#rekeningContainer').html('<div class="col-12"><div class="alert alert-danger">Gagal mengambil data rekening terapis.</div></div>');
    }
}

// 3. Cascade Select2 Logic
async function setupSelect2Cascade() {
    // 3a. Load Terapis
    try {
        const resT = await fetch('../../api/terapis/list.php');
        const jsonT = await resT.json();
        if(jsonT.status === 'success') {
            let html = '<option value="">-- Cari Terapis --</option>';
            jsonT.data.forEach(t => {
                html += `<option value="${t.id_terapis}">${t.nama_terapis} (${t.kode_terapis})</option>`;
            });
            $('#sel_terapis').html(html);
        }
    } catch(e) {}

    // 3b. On Terapis Change -> Load Booking (SELESAI)
    $('#sel_terapis').on('change', async function() {
        const idTerapis = $(this).val();
        $('#sel_booking').html('<option value="">-- Pilih Booking --</option>').prop('disabled', true);
        $('#sel_komisi').html('<option value="">-- Pilih Komisi --</option>').prop('disabled', true);
        $('#nominal').val(0);
        
        if(idTerapis) {
            try {
                let url = `../../api/booking/list.php?status_booking=SELESAI&id_terapis=${idTerapis}`;
                const tgl = $('#filter_tanggal').val();
                if (tgl) url += `&tanggal_awal=${tgl}&tanggal_akhir=${tgl}`;
                
                const resB = await fetch(url);
                const jsonB = await resB.json();
                if(jsonB.status === 'success') {
                    let html = '<option value="">-- Semua Booking Selesai --</option>';
                    jsonB.data.forEach(b => {
                        html += `<option value="${b.id_booking}">${b.kode_booking} - ${b.nama_member || 'Guest'}</option>`;
                    });
                    $('#sel_booking').html(html).prop('disabled', false);
                }
                
                loadMiniTable(idTerapis, $('#sel_booking').val(), tgl);
                
                const selectedText = $('#sel_terapis option:selected').text();
                const match = selectedText.match(/\(([^)]+)\)$/);
                if (match) {
                    loadRekeningTerapis(match[1]);
                }
            } catch(e){}
        } else {
            $('#rekeningContainer').html('<div class="col-12"><div class="alert alert-warning">-- Data terapis kosong --</div></div>');
        }
    });

    $('#filter_tanggal').on('change', function() {
        $('#sel_terapis').trigger('change');
    });

    // 3c. On Booking Change -> Load Komisi (BELUM_CAIR)
    $('#sel_booking').on('change', function() {
        const idTerapis = $('#sel_terapis').val();
        const tgl = $('#filter_tanggal').val();
        loadMiniTable(idTerapis, $(this).val(), tgl);
    });

    // CheckAll logic
    $('#checkAll').on('change', function() {
        $('.komisi-check').prop('checked', $(this).prop('checked'));
        calculateTotal();
    });

    $(document).on('change', '.komisi-check', function() {
        if (!$(this).prop('checked')) {
            $('#checkAll').prop('checked', false);
        } else if ($('.komisi-check:checked').length === $('.komisi-check').length) {
            $('#checkAll').prop('checked', true);
        }
        calculateTotal();
    });
}

function calculateTotal() {
    let total = 0;
    $('.komisi-check:checked').each(function() {
        total += parseFloat($(this).data('nominal'));
    });
    $('#nominal').val(new Intl.NumberFormat('id-ID').format(total));
}

async function loadMiniTable(idTerapis, idBooking, tanggal) {
    if (!idTerapis) return;
    
    $('#miniTableBody').html('<tr><td colspan="3" class="text-center"><i class="mdi mdi-spin mdi-loading"></i> Memuat...</td></tr>');
    $('#nominal').val(0);
    $('#checkAll').prop('checked', false);
    allKomisiRaw = [];
    
    let url = `../../api/komisi/list.php?status_pencairan=BELUM_CAIR&id_terapis=${idTerapis}`;
    if (idBooking) url += `&id_booking=${idBooking}`;
    if (tanggal) url += `&tanggal=${tanggal}`;
    
    try {
        const resK = await fetch(url);
        const jsonK = await resK.json();
        if(jsonK.status === 'success') {
            allKomisiRaw = jsonK.data;
            let html = '';
            
            if (jsonK.data.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted">Tidak ada komisi belum cair.</td></tr>';
            } else {
                jsonK.data.forEach(k => {
                    const komisi = parseFloat(k.nominal_komisi || 0);
                    
                    html += `
                        <tr>
                            <td class="text-center"><input type="checkbox" class="komisi-check" data-id="${k.id_komisi}" data-nominal="${komisi}"></td>
                            <td>${k.kode_booking || '-'}</td>
                            <td class="text-right font-weight-bold text-success">${formatRupiah(komisi)}</td>
                        </tr>
                    `;
                });
            }
            $('#miniTableBody').html(html);
        }
    } catch(e){
        $('#miniTableBody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>');
    }
}

function showForm() {
    if(isClosed) return;
    document.getElementById('detailForm').reset();
    document.getElementById('id_detail_pencairan').value = '';
    
    // Show select group
    document.getElementById('insertGroup').classList.remove('d-none');
    
    // Jika transaksi sudah memiliki rincian komisi, kunci pilihan terapis ke terapis tersebut
    if (currentList && currentList.length > 0) {
        const lockedIdTerapis = currentList[0].id_terapis;
        $('#sel_terapis').val(lockedIdTerapis).trigger('change');
        $('#sel_terapis').prop('disabled', true);
    } else {
        $('#sel_terapis').val('').trigger('change');
        $('#sel_terapis').prop('disabled', false);
    }
    
    $('#filter_tanggal').val('');
    $('#miniTableBody').html('<tr><td colspan="3" class="text-center text-muted">Pilih terapis terlebih dahulu...</td></tr>');
    
    document.getElementById('formTitle').innerText = 'Tambah Rincian Komisi';
    $('#formModal').modal('show');
}

async function submitClosing(e) {
    e.preventDefault();
    
    if (!window.currentTotalBersih || window.currentTotalBersih <= 0) {
        Swal.fire('Peringatan', 'Total Komisi Cair (Bersih) belum tersedia atau bernilai 0.', 'warning');
        return;
    }
    
    const selectedRadio = document.querySelector('input[name="rekening_radio"]:checked');
    if (!selectedRadio) {
        Swal.fire('Peringatan', 'Silakan pilih Rekening Tujuan terlebih dahulu.', 'warning');
        return;
    }
    
    document.getElementById('an_rekClosing').value = selectedRadio.getAttribute('data-an');
    document.getElementById('no_rekClosing').value = selectedRadio.value;
    
    const form = document.getElementById('closingForm');
    const formData = new FormData(form);
    formData.append('kode_pencairan', KODE_PENCAIRAN);
    
    const btn = document.getElementById('btnSubmitClosing');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading mr-1"></i> Memproses...';
    
    try {
        const response = await fetch('../../api/pencairan-komisi/closing.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();
        
        if (res.status === 'success') {
            Swal.fire('Berhasil', res.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal', res.message, 'error');
            btn.disabled = false;
            btn.innerHTML = oriText;
        }
    } catch(err) {
        Swal.fire('Error', 'Gangguan sistem: ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = oriText;
    }
}

async function saveData(e) {
    e.preventDefault();
    
    // Multi-insert mode
    const checkedBoxes = $('.komisi-check:checked');
    if (checkedBoxes.length === 0) {
        Swal.fire('Peringatan', 'Silakan centang minimal 1 komisi untuk ditambahkan.', 'warning');
        return;
    }
    
    const btn = document.getElementById('btnSubmit');
    const oriText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-spin mdi-loading"></i> Menyimpan...';
    
    try {
        const promises = [];
        checkedBoxes.each(function() {
            const idKomisi = $(this).data('id');
            const nominal = $(this).data('nominal');
            
            const fd = new FormData();
            fd.append('id_pencarian', idPencairan);
            fd.append('id_komisi', idKomisi);
            fd.append('nominal', nominal);
            
            promises.push(fetch('../../api/pencairan-komisi/save-detail.php', {
                method: 'POST',
                body: fd
            }).then(r => r.json()));
        });
        
        const results = await Promise.all(promises);
        const allSuccess = results.every(r => r.status === 'success');
        
        if (allSuccess) {
            $('#formModal').modal('hide');
            Swal.fire('Berhasil!', 'Semua rincian komisi berhasil ditambahkan.', 'success');
            fetchList();
        } else {
            Swal.fire('Info', 'Beberapa komisi mungkin gagal disimpan. Silakan periksa kembali.', 'info');
            fetchList();
        }
    } catch (error) {
        Swal.fire('Error', 'Gangguan koneksi ke server', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = oriText;
}

function deleteData(id_pencarian, id_komisi) {
    if(isClosed) return;
    
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: "Rincian komisi ini akan dihapus dari transaksi pencairan!",
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed || result.value) {
            try {
                const formData = new FormData();
                formData.append('id_pencarian', id_pencarian);
                formData.append('id_komisi', id_komisi);
                
                const response = await fetch('../../api/pencairan-komisi/delete-detail.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('HTTP status ' + response.status);
                
                const res = await response.json();
                
                if (res.status === 'success') {
                    // Berhasil dihapus, cukup refresh tabel tanpa notif sukses tambahan
                    fetchList();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                logDebug("Caught exception: " + err.message);
                Swal.fire('Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
            }
        }
    }).catch(err => {
        logDebug("SweetAlert exception: " + err.message);
    });
}
</script>
