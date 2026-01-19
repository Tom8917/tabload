<?php
/**
 * FRONT Sidebar (même style que admin)
 * Variables attendues:
 * - $menus (array) -> depuis front.json
 * - $user (object|null)
 * - $localmenu (string|null)
 */
$menus     = $menus ?? [];
$user      = $user ?? null;
$localmenu = $localmenu ?? null;
?>

<div class="sidebar sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center justify-content-center">
                <img src="<?= base_url('assets/brand/tabload_logo_2.png') ?>"
                     alt="Tabload logo"
                     height="100">
            </div>
        </div>
    </div>

    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <?php foreach ($menus as $km => $menu): ?>
            <?php
            // sécurité
            if (!is_array($menu)) continue;

            $title = $menu['title'] ?? $km;
            $url   = $menu['url'] ?? '#';
            $icon  = $menu['icon'] ?? null;

            // si un jour tu rajoutes des règles front (admin/require), on laisse prêt:
            if (isset($menu['admin']) && $menu['admin'] && $user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
                continue;
            }
            if (isset($menu['require']) && $user && method_exists($user, 'check') && !$user->check($menu['require'])) {
                continue;
            }
            ?>

            <?php if (!isset($menu['subs'])): ?>
                <li class="nav-item <?= ($localmenu === $km ? 'active' : '') ?>" id="menu_<?= esc($km) ?>">
                    <a class="nav-link" href="<?= base_url($url) ?>">
                        <?php if ($icon): ?>
                            <?= $icon ?>
                        <?php else: ?>
                            <svg class="nav-icon"><span class="bullet bullet-dot"></span></svg>
                        <?php endif; ?>
                        <?= esc($title) ?>
                    </a>
                </li>

            <?php else: ?>
                <li class="nav-group">
                    <a class="nav-link nav-group-toggle" href="#">
                        <?= $icon ? $icon : '' ?>
                        <?= esc($title) ?>
                    </a>

                    <ul class="nav-group-items compact">
                        <?php foreach (($menu['subs'] ?? []) as $ksm => $smenu): ?>
                            <?php
                            if (!is_array($smenu)) continue;

                            $stitle = $smenu['title'] ?? $ksm;
                            $surl   = $smenu['url'] ?? '#';
                            $sicon  = $smenu['icon'] ?? null;

                            if (isset($smenu['admin']) && $smenu['admin'] && $user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
                                continue;
                            }
                            if (isset($smenu['require']) && $user && method_exists($user, 'check') && !$user->check($smenu['require'])) {
                                continue;
                            }
                            ?>

                            <li class="nav-item ps-2" id="menu_<?= esc($ksm) ?>">
                                <a class="nav-link" href="<?= base_url($surl) ?>">
                                    <?php if ($sicon) echo $sicon; ?>
                                    <?= esc($stitle) ?>
                                </a>

                                <?php if (isset($smenu['subs'])): ?>
                                    <ul class="nav-group-items compact">
                                        <?php foreach (($smenu['subs'] ?? []) as $kssm => $ssmenu): ?>
                                            <?php
                                            if (!is_array($ssmenu)) continue;

                                            $sstitle = $ssmenu['title'] ?? $kssm;
                                            $ssurl   = $ssmenu['url'] ?? '#';
                                            $ssicon  = $ssmenu['icon'] ?? null;

                                            if (isset($ssmenu['admin']) && $ssmenu['admin'] && $user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
                                                continue;
                                            }
                                            if (isset($ssmenu['require']) && $user && method_exists($user, 'check') && !$user->check($ssmenu['require'])) {
                                                continue;
                                            }
                                            ?>

                                            <li class="nav-item ps-3" id="menu_<?= esc($kssm) ?>">
                                                <a class="nav-link" href="<?= base_url($ssurl) ?>">
                                                    <?php if ($ssicon) echo $ssicon; ?>
                                                    <?= esc($sstitle) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>

        <li class="nav-item mt-auto">
            <a class="nav-link" href="<?= base_url('/login/logout'); ?>">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Déconnexion
            </a>
        </li>
    </ul>

    <div class="sidebar-footer border-top d-none d-md-flex">
        <small>Version : 1.0</small>
    </div>
</div>
