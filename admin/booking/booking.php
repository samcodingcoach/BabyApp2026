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

                <!-- FORM BOOKING (MASTER DETAIL) -->
                <div id="formBooking" style="display: none; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 20px; margin-bottom: 30px;">
                    <h5 class="text-success mb-4" id="formTitle">Form Tambah Transaksi Booking</h5>
                    <form id="bookingForm" onsubmit="saveData(event)">
                        <div class="row">
                            <!-- Kolom Kiri: Data Klien -->
                            <div class="col-md-6">
                                <h5 class="font-size-14 mb-3"><i class="mdi mdi-account-circle mr-1 text-primary"></i> Informasi Pelanggan</h5>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Member (Orang Tua) *</label>
                                    <div class="col-sm-8">
                                        <select id="id_member" class="form-control" required onchange="loadBabies()">
                                            <option value="">-- Pilih Member --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Target Pasien *</label>
                                    <div class="col-sm-8">
                                        <select id="id_member_or_id_bayi" class="form-control" required>
                                            <option value="">-- Pilih Member Dulu --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Alamat Kunjungan</label>
                                    <div class="col-sm-8">
                                        <textarea id="alamat_baru" class="form-control" rows="2" placeholder="(Opsional) Isi jika alamat berbeda dengan profil"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Whatsapp Aktif</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="whatsapp_baru" class="form-control" placeholder="(Opsional) WA yg bisa dihubungi saat ini">
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Jadwal & Terapis -->
                            <div class="col-md-6">
                                <h5 class="font-size-14 mb-3"><i class="mdi mdi-calendar-clock mr-1 text-warning"></i> Jadwal & Terapis</h5>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Tanggal & Jam *</label>
                                    <div class="col-sm-8">
                                        <input type="datetime-local" id="tanggal_booking" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Pilih Terapis *</label>
                                    <div class="col-sm-8">
                                        <select id="id_terapis" class="form-control" required>
                                            <option value="">-- Pilih Terapis --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Catatan Booking</label>
                                    <div class="col-sm-8">
                                        <textarea id="catatan" class="form-control" rows="2" placeholder="Cth: Tolong bawakan mainan..."></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Prioritas (VIP)</label>
                                    <div class="col-sm-8">
                                        <select id="prioritas" class="form-control bg-light">
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya, Prioritaskan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
                                    <div class="col-sm-8">
                                        <select id="tarif_ongkir" class="form-control font-weight-bold" onchange="kalkulasiGrandTotal()">
                                            <option value="0">-- Gratis Ongkir (Rp 0) --</option>
                                        </select>
                                        <small class="form-text text-muted">(Otomatis terdeteksi dari rute Terapis &#10142; Member)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-size-15 m-0 text-info">Rincian Layanan yang Dipesan</h5>
                            <button type="button" onclick="addRowLayanan()" class="btn btn-sm btn-info waves-effect waves-light">
                                <i class="mdi mdi-plus"></i> Tambah Baris Layanan
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tableLayanan">
                                <thead class="thead-light">
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
                                        <td colspan="4" class="text-right font-weight-bold font-size-16 align-middle">GRAND TOTAL :</td>
                                        <td colspan="2" class="font-weight-bold font-size-18 text-success align-middle">
                                            Rp <span id="lblGrandTotal">0</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="button" onclick="hideForm()" class="btn btn-secondary waves-effect waves-light mr-2 font-weight-bold">Batal</button>
                            <button type="submit" id="btnSubmit" class="btn btn-success waves-effect waves-light font-weight-bold px-4">Simpan & Terbitkan Booking</button>
                        </div>
                    </form>
                </div>

                <!-- DETAIL / INVOICE VIEW -->
                <div id="detailBooking" style="display: none; margin-bottom: 30px;">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card border border-light shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
                                        <div>
                                            <h3 class="font-weight-bold mb-1 text-dark">INVOICE / STRUK</h3>
                                            <p class="text-muted mb-0" id="inv_kode"></p>
                                        </div>
                                        <div>
                                            <button onclick="document.getElementById('detailBooking').style.display='none'" class="btn btn-sm btn-outline-danger font-weight-bold">
                                                <i class="mdi mdi-close"></i> Tutup Nota
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4">
                                        <div class="col-sm-6">
                                            <h6 class="text-muted font-size-14 text-uppercase mb-3">Ditujukan Kepada:</h6>
                                            <div class="mb-1"><strong>Member:</strong> <span id="inv_member"></span></div>
                                            <div class="mb-1"><strong>Pasien (Anak):</strong> <span id="inv_bayi"></span></div>
                                            <div class="mb-1"><strong>No. WA:</strong> <span id="inv_wa"></span></div>
                                            <div><strong>Alamat:</strong> <span id="inv_alamat"></span></div>
                                        </div>
                                        <div class="col-sm-6 text-sm-right mt-4 mt-sm-0">
                                            <h6 class="text-muted font-size-14 text-uppercase mb-3">Detail Pemesanan:</h6>
                                            <div class="mb-1"><strong>Tgl Cetak:</strong> <span id="inv_created_at"></span></div>
                                            <div class="mb-1"><strong>Jadwal:</strong> <span id="inv_jadwal" class="text-primary font-weight-bold"></span></div>
                                            <div class="mb-1"><strong>Terapis:</strong> <span id="inv_terapis"></span></div>
                                            <div class="mb-1"><strong>Status:</strong> <span id="inv_status"></span></div>
                                            <div><strong>Prioritas:</strong> <span id="inv_prioritas"></span></div>
                                        </div>
                                    </div>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-centered mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Layanan</th>
                                                    <th>Keluhan</th>
                                                    <th class="text-right">Harga</th>
                                                </tr>
                                            </thead>
                                            <tbody id="inv_body_layanan">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-right">BIAYA ONGKOS KIRIM</th>
                                                    <th class="text-right font-size-15">Rp <span id="inv_ongkir">0</span></th>
                                                </tr>
                                                <tr class="bg-light">
                                                    <th colspan="3" class="text-right font-size-16">TOTAL PEMBAYARAN</th>
                                                    <th class="text-right font-size-18 text-success font-weight-bold">Rp <span id="inv_grandtotal">0</span></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <div class="row">
                                        <!-- UPDATE STATUS SECTION -->
                                        <div class="col-md-6 mb-3">
                                            <div class="bg-light p-3 border rounded">
                                                <h6 class="font-weight-bold mb-3">Ubah Status Transaksi:</h6>
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

                                        <!-- RESCHEDULE SECTION -->
                                        <div class="col-md-6 mb-3">
                                            <div class="p-3 border border-warning rounded" style="background-color: #fff3cd;">
                                                <h6 class="font-weight-bold mb-3 text-dark">Reschedule Jadwal:</h6>
                                                <div class="input-group">
                                                    <input type="datetime-local" id="reschedule_tgl" class="form-control">
                                                    <div class="input-group-append">
                                                        <button onclick="eksekusiReschedule()" class="btn btn-warning font-weight-bold text-dark" type="button">Simpan Jadwal</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead class="thead-dark">
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
                            <tr><td colspan="9" class="text-center">Memuat database transaksi...</td></tr>
                        </tbody>
                    </table>
                </div>

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

window.onload = async () => {
    await fetchList();
    await preloadData();
};

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
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
        const response = await fetch('../../api/booking/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Belum ada riwayat booking.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const dateObj = new Date(item.tanggal_booking);
                const tgl = dateObj.toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
                
                const dateEndObj = new Date(item.waktu_selesai);
                const tglEnd = dateEndObj.toLocaleString('id-ID', {hour:'2-digit', minute:'2-digit'});

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
        }
    } catch (e) {}
}

// ----------------------------------------------------
// PRELOAD DATA UNTUK FORM (Member, Terapis, Layanan)
// ----------------------------------------------------
async function preloadData() {
    // Load Layanan
    let resLayanan = await fetch('../../api/layanan/list.php');
    let jsonLayanan = await resLayanan.json();
    if(jsonLayanan.status === 'success') masterLayanan = jsonLayanan.data;

    // Load Ongkir
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

    // Load Member
    let resMember = await fetch('../../api/member/list.php');
    let jsonMember = await resMember.json();
    if(jsonMember.status === 'success') {
        masterMember = jsonMember.data;
        const selMember = document.getElementById('id_member');
        masterMember.forEach(m => {
            selMember.innerHTML += `<option value="${m.id_member}" data-kecamatan="${m.kecamatan || ''}">${m.nama} (NIK: ${m.nik})</option>`;
        });
    }

    // Load Terapis
    let resTerapis = await fetch('../../api/terapis/list.php');
    let jsonTerapis = await resTerapis.json();
    if(jsonTerapis.status === 'success') {
        masterTerapis = jsonTerapis.data;
        const selTerapis = document.getElementById('id_terapis');
        masterTerapis.forEach(t => {
            selTerapis.innerHTML += `<option value="${t.id_terapis}" data-kecamatan="${t.kecamatan || ''}">${t.nama_terapis}</option>`;
        });
        selTerapis.addEventListener('change', autoSelectOngkir);
    }
}

// Load Bayi saat Member dipilih
async function loadBabies() {
    const id_member = document.getElementById('id_member').value;
    const selBayi = document.getElementById('id_member_or_id_bayi');
    selBayi.innerHTML = '<option value="">-- Diri Sendiri (Bukan Bayi) --</option>'; 
    
    autoSelectOngkir();
    
    if(!id_member) return;

    let res = await fetch(`../../api/bayi/list.php?id_member=${id_member}`);
    let json = await res.json();
    
    if(json.status === 'success' && json.data.length > 0) {
        json.data.forEach(b => {
            selBayi.innerHTML += `<option value="${b.id_bayi}">ANAK: ${b.nama_bayi}</option>`;
        });
    }
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
        <td><select class="form-control sel-layanan" onchange="kalkulasiRow(${rowCount})">${optionsLayanan}</select></td>
        <td><input type="text" class="form-control inp-keluhan" placeholder="Cth: Pegal bahu"></td>
        <td><input type="number" class="form-control inp-harga" readonly style="background:#e9ecef;"></td>
        <td><input type="number" class="form-control inp-diskon" value="0" oninput="kalkulasiRow(${rowCount})"></td>
        <td><input type="number" class="form-control inp-subtotal text-success font-weight-bold" readonly style="background:#e9ecef;"></td>
        <td class="text-center align-middle"><button type="button" onclick="hapusRow(${rowCount})" class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></button></td>
    `;
    
    document.getElementById('tbodyLayanan').appendChild(tr);
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
    
    tr.querySelector('.inp-harga').value = harga;
    
    let diskon = parseFloat(tr.querySelector('.inp-diskon').value) || 0;
    let subtotal = harga - diskon;
    if(subtotal < 0) subtotal = 0;
    
    tr.querySelector('.inp-subtotal').value = subtotal;
    
    kalkulasiGrandTotal();
}

function kalkulasiGrandTotal() {
    let grand = 0;
    const subtotals = document.querySelectorAll('.inp-subtotal');
    subtotals.forEach(el => {
        grand += parseFloat(el.value) || 0;
    });
    
    const ongkir = parseFloat(document.getElementById('tarif_ongkir').value) || 0;
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
                nominal: parseFloat(tr.querySelector('.inp-harga').value) || 0,
                diskon: parseFloat(tr.querySelector('.inp-diskon').value) || 0,
                ppn: 0,
                total: parseFloat(tr.querySelector('.inp-subtotal').value) || 0
            });
        }
    }
    
    if(details.length === 0) {
        alert("Pilih minimal 1 Layanan!");
        return;
    }

    const params = new URLSearchParams();
    params.append('id_member', id_member);
    params.append('id_member_or_id_bayi', id_member_or_id_bayi);
    params.append('tanggal_booking', tanggal_booking);
    params.append('id_terapis', id_terapis);
    params.append('alamat_baru', document.getElementById('alamat_baru').value);
    params.append('whatsapp_baru', document.getElementById('whatsapp_baru').value);
    params.append('prioritas', document.getElementById('prioritas').value);
    params.append('catatan', document.getElementById('catatan').value);
    params.append('tarif_ongkir', document.getElementById('tarif_ongkir').value);
    params.append('details', JSON.stringify(details));
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerText = "Memproses...";

    try {
        const res = await fetch('../../api/booking/save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        const json = await res.json();
        
        if(json.status === 'success') {
            alert(json.message + "\nKode Booking: " + json.data.kode_booking);
            hideForm();
            fetchList();
        } else {
            alert("Gagal: " + json.message);
        }
    } catch(err) {
        alert("Terjadi kesalahan jaringan.");
    }
    
    btnSubmit.disabled = false;
    btnSubmit.innerText = "Simpan & Terbitkan Booking";
}

// ----------------------------------------------------
// TAMPILAN INVOICE & UPDATE STATUS
// ----------------------------------------------------
async function lihatDetail(id_booking) {
    document.getElementById('formBooking').style.display = 'none';
    document.getElementById('detailBooking').style.display = 'block';
    
    document.getElementById('inv_body_layanan').innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';
    
    try {
        const res = await fetch('../../api/booking/detail.php?id_booking=' + id_booking);
        const json = await res.json();
        
        if(json.status === 'success') {
            const b = json.data;
            document.getElementById('inv_kode').innerText = "KODE: " + b.kode_booking;
            document.getElementById('inv_member').innerText = b.nama_member;
            document.getElementById('inv_bayi').innerText = b.nama_bayi || 'Diri Sendiri';
            document.getElementById('inv_wa').innerText = b.whatsapp_tampil || b.whatsapp_baru || b.whatsapp_member || '-';
            document.getElementById('inv_alamat').innerText = b.alamat_tampil || b.alamat_baru || '-';
            
            const dateObj = new Date(b.tanggal_booking);
            const dateCreate = new Date(b.created_at);
            document.getElementById('inv_jadwal').innerText = dateObj.toLocaleString('id-ID');
            document.getElementById('inv_created_at').innerText = dateCreate.toLocaleString('id-ID');
            document.getElementById('inv_terapis').innerText = b.nama_terapis;
            document.getElementById('inv_status').innerHTML = getBadge(b.status_booking);
            document.getElementById('inv_prioritas').innerText = b.prioritas == 1 ? 'VIP / Penting' : 'Normal';
            
            document.getElementById('update_id_booking').value = b.id_booking;
            document.getElementById('update_status_sel').value = b.status_booking;
            
            let tbody = '';
            b.details.forEach((d, i) => {
                tbody += `
                    <tr>
                        <td>${i+1}</td>
                        <td><strong>${d.nama_layanan}</strong></td>
                        <td>${d.keluhan || '-'}</td>
                        <td class="text-right">Rp ${formatRp(d.total)}</td>
                    </tr>
                `;
            });
            document.getElementById('inv_body_layanan').innerHTML = tbody;
            document.getElementById('inv_ongkir').innerText = formatRp(b.tarif_ongkir);
            document.getElementById('inv_grandtotal').innerText = formatRp(b.grand_total);
        }
    } catch (e) {
        alert('Gagal mengambil data nota.');
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
            alert('Berhasil: ' + json.message);
            lihatDetail(id);
            fetchList(); 
        } else {
            alert('Gagal: ' + json.message);
        }
    } catch(e) {}
}

async function eksekusiReschedule() {
    const id = document.getElementById('update_id_booking').value;
    const tgl = document.getElementById('reschedule_tgl').value;
    
    if(!tgl) {
        alert("Pilih tanggal & jam reschedule!");
        return;
    }
    
    if(!confirm("Yakin ingin memindahkan jadwal pesanan ini ke " + tgl + "? Status otomatis menjadi DIJADWALKAN.")) return;

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
            alert(json.message);
            lihatDetail(id);
            fetchList(); 
        } else {
            alert('Gagal Reschedule:\n' + json.message);
        }
    } catch(e) {
        alert("Gagal koneksi ke server.");
    }
}

function showForm() {
    document.getElementById('detailBooking').style.display = 'none';
    $('#formBooking').fadeIn();
    document.getElementById('bookingForm').reset();
    document.getElementById('tbodyLayanan').innerHTML = ''; 
    document.getElementById('lblGrandTotal').innerText = '0';
    document.getElementById('id_member_or_id_bayi').innerHTML = '<option value="">-- Pilih Member Dulu --</option>';
    rowCount = 0;
    addRowLayanan(); 
}

function hideForm() {
    $('#formBooking').fadeOut();
}
</script>
