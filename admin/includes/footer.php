                </div> <!-- container-fluid -->
            </div> <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            2026 © Klinik Terapi.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-right d-none d-sm-block">
                                Aplikasi Manajemen
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div> <!-- end main content-->

    </div> <!-- END layout-wrapper -->

    <!-- Overlay-->
    <div class="menu-overlay"></div>

    <!-- jQuery  -->
    <script src="<?= $base_url ?>/assets/js/jquery.min.js"></script>
    <script src="<?= $base_url ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $base_url ?>/assets/js/metismenu.min.js"></script>
    <script src="<?= $base_url ?>/assets/js/waves.js"></script>
    <script src="<?= $base_url ?>/assets/js/simplebar.min.js"></script>

    <!-- third party js -->
    <script src="<?= $base_url ?>/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= $base_url ?>/plugins/datatables/dataTables.bootstrap4.js"></script>
    <script src="<?= $base_url ?>/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= $base_url ?>/plugins/datatables/responsive.bootstrap4.min.js"></script>
    <script src="<?= $base_url ?>/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= $base_url ?>/plugins/select2/select2.min.js"></script>

    <!-- Dropify, Mask, Quill JS -->
    <script src="<?= $base_url ?>/plugins/dropify/dropify.min.js"></script>
    <script src="<?= $base_url ?>/plugins/jquery-mask/jquery.mask.min.js"></script>
    <script src="<?= $base_url ?>/plugins/quill/quill.min.js"></script>

    <!-- App js -->
    <script src="<?= $base_url ?>/assets/js/theme.js"></script>
    
    <!-- Global Init for Mask -->
    <script>
    $(document).ready(function() {
        if ($('[data-toggle="input-mask"]').length > 0) {
            $('[data-toggle="input-mask"]').each(function() {
                var format = $(this).data("mask-format");
                var reverse = $(this).data("reverse");
                if (reverse !== undefined) {
                    $(this).mask(format, { reverse: reverse });
                } else {
                    $(this).mask(format);
                }
            });
        }
    });
    </script>
</body>
</html>
