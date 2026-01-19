<!DOCTYPE html>
<html lang="fr-FR">
<head>
    <base href="<?= base_url('/') ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <title><?= esc($title ?? 'TabLoad') ?></title>

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

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= base_url('vendors/simplebar/css/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/vendors/simplebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/@coreui/chartjs/css/coreui-chartjs.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/toastr.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/sweetalert2.min.css') ?>">

    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/custom.css') ?>">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- DataTables (si tu l’utilises aussi en front) -->
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.css" rel="stylesheet">

    <!-- Select2 (si tu l’utilises aussi en front) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>

    <!-- Leaflet (si utilisé en front, une seule fois) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Quill -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <!-- JS (ordre propre) -->
    <script src="<?= base_url('js/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= base_url('js/color-modes.js') ?>"></script>
    <script src="<?= base_url('js/config.js') ?>"></script>

    <script src="<?= base_url('vendors/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
    <script src="<?= base_url('vendors/simplebar/js/simplebar.min.js') ?>"></script>
    <script src="<?= base_url('vendors/@coreui/utils/js/index.js') ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.0.0/b-3.0.0/b-html5-3.0.0/fh-4.0.0/sp-2.3.0/datatables.min.js"></script>

    <script src="<?= base_url('js/toastr.min.js') ?>"></script>
    <script src="<?= base_url('js/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= base_url('js/bootstrap.bundle.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- TinyMCE (si tu l’utilises en front) -->
    <script src="<?= base_url('js/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= base_url('assets/tinymce/tinymce.min.js') ?>"></script>

    <!-- FRONT JS (si tu veux, crée un fichier dédié) -->
    <!-- <script src="<?= base_url('js/front.js') ?>"></script> -->

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
        body { background-color: whitesmoke; }
    </style>
</head>
<body>
<?php
if (!empty($show_menu)) {
    echo view($template_dir . 'sidebar', [
        'menus'     => $menus ?? [],
        'user'      => $user ?? null,
        'localmenu' => $localmenu ?? null,
        'mainmenu'  => $mainmenu ?? null,
    ]);
}
?>
<div class="wrapper d-flex flex-column min-vh-100">
    <?php if (isset($breadcrumb)) echo view($template_dir . 'breadcrumb'); ?>
    <div class="body flex-grow-1">
        <div class="container-fluid px-4">
