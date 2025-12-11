<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des <mark>E-liquides</mark></h4>
        <a href="<?= base_url('/admin/eliquid/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableEliquids" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Volume</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";

        var dataTable = $('#tableEliquids').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 10,
            language: {
                url: baseUrl + "/js/datatable/datatable-2.1.4-fr-FR.json",
            },
            ajax: {
                url: baseUrl + "admin/eliquid/search",
                type: "POST"
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'volume_ml' },
                { data: 'price' },
                {
                    data: 'stock',
                    render: function (data) {
                        let stock = parseFloat(data);
                        if (stock <= 10) {
                            return `<span class="badge bg-danger">${stock}</span>`;
                        }
                        return stock;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="${baseUrl}admin/eliquid/edit/${data}" class="text-warning"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="#" class="text-danger delete-btn" data-id="${data}"><i class="fa-solid fa-trash"></i></a>`;
                    }
                }
            ]
        });

        // Suppression avec confirmation
        $(document).on("click", ".delete-btn", function (e) {
            e.preventDefault();
            const id = $(this).data("id");

            Swal.fire({
                title: "Supprimer ce e-liquide ?",
                text: "Cette action est irréversible.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Oui, supprimer",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = baseUrl + "admin/eliquid/delete/" + id;
                }
            });
        });
    });
</script>

<style>
    #tableEliquids th, #tableEliquids td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }
</style>