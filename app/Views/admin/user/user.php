<div class="row">
    <div class="col">
        <form action="<?= isset($utilisateur) ? base_url("/admin/user/update") : base_url("/admin/user/create") ?>"
              method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="table-responsive">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <?= isset($utilisateur) ? "Edition de " . $utilisateur['firstname'] . " " . $utilisateur['lastname'] : "Créer un utilisateur" ?>
                        </h4>

                        <div class="d-flex justify-content-end align-items-center">
                            <!--                    ACTIF -->
                            <?php
                            if (isset($utilisateur) && $utilisateur['deleted_at'] == null) { ?>
                                Activer l'utilisateur
                                <a title="Désactiver l'utilisateur"
                                   href="<?= base_url('admin/user/deactivate/') . $utilisateur['id']; ?>">
                                    <i class="fa-solid fa-xl fa-toggle-on text-success me-4 ms-1"></i>
                                </a>
                                <?php
                            } elseif (isset($utilisateur)) { ?>
                                Activer l'utilisateur
                                <a title="Activer un utilisateur"
                                   href="<?= base_url('admin/user/activate/') . $utilisateur['id']; ?>">
                                    <i class="fa-solid fa-toggle-off fa-xl text-danger me-4 ms-1"></i>
                                </a>
                                <?php
                            }
                            ?>

                            <!--                    BAN-->
                            <?php
                            if (isset($utilisateur) && $utilisateur['blacklistid_user'] == null) { ?>
                                Bannir l'utilisateur
                                <a title="Désactiver l'utilisateur"
                                   href="<?= base_url('admin/user/blacklist/') . $utilisateur['id']; ?>">
                                    <i class="fa-solid fa-xl fa-toggle-off text-success me-2 ms-2"></i>
                                </a>
                                <?php
                            } elseif (isset($utilisateur)) { ?>
                                Débannir l'utilisateur
                                <a title="Activer un utilisateur"
                                   href="<?= base_url('admin/user/removeblacklist/') . $utilisateur['blacklistid_user']; ?>">
                                    <i class="fa-solid fa-toggle-on fa-xl text-success me-2 ms-2"></i>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profil-tab" data-bs-toggle="tab"
                                        data-bs-target="#profil" type="button" role="tab" aria-controls="profil"
                                        aria-selected="true">Identité
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="onglet-tab" data-bs-toggle="tab"
                                        data-bs-target="#messagerie" type="button" role="tab" aria-controls="onglet"
                                        aria-selected="false">Messagerie
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="onglet-tab" data-bs-toggle="tab"
                                        data-bs-target="#code" type="button" role="tab" aria-controls="onglet"
                                        aria-selected="false">Code d'accès
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="onglet-tab" data-bs-toggle="tab"
                                        data-bs-target="#token" type="button" role="tab" aria-controls="onglet"
                                        aria-selected="false">Token
                                </button>
                            </li>
                        </ul>


                        <!-- Tab panes -->
                        <div class="tab-content border p-3">
                            <div class="tab-pane active" id="profil" role="tabpanel" aria-labelledby="profil-tab"
                                 tabindex="0">
                                <h4 class="text-center">Identité</h4>
                                <div class="mb-3">
                                    <label for="lastname" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="lastname" placeholder="Votre nom"
                                           value="<?= isset($utilisateur) ? $utilisateur['lastname'] : ""; ?>"
                                           name="lastname">
                                </div>
                                <div class="mb-3">
                                    <label for="firstname" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="firstname" placeholder="Votre prénom"
                                           value="<?= isset($utilisateur) ? $utilisateur['firstname'] : ""; ?>"
                                           name="firstname">
                                </div>
                                <div class="mb-3">
                                    <label for="id_permission" class="form-label">Rôle</label>
                                    <select class="form-select" id="id_permission" name="id_permission">
                                        <option disabled <?= !isset($utilisateur) ? "selected" : ""; ?> >Sélectionner un
                                            role
                                        </option>
                                        <?php foreach ($permissions as $p): ?>
                                            <option value="<?= $p['id']; ?>" <?= (isset($utilisateur) && $p['id'] == $utilisateur['id_permission']) ? "selected" : "" ?> >
                                                <?= $p['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label me-2">Avatar</label>
                                    <div id="preview">
                                        <?php
                                        $profileImageUrl = isset($utilisateur['avatar_url']) ? base_url($utilisateur['avatar_url']) : "#";
                                        ?>
                                        <img class="img-thumbnail me-2" alt="Aperçu de l'image"
                                             style="display: <?= isset($utilisateur['avatar_url']) ? "block" : "none" ?>; max-width: 100px;"
                                             src="<?= $profileImageUrl ?>">
                                    </div>

                                    <input class="form-control" type="file" name="profile_image" id="image">
                                </div>
                            </div>


                            <div class="tab-pane" id="messagerie" role="tabpanel" aria-labelledby="messagerie-tab"
                                 tabindex="0">
                                <h4 class="text-center">Messagerie</h4>
                                <div class="mb-3">
                                    <label for="mail" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="mail" placeholder="Votre E-mail"
                                           value="<?= isset
                                           ($utilisateur) ? $utilisateur['email'] : "" ?>" <?= isset($utilisateur) ? "readonly"
                                        : "" ?> <?= isset($utilisateur) ? "" : "name='email'" ?> >
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password"
                                           placeholder="Votre mot de passe" value="" name="password">
                                </div>
                            </div>

                            <div class="tab-pane" id="code" role="tabpanel" aria-labelledby="code-tab" tabindex="0">
                                <h4 class="text-center">Codes d'Accès</h4>
                                <div class="mb-3">
                                    <label for="sessionId" class="form-label">Identifiant de session</label>
                                    <input type="text" class="form-control" id="sessionId" placeholder="Votre nom"
                                           value="<?= isset($utilisateur) ? $utilisateur['sessionId'] : ""; ?>"
                                           name="sessionId">
                                </div>
                                <div class="mb-3">
                                    <label for="sessionPassword" class="form-label">Mot de passe de session</label>
                                    <input type="password" class="form-control" id="sessionPassword"
                                           placeholder="Votre mot de passe de session" value="" name="sessionPassword">
                                </div>

                                <div class="mb-3">
                                    <label for="uegarId" class="form-label">Identifiant uÉgar</label>
                                    <input type="text" class="form-control" id="uegarId" placeholder="Votre nom"
                                           value="<?= isset($utilisateur) ? $utilisateur['uegarId'] : ""; ?>"
                                           name="uegarId">
                                </div>
                                <div class="mb-3">
                                    <label for="uegarPassword" class="form-label">Mot de passe uÉgar</label>
                                    <input type="password" class="form-control" id="uegarPassword"
                                           placeholder="Votre mot de passe d'uÉgar'" value="" name="uegarPassword">
                                </div>
                            </div>


                            <div class="tab-pane" id="token" role="tabpanel" aria-labelledby="token-tab" tabindex="0">
                                <h4 class="text-center">Token</h4>
                                <div class="mb-3">
                                    <label for="id_api_tokens" class="form-label">Token</label>
                                    <input
                                            type="text"
                                            class="form-control"
                                            id="id_api_tokens"
                                            placeholder="Aucun Token"
                                            value="<?= isset($utilisateur) ? $utilisateur['id_api_tokens'] : "" ?>"
                                            readonly>
                                </div>
                                <?php if (isset($utilisateur) && empty($utilisateur['id_api_tokens'])): ?>
                                    <button
                                            type="button"
                                            class="btn btn-primary"
                                            id="generateTokenButton"
                                    >
                                        Générer un Token
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($utilisateur)): ?>
                        <input type="hidden" name="id" value="<?= $utilisateur['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($utilisateur) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>


            <script>
                // $(document).ready(function () {
                //     $('#id_job').select2({
                //         theme: 'bootstrap-5',
                //         placeholder: 'Sélectionner un métier',
                //         allowClear: true
                //     });
                // });


                document.getElementById('generateTokenButton')?.addEventListener('click', function () {
                    const userId = <?= isset($utilisateur) ? (int)$utilisateur['id'] : 'null' ?>;
                    if (!userId) return alert("L'ID utilisateur est manquant.");

                    const url = `<?= base_url('api/login/token') ?>?userId=${userId}&force_regenerate=true`;

                    fetch(url, {
                        method: 'GET',
                        headers: {'Accept': 'application/json'}
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.token) {
                                document.getElementById('id_api_tokens').value = data.token;
                                toastr.success('Token généré avec succès !');
                            } else {
                                alert('Erreur : ' + (data.message ?? 'Impossible de générer un token'));
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert("Erreur réseau ou serveur.");
                        });
                });
            </script>

        </form>
    </div>
</div>
