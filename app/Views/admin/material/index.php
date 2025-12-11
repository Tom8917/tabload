<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Matériaux</mark>
        </h4>
        <a href="<?= base_url('/admin/material/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableMaterials" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Marque</th>
                    <th>Référence</th>
                    <th>N° Série</th>
                    <th>Badge</th>
                    <th>Centre</th>
                    <th>Attribution</th>
                    <th>Garantie</th>
                    <th class="text-start">Modifier</th>
                    <th class="text-start">Supprimer</th>
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

        var dataTable = $('#tableMaterials').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "language": {
                url: baseUrl + "/js/datatable/datatable-2.1.4-fr-FR.json",
            },
            "ajax": {
                "url": baseUrl + "admin/material/SearchMaterial",
                "type": "POST"
            },
            "columns": [
                {"data": "id"},
                {"data": "materialtype_type"},
                {"data": "materialbrand_marque"},
                {"data": "reference"},
                {"data": "nserie"},
                {"data": "badge"},
                {"data": "center_ville"},
                {"data": "user_email"},
                {
                    data: 'end_warranty',
                    sortable: false,
                    render: function (data, type, row) {
                        // Si la donnée est vide, "Non renseignée" ou "undefined"
                        if (!data || data === "Non renseignée" || data === "" || row.time_warranty === "11") {
                            return `<span class="badge bg-secondary">Non renseignée</span>`;
                        } else {
                            const currentDate = new Date();
                            const warrantyDate = new Date(data);

                            // Vérification de la validité de la date de fin de garantie
                            if (isNaN(warrantyDate)) {
                                return `<span class="badge bg-secondary">Aucune</span>`; // Si la date est invalide
                            }

                            // Formater la date en jj/mm/yyyy
                            const formattedDate = warrantyDate.toLocaleDateString('fr-FR');

                            // Si la garantie est expirée
                            if (warrantyDate < currentDate) {
                                return `<span class="badge bg-danger">Expirée (${formattedDate})</span>`;
                            } else {
                                // Si la garantie est encore valide
                                return `<span class="badge bg-success">Valide (${formattedDate})</span>`;
                            }
                        }
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="${baseUrl}admin/material/${data}" class="text-primary" title="Modifier">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="#" class="text-danger delete-btn" data-id="${data}" title="Supprimer">
                                    <i class="fa-solid fa-trash"></i>
                                </a>`;
                    }
                }
            ]
        });

        // Ajout de la confirmation avant suppression
        $(document).on("click", ".delete-btn", function (e) {
            e.preventDefault();
            var materialId = $(this).data("id");

            Swal.fire({
                title: "Êtes-vous sûr ?",
                text: "Cette action est irréversible !",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Oui, supprimer !",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = baseUrl + "admin/material/delete/" + materialId;
                }
            });
        });
    });
</script>

<style>
    #tableMaterials th, #tableMaterials td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableMaterials th, #tableMaterials td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>