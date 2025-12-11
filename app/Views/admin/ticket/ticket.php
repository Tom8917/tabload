<div class="row justify-content-center">
    <?php if (isset($isTeamMember) && $isTeamMember): ?>
        <!-- Colonne gauche : ajout sous-tâche -->
        <div class="col-md-3">
            <form id="subtaskForm">
                <div class="card">
                    <div class="card-header">
                        <h5>Ajouter une sous-tâche</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id_ticket" value="<?= $ticket['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assigner à</label>
                            <select name="id_user" class="form-select" required>
                                <option value="" disabled selected>Choisir un utilisateur</option>
                                <?php foreach ($teamMembers as $member): ?>
                                    <option value="<?= $member['id'] ?>"><?= esc($member['email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </div>
            </form>
            <div id="subtaskAlert" class="mt-2"></div>
        </div>
    <?php endif; ?>

    <!-- Colonne droite : formulaire principal + sous-tâches -->
    <div class="col-md-9">
        <form action="<?= isset($ticket) ? base_url("/admin/ticket/update/" . $ticket['id']) : base_url("/admin/ticket/create") ?>"
              method="POST">
            <div class="card">
                <div class="card-header">
                    <h4><?= isset($ticket) ? "Éditer le Ticket #{$ticket['id']}" : "Créer un Ticket" ?></h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="id_status" class="form-select">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status['id'] ?>" <?= (isset($ticket) && $ticket['id_status'] == $status['id']) ? 'selected' : '' ?>>
                                    <?= esc($status['type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <input type="text" name="comment" class="form-control"
                               value="<?= esc($ticket['comment'] ?? '') ?>">
                    </div>
                </div>
                <div class="card-footer text-end">
                    <input type="hidden" name="id" value="<?= $ticket['id'] ?? '' ?>">
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </div>
        </form>

        <!-- Sous-tâches -->
        <h5 class="mt-4">Sous-tâches</h5>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>État</th>
                    <th>Assigné</th>
                    <th>Action</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody id="subtaskList">
                <?php foreach ($subtasks as $subtask): ?>
                    <tr>
                        <td><?= esc($subtask['title']) ?></td>
                        <td><?= esc($subtask['description']) ?></td>
                        <td><span class="badge bg-secondary"><?= esc($subtask['status']) ?></span></td>
                        <td>
                            <?php $user = model('UserModel')->find($subtask['id_user']);
                            echo $user ? esc($user['email']) : '<em>Non assigné</em>'; ?>
                        </td>
                        <td>
                            <?php if ($subtask['id_user'] == $currentUserId): ?>
                                <select data-subtask-id="<?= $subtask['id'] ?>" class="form-select form-select-sm subtask-status-select">
                                        <option value="A faire" <?= $subtask['status'] === 'A faire' ? 'selected' : '' ?>>A faire</option>
                                        <option value="En cours" <?= $subtask['status'] === 'En cours' ? 'selected' : '' ?>>En cours</option>
                                        <option value="Terminé" <?= $subtask['status'] === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
                                    </select>
                            <?php else: ?>

                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $subtask['id'] ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($subtasks)): ?>
                    <tr><td colspan="5" class="text-center">Aucune sous-tâche enregistrée.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.subtask-status-select').forEach(select => {
        select.addEventListener('change', function () {
            const subtaskId = this.getAttribute('data-subtask-id');
            const newStatus = this.value;

            fetch(`<?= base_url('/admin/subtask/update') ?>/${subtaskId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `status=${encodeURIComponent(newStatus)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('Statut mis à jour.', 'success');
                    } else {
                        showToast(data.error || 'Erreur lors de la mise à jour.', 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Erreur réseau.', 'danger');
                });
        });
    });

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed top-0 end-0 m-3 show`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    $(document).on("click", ".delete-btn", function (e) {
        e.preventDefault();
        var subtaskId = $(this).data("id");

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
                    url: "<?= base_url('/admin/subtask/delete/'); ?>" + subtaskId,
                    type: "DELETE",
                    success: function (response) {
                        Swal.fire("Supprimé !", "La sous-tâche a été supprimé avec succès.", "success");
                        table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Erreur !", "Impossible de supprimer la sous-tâche.", "error");
                    }
                });
            }
        });
    });
</script>
