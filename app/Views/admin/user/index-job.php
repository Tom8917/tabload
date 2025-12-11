<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des rôles</h4>
        <a href="<?= base_url('/admin/job/new'); ?>">
            <i class="fa-solid fa-circle-plus"></i>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableJob" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Diminutif</th>
                    <th>Slug</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
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
        var dataTable = $('#tableJob').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": baseUrl + "admin/job/SearchJob",
                "type": "POST"
            },
            "columns": [
                {"data": "id"},
                {"data": "type"},
                {"data": "diminutif"},
                {"data": "slug"},
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="${baseUrl}admin/job/${data}">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href='${baseUrl}admin/job/delete/${data}'>
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </a>`;
                    }
                }
            ]
        });
    });
</script>

<style>
    #tableJobs th, #tableJobs td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableJobs th, #tableJobs td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>