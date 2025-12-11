<!DOCTYPE html>
<html lang="fr-FR" data-coreui-theme="auto">
<head>
    <base href="<?= base_url('./') ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <title><?= $title ?></title>
    <link rel="apple-touch-icon" sizes="57x57" href="<?= base_url('/assets/favicon/apple-icon-57x57.png') ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= base_url('/assets/favicon/apple-icon-60x60.png') ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= base_url('/assets/favicon/apple-icon-72x72.png') ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('/assets/favicon/apple-icon-76x76.png') ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= base_url('/assets/favicon/apple-icon-114x114.png') ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= base_url('/assets/favicon/apple-icon-120x120.png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= base_url('/assets/favicon/apple-icon-144x144.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('/assets/favicon/apple-icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('/assets/favicon/apple-icon-180x180.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192"
          href="<?= base_url('/assets/favicon/android-icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('/assets/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= base_url('/assets/favicon/favicon-96x96.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('/assets/favicon/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= base_url('/assets/favicon/manifest.json') ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSS-->
    <link rel="stylesheet" href="<?= base_url('/vendors/simplebar/css/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/vendors/simplebar.css') ?>">
    <link href="<?= base_url('/css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('/vendors/@coreui/chartjs/css/coreui-chartjs.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/css/toastr.min.css') ?>">
    <link href="<?= base_url('/css/neon.css') ?>" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="<?= base_url('/js/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('/js/config.js') ?>"></script>
    <script src="<?= base_url('/js/color-modes.js') ?>"></script>
    <script src="<?= base_url('/vendors/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
    <script src="<?= base_url('/vendors/simplebar/js/simplebar.min.js') ?>"></script>
    <script src="<?= base_url('/vendors/@coreui/utils/js/index.js') ?>"></script>
    <script src="<?= base_url('/js/admin.js') ?>"></script>
    <script src="<?= base_url('/js/toastr.min.js') ?>"></script>
    <script src="<?= base_url('/js/tinymce/tinymce.min.js') ?>"></script>

    <!--    MAPS     -->
    <!-- CSS Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <!-- JS Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const header = document.querySelector('header.header');

        document.addEventListener('scroll', () => {
            if (header) {
                header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
            }
        });


        toastr.options = {
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

    </script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"
            integrity="sha512-GWzVrcGlo0TxTRvz9ttioyYJ+Wwk9Ck0G81D+eO63BaqHaJ3YZX9wuqjwgfcV/MrB2PhaVX9DkYVhbFpStnqpQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Datatable -->
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.css"
          rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.js"></script>

    <!-- SWEETALERT 2  -->
    <link href="<?= base_url('/css/sweetalert2.min.css') ?>" rel="stylesheet">
    <script src="<?= base_url('/js/sweetalert2.all.min.js') ?>"></script>

    <!-- BOOTSTRAP BUNDLE -->
    <script src="<?= base_url('/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- SELECT 2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <!-- Fonts (titres si tu veux, mais pas obligatoire) -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* ================= Violet Premium — Variables ================= */
        :root{
            /* Palette (violet) */
            --violet-950:#12081f;   /* quasi noir / profondeur */
            --violet-900:#1d0e2f;   /* très sombre (fond principal) */
            --violet-800:#2a1743;   /* sombre */
            --violet-700:#3a225d;   /* foncé principal */
            --violet-600:#51317d;   /* accent (brand) */
            --violet-500:#6a49a1;   /* hover / clair */
            --violet-400:#8b6bc0;   /* clair */
            --violet-300:#b19ade;   /* très clair / surfaces */
            --violet-200:#d6c9f0;   /* chips / fonds pâles */

            /* Jaune secondaire (accents secondaires uniquement) */
            --yellow:#d7fa00;

            /* Lignes / bordures (lavande neutre dérivée) */
            --line-500:#b7a9d9;
            --line-300:#e6def7;

            /* Tokens */
            --r-sm:10px; --r-md:14px; --r-lg:18px;
            --shadow-1:0 6px 18px rgba(0,0,0,.10);
            --shadow-2:0 10px 28px rgba(0,0,0,.16);
        }

        /* ===================================================================== */
        /* =====================  BOUTONS (Bootstrap)  ========================== */
        /* ===================================================================== */

        /* .btn-primary : plein violet */
        .btn-primary{
            background-color:var(--violet-600) !important;
            border-color:color-mix(in oklab, var(--violet-600) 65%, black) !important;
            color:#ffffff !important; /* lisible sur fond violet */
            box-shadow:inset 0 1px 0 rgba(255,255,255,.16), var(--shadow-1) !important;
        }
        .btn-primary:hover{
            background-color:var(--violet-500) !important;
            border-color:color-mix(in oklab, var(--violet-500) 65%, black) !important;
            filter:brightness(1.02);
        }
        .btn-primary:active, .btn-primary:focus{
            box-shadow:var(--shadow-1), 0 0 0 .2rem color-mix(in oklab, var(--violet-500) 25%, white) !important;
        }

        /* .btn-outline-primary : contour violet */
        .btn-outline-primary{
            color:var(--violet-700) !important;
            border-color:var(--violet-700) !important;
            background:transparent !important;
        }
        .btn-outline-primary:hover{
            color:var(--violet-900) !important;
            border-color:var(--violet-600) !important;
            background:color-mix(in oklab, var(--violet-500) 12%, transparent) !important;
        }

        /* .btn-link : souligné discret avec l’accent (violet) en soulignement */
        .btn-link{
            text-decoration:none !important;
            box-shadow: inset 0 -2px 0 0 var(--violet-600) !important;
        }
        .btn-link:hover{ box-shadow: inset 0 -3px 0 0 var(--violet-500) !important; }

        /* ===================================================================== */
        /* ======================  BADGES / ALERTS  ============================= */
        /* ===================================================================== */

        .badge.bg-primary{ background-color:var(--violet-600) !important; color:#f6f2ff !important; }
        .badge.text-bg-primary{ background-color:var(--violet-600) !important; color:#f6f2ff !important; }
        .badge.border-primary{ border-color:var(--violet-700) !important; }

        /* Alertes primary (texte par Bootstrap; on teinte fond/bord) */
        .alert-primary{
            background-color:color-mix(in oklab, var(--violet-400) 18%, white) !important;
            border-color:color-mix(in oklab, var(--violet-600) 35%, white) !important;
            color:inherit !important;
        }

        /* ===================================================================== */
        /* ===================  CARDS / PANELS / CONTENEURS  ==================== */
        /* ===================================================================== */

        .card{
            background:transparent !important;
            border:1px solid color-mix(in oklab, var(--line-500) 70%, transparent) !important;
            border-radius:var(--r-lg) !important;
            box-shadow:none !important;
        }
        .card:hover{ border-color:var(--violet-600) !important; }
        .card .card-header{
            background:transparent !important;
            border-bottom:1px solid color-mix(in oklab, var(--line-500) 70%, transparent) !important;
        }

        /* (1) Panel dégradé violet → transparent */
        .panel-gradient{
            border-radius:var(--r-lg);
            background: linear-gradient(135deg,
            color-mix(in oklab, var(--violet-400) 18%, white) 0%,
            color-mix(in oklab, var(--violet-700) 14%, transparent) 46%,
            transparent 78%);
            padding:1rem;
            border:1px solid color-mix(in oklab, var(--line-500) 55%, transparent);
        }
        .panel-gradient--soft{
            background: linear-gradient(135deg,
            color-mix(in oklab, var(--violet-400) 10%, white) 0%,
            transparent 60%);
        }

        /* (2) Panel transparent + bordure dégradée */
        .panel-border-grad{
            padding:1px; border-radius:var(--r-lg);
            background: linear-gradient(135deg, var(--violet-600), var(--violet-500));
        }
        .panel-border-grad > .inner{
            background:transparent; border-radius:calc(var(--r-lg) - 1px); padding:1rem;
        }

        /* (3) Panel transparent + bordure pleine */
        .panel-border-solid{
            border-radius:var(--r-lg);
            background:transparent;
            border:1.5px solid var(--violet-600);
            padding:1rem;
        }
        .panel-border-solid--soft{
            background:transparent;
            border:1.5px solid var(--violet-600);
            padding:1rem;
        }

        /* Encadrements génériques Bootstrap */
        .border-primary{ border-color:var(--violet-700) !important; }
        .border-success{ border-color:var(--violet-600) !important; } /* alias si besoin */
        .bg-primary-subtle{ background-color:color-mix(in oklab, var(--violet-300) 22%, white) !important; }

        /* ===================================================================== */
        /* =====================  NAVS / PILLS / TABS  ========================== */
        /* ===================================================================== */

        .nav-pills .nav-link.active, .nav-pills .show > .nav-link{
            background-color:var(--violet-600) !important;
            color:#ffffff !important;
            border-color:color-mix(in oklab, var(--violet-600) 65%, black) !important;
        }
        .nav-tabs .nav-link.active{
            border-color:var(--violet-600) !important;
            border-bottom-color:transparent !important;
        }
        .nav-tabs .nav-link:hover{ border-color:color-mix(in oklab, var(--violet-600) 55%, white) !important; }

        /* ===================================================================== */
        /* =======================  FORMULAIRES  ================================ */
        /* ===================================================================== */

        .form-control:focus, .form-select:focus{
            border-color:var(--violet-600) !important;
            box-shadow:0 0 0 .2rem color-mix(in oklab, var(--violet-400) 28%, white) !important;
        }

        /* Checkboxes / radios */
        .form-check-input:checked{
            background-color:var(--violet-600) !important;
            border-color:var(--violet-600) !important;
        }
        .form-check-input:focus{
            box-shadow:0 0 0 .2rem color-mix(in oklab, var(--violet-400) 28%, white) !important;
            border-color:var(--violet-600) !important;
        }

        /* Switch */
        .form-switch .form-check-input:checked{
            background-color:var(--violet-600) !important;
            border-color:var(--violet-600) !important;
        }

        /* Range */
        .form-range::-webkit-slider-thumb{ background:var(--violet-600); }
        .form-range::-webkit-slider-runnable-track{ background:color-mix(in oklab, var(--violet-400) 25%, #ddd); }
        .form-range::-moz-range-thumb{ background:var(--violet-600); }
        .form-range::-moz-range-track{ background:color-mix(in oklab, var(--violet-400) 25%, #ddd); }

        /* ===================================================================== */
        /* =============  LISTS / PAGINATION / DROPDOWN / PROGRESS  ============ */
        /* ===================================================================== */

        .list-group-item.active{
            background-color:var(--violet-600) !important;
            border-color:var(--violet-600) !important;
            color:#ffffff !important;
        }

        .pagination .page-item.active .page-link{
            background-color:var(--violet-600) !important;
            border-color:var(--violet-600) !important;
            color:#ffffff !important;
        }

        .dropdown-item.active, .dropdown-item:active{
            background-color:var(--violet-600) !important;
            color:#ffffff !important;
        }

        .progress .progress-bar{ background-color:var(--violet-600) !important; }

        /* ===================================================================== */
        /* ==========================  TABLES  ================================== */
        /* ===================================================================== */

        .table-primary{
            --bs-table-bg: color-mix(in oklab, var(--violet-300) 16%, white) !important;
            --bs-table-border-color: color-mix(in oklab, var(--violet-600) 35%, white) !important;
        }
        .table thead th{
            border-bottom:2px solid color-mix(in oklab, var(--violet-600) 40%, white) !important;
        }

        /* ===================================================================== */
        /* =========================  ACCENTS DIVERS  =========================== */
        /* ===================================================================== */

        /* Lien souligné premium — accent violet (le jaune reste secondaire) */
        .accent-underline{
            text-decoration:none;
            box-shadow: inset 0 -2px 0 0 var(--violet-600);
            transition: box-shadow .2s ease;
        }
        .accent-underline:hover{ box-shadow: inset 0 -3px 0 0 var(--violet-500); }

        /* Focus générique réutilisable (clavier) */
        .focus-accent:focus-visible{
            outline:3px solid color-mix(in oklab, var(--violet-400) 60%, white);
            outline-offset:3px;
        }

        /* === Usage du jaune (accents secondaires uniquement) ================= */
        .badge.bg-warning,
        .badge.text-bg-warning{ background-color:var(--yellow) !important; color:#1d1d1d !important; }
        .text-yellow{ color:var(--yellow) !important; }
        .border-yellow{ border-color:var(--yellow) !important; }

        /* === Breadcrumb Violet — mode clair === */
        [data-coreui-theme="light"] .breadcrumb {
            background:var(--line-300);
            border:1px solid var(--violet-600);
            box-shadow:0 2px 6px rgba(0,0,0,.05);
        }
        [data-coreui-theme="light"] .breadcrumb-item { color:var(--violet-900); }
        [data-coreui-theme="light"] .breadcrumb-item+.breadcrumb-item::before { color:var(--violet-700); }
        [data-coreui-theme="light"] .breadcrumb-item a {
            color:var(--violet-700);
        }
        [data-coreui-theme="light"] .breadcrumb-item a:hover {
            color:var(--violet-600);
            box-shadow:inset 0 -2px 0 0 var(--violet-600);
        }
        [data-coreui-theme="light"] .breadcrumb-item.active {
            color:var(--violet-900);
            font-weight:600;
        }

        /* === Breadcrumb Violet — mode sombre === */
        [data-coreui-theme="dark"] .breadcrumb {
            background:var(--violet-900);
            border:1px solid var(--violet-600);
            box-shadow:0 2px 6px rgba(0,0,0,.35);
        }
        [data-coreui-theme="dark"] .breadcrumb-item { color:#eae6f8; }
        [data-coreui-theme="dark"] .breadcrumb-item+.breadcrumb-item::before { color:var(--violet-400); }
        [data-coreui-theme="dark"] .breadcrumb-item a { color:var(--line-300); }
        [data-coreui-theme="dark"] .breadcrumb-item a:hover {
            color:var(--violet-300);
            box-shadow:inset 0 -2px 0 0 var(--violet-300);
        }
        [data-coreui-theme="dark"] .breadcrumb-item.active {
            color:#fff;
            font-weight:600;
        }

        /* === Breadcrumb Violet (défaut) === */
        .breadcrumb{
            margin:0;
            padding:.75rem 1rem;
            border-radius:var(--r-md);
            background:var(--violet-900);
            border:1px solid var(--violet-600);
            box-shadow:0 2px 6px rgba(0,0,0,.35);
        }
        .breadcrumb-item{
            font-weight:500;
            color:#eae6f8;
        }
        .breadcrumb-item+.breadcrumb-item::before{
            content:"›";
            color:var(--violet-400);
            margin:0 .5rem;
            font-weight:bold;
        }
        .breadcrumb-item a{
            color:var(--line-300);
            text-decoration:none;
            transition:color .2s ease, box-shadow .2s ease;
            box-shadow:inset 0 -2px 0 0 transparent;
        }
        .breadcrumb-item a:hover{
            color:var(--violet-300);
            box-shadow:inset 0 -2px 0 0 var(--violet-300);
        }
        .breadcrumb-item.active{
            color:#fff;
            font-weight:600;
        }
    </style>


</head>
<body>
<?php if (isset($menus)) {
    echo view($template_dir . 'sidebar', ['menus' => $menus]);
} ?>
<div class="wrapper d-flex flex-column min-vh-100">
    <?php if (isset($breadcrumb)) {
        echo view($template_dir . '/breadcrumb');
    } ?>
    <div class="body flex-grow-1">
        <div class="container-fluid px-4">
