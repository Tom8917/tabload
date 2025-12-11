<div class="row">
    <div class="col">
        <form action="<?= isset($token) ? base_url("/admin/token/update") : base_url("/admin/token/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($token) ? "Éditer le token de l'utilisateur #" . $token['id_user'] : "Créer un Token" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="id_user" class="form-label">Utilisateur</label>
                        <input type="text" class="form-control" id="id_user" placeholder="Utilisateur"
                               value="<?= isset($token) ? $token['id_user'] : ""; ?>" name="id_user" <?= isset($token) ? 'readonly' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label for="token" class="form-label">Token</label>
                        <input class="form-control" id="token" placeholder="Token" name="token"
                               value="<?= isset($token) ? $token['token'] : ""; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="counter" class="form-label">Compteur</label>
                        <div class="d-flex align-items-center">
                            <input class="form-control me-2" id="counter" name="counter"
                                   value="<?= isset($token) ? $token['counter'] : ""; ?>">
                            <?php if (isset($token)): ?>
                                <button type="button" id="btnSetApiLimit" class="btn btn-outline-primary">Limiter</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Annuler</button>
                    <?php if (isset($token)): ?>
                        <input type="hidden" name="id" value="<?= $token['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($token) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('btnSetApiLimit');
        const tokenIdField = document.querySelector('input[name="id"]'); // récupère dynamiquement l’ID du token actuel
        const counterInput = document.getElementById('counter');

        if (!button || !tokenIdField || !counterInput) return;

        const currentTokenId = parseInt(tokenIdField.value); // ← récupéré depuis le champ caché du formulaire

        // Crée la modale pour définir la limite de l'API
        const modal = document.createElement('div');
        modal.id = 'apiLimitModal';
        modal.style.display = 'none';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.zIndex = '9999';

        modal.innerHTML = `
        <div class="card" style="padding: 20px; border: 2px; border-radius: 10px; max-width: 300px; width: 100%; text-align: center;">
            <h3>Définir la limite API</h3>
            <select id="apiLimitSelect" class="form-select mb-3">
                <option value="10">10 requêtes/jour</option>
                <option value="100">100 requêtes/jour</option>
                <option value="200">200 requêtes/jour</option>
                <option value="10000">Illimité</option>
            </select>
            <div class="d-flex justify-content-between">
                <button id="saveApiLimit" class="btn btn-success">Enregistrer</button>
                <button id="closeApiLimitModal" class="btn btn-secondary">Fermer</button>
            </div>
        </div>
    `;
        document.body.appendChild(modal);

        button.addEventListener('click', function () {
            modal.style.display = 'flex';
        });

        document.addEventListener('click', function (event) {
            if (event.target.id === 'closeApiLimitModal' || event.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.getElementById('saveApiLimit').addEventListener('click', function () {
            const selectedLimit = document.getElementById('apiLimitSelect').value;
            const baseUrl = "<?= base_url(); ?>";

            // Mise à jour de la valeur du compteur dans le formulaire avant d'envoyer la requête
            counterInput.value = (selectedLimit === 'infinite') ? '∞' : selectedLimit;

            // Envoi de la nouvelle limite au serveur
            fetch(baseUrl + '/Api/Login/setRequestLimit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: currentTokenId, limit: selectedLimit })
            })
                .then(response => response.json())
                .then(data => {
                    toastr.success('Limite API mise à jour.');
                    modal.style.display = 'none';
                })
                .catch(error => {
                    console.error('Erreur lors de l\'enregistrement :', error);
                    modal.style.display = 'none';  // Ferme la modale même en cas d'erreur
                });
        });
    });
</script>