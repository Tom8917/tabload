<div class="row">
    <div class="col">
        <form action="<?= isset($materialmedical) ? base_url("/admin/materialmedical/update") : base_url("/admin/materialmedical/create") ?>"
              method="POST">
            <div class="card">
                <div class="card-header mb-2">
                    <h4 class="card-title">
                        <?= isset($materialmedical) ? "Modifier " . $materialmedical['id'] : "Créer un Matériel Médical" ?>
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
                        <button class="nav-link" id="vulnerability-tab" data-bs-toggle="tab"
                                data-bs-target="#vulnerability"
                                type="button" role="tab" aria-controls="vulnerability" aria-selected="false">
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
                            <select class="form-control" id="id_materialmedicaltype" name="id_materialmedicaltype" required>
                                <option value="">Sélectionnez un type</option>
                                <?php foreach ($materialmedicaltypes as $materialmedicaltype): ?>
                                    <option value="<?= $materialmedicaltype['id']; ?>" <?= (isset($materialmedical) && $materialmedicaltype['id'] == $materialmedical['id_materialmedicaltype']) ? "selected" : ""; ?>>
                                        <?= $materialmedicaltype['type']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="marque" class="form-label">Marque*</label>
                            <select class="form-control" id="id_materialmedicalbrand" name="id_materialmedicalbrand" required>
                                <option value="">Sélectionnez une marque</option>
                                <?php foreach ($materialmedicalbrands as $materialmedicalbrand): ?>
                                    <option value="<?= $materialmedicalbrand['id']; ?>" <?= (isset($materialmedical) && $materialmedicalbrand['id'] == $materialmedical['id_materialmedicalbrand']) ? "selected" : ""; ?>>
                                        <?= $materialmedicalbrand['marque']; ?>
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
                                   value="<?= isset($materialmedical) ? $materialmedical['reference'] : ""; ?>"
                                   name="reference"
                                   required>
                        </div>
                        <div class="mb-4">
                            <label for="nserie" class="form-label">Numéro de Série</label>
                            <input type="text" class="form-control" id="nserie" placeholder="Numéro de Série"
                                   value="<?= isset($materialmedical) ? $materialmedical['nserie'] : ""; ?>"
                                   name="nserie">
                        </div>
                    </div>

                    <!--Vulnérabilité-->
                    <div class="tab-pane fade" id="vulnerability" role="tabpanel"
                         aria-labelledby="vulnerability-tab">
                        <div class="mb-3">
                            <label for="material_vulnerability" class="form-label">Vulnérabilité</label>
                            <select class="form-control" id="id_material_vulnerability"
                                    name="id_material_vulnerability">
                                <option value="">Sélectionnez la vulnérabilité</option>
                                <?php foreach ($materialvulnerabilitys as $materialvulnerability): ?>
                                    <option value="<?= $materialvulnerability['id']; ?>" <?= (isset($materialmedical) && $materialvulnerability['id'] == $materialmedical['id_material_vulnerability']) ? "selected" : ""; ?>>
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
                                <option value="" disabled <?= !isset($materialmedical) ? "selected" : ""; ?>>
                                    Sélectionner un
                                    centre
                                </option>
                                <?php foreach ($centers as $center): ?>
                                    <option value="<?= $center['id']; ?>" <?= (isset($materialmedical) && $center['id'] == $materialmedical['id_center']) ? "selected" : ""; ?>>
                                        <?= $center['ville']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_user" class="form-label">Utilisateur attribué*</label>
                            <select class="form-select" id="id_user" name="id_user" required>
                                <option value="" disabled <?= !isset($materialmedical) ? "selected" : ""; ?>>
                                    Sélectionner un
                                    utilisateur
                                </option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id']; ?>" <?= (isset($materialmedical) && $user['id'] == $materialmedical['id_user']) ? "selected" : ""; ?>>
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
                                   value="<?= isset($materialmedical) ? $materialmedical['start_warranty'] : ''; ?>"
                                   onchange="updateEndWarranty()" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_warranty" class="form-label">Durée de la garantie</label>
                            <select class="form-select" id="id_warranty" name="id_warranty"
                                    onchange="updateEndWarranty()"
                                    required>
                                <?php foreach ($warrantys as $warranty): ?>
                                    <option value="<?= $warranty['time_warranty']; ?>" <?= (isset($materialmedical) && $warranty['time_warranty'] == $materialmedical['id_warranty']) ? "selected" : ""; ?>>
                                        <?= $warranty['time_warranty']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Champ caché pour conserver la valeur actuelle de time_warranty -->
                            <input type="hidden" name="hidden_time_warranty"
                                   value="<?= isset($materialmedical) ? $materialmedical['id_warranty'] : ''; ?>">
                        </div>

                        <!-- Case 5: Date de fin de garantie -->
                        <div class="col-md-3 mb-3">
                            <label for="end_warranty" class="form-label">Date de fin de garantie</label>
                            <input type="text" class="form-control" id="end_warranty"
                                   placeholder="Date de fin de garantie"
                                   name="end_warranty"
                                   value="<?= isset($materialmedical) ? $materialmedical['end_warranty'] : ''; ?>"
                                   readonly>
                        </div>

                        <!-- Champ caché pour envoyer la date de fin de garantie calculée -->
                        <input type="hidden" id="hidden_end_warranty" name="hidden_end_warranty"
                               value="<?= isset($materialmedical) ? $materialmedical['end_warranty'] : ''; ?>">
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
                    <?php if (isset($materialmedical)): ?>
                        <input type="hidden" name="id" value="<?= $materialmedical['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialmedical) ? "Mettre à jour" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
