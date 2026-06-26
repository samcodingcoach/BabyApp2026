<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login-admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi Booking</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #eee; }
        button { padding: 6px 12px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;}
        
        .form-container, .detail-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background: #fffcf0; display: none; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 180px; vertical-align: top; font-weight: bold;}
        .form-group input, .form-group select, .form-group textarea { padding: 6px; width: 300px; border: 1px solid #ccc; border-radius:3px; }
        
        /* Dynamic Table untuk Layanan */
        .table-layanan { margin-top: 10px; background: #fff; }
        .table-layanan th { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        .table-layanan td { vertical-align: top; }
        .table-layanan select, .table-layanan input { width: 90%; }
        
        .badge { padding: 4px 8px; border-radius: 3px; font-weight: bold; color: white; font-size: 0.85em; }
        .bg-menunggu { background: #ffc107; color: black; }
        .bg-dijadwalkan { background: #17a2b8; }
        .bg-dikonfirmasi { background: #007bff; }
        .bg-selesai { background: #28a745; }
        .bg-batal { background: #dc3545; }
        
        .invoice-box { background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 800px; margin: auto; }
        .invoice-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajemen Transaksi Booking (Master-Detail)</h2>
    <a href="../../logout-admin.php" style="float: right; color: red; text-decoration: none;">Logout</a>
    <br><br>
    
    <button onclick="showForm()" style="background:#28a745; color:white; border:none; border-radius:3px; padding:10px 15px; font-weight:bold;">+ Buat Transaksi Baru</button>

    <!-- FORM BOOKING (MASTER DETAIL) -->
    <div class="form-container" id="formBooking">
        <h3 id="formTitle" style="color: #28a745;">Form Tambah Transaksi Booking</h3>
        <form id="bookingForm" onsubmit="saveData(event)">
            
            <div style="display:flex; gap: 40px;">
                <!-- Kolom Kiri: Data Klien -->
                <div style="flex: 1;">
                    <h4>Informasi Pelanggan</h4>
                    <div class="form-group">
                        <label>Member (Orang Tua) *</label>
                        <select id="id_member" required onchange="loadBabies()">
                            <option value="">-- Pilih Member --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Pasien *</label>
                        <select id="id_member_or_id_bayi" required>
                            <option value="">-- Pilih Member Dulu --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alamat Kunjungan Baru</label>
                        <textarea id="alamat_baru" rows="2" placeholder="(Opsional) Isi jika alamat berbeda dengan profil"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Whatsapp Aktif</label>
                        <input type="text" id="whatsapp_baru" placeholder="(Opsional) WA yg bisa dihubungi saat ini">
                    </div>
                </div>

                <!-- Kolom Kanan: Jadwal & Terapis -->
                <div style="flex: 1;">
                    <h4>Jadwal & Terapis</h4>
                    <div class="form-group">
                        <label>Tanggal & Jam Booking *</label>
                        <input type="datetime-local" id="tanggal_booking" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Terapis *</label>
                        <select id="id_terapis" required>
                            <option value="">-- Pilih Terapis --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan Booking</label>
                        <textarea id="catatan" rows="2" placeholder="Cth: Tolong bawakan mainan..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Prioritas (VIP)</label>
                        <select id="prioritas">
                            <option value="0">Tidak</option>
                            <option value="1">Ya, Prioritaskan</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0; border:0; border-top:1px solid #ccc;">
            
            <h4>Rincian Layanan yang Dipesan</h4>
            <button type="button" onclick="addRowLayanan()" style="background:#007bff; color:white; border:none; padding:5px 10px; border-radius:3px;">+ Tambah Baris Layanan</button>
            <table class="table-layanan" id="tableLayanan">
                <thead>
                    <tr>
                        <th style="width: 35%;">Pilih Layanan</th>
                        <th style="width: 20%;">Keluhan Spesifik</th>
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
                        <td colspan="4" style="text-align: right; font-weight: bold; font-size: 1.1em;">GRAND TOTAL :</td>
                        <td colspan="2" style="font-weight: bold; font-size: 1.1em; color: green;">Rp <span id="lblGrandTotal">0</span></td>
                    </tr>
                </tfoot>
            </table>

            <br>
            <button type="submit" id="btnSubmit" style="background: green; color: white; border-radius:3px; padding:10px 20px; border:none; font-weight:bold; font-size:1.1em; cursor:pointer;">Simpan & Terbitkan Booking</button>
            <button type="button" onclick="hideForm()" style="border-radius:3px; padding:10px 20px; border:1px solid #ccc; cursor:pointer;">Batal</button>
        </form>
    </div>

    <!-- DETAIL / INVOICE VIEW -->
    <div class="detail-container" id="detailBooking">
        <div class="invoice-box">
            <div class="invoice-header">
                <h3 style="margin:0; color:#333;">INVOICE / STRUK BOOKING</h3>
                <p style="margin:5px 0 0 0; color:#666;" id="inv_kode"></p>
                <button onclick="document.getElementById('detailBooking').style.display='none'" style="float:right; margin-top:-30px;">Tutup Nota</button>
            </div>
            
            <table style="border:none; margin-top:0;">
                <tr style="border:none;">
                    <td style="border:none; width:50%;">
                        <strong>Member:</strong> <span id="inv_member"></span><br>
                        <strong>Pasien (Anak):</strong> <span id="inv_bayi"></span><br>
                        <strong>No. WA:</strong> <span id="inv_wa"></span><br>
                        <strong>Alamat:</strong> <span id="inv_alamat"></span>
                    </td>
                    <td style="border:none; text-align:right;">
                        <strong>Tgl Cetak Nota:</strong> <span id="inv_created_at"></span><br>
                        <strong>Jadwal Kunjungan:</strong> <span id="inv_jadwal" style="color:#007bff;"></span><br>
                        <strong>Terapis:</strong> <span id="inv_terapis"></span><br>
                        <strong>Status:</strong> <span id="inv_status"></span><br>
                        <strong>Prioritas:</strong> <span id="inv_prioritas"></span>
                    </td>
                </tr>
            </table>

            <table style="margin-top: 20px;">
                <thead>
                    <tr style="background:#eee;">
                        <th>No</th>
                        <th>Layanan</th>
                        <th>Keluhan</th>
                        <th style="text-align:right;">Harga</th>
                    </tr>
                </thead>
                <tbody id="inv_body_layanan">
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right;">TOTAL PEMBAYARAN</th>
                        <th style="text-align:right; font-size:1.2em; color:green;">Rp <span id="inv_grandtotal">0</span></th>
                    </tr>
                </tfoot>
            </table>
            
            <!-- UPDATE STATUS SECTION -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd;">
                <strong>Ubah Status Transaksi:</strong>
                <select id="update_status_sel" style="padding:5px; margin-left:10px;">
                    <option value="MENUNGGU">Menunggu</option>
                    <option value="DIJADWALKAN">Dijadwalkan</option>
                    <option value="DIKONFIRMASI">Dikonfirmasi</option>
                    <option value="SELESAI">Selesai</option>
                    <option value="BATAL">Batal</option>
                </select>
                <input type="hidden" id="update_id_booking">
                <button onclick="eksekusiUpdateStatus()" style="background:#007bff; color:white; border:none; padding:6px 12px; border-radius:3px;">Update Status</button>
            </div>
        </div>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode</th>
                <th>Tgl Jadwal</th>
                <th>Klien (Member)</th>
                <th>Terapis</th>
                <th>Total Rp</th>
                <th>Status</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="8" style="text-align: center;">Memuat database transaksi...</td></tr>
        </tbody>
    </table>
</div>

<script>
let masterLayanan = [];
let masterMember = [];
let masterTerapis = [];

window.onload = async () => {
    await fetchList();
    await preloadData();
};

function formatRp(angka) {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
}

function getBadge(status) {
    switch(status) {
        case 'MENUNGGU': return '<span class="badge bg-menunggu">Menunggu</span>';
        case 'DIJADWALKAN': return '<span class="badge bg-dijadwalkan">Dijadwalkan</span>';
        case 'DIKONFIRMASI': return '<span class="badge bg-dikonfirmasi">Dikonfirmasi</span>';
        case 'SELESAI': return '<span class="badge bg-selesai">Selesai</span>';
        case 'BATAL': return '<span class="badge bg-batal">Dibatalkan</span>';
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
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Belum ada riwayat booking.</td></tr>';
                return;
            }
            
            result.data.forEach((item, index) => {
                const dateObj = new Date(item.tanggal_booking);
                const tgl = dateObj.toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${item.kode_booking}</strong></td>
                        <td>${tgl}</td>
                        <td>${item.nama_member}</td>
                        <td>${item.nama_terapis}</td>
                        <td>Rp ${formatRp(item.grand_total)}</td>
                        <td>${getBadge(item.status_booking)}</td>
                        <td>
                            <button onclick="lihatDetail(${item.id_booking})" style="background:#17a2b8; color:white; border:none; padding:4px 8px; border-radius:3px;">Buka Nota</button>
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

    // Load Member
    let resMember = await fetch('../../api/member/list.php');
    let jsonMember = await resMember.json();
    if(jsonMember.status === 'success') {
        masterMember = jsonMember.data;
        const selMember = document.getElementById('id_member');
        masterMember.forEach(m => {
            selMember.innerHTML += `<option value="${m.id_member}">${m.nama} (NIK: ${m.nik})</option>`;
        });
    }

    // Load Terapis
    let resTerapis = await fetch('../../api/terapis/list.php');
    let jsonTerapis = await resTerapis.json();
    if(jsonTerapis.status === 'success') {
        masterTerapis = jsonTerapis.data;
        const selTerapis = document.getElementById('id_terapis');
        masterTerapis.forEach(t => {
            selTerapis.innerHTML += `<option value="${t.id_terapis}">${t.nama_terapis}</option>`;
        });
    }
}

// Load Bayi saat Member dipilih
async function loadBabies() {
    const id_member = document.getElementById('id_member').value;
    const selBayi = document.getElementById('id_member_or_id_bayi');
    selBayi.innerHTML = '<option value="">-- Diri Sendiri (Bukan Bayi) --</option>'; // Default value is empty if self
    
    if(!id_member) return;

    let res = await fetch(`../../api/bayi/list.php?id_member=${id_member}`);
    let json = await res.json();
    
    if(json.status === 'success' && json.data.length > 0) {
        json.data.forEach(b => {
            selBayi.innerHTML += `<option value="${b.id_bayi}">ANAK: ${b.nama_bayi}</option>`;
        });
    }
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
        <td><select class="sel-layanan" onchange="kalkulasiRow(${rowCount})">${optionsLayanan}</select></td>
        <td><input type="text" class="inp-keluhan" placeholder="Cth: Pegal bahu"></td>
        <td><input type="number" class="inp-harga" readonly style="background:#eee;"></td>
        <td><input type="number" class="inp-diskon" value="0" oninput="kalkulasiRow(${rowCount})"></td>
        <td><input type="number" class="inp-subtotal" readonly style="background:#eee; font-weight:bold;"></td>
        <td><button type="button" onclick="hapusRow(${rowCount})" style="background:red; color:white; border:none; padding:4px 8px;">X</button></td>
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
    
    // Kumpulkan array Detail
    let details = [];
    const rows = document.getElementById('tbodyLayanan').querySelectorAll('tr');
    
    for(let i=0; i<rows.length; i++) {
        const tr = rows[i];
        const sel = tr.querySelector('.sel-layanan');
        const opt = sel.options[sel.selectedIndex];
        
        if(opt.value !== '') {
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

    // Build URL Encoded Payload
    const params = new URLSearchParams();
    params.append('id_member', id_member);
    params.append('id_member_or_id_bayi', id_member_or_id_bayi);
    params.append('tanggal_booking', tanggal_booking);
    params.append('id_terapis', id_terapis);
    params.append('alamat_baru', document.getElementById('alamat_baru').value);
    params.append('whatsapp_baru', document.getElementById('whatsapp_baru').value);
    params.append('prioritas', document.getElementById('prioritas').value);
    params.append('catatan', document.getElementById('catatan').value);
    params.append('details', JSON.stringify(details));
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerText = "Memproses Transaksi...";

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
    
    document.getElementById('inv_body_layanan').innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
    
    try {
        const res = await fetch('../../api/booking/detail.php?id_booking=' + id_booking);
        const json = await res.json();
        
        if(json.status === 'success') {
            const b = json.data;
            document.getElementById('inv_kode').innerText = "Kode: " + b.kode_booking;
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
            
            // Render Details
            let tbody = '';
            b.details.forEach((d, i) => {
                tbody += `
                    <tr>
                        <td>${i+1}</td>
                        <td><strong>${d.nama_layanan}</strong></td>
                        <td>${d.keluhan || '-'}</td>
                        <td style="text-align:right;">Rp ${formatRp(d.total)}</td>
                    </tr>
                `;
            });
            document.getElementById('inv_body_layanan').innerHTML = tbody;
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
            lihatDetail(id); // Reload Nota
            fetchList(); // Reload Tabel Background
        } else {
            alert('Gagal: ' + json.message);
        }
    } catch(e) {}
}

function showForm() {
    document.getElementById('detailBooking').style.display = 'none';
    document.getElementById('formBooking').style.display = 'block';
    document.getElementById('bookingForm').reset();
    document.getElementById('tbodyLayanan').innerHTML = ''; // Kosongkan row layanan
    document.getElementById('lblGrandTotal').innerText = '0';
    document.getElementById('id_member_or_id_bayi').innerHTML = '<option value="">-- Pilih Member Dulu --</option>';
    rowCount = 0;
    addRowLayanan(); // Auto-add 1 baris
}

function hideForm() {
    document.getElementById('formBooking').style.display = 'none';
}
</script>

</body>
</html>
