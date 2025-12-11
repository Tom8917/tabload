<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Tickets</mark>
        </h4>
        <a href="<?= base_url('/ticket/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <?php if (empty($tickets)): ?>
            <div class="text-center">
                <p class="text-center text-muted mt-4 mb-4">Aucun ticket ici.</p>
                <a href="<?= base_url('/ticket/new'); ?>" style="text-decoration: none">Ouvrir un Ticket <i class="fa-solid fa-plus"></i></a>
            </div>
        <?php else: ?>
            <!-- Tableau des tickets ouverts -->
            <h5>Tickets ouverts</h5>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Utilisateur</th>
                    <th>Catégorie</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <?php if ($ticket['status_type'] != 'Clôturé'): ?>
                        <tr>
                            <td><?= $ticket['id']; ?></td>
                            <td><?= $ticket['user_email']; ?></td>
                            <td><?= $ticket['ticketcategory_type']; ?></td>
                            <td><?= $ticket['title']; ?></td>
                            <td class="description"><?= $ticket['description']; ?></td>
                            <td><?= $ticket['status_type']; ?></td>
                            <td>
                                <a href="<?= base_url('/ticket/' . $ticket['id']) ?>"><i class="fa-solid fa-pencil"></i></a>
                            </td>
                            <td>
                                <a href="<?= base_url('/ticket/delete/' . $ticket['id']); ?>" class="text-danger delete-btn" data-id="<?= $ticket['id'] ?>"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Tableau des tickets clôturés -->
            <h5 class="mt-5">Tickets clôturés</h5>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Utilisateur</th>
                    <th>Catégorie</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Commentaire d'un Administrateur</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <?php if ($ticket['status_type'] == 'Clôturé'): ?>
                        <tr>
                            <td><?= $ticket['id']; ?></td>
                            <td><?= $ticket['user_email']; ?></td>
                            <td><?= $ticket['ticketcategory_type']; ?></td>
                            <td><?= $ticket['title']; ?></td>
                            <td class="description"><?= $ticket['description']; ?></td>
                            <td class="comment"><?= $ticket['comment']; ?></td>
                            <td><?= $ticket['status_type']; ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
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
                    url: "<?= base_url('/ticket/delete/'); ?>" + ticketId,
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

<style>
    td.description, td.comment {
        max-width: 250px;      /* limite la largeur max */
        white-space: normal;   /* autorise le retour à la ligne */
        word-wrap: break-word; /* coupe les mots trop longs */
        overflow-wrap: break-word; /* compatibilité */
    }
</style>