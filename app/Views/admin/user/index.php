<!-- Carte des utilisateurs -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des utilisateurs</h4>
        <!--        <button id="btnsetAllRequestLimits" class="btn btn-outline-primary me-2">Limiter les requêtes API</button>-->
        <span>
        <a href="<?= base_url('/admin/token/'); ?>" class="m-4">Token <i class="fa-solid fa-map-pin"></i></a>
        <a href="<?= base_url('/admin/user/new'); ?>">Créer un utilisateur <i class="fa-solid fa-user-plus"></i></a>
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableUsers" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Avatar</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Mail</th>
                    <th>Rôle</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                    <th>Actif</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour afficher les informations utilisateur -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Informations utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="userAvatar" class="mx-auto d-block" width="150px" src="/assets/img/avatars/unknow.png"
                     alt="Avatar">
                <p><strong>ID :</strong> <span id="userId"></span></p>
                <p><strong>Prénom :</strong> <span id="userFirstname"></span></p>
                <p><strong>Nom :</strong> <span id="userLastname"></span></p>
                <p><strong>Email :</strong> <span id="userEmail"></span></p>
                <p><strong>Mot de passe Email :</strong> <span id="userPassword"></span></p>
                <p><strong>Identifiant de session :</strong> <span id="usersessionId"></span></p>
                <p><strong>Mot de passe de session :</strong> <span id="usersessionPassword"></span></p>
                <p><strong>Identifiant uÉgar :</strong> <span id="useruegarId"></span></p>
                <p><strong>Mot de passe uÉgar :</strong> <span id="useruegarPassword"></span></p>
                <p><strong>Rôle:</strong> <span id="userRole"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";
        var dataTable = $('#tableUsers').DataTable({
            responsive: false,
            autoWidth: false,
            processing: true,
            serverSide: true,
            "pageLength": 10,
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": baseUrl + "admin/user/SearchUser",
                "type": "POST"
            },
            "columns": [
                {"data": "id"},
                {
                    data: 'avatar_url',
                    sortable: false,
                    render: function (data, type, row) {
                        return `<a href="#" data-bs-toggle="modal" data-bs-target="#userModal" class="user-info" data-id="${row.id}" data-firstname="${row.firstname}" data-lastname="${row.lastname}" data-email="${row.email}" data-role="${row.permission_name}" data-job="${row.id_job}">
                                    <img src="${baseUrl}${data || 'assets/img/avatars/unknow.png'}" alt="Avatar" style="max-width: 20px; height: auto;">
                                </a>`;
                    }
                },
                {"data": "firstname"},
                {"data": "lastname"},
                {"data": "email"},
                {"data": "permission_name"},
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href="${baseUrl}admin/user/${data}"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<a href='${baseUrl}admin/user/delete/${data}'><i class="fa-solid fa-trash text-danger"></i></a>`;
                    }
                },
                {
                    data: 'id',
                    sortable: true,
                    render: function (data, type, row) {
                        return (row.deleted_at === null ?
                            `<a title="Désactiver l'utilisateur" href="${baseUrl}admin/user/deactivate/${row.id}"><i class="fa-solid fa-xl fa-toggle-on text-success"></i></a>` :
                            `<a title="Activer un utilisateur" href="${baseUrl}admin/user/activate/${row.id}"><i class="fa-solid fa-toggle-off fa-xl text-danger"></i></a>`);
                    }
                },
            ]
        });

        // Gérer l'affichage du modal avec les données utilisateur
        $('#tableUsers tbody').on('click', 'a.user-info', function () {
            $('#userId').text($(this).data('id'));
            $('#userFirstname').text($(this).data('firstname'));
            $('#userLastname').text($(this).data('lastname'));
            $('#userEmail').text($(this).data('email'));
            $('#userPassword').text($(this).data('password'));
            $('#usersessionId').text($(this).data('sessionId'));
            $('#usersessionPassword').text($(this).data('sessionPassword'));
            $('#useruegarId').text($(this).data('uegarId'));
            $('#useruegarPassword').text($(this).data('uegarPassword'));
            $('#userRole').text($(this).data('role'));
            const avatar = $(this).find('img').attr('src') || '/assets/img/avatars/1.jpg';
            $('#userAvatar').attr('src', avatar);
        });
    });

    // JavaScript + PHP : Intégration complète pour modifier la limite de requêtes API et mettre à jour le 'counter' dans la table api_token

    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('btnsetAllRequestLimits');

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

        modal.innerHTML = `
        <div style="background: white; padding: 20px; border-radius: 10px; max-width: 300px; width: 100%; text-align: center;">
            <h3>Définir la limite API</h3>
            <select id="apiLimitSelect" class="form-select mb-3">
                <option value="10">10 requêtes/jour</option>
                <option value="100">100 requêtes/jour</option>
                <option value="200">200 requêtes/jour</option>
                <option value="infinite">Illimité</option>
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
            var baseUrl = "<?= base_url(); ?>";
            fetch(baseUrl + '/api/login/setAllRequestLimits', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({limit: selectedLimit})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Limite de requêtes mise à jour : ' + selectedLimit);
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                    modal.style.display = 'none';
                })
                .catch(error => console.error('Erreur lors de la mise à jour :', error));
        });
    });
</script>

<style>
    #tableUsers th, #tableUsers td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableUsers th, #tableUsers td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>
