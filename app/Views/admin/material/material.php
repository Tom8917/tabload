<div class="row">
    <div class="col">
        <form action="<?= isset($material) ? base_url("/admin/material/update") : base_url("/admin/material/create") ?>"
              method="POST">
            <div class="card">
                <div class="card-header mb-2">
                    <h4 class="card-title">
                        <?= isset($material) ? "Modifier " . $material['badge'] : "Créer un Matériel" ?>
                    </h4>
                </div>

                <ul class="nav nav-tabs" id="materialTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="type-tab" data-bs-toggle="tab" data-bs-target="#type"
                                type="button" role="tab" aria-controls="type" aria-selected="true">Type et Marque
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reference-tab" data-bs-toggle="tab" data-bs-target="#reference"
                                type="button" role="tab" aria-controls="reference" aria-selected="false">Référence et
                            Numéro
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="operational_system-tab" data-bs-toggle="tab"
                                data-bs-target="#operational_system"
                                type="button" role="tab" aria-controls="operational_system" aria-selected="false">OS et
                            Vulnérabilité
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attribution-tab" data-bs-toggle="tab" data-bs-target="#attribution"
                                type="button" role="tab" aria-controls="attribution" aria-selected="false">Centre et
                            Attribution
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="warranty-tab" data-bs-toggle="tab" data-bs-target="#warranty"
                                type="button" role="tab" aria-controls="warranty" aria-selected="false">Garantie
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3 mt-1">
                    <!-- Onglet Type et Marque -->
                    <div class="tab-pane fade show active" id="type" role="tabpanel" aria-labelledby="type-tab">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type*</label>
                            <select class="form-control" id="id_materialtype" name="id_materialtype" required>
                                <option value="">Sélectionnez un type</option>
                                <?php foreach ($materialtypes as $materialtype): ?>
                                    <option value="<?= $materialtype['id']; ?>" <?= (isset($material) && $materialtype['id'] == $material['id_materialtype']) ? "selected" : ""; ?>>
                                        <?= $materialtype['type']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="marque" class="form-label">Marque*</label>
                            <select class="form-control" id="id_materialbrand" name="id_materialbrand" required>
                                <option value="">Sélectionnez une marque</option>
                                <?php foreach ($materialbrands as $materialbrand): ?>
                                    <option value="<?= $materialbrand['id']; ?>" <?= (isset($material) && $materialbrand['id'] == $material['id_materialbrand']) ? "selected" : ""; ?>>
                                        <?= $materialbrand['marque']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Onglet Référence et Numéro -->
                    <div class="tab-pane fade" id="reference" role="tabpanel" aria-labelledby="reference-tab">
                        <div class="mb-3">
                            <label for="reference" class="form-label">Référence*</label>
                            <input type="text" class="form-control" id="reference" placeholder="#Référence"
                                   value="<?= isset($material) ? $material['reference'] : ""; ?>" name="reference"
                                   required>
                        </div>
                        <div class="mb-4">
                            <label for="nserie" class="form-label">Numéro de Série</label>
                            <input type="text" class="form-control" id="nserie" placeholder="Numéro de Série"
                                   value="<?= isset($material) ? $material['nserie'] : ""; ?>" name="nserie">
                        </div>
                        <div class="row">
                            <!-- Bouton Switch Oui/Non -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Activer le badge</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableBadge" checked>
                                    <label class="form-check-label" for="enableBadge">Oui</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Sélecteur de centres -->
                            <div class="col-md-3 mb-3">
                                <label for="center" class="form-label">Centre</label>
                                <select class="form-control" id="center" name="center">
                                    <option value="" disabled selected>Sélectionner un centre</option>
                                    <?php foreach ($centers as $center): ?>
                                        <option value="<?= $center['diminutif']; ?>" <?= (isset($material) && $center['id'] == $material['id_center']) ? "selected" : ""; ?>>
                                            <?= $center['diminutif']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sélecteur de métier des utilisateurs -->
                            <div class="col-md-3 mb-3">
                                <label for="id_job" class="form-label">Métier</label>
                                <select class="form-control" name="id_job" id="id_job">
                                    <option value="">Sélectionner un métier</option>
                                    <?php foreach ($jobs as $job) : ?>
                                        <option value="<?= $job['id']; ?>" <?= isset($material) && $material['id_job'] == $job['id'] ? 'selected' : ''; ?>>
                                            <?= $job['diminutif']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sélecteur de numéros de 01 à 10 -->
                            <div class="col-md-3 mb-3">
                                <label for="number" class="form-label">Numéro</label>
                                <select class="form-control" id="number" name="number">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= sprintf('%02d', $i); ?>" <?= (isset($material) && $material['badge'] && strpos($material['badge'], sprintf('%02d', $i)) !== false) ? 'selected' : ''; ?>>
                                            <?= sprintf('%02d', $i); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Résultat combiné -->
                            <div class="col-md-3 mb-3">
                                <label for="badge" class="form-label">Badge</label>
                                <input type="text" class="form-control" id="badge" placeholder="Résultat combiné"
                                       name="badge" readonly>
                            </div>
                        </div>

                        <script>
                            const fixedCenter = "APAS";
                            const enableBadgeSwitch = document.getElementById('enableBadge');
                            const centerSelect = document.getElementById('center');
                            const jobSelect = document.getElementById('id_job');
                            const numberSelect = document.getElementById('number');
                            const badgeInput = document.getElementById('badge');

                            function updateBadge() {
                                if (!enableBadgeSwitch.checked) {
                                    badgeInput.value = ""; // Badge null
                                    return;
                                }

                                const center = centerSelect.value || 'XXX';
                                const jobId = jobSelect.value || 'XXX';
                                const number = numberSelect.value || '00';

                                const selectedJobOption = Array.from(jobSelect.options).find(option => option.value === jobId);
                                const jobDiminutif = selectedJobOption ? selectedJobOption.text : 'XXX';

                                badgeInput.value = `${fixedCenter}-${center}-${jobDiminutif}${number}`;
                            }

                            function toggleFields() {
                                const isEnabled = enableBadgeSwitch.checked;

                                [centerSelect, jobSelect, numberSelect].forEach(field => {
                                    field.disabled = !isEnabled;
                                    field.classList.toggle("text-muted", !isEnabled);
                                    if (!isEnabled) field.value = ""; // Remettre les valeurs à null si désactivé
                                });

                                updateBadge();
                            }

                            enableBadgeSwitch.addEventListener('change', toggleFields);
                            centerSelect.addEventListener('change', updateBadge);
                            jobSelect.addEventListener('change', updateBadge);
                            numberSelect.addEventListener('change', updateBadge);

                            document.addEventListener('DOMContentLoaded', () => {
                                toggleFields(); // Initialisation de l'état au chargement
                            });
                        </script>
                    </div>


                <!--OS et Vulnérabilité-->
                <div class="tab-pane fade" id="operational_system" role="tabpanel"
                     aria-labelledby="operational_system-tab">
                    <div class="mb-3">
                        <label for="operational_system" class="form-label">Système d'exploitation</label>
                        <select class="form-control" id="id_material_operational_system"
                                name="id_material_operational_system">
                            <option value="">Sélectionnez un OS</option>
                            <?php foreach ($materialoperationalsystems as $materialoperationalsystem): ?>
                                <option value="<?= $materialoperationalsystem['id']; ?>" <?= (isset($material) && $materialoperationalsystem['id'] == $material['id_material_operational_system']) ? "selected" : ""; ?>>
                                    <?= $materialoperationalsystem['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="material_vulnerability" class="form-label">Vulnérabilité</label>
                        <select class="form-control" id="id_material_vulnerability" name="id_material_vulnerability">
                            <option value="">Sélectionnez la vulnérabilité</option>
                            <?php foreach ($materialvulnerabilitys as $materialvulnerability): ?>
                                <option value="<?= $materialvulnerability['id']; ?>" <?= (isset($material) && $materialvulnerability['id'] == $material['id_material_vulnerability']) ? "selected" : ""; ?>>
                                    <?= $materialvulnerability['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>


                <!-- Onglet Centre et Attribution -->
                <div class="tab-pane fade" id="attribution" role="tabpanel" aria-labelledby="attribution-tab">
                    <div class="mb-3">
                        <label for="id_center" class="form-label">Centre*</label>
                        <select class="form-select" id="id_center" name="id_center" required>
                            <option value="" disabled <?= !isset($material) ? "selected" : ""; ?>>Sélectionner un centre</option>
                            <?php foreach ($centers as $center): ?>
                                <option value="<?= $center['id']; ?>" <?= (isset($material) && $center['id'] == $material['id_center']) ? "selected" : ""; ?>>
                                    <?= $center['ville']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_user" class="form-label">Utilisateur attribué*</label>
                        <select class="form-select" id="id_user" name="id_user" required>
                            <option value="" disabled <?= !isset($material) ? "selected" : ""; ?>>Sélectionner un
                                utilisateur
                            </option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id']; ?>" <?= (isset($material) && $user['id'] == $material['id_user']) ? "selected" : ""; ?>>
                                    <?= $user['email']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!--Garantie-->
                <div class="tab-pane fade" id="warranty" role="tabpanel" aria-labelledby="warranty-tab">
                    <div class="mb-3">
                        <label for="start_warranty" class="form-label">Date de début de garantie</label>
                        <input type="date" class="form-control" id="start_warranty" name="start_warranty"
                               value="<?= isset($material) ? $material['start_warranty'] : ''; ?>"
                               onchange="updateEndWarranty()" required>
                    </div>

                    <div class="mb-3">
                        <label for="id_warranty" class="form-label">Durée de la garantie</label>
                        <select class="form-select" id="id_warranty" name="id_warranty" onchange="updateEndWarranty()"
                                required>
                            <?php foreach ($warrantys as $warranty): ?>
                                <option value="<?= $warranty['time_warranty']; ?>" <?= (isset($material) && $warranty['time_warranty'] == $material['id_warranty']) ? "selected" : ""; ?>>
                                    <?= $warranty['time_warranty']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Champ caché pour conserver la valeur actuelle de time_warranty -->
                        <input type="hidden" name="hidden_time_warranty"
                               value="<?= isset($material) ? $material['id_warranty'] : ''; ?>">
                    </div>

                    <!-- Case 5: Date de fin de garantie -->
                    <div class="col-md-3 mb-3">
                        <label for="end_warranty" class="form-label">Date de fin de garantie</label>
                        <input type="text" class="form-control" id="end_warranty" placeholder="Date de fin de garantie"
                               name="end_warranty" value="<?= isset($material) ? $material['end_warranty'] : ''; ?>"
                               readonly>
                    </div>

                    <!-- Champ caché pour envoyer la date de fin de garantie calculée -->
                    <input type="hidden" id="hidden_end_warranty" name="hidden_end_warranty"
                           value="<?= isset($material) ? $material['end_warranty'] : ''; ?>">
                </div>

                <script>
                    function updateEndWarranty() {
                        const startWarranty = document.getElementById('start_warranty').value;
                        const warrantyDuration = document.getElementById('id_warranty').value;

                        if (warrantyDuration === "1" || warrantyDuration === "Non renseigné") {
                            document.getElementById('end_warranty').value = 'Non renseigné';
                            document.getElementById('hidden_end_warranty').value = 'Non renseigné';
                            return;
                        }

                        // Cas où la garantie est renseignée
                        if (startWarranty && warrantyDuration && warrantyDuration !== "0") {
                            // Convertir warrantyDuration en nombre entier
                            const warrantyDurationNum = parseInt(warrantyDuration, 10);

                            // Vérifier si la conversion a échoué
                            if (isNaN(warrantyDurationNum)) {
                                console.error("La durée de la garantie n'est pas un nombre valide");
                                return;
                            }

                            const startDate = new Date(startWarranty);
                            if (isNaN(startDate)) {
                                console.error("Invalid start date");
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

                    $(document).ready(function () {
                        $('#id_materialtype').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Rechercher un utilisateur',
                            allowClear: true
                        });
                        $('#id_materialbrand').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Rechercher un utilisateur',
                            allowClear: true
                        });
                        $('#id_user').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Rechercher un utilisateur',
                            allowClear: true
                        });
                        $('#id_center').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Rechercher un centre',
                            allowClear: true
                        });
                    });
                </script>
            </div>

            <div class="card-footer text-end mt-2">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    Annuler
                </button>
                <?php if (isset($material)): ?>
                    <input type="hidden" name="id" value="<?= $material['id']; ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <?= isset($material) ? "Mettre à jour" : "Enregistrer" ?>
                </button>
            </div>
    </div>
    </form>
</div>
</div>
