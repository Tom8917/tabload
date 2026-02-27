<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Liste des utilisateurs</h4>

        <span class="d-flex gap-3 align-items-center">
            <a href="<?= base_url('/admin/token'); ?>" class="text-decoration-none">
                Token <i class="fa-solid fa-map-pin"></i>
            </a>
            <a href="<?= base_url('/admin/user/new'); ?>" class="text-decoration-none">
                Créer un utilisateur <i class="fa-solid fa-user-plus"></i>
            </a>
        </span>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="tableUsers" class="table table-hover align-middle mb-0 w-100">
                <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Mail</th>
                    <th>Rôle</th>
                    <th style="width:90px;">Modifier</th>
                    <th style="width:90px;">Supprimer</th>
                    <th style="width:90px;">Actif</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal infos utilisateur -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Informations utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="mb-1"><strong>ID :</strong> <span id="userId"></span></p>
                <p class="mb-1"><strong>Prénom :</strong> <span id="userFirstname"></span></p>
                <p class="mb-1"><strong>Nom :</strong> <span id="userLastname"></span></p>
                <p class="mb-1"><strong>Email :</strong> <span id="userEmail"></span></p>
                <p class="mb-0"><strong>Rôle :</strong> <span id="userRole"></span></p>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const baseUrl = "<?= rtrim(base_url(), '/') . '/' ?>";
        const csrfName = "<?= csrf_token() ?>";
        let csrfHash   = "<?= csrf_hash() ?>";

        const table = $('#tableUsers').DataTable({
            serverSide: true,
            processing: true,
            responsive: true,
            autoWidth: false,

            ajax: {
                url: baseUrl + "admin/user/search-user",
                type: "POST",
                data: function (d) {
                    d[csrfName] = csrfHash;
                    return d;
                },
                dataSrc: function (json) {
                    if (json && (json.csrfHash || json.csrf_token || json.token)) {
                        csrfHash = json.csrfHash || json.csrf_token || json.token;
                    }
                    return json.data || json.aaData || [];
                },
                error: function (xhr) {
                    console.error("DataTables AJAX error:", xhr.status, xhr.responseText);
                }
            },

            columns: [
                { data: "id", defaultContent: "—",
                    render: function (data, type, row) {
                        const firstname = row.firstname ?? '';
                        const lastname  = row.lastname ?? '';
                        const email     = row.email ?? '';
                        const role      = row.permission_name ?? '—';

                        return `
                          <a href="#" class="user-info text-decoration-none"
                             data-bs-toggle="modal" data-bs-target="#userModal"
                             data-id="${row.id ?? ''}"
                             data-firstname="${String(firstname).replaceAll('"','&quot;')}"
                             data-lastname="${String(lastname).replaceAll('"','&quot;')}"
                             data-email="${String(email).replaceAll('"','&quot;')}"
                             data-role="${String(role).replaceAll('"','&quot;')}">
                             ${data ?? '—'}
                          </a>
                        `;
                    }
                },
                { data: "firstname", defaultContent: "—" },
                { data: "lastname",  defaultContent: "—" },
                { data: "email",     defaultContent: "—" },
                { data: "permission_name", defaultContent: "—" },

                {
                    data: "id",
                    orderable: false,
                    searchable: false,
                    render: function (id) {
                        return `<a href="${baseUrl}admin/user/${id}" title="Modifier"><i class="fa-solid fa-pencil"></i></a>`;
                    }
                },
                {
                    data: "id",
                    orderable: false,
                    searchable: false,
                    render: function (id) {
                        return `<a href="${baseUrl}admin/user/delete/${id}" title="Supprimer"
                                   onclick="return confirm('Supprimer cet utilisateur ?');">
                                   <i class="fa-solid fa-trash text-danger"></i>
                                </a>`;
                    }
                },
                {
                    data: null,
                    orderable: true,
                    searchable: false,
                    render: function (data, type, row) {
                        const active = (row.deleted_at === null || row.deleted_at === '' || typeof row.deleted_at === 'undefined');
                        return active
                            ? `<a title="Désactiver l'utilisateur" href="${baseUrl}admin/user/deactivate/${row.id}">
                                   <i class="fa-solid fa-toggle-on fa-xl text-success"></i>
                               </a>`
                            : `<a title="Activer l'utilisateur" href="${baseUrl}admin/user/activate/${row.id}">
                                   <i class="fa-solid fa-toggle-off fa-xl text-danger"></i>
                               </a>`;
                    }
                }
            ],

            order: [[0, "desc"]],

            language: {
                processing: "Traitement...",
                search: "Rechercher :",
                lengthMenu: "Afficher _MENU_ éléments",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
                infoEmpty: "Affichage de 0 à 0 sur 0 élément",
                infoFiltered: "(filtré de _MAX_ éléments au total)",
                loadingRecords: "Chargement...",
                zeroRecords: "Aucun résultat trouvé",
                emptyTable: "Aucune donnée disponible",
                paginate: { first: "Premier", previous: "Précédent", next: "Suivant", last: "Dernier" },
                aria: {
                    sortAscending: ": activer pour trier la colonne par ordre croissant",
                    sortDescending: ": activer pour trier la colonne par ordre décroissant"
                }
            }
        });

        // Modal infos utilisateur
        $('#tableUsers tbody').on('click', 'a.user-info', function (e) {
            e.preventDefault();
            const $a = $(this);
            $('#userId').text($a.data('id') ?? '—');
            $('#userFirstname').text($a.data('firstname') ?? '—');
            $('#userLastname').text($a.data('lastname') ?? '—');
            $('#userEmail').text($a.data('email') ?? '—');
            $('#userRole').text($a.data('role') ?? '—');
        });
    });
</script>

<style>
    #tableUsers th, #tableUsers td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 180px;
    }

    @media (max-width: 768px) {
        #tableUsers th, #tableUsers td {
            font-size: 12px;
            max-width: 120px;
        }
    }
</style>