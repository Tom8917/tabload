<!DOCTYPE html>
<html lang="fr-FR" data-coreui-theme="auto">
<head>
    <base href="<?= base_url('/') ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <title><?= esc($title ?? 'Admin') ?></title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="57x57" href="<?= base_url('assets/favicon/apple-icon-57x57.png') ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= base_url('assets/favicon/apple-icon-60x60.png') ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= base_url('assets/favicon/apple-icon-72x72.png') ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets/favicon/apple-icon-76x76.png') ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= base_url('assets/favicon/apple-icon-114x114.png') ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= base_url('assets/favicon/apple-icon-120x120.png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= base_url('assets/favicon/apple-icon-144x144.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('assets/favicon/apple-icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon/apple-icon-180x180.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('assets/favicon/android-icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= base_url('assets/favicon/favicon-96x96.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/favicon/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= base_url('assets/favicon/manifest.json') ?>">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= base_url('vendors/simplebar/css/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/vendors/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/@coreui/chartjs/css/coreui-chartjs.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/sweetalert2.min.css') ?>">

    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>

    <!-- Leaflet (une seule fois) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Quill -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <!-- TinyMCE (si tu l’utilises en front) -->
    <script src="<?= base_url('js/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= base_url('assets/tinymce/tinymce.min.js') ?>"></script>

    <!-- Core JS (ordre propre) -->
    <script src="<?= base_url('js/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('js/color-modes.js') ?>"></script>
    <script src="<?= base_url('js/config.js') ?>"></script>

    <script src="<?= base_url('vendors/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
    <script src="<?= base_url('vendors/simplebar/js/simplebar.min.js') ?>"></script>
    <script src="<?= base_url('vendors/@coreui/utils/js/index.js') ?>"></script>

    <!-- Plugins JS -->
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.js"></script>

    <script src="<?= base_url('js/toastr.min.js') ?>"></script>
    <script src="<?= base_url('js/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= base_url('js/bootstrap.bundle.min.js') ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- TinyMCE -->
    <script src="<?= base_url('js/tinymce/tinymce.min.js') ?>"></script>

    <!-- Admin JS (uniquement admin) -->
    <script src="<?= base_url('js/admin.js') ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const header = document.querySelector('header.header');
            document.addEventListener('scroll', () => {
                if (header) header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
            });

            if (window.toastr) {
                toastr.options = { positionClass: "toast-top-right", timeOut: "3000" };
            }
        });
    </script>

    <style>
        /* =========================================================
           TABLOAD BRAND THEME (CoreUI + Bootstrap 5 + plugins)
           Colors:
             Blue   #13438B
             Orange #FC9000
           ========================================================= */

        :root{
            --tl-blue: #13438B;
            --tl-blue-hover: #0f356f;
            --tl-blue-active: #0c2b59;

            --tl-orange: #FC9000;
            --tl-orange-hover: #f08a00;

            --tl-bg: whitesmoke;

            /* Bootstrap */
            --bs-primary: var(--tl-blue);
            --bs-link-color: var(--tl-blue);
            --bs-link-hover-color: var(--tl-blue-hover);

            /* CoreUI */
            --cui-primary: var(--tl-blue);
            --cui-link-color: var(--tl-blue);
        }

        body { background-color: var(--tl-bg); }

        /* ---------------------------------
           LINKS
        ---------------------------------- */
        a { color: var(--tl-blue); }
        a:hover { color: var(--tl-blue-hover); }

        /* ---------------------------------
           BUTTONS (robuste = force bg/border)
           -> évite les pages où les variables ne s'appliquent pas
        ---------------------------------- */

        /* Bootstrap primary */
        .btn.btn-primary{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
            color: #fff !important;
        }
        .btn.btn-primary:hover{
            background-color: var(--tl-blue-hover) !important;
            border-color: var(--tl-blue-hover) !important;
        }
        .btn.btn-primary:active,
        .btn.btn-primary.active,
        .btn.btn-primary:focus{
            background-color: var(--tl-blue-active) !important;
            border-color: var(--tl-blue-active) !important;
        }

        /* Bootstrap outline primary */
        .btn.btn-outline-primary{
            color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
        }
        .btn.btn-outline-primary:hover{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
            color: #fff !important;
        }
        .btn.btn-outline-primary:active,
        .btn.btn-outline-primary.active{
            background-color: var(--tl-blue-hover) !important;
            border-color: var(--tl-blue-hover) !important;
            color: #fff !important;
        }

        /* CoreUI ghost primary (souvent utilisé dans toolbars) */
        .btn.btn-ghost-primary{
            color: var(--tl-blue) !important;
            border-color: transparent !important;
            background: transparent !important;
        }
        .btn.btn-ghost-primary:hover{
            background: rgba(19,67,139,.08) !important;
            color: var(--tl-blue) !important;
        }

        /* CTA orange */
        .btn.btn-tl-orange{
            background: var(--tl-orange) !important;
            border-color: var(--tl-orange) !important;
            color: #111 !important;
            font-weight: 700;
        }
        .btn.btn-tl-orange:hover{
            background: var(--tl-orange-hover) !important;
            border-color: var(--tl-orange-hover) !important;
            color: #111 !important;
        }

        .btn.btn-outline-tl-orange{
            background: transparent !important;
            border-color: var(--tl-orange) !important;
            color: var(--tl-orange) !important;
            font-weight: 700;
        }
        .btn.btn-outline-tl-orange:hover{
            background: var(--tl-orange) !important;
            border-color: var(--tl-orange) !important;
            color: #111 !important;
        }

        /* ---------------------------------
           FORMS (focus / checked)
        ---------------------------------- */
        .form-control:focus,
        .form-select:focus,
        textarea:focus,
        input:focus{
            border-color: rgba(19,67,139,.55) !important;
            box-shadow: 0 0 0 .2rem rgba(19,67,139,.20) !important;
        }

        .form-check-input:checked{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
        }
        .form-check-input:focus{
            box-shadow: 0 0 0 .2rem rgba(19,67,139,.20) !important;
            border-color: rgba(19,67,139,.55) !important;
        }

        /* Select2 (bootstrap-5-theme) */
        .select2-container--bootstrap-5 .select2-selection{
            min-height: calc(2.375rem + 2px);
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5 .select2-selection:focus,
        .select2-container--bootstrap-5 .select2-selection:focus-within{
            border-color: rgba(19,67,139,.55) !important;
            box-shadow: 0 0 0 .2rem rgba(19,67,139,.20) !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__option--highlighted{
            background-color: rgba(19,67,139,.10) !important;
            color: #111 !important;
        }

        /* ---------------------------------
           BADGES / CHIPS
        ---------------------------------- */
        .badge.bg-primary{ background-color: var(--tl-blue) !important; }
        .badge.bg-warning{ background-color: var(--tl-orange) !important; color: #111 !important; }

        .text-tl-blue{ color: var(--tl-blue) !important; }
        .text-tl-orange{ color: var(--tl-orange) !important; }
        .bg-tl-blue{ background-color: var(--tl-blue) !important; color: #fff !important; }
        .bg-tl-orange{ background-color: var(--tl-orange) !important; color: #111 !important; }

        /* ---------------------------------
           NAV / PILLS / ACTIVE
        ---------------------------------- */
        .nav-pills .nav-link.active,
        .nav-link.active{
            background-color: var(--tl-blue) !important;
            color: #fff !important;
        }

        /* ---------------------------------
           PAGINATION
        ---------------------------------- */
        .page-link{ color: var(--tl-blue); }
        .page-link:hover{ color: var(--tl-blue-hover); }
        .page-item.active .page-link{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
        }

        /* ---------------------------------
           DATATABLES (Buttons extension)
           -> assure que les boutons générés suivent la charte
        ---------------------------------- */
        .dt-buttons .btn,
        .dataTables_wrapper .dt-buttons .btn{
            margin-right: .25rem;
        }

        .dt-buttons .btn.btn-primary,
        .dataTables_wrapper .dt-buttons .btn.btn-primary{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
            color: #fff !important;
        }

        /* si DataTables te sort du btn-secondary par défaut */
        .dt-buttons .btn.btn-secondary,
        .dataTables_wrapper .dt-buttons .btn.btn-secondary{
            background-color: var(--tl-blue) !important;
            border-color: var(--tl-blue) !important;
            color: #fff !important;
        }

        .sidebar .nav-link.active{
            background: rgba(0,0,0,.06) !important;
            color: #111 !important;
        }
    </style>

</head>
<body>
<?php
if (!empty($menus)) {
    echo view($template_dir . 'sidebar', [
        'menus'     => $menus,
        'user'      => $user ?? null,
        'localmenu' => $localmenu ?? null,
        'mainmenu'  => $mainmenu ?? null,
    ]);
}
?>

<div class="wrapper d-flex flex-column min-vh-100">
    <?php if (isset($breadcrumb)) {
        echo view($template_dir . '/breadcrumb');
    } ?>
    <div class="body flex-grow-1">
        <div class="container-fluid px-4">
