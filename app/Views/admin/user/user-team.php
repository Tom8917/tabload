<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form action="<?= isset($team) ? base_url("/admin/team/update") : base_url("/admin/team/create"); ?>" method="POST">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <?= isset($team) ? "Éditer " . esc($team['name']) : "Créer une équipe" ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nom de l'équipe</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= isset($team) ? esc($team['name']) : ""; ?>" placeholder="Nom">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description"
                       value="<?= isset($team) ? esc($team['description']) : ""; ?>" placeholder="Description">
            </div>

            <div class="mb-3">
                <label class="form-label">Utilisateurs</label>
                <div class="d-flex justify-content-between">
                    <div class="w-45">
                        <label for="available-users">Utilisateurs disponibles</label>
                        <ul id="available-users" class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($availableUsers as $user): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= esc($user['firstname'] . " " . $user['lastname']); ?>
                                    <button type="button" class="btn btn-success btn-sm add-user" data-user-id="<?= $user['id']; ?>">+</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="w-10 text-center my-auto"></div>

                    <div class="w-45">
                        <label for="assigned-users">Utilisateurs assignés</label>
                        <ul id="assigned-users" class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <?php if (isset($team)): ?>
                                <?php foreach ($team['users'] as $user): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($user['firstname'] . " " . $user['lastname']); ?>
                                        <button type="button" class="btn btn-danger btn-sm remove-user" data-user-id="<?= $user['id']; ?>">-</button>
                                        <input type="hidden" name="assigned-users[]" value="<?= $user['id']; ?>">
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catégories de tickets</label>
                <div class="d-flex justify-content-between">
                    <div class="w-45">
                        <label for="available-ticketcategories">Catégories de tickets disponibles</label>
                        <ul id="available-ticketcategories" class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($availableTicketCategories as $category): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= esc($category['type']); ?>
                                    <button type="button" class="btn btn-success btn-sm add-category" data-category-id="<?= $category['id']; ?>">+</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="w-10 text-center my-auto"></div>

                    <div class="w-45">
                        <label for="assigned-ticketcategories">Catégories assignées</label>
                        <ul id="assigned-ticketcategories" class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <?php if (isset($team)): ?>
                                <?php foreach ($team['ticketcategories'] as $category): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= esc($category['type']); ?>
                                        <button type="button" class="btn btn-danger btn-sm remove-category" data-category-id="<?= $category['id']; ?>">-</button>
                                        <input type="hidden" name="assigned-ticketcategories[]" value="<?= $category['id']; ?>">
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="card-footer text-end">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                Annuler
            </button>
            <?php if (isset($team)): ?>
                <input type="hidden" name="id" value="<?= esc($team['id']); ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><?= isset($team) ? "Sauvegarder" : "Enregistrer" ?></button>
        </div>
    </div>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Code pour la gestion des utilisateurs
        const availableUsersList = document.getElementById("available-users");
        const assignedUsersList = document.getElementById("assigned-users");

        availableUsersList.addEventListener("click", function (event) {
            if (event.target.classList.contains("add-user")) {
                let listItem = event.target.closest("li");
                moveUser(listItem, assignedUsersList, "remove-user", "btn-danger", "-", availableUsersList);
            }
        });

        assignedUsersList.addEventListener("click", function (event) {
            if (event.target.classList.contains("remove-user")) {
                let listItem = event.target.closest("li");
                moveUser(listItem, availableUsersList, "add-user", "btn-success", "+", assignedUsersList);
            }
        });

        // Fonction pour déplacer un utilisateur
        function moveUser(listItem, targetList, newClass, newBtnClass, newText, sourceList) {
            if (!listItem) return;

            if (targetList.contains(listItem)) return;

            const button = listItem.querySelector("button");
            button.classList.remove("add-user", "remove-user", "btn-success", "btn-danger");
            button.classList.add(newClass, newBtnClass);
            button.textContent = newText;

            if (newClass === "remove-user") {
                let hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "assigned-users[]";
                hiddenInput.value = button.getAttribute("data-user-id");
                listItem.appendChild(hiddenInput);
            } else {
                listItem.querySelector("input[name='assigned-users[]']").remove();
            }

            targetList.appendChild(listItem);
        }

        // Code pour la gestion des catégories de tickets
        const availableTicketCategoriesList = document.getElementById("available-ticketcategories");
        const assignedTicketCategoriesList = document.getElementById("assigned-ticketcategories");

        availableTicketCategoriesList.addEventListener("click", function (event) {
            if (event.target.classList.contains("add-category")) {
                let listItem = event.target.closest("li");
                moveCategory(listItem, assignedTicketCategoriesList, "remove-category", "btn-danger", "-", availableTicketCategoriesList);
            }
        });

        assignedTicketCategoriesList.addEventListener("click", function (event) {
            if (event.target.classList.contains("remove-category")) {
                let listItem = event.target.closest("li");
                moveCategory(listItem, availableTicketCategoriesList, "add-category", "btn-success", "+", assignedTicketCategoriesList);
            }
        });

        // Fonction pour déplacer une catégorie
        function moveCategory(listItem, targetList, newClass, newBtnClass, newText, sourceList) {
            if (!listItem) return;

            if (targetList.contains(listItem)) return;

            const button = listItem.querySelector("button");
            button.classList.remove("add-category", "remove-category", "btn-success", "btn-danger");
            button.classList.add(newClass, newBtnClass);
            button.textContent = newText;

            if (newClass === "remove-category") {
                let hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "assigned-ticketcategories[]";
                hiddenInput.value = button.getAttribute("data-category-id");
                listItem.appendChild(hiddenInput);
            } else {
                listItem.querySelector("input[name='assigned-ticketcategories[]']").remove();
            }

            targetList.appendChild(listItem);
        }
    });
</script>
