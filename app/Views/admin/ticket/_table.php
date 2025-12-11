<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des Tickets</h4>
    </div>
    <div class="card-body">

        <!-- Tickets à traiter -->
        <h5>Tickets <span style="color: red">à traiter</span></h5>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>ID ticket</th>
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
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <?php if ($ticket['status_type'] != 'Clôturé' && $ticket['status_type'] != 'En cours'): ?>
                    <tr>
                        <td><?= $ticket['id']; ?></td>
                        <td><?= $ticket['user_email']; ?></td>
                        <td><?= $ticket['ticketcategory_type']; ?></td>
                        <td><?= $ticket['priority_type']; ?></td>
                        <td><?= $ticket['status_type']; ?></td>
                        <td><?= $ticket['title']; ?></td>
                        <td><?= $ticket['description']; ?></td>
                        <td><?= $ticket['created_at']; ?></td>
                        <td><?= $ticket['updated_at']; ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/ticket/' . $ticket['id']); ?>"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/ticket/delete/' . $ticket['id']); ?>" class="text-danger delete-btn"><i class="fa-solid fa-trash"></i></a>
                        </td>
                        <td class="text-center">
                            <a class="btn" data-bs-toggle="modal" data-bs-target="#ticketModal"
                               data-ticket-id="<?= $ticket['id']; ?>" data-ticket-title="<?= $ticket['title']; ?>"
                               data-ticket-description="<?= $ticket['description']; ?>"
                               data-ticket-email="<?= $ticket['user_email']; ?>"
                               data-ticket-created="<?= $ticket['created_at']; ?>"
                               data-ticket-updated="<?= $ticket['updated_at']; ?>"
                               data-ticket-category="<?= $ticket['ticketcategory_type']; ?>"
                               data-ticket-priority="<?= $ticket['priority_type']; ?>"
                               data-ticket-status="<?= $ticket['status_type']; ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Tickets en cours -->
        <h5 class="mt-5">Tickets <span style="color: darkorange">en cours</span></h5>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>ID ticket</th>
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
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <?php if ($ticket['status_type'] == 'En cours'): ?>
                    <tr>
                        <td><?= $ticket['id']; ?></td>
                        <td><?= $ticket['user_email']; ?></td>
                        <td><?= $ticket['ticketcategory_type']; ?></td>
                        <td><?= $ticket['priority_type']; ?></td>
                        <td><?= $ticket['status_type']; ?></td>
                        <td><?= $ticket['title']; ?></td>
                        <td><?= $ticket['description']; ?></td>
                        <td><?= $ticket['created_at']; ?></td>
                        <td><?= $ticket['updated_at']; ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/ticket/' . $ticket['id']); ?>"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/ticket/delete/' . $ticket['id']); ?>" class="text-danger delete-btn"><i class="fa-solid fa-trash"></i></a>
                        </td>
                        <td class="text-center">
                            <a class="btn" data-bs-toggle="modal" data-bs-target="#ticketModal"
                               data-ticket-id="<?= $ticket['id']; ?>" data-ticket-title="<?= $ticket['title']; ?>"
                               data-ticket-description="<?= $ticket['description']; ?>"
                               data-ticket-email="<?= $ticket['user_email']; ?>"
                               data-ticket-created="<?= $ticket['created_at']; ?>"
                               data-ticket-updated="<?= $ticket['updated_at']; ?>"
                               data-ticket-category="<?= $ticket['ticketcategory_type']; ?>"
                               data-ticket-priority="<?= $ticket['priority_type']; ?>"
                               data-ticket-status="<?= $ticket['status_type']; ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Tickets Clôturés -->
        <h5 class="mt-5">Tickets <span style="color: green">clôturés</span></h5>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>ID ticket</th>
                <th>Email</th>
                <th>Catégorie</th>
                <th>Status</th>
                <th>Objet</th>
                <th>Description</th>
                <th>Créé le</th>
                <th>Mis à jour le</th>
                <th>Visualiser</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <?php if ($ticket['status_type'] == 'Clôturé'): ?>
                    <tr>
                        <td><?= $ticket['id']; ?></td>
                        <td><?= $ticket['user_email']; ?></td>
                        <td><?= $ticket['ticketcategory_type']; ?></td>
                        <td><?= $ticket['status_type']; ?></td>
                        <td><?= $ticket['title']; ?></td>
                        <td><?= $ticket['description']; ?></td>
                        <td><?= $ticket['created_at']; ?></td>
                        <td><?= $ticket['updated_at']; ?></td>
                        <td class="text-center">
                            <a class="btn" data-bs-toggle="modal" data-bs-target="#ticketModal"
                               data-ticket-id="<?= $ticket['id']; ?>" data-ticket-title="<?= $ticket['title']; ?>"
                               data-ticket-description="<?= $ticket['description']; ?>"
                               data-ticket-email="<?= $ticket['user_email']; ?>"
                               data-ticket-created="<?= $ticket['created_at']; ?>"
                               data-ticket-updated="<?= $ticket['updated_at']; ?>"
                               data-ticket-category="<?= $ticket['ticketcategory_type']; ?>"
                               data-ticket-priority="<?= $ticket['priority_type']; ?>"
                               data-ticket-status="<?= $ticket['status_type']; ?>">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel">Détails du Ticket #<span id="ticket-id"></span>
                    <br>
                    Auteur du ticket : <span id="ticket-email"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel2"><span id="ticket-id"></span>
                    Statut : <span id="ticket-status" class="text-end"></span>
                </h5>
            </div>
            <div class="modal-body text-center">
                <p><strong>Catégorie du ticket :</strong>
                    <span><h4 id="ticket-ticketcategory" style="font-weight: normal"></h4></span>
                </p>
                <br>
                <p><strong>Titre :</strong>
                    <span><h5 id="ticket-title" style="font-weight: normal"></h5></span>
                </p>
                <br>
                <p><strong>Description :</strong>
                    <span><p id="ticket-description" style="font-weight: normal"></p></span>
                </p>
                <br>

                <p><strong>Priorité :</strong> <span id="ticket-priority"></span></p>
                <p><strong>Créé le :</strong> <span id="ticket-created"></span></p>
                <p><strong>Mis à jour le :</strong> <span id="ticket-updated"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    var ticketModal = document.getElementById('ticketModal');
    ticketModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Le bouton cliqué
        var ticketId = button.getAttribute('data-ticket-id');
        var ticketTitle = button.getAttribute('data-ticket-title');
        var ticketDescription = button.getAttribute('data-ticket-description');
        var ticketEmail = button.getAttribute('data-ticket-email');
        var ticketCreated = button.getAttribute('data-ticket-created');
        var ticketUpdated = button.getAttribute('data-ticket-updated');
        var ticketCategory = button.getAttribute('data-ticket-category');
        var ticketPriority = button.getAttribute('data-ticket-priority');
        var ticketStatus = button.getAttribute('data-ticket-status');

        document.getElementById('ticket-id').textContent = ticketId;
        document.getElementById('ticket-title').textContent = ticketTitle;
        document.getElementById('ticket-description').textContent = ticketDescription;
        document.getElementById('ticket-email').textContent = ticketEmail;
        document.getElementById('ticket-created').textContent = ticketCreated;
        document.getElementById('ticket-updated').textContent = ticketUpdated;
        document.getElementById('ticket-ticketcategory').textContent = ticketCategory;
        document.getElementById('ticket-priority').textContent = ticketPriority;
        document.getElementById('ticket-status').textContent = ticketStatus;
    });

    $(document).on("click", ".delete-btn", function (e) {
        e.preventDefault();

        var ticketId = $(this).data("id");  // Récupère l'ID du ticket

        // Affiche la SweetAlert pour demander la confirmation
        Swal.fire({
            title: "Êtes-vous sûr ?",
            text: "Cette action est irréversible !",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",  // Couleur du bouton "Oui"
            cancelButtonColor: "#3085d6",  // Couleur du bouton "Annuler"
            confirmButtonText: "Oui, supprimer !",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                // Utilisation de AJAX pour éviter un rechargement de page
                $.ajax({
                    url: "<?= base_url('/admin/ticket/delete/'); ?>" + ticketId,
                    type: "GET",
                    success: function(response) {
                        // Si la suppression réussit, on affiche une alerte de succès
                        Swal.fire(
                            'Supprimé !',
                            'Le ticket a été supprimé avec succès.',
                            'success'
                        ).then(function() {
                            // Après la confirmation de succès, on recharge la page
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        // Si la suppression échoue, on affiche un message d'erreur
                        Swal.fire(
                            'Erreur !',
                            'Une erreur est survenue lors de la suppression du ticket.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
