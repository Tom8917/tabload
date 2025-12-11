<div class="row">
    <div class="col">
        <form action="<?= isset($software) ? base_url("/admin/software/update") : base_url("/admin/software/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($software) ? "Editer " . $software['name'] : "Créer un Software" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du Logiciel</label>
                        <input type="text" class="form-control" id="name" placeholder="Nom" value="<?= isset($software) ? $software['name'] : ""; ?>" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" placeholder="Mot de passe" value="<?= isset($software) ? $software['password'] : ""; ?>" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="key" class="form-label">Clé de Licence</label>
                        <input type="text" class="form-control" id="key" placeholder="Clé" value="<?= isset($software) ? $software['key'] : ""; ?>" name="key">
                    </div>

                    <!--Garantie-->
                        <div class="mb-3">
                            <label for="start_warranty" class="form-label">Date de début de garantie</label>
                            <input type="date" class="form-control" id="start_warranty" name="start_warranty"
                                   value="<?= isset($software) ? $software['start_warranty'] : ''; ?>"
                                   onchange="updateEndWarranty()">
                        </div>

                    <div class="mb-3">
                        <label for="id_warranty" class="form-label">Durée de la garantie</label>
                        <select class="form-select" id="id_warranty" name="id_warranty" onchange="updateEndWarranty()">
                            <?php foreach ($warrantys as $warranty): ?>
                                <!-- Comparez l'ID de la garantie (pas la durée) pour savoir si c'est celui qui doit être sélectionné -->
                                <option value="<?= $warranty['id']; ?>" <?= (isset($software) && $warranty['id'] == $software['id_warranty']) ? "selected" : ""; ?>>
                                    <?= $warranty['time_warranty']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Case 5: Date de fin de garantie -->
                        <div class="col-md-3 mb-3">
                            <label for="end_warranty" class="form-label">Date de fin de garantie</label>
                            <input type="text" class="form-control" id="end_warranty" placeholder="Date de fin de garantie"
                                   name="end_warranty" value="<?= isset($software) ? $software['end_warranty'] : ''; ?>"
                                   readonly>
                        </div>

                        <!-- Champ caché pour envoyer la date de fin de garantie calculée -->
                        <input type="hidden" id="hidden_end_warranty" name="hidden_end_warranty"
                               value="<?= isset($software) ? $software['end_warranty'] : ''; ?>">
                </div>
                <script>
                    function updateEndWarranty() {
                        const startWarranty = document.getElementById('start_warranty').value;
                        let warrantyDuration = document.getElementById('id_warranty').value;

                        // Si la durée de la garantie est "Non renseigné" ou "1", ne pas calculer la date de fin
                        if (warrantyDuration === "0" || warrantyDuration === "Non renseigné") {
                            document.getElementById('end_warranty').value = 'Non renseigné';
                            document.getElementById('hidden_end_warranty').value = 'Non renseigné';
                            return;
                        }

                        // L'ID de la garantie correspond à la durée de la garantie + 1
                        // Si warrantyDuration est 5, l'ID sera 6, etc.
                        warrantyDuration = parseInt(warrantyDuration, 10) - 1;

                        // Cas où la garantie est renseignée avec une durée valide
                        if (startWarranty && warrantyDuration) {
                            // Convertir warrantyDuration en nombre entier
                            const warrantyDurationNum = parseInt(warrantyDuration, 10);

                            // Vérifier si la conversion a échoué
                            if (isNaN(warrantyDurationNum)) {
                                console.error("La durée de la garantie n'est pas un nombre valide");
                                return;
                            }

                            const startDate = new Date(startWarranty);
                            if (isNaN(startDate)) {
                                console.error("Date de début invalide");
                                return;
                            }

                            const endDate = new Date(startDate);
                            endDate.setFullYear(endDate.getFullYear() + warrantyDurationNum);

                            // Formater la date de fin
                            const day = String(endDate.getDate()).padStart(2, '0');
                            const month = String(endDate.getMonth() + 1).padStart(2, '0');
                            const year = endDate.getFullYear();
                            const formattedEndDate = `${day}/${month}/${year}`;

                            // Mettre à jour le champ visible "end_warranty"
                            document.getElementById('end_warranty').value = formattedEndDate;

                            // Convertir pour le champ caché (pour la BDD)
                            document.getElementById('hidden_end_warranty').value = endDate.toISOString().split('T')[0];
                        } else {
                            // Si startWarranty ou warrantyDuration sont vides, effacer les valeurs
                            document.getElementById('end_warranty').value = '';
                            document.getElementById('hidden_end_warranty').value = '';
                        }
                    }
                </script>

                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($software)): ?>
                        <input type="hidden" name="id" value="<?= $software['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($software) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
