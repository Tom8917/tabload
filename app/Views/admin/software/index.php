<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Licences & Logiciels</mark>
        </h4>
        <a href="<?= base_url('/admin/software/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableSoftwares" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Mot de passe</th>
                    <th>Clé</th>
                    <th>Garantie</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                    <th>Voir</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($softwares) && !empty($softwares)): ?>
                    <?php foreach ($softwares as $software): ?>
                        <tr>
                            <td><?= $software['id']; ?></td>
                            <td><?= esc($software['name']); ?></td>
                            <td class="password-cell" data-private="true"
                                data-real="<?= esc($software['password']); ?>">
                                <?= str_repeat('*', strlen($software['password'])); ?>
                            </td>
                            <td class="key-cell" data-private="true" data-real="<?= esc($software['key']); ?>">
                                <?= str_repeat('*', strlen($software['key'])); ?>
                            </td>
                            <td id="end-warranty-<?= $software['id']; ?>"><?= esc($software['end_warranty']); ?></td>
                            <td>
                                <a href="<?= base_url('admin/software/' . $software['id']); ?>" class="text-primary">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/software/delete/' . $software['id']); ?>"
                                   class="text-danger delete-btn" data-id="<?= $software['id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-primary view-password-btn" data-page="software"
                                        data-id="<?= $software['id']; ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Aucun software disponible.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Suppression
        document.querySelectorAll(".delete-btn").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
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
                        window.location.href = deleteUrl;
                    }
                });
            });
        });

        // Affichage des garanties
        document.querySelectorAll("td[id^='end-warranty-']").forEach(function (cell) {
            const endWarranty = cell.textContent.trim();
            const currentDate = new Date();
            const warrantyDate = new Date(endWarranty);

            if (!endWarranty || endWarranty === "Non renseignée" || isNaN(warrantyDate)) {
                cell.innerHTML = `<span class="badge bg-secondary">Non renseignée</span>`;
            } else {
                const formattedDate = warrantyDate.toLocaleDateString('fr-FR');
                if (warrantyDate < currentDate) {
                    cell.innerHTML = `<span class="badge bg-danger">Expirée (${formattedDate})</span>`;
                } else {
                    cell.innerHTML = `<span class="badge bg-success">Valide (${formattedDate})</span>`;
                }
            }
        });

        // Affichage sécurisé des mots de passe et clés
        document.querySelectorAll(".view-password-btn").forEach(function (button) {
            button.addEventListener("click", function () {
                const pageSlug = this.getAttribute("data-page");
                const softwareId = this.getAttribute("data-id");

                Swal.fire({
                    title: "Mot de passe requis",
                    input: "password",
                    inputLabel: "Entrez le mot de passe pour afficher les infos",
                    showCancelButton: true,
                    confirmButtonText: "Valider"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const password = result.value;

                        // Assure-toi que base_url est correctement généré côté serveur PHP
                        const checkPasswordUrl = "<?= base_url('admin/configpassword/checkPassword'); ?>";

                        fetch(`${checkPasswordUrl}?slug=${pageSlug}&password=${encodeURIComponent(password)}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`Erreur HTTP : ${response.status}`);
                                }
                                return response.json();  // Si la réponse est OK, on tente de la convertir en JSON
                            })
                            .then(data => {
                                console.log(data); // Ajouter un log pour voir la structure de la réponse
                                if (data.success) {
                                    // Révéler les données dans la ligne correspondante
                                    const row = button.closest("tr");
                                    row.querySelectorAll("[data-private='true']").forEach(function (cell) {
                                        cell.textContent = cell.getAttribute("data-real");
                                    });
                                    Swal.fire("Accès autorisé", "Les informations sont maintenant visibles.", "success");
                                } else {
                                    Swal.fire("Erreur", data.message, "error");
                                }
                            })
                            .catch(error => {
                                console.error("Erreur :", error);
                                Swal.fire("Erreur", "Impossible de vérifier le mot de passe.", "error");
                            });
                    }
                });
            });
        });
    });
</script>

<style>
    #tableSoftwares th, #tableSoftwares td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableSoftwares th, #tableSoftwares td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>