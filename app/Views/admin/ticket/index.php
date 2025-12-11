<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des Tickets</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ticketsTable" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Catégorie</th>
                    <th>Priorité</th>
                    <th>Status</th>
                    <th>Objet</th>
                    <th>Description</th>
                    <th>Créé le</th>
                    <th>Mis à jour le</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                    <th>Visualiser</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Détails du Ticket #<span id="ticket-id"></span>
                    <br> Auteur : <span id="ticket-email"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-header">
                <h5 class="modal-title">Statut : <span id="ticket-status"></span></h5>
            </div>
            <div class="modal-body">
                <p><strong>Catégorie :</strong> <span id="ticket-category"></span></p>
                <p><strong>Titre :</strong> <span id="ticket-title"></span></p>
                <p><strong>Description :</strong> <span id="ticket-description"></span></p>
                <p><strong>Priorité :</strong> <span id="ticket-priority"></span></p>
                <p><strong>Créé le :</strong> <span id="ticket-created"></span></p>
                <p><strong>Mis à jour le :</strong> <span id="ticket-updated"></span></p>
                <p><strong>Commentaire :</strong> <span id="ticket-comment"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";

        var table = $('#ticketsTable').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, 1000], [10, 25, 50, 100, "Tous"]],
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": "<?= base_url('/admin/ticket/SearchTicket'); ?>",
                "type": "POST",
            },
            "columns": [
                {"data": "id"},
                {"data": "user_email"},
                {"data": "ticketcategory_type"},
                {"data": "priority_type"},
                {"data": "status_type"},
                {"data": "title"},
                {"data": "description"},
                {"data": "created_at"},
                {"data": "updated_at"},
                {
                    "data": "id",
                    "render": function (data) {
                        return `<a href="<?= base_url('/admin/ticket/'); ?>${data}" class="text-primary"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    "data": "id",
                    "render": function (data) {
                        return `<a href="#" class="text-danger delete-btn" data-id="${data}"><i class="fa-solid fa-trash"></i></a>`;
                    }
                },
                {
                    "data": null,
                    "render": function (data) {
                        return `<a class="btn view-ticket" data-bs-toggle="modal" data-bs-target="#ticketModal"
           data-id="${data.id}" data-title="${data.title}" data-description="${data.description}"
           data-email="${data.user_email}" data-created="${data.created_at}" data-updated="${data.updated_at}"
           data-category="${data.ticketcategory_type}" data-priority="${data.priority_type}" data-status="${data.status_type}" data-comment="${data.comment}">
    <i class="fa-solid fa-eye"></i>
</a>`;
                    }
                }
            ]
        });

// 🟠 Ouvrir le modal avec les données dynamiques
        $(document).on("click", ".view-ticket", function () {
            $("#ticket-id").text($(this).data("id"));
            $("#ticket-title").text($(this).data("title"));
            $("#ticket-description").text($(this).data("description"));
            $("#ticket-email").text($(this).data("email"));
            $("#ticket-created").text($(this).data("created"));
            $("#ticket-updated").text($(this).data("updated"));
            $("#ticket-category").text($(this).data("category"));
            $("#ticket-priority").text($(this).data("priority"));
            $("#ticket-status").text($(this).data("status"));
            $("#ticket-comment").text($(this).data("comment"));
        });

// 🔴 Supprimer un ticket via AJAX
        $(document).on("click", ".delete-btn", function (e) {
            e.preventDefault();
            var ticketId = $(this).data("id");

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
                        url: "<?= base_url('/admin/ticket/delete/'); ?>" + ticketId,
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
    #ticketsTable th, #ticketsTable td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #ticketsTable th, #ticketsTable td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>