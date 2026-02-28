<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edoc Tag Management</title>

    <!-- Favicon -->
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/backend-plugin.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/backend.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/remixicon/fonts/remixicon.css') ?>">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="color-light">
    <!-- loader Start -->
    <div id="loading">
        <div id="loading-center">
        </div>
    </div>
    <!-- loader END -->

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="iq-sidebar sidebar-default" id="mainMenu">
        </div>
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Edoc Tags Management</h4>
                                </div>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createTagModal">
                                    <i class="las la-plus mr-2"></i>Add New Tag
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tagTable" class="table data-table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Nickname</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <!-- Create Modal -->
    <div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tag</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createTagForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="nickname">Nickname</label>
                            <input type="text" class="form-control" name="nickname">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editTagForm">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_nickname">Nickname</label>
                            <input type="text" class="form-control" name="nickname" id="edit_nickname">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="iq-footer">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><a href="../backend/privacy-policy.html">Privacy Policy</a></li>
                                <li class="list-inline-item"><a href="../backend/terms-of-service.html">Terms of Use</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6 text-right">
                            <span class="mr-1">
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>©
                            </span> <a href="#" class="">EdocDocument</a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/backend-bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/table-treeview.js') ?>"></script>
    <script src="<?= base_url('assets/js/customizer.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

    <script>
        $("#mainMenu").load("<?= base_url('index.php/utility/menuController/getMainMenu') ?>");


        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this tag?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('index.php/admin/edoctagcontroller/delete/') ?>' + id,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.status === 'success') {
                                table.ajax.reload();
                                showAlert('success', response.message);
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'An error occurred while processing your request.');
                        }
                    });
                }
            });
        });

        var table = null;
        $(document).ready(function() {
            // Initialize DataTable
            table = $('#tagTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '<?= base_url('index.php/admin/edoctagcontroller/getAll') ?>',
                    type: 'GET'
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'first_name'
                    },
                    {
                        data: 'last_name'
                    },
                    {
                        data: 'nickname'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-sm bg-secondary mr-2 edit-btn" data-id="${row.id}">
                                        <i class="ri-pencil-line mr-0"></i>
                                    </button>
                                    <button class="btn btn-sm bg-danger delete-btn" data-id="${row.id}">
                                        <i class="ri-delete-bin-line mr-0"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"></div>'
                }
            });

            // Reset form when modal is closed
            $('.modal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
            });

            // Create Tag
            $('#createTagForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?= base_url('index.php/admin/edoctagcontroller/create') ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#createTagModal').modal('hide');
                            table.ajax.reload();
                            showAlert('success', response.message);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function() {
                        showAlert('error', 'An error occurred while processing your request.');
                    }
                });
            });

            // Edit Tag
            $(document).on('click', '.edit-btn', function() {
                var row = table.row($(this).closest('tr')).data();
                $('#edit_id').val(row.id);
                $('#edit_first_name').val(row.first_name);
                $('#edit_last_name').val(row.last_name);
                $('#edit_nickname').val(row.nickname);
                $('#editTagModal').modal('show');
            });

            // Update Tag
            $('#editTagForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_id').val();
                $.ajax({
                    url: '<?= base_url('index.php/admin/edoctagcontroller/update/') ?>' + id,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editTagModal').modal('hide');
                            table.ajax.reload();
                            showAlert('success', response.message);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function() {
                        showAlert('error', 'An error occurred while processing your request.');
                    }
                });
            });

            // Delete Tag



        });

        // Alert function using template's style
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'bg-success' : 'bg-danger';
            const alert = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <div class="iq-alert-text">${message}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                `;

            // Remove existing alerts
            $('.alert').remove();

            // Add new alert at the top of the card body
            $('.card-body').prepend(alert);

            // Auto dismiss after 3 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 3000);
        }
    </script>
</body>

</html>