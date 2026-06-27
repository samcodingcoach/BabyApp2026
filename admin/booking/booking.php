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
            <h4 class="mb-0 font-size-18">Manajemen Booking</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Klinik</a></li>
                    <li class="breadcrumb-item active">Booking</li>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Daftar Transaksi Booking</h4>
                    <button onclick="showForm()" class="btn btn-success waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-plus mr-1"></i> Buat Transaksi Baru
                    </button>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Kode</th>
                                <th>Tgl Jadwal</th>
                                <th>Avail. At</th>
                                <th>Klien (Member)</th>
                                <th>Terapis</th>
                                <th>Total Rp</th>
                                <th>Status</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Form Booking (MASTER DETAIL) -->
<div class="modal fade" id="modalFormBooking" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="formTitle">Form Tambah Transaksi Booking</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="bookingForm" onsubmit="saveData(event)">
                <div class="modal-body">
                    <ul class="nav nav-tabs nav-justified mb-3">
                        <li class="nav-item">
                            <a href="#tab-pelanggan" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                <i class="mdi mdi-account-circle d-lg-none d-block"></i>
                                <span class="d-none d-lg-block">Informasi Pelanggan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-jadwal" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <i class="mdi mdi-calendar-clock d-lg-none d-block"></i>
                                <span class="d-none d-lg-block">Jadwal & Terapis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-layanan" data-toggle="tab" aria-expanded="false" class="nav-link">
                                <i class="mdi mdi-format-list-bulleted d-lg-none d-block"></i>
                                <span class="d-none d-lg-block">Rincian Layanan</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB PELANGGAN -->
                        <div class="tab-pane show active" id="tab-pelanggan">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Member (Orang Tua) *</label>
                                <div class="col-sm-9">
                                    <select id="id_member" class="form-control select2" required onchange="loadBabies()" style="width: 100%;">
                                        <option value="">-- Pilih Member --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Target Pasien</label>
                                <div class="col-sm-9">
                                    <select id="id_member_or_id_bayi" class="form-control select2" style="width: 100%;">
                                        <option value="">-- Diri Sendiri (Bukan Bayi) --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat Kunjungan</label>
                                <div class="col-sm-9">
                                    <input type="hidden" id="alamat_baru">
                                    <div id="alamat_baru_editor" style="height: 100px;"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Whatsapp Aktif</label>
                                <div class="col-sm-9">
                                    <input type="text" id="whatsapp_baru" class="form-control" placeholder="(Opsional) WA yg bisa dihubungi saat ini" data-toggle="input-mask" data-mask-format="0000-0000-00000">
                                </div>
                            </div>
                        </div>

                        <!-- TAB JADWAL -->
                        <div class="tab-pane" id="tab-jadwal">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal & Jam *</label>
                                <div class="col-sm-9">
                                    <input type="datetime-local" id="tanggal_booking" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Pilih Terapis *</label>
                                <div class="col-sm-9">
                                    <select id="id_terapis" class="form-control select2" required style="width: 100%;">
                                        <option value="">-- Pilih Terapis --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Catatan Booking</label>
                                <div class="col-sm-9">
                                    <input type="hidden" id="catatan">
                                    <div id="catatan_editor" style="height: 100px;"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Prioritas (VIP)</label>
                                <div class="col-sm-9">
                                    <select id="prioritas" class="form-control select2" style="width: 100%;">
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya, Prioritaskan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Ongkos Kirim</label>
                                <div class="col-sm-9">
                                    <select id="tarif_ongkir" class="form-control font-weight-bold select2" onchange="kalkulasiGrandTotal()" style="width: 100%;">
                                        <option value="0">-- Gratis Ongkir (Rp 0) --</option>
                                    </select>
                                    <small class="form-text text-muted">(Otomatis terdeteksi dari rute Terapis &#10142; Member)</small>
                                </div>
                            </div>
                        </div>

                        <!-- TAB LAYANAN -->
                        <div class="tab-pane" id="tab-layanan">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="font-size-15 m-0 text-info">Rincian Layanan yang Dipesan</h5>
                                <button type="button" onclick="addRowLayanan()" class="btn btn-sm btn-info waves-effect waves-light">
                                    <i class="mdi mdi-plus"></i> Tambah Baris Layanan
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="tableLayanan">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Pilih Layanan</th>
                                            <th style="width: 25%;">Keluhan Spesifik</th>
                                            <th style="width: 15%;">Tarif Dasar (Rp)</th>
                                            <th style="width: 10%;">Diskon (Rp)</th>
                                            <th style="width: 15%;">Subtotal (Rp)</th>
                                            <th style="width: 5%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyLayanan">
                                        <!-- Dynamic Rows Here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right font-weight-bold font-size-14 align-middle text-muted">Ongkos Kirim :</td>
                                            <td colspan="2" class="font-weight-bold font-size-14 text-muted align-middle">
                                                Rp <span id="lblOngkirTotal">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right font-weight-bold font-size-16 align-middle">GRAND TOTAL :</td>
                                            <td colspan="2" class="font-weight-bold font-size-18 text-success align-middle">
                                                Rp <span id="lblGrandTotal">0</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-success waves-effect waves-light font-weight-bold px-4">Simpan & Terbitkan Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail/Invoice Booking -->
<div class="modal fade" id="modalDetailBooking" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold mb-1 text-dark">INVOICE / STRUK</h5>
                <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs nav-justified mb-3" id="navTabsBooking">
                    <li class="nav-item" id="nav_item_invoice">
                        <a href="#tab-inv-detail" data-toggle="tab" class="nav-link active">Invoice / Struk</a>
                    </li>
                    <li class="nav-item" id="nav_item_status">
                        <a href="#tab-inv-status" data-toggle="tab" class="nav-link">Ubah Status Transaksi</a>
                    </li>
                    <li class="nav-item" id="nav_item_reschedule">
                        <a href="#tab-inv-reschedule" data-toggle="tab" class="nav-link">Reschedule Jadwal</a>
                    </li>
                    <li class="nav-item" id="nav_item_bayar" style="display:none;">
                        <a href="#tab-inv-bayar" data-toggle="tab" class="nav-link text-success font-weight-bold">Pelunasan</a>
                    </li>
                </ul>

                <div id="div_batal_info" style="display:none;" class="text-center p-5 border rounded bg-white">
                    <i class="mdi mdi-close-circle text-danger" style="font-size: 72px;"></i>
                    <h3 class="text-danger mt-3 font-weight-bold">TRANSAKSI TELAH DIBATALKAN</h3>
                    <h5 class="text-muted mt-2">Dibatalkan pada: <span id="batal_timestamp" class="font-weight-bold text-dark"></span></h5>
                </div>

                <div class="tab-content" id="tabContentBooking">
                    <div class="tab-pane show active" id="tab-inv-detail">
                        <div class="card border border-light shadow-none mb-0" style="background-color: #fafafa; border-radius: 12px; border: 1px solid #e3e6f0 !important; overflow: hidden;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #fff; border-bottom: 2px dashed #e3e6f0;">
                                <h4 class="mb-0 font-weight-bold" id="inv_main_title"><i class="mdi mdi-receipt mr-2"></i> INVOICE TAGIHAN</h4>
                                <h4 class="m-0 text-muted font-weight-bold" id="inv_kode"></h4>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h6 class="font-size-14 text-muted font-weight-bold text-uppercase mb-2">Ditagihkan Kepada:</h6>
                                        <h5 class="font-size-16 font-weight-bold text-dark mb-1" id="inv_member"></h5>
                                        <address class="line-h-24 text-muted">
                                            Pasien: <span class="text-dark" id="inv_bayi"></span><br>
                                            <i class="mdi mdi-whatsapp text-success"></i> <span id="inv_wa"></span><br>
                                            <i class="mdi mdi-map-marker-outline text-danger"></i> <span id="inv_alamat"></span>
                                        </address>
                                    </div>
                                    <div class="col-sm-6 text-sm-right mt-4 mt-sm-0">
                                        <h6 class="font-size-14 text-muted font-weight-bold text-uppercase mb-2">Detail Pemesanan:</h6>
                                        <p class="mb-1"><strong>Jadwal: </strong> <span id="inv_jadwal" class="text-primary font-weight-bold"></span></p>
                                        <p class="mb-1"><strong>Status: </strong> <span id="inv_status"></span></p>
                                        <p class="mb-1"><strong>Terapis: </strong> <span class="text-dark font-weight-bold" id="inv_terapis"></span></p>
                                        <p class="mb-1"><strong>Prioritas: </strong> <span class="text-danger font-weight-bold" id="inv_prioritas"></span></p>
                                        <p class="mb-0"><strong>Tgl Cetak: </strong> <span class="text-muted" id="inv_created_at"></span></p>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="table-responsive border rounded">
                                            <table class="table table-centered table-nowrap table-striped mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th style="width: 5%;">No</th>
                                                        <th>Layanan & Keluhan</th>
                                                        <th class="text-right" style="width: 25%;">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="inv_body_layanan">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-sm-6">
                                        <!-- Ruang kosong atau catatan -->
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="float-right text-right">
                                            <p class="mb-2 font-size-15"><b>Ongkos Kirim:</b> <span class="ml-3 text-muted">Rp <span id="inv_ongkir">0</span></span></p>
                                            <hr>
                                            <h3 class="text-success font-weight-bold">Rp <span id="inv_grandtotal">0</span></h3>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4" id="inv_pembayaran_section" style="display:none;">
                                    <div class="col-12">
                                        <h6 class="font-size-14 text-muted font-weight-bold text-uppercase mb-2 border-bottom pb-2">Pembayaran:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 200px;"><b>Kode Booking</b></td>
                                                    <td style="width: 10px;">:</td>
                                                    <td id="inv_detail_kode_booking"></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Metode Pembayaran</b></td>
                                                    <td>:</td>
                                                    <td id="inv_detail_metode"></td>
                                                </tr>
                                                <tr id="tr_inv_detail_tgl" style="display:none;">
                                                    <td><b>Tanggal Pembayaran</b></td>
                                                    <td>:</td>
                                                    <td id="inv_detail_tgl"></td>
                                                </tr>
                                                <tr id="tr_inv_detail_va" style="display:none;">
                                                    <td><b>Nomor Virtual Account</b></td>
                                                    <td>:</td>
                                                    <td><b class="text-dark" id="inv_detail_va"></b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <button type="button" class="btn btn-danger font-weight-bold px-4 py-2 shadow-sm" id="btnDownloadInvoice" style="display:none; font-size:16px;">
                                <i class="mdi mdi-file-pdf-outline mr-1"></i> Download Faktur (PDF)
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab-inv-status">
                        <div class="form-group">
                            <label>Pilih Status Baru:</label>
                            <div class="input-group">
                                <select id="update_status_sel" class="custom-select font-weight-bold">
                                    <option value="MENUNGGU">Menunggu</option>
                                    <option value="DIJADWALKAN">Dijadwalkan</option>
                                    <option value="DIKONFIRMASI">Dikonfirmasi</option>
                                    <option value="SELESAI">Selesai</option>
                                    <option value="BATAL">Batal</option>
                                </select>
                                <div class="input-group-append">
                                    <input type="hidden" id="update_id_booking">
                                    <button onclick="eksekusiUpdateStatus()" class="btn btn-primary font-weight-bold" type="button">Update Status</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab-inv-reschedule">
                        <div class="form-group">
                            <label>Pilih Tanggal & Jam Baru:</label>
                            <div class="input-group">
                                <input type="datetime-local" id="reschedule_tgl" class="form-control">
                                <div class="input-group-append">
                                    <button onclick="eksekusiReschedule()" class="btn btn-warning font-weight-bold text-dark" type="button">Simpan Jadwal</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB PELUNASAN -->
                    <div class="tab-pane" id="tab-inv-bayar">
                        <form id="formPelunasan">
                            <input type="hidden" id="bayar_id_booking" name="id_booking">
                            <input type="hidden" id="bayar_nominal_asli" name="jumlah_bayar">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Grand Total Tagihan</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control font-weight-bold text-danger" id="bayar_tagihan" readonly style="font-size: 20px; background:#fff;">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Metode Pembayaran</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" id="metode_pembayaran" name="metode_pembayaran" required style="width: 100%;">
                                        <option value="Cash">Cash (Tunai)</option>
                                        <option value="Transfer Bank">Transfer Bank Manual</option>
                                        <option value="QRIS">QRIS (Otomatis)</option>
                                        <option value="VA">Virtual Account (Otomatis)</option>
                                        <option value="Debit / EDC">Debit / Mesin EDC</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-block font-weight-bold mt-4" id="btnSubmitBayar" style="font-size:16px; padding:10px;">Proses Pembayaran (LUNAS)</button>
                        </form>
                        
                        <div id="divWaitingBayar" style="display:none;" class="text-center p-4 border rounded bg-light">
                            <h4 class="text-warning mt-3 font-weight-bold"><i class="mdi mdi-clock-outline"></i> MENUNGGU PEMBAYARAN</h4>
                            <h5 class="text-dark mt-2" id="waiting_metode_teks"></h5>
                            <div id="waiting_instruction" class="my-3"></div>
                            <h3 class="text-danger font-weight-bold" id="waiting_countdown"></h3>
                            <button type="button" class="btn btn-outline-danger mt-3" onclick="showFormPelunasan()">Batalkan / Ubah Metode</button>
                        </div>
                        
                        <div id="divLunasInfo" style="display:none;" class="text-center p-4 border rounded bg-light">
                            <i class="mdi mdi-check-circle text-success" style="font-size: 64px;"></i>
                            <h3 class="text-success mt-3 font-weight-bold">TRANSAKSI TELAH LUNAS</h3>
                            <h5 class="text-dark mt-2" id="lunas_tgl_teks"></h5>
                            <h5 class="text-muted" id="lunas_metode_teks"></h5>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Tutup Nota</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let masterLayanan = [];
let masterMember = [];
let masterTerapis = [];
let masterOngkir = [];
let dataTable = null;
let quillAlamatBaru;
let quillCatatan;
let countdownInterval;

function showFormPelunasan() {
    document.getElementById('formPelunasan').style.display = 'block';
    document.getElementById('divWaitingBayar').style.display = 'none';
    document.getElementById('divLunasInfo').style.display = 'none';
    if(countdownInterval) clearInterval(countdownInterval);
}

function startCountdown(expireAt, idBooking) {
    if(countdownInterval) clearInterval(countdownInterval);
    
    const countdownEl = document.getElementById('waiting_countdown');
    
    countdownInterval = setInterval(() => {
        const now = new Date().getTime();
        const distance = expireAt.getTime() - now;
        
        if (distance < 0) {
            clearInterval(countdownInterval);
            countdownEl.innerHTML = "WAKTU HABIS";
            Swal.fire('Waktu Habis', 'Waktu pembayaran telah habis, silakan buat ulang.', 'warning').then(() => {
                showFormPelunasan();
            });
            return;
        }
        
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        countdownEl.innerHTML = minutes + "m " + seconds + "s ";
    }, 1000);
}


window.onload = async () => {
    if($().select2) {
        $('.select2').not('#metode_pembayaran').select2({ dropdownParent: $('#modalFormBooking') });
        $('#metode_pembayaran').select2({ dropdownParent: $('#modalDetailBooking') });
    }
    quillAlamatBaru = new Quill('#alamat_baru_editor', { theme: 'snow', placeholder: '(Opsional) Isi jika alamat berbeda dengan profil' });
    quillCatatan = new Quill('#catatan_editor', { theme: 'snow', placeholder: 'Cth: Tolong bawakan mainan...' });
    
    // Hentikan polling jika modal invoice ditutup
    $('#modalDetailBooking').on('hidden.bs.modal', function () {
        if(pollInterval) clearInterval(pollInterval);
    });
    
    await fetchList();
    await preloadData();
};

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
}

function unformatRp(str) {
    if(typeof str === 'number') return str;
    return parseFloat((str || '').toString().replace(/[^0-9]/g, '')) || 0;
}

function formatRupiahInput(input) {
    let val = unformatRp(input.value);
    input.value = val === 0 ? '0' : formatRp(val);
}

function getBadge(status) {
    switch(status) {
        case 'MENUNGGU': return '<span class="badge badge-warning">Menunggu</span>';
        case 'DIJADWALKAN': return '<span class="badge badge-info">Dijadwalkan</span>';
        case 'DIKONFIRMASI': return '<span class="badge badge-primary">Dikonfirmasi</span>';
        case 'SELESAI': return '<span class="badge badge-success">Selesai</span>';
        case 'BATAL': return '<span class="badge badge-danger">Dibatalkan</span>';
        default: return status;
    }
}

// ----------------------------------------------------
// FETCH DAFTAR TRANSAKSI
// ----------------------------------------------------
async function fetchList() {
    try {
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch('../../api/booking/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            result.data.forEach((item, index) => {
                const dateObj = new Date(item.tanggal_booking);
                const tgl = dateObj.toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':');
                
                const dateEndObj = new Date(item.waktu_selesai);
                const tglEnd = dateEndObj.toLocaleString('id-ID', {hour:'2-digit', minute:'2-digit'}).replace(/\./g, ':');

                tbody.innerHTML += `
                    <tr>
                        <td class="align-middle">${index + 1}</td>
                        <td class="align-middle"><strong>${item.kode_booking}</strong></td>
                        <td class="align-middle">${tgl}</td>
                        <td class="align-middle" style="color:#28a745; font-weight:bold;">${tglEnd}</td>
                        <td class="align-middle">${item.nama_member}</td>
                        <td class="align-middle">${item.nama_terapis}</td>
                        <td class="align-middle">Rp ${formatRp(item.grand_total)}</td>
                        <td class="align-middle">${getBadge(item.status_booking)}</td>
                        <td class="align-middle">
                            <button onclick="lihatDetail(${item.id_booking})" class="btn btn-sm btn-info waves-effect waves-light font-weight-bold">Buka Nota</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada riwayat booking."
            }
        });
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem API.', 'error');
    }
}

// ----------------------------------------------------
// PRELOAD DATA UNTUK FORM (Member, Terapis, Layanan)
// ----------------------------------------------------
async function preloadData() {
    let resLayanan = await fetch('../../api/layanan/list.php');
    let jsonLayanan = await resLayanan.json();
    if(jsonLayanan.status === 'success') masterLayanan = jsonLayanan.data;

    let resOngkir = await fetch('../../api/ongkir/list.php');
    let jsonOngkir = await resOngkir.json();
    if(jsonOngkir.status === 'success') {
        masterOngkir = jsonOngkir.data;
        const selOngkir = document.getElementById('tarif_ongkir');
        masterOngkir.forEach(o => {
            if (o.is_active == 1) {
                selOngkir.innerHTML += `<option value="${o.harga}">${o.dari_kecamatan} &#10142; ${o.ke_kecamatan} (Rp ${formatRp(o.harga)})</option>`;
            }
        });
    }

    let resMember = await fetch('../../api/member/list.php');
    let jsonMember = await resMember.json();
    if(jsonMember.status === 'success') {
        masterMember = jsonMember.data;
        const selMember = document.getElementById('id_member');
        masterMember.forEach(m => {
            selMember.innerHTML += `<option value="${m.id_member}" data-kecamatan="${m.kecamatan || ''}">${m.nama} (NIK: ${m.nik})</option>`;
        });
    }

    let resTerapis = await fetch('../../api/terapis/list.php');
    let jsonTerapis = await resTerapis.json();
    if(jsonTerapis.status === 'success') {
        masterTerapis = jsonTerapis.data;
        const selTerapis = document.getElementById('id_terapis');
        masterTerapis.forEach(t => {
            selTerapis.innerHTML += `<option value="${t.id_terapis}" data-kecamatan="${t.kecamatan || ''}">${t.nama_terapis}</option>`;
        });
        
        if($().select2) {
            $('#id_terapis').on('change', autoSelectOngkir);
        } else {
            selTerapis.addEventListener('change', autoSelectOngkir);
        }
    }
}

async function loadBabies() {
    const id_member = document.getElementById('id_member').value;
    const selBayi = document.getElementById('id_member_or_id_bayi');
    selBayi.innerHTML = '<option value="">-- Diri Sendiri (Bukan Bayi) --</option>'; 
    
    autoSelectOngkir();
    
    if(!id_member) {
        if($().select2) $('#id_member_or_id_bayi').trigger('change');
        return;
    }

    let res = await fetch(`../../api/bayi/list.php?id_member=${id_member}`);
    let json = await res.json();
    
    if(json.status === 'success' && json.data.length > 0) {
        json.data.forEach(b => {
            selBayi.innerHTML += `<option value="${b.id_bayi}">ANAK: ${b.nama_bayi}</option>`;
        });
    }
    
    if($().select2) $('#id_member_or_id_bayi').trigger('change');
}

function autoSelectOngkir() {
    const selMember = document.getElementById('id_member');
    const selTerapis = document.getElementById('id_terapis');
    
    const optMember = selMember.options[selMember.selectedIndex];
    const optTerapis = selTerapis.options[selTerapis.selectedIndex];
    
    const kecMember = optMember ? (optMember.getAttribute('data-kecamatan') || '').toLowerCase() : '';
    const kecTerapis = optTerapis ? (optTerapis.getAttribute('data-kecamatan') || '').toLowerCase() : '';
    
    const selOngkir = document.getElementById('tarif_ongkir');
    let found = false;
    
    if (kecMember && kecTerapis) {
        for (let i = 0; i < masterOngkir.length; i++) {
            const o = masterOngkir[i];
            if (o.is_active == 1 && 
                (o.dari_kecamatan || '').toLowerCase() === kecTerapis && 
                (o.ke_kecamatan || '').toLowerCase() === kecMember) {
                
                selOngkir.value = o.harga;
                found = true;
                break;
            }
        }
    }
    
    if (!found) {
        selOngkir.value = "0";
    }
    
    if($().select2) $('#tarif_ongkir').trigger('change.select2');
    kalkulasiGrandTotal();
}

// ----------------------------------------------------
// DYNAMIC ROWS UNTUK LAYANAN
// ----------------------------------------------------
let rowCount = 0;
function addRowLayanan() {
    rowCount++;
    let tr = document.createElement('tr');
    tr.id = 'rowLayanan_' + rowCount;
    
    let optionsLayanan = '<option value="">-- Pilih Layanan --</option>';
    masterLayanan.forEach(l => {
        optionsLayanan += `<option value="${l.id_layanan}" data-id_harga="${l.id_harga_layanan}" data-harga="${l.harga}">${l.nama_layanan} (Rp ${formatRp(l.harga)})</option>`;
    });

    tr.innerHTML = `
        <td><select class="form-control sel-layanan select2" onchange="kalkulasiRow(${rowCount})" style="width: 100%;">${optionsLayanan}</select></td>
        <td><input type="text" class="form-control inp-keluhan" placeholder="Cth: Pegal bahu"></td>
        <td><input type="text" class="form-control inp-harga" readonly style="background:#e9ecef;"></td>
        <td><input type="text" class="form-control inp-diskon" value="0" oninput="formatRupiahInput(this); kalkulasiRow(${rowCount})"></td>
        <td><input type="text" class="form-control inp-subtotal text-success font-weight-bold" readonly style="background:#e9ecef;"></td>
        <td class="text-center align-middle"><button type="button" onclick="hapusRow(${rowCount})" class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></button></td>
    `;
    
    document.getElementById('tbodyLayanan').appendChild(tr);
    
    if($().select2) {
        $(tr).find('.select2').select2({ dropdownParent: $('#modalFormBooking') });
    }
}

function hapusRow(id) {
    document.getElementById('rowLayanan_' + id).remove();
    kalkulasiGrandTotal();
}

function kalkulasiRow(id) {
    const tr = document.getElementById('rowLayanan_' + id);
    const sel = tr.querySelector('.sel-layanan');
    const opt = sel.options[sel.selectedIndex];
    
    let harga = 0;
    if(opt && opt.value !== '') {
        harga = parseFloat(opt.getAttribute('data-harga')) || 0;
    }
    
    tr.querySelector('.inp-harga').value = formatRp(harga);
    
    let diskon = unformatRp(tr.querySelector('.inp-diskon').value);
    let subtotal = harga - diskon;
    if(subtotal < 0) subtotal = 0;
    
    tr.querySelector('.inp-subtotal').value = formatRp(subtotal);
    
    kalkulasiGrandTotal();
}

function kalkulasiGrandTotal() {
    let grand = 0;
    const subtotals = document.querySelectorAll('.inp-subtotal');
    subtotals.forEach(el => {
        grand += unformatRp(el.value);
    });
    
    const ongkir = parseFloat(document.getElementById('tarif_ongkir').value) || 0;
    document.getElementById('lblOngkirTotal').innerText = formatRp(ongkir);
    
    grand += ongkir;
    document.getElementById('lblGrandTotal').innerText = formatRp(grand);
}

// ----------------------------------------------------
// SAVE TRANSAKSI MASTER-DETAIL
// ----------------------------------------------------
async function saveData(e) {
    e.preventDefault();
    
    const id_member = document.getElementById('id_member').value;
    const id_member_or_id_bayi = document.getElementById('id_member_or_id_bayi').value;
    const tanggal_booking = document.getElementById('tanggal_booking').value;
    const id_terapis = document.getElementById('id_terapis').value;
    
    let details = [];
    const rows = document.getElementById('tbodyLayanan').querySelectorAll('tr');
    
    for(let i=0; i<rows.length; i++) {
        const tr = rows[i];
        const sel = tr.querySelector('.sel-layanan');
        const opt = sel.options[sel.selectedIndex];
        
        if(opt && opt.value !== '') {
            details.push({
                id_layanan: opt.value,
                id_harga_layanan: opt.getAttribute('data-id_harga'),
                keluhan: tr.querySelector('.inp-keluhan').value,
                nominal: unformatRp(tr.querySelector('.inp-harga').value),
                diskon: unformatRp(tr.querySelector('.inp-diskon').value),
                ppn: 0,
                total: unformatRp(tr.querySelector('.inp-subtotal').value)
            });
        }
    }
    
    if(details.length === 0) {
        Swal.fire('Peringatan', "Pilih minimal 1 Layanan!", 'warning');
        return;
    }

    const alamatText = quillAlamatBaru.root.innerHTML === '<p><br></p>' ? '' : quillAlamatBaru.root.innerHTML;
    const catatanText = quillCatatan.root.innerHTML === '<p><br></p>' ? '' : quillCatatan.root.innerHTML;
    const whatsappClean = $('#whatsapp_baru').cleanVal() ? $('#whatsapp_baru').cleanVal() : $('#whatsapp_baru').val();

    const params = new URLSearchParams();
    params.append('id_member', id_member);
    params.append('id_member_or_id_bayi', id_member_or_id_bayi);
    params.append('tanggal_booking', tanggal_booking);
    params.append('id_terapis', id_terapis);
    params.append('alamat_baru', alamatText);
    params.append('whatsapp_baru', whatsappClean);
    params.append('prioritas', document.getElementById('prioritas').value);
    params.append('catatan', catatanText);
    params.append('tarif_ongkir', document.getElementById('tarif_ongkir').value);
    params.append('details', JSON.stringify(details));
    
    const btnSubmit = document.getElementById('btnSubmit');
    const oriText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = "<i class='mdi mdi-spin mdi-loading'></i> Memproses...";

    try {
        const res = await fetch('../../api/booking/save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        const json = await res.json();
        
        if(json.status === 'success') {
            Swal.fire('Sukses', json.message + "\nKode Booking: " + json.data.kode_booking, 'success');
            hideForm();
            fetchList();
        } else {
            Swal.fire('Gagal', json.message, 'error');
        }
    } catch(err) {
        Swal.fire('Error', "Terjadi kesalahan jaringan.", 'error');
    }
    
    btnSubmit.disabled = false;
    btnSubmit.innerHTML = oriText;
}

// ----------------------------------------------------
// TAMPILAN INVOICE & UPDATE STATUS
// ----------------------------------------------------
async function lihatDetail(id_booking) {
    document.getElementById('inv_body_layanan').innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';
    
    try {
        const res = await fetch('../../api/invoice/list.php?id_booking=' + id_booking);
        const json = await res.json();
        
        if(json.status === 'success') {
            const b = json.data;
            document.getElementById('inv_member').innerText = b.nama;
            document.getElementById('inv_bayi').innerText = b.nama_bayi || 'Diri Sendiri';
            document.getElementById('inv_wa').innerText = b.whatsapp || '-';
            
            let alamatLengkap = b.alamat_baru || b.alamat || '-';
            if(b.kecamatan) alamatLengkap += ' (Kec. ' + b.kecamatan + ')';
            document.getElementById('inv_alamat').innerHTML = alamatLengkap;
            
            const dateObj = new Date(b.tanggal_booking);
            const dateCreate = new Date(b.created_at);
            document.getElementById('inv_jadwal').innerText = dateObj.toLocaleString('id-ID').replace(/\./g, ':');
            document.getElementById('inv_created_at').innerText = dateCreate.toLocaleString('id-ID').replace(/\./g, ':');
            document.getElementById('inv_terapis').innerText = b.nama_terapis;
            document.getElementById('inv_status').innerHTML = getBadge(b.status_booking);
            document.getElementById('inv_prioritas').innerText = b.prioritas == 1 ? 'VIP / Penting' : 'Normal';
            
            document.getElementById('update_id_booking').value = b.id_booking;
            document.getElementById('update_status_sel').value = b.status_booking;
            
            let tbody = '';
            b.details.forEach((d, i) => {
                let keluhanHtml = d.keluhan ? `<br><small class="text-danger"><i class="mdi mdi-alert-circle-outline"></i> Keluhan: ${d.keluhan}</small>` : '';
                let kategoriHtml = d.nama_kategori ? `<span class="badge badge-soft-info mr-1">${d.nama_kategori}</span>` : '';
                let durasiHtml = d.durasi_menit ? `<span class="badge badge-soft-secondary"><i class="mdi mdi-clock-outline"></i> ${d.durasi_menit} mnt</span>` : '';
                
                tbody += `
                    <tr>
                        <td class="text-center align-middle">${i+1}</td>
                        <td>
                            <b class="text-dark font-size-15">${d.nama_layanan}</b><br>
                            ${kategoriHtml} ${durasiHtml}
                            ${keluhanHtml}
                        </td>
                        <td class="text-right align-middle text-dark font-weight-bold">Rp ${formatRp(d.total)}</td>
                    </tr>
                `;
            });
            document.getElementById('inv_body_layanan').innerHTML = tbody;
            document.getElementById('inv_ongkir').innerText = formatRp(b.tarif_ongkir);
            document.getElementById('inv_grandtotal').innerText = formatRp(b.grand_total);
            
            // Matrix Visibilitas Tab
            $('#div_batal_info').hide();
            $('#navTabsBooking').show();
            $('#tabContentBooking').show();
            
            $('#nav_item_invoice').show();
            $('#nav_item_status').show();
            $('#nav_item_reschedule').show();
            $('#nav_item_bayar').hide();
            $('#btnDownloadInvoice').hide();
            
            const navBayar = document.getElementById('nav_item_bayar');
            const formBayar = document.getElementById('formPelunasan');
            const divLunas = document.getElementById('divLunasInfo');
            
            if (b.status_booking === 'BATAL') {
                $('#navTabsBooking').hide();
                $('#tabContentBooking').hide();
                $('#div_batal_info').show();
                // Tampilkan tgl jika ada update_at, atau tgl_booking sbg fallback
                $('#batal_timestamp').text(dateCreate.toLocaleString('id-ID'));
            } else {
                if (b.is_lunas) {
                    $('#inv_main_title').html('<i class="mdi mdi-check-decagram mr-2"></i> FAKTUR LUNAS').removeClass('text-danger text-warning').addClass('text-success');
                    $('#btnDownloadInvoice').html('<i class="mdi mdi-file-pdf-outline mr-1"></i> Download Faktur Lunas (PDF)').removeClass('btn-outline-danger btn-danger').addClass('btn-success');
                    document.getElementById('inv_kode').innerText = (b.kode_pembayaran ? b.kode_pembayaran : b.kode_booking);
                } else {
                    $('#inv_main_title').html('<i class="mdi mdi-alert-circle-outline mr-2"></i> INVOICE TAGIHAN').removeClass('text-success text-warning').addClass('text-danger');
                    $('#btnDownloadInvoice').html('<i class="mdi mdi-file-pdf-outline mr-1"></i> Download Invoice Tagihan (PDF)').removeClass('btn-outline-success btn-success').addClass('btn-danger');
                    document.getElementById('inv_kode').innerText = b.kode_booking;
                }

                if (b.id_pembayaran) {
                    $('#inv_pembayaran_section').show();
                    document.getElementById('inv_detail_kode_booking').innerText = b.kode_booking;
                    document.getElementById('inv_detail_metode').innerText = (b.metode_pembayaran || '-').toUpperCase();
                    
                    if (b.is_lunas && b.tanggal_bayar) {
                        $('#tr_inv_detail_tgl').show();
                        const dBayar = new Date(b.tanggal_bayar.replace(' ', 'T'));
                        document.getElementById('inv_detail_tgl').innerText = dBayar.toLocaleString('id-ID').replace(/\./g, ':');
                    } else {
                        $('#tr_inv_detail_tgl').hide();
                    }
                    
                    if (b.metode_pembayaran && b.metode_pembayaran.toUpperCase().includes('VA')) {
                        $('#tr_inv_detail_va').show();
                        document.getElementById('inv_detail_va').innerText = b.va_number || '-';
                    } else {
                        $('#tr_inv_detail_va').hide();
                    }
                } else {
                    $('#inv_pembayaran_section').hide();
                }

                if (b.status_booking === 'DIKONFIRMASI') {
                    $('#nav_item_reschedule').hide();
                } else if (b.status_booking === 'SELESAI') {
                    $('#nav_item_status').hide();
                    $('#nav_item_reschedule').hide();
                    $('#nav_item_bayar').show();
                    
                    document.getElementById('bayar_id_booking').value = b.id_booking;
                    document.getElementById('bayar_nominal_asli').value = b.grand_total;
                    document.getElementById('bayar_tagihan').value = 'Rp ' + formatRp(b.grand_total);
                    
                    if (b.is_lunas) {
                        formBayar.style.display = 'none';
                        if (document.getElementById('divWaitingBayar')) document.getElementById('divWaitingBayar').style.display = 'none';
                        divLunas.style.display = 'block';
                        document.getElementById('lunas_tgl_teks').innerText = 'Tgl Bayar: ' + b.tanggal_bayar;
                        document.getElementById('lunas_metode_teks').innerText = 'Metode: ' + b.metode_pembayaran;
                    } else if (b.id_pembayaran && b.status_pembayaran === 'BELUM_LUNAS') {
                        // Cek waktu kadaluarsa
                        const createdAtStr = b.pembayaran_created_at.replace(' ', 'T');
                        const createdAt = new Date(createdAtStr);
                        const now = new Date();
                        let maxMinutes = 0;
                        if (b.metode_pembayaran.includes('QRIS')) maxMinutes = 8;
                        else if (b.metode_pembayaran.includes('VA')) maxMinutes = 60;
                        
                        const expireAt = new Date(createdAt.getTime() + maxMinutes * 60000);
                        if (now < expireAt && maxMinutes > 0) {
                            formBayar.style.display = 'none';
                            divLunas.style.display = 'none';
                            document.getElementById('divWaitingBayar').style.display = 'block';
                            
                            document.getElementById('waiting_metode_teks').innerText = 'Metode: ' + b.metode_pembayaran;
                            
                            let inst = '';
                            if (b.metode_pembayaran.includes('QRIS')) {
                                inst = `<img src="${b.qris_image}" style="width:200px; height:200px;"><br><small>Scan QRIS ini dengan aplikasi M-Banking/E-Wallet Anda.</small>`;
                            } else {
                                inst = `<h2 class="text-primary font-weight-bold">${b.va_number}</h2><small>Transfer ke Virtual Account di atas.</small>`;
                            }
                            document.getElementById('waiting_instruction').innerHTML = inst;
                            
                            startCountdown(expireAt, b.id_booking);
                            startPollingStatus(b.id_booking);
                        } else {
                            showFormPelunasan();
                        }
                    } else {
                        showFormPelunasan();
                    }
                }
                
                // Tampilkan tombol download HANYA JIKA ada data pembayaran (LUNAS atau BELUM_LUNAS)
                if (b.id_pembayaran && (b.status_pembayaran === 'BELUM_LUNAS' || b.status_pembayaran === 'LUNAS')) {
                    $('#btnDownloadInvoice').show().attr('onclick', `window.open('../../api/booking/cetak_invoice.php?id_booking=${b.id_booking}')`);
                }
                
                if (b.is_lunas) {
                    document.getElementById('inv_status').innerHTML += ' <span class="badge badge-success ml-1" style="font-size:14px;"><i class="mdi mdi-check-decagram"></i> LUNAS</span>';
                }
            }
            
            // Selalu set tab pertama aktif secara default saat dibuka
            $('#navTabsBooking a[href="#tab-inv-detail"]').tab('show');
            
            $('#modalDetailBooking').modal('show');
        } else {
            Swal.fire('Error', 'Gagal memuat detail nota.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Gagal mengambil data nota.', 'error');
    }
}

async function eksekusiUpdateStatus() {
    const id = document.getElementById('update_id_booking').value;
    const st = document.getElementById('update_status_sel').value;
    
    const params = new URLSearchParams();
    params.append('id_booking', id);
    params.append('status_booking', st);
    
    try {
        const res = await fetch('../../api/booking/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        const json = await res.json();
        if(json.status === 'success') {
            Swal.fire('Berhasil', json.message, 'success');
            lihatDetail(id);
            fetchList(); 
        } else {
            Swal.fire('Gagal', json.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Kesalahan koneksi.', 'error');
    }
}

// ----------------------------------------------------
// PROSES PELUNASAN
// ----------------------------------------------------
document.getElementById('formPelunasan').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitBayar');
    btn.disabled = true;
    btn.innerText = 'Memproses...';

    const formData = new FormData(e.target);

    const metode = document.getElementById('metode_pembayaran').value;
    
    let url = '../../api/booking/bayar.php';
    if (metode === 'QRIS') url = '../../api/midtrans/create_qris.php';
    if (metode === 'VA') url = '../../api/midtrans/create_va.php';

    try {
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        
        if (json.status === 'success') {
            Swal.fire('Berhasil', 'Pembayaran sedang diproses / disiapkan...', 'success').then(() => {
                lihatDetail(document.getElementById('bayar_id_booking').value);
                fetchList();
            });
        } else {
            Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Terjadi kesalahan pada sistem.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Proses Pembayaran (LUNAS)';
    }
});

async function eksekusiReschedule() {
    const id = document.getElementById('update_id_booking').value;
    const tgl = document.getElementById('reschedule_tgl').value;
    
    if(!tgl) {
        Swal.fire('Peringatan', "Pilih tanggal & jam reschedule!", 'warning');
        return;
    }
    
    // Langsung proses tanpa dialog konfirmasi
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    const params = new URLSearchParams();
    params.append('id_booking', id);
    params.append('tanggal_booking', tgl);
    
    try {
        const res = await fetch('../../api/booking/reschedule.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        const json = await res.json();
        
        if(json.status === 'success') {
            Swal.close(); // Tutup loading
            lihatDetail(id);
            fetchList(); 
        } else {
            Swal.fire('Gagal', json.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', "Gagal koneksi ke server.", 'error');
    }
}

function showForm() {
    document.getElementById('bookingForm').reset();
    document.getElementById('tbodyLayanan').innerHTML = ''; 
    document.getElementById('lblGrandTotal').innerText = '0';
    document.getElementById('id_member_or_id_bayi').innerHTML = '<option value="">-- Pilih Member Dulu --</option>';
    
    if($().select2) {
        $('#id_member').val('').trigger('change');
        $('#id_terapis').val('').trigger('change');
        $('#prioritas').val('0').trigger('change');
        $('#tarif_ongkir').val('0').trigger('change');
    }
    
    if (quillAlamatBaru) quillAlamatBaru.setContents([]);
    if (quillCatatan) quillCatatan.setContents([]);
    
    rowCount = 0;
    addRowLayanan(); 
    $('#modalFormBooking').modal('show');
}

function hideForm() {
    $('#modalFormBooking').modal('hide');
}

$('#modalDetailBooking').on('hidden.bs.modal', function () {
    if (pollInterval) clearInterval(pollInterval);
});

let pollInterval;
function startPollingStatus(id_booking) {
    if(pollInterval) clearInterval(pollInterval);
    
    pollInterval = setInterval(async () => {
        try {
            const res = await fetch('../../api/booking/detail.php?id_booking=' + id_booking);
            const json = await res.json();
            if(json.status === 'success') {
                const b = json.data;
                // Jika pembayaran lunas, hentikan polling dan otomatis update UI
                if(b.status_pembayaran === 'LUNAS') {
                    clearInterval(pollInterval);
                    Swal.fire('LUNAS!', 'Pembayaran ' + b.metode_pembayaran + ' telah berhasil diterima secara otomatis.', 'success');
                    lihatDetail(id_booking); // Refresh tab invoice
                    fetchList(); // Refresh tabel transaksi
                }
            }
        } catch(e) {}
    }, 4000); // Polling setiap 4 detik
}
</script>
