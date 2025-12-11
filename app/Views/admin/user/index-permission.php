<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des rôles</h4>
        <a href="<?= base_url('/admin/userpermission/new'); ?>"><i class="fa-solid fa-circle-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablePermission" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Slug</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";
        var dataTable = $('#tablePermission').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": baseUrl + "admin/userpermission/SearchPermission",
                "type": "POST"
            },
            "columns": [
                {"data": "id"},
                {"data": "name"},
                {"data": "slug"},
            ]
        });
    });

</script>

<style>
    #tablePermissions th, #tablePermissions td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tablePermissions th, #tablePermissions td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>