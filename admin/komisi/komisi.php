<?php
// Set session dan auth
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth_helper.php';

// Verifikasi auth
if (!check_auth($koneksi)) {
    header('Location: ../../login-admin.php');
    exit();
}

// Set variabel halaman
$page_title = "Komisi Terapis";
$active_menu = "komisi";
$base_url = "/terapi/admin"; // Menyesuaikan dengan base url
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">Komisi Terapis</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                            <li class="breadcrumb-item active">Komisi Terapis</li>
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
                        <h4 class="card-title mb-4">Data Komisi Terapis</h4>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Kode Booking</th>
                                        <th>Terapis</th>
                                        <th>Nominal Komisi</th>
                                        <th>Status Pencairan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <!-- Data akan di-load via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->

<?php include '../includes/footer.php'; ?>

<!-- Page Specific Script -->
<script>
let dataTable;

const formatRp = (angka) => {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(angka);
};

const getBadge = (status) => {
    if(status === 'BELUM_CAIR') return `<span class="badge badge-warning">BELUM CAIR</span>`;
    return `<span class="badge badge-success">SUDAH CAIR</span>`;
};

// Load Data
async function fetchList() {
    try {
        if (dataTable) {
            dataTable.destroy();
        }
        
        const response = await fetch('../../api/komisi/list.php');
        const result = await response.json();
        
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            result.data.forEach((item, index) => {
                
                let btnHtml = '';
                if (item.status_pencairan === 'BELUM_CAIR') {
                    btnHtml = `<button onclick="cairkan(${item.id_komisi})" class="btn btn-sm btn-success waves-effect waves-light"><i class="mdi mdi-cash-multiple"></i> Cairkan</button>`;
                } else {
                    btnHtml = `<span class="text-success"><i class="mdi mdi-check"></i> Selesai</span><br><small class="text-muted">${item.tanggal_pencairan}</small>`;
                }

                tbody.innerHTML += `
                    <tr>
                        <td class="align-middle">${index + 1}</td>
                        <td class="align-middle">${item.created_at}</td>
                        <td class="align-middle font-weight-bold">${item.kode_booking}</td>
                        <td class="align-middle">${item.nama_terapis}</td>
                        <td class="align-middle text-success font-weight-bold">Rp ${formatRp(item.nominal_komisi)}</td>
                        <td class="align-middle">${getBadge(item.status_pencairan)}</td>
                        <td class="align-middle text-center">${btnHtml}</td>
                    </tr>
                `;
            });
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data komisi."
            }
        });
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem API.', 'error');
    }
}

// Cairkan
function cairkan(id) {
    Swal.fire({
        title: 'Konfirmasi Pencairan',
        text: "Apakah Anda yakin ingin mencairkan dana komisi ini kepada terapis bersangkutan?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Cairkan!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id_komisi', id);
                
                const response = await fetch('../../api/komisi/cairkan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const res = await response.json();
                
                if (res.status === 'success') {
                    Swal.fire('Berhasil!', res.message, 'success');
                    fetchList();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        }
    });
}

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    fetchList();
});
</script>
