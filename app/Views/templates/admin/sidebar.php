<div class="sidebar sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <?php if ($user->id_permission === 1) { ?>
                <div class="d-flex align-items-center justify-content-center">
<!--                    <a href="--><?php //= base_url('admin') ?><!--">-->
<!--                        <img src="--><?php //= base_url('assets/brand/coda_logo.png') ?><!--" alt="Logo"-->
<!--                             style="max-width:175px; height:auto; filter: drop-shadow(0 6px 10px rgba(0,0,0,0.6));"-->
<!--                             class="ms-3">-->
<!--                    </a>-->
                    <div class="d-flex align-items-center justify-content-center">
                        <img src="<?= base_url('assets/brand/tabload_logo_2.png') ?>"
                             alt="Tabload logo"
                             height="100">
                    </div>                </div>
            <?php } else { ?>
                <div class="d-flex align-items-center justify-content-center">
<!--                    <a href="--><?php //= base_url('/Dashboard') ?><!--">-->
<!--                        <img src="--><?php //= base_url('assets/brand/coda_logo.png') ?><!--" alt="Logo"-->
<!--                             style="max-width:175px; height:auto; filter: drop-shadow(0 6px 10px rgba(0,0,0,0.6));"-->
<!--                             class="ms-3">-->
<!--                    </a>-->
                </div>
            <?php } ?>

        </div>
        <!--        <a href="-->
        <?php //= base_url(); ?><!--" target="_blank" class="header-toggler" alt="Voir le site" title="Voir le site"><i class="fa-solid fa-house-laptop"></i></a>-->

    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <?php
        foreach ($menus as $km => $menu) {
            if (isset($menu['admin']) && !$user->isAdmin()) {
                continue;
            }
            if (isset($menu['require']) && !$user->check($menu['require'])) {
                continue;
            }
            if (!isset($menu['subs'])) { ?>
                <li class="nav-item <?= ($localmenu === $km ? 'active' : '') ?>" id="menu_<?= $km ?>">
                    <a class="nav-link" href="<?= base_url($menu['url']) ?>">
                        <?php if (isset($menu['icon'])) {
                            echo $menu['icon'];
                        } else { ?>
                            <svg class="nav-icon"><span class="bullet bullet-dot"></span></svg><?php } ?>
                        <?= $menu['title'] ?>
                    </a>
                </li>
            <?php } else { ?>
                <li class="nav-group">
                    <a class="nav-link nav-group-toggle" href="#">
                        <?= (isset($menu['icon'])) ? $menu['icon'] : ""; ?>
                        <?= $menu['title'] ?></a>
                    <ul class="nav-group-items compact">
                        <?php
                        foreach ($menu['subs'] as $ksm => $smenu) {
                            if (isset($smenu['admin']) && !$user->isAdmin()) {
                                continue;
                            }
                            if (isset($smenu['require']) && !$user->check($smenu['require'])) {
                                continue;
                            } ?>
                            <li class="nav-item ps-2" id="menu_<?= $ksm ?>">
                                <a class="nav-link" href="<?= base_url($smenu['url']); ?>">
                                    <?php if (isset($smenu['icon'])) echo $smenu['icon']; ?>
                                    <?= $smenu['title'] ?>
                                </a>
                                <?php
                                // Si ce sous-menu a encore des sous-menus (menu déroulant dans un autre menu déroulant)
                                if (isset($smenu['subs'])) { ?>
                                    <ul class="nav-group-items compact">
                                        <?php
                                        foreach ($smenu['subs'] as $kssm => $ssmenu) {
                                            if (isset($ssmenu['admin']) && !$user->isAdmin()) {
                                                continue;
                                            }
                                            if (isset($ssmenu['require']) && !$user->check($ssmenu['require'])) {
                                                continue;
                                            } ?>
                                            <li class="nav-item ps-3" id="menu_<?= $kssm ?>">
                                                <a class="nav-link" href="<?= base_url($ssmenu['url']); ?>">
                                                    <?php if (isset($ssmenu['icon'])) echo $ssmenu['icon']; ?>
                                                    <?= $ssmenu['title'] ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php }
        } ?>

        <li class="nav-item mt-auto">

            <?php if ($user->id_permission === 1) { ?>
                <!-- Bouton pour afficher le toast -->
                <button type="button" class="btn btn-primary ms-3" onclick="showPalette()">Voir la palette</button>

                <!-- Toast Bootstrap -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="paletteToast" class="toast" role="alert">
                        <div class="toast-header">
                            <strong class="me-auto">Palette TABLOAD</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin:6px 0;">
                                <div style="width:60px;height:60px;background:#1A365D;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;">
                                    #1A365D
                                </div>
                                <div style="width:60px;height:60px;background:#105298;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;">
                                    #105298
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <script>
                    function showPalette() {
                        const toastEl = document.getElementById('paletteToast');
                        const toast = new bootstrap.Toast(toastEl);
                        toast.show();
                    }
                </script>

            <?php } ?>


            <a class="nav-link" href="<?= base_url('/login/logout'); ?>">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Déconnexion</a>
        </li>
    </ul>
    <div class="sidebar-footer border-top d-none d-md-flex">
        <small>Version : 1.0</small>
        <div class="row">

            <?php if ($user->id_permission === 1) { ?>
            <div class="col-md-3 ms-5">
                <a href="http://localhost:8081/" target="_blank"
                   style="color: orange; font-size: 1.5rem;">
                    <i class="fa-brands fa-php"></i>
                </a>
            </div>
            <?php } ?>

            <!--            <div class="col-md-3 ms-3">-->
            <!--                <a href="-->
            <?php //= base_url() ?><!--" style="color: orangered; font-size: 1.5rem;">-->
            <!--                    <i class="fa-brands fa-free-code-camp"></i>-->
            <!--                </a>-->
            <!--            </div>  -->
        </div>
    </div>
</div>