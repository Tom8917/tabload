<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des Tokens des utilisateurs</h4>
        <button id="btnSetAllApiLimits" class="btn btn-outline-primary me-2">Limiter les requêtes API</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <?php if (empty($tokens)) : ?>
                <p>Pas de token disponible dans la base de données.</p>
            <?php else : ?>
                <table id="tableTokens" class="table table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Token</th>
                        <th>Compteur</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";
        var dataTable = $('#tableTokens').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": baseUrl + "admin/token/SearchToken",
                "type": "POST",
            },
            "columns": [
                {"data": "id"},
                {"data": "id_user"},
                {"data": "token"},
                {"data": "counter"},
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="${baseUrl}admin/token/${data}"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href='${baseUrl}admin/token/delete/${data}'><i class="fa-solid fa-trash text-danger"></i></a>`;
                    }
                },
            ]
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('btnSetAllApiLimits'); // Bouton pour appliquer à tous

        if (!button) return; // Correction ici

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
            <h3>Définir la limite API pour tous</h3>
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

            fetch(baseUrl + '/Api/Login/setAllRequestLimits', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({limit: selectedLimit})
            })
                .then(response => response.json())
                .then(data => {
                    toastr.success('Limite API mise à jour pour tous les utilisateurs.');
                    $('#tableTokens').DataTable().ajax.reload(null, false);
                    modal.style.display = 'none';
                })
                .catch(error => {
                    console.error('Erreur lors de l\'enregistrement :', error);
                    modal.style.display = 'none';
                });
        });
    });
</script>

<style>
    #tableTokens th, #tableTokens td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableTokens th, #tableTokens td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>