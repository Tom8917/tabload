<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des categories de tickets</h4>
        <a href="<?= base_url('/admin/ticketcategory/new'); ?>" style="text-decoration: none"><i
                    class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ticketcategoriesTable" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Slug</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";

        var table = $('#ticketcategoriesTable').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, 1000], [10, 25, 50, 100, "Tous"]],
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": "<?= base_url('/admin/ticketcategory/SearchTicketCategory'); ?>",
                "type": "POST",
            },
            "columns": [
                {"data": "id"},
                {"data": "type"},
                {"data": "slug"},
                {
                    "data": "id",
                    "render": function (data) {
                        return `<a href="<?= base_url('/admin/ticket/ticket-category'); ?>${data}" class="text-primary"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    "data": "id",
                    "render": function (data) {
                        return `<a href="#" class="text-danger delete-btn" data-id="${data}"><i class="fa-solid fa-trash"></i></a>`;
                    }
                },
            ]
        });

// 🔴 Supprimer un ticket via AJAX
        $(document).on("click", ".delete-btn", function (e) {
            e.preventDefault();
            var ticketcategoryId = $(this).data("id");

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
                    $.ajax({
                        url: "<?= base_url('/admin/ticketcategory/delete/'); ?>" + ticketcategoryId,
                        type: "GET",
                        success: function (response) {
                            Swal.fire("Supprimé !", "Le ticket a été supprimé avec succès.", "success");
                            table.ajax.reload();
                        },
                        error: function () {
                            Swal.fire("Erreur !", "Impossible de supprimer le ticket.", "error");
                        }
                    });
                }
            });
        });
    });
</script>

<style>
    #ticketcategoriesTable th, #ticketcategoriesTable td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #ticketcategoriesTable th, #ticketcategoriesTable td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>