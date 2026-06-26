<?php
session_start();
// Proteksi halaman admin, harus login
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
            <h4 class="mb-0 font-size-18">Manajemen Role / Hak Akses</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="../users/users.php">Users</a></li>
                    <li class="breadcrumb-item active">Role</li>
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
                    <h4 class="card-title mb-0">Daftar Role Sistem</h4>
                    <a href="../users/users.php" class="btn btn-secondary waves-effect waves-light font-weight-bold">
                        <i class="mdi mdi-arrow-left mr-1"></i> Kembali ke Users
                    </a>
                </div>

                <!-- TABEL DATA ROLE -->
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 10%; text-align: center;">ID Role</th>
                                <th style="width: 30%;">Nama Role</th>
                                <th style="width: 60%;">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody id="roleTableBody">
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
let dataTable = null;

window.onload = () => {
    fetchRolesList();
};

async function fetchRolesList() {
    try {
        const response = await fetch('../../api/role/list.php');
        const result = await response.json();
        
        if (dataTable) {
            dataTable.destroy();
        }
        
        const tbody = document.getElementById('roleTableBody');
        tbody.innerHTML = '';
        
        if (result.status === 'success') {
            result.data.forEach(role => {
                tbody.innerHTML += `
                    <tr>
                        <td class="text-center align-middle">${role.role_id}</td>
                        <td class="align-middle"><strong>${role.role_name}</strong></td>
                        <td class="align-middle">${role.description || '-'}</td>
                    </tr>
                `;
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
        
        dataTable = $('#datatable').DataTable({
            language: {
                emptyTable: "Belum ada data Role di dalam database."
            }
        });
        
    } catch (error) {
        Swal.fire('Error', 'Terjadi gangguan jaringan atau API tidak merespons.', 'error');
    }
}
</script>
