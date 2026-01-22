<header class="header header-sticky p-0 mb-4 breadcrumb">
    <div class="container-fluid px-4">
        <button class="header-toggler me-4" type="button"
                onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"
                style="margin-inline-start: -14px;">
            <i class="icon icon-lg fa-solid fa-bars"></i>
        </button>

        <style>
            .sub-menu:hover {
                transform: scale(1.1); /* Agrandissement de la carte au survol */
                z-index: 10; /* Pour que la carte agrandie reste au-dessus des autres */
            }
        </style>

        <?php if ($user->id_permission == 3): ?>
        <div style="border: solid 1px; border-radius: 25px; color: inherit" class="ms-5">
                    <div class="text-center">
                            <a href="
        <?= base_url('/Dashboard') ?>" class="btn ms-3 me-2 sub-menu"><i class="fa-solid fa-house"></i></a>
                            <a href="
        <?= base_url('/pages') ?>" class="btn me-4 sub-menu"><i class="fa-solid fa-file"></i></a>
<!--                        --><?php //elseif ($user->id_permission == 2): ?>
<!--                            <a href="-->
<!--        --><?php //= base_url('/Dashboard') ?><!--" class="btn me-4 ms-4 sub-menu"><i class="fa-solid fa-house"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/material') ?><!--" class="btn me-4 sub-menu"><i class="fa-solid fa-network-wired"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/materialmedical') ?><!--" class="btn me-4 sub-menu"><i class="fa-solid fa-suitcase-medical"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/ticket') ?><!--" class="btn me-4 sub-menu"><i class="fa-solid fa-ticket"></i></a>-->
<!--                        --><?php //else : ?>
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin') ?><!--" class="btn ms-3 me-2 sub-menu"><i class="fa-solid fa-house"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/task') ?><!--" class="btn me-2 sub-menu"><i class="fa-solid fa-list-check"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/user') ?><!--" class="btn me-2 sub-menu"><i class="fa-solid fa-users"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/material') ?><!--" class="btn me-2 sub-menu"><i class="fa-solid fa-network-wired"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/materialmedical') ?><!--" class="btn me-2 sub-menu"><i class="fa-solid fa-suitcase-medical"></i></a>-->
<!--                            <a href="-->
<!--        --><?php //= base_url('/admin/ticket') ?><!--" class="btn me-3 sub-menu"><i class="fa-solid fa-ticket"></i></a>-->
                    </div>
                </div>
        <?php else : ?>
        <?php endif; ?>

        <ul class="header-nav">
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button"
                        aria-expanded="false" data-coreui-toggle="dropdown">
                    <i class="fa-solid icon-lg fa-circle-half-stroke theme-icon-active"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem;">
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                                data-coreui-theme-value="light">
                            <i class="fa-solid fa-sun me-3"></i>Light
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center" type="button"
                                data-coreui-theme-value="dark">
                            <i class="fa-solid fa-moon me-3"></i>Dark
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center active" type="button"
                                data-coreui-theme-value="auto">
                            <i class="fa-solid fa-circle-half-stroke me-3"></i>Auto
                        </button>
                    </li>
                </ul>
            </li>

            <li class="nav-item py-0">
                <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-2 px-2 d-flex align-items-center" type="button"
                        aria-expanded="false" data-coreui-toggle="dropdown">
                    <i class="icon icon-lg theme-icon-active fa-solid fa-user"></i>
                </button>
                <ul class="dropdown-menu">
                    <li class="p-2"><img class="img-thumbnail mx-auto d-block" height="80px"
                                         src="<?= base_url($user->getProfileImage()); ?>"></li>
                    Utilisateur connecté : <?= $user->id ?>
                    <?php if ($user->id_permission === 1) { ?>
                        <li><a class="dropdown-item" href="<?= base_url('/admin/user/'); ?><?= $user->id; ?>">Modifier
                                mon compte</a></li>
                    <?php } else { ?>
                        <li><a class="dropdown-item" href="<?= base_url('/profile/'); ?><?= $user->id; ?>">Mon
                                compte</a></li>
                    <?php } ?>
                    <li><a class="dropdown-item text-danger" href="<?= base_url('/login/logout'); ?>">Se deconnecter</a>
                    </li>
                </ul>

            </li>
        </ul>
    </div>

    <?php if (count($breadcrumb) > 0) { ?>
        <div class="container-fluid px-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb my-0">
                    <?php foreach ($breadcrumb as $bitem) { ?>
                        <li class="breadcrumb-item">
                            <?php if ($bitem['url'] !== "") { ?>
                                <a class="link-underline link-underline-opacity-0" href="<?= base_url($bitem['url']) ?>"
                                   class=""><?= $bitem['text'] ?></a>
                            <?php } else { ?>
                                <?= $bitem['text'] ?>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ol>

            </nav>
        </div>
    <?php } ?>
</header>
