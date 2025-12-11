<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Équipes</mark>
        </h4>
        <a href="<?= base_url('/admin/team/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableTeams" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Utilisateurs</th>
                    <th>Catégories</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($teams) && !empty($teams)): ?>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?= $team['id']; ?></td>
                            <td><?= $team['name']; ?></td>
                            <td><?= $team['description']; ?></td>
                            <td>
                                <?php if (!empty($team['users'])): ?>
                                    <ul>
                                        <?php foreach ($team['users'] as $user): ?>
                                            <li><?= esc($user['firstname']) ?> <?= esc($user['lastname']) ?>
                                                (<?= esc($user['email']) ?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    Aucun utilisateur
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($team['ticketcategories'])): ?>
                                    <ul>
                                        <?php foreach ($team['ticketcategories'] as $category): ?>
                                            <li><?= esc($category['type']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    Aucune catégorie de ticket
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('admin/team/' . $team['id']); ?>"><i
                                            class="fa-solid fa-pencil"></i></a>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('admin/team/delete/' . $team['id']); ?>"
                                   class="text-danger delete-btn"
                                   data-id="<?= $team['id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Aucune équipe disponible.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-btn").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                const teamId = this.getAttribute("data-id");
                const deleteUrl = this.getAttribute("href");

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
                        fetch(deleteUrl, {
                            method: "GET"
                        })
                            .then(response => {
                                if (response.redirected) {
                                    // Affiche le message de succès avec timer
                                    Swal.fire({
                                        title: "Supprimé !",
                                        text: "L'équipe a été supprimée avec succès.",
                                        icon: "success",
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });

                                    // Redirige après 2 seconde
                                    setTimeout(() => {
                                        window.location.href = response.url;
                                    }, 2000);
                                }
                            })
                            .catch(() => {
                                Swal.fire("Erreur !", "Impossible de supprimer l'équipe.", "error");
                            });
                    }
                });
            });
        });
    });
</script>

<style>
    #tableTeams th, #tableTeams td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableTeams th, #tableTeams td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>